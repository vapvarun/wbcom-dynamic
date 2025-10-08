<?php
/**
 * Plugin Name: WBCOM Cart Rules
 * Description: Adds dynamic rule-based cart adjustments to WooCommerce. Tiered quantity discounts, spend thresholds, and first-time customer rewards.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: wbcom-cart-rules
 * Domain Path: /languages
 * Requires at least: 6.5
 * Tested up to: 6.5
 * Requires PHP: 8.1
 * WC requires at least: 8.0
 */

defined('ABSPATH') || exit;

// Autoload classes via Composer or fallback PSR-4.
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    spl_autoload_register(function ($class) {
        $prefix = 'WBCOM\\CartRules\\';
        $base_dir = __DIR__ . '/src/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) return;
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) require $file;
    });
}

// Load text domain for translations.
add_action('plugins_loaded', function () {
    load_plugin_textdomain('wbcom-cart-rules', false, basename(__DIR__) . '/languages');
});

// Initialize plugin when WooCommerce is loaded.
add_action('woocommerce_loaded', function () {
    (new WBCOM\CartRules\Services\CartAdjuster())->init();
    (new WBCOM\CartRules\Admin\SettingsPage())->init();
});

add_action('init', function() {
    (new \WBCOM\CartRules\Services\CartAdjuster())->init();
});

register_activation_hook(__FILE__, function () {
    // Future activation logic if needed.
});