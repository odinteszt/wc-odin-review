<?php
header('Content-Type: application/json');
require_once('../../../../wp-load.php'); 

// Az oldal védelme
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Hozzáférés megtagadva');
}

global $wpdb;
$table_name = $wpdb->prefix . 'wc_odin_review_ertekeles_ellenorzo';

// Ellenőrizzük, hogy van-e ID és új adat a kérésben
if (isset($_POST['id']) && is_numeric($_POST['id']) && isset($_POST['szoveges']) && isset($_POST['csillagok'])) {
    $id = intval($_POST['id']);
    $szoveges_ertekeles = sanitize_textarea_field($_POST['szoveges']);
    $csillag_ertekeles = intval($_POST['csillagok']);
    $send_coupon = $_POST['send_coupon'] === 'true' ? 'yes' : 'no';

    // Lekérjük a termék ID-ját, hogy módosíthassuk a statisztikai adatokat
    $review = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id)
    );

    if ($review) {
        $termek_id = $review->termek_id;
        $old_csillag_ertekeles = $review->csillag;

        // Frissítjük az értékelést
        $updated = $wpdb->update(
            $table_name,
            [
                'szoveges_ertekeles' => $szoveges_ertekeles,
                'csillag' => $csillag_ertekeles,
            ],
            ['id' => $id],
            ['%s', '%d'],
            ['%d']
        );

        if ($updated !== false) {
            // Process the send coupon option if needed
            if ($send_coupon === 'yes') {
                // Add your coupon sending logic here
            }

            echo json_encode(['success' => true, 'message' => 'Értékelés sikeresen módosítva.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Hiba történt az értékelés módosításakor.', 'error' => $wpdb->last_error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Nem található ilyen értékelés.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen kérés vagy hiányzó adat.']);
}

exit;