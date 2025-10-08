# WBCOM Cart Rules Plugin

## Setup

1. Clone or download the ZIP and extract to `wp-content/plugins/wbcom-cart-rules`.
2. Run `composer install` if using Composer autoloader.
3. Activate in WP Admin > Plugins.
4. Find settings under WooCommerce → Cart Rules.
5. Setup thresholds, products, etc. per your store.

## Configuration

- **Tiered Quantity Discount:** Select category and input thresholds (e.g. 5:5,10:10,20:15).
- **Spend Threshold Reward:** Set spend amount and select free product.
- **First-Time Customer Offer:** Set fixed or percent discount, only for first completed order per user/email.

## Security

- Admin settings form is protected by a nonce (`wbcom_cart_rules_nonce`).
- All fields are sanitized and validated.

## Calculation Order

1. Tiered Quantity Discount (adjusts line item prices)
2. Spend Threshold Reward (adds free product, shows notice)
3. First-Time Customer Offer (applies cart fee)

## Conflict Handling

- Notices shown if free product out of stock/already in cart.
- First-time discount does not repeat per user/email.
- Double benefits prevented if coupon/discount overlap.

## Edge Cases

- Store-wide compatibility for multiple categories/products.
- Deterministic discount composition.
- All rules independently toggleable.

## Testing

- PHPUnit unit/integration tests for all rules included.
- GitHub Actions CI for PHP 8.1/8.2.
- Run tests locally with `vendor/bin/phpunit`.
- Example test output:
  ```
  PHPUnit 10.0.0 by Sebastian Bergmann and contributors.

  RuleTieredQuantityTest
   ✔ test_no_discount_below_threshold
   ✔ test_discount_applied_above_threshold

  RuleSpendThresholdTest
   ✔ test_no_reward_below_threshold
   ✔ test_reward_added_above_threshold

  RuleFirstTimeCustomerTest
   ✔ test_first_time_discount_applied

  CartCompositionTest
   ✔ test_combined_rules_deterministic_result

  Time: 00:01, Memory: 4.00 MB
  ```


---

### Approach Note (≤300 words)

This plugin was built to strictly follow assignment logic, focusing on OOP design, deterministic rule composition, and WooCommerce best practices. Each rule is modular, toggleable, and integrated via core WC hooks. The admin UI uses native selectors for categories/products and groups related settings for clarity. Notices are rendered dynamically and update via cart fragments for AJAX compatibility. All calculations happen on cart refresh for performance. Security is enforced via nonces and capability checks. PHPUnit tests cover core logic and integration, and CI workflow ensures code quality. No inline CSS is used. The plugin is ready for real-world stores and extensible for future rules.
