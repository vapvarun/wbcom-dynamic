<?php
namespace WBCOM\CartRules\Support;

class Helpers {
   public static function item_name_with_breakdown($name, $cart_item, $cart_item_key) {
    if (!empty($cart_item['wbcom_cart_rules'])) {
        $breakdown = array_map(function($rule) {
            return esc_html($rule['label'] . ': ' . $rule['amount']);
        }, $cart_item['wbcom_cart_rules']);
        $tooltip = implode('<br>', $breakdown);
        $name .= sprintf(
            '<span class="wbcom-cart-rules-info" title="%s" style="cursor:help;"> <span class="dashicons dashicons-info"></span></span>',
            esc_attr(sprintf(__('Breakdown: %s', 'wbcom-cart-rules'), $tooltip))
        );
    }
    return $name;
}

    public static function show_cart_rule_notices() {
        $notices = WC()->session->get('wbcom_cart_rules_notices', []);
        foreach ($notices as $notice) {
            wc_print_notice($notice, 'notice');
        }
        self::clear_notices();
    }

    public static function get_notices_html() {
        ob_start();
        self::show_cart_rule_notices();
        return ob_get_clean();
    }

    public static function add_notice($message) {
        $notices = WC()->session->get('wbcom_cart_rules_notices', []);
        if (!in_array($message, $notices)) {
            $notices[] = $message;
        }
        WC()->session->set('wbcom_cart_rules_notices', $notices);
    }

    public static function clear_notices() {
        WC()->session->set('wbcom_cart_rules_notices', []);
    }
}