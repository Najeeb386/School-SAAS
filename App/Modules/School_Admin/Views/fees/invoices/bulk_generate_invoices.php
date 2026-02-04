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
    $action = $_POST['action'] ?? $_GET['action'] ?? 'preview';
    
    // Get form data - accept both POST and GET for flexibility (GET for debugging)
    $session_id = intval($_POST['session_id'] ?? $_GET['session_id'] ?? 0);
    $billing_month_input = trim($_POST['billing_month'] ?? $_GET['billing_month'] ?? '');
    // Convert month input (YYYY-MM) to a full date (first day of month) for DB date column
    $billing_month = $billing_month_input ? date('Y-m-d', strtotime($billing_month_input . '-01')) : '';
    $apply_to = trim($_POST['apply_to'] ?? $_GET['apply_to'] ?? 'all');
    $class_id = intval($_POST['class_id'] ?? $_GET['class_id'] ?? 0);
    $due_date = trim($_POST['due_date'] ?? $_GET['due_date'] ?? date('Y-m-d'));
    // Normalize due_date to Y-m-d
    $due_date = $due_date ? date('Y-m-d', strtotime($due_date)) : null;
    
    // For diagnose_concession action, these are optional at first check
    if ($action !== 'diagnose_concession' && (!$session_id || !$billing_month)) {
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
        // Specific class - get students enrolled in that class
        $stmtStudents = $db->prepare('
            SELECT DISTINCT s.id, s.admission_no, s.first_name, s.last_name, e.class_id
            FROM school_students s
            INNER JOIN school_student_enrollments e ON e.student_id = s.id
            WHERE s.school_id = :sid AND e.class_id = :cid AND e.session_id = :sess AND s.status = 1
            ORDER BY s.first_name, s.last_name
        ');
        $stmtStudents->execute(['sid' => $school_id, 'cid' => $class_id, 'sess' => $session_id]);
    } else {
        // All classes - get all active students enrolled in this session
        $stmtStudents = $db->prepare('
            SELECT DISTINCT s.id, s.admission_no, s.first_name, s.last_name, e.class_id
            FROM school_students s
            INNER JOIN school_student_enrollments e ON e.student_id = s.id
            WHERE s.school_id = :sid AND e.session_id = :sess AND s.status = 1
            ORDER BY e.class_id, s.first_name, s.last_name
        ');
        $stmtStudents->execute(['sid' => $school_id, 'sess' => $session_id]);
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

    // ============ CONCESSION DIAGNOSTIC MODE ============
    if ($action === 'diagnose_concession') {
        $admission_no = trim($_REQUEST['admission_no'] ?? '');
        if (!$admission_no) {
            throw new Exception('admission_no is required');
        }

        $month_start = date('Y-m-01', strtotime($billing_month));
        
        // Get all concessions for this school
        $stmtAll = $db->prepare('
            SELECT id, admission_no, value, value_type, applies_to, status, start_month, end_month
            FROM school_student_fees_concessions
            WHERE school_id = :sid
            ORDER BY admission_no
        ');
            try {
                $stmtAll->execute(['sid' => $school_id]);
                $all_concessions = $stmtAll->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log('[DIAG_CONCESSION_SEARCH] stmtAll failed: ' . $e->getMessage());
                error_log('[DIAG_CONCESSION_SEARCH] SQL: ' . $stmtAll->queryString);
                error_log('[DIAG_CONCESSION_SEARCH] Params: ' . json_encode(['sid' => $school_id]));
                throw new Exception('Concession lookup failed: ' . $e->getMessage());
            }
        
        // Try to find matching concession
        $stmtMatch = $db->prepare('
            SELECT id, admission_no, value, value_type, applies_to, status, start_month, end_month
            FROM school_student_fees_concessions
            WHERE school_id = :sid AND status = 1
            AND (end_month IS NULL OR end_month = "0000-00-00" OR end_month >= :month_start)
            AND (start_month IS NULL OR start_month <= :month_start)
            AND admission_no = :adno
            LIMIT 1
        ');
            $matchParams = ['sid' => $school_id, 'month_start' => $month_start, 'adno' => $admission_no];
            try {
                $stmtMatch->bindValue(':sid', $matchParams['sid']);
                $stmtMatch->bindValue(':month_start', $matchParams['month_start']);
                $stmtMatch->bindValue(':adno', $matchParams['adno']);
                error_log('[DIAG_CONCESSION_SEARCH] Binding params: ' . json_encode($matchParams));
                $stmtMatch->execute();
                $matched = $stmtMatch->fetch(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log('[DIAG_CONCESSION_SEARCH] stmtMatch failed: ' . $e->getMessage());
                error_log('[DIAG_CONCESSION_SEARCH] SQL: ' . $stmtMatch->queryString);
                error_log('[DIAG_CONCESSION_SEARCH] Params: ' . json_encode($matchParams));
                throw new Exception('Concession match lookup failed: ' . $e->getMessage());
            }
        
        $response = [
            'school_id' => $school_id,
            'searching_for_admission_no' => $admission_no,
            'admission_no_length' => strlen($admission_no),
            'billing_month' => $billing_month,
            'month_start_filter' => $month_start,
            'total_concessions_in_school' => count($all_concessions),
            'all_concessions' => $all_concessions,
            'matched_concession' => $matched,
            'match_found' => $matched ? true : false,
            'debug_info' => []
        ];
        
        // Check each concession against the search criteria
        foreach ($all_concessions as $con) {
            $debug = [
                'admission_no' => $con['admission_no'],
                'matches_search_adno' => $con['admission_no'] === $admission_no,
                'status' => $con['status'],
                'status_active' => $con['status'] == 1,
                'start_month' => $con['start_month'],
                'end_month' => $con['end_month'],
                'start_month_check' => ($con['start_month'] === null || $con['start_month'] === '' || $con['start_month'] <= $month_start),
                'end_month_check' => ($con['end_month'] === null || $con['end_month'] === '0000-00-00' || $con['end_month'] >= $month_start),
                'value' => $con['value'],
                'value_type' => $con['value_type'],
                'applies_to' => $con['applies_to'],
                'would_match' => ($con['admission_no'] === $admission_no && $con['status'] == 1 && 
                                  ($con['start_month'] === null || $con['start_month'] <= $month_start) &&
                                  ($con['end_month'] === null || $con['end_month'] === '0000-00-00' || $con['end_month'] >= $month_start))
            ];
            $response['debug_info'][] = $debug;
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        ob_end_flush();
        exit;
    }

    // ============ DEBUG MODE ============
    if ($action === 'debug') {
        $admission_no = trim($_REQUEST['admission_no'] ?? '');
        if (!$admission_no) {
            throw new Exception('admission_no is required for debug');
        }

        // Find the student id and class for this admission_no in this school/session
        $stmtS = $db->prepare('SELECT id, admission_no FROM school_students WHERE school_id = :sid AND admission_no = :adno LIMIT 1');
        $stmtS->execute(['sid' => $school_id, 'adno' => $admission_no]);
        $stu = $stmtS->fetch(\PDO::FETCH_ASSOC);
        if (!$stu) throw new Exception('Student not found for admission_no');

        // Try to get the student's enrollment class for the given session
        $stmtE = $db->prepare('SELECT class_id FROM school_student_enrollments WHERE student_id = :stid AND session_id = :sess LIMIT 1');
        $stmtE->execute(['stid' => $stu['id'], 'sess' => $session_id]);
        $enr = $stmtE->fetch(\PDO::FETCH_ASSOC);
        $class_id_debug = $enr['class_id'] ?? 0;

        $calc = calculateInvoiceAmount($db, $school_id, $stu['id'], $stu['admission_no'], $session_id, $class_id_debug, $billing_month, $additional_fees);

        echo json_encode(['success' => true, 'calculation' => $calc, 'class_id' => $class_id_debug]);
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
                    'sid' => $school_id,
                    'stid' => $student['id'],
                    'ssid' => $session_id,
                    'bm' => $billing_month
                ]);
                
                if ($stmtCheck->rowCount() > 0) {
                    continue; // Skip if invoice already exists
                }

                // Calculate invoice amount
                $calc = calculateInvoiceAmount(
                    $db, $school_id, $student['id'], $student['admission_no'],
                    $session_id, $student['class_id'], $billing_month, $additional_fees
                );

                // Validate that we have at least some fees to invoice
                if ($calc['total_amount'] <= 0 && empty($calc['fee_items'])) {
                    $errors[] = $student['admission_no'] . ' - No fees found for class. Check if fee assignments exist for this class/session.';
                    continue;
                }

                // Generate invoice number using atomic counter from invoice_counters table
                $invoice_no = getNextInvoiceNumber($db, $school_id, $session_id);

                // Ensure concession is always set (initialized to 0 if not set)
                $gross = (float)$calc['base_amount'];
                $concession = (float)($calc['concessions'] ?? 0);
                $additional = (float)($calc['additional_fees_total'] ?? 0);
                $net = max(0, $gross - $concession + $additional); // Never allow negative amount

                error_log("[InvoiceGenerate] invoice_no={$invoice_no} student_id={$student['id']} gross={$gross} concession={$concession} additional={$additional} net={$net}");

                // Insert into schoo_fee_invoices with gross, concession, and net amounts
                // Try to insert with new columns, fallback to old columns if they don't exist
                try {
                    $stmtInsert = $db->prepare('
                        INSERT INTO schoo_fee_invoices 
                        (school_id, student_id, session_id, invoice_no, billing_month, gross_amount, concession_amount, net_payable, total_amount, status, due_date, created_at, updated_at)
                        VALUES (:sid, :stid, :ssid, :inv, :bm, :gross, :concession, :net, :total, :status, :due, NOW(), NOW())
                    ');
                    
                    $stmtInsert->execute([
                        'sid' => $school_id,
                        'stid' => $student['id'],
                        'ssid' => $session_id,
                        'inv' => $invoice_no,
                        'bm' => $billing_month,
                        'gross' => $gross,
                        'concession' => $concession,
                        'net' => $net,
                        'total' => $net,  // total_amount = net payable
                        'status' => 'draft',
                        'due' => $due_date
                    ]);
                } catch (Exception $e) {
                    // Fallback: columns don't exist yet, use old schema
                    if (strpos($e->getMessage(), 'Unknown column') !== false) {
                        $stmtInsert = $db->prepare('
                            INSERT INTO schoo_fee_invoices (school_id, student_id, session_id, invoice_no, billing_month, total_amount, status, due_date, created_at, updated_at)
                            VALUES (:sid, :stid, :ssid, :inv, :bm, :total, :status, :due, NOW(), NOW())
                        ');
                        
                        $stmtInsert->execute([
                            'sid' => $school_id,
                            'stid' => $student['id'],
                            'ssid' => $session_id,
                            'inv' => $invoice_no,
                            'bm' => $billing_month,
                            'total' => $net,  // Net amount
                            'status' => 'pending',
                            'due' => $due_date
                        ]);
                        
                        error_log("[InvoiceGenerate] Using legacy schema (no gross/concession columns)");
                    } else {
                        throw $e;
                    }
                }

                $invoice_id = $db->lastInsertId();

                // Insert line items into schoo_fee_invoice_items
                foreach ($calc['fee_items'] as $item) {
                    $stmtItem = $db->prepare('
                        INSERT INTO schoo_fee_invoice_items (invoice_id, fee_item_id, description, amount, created_at)
                        VALUES (:inv_id, :fee_id, :desc, :amount, NOW())
                    ');
                    
                    $stmtItem->execute([
                        'inv_id' => $invoice_id,
                        'fee_id' => intval($item['fee_item_id'] ?? 0),
                        'desc' => $item['description'],
                        'amount' => (float)($item['amount'] ?? 0)
                    ]);
                }

                $invoice_count++;

            } catch (Exception $e) {
                error_log('Invoice generation error for ' . $student['admission_no'] . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());
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
            $error_details = !empty($errors) ? implode('; ', $errors) : 'No students processed. Check if fee assignments exist for the selected class/session.';
            throw new Exception($error_details);
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
 * Get next invoice number using atomic counter increment with FOR UPDATE lock
 * Format: INV-{school_id}-{session_year}-{zero_padded_counter}
 */
function getNextInvoiceNumber($db, $school_id, $session_id) {
    try {
        // Get session year from the session_id by extracting from start_date
        $stmtSession = $db->prepare('SELECT YEAR(start_date) as year FROM school_sessions WHERE id = :sid LIMIT 1');
        $stmtSession->execute(['sid' => $session_id]);
        $sessionData = $stmtSession->fetch(\PDO::FETCH_ASSOC);
        $session_year = $sessionData['year'] ?? date('Y');
        
        // Lock the counter row for this school/session and increment atomically
        // SELECT...FOR UPDATE ensures exclusive lock on the row
        $stmtLock = $db->prepare(
            'SELECT id, current_counter FROM invoice_counters '
            . 'WHERE school_id = :sid AND session_id = :ssid '
            . 'FOR UPDATE'
        );
        $stmtLock->execute(['sid' => $school_id, 'ssid' => $session_id]);
        $counter = $stmtLock->fetch(\PDO::FETCH_ASSOC);
        
        if (!$counter) {
            // Counter doesn't exist, create it
            $stmtInsert = $db->prepare(
                'INSERT INTO invoice_counters (school_id, session_id, prefix, current_counter, reset_type) '
                . 'VALUES (:sid, :ssid, "INV", 0, "session")'
            );
            $stmtInsert->execute(['sid' => $school_id, 'ssid' => $session_id]);
            $current_counter = 0;
        } else {
            $current_counter = intval($counter['current_counter']);
        }
        
        // Increment counter
        $next_counter = $current_counter + 1;
        
        // Update the counter
        $stmtUpdate = $db->prepare(
            'UPDATE invoice_counters SET current_counter = :next_counter '
            . 'WHERE school_id = :sid AND session_id = :ssid'
        );
        $stmtUpdate->execute([
            'next_counter' => $next_counter,
            'sid' => $school_id,
            'ssid' => $session_id
        ]);
        
        // Build invoice number: INV-{school_id}-{session_year}-{zero_padded_counter}
        $invoice_no = 'INV-' . $school_id . '-' . $session_year . '-' . str_pad($next_counter, 5, '0', STR_PAD_LEFT);
        
        error_log("[InvoiceCounter] Generated invoice_no={$invoice_no} for school_id={$school_id} session_id={$session_id}");
        
        return $invoice_no;
    } catch (Exception $e) {
        error_log('Error getting next invoice number: ' . $e->getMessage());
        throw new Exception('Failed to generate invoice number: ' . $e->getMessage());
    }
}

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
        // 1. Get fee structure from schoo_fee_assignments, join schoo_fee_items to obtain names/amounts
        $stmtFeeAssignment = $db->prepare('
            SELECT a.id, a.fee_item_id, a.amount AS assignment_amount, fi.name AS fee_name, fi.amount AS item_amount, fi.category_id AS fee_category_id, fc.name AS fee_category_name
            FROM schoo_fee_assignments a
            LEFT JOIN schoo_fee_items fi ON fi.id = a.fee_item_id
            LEFT JOIN schoo_fee_categories fc ON fc.id = fi.category_id
            WHERE a.school_id = :sid AND a.class_id = :cid AND a.session_id = :ssid
        ');
        $stmtFeeAssignment->execute([
            'sid' => $school_id,
            'cid' => $class_id,
            'ssid' => $session_id
        ]);

        $fee_assignments = $stmtFeeAssignment->fetchAll(\PDO::FETCH_ASSOC);
        // DEBUG: log fee assignments fetched for this class/session
        error_log("[InvoiceCalc] student_id={$student_id} class_id={$class_id} session_id={$session_id} fee_assignments=" . json_encode($fee_assignments));

        // Add base fees to result (use assignment amount if present, otherwise fall back to item amount)
        foreach ($fee_assignments as $fa) {
            $amount = 0.0;
            if (isset($fa['assignment_amount']) && $fa['assignment_amount'] !== null && $fa['assignment_amount'] !== '') {
                $amount = (float)$fa['assignment_amount'];
            } elseif (isset($fa['item_amount'])) {
                $amount = (float)$fa['item_amount'];
            }

            $desc = $fa['fee_name'] ?? 'Base Fee';

            $result['base_amount'] += $amount;
            $result['fee_items'][] = [
                'fee_item_id' => intval($fa['fee_item_id'] ?? 0),
                'description' => $desc,
                'amount' => $amount,
                'category_id' => intval($fa['category_id'] ?? 0),
                'category_name' => $fa['fee_category_name'] ?? ''
            ];
        }

        // 2. Check for concessions/scholarships in school_student_fees_concessions (by admission_no)
        // NOTE: To keep behaviour simple and robust, we currently treat all
        // active concession rows for a student as applicable, without
        // filtering by start/end month. This guarantees that if an entry
        // exists in school_student_fees_concessions for the admission_no,
        // it will be picked up during invoice calculation.
        // DIAGNOSTIC: Log what we're searching for
        error_log("[DIAG_CONCESSION_SEARCH] ========== STARTING CONCESSION SEARCH ==========");
        error_log("[DIAG_CONCESSION_SEARCH] school_id={$school_id}");
        error_log("[DIAG_CONCESSION_SEARCH] admission_no='{$admission_no}' (LENGTH: " . strlen($admission_no) . ")");
        error_log("[DIAG_CONCESSION_SEARCH] student_id={$student_id}");
        
        // First, let's check what concession records exist for this school
        $stmtCheckAll = $db->prepare('
            SELECT id, admission_no, value, value_type, applies_to, status, start_month, end_month
            FROM school_student_fees_concessions
            WHERE school_id = :sid
            ORDER BY admission_no
        ');
        $stmtCheckAll->execute(['sid' => $school_id]);
        $allConcessions = $stmtCheckAll->fetchAll(\PDO::FETCH_ASSOC);
        error_log("[DIAG_CONCESSION_SEARCH] Total concession records in school_id={$school_id}: " . count($allConcessions));
        foreach ($allConcessions as $con) {
            error_log("[DIAG_CONCESSION_SEARCH]   - admission_no='{$con['admission_no']}' (LENGTH: " . strlen($con['admission_no']) . ") status={$con['status']} value={$con['value']} value_type={$con['value_type']}");
        }
        
        error_log("[DIAG_CONCESSION_SEARCH] ========== RUNNING ACTUAL QUERY ==========");
        
        // NOTE: A student may have multiple concession rows (e.g. scholarship + fixed discount).
        // We therefore fetch ALL matching rows and aggregate their effect, instead of arbitrarily picking one.
        $stmtConcession = $db->prepare(
            'SELECT id, value, value_type, applies_to, start_month, end_month, admission_no 
             FROM school_student_fees_concessions
             WHERE school_id = :sid 
               AND status = 1
               AND admission_no = :adno'
        );

        try {
            $stmtConcession->bindValue(':sid', $school_id);
            $stmtConcession->bindValue(':adno', $admission_no);
            error_log('[DIAG_CONCESSION_SEARCH] Binding params for calc: ' . json_encode(['sid' => $school_id, 'adno' => $admission_no]));
            $stmtConcession->execute();
        } catch (\PDOException $e) {
            error_log('[DIAG_CONCESSION_SEARCH] stmtConcession failed: ' . $e->getMessage());
            error_log('[DIAG_CONCESSION_SEARCH] SQL: ' . $stmtConcession->queryString);
            error_log('[DIAG_CONCESSION_SEARCH] Params: ' . json_encode(['sid' => $school_id, 'month_start' => $month_start, 'adno' => $admission_no]));
            throw new Exception('Concession match lookup failed: ' . $e->getMessage());
        }

        $concessions = $stmtConcession->fetchAll(\PDO::FETCH_ASSOC);
        
        error_log("[DIAG_CONCESSION_SEARCH] Query returned count=" . count($concessions));
        if (!empty($concessions)) {
            error_log("[DIAG_CONCESSION_SEARCH] Concession rows: " . json_encode($concessions));
        }

        if (!empty($concessions)) {
            $total_concession_amount = 0.0;

            foreach ($concessions as $concession) {
                $con_val = (float)($concession['value'] ?? 0);
                $con_type = strtolower($concession['value_type'] ?? 'fixed');
                $applies_to = strtolower($concession['applies_to'] ?? 'all');

                // Determine base amount for this concession: either overall base_amount or only tuition items
                $base_for_concession = $result['base_amount'];
                if ($applies_to === 'tuition_only') {
                    $tuition_total = 0.0;
                    foreach ($result['fee_items'] as $fi_item) {
                        $cat_name = strtolower(trim($fi_item['category_name'] ?? ''));
                        $cat_id = intval($fi_item['category_id'] ?? 0);
                        if ($cat_id === 1 || strpos($cat_name, 'tuition') !== false) {
                            $tuition_total += (float)$fi_item['amount'];
                        }
                    }
                    $base_for_concession = $tuition_total;
                }

                if ($con_type === 'percentage' || $con_type === 'percent') {
                    $concession_amount = ($base_for_concession * $con_val) / 100.0;
                } else {
                    // treat as fixed amount
                    $concession_amount = $con_val;
                }

                // Cap this concession so we never exceed the base used for it
                $concession_amount = max(0.0, min($concession_amount, $base_for_concession));
                $total_concession_amount += $concession_amount;

                // If aggregated concession would exceed overall base, cap it
                if ($total_concession_amount >= $result['base_amount']) {
                    $total_concession_amount = $result['base_amount'];
                    break;
                }
            }

            // Store total concession amount
            $result['concessions'] = (float)$total_concession_amount;
            
            // Add a single aggregated concession line item (negative amount) for invoice detail
            if ($total_concession_amount > 0) {
                $result['fee_items'][] = [
                    'fee_item_id' => 0,
                    'description' => 'Concessions / Scholarships',
                    'amount' => -1 * $total_concession_amount
                ];
            }

            // DEBUG: log aggregated concession details
            error_log("[InvoiceCalc] CONCESSIONS APPLIED: admission_no={$admission_no} session_id={$session_id} total_concession={$total_concession_amount} base_amount={$result['base_amount']}");
        } else {
            // No concession found - explicitly log this so we know it was checked
            error_log("[InvoiceCalc] NO CONCESSION FOUND: admission_no={$admission_no} school_id={$school_id} month_start={$month_start}");
            $result['concessions'] = 0.0;
        }

        // 3. Add additional fees -- try to map to schoo_fee_items (by code or name) if available
        // 3. Add additional fees.
        // If the user does not manually provide an amount for a selected
        // additional fee type, we will automatically fall back to the
        // default amount defined in schoo_fee_items (matched by code or name).
        $stmtFindItem = $db->prepare('SELECT id, name, amount FROM schoo_fee_items WHERE school_id = :sid AND (code = :code OR name LIKE :like) LIMIT 1');
        foreach ($additional_fees as $fee_key => $fee_data) {
            // Attempt to find a matching fee item in schoo_fee_items
            $like = '%' . str_replace('_', ' ', $fee_key) . '%';
            $stmtFindItem->execute(['sid' => $school_id, 'code' => $fee_key, 'like' => $like]);
            $found = $stmtFindItem->fetch(\PDO::FETCH_ASSOC);

            // Decide final amount:
            //  - Prefer the explicit amount typed by the user.
            //  - If it is empty/zero, but a fee item is found, use its default amount.
            $amount = (float)($fee_data['amount'] ?? 0);
            if ($amount <= 0 && $found && isset($found['amount'])) {
                $amount = (float)$found['amount'];
            }

            // Skip if we still have no positive amount
            if ($amount <= 0) {
                continue;
            }

            $result['additional_fees_total'] += $amount;

            if ($found) {
                $fee_item_id = intval($found['id']);
                $desc = $found['name'];
            } else {
                $fee_item_id = 0;
                $desc = ucwords(str_replace('_', ' ', $fee_key)) . ' Fee';
            }

            $result['fee_items'][] = [
                'fee_item_id' => $fee_item_id,
                'description' => $desc,
                'amount' => $amount
            ];
        }

        // DEBUG: log additional fees and intermediate totals
        error_log("[InvoiceCalc] student_id={$student_id} additional_fees=" . json_encode($additional_fees));
        error_log("[InvoiceCalc] student_id={$student_id} base_amount={$result['base_amount']} concessions={$result['concessions']} additional_total={$result['additional_fees_total']} total_amount={$result['total_amount']}");

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
    // $billing_month may now be a full date (YYYY-MM-01), so parse directly
    $month_name = date('F Y', strtotime($billing_month));
    
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
