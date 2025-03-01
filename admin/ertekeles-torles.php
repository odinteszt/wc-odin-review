<?php
header('Content-Type: application/json');
require_once('../../../../wp-load.php'); 
global $wpdb;

$table_name_reviews = $wpdb->prefix . 'wc_odin_review_ertekelesek';
$table_name_stats = $wpdb->prefix . 'wc_odin_review_ertekelendo_termekek';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']); // Az értékelés ID-ja, amit törölni akarunk

    // Először ellenőrizzük, hogy létezik-e az értékelés
    $review = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_name_reviews WHERE id = %d", $id)
    );

    if ($review) {
        $termek_id = $review->termek_id;
        $csillag_ertekeles = $review->csillag_ertekeles;

        // Az értékelés törlése
        $delete_result = $wpdb->delete(
            $table_name_reviews,
            ['id' => $id],
            ['%d']
        );

        if ($delete_result) {
            // A statisztikai adatok frissítése
            $stats = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_name_stats WHERE termek_id = %d", $termek_id)
            );

            if ($stats) {
                // Csökkentjük az összes értékelést
                $new_ossz_ertekeles = $stats->ossz_ertekeles - 1;

                // Csökkentjük a megfelelő csillagértékelés számát
                $update_data = [];
                if ($csillag_ertekeles == 1) {
                    $update_data['egycsillag'] = $stats->egycsillag - 1;
                } elseif ($csillag_ertekeles == 2) {
                    $update_data['ketcsillag'] = $stats->ketcsillag - 1;
                } elseif ($csillag_ertekeles == 3) {
                    $update_data['haromcsillag'] = $stats->haromcsillag - 1;
                } elseif ($csillag_ertekeles == 4) {
                    $update_data['negycsillag'] = $stats->negycsillag - 1;
                } elseif ($csillag_ertekeles == 5) {
                    $update_data['otcsillag'] = $stats->otcsillag - 1;
                }

                // Frissítjük a csillagokat és az összes értékelést
                $wpdb->update(
                    $table_name_stats,
                    array_merge($update_data, [
                        'ossz_ertekeles' => $new_ossz_ertekeles,
                    ]),
                    ['termek_id' => $termek_id],
                    array_merge(array_fill(0, count($update_data), '%d'), ['%d']),
                    ['%d']
                );

                // Frissítjük az átlagot
                $new_stats = $wpdb->get_row(
                    $wpdb->prepare("SELECT * FROM $table_name_stats WHERE termek_id = %d", $termek_id)
                );

                if ($new_stats) {
                    // Összes csillag értékelés számítása a megfelelő csillagértékelések alapján
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

            echo json_encode(['success' => true, 'message' => 'Értékelés és statisztikai adatok sikeresen törölve.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Hiba történt az értékelés törlésekor.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Nem található ilyen értékelés.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nem érvényes kérés.']);
}

exit;
