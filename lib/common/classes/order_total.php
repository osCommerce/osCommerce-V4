<?php

/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\classes;

class order_total extends modules\ModuleCollection {

    var $modules;
    var $readonly = ['ot_tax', 'ot_total', 'ot_subtotal', 'ot_due', 'ot_paid', 'ot_subtax', 'ot_refund'];
    protected $include_modules = [];
    private $manager;

// class constructor
    function __construct($reconfig, \common\services\OrderManager $manager)
    {
        global $language;

        if (defined('MODULE_ORDER_TOTAL_INSTALLED') && tep_not_null(MODULE_ORDER_TOTAL_INSTALLED)) {
            $this->modules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);
            \common\helpers\Translation::init('ordertotal');
            $this->manager = $manager;
            $builder = new \common\classes\modules\ModuleBuilder($manager);
            if (is_array($this->modules)) {
                foreach ($this->modules as $value) {
                    //$value = basename(str_replace('\\', '/', $value));
                    $class = substr($value, 0, strrpos($value, '.'));
                    $module = "\\common\\modules\\orderTotal\\{$class}";
                    if (!class_exists($module)) {
                        continue;
                    }
                    if (\frontend\design\Info::isTotallyAdmin()) {
                        defined('ONE_PAGE_CHECKOUT') or define('ONE_PAGE_CHECKOUT', 'True');
                        defined('ONE_PAGE_SHOW_TOTALS') or define('ONE_PAGE_SHOW_TOTALS', 'true');
                    }
                    $this->include_modules[$class] = $builder(['class' => $module]);
                    if (method_exists($this->include_modules[$class], 'config')) {
                        $config_array = array_merge(
                            array(
                                'ONE_PAGE_CHECKOUT' => defined('ONE_PAGE_CHECKOUT') ? ONE_PAGE_CHECKOUT : 'False',
                                'ONE_PAGE_SHOW_TOTALS' => defined('ONE_PAGE_SHOW_TOTALS') ? ONE_PAGE_SHOW_TOTALS : 'false',
                            ),
                            is_array($reconfig) ? $reconfig : array()
                        );
                        $this->include_modules[$class]->config($config_array);
                    }
                }
            }
        }
    }

    function getCustomValue($module){
        $replacing_value = -1;
        if (in_array('admin', $this->manager->getModulesVisibility())){
            $currencies = \Yii::$container->get('currencies');
            $cart = $this->manager->getCart();
            $currency = $this->manager->get('currency');
            if ((array_key_exists($module->code, $cart->totals) && tep_not_null($cart->totals[$module->code])) && (!in_array($module->code, $this->readonly) || $module->code == 'ot_tax' || $module->code == 'ot_paid')) {
                if ($cart->totals[$module->code]['value'] != '&nbsp;') {
                    if (is_array($cart->totals[$module->code]['value'])) {
                        $replacing_value = [
                                'in' => (float)$cart->totals[$module->code]['value']['in'],
                                'ex' => (float)$cart->totals[$module->code]['value']['ex'],
                            ];
                    }
                } else {
                    $replacing_value = 0;
                }
            }
        }
        return $replacing_value;
    }

    private $order_total_array = null;
    /*$processAgain = array['ot_due', 'ot_paid']*/
    function process($reProcess = []) {
        //static $order_total_array = null;
        if (is_null($this->order_total_array) || !empty($reProcess)) {
            $reProcessOn = false;
            if (is_null($this->order_total_array)) $this->order_total_array = [];
            $processinModules = $this->getEnabledModules();
            if (is_array($reProcess) && count($reProcess)) {
                $processinModules = $this->overwriteModules($reProcess);
                $reProcessOn = true;
            }

            $processing_order = array_flip(array_keys($processinModules));
            foreach($processinModules as $module){
                $module->setProcessingOrder($processing_order);
                if ($this->manager->hasCart() && $this->manager->getCart()->existHiddenModule($module->code))  continue;//shoul work only for manual edited modules
                if ($module->getVisibily($module->manager->getPlatformId(), $module->manager->getModulesVisibility())){
                    $replacing_value = $this->getCustomValue($module);
                    $module->process($replacing_value, (is_array($replacing_value)? true: false));
                    if ($reProcessOn) {
                        $this->unsetTotal($module->code);
                    }
                    for ($i = 0, $n = sizeof($module->output); $i < $n; $i++) {
                        if (tep_not_null($module->output[$i]['title']) && tep_not_null($module->output[$i]['text'])) {
                            $sort = $module->output[$i]['sort_order'] ?? $module->sort_order;
                            while (isset($this->order_total_array[$sort])) {
                                $sort++;
                            }
                            $this->order_total_array[$sort] = [
                                'code' => $module->code,
                                'title' => $module->output[$i]['title'],
                                'text' => $module->output[$i]['text'],
                                'value' => $module->output[$i]['value']??null,
                                'sort_order' => $sort,
                                'text_exc_tax' => $module->output[$i]['text_exc_tax']??null,
                                'text_inc_tax' => $module->output[$i]['text_inc_tax']??null,
                                'tax_class_id' => $module->output[$i]['tax_class_id']??null,
                                'value_exc_vat' => $module->output[$i]['value_exc_vat']??null,
                                'value_inc_tax' => $module->output[$i]['value_inc_tax']??null,
                            ];
                            
                        }
                    }
                }
            }
        }

        return $this->order_total_array;
    }
    
    private function unsetTotal(string $moduleCode ) {
        if (!empty($moduleCode) && is_array($this->order_total_array) && count($this->order_total_array)) {
            foreach ($this->order_total_array as $k => $v ) {
                if ($v['code'] == $moduleCode) {
                    unset($this->order_total_array[$k]);
                }
            }
        }
    }

    public function clearTotalCache() {
        $this->order_total_array = null;
    }


    private function overwriteModules($reProcess){
        $processinModules = [];
        if($reProcess && is_array($reProcess)){
            foreach($reProcess as $mod){
                $module = $this->get($mod);
                if ($module){
                    $processinModules[] = $module;
                }
            }
        }
        return $processinModules;
    }

    private $enabled = null;
    public function getEnabledModules() {
        //static $enabled = null;
        if (is_null($this->enabled)){
            $this->enabled = [];
            foreach ($this->include_modules as $class => $module){
                if (is_object($module) && $module->enabled){
                    $this->enabled[$class] = $module;
                }
            }
        }
        return $this->enabled;
    }

    public function get($class, $all = false){
        $enabled = $all ? $this->include_modules : $this->getEnabledModules();
        return $enabled[$class] ?? null;
    }

    function output() {
        $output_string = '';
        foreach($this->getEnabledModules() as $module){
            $size = sizeof($module->output);
            for ($i = 0; $i < $size; $i++) {
                $output_string .= '              <div class="row">' . "\n" .
                        '                <strong>' . $module->output[$i]['title'] . '</strong>&nbsp;' . "\n" .
                        '                <span>' . $module->output[$i]['text'] . '</span>' . "\n" .
                        '              </div>';
            }
        }

        return $output_string;
    }

// update_credit_account is called in checkout process on a per product basis. It's purpose
// is to decide whether each product in the cart should add something to a credit account.
// e.g. for the Gift Voucher it checks whether the product is a Gift voucher and then adds the amount
// to the Gift Voucher account.
// Another use would be to check if the product would give reward points and add these to the points/reward account.
//
    function update_credit_account($i) {
        if (MODULE_ORDER_TOTAL_INSTALLED) {
            foreach($this->getEnabledModules() as $module){
                if ( ($module->enabled ?? false) && ($module->credit_class ?? false) ) {
                    $module->update_credit_account($i);
                }
            }
        }
    }

    public function getCreditClasses(){
        $modules = [];
        foreach ($this->getEnabledModules() as $code => $module){
            if (isset($module->credit_class) && $module->credit_class){
                if ($module->getVisibily($module->manager->getPlatformId(), $module->manager->getModulesVisibility())){
                    $modules[$code] = true;
                }
            }
        }
        return $modules;
    }

// This function is called in checkout confirmation.
// It's main use is for credit classes that use the credit_selection() method. This is usually for
// entering redeem codes(Gift Vouchers/Discount Coupons). This function is used to validate these codes.
// If they are valid then the necessary actions are taken, if not valid we are returned to checkout payment
// with an error
//
    function collect_posts($limit_class = '', $post_data= []) {
        $result = [];
        if (defined('MODULE_ORDER_TOTAL_INSTALLED') && MODULE_ORDER_TOTAL_INSTALLED) {
            foreach($this->getEnabledModules() as $class => $module){
                if (($module->credit_class ?? false) || method_exists($module, 'collect_posts')) {
                    $post_var = 'c' . $module->code;
                    if (isset($post_data[$post_var])) {
                        if ($module->manager){
                            $module->manager->set($post_var, $post_data[$post_var]);
                        }
                    }
                    if (!empty($limit_class) && $limit_class != $class)
                        continue;
                    $response = $module->collect_posts($post_data);
                    if ($response) $result[$module->code] = $response;
                }
            }
        }
        return $result;
    }

// pre_confirmation_check is called on checkout confirmation. It's function is to decide whether the
// credits available are greater than the order total. If they are then a variable (credit_covers) is set to
// true. This is used to bypass the payment method. In other words if the Gift Voucher is more than the order
// total, we don't want to go to paypal etc.
//
    function pre_confirmation_check($order) {
        if (MODULE_ORDER_TOTAL_INSTALLED) {
            if (number_format($order->info['total_inc_tax'],6) <= 0){
                $this->manager->set('credit_covers', true);
            }else {
                $this->manager->remove('credit_covers');
            }
        }
    }

// this function is called in checkout process. it tests whether a decision was made at checkout payment to use
// the credit amount be applied aginst the order. If so some action is taken. E.g. for a Gift voucher the account
// is reduced the order total amount.
//
    function apply_credit() {
        if (MODULE_ORDER_TOTAL_INSTALLED) {
            foreach ($this->getEnabledModules() as $module){
                if (($module->credit_class ?? false)) {
                    $module->apply_credit();
                }
            }
        }
    }

// Called in checkout process to clear session variables created by each credit class module.
//
    function clear_posts() {
        if (MODULE_ORDER_TOTAL_INSTALLED) {
            foreach ($this->getEnabledModules() as $module){
                if (($module->credit_class ?? false)) {
                    $post_var = 'c' . $module->code;
                    $module->manager->remove($post_var);
                }
            }
        }
    }

// Called at various times. This function calulates the total value of the order that the
// credit will be appled aginst. This varies depending on whether the credit class applies
// to shipping & tax
//
    function get_order_total_main($class, $order_total) {
        //global $credit, $order;
//      if ($GLOBALS[$class]->include_tax == 'false') $order_total=$order_total-$order->info['tax'];
//      if ($GLOBALS[$class]->include_shipping == 'false') $order_total=$order_total-$order->info['shipping_cost'];
        return $order_total;
    }

// ICW ORDER TOTAL CREDIT CLASS/GV SYSTEM - END ADDITION

    function get_all_totals_list() {//admin - reconstruct

        if (MODULE_ORDER_TOTAL_INSTALLED) {



        }
    }

    function get_pos_totals_list() {
        $output = [];
        if (MODULE_ORDER_TOTAL_INSTALLED) {
            foreach ($this->getEnabledModules() as $module){
                if (is_array($module->output) && count($module->output)) {
                    for ($i = 0; $i < count($module->output); $i++) {
                        $i = 0;
                        if (in_array($module->code, ['ot_subtotal', 'ot_shipping','ot_coupon', 'ot_total'])) {
                            if ($module->code == 'ot_shipping' && $module->output[$i]['value_inc_tax'] == 0) {
                                continue;
                            }
                            $output[] = [
                                'title' => $this->getShortText($module->output[$i]['title'],15),
                                'exc' => $module->output[$i]['text_exc_tax'],
                                'exc' => $module->output[$i]['text_exc_tax'],
                                'inc' => $module->output[$i]['text_inc_tax'],
                                'value' => $module->output[$i]['value'],
                                'type' => ($module->code == 'ot_total' ? 1 : 0)
                            ];
                        }
                    }
                }
            }
        }
        return $output;
    }
    public function getShortText($str = '',$count = 0,$pattern ='..'){
        $text=strip_tags($str);
        if(mb_strlen($text, "UTF-8") > $count) {
            $text_cut = mb_substr($text, 0, $count, "UTF-8");
            $text_explode = explode(" ", $text_cut);

            unset($text_explode[count($text_explode) - 1]);

            $text_implode = implode(" ", $text_explode);

            return $text_implode.$pattern;
        }
        return $text;
    }
}
