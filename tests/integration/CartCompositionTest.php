<?php
use PHPUnit\Framework\TestCase;
use WBCOM\CartRules\Rules\TieredQuantity;

/**
 * Integration-style logic test without spinning up WooCommerce.
 * Simulates a cart with mixed categories and validates tier resolution outcome.
 */
final class CartCompositionTest extends TestCase {

	public function test_mixed_categories_tier_applies_only_to_target_category(): void {
		$tiers = [
			['qty'=>5,'percent'=>5],
			['qty'=>10,'percent'=>10],
		];
		// Simulated cart lines: [product_id, category_id, qty, price].
		$target_category = 7;
		$lines = [
			['pid'=>1,'cat'=>7,'qty'=>3,'price'=>100.0],
			['pid'=>2,'cat'=>7,'qty'=>2,'price'=>200.0],
			['pid'=>3,'cat'=>8,'qty'=>10,'price'=>50.0],
		];
		$total_qty_target = 0;
		$item_discount_total = 0.0;

		foreach ( $lines as $l ) {
			if ( $l['cat'] === $target_category ) {
				$total_qty_target += $l['qty'];
			}
		}
		$percent = TieredQuantity::tier_percent_for_quantity( $total_qty_target, $tiers );
		$rate = $percent / 100;

		foreach ( $lines as $l ) {
			if ( $l['cat'] === $target_category ) {
				$item_discount_total += $l['qty'] * $l['price'] * $rate;
			}
		}

		$this->assertSame(5.0, $percent);
		$this->assertSame( (3*100.0 + 2*200.0) * 0.05, $item_discount_total );
	}
}
