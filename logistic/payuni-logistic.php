<?php

class PAYUNi_Logistic extends WC_Shipping_Method
{
     public function __construct($instance_id)
    {
        $this->instance_id          = absint($instance_id);
        $this->instance_form_fields = include plugin_dir_path( __FILE__ ) . '/payuni-logistic-config.php';

        $this->init_settings();

        $this->title         = $this->get_option('title');
        $this->tax_status    = $this->get_option('tax_status');
        $this->cost          = $this->get_option('cost');
        $this->cost_requires = $this->get_option('cost_requires');
        $this->min_amount    = $this->get_option('min_amount', 0);

        $this->coupon_check  = ['coupon', 'either', 'both'];

        $this->supports = [
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal',
        ];

        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    public function calculate_shipping($package = [])
    {
        $rate = [
            'id'      => $this->get_rate_id(),
            'label'   => $this->title,
            'cost'    => $this->cost,
            'package' => $package,
        ];

        if ($this->check_free_shipping()) {
            $rate['cost'] = 0;
        }

        $this->add_rate($rate);
        do_action('woocommerce_' . $this->id . '_shipping_add_rate', $this, $rate);
    }

    private function check_free_shipping()
    {
        $has_coupon = $this->has_coupon();

        $total = WC()->cart->get_displayed_subtotal();
        if ('incl' === WC()->cart->get_tax_price_display_mode) {
            $total = round($total - (WC()->cart->get_cart_discount_total() + WC()->cart->get_cart_discount_tax_total()), wc_get_price_decimals());
        } else {
            $total = round($total - WC()->cart->get_cart_discount_total(), wc_get_price_decimals());
        }

        $min_amount_condition = ($total >= $this->min_amount);

        if ( $this->cost_requires == 'coupon') {
            return $has_coupon;
        }

        if ( $this->cost_requires == 'min_amount') {
            return $min_amount_condition;
        }

        if ( $this->cost_requires == 'either') {
            return $has_coupon || $min_amount_condition;
        }
        
        if ( $this->cost_requires == 'both' ) {
            return $has_coupon && $min_amount_condition;
        }

        return false;
    }

    private function has_coupon()
    {
        if ( !in_array( $this->cost_requires, $this->coupon_check) ) {
            return false;
        }

        $coupons = WC()->cart->get_coupons();
        if ( empty($coupons) ) {
            return false;
        }

        foreach ( $coupons as $coupon ) {
            if ( $coupon->is_valid() && $coupon->get_free_shipping() ) {
                return true;
            }
        }

        return false;
    }
}