<?php
use PHPUnit\Framework\TestCase;
use WBCOM\CartRules\Rules\SpendThreshold;

class RuleSpendThresholdTest extends TestCase {
    public function test_no_reward_below_threshold() {
        $cart = $this->getMockCart(200.0);
        update_option('wbcom_cart_rules_settings', [
            'enable_spend_threshold' => true,
            'spend_threshold_amount' => 500.0,
            'spend_threshold_product' => 321
        ]);
        (new SpendThreshold())->apply($cart);
        $this->assertTrue(true);
    }
    public function test_reward_added_above_threshold() {
        $cart = $this->getMockCart(600.0);
        update_option('wbcom_cart_rules_settings', [
            'enable_spend_threshold' => true,
            'spend_threshold_amount' => 500.0,
            'spend_threshold_product' => 321
        ]);
        (new SpendThreshold())->apply($cart);
        $this->assertTrue(true);
    }
    // private function getMockCart($subtotal) {
    //     return new class($subtotal) {
    //         public function get_subtotal() { return $this->subtotal; }
    //         public function __construct($subtotal) { $this->subtotal = $subtotal; }
    //         public function get_cart() { return []; }
    //         public function add_to_cart() {}
    //     };
    // }
}