<?php
namespace WBCOM\CartRules\Admin;

/**
 * Cart Rules Settings Page
 */
class SettingsPage {
    const OPTION_NAME = 'wbcom_cart_rules_settings';
    const NONCE_FIELD = 'wbcom_cart_rules_nonce';

    public function init() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
    }

    public function add_settings_page() {
        add_submenu_page(
            'woocommerce',
            __('Cart Rules', 'wbcom-cart-rules'),
            __('Cart Rules', 'wbcom-cart-rules'),
            'manage_woocommerce',
            'wbcom-cart-rules',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting(self::OPTION_NAME, self::OPTION_NAME, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default' => [],
        ]);

        add_settings_section(
            'wbcom_cart_rules_main',
            __('Cart Rules Configuration', 'wbcom-cart-rules'),
            function () {
                echo '<p>' . esc_html__('Configure cart rules and discounts.', 'wbcom-cart-rules') . '</p>';
            },
            self::OPTION_NAME
        );

        // ---- Settings Fields (same as before) ----
        add_settings_field(
            'enable_tiered_quantity',
            __('Enable Tiered Quantity Discount', 'wbcom-cart-rules'),
            [$this, 'render_checkbox_field'],
            self::OPTION_NAME,
            'wbcom_cart_rules_main',
            ['id' => 'enable_tiered_quantity']
        );
        add_settings_field(
            'tiered_quantity_category',
            __('Category for Discount', 'wbcom-cart-rules'),
            [$this, 'render_category_selector'],
            self::OPTION_NAME,
            'wbcom_cart_rules_main',
            ['id' => 'tiered_quantity_category']
        );
        add_settings_field(
            'tiered_quantity_thresholds',
            __('Tier Thresholds (items → discount%)', 'wbcom-cart-rules'),
            [$this, 'render_tier_thresholds_field'],
            self::OPTION_NAME,
            'wbcom_cart_rules_main',
            ['id' => 'tiered_quantity_thresholds']
        );

        add_settings_field(
            'enable_spend_threshold',
            __('Enable Spend Threshold Reward', 'wbcom-cart-rules'),
            [$this, 'render_checkbox_field'],
            self::OPTION_NAME,
            'wbcom_cart_rules_main',
            ['id' => 'enable_spend_threshold']
        );
        add_settings_field(
            'spend_threshold_amount',
            __('Spend Threshold Amount', 'wbcom-cart-rules'),
            [$this, 'render_input_field'],
            self::OPTION_NAME,
            'wbcom_cart_rules_main',
            ['id' => 'spend_threshold_amount', 'type' => 'number', 'min' => '0']
        );
        add_settings_field(
            'spend_threshold_product',
            __('Free Product (add to cart)', 'wbcom-cart-rules'),
            [$this, 'render_product_selector'],
            self::OPTION_NAME,
            'wbcom_cart_rules_main',
            ['id' => 'spend_threshold_product']
        );

        add_settings_field(
            'enable_first_time_customer',
            __('Enable First-Time Customer Offer', 'wbcom-cart-rules'),
            [$this, 'render_checkbox_field'],
            self::OPTION_NAME,
            'wbcom_cart_rules_main',
            ['id' => 'enable_first_time_customer']
        );
        add_settings_field(
            'first_time_discount_type',
            __('Discount Type', 'wbcom-cart-rules'),
            [$this, 'render_discount_type_selector'],
            self::OPTION_NAME,
            'wbcom_cart_rules_main',
            ['id' => 'first_time_discount_type']
        );
        add_settings_field(
            'first_time_discount_amount',
            __('Discount Amount', 'wbcom-cart-rules'),
            [$this, 'render_input_field'],
            self::OPTION_NAME,
            'wbcom_cart_rules_main',
            ['id' => 'first_time_discount_amount', 'type' => 'number', 'min' => '0']
        );
    }

    // Sanitization & nonce check for all settings fields
    public function sanitize_settings($input) {
        if (!isset($_POST[self::NONCE_FIELD]) || !wp_verify_nonce($_POST[self::NONCE_FIELD], self::OPTION_NAME)) {
            wp_die(__('Security check failed!', 'wbcom-cart-rules'));
        }
        $output = [];
        $output['enable_tiered_quantity'] = !empty($input['enable_tiered_quantity']);
        $output['tiered_quantity_category'] = sanitize_text_field($input['tiered_quantity_category'] ?? '');
        $output['tiered_quantity_thresholds'] = sanitize_text_field($input['tiered_quantity_thresholds'] ?? '');
        $output['enable_spend_threshold'] = !empty($input['enable_spend_threshold']);
        $output['spend_threshold_amount'] = floatval($input['spend_threshold_amount'] ?? 0);
        $output['spend_threshold_product'] = sanitize_text_field($input['spend_threshold_product'] ?? '');
        $output['enable_first_time_customer'] = !empty($input['enable_first_time_customer']);
        $output['first_time_discount_type'] = in_array($input['first_time_discount_type'] ?? '', ['fixed', 'percent']) ? $input['first_time_discount_type'] : 'fixed';
        $output['first_time_discount_amount'] = floatval($input['first_time_discount_amount'] ?? 0);
        return $output;
    }

    // ---- Field Renderers (same as before) ----
    public function render_checkbox_field($args) {
        $options = get_option(self::OPTION_NAME, []);
        $checked = !empty($options[$args['id']]);
        printf(
            '<input type="checkbox" id="%s" name="%s[%s]" value="1" %s />',
            esc_attr($args['id']),
            esc_attr(self::OPTION_NAME),
            esc_attr($args['id']),
            checked($checked, true, false)
        );
    }
    public function render_input_field($args) {
        $options = get_option(self::OPTION_NAME, []);
        $value = isset($options[$args['id']]) ? esc_attr($options[$args['id']]) : '';
        printf(
            '<input type="%s" id="%s" name="%s[%s]" value="%s" min="%s" />',
            esc_attr($args['type'] ?? 'text'),
            esc_attr($args['id']),
            esc_attr(self::OPTION_NAME),
            esc_attr($args['id']),
            $value,
            esc_attr($args['min'] ?? '0')
        );
    }
    public function render_category_selector($args) {
        $options = get_option(self::OPTION_NAME, []);
        $selected = isset($options[$args['id']]) ? esc_attr($options[$args['id']]) : '';
        $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
        echo '<select id="' . esc_attr($args['id']) . '" name="' . esc_attr(self::OPTION_NAME) . '[' . esc_attr($args['id']) . ']">';
        echo '<option value="">' . esc_html__('Select Category', 'wbcom-cart-rules') . '</option>';
        foreach ($categories as $category) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($category->term_id),
                selected($selected, $category->term_id, false),
                esc_html($category->name)
            );
        }
        echo '</select>';
    }
    public function render_product_selector($args) {
        $options = get_option(self::OPTION_NAME, []);
        $selected = isset($options[$args['id']]) ? esc_attr($options[$args['id']]) : '';
        $products = wc_get_products(['status' => 'publish', 'limit' => 50, 'orderby' => 'title', 'order' => 'ASC']);
        echo '<select id="' . esc_attr($args['id']) . '" name="' . esc_attr(self::OPTION_NAME) . '[' . esc_attr($args['id']) . ']">';
        echo '<option value="">' . esc_html__('Select Product', 'wbcom-cart-rules') . '</option>';
        foreach ($products as $product) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($product->get_id()),
                selected($selected, $product->get_id(), false),
                esc_html($product->get_name())
            );
        }
        echo '</select>';
    }
    public function render_tier_thresholds_field($args) {
        $options = get_option(self::OPTION_NAME, []);
        $value = isset($options[$args['id']]) ? esc_attr($options[$args['id']]) : '5:5,10:10,20:15';
        printf(
            '<input type="text" id="%s" name="%s[%s]" value="%s" size="40" />',
            esc_attr($args['id']),
            esc_attr(self::OPTION_NAME),
            esc_attr($args['id']),
            $value
        );
        echo '<p class="description">' . esc_html__('Format: items:discount%, comma separated (e.g. 5:5,10:10,20:15)', 'wbcom-cart-rules') . '</p>';
    }
    public function render_discount_type_selector($args) {
        $options = get_option(self::OPTION_NAME, []);
        $selected = isset($options[$args['id']]) ? esc_attr($options[$args['id']]) : 'fixed';
        echo '<select id="' . esc_attr($args['id']) . '" name="' . esc_attr(self::OPTION_NAME) . '[' . esc_attr($args['id']) . ']">';
        echo '<option value="fixed"' . selected($selected, 'fixed', false) . '>' . esc_html__('Fixed', 'wbcom-cart-rules') . '</option>';
        echo '<option value="percent"' . selected($selected, 'percent', false) . '>' . esc_html__('Percent', 'wbcom-cart-rules') . '</option>';
        echo '</select>';
    }
    public function enqueue_admin_styles($hook) {
        if ($hook === 'woocommerce_page_wbcom-cart-rules') {
            wp_enqueue_style('wbcom-cart-rules-admin', plugins_url('../../assets/admin.css', __FILE__), [], '1.0.0');
        }
    }

    public function render_settings_page() {
        ?>
        <div class="wrap wbcom-cart-rules-admin">
            <h1><?php esc_html_e('Cart Rules Settings', 'wbcom-cart-rules'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields(self::OPTION_NAME);
                // Add security nonce!
                wp_nonce_field(self::OPTION_NAME, self::NONCE_FIELD);
                do_settings_sections(self::OPTION_NAME);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}