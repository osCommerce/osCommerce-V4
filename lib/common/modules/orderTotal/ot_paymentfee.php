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
namespace common\modules\orderTotal;

use yii\helpers\ArrayHelper;
use common\classes\modules\ModuleTotal;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;

class ot_paymentfee extends ModuleTotal {

    var $title, $output;

    protected $defaultTranslationArray = [
        'MODULE_ORDER_TOTAL_PAYMENTFEE_TITLE' => 'Payment Fee',
        'MODULE_ORDER_TOTAL_PAYMENTFEE_DESCRIPTION' => 'Payment Fee Module'
    ];

    public function __construct() {
        parent::__construct();

        $this->code = 'ot_paymentfee';
        $this->title = MODULE_ORDER_TOTAL_PAYMENTFEE_TITLE;
        $this->description = MODULE_ORDER_TOTAL_PAYMENTFEE_DESCRIPTION;
        if (!defined('MODULE_ORDER_TOTAL_PAYMENTFEE_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->enabled = ((MODULE_ORDER_TOTAL_PAYMENTFEE_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_PAYMENTFEE_SORT_ORDER;

        $this->output = array();
    }

    public function process($replacing_value = -1, $visible = false) {

        $pass = false;
        $order = $this->manager->getOrderInstance();
        $currencies = \Yii::$container->get('currencies');
        switch (MODULE_ORDER_TOTAL_PAYMENTFEE_DESTINATION) {
            case 'national':
                if ($this->delivery['country_id'] == STORE_COUNTRY)
                    $pass = true;
                break;
            case 'international':
                if ($this->delivery['country_id'] != STORE_COUNTRY)
                    $pass = true;
                break;
            case 'both':
                $pass = true;
                break;
            default:
                $pass = false;
                break;
        }

        if ($pass || $replacing_value != -1) {
            //$tax = \common\helpers\Tax::get_tax_rate(MODULE_ORDER_TOTAL_PAYMENTFEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
            //$tax_description = \common\helpers\Tax::get_tax_description(MODULE_ORDER_TOTAL_PAYMENTFEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);

            $taxation = $this->getTaxValues(MODULE_ORDER_TOTAL_PAYMENTFEE_TAX_CLASS, $order);

            $tax_class_id = $taxation['tax_class_id'];
            $tax = $taxation['tax'];
            $tax_description = $taxation['tax_description'];

            $platform_id = (int) ArrayHelper::getValue($order->info, 'platform_id');

            $of_amount = 0;

            $module = $this->manager->getPayment();
            $check_query = tep_db_query("select * from payment_fee where platform_id='" . $platform_id . "' and payment_code='" . tep_db_input($module) . "'");

            if (tep_db_num_rows($check_query) > 0) {
                $check = tep_db_fetch_array($check_query);
                $of_amount += ($check['fixed_prefix'] * $check['fixed_value']);
                $of_amount += ($check['percent_prefix'] * $order->info['total_exc_tax'] * ($check['percent_value'] / 100));
            }

            $of_amount = round($of_amount, 2);

            $tof_amount = $of_amount + \common\helpers\Tax::roundTax(\common\helpers\Tax::calculate_tax($of_amount, $tax));

            if ($replacing_value != -1) {
                $cart = $this->manager->getCart();
                if (is_array($replacing_value)) {
                    $of_amount = $replacing_value['ex'];
                    $tof_amount = $replacing_value['in'];
                } else {
                    $replacing_value = [];
                    $replacing_value['ex'] = $of_amount;
                    $replacing_value['in'] = $tof_amount;
                }
                $cart->setTotalKey($this->code, $replacing_value);
            }

            $_tax = $tof_amount - $of_amount;

//                $tof_amount = round($tof_amount, 2);
            if ($tof_amount || $visible) {
                $order->info['tax'] += $_tax;
                $order->info['tax_groups']["$tax_description"] += $_tax;
                $order->info['total'] += $tof_amount;
                $order->info['total_inc_tax'] += $tof_amount;
                $order->info['total_exc_tax'] += $of_amount;

                parent::$adjusting += $currencies->format_clear($of_amount, true, $order->info['currency'], $order->info['currency_value']);

                $moduleTitle = $this->manager->getPaymentCollection()->getConfirmationTitle();
                if (empty($moduleTitle) && is_object($this->manager->getPaymentCollection()->get($module))) {
                  $moduleTitle = $this->manager->getPaymentCollection()->get($module)->title;
                }

                $this->title = sprintf(defined('MODULE_ORDER_TOTAL_PAYMENTFEE__FRONTEND_TITLE') && !empty(MODULE_ORDER_TOTAL_PAYMENTFEE__FRONTEND_TITLE)?MODULE_ORDER_TOTAL_PAYMENTFEE__FRONTEND_TITLE:$this->title,
                    $moduleTitle
                    );

                $this->output[] = array('title' => $this->title . ':',
                    'text' => $currencies->format(\common\helpers\Tax::add_tax($of_amount, $tax), true, $order->info['currency'], $order->info['currency_value']),
                    'value' => \common\helpers\Tax::add_tax($of_amount, $tax),
                    'text_exc_tax' => $currencies->format($of_amount, true, $order->info['currency'], $order->info['currency_value']),
                    'text_inc_tax' => $currencies->format($tof_amount, true, $order->info['currency'], $order->info['currency_value']),
                    // {{
                    'tax_class_id' => $tax_class_id,
                    'value_exc_vat' => $of_amount,
                    'value_inc_tax' => $tof_amount,
                        // }}
                );
            }
        }
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_PAYMENTFEE_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_PAYMENTFEE_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_PAYMENTFEE_STATUS' =>
            array(
                'title' => 'Display Payment Fee',
                'value' => 'true',
                'description' => 'Do you want to display the payment fee?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_PAYMENTFEE_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '4',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
            'MODULE_ORDER_TOTAL_PAYMENTFEE_DESTINATION' =>
            array(
                'title' => 'Attach Payment Fee On Orders Made',
                'value' => 'both',
                'description' => 'Attach payment fee for orders sent to the set destination.',
                'sort_order' => '6',
                'set_function' => 'tep_cfg_select_option(array(\'national\', \'international\', \'both\'), ',
            ),
            'MODULE_ORDER_TOTAL_PAYMENTFEE_TAX_CLASS' =>
            array(
                'title' => 'Tax Class',
                'value' => '0',
                'description' => 'Use the following tax class on the payment fee.',
                'sort_order' => '7',
                'use_function' => '\\common\\helpers\\Tax::get_tax_class_title',
                'set_function' => 'tep_cfg_pull_down_tax_classes(',
            ),
        );
    }

    public function install($platform_id) {
        $languages = \common\helpers\Language::get_languages(true);

        tep_db_query("delete from `translation` where translation_key='MODULE_ORDER_TOTAL_PAYMENTFEE_ALLOW_FEE_TITLE'");
        tep_db_query("delete from `translation` where translation_key='MODULE_ORDER_TOTAL_PAYMENTFEE_ALLOW_FEE_DESCRIPTION'");

        foreach ($languages as $language) {
            tep_db_query("INSERT IGNORE INTO `translation` (`language_id`, `translation_key`, `translation_entity`, `translation_value`, `hash`, `not_used`, `translated`) VALUES (" . (int) $language['id'] . ", 'MODULE_ORDER_TOTAL_PAYMENTFEE_TITLE', 'ordertotal', 'Payment Fee', '8f89d8c0ea4d24044b486e158286f9b0', 0, 0);");
            tep_db_query("INSERT IGNORE INTO `translation` (`language_id`, `translation_key`, `translation_entity`, `translation_value`, `hash`, `not_used`, `translated`) VALUES (" . (int) $language['id'] . ", 'MODULE_ORDER_TOTAL_PAYMENTFEE__FRONTEND_TITLE', 'ordertotal', '%s Fee', '', 0, 0);");
            tep_db_query("INSERT IGNORE INTO `translation` (`language_id`, `translation_key`, `translation_entity`, `translation_value`, `hash`, `not_used`, `translated`) VALUES (" . (int) $language['id'] . ", 'MODULE_ORDER_TOTAL_PAYMENTFEE_DESCRIPTION', 'ordertotal', 'Payment Fee Module', '104133bbb99ed8997600e0f4fb0b1c9e', 0, 0);");
            tep_db_query("INSERT IGNORE INTO `translation` (`language_id`, `translation_key`, `translation_entity`, `translation_value`, `hash`, `not_used`, `translated`) VALUES (" . (int) $language['id'] . ", 'TEXT_ADD_PAYMENT_METHOD', 'ordertotal', 'Add Payment Method', 'e1794e651bb6511baa6dcf4145514bc2', 0, 0);");
            tep_db_query("INSERT IGNORE INTO `translation` (`language_id`, `translation_key`, `translation_entity`, `translation_value`, `hash`, `not_used`, `translated`) VALUES (" . (int) $language['id'] . ", 'TEXT_PERCENT', 'ordertotal', 'Percent', '7d10c2d3630c5b656e87653d704c53a0', 0, 0);");
            tep_db_query("INSERT IGNORE INTO `translation` (`language_id`, `translation_key`, `translation_entity`, `translation_value`, `hash`, `not_used`, `translated`) VALUES (" . (int) $language['id'] . ", 'TEXT_FIXED', 'ordertotal', 'Fixed', '7aaffbb7ed11c07e3ec64c3e10a515f7', 0, 0);");
        }
        tep_db_query("CREATE TABLE IF NOT EXISTS `payment_fee` (
  `payment_fee_id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_id` int(11) NOT NULL,
  `payment_code` varchar(64) DEFAULT NULL,
  `percent_prefix` int(4) NOT NULL DEFAULT '1',
  `percent_value` double(11,2) NOT NULL DEFAULT '0.00',
  `fixed_prefix` int(4) NOT NULL DEFAULT '1',
  `fixed_value` decimal(11,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`payment_fee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        return parent::install($platform_id);
    }

    function get_extra_params($platform_id) {
        $response = [];
        foreach ((new \yii\db\Query())
                ->from('payment_fee')
                ->where('platform_id = ' . (int)$platform_id)
                ->all() as $methods) {
            unset($methods['payment_fee_id']);
            unset($methods['platform_id']);
            $response['payment_fee'][] = $methods;
        }
        return $response;
    }
    
    function set_extra_params($platform_id, $data) {
        \Yii::$app->db->createCommand('DELETE FROM payment_fee WHERE platform_id='. $platform_id)->execute();
        if (isset($data['payment_fee']) && is_array($data['payment_fee'])) {
            foreach ($data['payment_fee'] as $value) {
                $attr = (array)$value;
                $attr['platform_id'] = (int)$platform_id;
                $next_id_query = tep_db_query("select max(payment_fee_id) as payment_fee_id from payment_fee");
                $next_id = tep_db_fetch_array($next_id_query);
                $new_id = $next_id['payment_fee_id'] + 1;
                $attr['payment_fee_id'] = $new_id;
                \Yii::$app->getDb()->createCommand()->insert('payment_fee', $attr )->execute();
            }
        }
    }
    
    public function extra_params() {
        $platform_id = (int) \Yii::$app->request->get('platform_id');
        if ($platform_id == 0) {
            $platform_id = (int) \Yii::$app->request->post('platform_id');
        }

        $method_action = \Yii::$app->request->post('action', '');
        $method_value = \Yii::$app->request->post('id', '');
        if (!empty($method_action)) {
            switch ($method_action) {
                case 'add':
                    $sql_data_array = array(
                        'platform_id' => $platform_id,
                        'payment_code' => $method_value,
                    );
                    tep_db_perform('payment_fee', $sql_data_array);
                    break;
                case 'del':
                    tep_db_query("delete from payment_fee where payment_fee_id = '" . (int) $method_value . "'");
                    break;
                default:
                    break;
            }
        }

        $updates = \Yii::$app->request->post('percent_prefix', array());
        if (is_array($updates)) {
            foreach ($updates as $key => $value) {
                tep_db_query("update payment_fee set percent_prefix = '" . $value . "' where payment_fee_id = '" . $key . "'");
            }
        }

        $updates = \Yii::$app->request->post('percent_value', array());
        if (is_array($updates)) {
            foreach ($updates as $key => $value) {
                tep_db_query("update payment_fee set percent_value = '" . $value . "' where payment_fee_id = '" . $key . "'");
            }
        }

        $updates = \Yii::$app->request->post('fixed_prefix', array());
        if (is_array($updates)) {
            foreach ($updates as $key => $value) {
                tep_db_query("update payment_fee set fixed_prefix = '" . $value . "' where payment_fee_id = '" . $key . "'");
            }
        }

        $updates = \Yii::$app->request->post('fixed_value', array());
        if (is_array($updates)) {
            foreach ($updates as $key => $value) {
                tep_db_query("update payment_fee set fixed_value = '" . $value . "' where payment_fee_id = '" . $key . "'");
            }
        }

        $html = '';
        if (!\Yii::$app->request->isAjax) {
            $html .= '<div id="modules_extra_params">';
        }

        \common\helpers\Translation::init('payment');

        if (!is_object($this->manager)){
            $this->manager = \common\services\OrderManager::loadManager();
        }

        $modules_files = [];
        $avaiable_files = [];
        foreach($this->manager->getPaymentCollection()->getEnabledModules() as $module){
            $modules_files[$module->code] = $module->title;
            $check_query = tep_db_query("select * from payment_fee where platform_id='" . $platform_id . "' and payment_code='" . $module->code . "'");
            if (tep_db_num_rows($check_query) == 0) {
                $avaiable_files[] = array('id' => $module->code, 'text' => $module->title);
            }
        }

        $prefixes = [
            [
                'id' => '1',
                'text' => '+',
            ],
            [
                'id' => '-1',
                'text' => '-',
            ],
        ];
        $html .= '<table width="100%" class="selected-methods">';
        $html .= '<tr><th width="10%">' . TABLE_HEADING_ACTION . '</th><th width="20%">' . TABLE_HEADING_TITLE . '</th><th width="70%">' . IMAGE_DETAILS . '</th></tr>';
        $payment_modules_query = tep_db_query("select * from payment_fee where platform_id='" . $platform_id . "' order by payment_code");
        while ($payment_modules = tep_db_fetch_array($payment_modules_query)) {
            $html .= '<tr><td><span class="delMethod" onclick="delShipMethod(\'' . $payment_modules['payment_fee_id'] . '\')"></span></td><td>';
            $html .= $modules_files[$payment_modules['payment_code']];
            $html .= '</td><td>';
            $html .= TEXT_PERCENT . ':' . tep_draw_pull_down_menu('percent_prefix[' . $payment_modules['payment_fee_id'] . ']', $prefixes, $payment_modules['percent_prefix'], 'style="width: 44px;"') . tep_draw_input_field('percent_value[' . $payment_modules['payment_fee_id'] . ']', $payment_modules['percent_value']) . '<br>';
            $html .= TEXT_FIXED . ':' . tep_draw_pull_down_menu('fixed_prefix[' . $payment_modules['payment_fee_id'] . ']', $prefixes, $payment_modules['fixed_prefix'], 'style="width: 44px;"') . tep_draw_input_field('fixed_value[' . $payment_modules['payment_fee_id'] . ']', $payment_modules['fixed_value']) . '<br>';
            $html .= '</td></tr>';
        }
        $html .= '</table><br><br>';


        if (count($avaiable_files) > 0) {
            $html .= '<div>' . tep_draw_pull_down_menu('ship_method_code', $avaiable_files) . ' <span class="btn" onclick="return addShipMethod();">' . TEXT_ADD_PAYMENT_METHOD . '</span></div>';
        }

        if (!\Yii::$app->request->isAjax) {
            $html .= '</div>';

            $html .= '<script type="text/javascript">
function delShipMethod(id) {
    $.post("' . tep_href_link('modules/extra-params') . '", {"set": "ordertotal", "module": "' . $this->code . '", "platform_id": "' . (int) $platform_id . '", "action": "del", "id": id}, function(data, status) {
        if (status == "success") {
            $(\'#modules_extra_params\').html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
function addShipMethod() {
var id = $(\'select[name="ship_method_code"]\').val();
    $.post("' . tep_href_link('modules/extra-params') . '", {"set": "ordertotal", "module": "' . $this->code . '", "platform_id": "' . (int) $platform_id . '", "action": "add", "id": id}, function(data, status) {
        if (status == "success") {
            $(\'#modules_extra_params\').html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
</script>';
        }

        return $html;
    }

}