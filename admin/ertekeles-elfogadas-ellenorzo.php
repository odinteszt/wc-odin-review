<?php
header('Content-Type: application/json');
require_once(dirname(__FILE__) . '/../../../../wp-load.php'); 

// Az oldal védelme
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Hozzáférés megtagadva');
}

global $wpdb;
$table_name_reviews = $wpdb->prefix . 'wc_odin_review_ertekeles';
$table_name_pending = $wpdb->prefix . 'wc_odin_review_ertekeles_ellenorzo';
$table_name_stats = $wpdb->prefix . 'wc_odin_review_ertekelendo_termekek';

// Ellenőrizzük, hogy van-e ID és új adat a kérésben
if (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = intval($_POST['id']);
    $send_coupon = $_POST['send_coupon'] === 'true' ? 'yes' : 'no';

    // Lekérjük az értékelést
    $review = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_name_pending WHERE id = %d", $id)
    );

    if ($review) {
        $termek_id = $review->termek_id;
        $rendeles_id = $review->rendeles_id;
        $csillag_ertekeles = $review->csillag;

        // Ha a kupon küldés be van jelölve, küldjük el a kupont
        if ($send_coupon === 'yes') {
            // Lekérjük a rendeléshez tartozó email címet
            $order = wc_get_order($rendeles_id);
            if ($order) {
                $email = $order->get_billing_email();

                // Generáljuk és küldjük el a kupont a WebToffee Coupon Manager segítségével
                $coupon_code = 'YOUR_COUPON_CODE'; // Itt generálhatod a kupont a WebToffee Coupon Manager segítségével
                $coupon = new WC_Coupon($coupon_code);
                $coupon->set_email_restrictions([$email]);
                $coupon->save();

                // Küldjük el a kupont emailben
                wp_mail($email, 'Your Coupon Code', 'Here is your coupon code: ' . $coupon_code);
            }
        }

        // Áthelyezzük az értékelést a végleges táblába
        $inserted = $wpdb->insert(
            $table_name_reviews,
            [
                'rendeles_id' => $review->rendeles_id,
                'termek_id' => $review->termek_id,
                'keresztnev' => $review->keresztnev,
                'szoveges_ertekeles' => $review->szoveges_ertekeles,
                'csillag_ertekeles' => $review->csillag,
                'datum' => current_time('mysql'),
                'statusz' => 'approved',
                'email_kuldve' => $send_coupon
            ],
            ['%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s']
        );

        if ($inserted !== false) {
            // Töröljük az értékelést az ellenőrző táblából
            $wpdb->delete($table_name_pending, ['id' => $id], ['%d']);

            // Frissítjük a statisztikai adatokat
            $stats = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_name_stats WHERE termek_id = %d", $termek_id)
            );

            if ($stats) {
                // Növeljük az új csillagértékelés számát
                $update_data = [];
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
                $new_ossz_ertekeles = $stats->ossz_ertekeles + 1;
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

            echo json_encode(['success' => true, 'message' => 'Értékelés sikeresen elfogadva és feldolgozva.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Hiba történt az értékelés áthelyezésekor.', 'error' => $wpdb->last_error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Nem található ilyen értékelés.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen kérés vagy hiányzó adat.']);
}

exit;