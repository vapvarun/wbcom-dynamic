<?php
namespace WBCOM\CartRules\Rules;

use WBCOM\CartRules\Support\Helpers;

class SpendThreshold {
    public function apply($cart) {
        $settings = get_option('wbcom_cart_rules_settings', []);
        if (empty($settings['enable_spend_threshold'])) return;

        $threshold = floatval($settings['spend_threshold_amount'] ?? 0);
        $free_product_id = intval($settings['spend_threshold_product'] ?? 0);
        if (!$threshold || !$free_product_id) return;

        $subtotal = $cart->get_subtotal();

        // Find all free product items in the cart
        $free_product_keys = [];
        foreach ($cart->get_cart() as $key => $item) {
            if ($item['product_id'] == $free_product_id && isset($item['wbcom_cart_rules_free'])) {
                $free_product_keys[] = $key;
            }
        }
       
        // Find if customer-added free product is in cart
        $customer_added_free_product = false;
        foreach ($cart->get_cart() as $key => $item) {
            if ($item['product_id'] == $free_product_id && !isset($item['wbcom_cart_rules_free'])) {
                $customer_added_free_product = true;
                break;
            }
        }

        $product = wc_get_product($free_product_id);
        $product_name = $product ? $product->get_name() : __('free gift', 'wbcom-cart-rules');

        // If subtotal is less than threshold, show "spend more" notice
        if ($subtotal < $threshold) {
            $remaining = $threshold - $subtotal;
            
            Helpers::add_notice(sprintf(
                __('Spend %s more to get a free %s!', 'wbcom-cart-rules'),
                wc_price($remaining),
                esc_html($product_name)
            ));
            return;
        }

        // If product is out of stock, show notice
        if (!$product || !$product->is_in_stock()) {
            Helpers::add_notice(__('Free product is out of stock!', 'wbcom-cart-rules'));
            return;
        }
        
        // If free product is already in cart (added by rule), show notice
        if (!empty($free_product_keys)) {
            Helpers::add_notice(sprintf(__('Free product already in cart!', 'wbcom-cart-rules')));
            return;
        }
        
        // If customer added the product manually, show notice
        if ($customer_added_free_product) {
            Helpers::add_notice(__('You already have the free product in your cart!', 'wbcom-cart-rules'));
            return;
        }
        
        // Otherwise, show notice for adding (actual add/remove is in CartAdjuster)
        Helpers::add_notice(__('Free product will be added to cart!', 'wbcom-cart-rules'));
    }
}