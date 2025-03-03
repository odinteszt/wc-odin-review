<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// WordPress környezet betöltése
require_once('../../../../wp-load.php'); 

global $wpdb;
$table_name = $wpdb->prefix . 'wc_odin_review_ertekelesek';
$table_name_stats = $wpdb->prefix . 'wc_odin_review_ertekelendo_termekek';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['termek_id'])) {
    $rendeles_id = isset($_POST['rendeles_id']) ? intval($_POST['rendeles_id']) : 0;
    $termek_id = intval($_POST['termek_id']);
    $keresztnev = sanitize_text_field($_POST['keresztnev']);
    $szoveges_ertekeles = sanitize_textarea_field($_POST['szoveges_ertekeles']);
    $csillag_ertekeles = intval($_POST['csillag_ertekeles']);
    $elfogadva = 'pending'; // Alapértelmezett érték
    $van_szoveges_ertekeles = !empty($szoveges_ertekeles) ? 'yes' : 'no';
    
    if (!check_if_product_exists_in_ertekelendo_termekek($termek_id)) {
        addblankertekelendotermek($termek_id);
    }

    // Próbáljuk meg beilleszteni az adatokat az adatbázisba
    $inserted = $wpdb->insert(
        $table_name,
        [
            'rendeles_id' => $rendeles_id,
            'termek_id' => $termek_id,
            'keresztnev' => $keresztnev,
            'szoveges_ertekeles' => $szoveges_ertekeles,
            'csillag_ertekeles' => $csillag_ertekeles,
            'elfogadva' => $elfogadva,
            'van_szoveges_ertekeles' => $van_szoveges_ertekeles,
        ],
        ['%d', '%d', '%s', '%s', '%d', '%s', '%s']
    );

    if ($inserted) {
        // Frissítjük a statisztikai adatokat
        $stats = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name_stats WHERE termek_id = %d", $termek_id)
        );

        if ($stats) {
            // Növeljük a megfelelő csillagértékelést a statisztikákban
            $update_data = [
                'ossz_ertekeles' => $stats->ossz_ertekeles + 1, // Növeljük az összes értékelést
            ];

            if ($csillag_ertekeles == 1) {
                $update_data['egycsillag'] = $stats->egycsillag + 1;
            } elseif ($csillag_ertekeles == 2) {
                $update_data['ketcsillag'] = $stats->ketcsillag + 1;
            } elseif ($csillag_ertekeles == 3) {
                $update_data['haromcsillag'] = $stats->haromcsillag + 1;
            } elseif ($csillag_ertekeles == 4) {
                $update_data['negycsillag'] = $stats->negycsillag + 1;
            } elseif ($csillag_ertekeles == 5) {
                $update_data['otcsillag'] = $stats->otcsillag + 1;
            }

            // Frissítjük a statisztikai adatokat
            $wpdb->update(
                $table_name_stats,
                $update_data,
                ['termek_id' => $termek_id],
                array_fill(0, count($update_data), '%d'),
                ['%d']
            );

            // Frissítjük az átlagos csillagértékelést
            $new_stats = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_name_stats WHERE termek_id = %d", $termek_id)
            );

            if ($new_stats) {
                // Összes csillag értékelés számítása
                $total_stars = ($new_stats->egycsillag * 1) + ($new_stats->ketcsillag * 2) + ($new_stats->haromcsillag * 3) + ($new_stats->negycsillag * 4) + ($new_stats->otcsillag * 5);

                // Ha van értékelés, akkor kiszámoljuk az átlagot, egyébként 0-t adunk
                if ($new_stats->ossz_ertekeles > 0) {
                    $average_rating = round($total_stars / $new_stats->ossz_ertekeles, 2);
                } else {
                    $average_rating = 0;
                }

                // Frissítjük az átlagot
                $wpdb->update(
                    $table_name_stats,
                    ['atlag' => $average_rating],
                    ['termek_id' => $termek_id],
                    ['%f'],
                    ['%d']
                );
            }
        }

        echo json_encode(['success' => true, 'message' => 'Értékelés sikeresen mentve és statisztikai adatok frissítve.']);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Hiba történt az adatbázis mentésekor.', 
            'error' => $wpdb->last_error // Hibaüzenet megjelenítése
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen kérés.']);
}

exit;
