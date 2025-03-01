<?php

require_once('../../../../wp-load.php'); // Betöltjük a WordPress alapfájljait

global $wpdb;
$table_name = $wpdb->prefix . "wc_odin_review_ertekeles_ellenorzo"; // A tábla neve

// Ellenőrizzük, hogy kaptunk-e ID-t POST-ban
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Töröljük az értékelést az adatbázisból
    $result = $wpdb->delete(
        $table_name,
        array('id' => $id),
        array('%d')
    );

    // Ellenőrizzük a törlés sikerességét
    if ($result) {
        // Sikeres törlés esetén JSON választ küldünk
        wp_send_json_success(array('message' => 'Az értékelés sikeresen törölve!'));
    } else {
        // Hiba esetén JSON választ küldünk
        wp_send_json_error(array('message' => 'Hiba történt az értékelés törlése során!'));
    }
} else {
    // Ha nincs ID megadva, JSON hibát küldünk
    wp_send_json_error(array('message' => 'Hiányzó ID!'));
}

?>