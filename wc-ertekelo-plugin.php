<?php
/*
Plugin Name: WooCommerce Egyedi Értékelő
Plugin URI: https://example.com
Description: Egyedi WooCommerce termékértékelő plugin.
Version: 1.0
Author: Szebényi Márk
Author URI: https://example.com
License: GPL2
*/
if (!defined('ABSPATH')) {
    exit; // Kilépés, ha közvetlenül hívják meg
}
require_once plugin_dir_path(__FILE__) . 'functions.php';
class WC_Egyedi_Ertekelo {
    public function __construct() {
        // Admin menü hozzáadása
        add_action('admin_menu', array($this, 'register_admin_menu'));
        register_activation_hook(__FILE__, array($this, 'on_activation'));  // Hook hozzáadása az osztályon belül
    }

    public function register_admin_menu() {
        // Értékelések főmenü (Vezérlőpult az alapértelmezett oldal)
        add_menu_page(
            'Értékelések', 
            'Értékelések', 
            'manage_options', 
            'wc_ertekelok', 
            array($this, 'vezerlopult'), 
            'dashicons-star-filled',
            56
        );

        // Vezérlőpult (főoldal)
        add_submenu_page(
            'wc_ertekelok',
            'Vezérlőpult',
            'Vezérlőpult',
            'manage_options',
            'wc_ertekelok',
            array($this, 'vezerlopult')
        );

        // Értékelés Kezelő
        add_submenu_page(
            'wc_ertekelok',
            'Értékelés Kezelő',
            'Értékelés Kezelő',
            'manage_options',
            'wc_ertekelesek_kezelo',
            array($this, 'ertekelesek_kezelo')
        );

        // Értékelés Ellenőrző
        add_submenu_page(
            'wc_ertekelok',
            'Értékelés Ellenőrző',
            'Értékelés Ellenőrző',
            'manage_options',
            'wc_ertekelesek_ellenorzo',
            array($this, 'ertekelesek_ellenorzo')
        );

        // Beállítások
        add_submenu_page(
            'wc_ertekelok',
            'Beállítások',
            'Beállítások',
            'manage_options',
            'wc_ertekelo_beallitasok',
            array($this, 'beallitasok')
        );
    }

    public function on_activation() {
        // Táblák létrehozása (később definiáljuk pontosan)
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // SQL parancsok itt...
        
        // Táblák létrehozása
        $sql_ertekelendo_termekek = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wc_odin_review_ertekelendo_termekek (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            termek_id BIGINT UNSIGNED NOT NULL,
            ossz_ertekeles INT NOT NULL DEFAULT 0,
            atlag DECIMAL(3,2) NOT NULL DEFAULT 0,
            ertekeles_datab INT NOT NULL DEFAULT 0,
            egycsillag INT NOT NULL DEFAULT 0,
            ketcsillag INT NOT NULL DEFAULT 0,
            haromcsillag INT NOT NULL DEFAULT 0,
            negycsillag INT NOT NULL DEFAULT 0,
            otcsillag INT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";
        
        $sql_ertekelesek = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wc_odin_review_ertekelesek (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            rendeles_id BIGINT UNSIGNED NOT NULL,
            termek_id BIGINT UNSIGNED NOT NULL,
            keresztnev VARCHAR(255) NOT NULL,
            szoveges_ertekeles TEXT NOT NULL,
            csillag_ertekeles INT NOT NULL,
            datum DATETIME DEFAULT CURRENT_TIMESTAMP,
            elfogadva ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
            van_szoveges_ertekeles ENUM('yes', 'no') NOT NULL DEFAULT 'no'
        ) $charset_collate;";

        $sql_ertekeles_ellenorzo = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wc_odin_review_ertekeles_ellenorzo (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            rendeles_id BIGINT UNSIGNED NOT NULL,
            termek_id BIGINT UNSIGNED NOT NULL,
            keresztnev VARCHAR(255) NOT NULL,
            szoveges_ertekeles TEXT NOT NULL,
            csillag INT NOT NULL,
            datum DATETIME DEFAULT CURRENT_TIMESTAMP,
            token VARCHAR(255) NOT NULL,
            statusz ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
            email_kuldve ENUM('yes', 'no') NOT NULL DEFAULT 'no'
        ) $charset_collate;";

        $sql_options = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wc_odin_review_options (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            meta_key VARCHAR(255) NOT NULL,
            value TEXT NOT NULL
        ) $charset_collate;";
        
        dbDelta($sql_ertekelendo_termekek);
        if ($wpdb->last_error) {
            error_log("Error creating table wc_odin_review_ertekelendo_termekek: " . $wpdb->last_error);
        }

        dbDelta($sql_ertekelesek);
        if ($wpdb->last_error) {
            error_log("Error creating table wc_odin_review_ertekelesek: " . $wpdb->last_error);
        }

        dbDelta($sql_ertekeles_ellenorzo);
        if ($wpdb->last_error) {
            error_log("Error creating table wc_odin_review_ertekeles_ellenorzo: " . $wpdb->last_error);
        }

        dbDelta($sql_options);
        if ($wpdb->last_error) {
            error_log("Error creating table wc_odin_review_options: " . $wpdb->last_error);
        }
        
        // Alapértelmezett értékek hozzáadása
        $meta_key = 'vasarlo_email_form'; 
        $value = '<html><body><h1>Üdvözöljük!</h1><p>Kedves Vásárló!</p></body></html>';
        $wpdb->insert("{$wpdb->prefix}wc_odin_review_options", array('meta_key' => $meta_key, 'value' => $value));

        $meta_key = 'admin_email_form'; 
        $value = '<html><body><h1>Új értékelés érkezett!</h1><p>Kedves Magdi!</p></body></html>';
        $wpdb->insert("{$wpdb->prefix}wc_odin_review_options", array('meta_key' => $meta_key, 'value' => $value));

        $meta_key = 'varakozasi_ido'; 
        $value = '5';
        $wpdb->insert("{$wpdb->prefix}wc_odin_review_options", array('meta_key' => $meta_key, 'value' => $value));
		
		$meta_key = 'admin_email_cim'; 
        $value = 'info@example.hu';
        $wpdb->insert("{$wpdb->prefix}wc_odin_review_options", array('meta_key' => $meta_key, 'value' => $value));

        $meta_key = 'vasarlo_email_sub'; 
        $value = 'Értékelés kérése';
        $wpdb->insert("{$wpdb->prefix}wc_odin_review_options", array('meta_key' => $meta_key, 'value' => $value));
		
		$meta_key = 'admin_email_sub'; 
        $value = 'Új értékelés érkezett';
        $wpdb->insert("{$wpdb->prefix}wc_odin_review_options", array('meta_key' => $meta_key, 'value' => $value));
		
        $meta_key = 'kupon_email_sub'; 
        $value = 'Az értékelésedet kuponnal jutalmazzuk!';
        $wpdb->insert("{$wpdb->prefix}wc_odin_review_options", array('meta_key' => $meta_key, 'value' => $value));
		
        $meta_key = 'kupon_email_form'; 
        $value = 'itt a kuponod!';
        $wpdb->insert("{$wpdb->prefix}wc_odin_review_options", array('meta_key' => $meta_key, 'value' => $value));

		// Cron feladat hozzáadása
        if (!wp_next_scheduled('wc_ertekelesek_cron')) {
            wp_schedule_event(time(), 'daily', 'wc_ertekelesek_cron');
        }
    }

    public function ertekelesek_kezelo() {
        include plugin_dir_path(__FILE__) . 'admin/ertekelesek-kezelo.php';
    }

    public function ertekelesek_ellenorzo() {
        include plugin_dir_path(__FILE__) . 'admin/ertekelesek-ellenorzo.php';
    }

    public function beallitasok() {
        include plugin_dir_path(__FILE__) . 'admin/beallitasok.php';
    }

    public function vezerlopult() {
        include plugin_dir_path(__FILE__) . 'admin/vezerlopult.php';
    }
}

// Plugin inicializálása
new WC_Egyedi_Ertekelo();

// Cron esemény kezelése
add_action('wc_ertekelesek_cron', function() {
    error_log('WooCommerce értékelés cron futott.');
});
