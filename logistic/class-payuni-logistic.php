<?php

class WC_PAYUNi_Logistic {

    public function __construct()
    {
        add_action( 'woocommerce_shipping_init', array( $this, 'get_logistic' ) );
        add_filter( 'woocommerce_shipping_methods', array( $this, 'insert_shipping_methods' ) );
        add_filter( 'woocommerce_available_payment_gateways', array( $this, 'check_shipping_payment'), 1 );
    }

    // 取得支援物流方式
    public function get_logistic()
    {
        include plugin_dir_path( __FILE__ ) . 'payuni-logistic.php';
        include plugin_dir_path( __FILE__ ) . 'payuni-logistic-711.php';
        include plugin_dir_path( __FILE__ ) . 'payuni-logistic-711-freeze.php';
        include plugin_dir_path( __FILE__ ) . 'payuni-logistic-tcat.php';
        include plugin_dir_path( __FILE__ ) . 'payuni-logistic-tcat-freeze.php';
        include plugin_dir_path( __FILE__ ) . 'payuni-logistic-tcat-cold.php';
    }

    // 塞入物流方式
    public function insert_shipping_methods($methods)
    {
        $methods['PAYUNi_Logistic_711'] = 'PAYUNi_Logistic_711';
        $methods['PAYUNi_Logistic_711_Freeze'] = 'PAYUNi_Logistic_711_Freeze';
        $methods['PAYUNi_Logistic_Tcat'] = 'PAYUNi_Logistic_Tcat';
        $methods['PAYUNi_Logistic_Tcat_Freeze'] = 'PAYUNi_Logistic_Tcat_Freeze';
        $methods['PAYUNi_Logistic_Tcat_Cold'] = 'PAYUNi_Logistic_Tcat_Cold';

        return $methods;
    }

    // 物流必須同時使用統一金流支付
    public function check_shipping_payment($payment_gateways)
    {
        if ( ! is_admin() && function_exists('wc_get_chosen_shipping_method_ids') ) {
            $chosen_shipping_tmp = wc_get_chosen_shipping_method_ids();
            if(empty($chosen_shipping_tmp)){
                return $payment_gateways;
            }
            $chosen_shipping = $chosen_shipping_tmp[0] ;

            if( !empty($chosen_shipping) && preg_match("/PAYUNi/i", $chosen_shipping) ){
                $payment_gateways = [
                    'payuni' => $payment_gateways['payuni']
                ];

                $total = $this->get_order_total();
                // 結帳總金額檢查(超過兩萬提示訊息)
                if($total >= 20000){
                    $payment_gateways['payuni']->description .= '<p class="woocommerce-notice woocommerce-notice--info woocommerce-info">訂單總金額超過2萬元，將無法使用貨到付款。</p>';
                }
                // 結帳總金額檢查(低於1元提示訊息)
                if($total <= 1){
                    $payment_gateways['payuni']->description .= '<p class="woocommerce-notice woocommerce-notice--info woocommerce-info">訂單總金額低於1元，將無法使用貨到付款。</p>';
                }
            }
        }

        return $payment_gateways;
    }

    private function get_order_total() {
        $cart = is_null(WC()->session) ? array() : WC()->session->get('cart_totals', array());
        return isset($cart['total']) ? (int) $cart['total'] : 0;
    }
}