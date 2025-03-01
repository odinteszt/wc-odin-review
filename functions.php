<?php
if (!class_exists('WooCommerce')) {
    return; 
}
add_action('plugins_loaded', function() {
    if (!function_exists('wc_get_orders')) {
        return;
    }
});
function custom_enqueue_scripts() {
    wp_enqueue_script('jquery');

    // Ellenőrző üzenet az error_log-ba
    error_log("Betöltődik a custom_enqueue_scripts");

    wp_enqueue_script(
        'product-search',
        plugins_url('js/product-search.js', __FILE__),
        array('jquery'),
        null,
        true
    );
    wp_localize_script('product-search', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'custom_enqueue_scripts');
add_action('admin_enqueue_scripts', 'custom_enqueue_scripts');
function rendelesek_lekerdezese_utolso_10_nap() {
    $date_10_days_ago = date('Y-m-d H:i:s', strtotime('-10 days'));
    $orders = wc_get_orders([
        'limit'        => -1, // Összes releváns rendelés lekérése
        'date_created' => '>' . $date_10_days_ago,
        'orderby'      => 'date',
        'order'        => 'DESC',
    ]);
    $order_data = [];
    foreach ($orders as $order) {
        $order_id = $order->get_id();
        $customer_email = $order->get_billing_email();
        $customer_name = $order->get_billing_last_name() . ' ' . $order->get_billing_first_name();
        $customer_first_name = $order->get_billing_first_name();
        $customer_last_name = $order->get_billing_last_name();
        $status = $order->get_status();
        
        $product_ids = [];
        foreach ($order->get_items() as $item) {
            $product_ids[] = $item->get_product_id();
        }
        $status_change_time = get_post_meta($order_id, '_status_changed', true);
        $days_since_status_change = 0;  // Alapértelmezetten 0-ra állítjuk
        if ($status_change_time && $status == 'completed') {
            $status_change_date = new DateTime($status_change_time);
            $current_date = new DateTime();
            $interval = $status_change_date->diff($current_date);
            $days_since_status_change = $interval->days;  // Az eltelt napok számát adja vissza
        }
        $order_data[] = [
            'order_id'                => $order_id,
            'product_ids'             => $product_ids,
            'customer_email'          => $customer_email,
            'customer_name'           => $customer_name,
            'status'                  => $status,
            'days_since_status_change' => $days_since_status_change,  // A státuszváltás napjai
        ];
    }
    return $order_data;
}
function get_meta_value_by_key($meta_key) {
    global $wpdb;
    $result = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT value FROM {$wpdb->prefix}wc_odin_review_options WHERE meta_key = %s LIMIT 1",
            $meta_key
        )
    );
    return $result ? $result : false;
}
function delete_meta_value_by_key($meta_key) {
    global $wpdb;
    $result = $wpdb->delete(
        "{$wpdb->prefix}wc_odin_review_options",
        array('meta_key' => $meta_key),
        array('%s')
    );
    return $result !== false; // Sikeres törlés esetén true-t ad vissza
}
function update_meta_value_by_key($meta_key, $new_value) {
    global $wpdb;
    $result = $wpdb->update(
        "{$wpdb->prefix}wc_odin_review_options",
        array('value' => $new_value),
        array('meta_key' => $meta_key),
        array('%s'),
        array('%s')
    );
    return $result !== false; // Sikeres frissítés esetén true-t ad vissza
}
function add_review_to_database(
    $rendeles_id, 
    $termek_id, 
    $keresztnev, 
    $szoveges_ertekeles, 
    $csillag_ertekeles, 
    $elfogadva = 'pending', 
    $van_szoveges_ertekeles = 'no'
) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wc_odin_review_ertekelesek';
    $data = array(
        'rendeles_id' => $rendeles_id,
        'termek_id' => $termek_id,
        'keresztnev' => $keresztnev,
        'szoveges_ertekeles' => $szoveges_ertekeles,
        'csillag_ertekeles' => $csillag_ertekeles,
        'elfogadva' => $elfogadva,
        'van_szoveges_ertekeles' => $van_szoveges_ertekeles
    );
    $format = array(
        '%d', // rendeles_id
        '%d', // termek_id
        '%s', // keresztnev
        '%s', // szoveges_ertekeles
        '%d', // csillag_ertekeles
        '%s', // elfogadva
        '%s'  // van_szoveges_ertekeles
    );
    $wpdb->insert($table_name, $data, $format);
    if ($wpdb->insert_id) {
        return $wpdb->insert_id; // Visszaadja a beszúrt sor ID-ját
    } else {
        return false; // Hiba esetén false-t ad vissza
    }
}
function check_if_product_exists_in_ertekelendo_termekek($termek_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wc_odin_review_ertekelendo_termekek';
    $product_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE termek_id = %d",
            $termek_id
        )
    );
    return $product_exists > 0;
}
function add_product_to_ertekelendo_termekek(
    $termek_id,
    $ossz_ertekeles = 0,
    $atlag = 0,
    $ertekeles_datab = 0,
    $egycsillag = 0,
    $ketcsillag = 0,
    $haromcsillag = 0,
    $negycsillag = 0,
    $otcsillag = 0
) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wc_odin_review_ertekelendo_termekek';
    $data = array(
        'termek_id' => $termek_id,
        'ossz_ertekeles' => $ossz_ertekeles,
        'atlag' => $atlag,
        'ertekeles_datab' => $ertekeles_datab,
        'egycsillag' => $egycsillag,
        'ketcsillag' => $ketcsillag,
        'haromcsillag' => $haromcsillag,
        'negycsillag' => $negycsillag,
        'otcsillag' => $otcsillag
    );
    $format = array(
        '%d', // termek_id
        '%d', // ossz_ertekeles
        '%f', // atlag
        '%d', // ertekeles_datab
        '%d', // egycsillag
        '%d', // ketcsillag
        '%d', // haromcsillag
        '%d', // negycsillag
        '%d'  // otcsillag
    );
    $wpdb->insert($table_name, $data, $format);
    if ($wpdb->insert_id) {
        return $wpdb->insert_id; // Visszaadja a beszúrt sor ID-ját
    } else {
        return false; // Hiba esetén false-t ad vissza
    }
}
function addblankertekelendotermek($termek_id){
	$result = add_product_to_ertekelendo_termekek(
    $termek_id,
		0,      // ossz_ertekeles
		0,     // atlag
		0,       // ertekeles_datab
		0,       // egycsillag
		0,       // ketcsillag
		0,       // haromcsillag
		0,       // negycsillag
		0        // otcsillag
	);
	if ($result){} 
}
$rendelesek=rendelesek_lekerdezese_utolso_10_nap();
$adminmailcim=get_meta_value_by_key("admin_mail_cim");
$welcomeuzenet=get_meta_value_by_key("vasarlo_email_form");
function add_review_callback() {
    global $wpdb;
    if (isset($_POST['termek_id'], $_POST['keresztnev'], $_POST['szoveges_ertekeles'], $_POST['csillag_ertekeles'])) {
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
        $table_name = $wpdb->prefix . 'wc_odin_review_ertekelesek';
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
            wp_send_json_success(['message' => 'Értékelés sikeresen mentve.']);
        } else {
            wp_send_json_error([
                'message' => 'Hiba történt az adatbázis mentésekor.',
                'error' => $wpdb->last_error // Hibaüzenet
            ]);
        }
    } else {
        wp_send_json_error(['message' => 'Hiányzó adatokat találtunk.']);
    }
    wp_die(); // WordPress megköveteli, hogy minden AJAX kérés végén meghívjuk a wp_die() funkciót
}
add_action('wp_ajax_add_review', 'add_review_callback'); // Bejelentkezett adminisztrátorok számára
?>
