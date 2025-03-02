<?php
require_once('../../../../wp-load.php');
global $wpdb;

$id = $_POST['id'];
$send_coupon = $_POST['send_coupon'] === 'true' ? 'yes' : 'no';

// Delete the review from the table
$table_name = $wpdb->prefix . "wc_odin_review_ertekeles_ellenorzo";
$wpdb->delete($table_name, ['id' => $id]);

// Process the send coupon option if needed
if ($send_coupon === 'yes') {
    // Add your coupon sending logic here
}

$response = ['message' => 'Értékelés elutasítva és törölve.'];
echo json_encode($response);
?>