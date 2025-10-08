<?php
namespace WBCOM\CartRules\Services;

use WBCOM\CartRules\Support\Helpers;
use WBCOM\CartRules\Rules\TieredQuantity;
use WBCOM\CartRules\Rules\SpendThreshold;
use WBCOM\CartRules\Rules\FirstTimeCustomer;

class CartAdjuster {

    public function init() {
        // Handle spend threshold reward on cart session load and cart updated (for AJAX and instant updates)
        add_action('woocommerce_cart_loaded_from_session', [$this, 'handle_spend_threshold_reward']);
        add_action('woocommerce_cart_updated', [$this, 'handle_spend_threshold_reward']);
        add_action('woocommerce_before_calculate_totals', [$this, 'adjust_cart'], 20, 1);
        add_action('woocommerce_cart_calculate_fees', [$this, 'apply_cart_fees'], 20, 1);
        add_filter('woocommerce_cart_item_name', [$this, 'add_discount_breakdown'], 10, 3);
        add_filter('woocommerce_add_to_cart_fragments', [$this, 'update_cart_fragments'], 20, 1);
        add_filter('woocommerce_cart_item_name', [$this, 'add_free_tag'], 20, 3);
    }

    /**
     * Safely add/remove free product according to spend threshold and rule enabled status.
     * Prevent recursion with static flag.
     */
    public function handle_spend_threshold_reward($cart) {
        static $already_processing = false;
        if ($already_processing) return;
        $already_processing = true;

        $settings = get_option('wbcom_cart_rules_settings', []);
        $free_product_id = intval($settings['spend_threshold_product'] ?? 0);

        // If rule is disabled, remove any free product added by rule
        if (empty($settings['enable_spend_threshold'])) {
            foreach ($cart->get_cart() as $key => $item) {
                if ($item['product_id'] == $free_product_id && isset($item['wbcom_cart_rules_free'])) {
                    $cart->remove_cart_item($key);
                }
            }
            return;
        }

        $threshold = floatval($settings['spend_threshold_amount'] ?? 0);
        if (!$threshold || !$free_product_id) return;

        $subtotal = $cart->get_subtotal();

        // Find free product cart items (added by rule)
        $free_product_keys = [];
        foreach ($cart->get_cart() as $key => $item) {
            if ($item['product_id'] == $free_product_id && isset($item['wbcom_cart_rules_free'])) {
                $free_product_keys[] = $key;
            }
        }

        // Remove free product if threshold not met
        if ($subtotal < $threshold) {
            foreach ($free_product_keys as $key) {
                $cart->remove_cart_item($key);
            }
            return;
        }

        $product = wc_get_product($free_product_id);
        if (!$product || !$product->is_in_stock()) return;

        // Prevent duplicate: Only add if not already present as a reward and not customer-added
        foreach ($cart->get_cart() as $item) {
            if ($item['product_id'] == $free_product_id) {
                return;
            }
        }

        // Add free product as reward
        $cart->add_to_cart($free_product_id, 1, 0, [], ['wbcom_cart_rules_free' => uniqid('reward_', true)]);
        Helpers::get_notices_html();
    }

    /**
     * Price adjustments, set free product price to zero.
     */
    public function adjust_cart($cart) {
        Helpers::clear_notices();
        (new TieredQuantity())->apply($cart);
        $this->set_free_product_price($cart);
    }

    public function set_free_product_price($cart) {
        $settings = get_option('wbcom_cart_rules_settings', []);
        $free_product_id = intval($settings['spend_threshold_product'] ?? 0);
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['wbcom_cart_rules_free']) && $cart_item['product_id'] == $free_product_id) {
                $cart->cart_contents[$cart_item_key]['data']->set_price(0);
            }
        }
    }

    public function add_discount_breakdown($name, $cart_item, $cart_item_key) {
        return Helpers::item_name_with_breakdown($name, $cart_item, $cart_item_key);
    }

    public function add_free_tag($name, $cart_item, $cart_item_key) {
        if (isset($cart_item['wbcom_cart_rules_free'])) {
            $name .= ' <span style="color:green;font-weight:bold;">(' . __('Free Product', 'wbcom-cart-rules') . ')</span>';
        }
        return $name;
    }

    public function apply_cart_fees($cart) {
        (new SpendThreshold())->apply($cart);
        (new FirstTimeCustomer())->apply($cart);
    }

    public function add_customer_notices() {
        Helpers::show_cart_rule_notices();
    }

    public function update_cart_fragments($fragments) {
        
        $fragments['.wbcom-cart-rules-notices'] = Helpers::get_notices_html();
        return $fragments;
    }
}