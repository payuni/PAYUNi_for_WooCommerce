<?php
/**
 * payuni Payment Gateway
 * Plugin URI: https://www.payuni.com.tw/
 * Description: 統一金流 整合式支付模組
 * Version: 1.0.0
 * Author URI: https://www.payuni.com.tw/
 * Author: 統一金流 PAYUNi
 * Plugin Name:   統一金流 PAYUNi
 * @class       payuni
 * @extends     WC_Payment_Gateway
 * @version
 */
require_once plugin_dir_path( __FILE__ ) . '/logistic/class-payuni-logistic.php';

add_action('plugins_loaded', 'payuni_gateway_init', 0);

function payuni_gateway_init() {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }
    $plugin_logistic = new WC_PAYUNi_Logistic();

    class WC_payuni extends WC_Payment_Gateway {
        /**
         * Constructor for the gateway.
         *
         * @access public
         * @return void
         */
        public function __construct() {
            // Check ExpireDate is validate or not
            if(isset($_POST['woocommerce_payuni_ExpireDate']) && (!preg_match('/^\d*$/', $_POST['woocommerce_payuni_ExpireDate']) || $_POST['woocommerce_payuni_ExpireDate'] < 1 || $_POST['woocommerce_payuni_ExpireDate'] > 180)){
              $_POST['woocommerce_payuni_ExpireDate'] = 7;
            }

            $this->id   = 'payuni';
            $this->icon = apply_filters('woocommerce_payuni_icon', plugins_url('icon/payuni_65_yb.png', __FILE__));
            $this->has_fields = false;
            $this->method_title = __('統一金流 PAYUNi', 'woocommerce');
            $this->method_description = '使用統一金流 PAYUNi付款，整合各式金流付款工具，並提供信託價金保管，付款更安心。';

            // Load the form fields.
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();

            // Define user set variables
            $this->title       = $this->settings['title'];
            $this->version     = '1.0';
            $this->description = $this->settings['description'];
            $this->MerchantID  = trim($this->settings['MerchantID']);
            $this->HashKey     = trim($this->settings['HashKey']);
            $this->HashIV      = trim($this->settings['HashIV']);
            $this->ExpireDate  = $this->settings['ExpireDate'];
            $this->TestMode    = $this->settings['TestMode'];
            $this->notify_url  = add_query_arg('wc-api', 'WC_payuni', home_url('/'));

            // Test Mode
            if ($this->TestMode == 'yes') {
                $this->gateway = "https://sandbox-api.payuni.com.tw/api/upp"; //測試網址
            } else {
                $this->gateway = "https://api.payuni.com.tw/api/upp"; // 正式網址
            }

            // Actions
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
            add_action('woocommerce_api_wc_' . $this->id, array($this, 'receive_response')); //api_"class名稱(小寫)"
        }

        /**
         * Initialise Gateway Settings Form Fields
         *
         * @access public
         * @return void
         * 後台欄位設置
         */
        function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('啟用/關閉', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('啟動 統一金流 整合式支付模組', 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('標題', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('', 'woocommerce'),
                    'default' => __('統一金流 PAYUNi', 'woocommerce')
                ),
                'description' => array(
                    'title' => __('描述', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('', 'woocommerce'),
                    'default' => __('您將前往統一金流 PAYUNi支付頁面，整合各式金流付款工具，並提供信託價金保管，付款更安心。', 'woocommerce')
                ),
                'MerchantID' => array(
                    'title' => __('統一金流 商店代號', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('請填入您的統一金流的商店代號', 'woocommerce')
                ),
                'HashKey' => array(
                    'title' => __('統一金流 Hash Key', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('請填入您的統一金流的Hash Key', 'woocommerce')
                ),
                'HashIV' => array(
                    'title' => __('統一金流 IV Key', 'woocommerce'),
                    'type' => 'text',
                    'description' => __("請填入您的統一金流的IV Key", 'woocommerce')
                ),
                'ExpireDate' => array(
                    'title' => __('繳費有效期限(天)', 'woocommerce'),
                    'type' => 'text',
                    'description' => __("請設定繳費有效期限(1~180天), 預設為7天", 'woocommerce'),
                    'default' => 7
                ),
                'TestMode' => array(
                    'title' => __('測試模組', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('啟動測試模組', 'woocommerce'),
                    'description' => __("選擇是否開啟測試模式", 'woocommerce'),
                    'default' => 'yes'
                ),
                'LogisticSettings' => array(
                    'title' => __('物流設定', 'woocommerce'),
                    'type' => 'title',
                ),
                'CvsType' => array(
                    'title' => __('超商取貨類型', 'woocommerce'),
                    'type' => 'select',
                    'default' => 'C2C',
                    'options' => [
                        'C2C' => __('C2C', 'CvsType', 'woocommerce'),
                        'B2C' => __('B2C', 'CvsType', 'woocommerce'),
                    ]
                ),
            );
        }

        /**
         * Admin Panel Options
         * - Options for bits like 'title' and availability on a country-by-country basis
         *
         * @access public
         * @return void
         */
        public function admin_options() {

            ?>
            <h3><?php _e('統一金流 整合式支付模組', 'woocommerce'); ?></h3>
            <p><?php _e('此模組可以讓您使用統一金流的整合式支付功能', 'woocommerce'); ?></p>
            <table class="form-table">
                <?php
                // Generate the HTML For the settings form.
                $this->generate_settings_html();
                ?>
                <script>
                  var invalidate = function(){
                        jQuery(this).css('border-color', 'red');
                        jQuery('#'+this.id+'_error_msg').show();
                        jQuery('input[type="submit"]').prop('disabled', 'disabled');
                      },
                      validate = function(){
                        jQuery(this).css('border-color', '');
                        jQuery('#'+this.id+'_error_msg').hide();
                        jQuery('input[type="submit"]').prop('disabled', '');
                      }

                            validate = function () {
                                jQuery(this).css('border-color', '');
                                jQuery('#' + this.id + '_error_msg').hide();
                                jQuery('input[type="submit"]').prop('disabled', '');

                            }

                    jQuery('#woocommerce_payuni_ExpireDate')
                            .bind('keypress', function (e) {
                                if (e.charCode < 48 || e.charCode > 57) {
                                    return false;
                                }
                            })
                            .bind('blur', function (e) {
                                if (!this.value) {
                                    validate.call(this);
                                }
                            });

                    jQuery('#woocommerce_payuni_ExpireDate')
                            .bind('input', function (e) {
                                if (!this.value) {
                                    validate.call(this);
                                    return false;
                                }

                                if (this.value < 1 || this.value > 180) {
                                    invalidate.call(this);

                                } else {
                                    validate.call(this);
                                }
                            })
                            .bind('blur', function (e) {
                                if (!this.value) {
                                    this.value = 7;
                                    validate.call(this);
                                }
                            })
                    .after('<span style="display: none;color: red;" id="woocommerce_payuni_ExpireDate_error_msg">請輸入範圍內1~180的數字</span>')
                </script>
            </table><!--/.form-table-->
            <?php
        }

        /**
         * Get payuni Args for passing to payuni
         *
         * @access public
         * @param mixed $order
         * @return array
         *
         * upp參數格式
         */
        function get_payuni_args($order) {
            return apply_filters('woocommerce_payuni_args',
                $this->transformpayuniVersion($order,$this->version)
            );
        }

        /**
         * Output for the order received page.
         *
         * @access public
         * @return void
         */
        function thankyou_page() {
            $postData = $_REQUEST;
            $result = $this->ResultProcess($postData);
            if ($result['success'] == true) {
                if (in_array($result['message']['Status'], array('SUCCESS', 'OK'))) {
                    $encryptInfo = $result['message']['EncryptInfo'];
                    $order = wc_get_order($encryptInfo['MerTradeNo']);
                    if (!$order) {
                        $msg = "取得訂單失敗，訂單編號：" . $encryptInfo['MerTradeNo'];
                        echo $msg;
                        $this->writeLog($msg);
                        exit;
                    }
                    $oAmt = round($order->get_total());
                    $rAmt = $encryptInfo['TradeAmt'];
                    if ($oAmt != $rAmt) {
                        $msg = "結帳金額與訂單金額不一致";
                        echo $msg;
                        $this->writeLog($msg);
                        exit;
                    }
                    $message = $this->SetNotice($encryptInfo);
                    echo $message;
                }
                else {
                    echo "交易失敗：" . $result['message']['Status'] . "(" . $result['message']['EncryptInfo']['Message'] . ")";
                }
            }
            else {
                echo "解密失敗";
            }
        }
        /**
         * 接收回傳參數驗證
         *
         * @access public
         * @return void
         */
        function receive_response() {
            global $woocommerce;
            $postData = $_REQUEST;
            $result = $this->ResultProcess($postData);
            if ($result['success'] == true) {
                if ($result['message']['Status'] == 'SUCCESS') {
                    $encryptInfo = $result['message']['EncryptInfo'];
                    $order = wc_get_order($encryptInfo['MerTradeNo']);
                    if (!$order) {
                        $msg = "取得訂單失敗，訂單編號：" . $encryptInfo['MerTradeNo'];
                        $this->writeLog($msg);
                        exit;
                    }
                    $oAmt = round($order->get_total());
                    $rAmt = $encryptInfo['TradeAmt'];
                    if ($oAmt != $rAmt) {
                        $msg = "結帳金額與訂單金額不一致";
                        $this->writeLog($msg);
                        exit;
                    }
                    $message = $this->SetNotice($encryptInfo);
                    $order->add_order_note($message);
                    switch ($encryptInfo['TradeStatus']) {
                        case '0':
                            $order->update_status('on-hold', __( 'Awaiting cheque payment', 'woocommerce' ));
                            break;
                        case '1':
                            $order->payment_complete();
                            break;
                    }
                }
                else {
                    $msg = "交易失敗：" . $result['message']['Status'] . "(" . $result['message']['EncryptInfo']['Message'] . ")";
                    $this->writeLog($msg);
                    exit;
                }
            }
            else {
                $msg = "解密失敗";
                $this->writeLog($msg);
                exit;
            }
            exit;
        }
        /**
         * 產生訊息內容
         * return string
         */
        private function SetNotice(Array $encryptInfo) {
            $trdStatus = ['待付款','已付款','付款失敗','付款取消'];

            // 訂單狀態(物流訂單需判斷是否是取貨完成)
            $shipping_final_status = isset($encryptInfo['Odno']);
            $status = $shipping_final_status ? $encryptInfo['Message'] : $trdStatus[$encryptInfo['TradeStatus']];

            $message   = "<<<code>統一金流 PAYUNi</code>>>";
            $message .= "</br>訂單狀態：" . $status;
            $message .= "</br>UNi序號：" . $encryptInfo['TradeNo'];

            switch ($encryptInfo['PaymentType']){
                case '1': // 信用卡
                    $authType = [1=>'一次', 2=>'分期', 3=>'紅利', 7=>'銀聯'];
                    if ( !$shipping_final_status ) {
                        $message .= "</br>授權狀態：" . $encryptInfo['Message'];
                        $message .= "</br>卡號：" . $encryptInfo['Card6No'] . '******' . $encryptInfo['Card4No'];
                        if ($encryptInfo['CardInst'] > 1) {
                            $message .= "</br>分期數：" . $encryptInfo['CardInst'];
                            $message .= "</br>首期金額：" . $encryptInfo['FirstAmt'];
                            $message .= "</br>每期金額：" . $encryptInfo['EachAmt'];
                        }
                        $message .= "</br>授權碼：" . $encryptInfo['AuthCode'];
                        $message .= "</br>授權銀行代號：" . $encryptInfo['AuthBank'];
                        $message .= "</br>授權銀行：" . $encryptInfo['AuthBankName'];
                        $message .= "</br>授權類型：" . $authType[$encryptInfo['AuthType']];
                        $message .= "</br>授權日期：" . $encryptInfo['AuthDay'];
                        $message .= "</br>授權時間：" . $encryptInfo['AuthTime'];
                    }
                    break;
                case '2': // atm轉帳
                    if ($encryptInfo['TradeStatus'] == 1) {
                        $message .= "</br>付款銀行代碼：" . $encryptInfo['PayBank'];
                        $message .= "</br>付款帳號後5碼：" . $encryptInfo['Account5No'];
                    } else {
                        $message .= "</br>銀行代碼：" . $encryptInfo['BankType'];
                        $message .= "</br>繳費帳號：" . $encryptInfo['PayNo'];
                        $message .= "</br>繳費截止時間：" . $encryptInfo['ExpireDate'];
                    }
                    break;
                case '3': // 超商代碼
                    $store = ['SEVEN' => '統一超商 (7-11)'];
                    if ($encryptInfo['TradeStatus'] == 0) {
                        $message .= "</br>繳費方式：" . $store[$encryptInfo['Store']];
                        $message .= "</br>繳費代號：" . $encryptInfo['PayNo'];
                        $message .= "</br>繳費截止時間：" . $encryptInfo['ExpireDate'];
                    }
                    break;
                case '6': // ICP 愛金卡
                    if ( !$shipping_final_status ) {
                        $message .= "</br>愛金卡交易序號：" . $encryptInfo['PayNo'];
                        $message .= "</br>付款日期時間：" . $encryptInfo['PayTime'];
                    }
                    break;
                case '7': // AFTEE
                    if ( !$shipping_final_status ) {
                        $message .= "</br>AFTEE交易序號：" . $encryptInfo['PayNo'];
                    }
                    break;
                case '9': // LINE Pay
                    if ( !$shipping_final_status ) {
                        $message .= "</br>LINE Pay交易序號：" . $encryptInfo['PayNo'];
                    }
                    break;
                default: // 預設顯示資訊
                    break;
            }

            //物流資訊
            if (isset($encryptInfo['ShipType'])) {
                switch ($encryptInfo['ShipType']){
                    case '1': // SEVEN
                        $goodsType = [1=>'常溫', 2=>'冷凍'];
                        $serviceType = [1=>'取貨付款', 3=>'取貨不付款'];
                        $message .= "</br>寄件型態：" . $goodsType[$encryptInfo['GoodsType']];
                        $message .= "</br>通路類別： 7-11";
                        $message .= "</br>取貨方式：" . $serviceType[$encryptInfo['ServiceType']];
                        if ( !$shipping_final_status ) {
                            $message .= "</br>取件門市名稱：" . $encryptInfo['StoreName'];
                            $message .= "</br>取件門市地址：" . $encryptInfo['StoreAddr'];
                            $message .= "</br>收件人：" . $encryptInfo['Consignee'];
                            $message .= "</br>收件人手機號碼：" . $encryptInfo['ConsigneeMobile'];
                        }
                        break;
                    default: // 預設顯示資訊
                        break;
                }
            }

            return $message;
        }
        /**
         *依版本轉換資料內容
         *
         * @access private
         * @param order $order, string $version
         * @return array
         */
        private function transformpayuniVersion($order,$version)
        {
            switch ($version) {
                case '1.0':
                    return $this->uppOnePointHandler($order);
                break;
                default:
                break;
            }
        }
        /**
         *upp資料處理
         *
         * @access private
         * @param order $order
         * @version 1.0
         * @return array
         */
        private function uppOnePointHandler($order)
        {
            $prodDesc = [];
            $items = $order->get_items();
            foreach ( $items as $item ) {
                $prodDesc[] = $item->get_name() . ' * ' . $item->get_quantity();
            }

            $encryptInfo = [
                'MerID' => $this->MerchantID,
                'MerTradeNo' => $order->get_id(),
                'TradeAmt'  => (int) $order->get_total(),
                'ExpireDate' => date('Y-m-d', strtotime("+".$this->ExpireDate." days")),
                'ProdDesc' => implode(';', $prodDesc),
                'UsrMail' => $order->get_billing_email(),
                'ReturnURL' => $this->get_return_url($order),
                "NotifyURL" => $this->notify_url, //幕後
                'Timestamp' => time()
            ];

            // 物流參數
            foreach( $order->get_items('shipping') as $item ){
                $item_data = $item->get_data();
                $shipping_data_method_id = $item_data['method_id'];
            }

            switch ($shipping_data_method_id) {
                // 711 超商取貨(常溫、冷凍)
                case 'PAYUNi_Logistic_711':
                case 'PAYUNi_Logistic_711_Freeze':
                    $encryptInfo['ShipTag']         = 1;
                    $encryptInfo['ShipType']        = 1;
                    $encryptInfo['LgsType']         = trim($this->settings['CvsType']);
                    $encryptInfo['GoodsType']       = ($shipping_data_method_id == 'PAYUNi_Logistic_711_Freeze') ? 2 : 1;
                    $encryptInfo['Consignee']       = $order->get_shipping_last_name() . $order->get_shipping_first_name();
                    $encryptInfo['ConsigneeMobile'] = $order->get_billing_phone();
                    break;
                default:
                    break;
            }

            $parameter['MerID']       = $this->MerchantID;
            $parameter['Version']     = $this->version;
            $parameter['EncryptInfo'] = $this->Encrypt($encryptInfo);
            $parameter['HashInfo']    = $this->HashInfo($parameter['EncryptInfo']);
            return $parameter;
        }
        /**
         * 加密
         *
         */
        private function Encrypt($encryptInfo) {
            $tag = '';
            $encrypted = openssl_encrypt(http_build_query($encryptInfo), 'aes-256-gcm', trim($this->HashKey), 0, trim($this->HashIV), $tag);
            return trim(bin2hex($encrypted . ':::' . base64_encode($tag)));
        }
        /**
         * 解密
         */
        private function Decrypt(string $encryptStr = '') {
            list($encryptData, $tag) = explode(':::', hex2bin($encryptStr), 2);
            $encryptInfo = openssl_decrypt($encryptData, 'aes-256-gcm', trim($this->HashKey), 0, trim($this->HashIV), base64_decode($tag));
            parse_str($encryptInfo, $encryptArr);
            return $encryptArr;
        }
        /**
         * hash
         */
        private function HashInfo(string $encryptStr = '') {
            return strtoupper(hash('sha256', $this->HashKey.$encryptStr.$this->HashIV));
        }
        /**
         * 處理api回傳的結果
         * @ author    Yifan
         * @ dateTime 2022-08-26
         */
        private function ResultProcess($result) {
            $msg = '';
            if (is_array($result)) {
                $resultArr = $result;
            }
            else {
                $resultArr = json_decode($result, true);
                if (!is_array($resultArr)){
                    $msg = 'Result must be an array';
                    $this->writeLog($msg);
                    return ['success' => false, 'message' => $msg];
                }
            }
            if (isset($resultArr['EncryptInfo'])){
                if (isset($resultArr['HashInfo'])){
                    $chkHash = $this->HashInfo($resultArr['EncryptInfo']);
                    if ( $chkHash != $resultArr['HashInfo'] ) {
                        $msg = 'Hash mismatch';
                        $this->writeLog($msg);
                        return ['success' => false, 'message' => $msg];
                    }
                    $resultArr['EncryptInfo'] = $this->Decrypt($resultArr['EncryptInfo']);
                    return ['success' => true, 'message' => $resultArr];
                }
                else {
                    $msg = 'missing HashInfo';
                    $this->writeLog($msg);
                    return ['success' => false, 'message' => $msg];
                }
            }
            else {
                $msg = 'missing EncryptInfo';
                $this->writeLog($msg);
                return ['success' => false, 'message' => $msg];
            }
        }
        /**
         * Generate the payuni button link (POST method)
         *
         * @access public
         * @param mixed $order_id
         * @return string
         */
        function generate_payuni_form($order_id) {
            $order = wc_get_order($order_id);
            $payuni_args = $this->get_payuni_args($order);
            $payuni_gateway = $this->gateway;
            $payuni_args_array = array();
            foreach ($payuni_args as $key => $value) {
                $payuni_args_array[] = '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
            }

            return '<form id="payuni" name="payuni" action="' . $payuni_gateway . '" method="post" target="_top">' . implode('', $payuni_args_array) . '
                <input type="submit" class="button-alt" id="submit_payuni_payment_form" value="' . __('前往 統一金流 PAYUNi 支付頁面', 'payuni') . '" />
                </form>'. "<script>setTimeout(\"document.forms['payuni'].submit();\",\"0\")</script>";
        }

        /**
         * Output for the order received page.
         *
         * @access public
         * @return void
         */
        function receipt_page($order) {
            echo '<p>' . __('3秒後會自動跳轉到統一金流支付頁面，或者按下方按鈕直接前往<br>', 'payuni') . '</p>';
            echo $this->generate_payuni_form($order);
        }
        private function writeLog($msg = '', $with_input = true)
        {
            $file_path = __DIR__ .'/payuni_logs/'; // 檔案路徑
            if(! is_dir($file_path)) {
                return;
            }

            $file_name = 'payuni_' . date('Ymd') . '.txt';
            $file = $file_path . $file_name;
            $fp = fopen($file, 'a');
            $input = ($with_input) ? '|REQUEST:' . json_encode($_REQUEST) : '';
            $log_str = date('Y-m-d H:i:s') . '|' . $msg . $input . "\n";
            fwrite($fp, $log_str);
            fclose($fp);
            $this->clean_old_log($file_path);
        }

        private function clean_old_log($dir = '') {
            $del_date = date('Ymd', strtotime('-30 day'));
            $scan_dir = glob($dir . 'payuni_*.txt');
            foreach ($scan_dir as $value) {
                $date = explode('_', basename($value, '.txt'));
                if (strtotime($del_date) > strtotime($date[1])) {
                    unlink($value);
                }
            }
        }
        /**
         * Process the payment and return the result
         *
         * @access public
         * @param int $order_id
         * @return array
         */
        function process_payment($order_id) {
            global $woocommerce;
            $order = wc_get_order($order_id);

            // Empty awaiting payment session
            unset($_SESSION['order_awaiting_payment']);
            //$this->receipt_page($order_id);
            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url(true)
            );
        }

        /**
         * Payment form on checkout page
         *
         * @access public
         * @return void
         */
        function payment_fields() {
            if ($this->description)
                echo wpautop(wptexturize($this->description));
        }
    }
    /**
     * Add the gateway to WooCommerce
     *
     * @access public
     * @param array $methods
     * @package     WooCommerce/Classes/Payment
     * @return array
     */
    function add_payuni_gateway($methods) {
        $methods[] = 'WC_payuni';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_payuni_gateway');
}
?>