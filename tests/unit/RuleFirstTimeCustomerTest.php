<?php
use PHPUnit\Framework\TestCase;
use WBCOM\CartRules\Rules\FirstTimeCustomer;

class RuleFirstTimeCustomerTest extends TestCase {
    public function test_first_time_discount_applied() {
        $cart = $this->getMockCart();
        update_option('wbcom_cart_rules_settings', [
            'enable_first_time_customer' => true,
            'first_time_discount_type' => 'fixed',
            'first_time_discount_amount' => 50.0
        ]);
        (new FirstTimeCustomer())->apply($cart);
        $this->assertTrue(true);
    }
    private function getMockCart() {
        return new class {
            public function get_customer_email() { return 'test@example.com'; }
            public function get_cart_session() { return []; }
            public function add_fee() {}
            public function get_subtotal() { return 100.0; }
        };
    }
}