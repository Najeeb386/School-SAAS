<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../../Core/database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) {
        throw new Exception('Unauthorized');
    }

    $db = \Database::connect();
    $action = $_POST['action'] ?? 'preview';
    
    // Get form data
    $session_id = intval($_POST['session_id'] ?? 0);
    $billing_month = trim($_POST['billing_month'] ?? '');
    $apply_to = trim($_POST['apply_to'] ?? 'all');
    $class_id = intval($_POST['class_id'] ?? 0);
    $due_date = trim($_POST['due_date'] ?? date('Y-m-d'));
    
    if (!$session_id || !$billing_month) {
        throw new Exception('Session and billing month are required');
    }

    // Parse additional fees
    $additional_fees = [];
    $additional_fees_list = $_POST['additional_fees'] ?? [];
    if (!empty($additional_fees_list) && is_array($additional_fees_list)) {
        foreach ($additional_fees_list as $fee_type) {
            $amount_key = 'fee_' . $fee_type . '_amount';
            $amount = floatval($_POST[$amount_key] ?? 0);
            if ($amount > 0) {
                $additional_fees[$fee_type] = [
                    'type' => $fee_type,
                    'amount' => $amount
                ];
            }
        }
    }

    // Get students to invoice
    if ($apply_to === 'specific' && $class_id) {
        // Specific class
        $stmtStudents = $db->prepare('
            SELECT DISTINCT s.id, s.admission_no, s.first_name, s.last_name, s.class_id
            FROM school_students s
            WHERE s.school_id = :sid AND s.class_id = :cid AND s.status = 1
            ORDER BY s.first_name, s.last_name
        ');
        $stmtStudents->execute([':sid' => $school_id, ':cid' => $class_id]);
    } else {
        // All classes
        $stmtStudents = $db->prepare('
            SELECT DISTINCT s.id, s.admission_no, s.first_name, s.last_name, s.class_id
            FROM school_students s
            WHERE s.school_id = :sid AND s.status = 1
            ORDER BY s.class_id, s.first_name, s.last_name
        ');
        $stmtStudents->execute([':sid' => $school_id]);
    }
    
    $students = $stmtStudents->fetchAll(\PDO::FETCH_ASSOC);

    if (empty($students)) {
        throw new Exception('No active students found for selected criteria');
    }

    // ============ PREVIEW MODE ============
    if ($action === 'preview') {
        $preview_data = [];
        
        foreach ($students as $student) {
            $calc = calculateInvoiceAmount(
                $db, $school_id, $student['id'], $student['admission_no'],
                $session_id, $student['class_id'], $billing_month, $additional_fees
            );
            
            $preview_data[] = [
                'name' => $student['first_name'] . ' ' . $student['last_name'],
                'admission_no' => $student['admission_no'],
                'class_id' => $student['class_id'],
                'base_amount' => $calc['base_amount'],
                'concessions' => $calc['concessions'],
                'additional_fees_total' => $calc['additional_fees_total'],
                'total_amount' => $calc['total_amount'],
                'fee_items' => $calc['fee_items']
            ];
        }

        // Build preview HTML
        $preview_html = buildPreviewHTML($preview_data, $billing_month);

        echo json_encode([
            'success' => true,
            'message' => 'Preview generated successfully',
            'student_count' => count($students),
            'preview_html' => $preview_html
        ]);
        ob_end_flush();
        exit;
    }

    // ============ GENERATE MODE ============
    if ($action === 'generate') {
        $db->beginTransaction();
        
        $invoice_count = 0;
        $errors = [];

        foreach ($students as $student) {
            try {
                // Check if invoice already exists for this month
                $stmtCheck = $db->prepare('
                    SELECT id FROM schoo_fee_invoices
                    WHERE school_id = :sid AND student_id = :stid AND session_id = :ssid AND billing_month = :bm
                    LIMIT 1
                ');
                $stmtCheck->execute([
                    ':sid' => $school_id,
                    ':stid' => $student['id'],
                    ':ssid' => $session_id,
                    ':bm' => $billing_month
                ]);
                
                if ($stmtCheck->rowCount() > 0) {
                    continue; // Skip if invoice already exists
                }

                // Calculate invoice amount
                $calc = calculateInvoiceAmount(
                    $db, $school_id, $student['id'], $student['admission_no'],
                    $session_id, $student['class_id'], $billing_month, $additional_fees
                );

                // Generate invoice number
                $invoice_no = 'INV-' . $school_id . '-' . date('Y') . '-' . str_pad($invoice_count + 1, 5, '0', STR_PAD_LEFT);

                // Insert into schoo_fee_invoices
                $stmtInsert = $db->prepare('
                    INSERT INTO schoo_fee_invoices (school_id, student_id, session_id, invoice_no, billing_month, total_amount, status, due_date, created_at, updated_at)
                    VALUES (:sid, :stid, :ssid, :inv, :bm, :total, :status, :due, NOW(), NOW())
                ');
                
                $stmtInsert->execute([
                    ':sid' => $school_id,
                    ':stid' => $student['id'],
                    ':ssid' => $session_id,
                    ':inv' => $invoice_no,
                    ':bm' => $billing_month,
                    ':total' => $calc['total_amount'],
                    ':status' => 'pending',
                    ':due' => $due_date
                ]);

                $invoice_id = $db->lastInsertId();

                // Insert line items into schoo_fee_invoice_items
                foreach ($calc['fee_items'] as $item) {
                    $stmtItem = $db->prepare('
                        INSERT INTO schoo_fee_invoice_items (invoice_id, fee_item_id, description, amount, created_at)
                        VALUES (:inv_id, :fee_id, :desc, :amount, NOW())
                    ');
                    
                    $stmtItem->execute([
                        ':inv_id' => $invoice_id,
                        ':fee_id' => $item['fee_item_id'] ?? 0,
                        ':desc' => $item['description'],
                        ':amount' => $item['amount']
                    ]);
                }

                $invoice_count++;

            } catch (Exception $e) {
                $errors[] = $student['admission_no'] . ' - ' . $e->getMessage();
            }
        }

        $db->commit();

        if ($invoice_count > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Invoices generated successfully',
                'invoice_count' => $invoice_count,
                'errors' => $errors
            ]);
        } else {
            throw new Exception('No invoices were generated. Check if invoices already exist for this month.');
        }

        ob_end_flush();
        exit;
    }

    throw new Exception('Invalid action');

} catch (Exception $e) {
    error_log('Invoice Generation Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    ob_end_flush();
    exit;
}

// ===================== HELPER FUNCTIONS =====================

/**
 * Calculate invoice amount for a student
 */
function calculateInvoiceAmount($db, $school_id, $student_id, $admission_no, $session_id, $class_id, $billing_month, $additional_fees = []) {
    $result = [
        'base_amount' => 0,
        'concessions' => 0,
        'additional_fees_total' => 0,
        'total_amount' => 0,
        'fee_items' => []
    ];

    try {
        // 1. Get fee structure from school_fee_assignment
        $stmtFeeAssignment = $db->prepare('
            SELECT id, fee_item_id, amount FROM school_fee_assignment
            WHERE school_id = :sid AND class_id = :cid AND session_id = :ssid AND status = 1
        ');
        $stmtFeeAssignment->execute([
            ':sid' => $school_id,
            ':cid' => $class_id,
            ':ssid' => $session_id
        ]);
        
        $fee_assignments = $stmtFeeAssignment->fetchAll(\PDO::FETCH_ASSOC);

        // Add base fees to result
        foreach ($fee_assignments as $fa) {
            $result['base_amount'] += (float)$fa['amount'];
            $result['fee_items'][] = [
                'fee_item_id' => $fa['fee_item_id'],
                'description' => 'Base Fee',
                'amount' => $fa['amount']
            ];
        }

        // 2. Check for concessions/scholarships in school_student_fees_concessions
        $stmtConcession = $db->prepare('
            SELECT id, discount_value, discount_type FROM school_student_fees_concessions
            WHERE school_id = :sid AND student_id = :stid AND status = 1
            AND (end_month IS NULL OR end_month = "0000-00-00" OR end_month >= DATE_FORMAT(CURDATE(), "%Y-%m-01"))
            LIMIT 1
        ');
        $stmtConcession->execute([
            ':sid' => $school_id,
            ':stid' => $student_id
        ]);
        
        $concession = $stmtConcession->fetch(\PDO::FETCH_ASSOC);

        if ($concession) {
            if ($concession['discount_type'] === 'percentage') {
                $concession_amount = ($result['base_amount'] * $concession['discount_value']) / 100;
            } else {
                $concession_amount = (float)$concession['discount_value'];
            }
            
            $result['concessions'] = $concession_amount;
            $result['fee_items'][] = [
                'fee_item_id' => 0,
                'description' => 'Concession/Scholarship',
                'amount' => -$concession_amount
            ];
        }

        // 3. Add additional fees
        foreach ($additional_fees as $fee_key => $fee_data) {
            $amount = (float)$fee_data['amount'];
            $result['additional_fees_total'] += $amount;
            $result['fee_items'][] = [
                'fee_item_id' => 0,
                'description' => ucwords(str_replace('_', ' ', $fee_key)) . ' Fee',
                'amount' => $amount
            ];
        }

        // 4. Calculate total
        $result['total_amount'] = $result['base_amount'] - $result['concessions'] + $result['additional_fees_total'];

    } catch (Exception $e) {
        error_log('Error calculating invoice amount: ' . $e->getMessage());
    }

    return $result;
}

/**
 * Build preview HTML table
 */
function buildPreviewHTML($preview_data, $billing_month) {
    $month_name = date('F Y', strtotime($billing_month . '-01'));
    
    $html = '<h6 class="mb-3">Students to be invoiced for ' . htmlspecialchars($month_name) . '</h6>';
    $html .= '<div style="max-height: 500px; overflow-y: auto;">';
    $html .= '<table class="table table-sm table-striped">';
    $html .= '<thead class="thead-light"><tr><th>Admission #</th><th>Name</th><th>Base</th><th>Concessions</th><th>Additional</th><th>Total</th></tr></thead>';
    $html .= '<tbody>';

    $total_base = 0;
    $total_concessions = 0;
    $total_additional = 0;
    $total_invoice = 0;

    foreach ($preview_data as $p) {
        $total_base += $p['base_amount'];
        $total_concessions += $p['concessions'];
        $total_additional += $p['additional_fees_total'];
        $total_invoice += $p['total_amount'];

        $html .= '<tr>';
        $html .= '<td><small>' . htmlspecialchars($p['admission_no']) . '</small></td>';
        $html .= '<td><small>' . htmlspecialchars($p['name']) . '</small></td>';
        $html .= '<td><small>' . number_format($p['base_amount'], 2) . '</small></td>';
        $html .= '<td><small class="text-danger">-' . number_format($p['concessions'], 2) . '</small></td>';
        $html .= '<td><small class="text-info">' . number_format($p['additional_fees_total'], 2) . '</small></td>';
        $html .= '<td><small><strong>' . number_format($p['total_amount'], 2) . '</strong></small></td>';
        $html .= '</tr>';
    }

    $html .= '<tr class="font-weight-bold bg-light">';
    $html .= '<td colspan="2">TOTAL (' . count($preview_data) . ' students)</td>';
    $html .= '<td>' . number_format($total_base, 2) . '</td>';
    $html .= '<td class="text-danger">-' . number_format($total_concessions, 2) . '</td>';
    $html .= '<td class="text-info">' . number_format($total_additional, 2) . '</td>';
    $html .= '<td>' . number_format($total_invoice, 2) . '</td>';
    $html .= '</tr>';

    $html .= '</tbody></table>';
    $html .= '</div>';

    return $html;
}
