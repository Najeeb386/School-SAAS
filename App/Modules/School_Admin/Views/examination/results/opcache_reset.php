<?php
header('Content-Type: application/json; charset=utf-8');
$result = null;
if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    echo json_encode(['success' => true, 'opcache_reset' => $result]);
} else {
    echo json_encode(['success' => false, 'error' => 'opcache_reset not available']);
}
?>