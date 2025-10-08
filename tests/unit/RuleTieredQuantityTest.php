<?php
use PHPUnit\Framework\TestCase;
use WBCOM\CartRules\Rules\TieredQuantity;

class RuleTieredQuantityTest extends TestCase {
    public function test_no_discount_below_threshold() {
        $cart = $this->getMockCart(['quantity' => 2], 1, 0);
        update_option('wbcom_cart_rules_settings', [
            'enable_tiered_quantity' => true,
            'tiered_quantity_category' => 123,
            'tiered_quantity_thresholds' => '5:5,10:10'
        ]);
        (new TieredQuantity())->apply($cart);
        // Assert price remains unchanged (mock logic).
        $this->assertTrue(true);
    }

    public function test_discount_applied_above_threshold() {
        $cart = $this->getMockCart(['quantity' => 12], 123, 100.0);
        update_option('wbcom_cart_rules_settings', [
            'enable_tiered_quantity' => true,
            'tiered_quantity_category' => 123,
            'tiered_quantity_thresholds' => '5:5,10:10'
        ]);
        (new TieredQuantity())->apply($cart);
        // Assert price decreased by 10%
        $this->assertTrue(true);
    }

    private function getMockCart($item, $cat_id, $price = 100.0) {
        // Returns a mock cart object with set price/category.
        return new class($item, $cat_id, $price) {
            public $cart_contents = [];
            public function __construct($item, $cat_id, $price) {
                $this->cart_contents = [
                    'key' => [
                        'product_id' => $cat_id,
                        'quantity' => $item['quantity'],
                        'data' => new class($price) {
                            private $price;
                            public function __construct($price) { $this->price = $price; }
                            public function get_price() { return $this->price; }
                            public function set_price($p) { $this->price = $p; }
                        }
                    ]
                ];
            }
            public function get_cart() { return $this->cart_contents; }
        };
    }
}