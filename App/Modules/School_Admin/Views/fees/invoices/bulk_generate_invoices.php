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
    
    // Period type for bulk invoices (default to 'single' for backward compatibility)
    $bulk_period_type = trim($_POST['period_type'] ?? $_GET['period_type'] ?? 'single');
    
    // For diagnose_concession, debug, and single-student actions, billing_month may be optional.
    // For bulk actions, validate based on period_type
    if (!in_array($action, ['diagnose_concession', 'debug', 'preview_single', 'generate_single'], true)) {
        if (!$session_id) {
            throw new Exception('Session is required');
        }
        if ($bulk_period_type === 'single' && !$billing_month) {
            throw new Exception('Billing month is required for single-month invoices');
        } elseif ($bulk_period_type === 'range') {
            $start_input = trim($_POST['start_month'] ?? $_GET['start_month'] ?? '');
            $end_input = trim($_POST['end_month'] ?? $_GET['end_month'] ?? '');
            if (!$start_input || !$end_input) {
                throw new Exception('Start and end month are required for range invoices');
            }
        }
        // full_session doesn't need billing_month validation
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

    // ============ SINGLE STUDENT MODES (preview_single / generate_single) ============
    if ($action === 'preview_single' || $action === 'generate_single') {
        $admission_no = trim($_POST['admission_no'] ?? $_GET['admission_no'] ?? '');
        if (!$session_id) {
            throw new Exception('Session is required');
        }
        if ($admission_no === '') {
            throw new Exception('Admission number is required');
        }

        // Resolve student and class for this session
        $stmtStu = $db->prepare('SELECT id, admission_no, first_name, last_name FROM school_students WHERE school_id = :sid AND admission_no = :adno LIMIT 1');
        $stmtStu->execute(['sid' => $school_id, 'adno' => $admission_no]);
        $student = $stmtStu->fetch(\PDO::FETCH_ASSOC);
        if (!$student) {
            throw new Exception('Student not found for given admission number');
        }

        $stmtEnr = $db->prepare('SELECT class_id FROM school_student_enrollments WHERE student_id = :stid AND session_id = :sess LIMIT 1');
        $stmtEnr->execute(['stid' => $student['id'], 'sess' => $session_id]);
        $enr = $stmtEnr->fetch(\PDO::FETCH_ASSOC);
        if (!$enr || empty($enr['class_id'])) {
            throw new Exception('Student is not enrolled in the selected session');
        }
        $single_class_id = (int)$enr['class_id'];

        // Determine period (list of months) based on period_type
        $period_type = trim($_POST['period_type'] ?? $_GET['period_type'] ?? 'single');
        $months = [];

        if ($period_type === 'single') {
            $month_input = trim($_POST['billing_month'] ?? $_GET['billing_month'] ?? '');
            if (!$month_input) {
                throw new Exception('Billing month is required for single-month invoices');
            }
            $months[] = date('Y-m-d', strtotime($month_input . '-01'));
        } elseif ($period_type === 'range') {
            $start_input = trim($_POST['start_month'] ?? $_GET['start_month'] ?? '');
            $end_input = trim($_POST['end_month'] ?? $_GET['end_month'] ?? '');
            if (!$start_input || !$end_input) {
                throw new Exception('Start and end month are required for range invoices');
            }
            $start = new \DateTime($start_input . '-01');
            $end = new \DateTime($end_input . '-01');
            if ($start > $end) {
                throw new Exception('Start month cannot be after end month');
            }
            $cursor = clone $start;
            while ($cursor <= $end) {
                $months[] = $cursor->format('Y-m-d');
                $cursor->modify('+1 month');
            }
        } else {
            // full_session: derive from session start/end dates; fallback to selected billing_month if missing
            $stmtSess = $db->prepare('SELECT start_date, end_date FROM school_sessions WHERE id = :sid AND school_id = :sch LIMIT 1');
            $stmtSess->execute(['sid' => $session_id, 'sch' => $school_id]);
            $sess = $stmtSess->fetch(\PDO::FETCH_ASSOC);
            if ($sess && !empty($sess['start_date']) && !empty($sess['end_date'])) {
                $start = new \DateTime(date('Y-m-01', strtotime($sess['start_date'])));
                $end = new \DateTime(date('Y-m-01', strtotime($sess['end_date'])));
                if ($start > $end) {
                    $tmp = $start; $start = $end; $end = $tmp;
                }
                $cursor = clone $start;
                while ($cursor <= $end) {
                    $months[] = $cursor->format('Y-m-d');
                    $cursor->modify('+1 month');
                }
            } else {
                if (!$billing_month) {
                    throw new Exception('Session does not have valid dates; please choose a billing month');
                }
                $months[] = $billing_month;
            }
        }

        if (empty($months)) {
            throw new Exception('No months found for selected period');
        }

        // Manual discount (applied per-month on top of concessions)
        $discount_type = trim($_POST['discount_type'] ?? $_GET['discount_type'] ?? '');
        $discount_value = (float)($_POST['discount_value'] ?? $_GET['discount_value'] ?? 0);

        if ($action === 'preview_single') {
            // Get once_per_session items from first month only
            $first_month = $months[0];
            $calc_first = calculateInvoiceAmount(
                $db, $school_id, $student['id'], $student['admission_no'],
                $session_id, $single_class_id, $first_month, $additional_fees, true
            );
            $once_per_session_items = $calc_first['once_per_session_items'] ?? [];
            $once_per_session_total = 0.0;
            foreach ($once_per_session_items as $ops_item) {
                $once_per_session_total += (float)($ops_item['amount'] ?? 0);
            }

            $rows = [];
            $grand_base_monthly = 0;
            $grand_concession = 0;
            $grand_additional = 0;
            $grand_total = 0;

            foreach ($months as $m) {
                // Calculate monthly fees only (exclude once_per_session)
                $calc = calculateInvoiceAmount(
                    $db, $school_id, $student['id'], $student['admission_no'],
                    $session_id, $single_class_id, $m, $additional_fees, false
                );

                $gross_monthly = (float)$calc['base_amount'];
                $concession = (float)($calc['concessions'] ?? 0);
                $additional = (float)($calc['additional_fees_total'] ?? 0);

                // Apply manual discount on monthly base
                $manual_discount = 0.0;
                if ($discount_type && $discount_value > 0) {
                    if ($discount_type === 'percentage') {
                        $manual_discount = ($gross_monthly * $discount_value) / 100.0;
                    } else {
                        // For fixed discount, divide by number of months
                        $manual_discount = $discount_value / count($months);
                    }
                    $manual_discount = max(0.0, min($manual_discount, $gross_monthly));
                }

                $total_concession = $concession + $manual_discount;
                
                // For first month, add once_per_session items
                $gross_total = $gross_monthly;
                if ($m === $first_month) {
                    $gross_total += $once_per_session_total;
                }
                
                $net = max(0.0, $gross_total - $total_concession + $additional);

                $rows[] = [
                    'month_label' => date('F Y', strtotime($m)),
                    'base_monthly' => $gross_monthly,
                    'base_once_per_session' => ($m === $first_month ? $once_per_session_total : 0),
                    'base_total' => $gross_total,
                    'concessions' => $total_concession,
                    'additional_fees_total' => $additional,
                    'total_amount' => $net,
                    'is_first_month' => ($m === $first_month)
                ];

                $grand_base_monthly += $gross_monthly;
                $grand_concession += $total_concession;
                $grand_additional += $additional;
                $grand_total += $net;
            }

            // Build HTML table for single-student preview
            // If multiple months, show as single consolidated invoice breakdown
            $is_multi_month = count($months) > 1;
            $period_label = $is_multi_month 
                ? (date('F Y', strtotime($months[0])) . ' to ' . date('F Y', strtotime(end($months))))
                : date('F Y', strtotime($months[0]));
            
            $html = '<h6 class="mb-3">Invoice Preview for ' . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) .
                ' (' . htmlspecialchars($student['admission_no']) . ')</h6>';
            $html .= '<p class="text-muted small mb-3">Period: <strong>' . htmlspecialchars($period_label) . '</strong> (' . count($months) . ' month' . (count($months) > 1 ? 's' : '') . ')</p>';
            
            if ($is_multi_month) {
                // Single invoice breakdown for multi-month
                $html .= '<div class="alert alert-info mb-3"><strong>Note:</strong> This will generate <strong>ONE consolidated invoice</strong> covering all months.</div>';
                
                $html .= '<div style="max-height: 500px; overflow-y: auto;">';
                $html .= '<table class="table table-sm table-striped">';
                $html .= '<thead class="thead-light"><tr><th>Description</th><th>Amount</th></tr></thead><tbody>';
                
                // Monthly fees breakdown
                foreach ($rows as $r) {
                    $html .= '<tr>';
                    $html .= '<td><small>Monthly Fees - ' . htmlspecialchars($r['month_label']) . '</small></td>';
                    $html .= '<td><small>' . number_format($r['base_monthly'], 2) . '</small></td>';
                    $html .= '</tr>';
                }
                
                // Once per session fees
                if ($once_per_session_total > 0) {
                    foreach ($once_per_session_items as $ops_item) {
                        $html .= '<tr class="table-info">';
                        $html .= '<td><small><strong>' . htmlspecialchars($ops_item['description']) . '</strong></small></td>';
                        $html .= '<td><small><strong>' . number_format($ops_item['amount'], 2) . '</strong></small></td>';
                        $html .= '</tr>';
                    }
                }
                
                // Additional fees (if any)
                if ($grand_additional > 0) {
                    $html .= '<tr class="table-warning">';
                    $html .= '<td><small><strong>Additional Fees</strong></small></td>';
                    $html .= '<td><small><strong>' . number_format($grand_additional, 2) . '</strong></small></td>';
                    $html .= '</tr>';
                }
                
                // Subtotal
                $subtotal = $grand_base_monthly + $once_per_session_total + $grand_additional;
                $html .= '<tr class="font-weight-bold">';
                $html .= '<td>Subtotal</td>';
                $html .= '<td>' . number_format($subtotal, 2) . '</td>';
                $html .= '</tr>';
                
                // Concessions
                if ($grand_concession > 0) {
                    $html .= '<tr class="text-danger font-weight-bold">';
                    $html .= '<td>Concessions & Discounts</td>';
                    $html .= '<td>-' . number_format($grand_concession, 2) . '</td>';
                    $html .= '</tr>';
                }
                
                // Total
                $html .= '<tr class="font-weight-bold bg-light">';
                $html .= '<td>TOTAL</td>';
                $html .= '<td><strong>' . number_format($grand_total, 2) . '</strong></td>';
                $html .= '</tr>';
                
                $html .= '</tbody></table></div>';
            } else {
                // Single month - show detailed breakdown
                $r = $rows[0];
                $html .= '<div style="max-height: 500px; overflow-y: auto;">';
                $html .= '<table class="table table-sm table-striped">';
                $html .= '<thead class="thead-light"><tr><th>Description</th><th>Amount</th></tr></thead><tbody>';
                $html .= '<tr><td><small>Monthly Fees - ' . htmlspecialchars($r['month_label']) . '</small></td><td><small>' . number_format($r['base_monthly'], 2) . '</small></td></tr>';
                if ($r['base_once_per_session'] > 0) {
                    foreach ($once_per_session_items as $ops_item) {
                        $html .= '<tr class="table-info"><td><small><strong>' . htmlspecialchars($ops_item['description']) . '</strong></small></td><td><small><strong>' . number_format($ops_item['amount'], 2) . '</strong></small></td></tr>';
                    }
                }
                if ($r['additional_fees_total'] > 0) {
                    $html .= '<tr class="table-warning"><td><small><strong>Additional Fees</strong></small></td><td><small><strong>' . number_format($r['additional_fees_total'], 2) . '</strong></small></td></tr>';
                }
                $subtotal = $r['base_total'] + $r['additional_fees_total'];
                $html .= '<tr class="font-weight-bold"><td>Subtotal</td><td>' . number_format($subtotal, 2) . '</td></tr>';
                if ($r['concessions'] > 0) {
                    $html .= '<tr class="text-danger font-weight-bold"><td>Concessions & Discounts</td><td>-' . number_format($r['concessions'], 2) . '</td></tr>';
                }
                $html .= '<tr class="font-weight-bold bg-light"><td>TOTAL</td><td><strong>' . number_format($r['total_amount'], 2) . '</strong></td></tr>';
                $html .= '</tbody></table></div>';
            }

            echo json_encode([
                'success' => true,
                'message' => 'Single-student preview generated successfully',
                'preview_html' => $html
            ]);
            ob_end_flush();
            exit;
        }

        // generate_single
        if ($action === 'generate_single') {
            $db->beginTransaction();
            $invoice_count = 0;
            $errors = [];
            
            $is_multi_month = count($months) > 1;
            $first_month = $months[0];
            
            // Get once_per_session items from first month only
            $calc_first = calculateInvoiceAmount(
                $db, $school_id, $student['id'], $student['admission_no'],
                $session_id, $single_class_id, $first_month, $additional_fees, true
            );
            $once_per_session_items = $calc_first['once_per_session_items'] ?? [];
            $once_per_session_total = 0.0;
            foreach ($once_per_session_items as $ops_item) {
                $once_per_session_total += (float)($ops_item['amount'] ?? 0);
            }

            if ($is_multi_month) {
                // Create ONE consolidated invoice for all months
                try {
                    // Check if consolidated invoice already exists (check by first month)
                    $stmtCheck = $db->prepare('
                        SELECT id FROM schoo_fee_invoices
                        WHERE school_id = :sid AND student_id = :stid AND session_id = :ssid AND billing_month = :bm
                        LIMIT 1
                    ');
                    $stmtCheck->execute([
                        'sid' => $school_id,
                        'stid' => $student['id'],
                        'ssid' => $session_id,
                        'bm' => $first_month
                    ]);
                    if ($stmtCheck->rowCount() > 0) {
                        throw new Exception('An invoice already exists for this period');
                    }

                    // Collect all monthly fees and calculate totals
                    $all_fee_items = [];
                    $total_gross_monthly = 0.0;
                    $total_concession_auto = 0.0;
                    $total_additional = 0.0;

                    foreach ($months as $m) {
                        // Calculate monthly fees only (exclude once_per_session)
                        $calc = calculateInvoiceAmount(
                            $db, $school_id, $student['id'], $student['admission_no'],
                            $session_id, $single_class_id, $m, $additional_fees, false
                        );

                        $gross_monthly = (float)$calc['base_amount'];
                        $concession_auto = (float)($calc['concessions'] ?? 0);
                        $additional = (float)($calc['additional_fees_total'] ?? 0);

                        $total_gross_monthly += $gross_monthly;
                        $total_concession_auto += $concession_auto;
                        $total_additional += $additional;

                        // Add monthly fee items with month label
                        foreach ($calc['fee_items'] as $item) {
                            $month_label = date('F Y', strtotime($m));
                            $all_fee_items[] = [
                                'fee_item_id' => intval($item['fee_item_id'] ?? 0),
                                'description' => $item['description'] . ' - ' . $month_label,
                                'amount' => (float)($item['amount'] ?? 0)
                            ];
                        }
                    }

                    // Add once_per_session items
                    foreach ($once_per_session_items as $ops_item) {
                        $all_fee_items[] = [
                            'fee_item_id' => intval($ops_item['fee_item_id'] ?? 0),
                            'description' => $ops_item['description'],
                            'amount' => (float)($ops_item['amount'] ?? 0)
                        ];
                    }

                    $gross_total = $total_gross_monthly + $once_per_session_total + $total_additional;

                    // Apply manual discount on total
                    $manual_discount = 0.0;
                    if ($discount_type && $discount_value > 0) {
                        if ($discount_type === 'percentage') {
                            $manual_discount = ($gross_total * $discount_value) / 100.0;
                        } else {
                            $manual_discount = $discount_value;
                        }
                        $manual_discount = max(0.0, min($manual_discount, $gross_total));
                    }

                    $total_concession = $total_concession_auto + $manual_discount;
                    $net = max(0, $gross_total - $total_concession);

                    // Add manual discount as line item if present
                    if ($manual_discount > 0) {
                        $all_fee_items[] = [
                            'fee_item_id' => 0,
                            'description' => 'Manual Discount',
                            'amount' => -1 * $manual_discount
                        ];
                    }

                    // Add concessions line item if any
                    if ($total_concession_auto > 0) {
                        $all_fee_items[] = [
                            'fee_item_id' => 0,
                            'description' => 'Concessions / Scholarships',
                            'amount' => -1 * $total_concession_auto
                        ];
                    }

                    if ($gross_total <= 0 && empty($all_fee_items)) {
                        throw new Exception('No fees found for this student/class.');
                    }

                    $invoice_no = getNextInvoiceNumber($db, $school_id, $session_id);
                    $period_label = date('F Y', strtotime($first_month)) . ' to ' . date('F Y', strtotime(end($months)));

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
                        'bm' => $first_month, // Use first month as billing_month
                        'gross' => $gross_total,
                        'concession' => $total_concession,
                        'net' => $net,
                        'total' => $net,
                        'status' => 'draft',
                        'due' => $due_date
                    ]);

                    $invoice_id = $db->lastInsertId();

                    // Insert all line items
                    foreach ($all_fee_items as $item) {
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

                    $invoice_count = 1;
                } catch (Exception $e) {
                    error_log('Consolidated invoice generation error for ' . $student['admission_no'] . ': ' . $e->getMessage());
                    $errors[] = $e->getMessage();
                }
            } else {
                // Single month - create one invoice as before
                $m = $months[0];
                try {
                    // Skip if invoice already exists
                    $stmtCheck = $db->prepare('
                        SELECT id FROM schoo_fee_invoices
                        WHERE school_id = :sid AND student_id = :stid AND session_id = :ssid AND billing_month = :bm
                        LIMIT 1
                    ');
                    $stmtCheck->execute([
                        'sid' => $school_id,
                        'stid' => $student['id'],
                        'ssid' => $session_id,
                        'bm' => $m
                    ]);
                    if ($stmtCheck->rowCount() > 0) {
                        throw new Exception('Invoice already exists for ' . date('F Y', strtotime($m)));
                    }

                    // Calculate with once_per_session included
                    $calc = calculateInvoiceAmount(
                        $db, $school_id, $student['id'], $student['admission_no'],
                        $session_id, $single_class_id, $m, $additional_fees, true
                    );

                    $gross_total = (float)$calc['base_amount'];
                    $concession_auto = (float)($calc['concessions'] ?? 0);
                    $additional = (float)($calc['additional_fees_total'] ?? 0);

                    if ($gross_total <= 0 && empty($calc['fee_items'])) {
                        throw new Exception('No fees found for this student/class.');
                    }

                    // Apply manual discount
                    $manual_discount = 0.0;
                    if ($discount_type && $discount_value > 0) {
                        if ($discount_type === 'percentage') {
                            $manual_discount = ($gross_total * $discount_value) / 100.0;
                        } else {
                            $manual_discount = $discount_value;
                        }
                        $manual_discount = max(0.0, min($manual_discount, $gross_total));
                    }

                    $total_concession = $concession_auto + $manual_discount;
                    $net = max(0, $gross_total - $total_concession + $additional);

                    // Add manual discount as line item if present
                    if ($manual_discount > 0) {
                        $calc['fee_items'][] = [
                            'fee_item_id' => 0,
                            'description' => 'Manual Discount',
                            'amount' => -1 * $manual_discount
                        ];
                    }

                    $invoice_no = getNextInvoiceNumber($db, $school_id, $session_id);

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
                        'bm' => $m,
                        'gross' => $gross_total,
                        'concession' => $total_concession,
                        'net' => $net,
                        'total' => $net,
                        'status' => 'draft',
                        'due' => $due_date
                    ]);

                    $invoice_id = $db->lastInsertId();

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

                    $invoice_count = 1;
                } catch (Exception $e) {
                    error_log('Single invoice generation error for ' . $student['admission_no'] . ' month ' . $m . ': ' . $e->getMessage());
                    $errors[] = date('F Y', strtotime($m)) . ' - ' . $e->getMessage();
                }
            }

            $db->commit();

            if ($invoice_count > 0) {
                $period_info = $is_multi_month 
                    ? ' (consolidated invoice for ' . count($months) . ' months)'
                    : '';
                echo json_encode([
                    'success' => true,
                    'message' => 'Generated ' . $invoice_count . ' consolidated invoice' . ($invoice_count > 1 ? 's' : '') . ' for student ' . $student['admission_no'] . $period_info,
                    'errors' => $errors
                ]);
            } else {
                $error_details = !empty($errors) ? implode('; ', $errors) : 'No invoices generated for this student.';
                throw new Exception($error_details);
            }

            ob_end_flush();
            exit;
        }
    }

    // Determine months for bulk invoices based on period_type
    $bulk_months = [];
    if (!in_array($action, ['diagnose_concession', 'debug', 'preview_single', 'generate_single'], true)) {
        if ($bulk_period_type === 'single') {
            $bulk_months[] = $billing_month;
        } elseif ($bulk_period_type === 'range') {
            $start_input = trim($_POST['start_month'] ?? $_GET['start_month'] ?? '');
            $end_input = trim($_POST['end_month'] ?? $_GET['end_month'] ?? '');
            $start = new \DateTime($start_input . '-01');
            $end = new \DateTime($end_input . '-01');
            if ($start > $end) {
                throw new Exception('Start month cannot be after end month');
            }
            $cursor = clone $start;
            while ($cursor <= $end) {
                $bulk_months[] = $cursor->format('Y-m-d');
                $cursor->modify('+1 month');
            }
        } else {
            // full_session: derive from session start/end dates
            $stmtSess = $db->prepare('SELECT start_date, end_date FROM school_sessions WHERE id = :sid AND school_id = :sch LIMIT 1');
            $stmtSess->execute(['sid' => $session_id, 'sch' => $school_id]);
            $sess = $stmtSess->fetch(\PDO::FETCH_ASSOC);
            if ($sess && !empty($sess['start_date']) && !empty($sess['end_date'])) {
                $start = new \DateTime(date('Y-m-01', strtotime($sess['start_date'])));
                $end = new \DateTime(date('Y-m-01', strtotime($sess['end_date'])));
                if ($start > $end) {
                    $tmp = $start; $start = $end; $end = $tmp;
                }
                $cursor = clone $start;
                while ($cursor <= $end) {
                    $bulk_months[] = $cursor->format('Y-m-d');
                    $cursor->modify('+1 month');
                }
            } else {
                throw new Exception('Session does not have valid dates for full session invoicing');
            }
        }
        
        if (empty($bulk_months)) {
            throw new Exception('No months found for selected period');
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
        $is_multi_month = count($bulk_months) > 1;
        $preview_data = [];
        
        foreach ($students as $student) {
            if ($is_multi_month) {
                // Multi-month: calculate consolidated totals
                $first_month = $bulk_months[0];
                
                // Get once_per_session items from first month
                $calc_first = calculateInvoiceAmount(
                    $db, $school_id, $student['id'], $student['admission_no'],
                    $session_id, $student['class_id'], $first_month, $additional_fees, true
                );
                $once_per_session_items = $calc_first['once_per_session_items'] ?? [];
                $once_per_session_total = 0.0;
                foreach ($once_per_session_items as $ops_item) {
                    $once_per_session_total += (float)($ops_item['amount'] ?? 0);
                }
                
                $total_base_monthly = 0.0;
                $total_concession = 0.0;
                $total_additional = 0.0;
                
                foreach ($bulk_months as $m) {
                    $calc = calculateInvoiceAmount(
                        $db, $school_id, $student['id'], $student['admission_no'],
                        $session_id, $student['class_id'], $m, $additional_fees, false
                    );
                    $total_base_monthly += (float)$calc['base_amount'];
                    $total_concession += (float)($calc['concessions'] ?? 0);
                    $total_additional += (float)($calc['additional_fees_total'] ?? 0);
                }
                
                $total_base = $total_base_monthly + $once_per_session_total;
                $total_amount = $total_base - $total_concession + $total_additional;
                
                $preview_data[] = [
                    'name' => $student['first_name'] . ' ' . $student['last_name'],
                    'admission_no' => $student['admission_no'],
                    'class_id' => $student['class_id'],
                    'base_amount' => $total_base,
                    'concessions' => $total_concession,
                    'additional_fees_total' => $total_additional,
                    'total_amount' => $total_amount,
                    'fee_items' => []
                ];
            } else {
                // Single month
                $calc = calculateInvoiceAmount(
                    $db, $school_id, $student['id'], $student['admission_no'],
                    $session_id, $student['class_id'], $bulk_months[0], $additional_fees
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
        }

        // Build preview HTML
        $period_label = $is_multi_month 
            ? (date('F Y', strtotime($bulk_months[0])) . ' to ' . date('F Y', strtotime(end($bulk_months))))
            : date('F Y', strtotime($bulk_months[0]));
        $preview_html = buildPreviewHTML($preview_data, $bulk_months[0], $period_label, $is_multi_month);

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
        $skipped_students = [];
        $is_multi_month = count($bulk_months) > 1;
        $first_month = $bulk_months[0] ?? $billing_month;

        // Pre-fetch existing invoices for all students and months (for smart duplicate prevention)
        $student_ids = array_map(function($s) { return $s['id']; }, $students);
        $existing_invoices = [];
        if (!empty($student_ids) && !empty($bulk_months)) {
            $placeholders_students = implode(',', array_fill(0, count($student_ids), '?'));
            $placeholders_months = implode(',', array_fill(0, count($bulk_months), '?'));
            $stmtCheckExisting = $db->prepare('
                SELECT student_id, billing_month FROM schoo_fee_invoices
                WHERE school_id = ? AND session_id = ?
                AND student_id IN (' . $placeholders_students . ')
                AND billing_month IN (' . $placeholders_months . ')
            ');
            $params = array_merge([$school_id, $session_id], $student_ids, $bulk_months);
            $stmtCheckExisting->execute($params);
            while ($row = $stmtCheckExisting->fetch(\PDO::FETCH_ASSOC)) {
                $key = $row['student_id'] . '_' . $row['billing_month'];
                $existing_invoices[$key] = true;
            }
        }

        foreach ($students as $student) {
            try {
                if ($is_multi_month) {
                    // Multi-month: create ONE consolidated invoice per student
                    // Check if consolidated invoice already exists (check by first month)
                    $check_key = $student['id'] . '_' . $first_month;
                    if (isset($existing_invoices[$check_key])) {
                        $skipped_students[] = $student['admission_no'] . ' (invoice exists for ' . date('F Y', strtotime($first_month)) . ')';
                        continue;
                    }

                    // Get once_per_session items from first month
                    $calc_first = calculateInvoiceAmount(
                        $db, $school_id, $student['id'], $student['admission_no'],
                        $session_id, $student['class_id'], $first_month, $additional_fees, true
                    );
                    $once_per_session_items = $calc_first['once_per_session_items'] ?? [];
                    $once_per_session_total = 0.0;
                    foreach ($once_per_session_items as $ops_item) {
                        $once_per_session_total += (float)($ops_item['amount'] ?? 0);
                    }

                    // Collect all monthly fees
                    $all_fee_items = [];
                    $total_gross_monthly = 0.0;
                    $total_concession_auto = 0.0;
                    $total_additional = 0.0;

                    foreach ($bulk_months as $m) {
                        // Smart duplicate prevention: skip this month if invoice already exists
                        $check_key = $student['id'] . '_' . $m;
                        if (isset($existing_invoices[$check_key])) {
                            continue; // Skip this month, but continue with others
                        }

                        $calc = calculateInvoiceAmount(
                            $db, $school_id, $student['id'], $student['admission_no'],
                            $session_id, $student['class_id'], $m, $additional_fees, false
                        );

                        $gross_monthly = (float)$calc['base_amount'];
                        $concession_auto = (float)($calc['concessions'] ?? 0);
                        $additional = (float)($calc['additional_fees_total'] ?? 0);

                        $total_gross_monthly += $gross_monthly;
                        $total_concession_auto += $concession_auto;
                        $total_additional += $additional;

                        // Add monthly fee items with month label
                        foreach ($calc['fee_items'] as $item) {
                            $month_label = date('F Y', strtotime($m));
                            $all_fee_items[] = [
                                'fee_item_id' => intval($item['fee_item_id'] ?? 0),
                                'description' => $item['description'] . ' - ' . $month_label,
                                'amount' => (float)($item['amount'] ?? 0)
                            ];
                        }
                    }

                    // Add once_per_session items
                    foreach ($once_per_session_items as $ops_item) {
                        $all_fee_items[] = [
                            'fee_item_id' => intval($ops_item['fee_item_id'] ?? 0),
                            'description' => $ops_item['description'],
                            'amount' => (float)($ops_item['amount'] ?? 0)
                        ];
                    }

                    $gross_total = $total_gross_monthly + $once_per_session_total + $total_additional;

                    if ($gross_total <= 0 && empty($all_fee_items)) {
                        $errors[] = $student['admission_no'] . ' - No fees found for class.';
                        continue;
                    }

                    // Add concessions line item
                    if ($total_concession_auto > 0) {
                        $all_fee_items[] = [
                            'fee_item_id' => 0,
                            'description' => 'Concessions / Scholarships',
                            'amount' => -1 * $total_concession_auto
                        ];
                    }

                    $total_concession = $total_concession_auto;
                    $net = max(0, $gross_total - $total_concession);

                    $invoice_no = getNextInvoiceNumber($db, $school_id, $session_id);

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
                        'bm' => $first_month,
                        'gross' => $gross_total,
                        'concession' => $total_concession,
                        'net' => $net,
                        'total' => $net,
                        'status' => 'draft',
                        'due' => $due_date
                    ]);

                    $invoice_id = $db->lastInsertId();

                    foreach ($all_fee_items as $item) {
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
                } else {
                    // Single month: original logic with smart duplicate prevention
                    $check_key = $student['id'] . '_' . $bulk_months[0];
                    if (isset($existing_invoices[$check_key])) {
                        $skipped_students[] = $student['admission_no'] . ' (invoice exists for ' . date('F Y', strtotime($bulk_months[0])) . ')';
                        continue; // Skip this student for this month only
                    }

                    // Calculate invoice amount
                    $calc = calculateInvoiceAmount(
                        $db, $school_id, $student['id'], $student['admission_no'],
                        $session_id, $student['class_id'], $bulk_months[0], $additional_fees
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
                            'bm' => $bulk_months[0],
                            'gross' => $gross,
                            'concession' => $concession,
                            'net' => $net,
                            'total' => $net,
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
                                'bm' => $bulk_months[0],
                                'total' => $net,
                            'status' => 'draft',
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
                }

            } catch (Exception $e) {
                error_log('Invoice generation error for ' . $student['admission_no'] . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                $errors[] = $student['admission_no'] . ' - ' . $e->getMessage();
            }
        }

        $db->commit();

        if ($invoice_count > 0) {
            $message = 'Generated ' . $invoice_count . ' invoice' . ($invoice_count > 1 ? 's' : '') . ' successfully';
            if (!empty($skipped_students)) {
                $message .= '. Skipped ' . count($skipped_students) . ' student(s) with existing invoices for this period.';
            }
            echo json_encode([
                'success' => true,
                'message' => $message,
                'invoice_count' => $invoice_count,
                'skipped_students' => $skipped_students,
                'errors' => $errors
            ]);
        } else {
            $error_details = !empty($errors) ? implode('; ', $errors) : 'No students processed. Check if fee assignments exist for the selected class/session.';
            if (!empty($skipped_students)) {
                $error_details .= ' All students already have invoices for this period.';
            }
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
 * @param bool $include_once_per_session If false, excludes fees with billing_cycle='once_per_session' (for multi-month invoices)
 */
function calculateInvoiceAmount($db, $school_id, $student_id, $admission_no, $session_id, $class_id, $billing_month, $additional_fees = [], $include_once_per_session = true) {
    $result = [
        'base_amount' => 0,
        'concessions' => 0,
        'additional_fees_total' => 0,
        'total_amount' => 0,
        'fee_items' => [],
        'once_per_session_items' => [] // Separate list for once_per_session fees
    ];

    try {
        // 1. Get fee structure from schoo_fee_assignments, join schoo_fee_items to obtain names/amounts and billing_cycle
        $stmtFeeAssignment = $db->prepare('
            SELECT a.id, a.fee_item_id, a.amount AS assignment_amount, 
                   fi.name AS fee_name, fi.amount AS item_amount, fi.category_id AS fee_category_id, 
                   fc.name AS fee_category_name, fi.billing_cycle
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
        error_log("[InvoiceCalc] student_id={$student_id} class_id={$class_id} session_id={$session_id} include_once_per_session={$include_once_per_session} fee_assignments=" . json_encode($fee_assignments));

        // Add base fees to result (use assignment amount if present, otherwise fall back to item amount)
        foreach ($fee_assignments as $fa) {
            $billing_cycle = strtolower(trim($fa['billing_cycle'] ?? 'monthly'));
            
            // Skip once_per_session items if not including them
            if (!$include_once_per_session && $billing_cycle === 'once_per_session') {
                continue;
            }

            $amount = 0.0;
            if (isset($fa['assignment_amount']) && $fa['assignment_amount'] !== null && $fa['assignment_amount'] !== '') {
                $amount = (float)$fa['assignment_amount'];
            } elseif (isset($fa['item_amount'])) {
                $amount = (float)$fa['item_amount'];
            }

            $desc = $fa['fee_name'] ?? 'Base Fee';
            
            // Mark once_per_session items with a suffix
            if ($billing_cycle === 'once_per_session') {
                $desc .= ' (Once per Session)';
            }

            $result['base_amount'] += $amount;
            
            $item_data = [
                'fee_item_id' => intval($fa['fee_item_id'] ?? 0),
                'description' => $desc,
                'amount' => $amount,
                'category_id' => intval($fa['category_id'] ?? 0),
                'category_name' => $fa['fee_category_name'] ?? '',
                'billing_cycle' => $billing_cycle
            ];
            
            $result['fee_items'][] = $item_data;
            
            // Also track once_per_session items separately
            if ($billing_cycle === 'once_per_session') {
                $result['once_per_session_items'][] = $item_data;
            }
        }

        // 2. Check for concessions/scholarships in school_student_fees_concessions (by admission_no)
        // NOTE: We currently treat all active concession rows for a student as applicable,
        // without filtering by start/end month (simpler + avoids date-format issues).
        // $month_start is kept only for logging/debug readability.
        $month_start = $billing_month ? date('Y-m-01', strtotime($billing_month)) : null;
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
            error_log('[DIAG_CONCESSION_SEARCH] Params: ' . json_encode(['sid' => $school_id, 'adno' => $admission_no]));
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
            error_log("[InvoiceCalc] NO CONCESSION FOUND: admission_no={$admission_no} school_id={$school_id} month_start=" . ($month_start ?: 'n/a'));
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
function buildPreviewHTML($preview_data, $billing_month, $period_label = null, $is_multi_month = false) {
    // $billing_month may now be a full date (YYYY-MM-01), so parse directly
    $month_name = $period_label ? $period_label : date('F Y', strtotime($billing_month));
    
    $html = '<h6 class="mb-3">Students to be invoiced for ' . htmlspecialchars($month_name) . '</h6>';
    if ($is_multi_month) {
        $html .= '<div class="alert alert-info mb-3"><strong>Note:</strong> This will generate <strong>consolidated invoices</strong> (one per student) covering all months in the selected period.</div>';
    }
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
