<?php
namespace WBCOM\CartRules\Rules;

class FirstTimeCustomer {
    public function apply($cart) {
        $settings = get_option('wbcom_cart_rules_settings', []);
        if (empty($settings['enable_first_time_customer'])) return;

        // Get billing email for both logged-in and guest users
        $billing_email = '';
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $user_info = get_userdata($user_id);
            $billing_email = $user_info ? $user_info->user_email : '';
        } else {
            if (method_exists($cart, 'get_customer')) {
                $customer = $cart->get_customer();
                if ($customer && method_exists($customer, 'get_email')) {
                    $billing_email = $customer->get_email();
                }
            }
            if (!$billing_email && !empty($_POST['billing_email'])) {
                $billing_email = sanitize_email($_POST['billing_email']);
            }
            if (!$billing_email && !empty(WC()->session->get('customer_billing_email'))) {
                $billing_email = sanitize_email(WC()->session->get('customer_billing_email'));
            }
        }

        if (empty($billing_email)) return;

        $orders = wc_get_orders([
            'limit' => 1,
            'type' => 'shop_order',
            'status' => ['completed', 'processing'],
            'billing_email' => $billing_email,
        ]);
        if (!empty($orders)) return;

        // REMOVE session flag check to ensure discount is always recalculated
        // if (!empty(WC()->session->get('wbcom_cart_rules_first_time_applied_' . md5($billing_email)))) return;
		
        $discount_type = $settings['first_time_discount_type'] ?? 'fixed';
        $discount_amount = floatval($settings['first_time_discount_amount'] ?? 0);
        if ($discount_amount <= 0) return;

        // Remove previous fee to avoid duplicates (defensive)
        foreach ($cart->get_fees() as $fee_key => $fee) {
            if ($fee->name === __('First Order Discount', 'wbcom-cart-rules')) {
                unset($cart->fees[$fee_key]);
            }
        }

        if ($discount_type === 'fixed') {
            $cart->add_fee(__('First Order Discount', 'wbcom-cart-rules'), -$discount_amount, false, '');
        } else {
            $subtotal = $cart->get_subtotal();
            $discount = $subtotal * $discount_amount / 100;
            $cart->add_fee(__('First Order Discount', 'wbcom-cart-rules'), -$discount, false, '');
        }

        // Optionally, set the session flag (for other logic), but do NOT block add_fee with it.
        WC()->session->set('wbcom_cart_rules_first_time_applied_' . md5($billing_email), true);
    }
}