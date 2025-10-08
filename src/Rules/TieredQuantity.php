<?php
namespace WBCOM\CartRules\Rules;

class TieredQuantity {
    public function apply($cart) {
        $settings = get_option('wbcom_cart_rules_settings', []);
        if (empty($settings['enable_tiered_quantity'])) return;
        $category_id = intval($settings['tiered_quantity_category'] ?? 0);
        if (!$category_id) return;
        $thresholds = [];
        if (!empty($settings['tiered_quantity_thresholds'])) {
            foreach (explode(',', $settings['tiered_quantity_thresholds']) as $pair) {
                list($qty, $disc) = array_map('trim', explode(':', $pair));
                $thresholds[intval($qty)] = floatval($disc);
            }
        }
        if (empty($thresholds)) return;

        $total_items = 0;
        $cart_items = [];
        foreach ($cart->get_cart() as $key => $item) {
            $product_id = $item['product_id'];
            if (has_term($category_id, 'product_cat', $product_id)) {
                $total_items += $item['quantity'];
                $cart_items[$key] = $item;
            }
        }

        $discount_percent = 0;
        foreach ($thresholds as $qty => $disc) {
            if ($total_items >= $qty) {
                $discount_percent = $disc;
            }
        }
        if ($discount_percent <= 0) return;

        foreach ($cart_items as $key => $item) {
            $price = $cart->cart_contents[$key]['data']->get_price();
            $discount = $price * $discount_percent / 100;
            $cart->cart_contents[$key]['data']->set_price($price - $discount);
            $cart->cart_contents[$key]['wbcom_cart_rules'][] = [
                'label' => __('Tiered Quantity', 'wbcom-cart-rules'),
                'amount' => '-' . wc_price($discount)
            ];
        }
    }
}