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

use common\classes\modules\ModuleTotal;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;

class ot_shippingfee extends ModuleTotal {

    var $title, $output;

    protected $defaultTranslationArray = [
        'MODULE_ORDER_TOTAL_SHIPPINGFEE_TITLE' => 'Shipping Fee',
        'MODULE_ORDER_TOTAL_SHIPPINGFEE_DESCRIPTION' => 'Shipping Fee Module'
    ];

    public function __construct() {
        parent::__construct();

        $this->code = 'ot_shippingfee';
        $this->title = MODULE_ORDER_TOTAL_SHIPPINGFEE_TITLE;
        $this->description = MODULE_ORDER_TOTAL_SHIPPINGFEE_DESCRIPTION;
        if (!defined('MODULE_ORDER_TOTAL_SHIPPINGFEE_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->enabled = ((MODULE_ORDER_TOTAL_SHIPPINGFEE_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_SHIPPINGFEE_SORT_ORDER;

        $this->output = array();
    }

    public function process($replacing_value = -1, $visible = false) {

        $order = $this->manager->getOrderInstance();
        $currencies = \Yii::$container->get('currencies');
        $pass = false;
        if (MODULE_ORDER_TOTAL_SHIPPINGFEE_ALLOW_FEE == 'true') {
            switch (MODULE_ORDER_TOTAL_SHIPPINGFEE_DESTINATION) {
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

            if ($pass || $replacing_value != -1 || $visible) {
                //$tax = \common\helpers\Tax::get_tax_rate(MODULE_ORDER_TOTAL_SHIPPINGFEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
                //$tax_description = \common\helpers\Tax::get_tax_description(MODULE_ORDER_TOTAL_SHIPPINGFEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);

                $taxation = $this->getTaxValues(MODULE_ORDER_TOTAL_SHIPPINGFEE_TAX_CLASS, $order);

                $tax_class_id = $taxation['tax_class_id'];
                $tax = $taxation['tax'];
                $tax_description = $taxation['tax_description'];

                $platform_id = (int) $order->info['platform_id'];

                $os_amount = 0;

                $shipping = $order->info['shipping_class'];
                list($module, $method) = explode('_', $shipping);
                $check_query = tep_db_query("select * from shipping_fee where platform_id='" . $platform_id . "' and shipping_code='" . $module . "'");
                if (tep_db_num_rows($check_query) > 0) {
                    $check = tep_db_fetch_array($check_query);
                    $os_amount += ($check['fixed_prefix'] * $check['fixed_value']);
                    $os_amount += ($check['percent_prefix'] * $order->info['subtotal_exc_tax'] * ($check['percent_value'] / 100));
                }

                $os_amount = round($os_amount, 2);

                $tos_amount = $os_amount + \common\helpers\Tax::roundTax(\common\helpers\Tax::calculate_tax($os_amount, $tax));

                if ($replacing_value != -1) {
                    $cart = $this->manager->getCart();
                    if (is_array($replacing_value)) {
                        $os_amount = $replacing_value['ex'];
                        $tos_amount = $replacing_value['in'];
                    } else {
                        if ($visible) {
                            $replacing_value = [];
                            $replacing_value['ex'] = $os_amount;
                            $replacing_value['in'] = $tos_amount;
                        }
                    }
                    $cart->setTotalKey($this->code, $replacing_value);
                }

                $_tax = $tos_amount - $os_amount;
//                $tos_amount = round($tos_amount, 2);
                if ($tos_amount || $visible) {
                    $order->info['tax'] += $_tax;
                    $order->info['tax_groups']["$tax_description"] += $_tax;
                    $order->info['total'] += $tos_amount;
                    $order->info['total_inc_tax'] += $tos_amount;
                    $order->info['total_exc_tax'] += $os_amount;

                    parent::$adjusting += $currencies->format_clear($os_amount, true, $order->info['currency'], $order->info['currency_value']);

                    $this->output[] = array('title' => $this->title . ':',
                        'text' => $currencies->format(\common\helpers\Tax::add_tax($os_amount, $tax), true, $order->info['currency'], $order->info['currency_value']),
                        'value' => \common\helpers\Tax::add_tax($os_amount, $tax),
                        'text_exc_tax' => $currencies->format($os_amount, true, $order->info['currency'], $order->info['currency_value']),
                        'text_inc_tax' => $currencies->format($tos_amount, true, $order->info['currency'], $order->info['currency_value']),
                        // {{
                        'tax_class_id' => $tax_class_id,
                        'value_exc_vat' => $os_amount,
                        'value_inc_tax' => $tos_amount,
                            // }}
                    );
                }
            }
        }
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_SHIPPINGFEE_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_SHIPPINGFEE_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_SHIPPINGFEE_STATUS' =>
            array(
                'title' => 'Display Shipping Fee',
                'value' => 'true',
                'description' => 'Do you want to display the shipping Fee?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_SHIPPINGFEE_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '4',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
            'MODULE_ORDER_TOTAL_SHIPPINGFEE_ALLOW_FEE' =>
            array(
                'title' => 'Allow Shipping Fee',
                'value' => 'false',
                'description' => 'Do you want to allow shipping fees?',
                'sort_order' => '3',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_SHIPPINGFEE_DESTINATION' =>
            array(
                'title' => 'Attach Shipping Fee on Orders Made',
                'value' => 'both',
                'description' => 'Attach shipping fee for orders sent to the set destination.',
                'sort_order' => '6',
                'set_function' => 'tep_cfg_select_option(array(\'national\', \'international\', \'both\'), ',
            ),
            'MODULE_ORDER_TOTAL_SHIPPINGFEE_TAX_CLASS' =>
            array(
                'title' => 'Tax Class',
                'value' => '0',
                'description' => 'Use the following tax class on the shipping fee.',
                'sort_order' => '7',
                'use_function' => '\\common\\helpers\\Tax::get_tax_class_title',
                'set_function' => 'tep_cfg_pull_down_tax_classes(',
            ),
        );
    }

    public function install($platform_id) {
        $languages = \common\helpers\Language::get_languages(true);

        foreach ($languages as $language) {
            tep_db_query("INSERT IGNORE INTO `translation` (`language_id`, `translation_key`, `translation_entity`, `translation_value`, `hash`, `not_used`, `translated`) VALUES (" . (int) $language['id'] . ", 'MODULE_ORDER_TOTAL_SHIPPINGFEE_TITLE', 'ordertotal', 'Shipping Fee', '61ed2531f835efee51db618ed02d38b7', 0, 0);");
            tep_db_query("INSERT IGNORE INTO `translation` (`language_id`, `translation_key`, `translation_entity`, `translation_value`, `hash`, `not_used`, `translated`) VALUES (" . (int) $language['id'] . ", 'MODULE_ORDER_TOTAL_SHIPPINGFEE_DESCRIPTION', 'ordertotal', 'Shipping Fee Module', '587e4fa83986a375c9790527347bf326', 0, 0);");
            tep_db_query("INSERT IGNORE INTO `translation` (`language_id`, `translation_key`, `translation_entity`, `translation_value`, `hash`, `not_used`, `translated`) VALUES (" . (int) $language['id'] . ", 'TEXT_ADD_SHIPPING_METHOD', 'ordertotal', 'Add Shipping Method', 'be072ed22501eb28d7f2cb4690504c0e', 0, 0);");
            tep_db_query("INSERT IGNORE INTO `translation` (`language_id`, `translation_key`, `translation_entity`, `translation_value`, `hash`, `not_used`, `translated`) VALUES (" . (int) $language['id'] . ", 'TEXT_PERCENT', 'ordertotal', 'Percent', '7d10c2d3630c5b656e87653d704c53a0', 0, 0);");
            tep_db_query("INSERT IGNORE INTO `translation` (`language_id`, `translation_key`, `translation_entity`, `translation_value`, `hash`, `not_used`, `translated`) VALUES (" . (int) $language['id'] . ", 'TEXT_FIXED', 'ordertotal', 'Fixed', '7aaffbb7ed11c07e3ec64c3e10a515f7', 0, 0);");
        }
        tep_db_query("CREATE TABLE IF NOT EXISTS `shipping_fee` (
  `shipping_fee_id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_id` int(11) NOT NULL,
  `shipping_code` varchar(64) DEFAULT NULL,
  `percent_prefix` int(4) NOT NULL DEFAULT '1',
  `percent_value` double(11,2) NOT NULL DEFAULT '0.00',
  `fixed_prefix` int(4) NOT NULL DEFAULT '1',
  `fixed_value` decimal(11,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`shipping_fee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        return parent::install($platform_id);
    }

    function get_extra_params($platform_id) {
        $response = [];
        foreach ((new \yii\db\Query())
                ->from('shipping_fee')
                ->where('platform_id = ' . (int)$platform_id)
                ->all() as $methods) {
            unset($methods['shipping_fee_id']);
            unset($methods['platform_id']);
            $response['shipping_fee'][] = $methods;
        }
        return $response;
    }
    
    function set_extra_params($platform_id, $data) {
        \Yii::$app->db->createCommand('DELETE FROM shipping_fee WHERE platform_id='. $platform_id)->execute();
        if (isset($data['shipping_fee']) && is_array($data['shipping_fee'])) {
            foreach ($data['shipping_fee'] as $value) {
                $attr = (array)$value;
                $attr['platform_id'] = (int)$platform_id;
                $next_id_query = tep_db_query("select max(shipping_fee_id) as shipping_fee_id from shipping_fee");
                $next_id = tep_db_fetch_array($next_id_query);
                $new_id = $next_id['shipping_fee_id'] + 1;
                $attr['shipping_fee_id'] = $new_id;
                \Yii::$app->getDb()->createCommand()->insert('shipping_fee', $attr )->execute();
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
                        'shipping_code' => $method_value,
                    );
                    tep_db_perform('shipping_fee', $sql_data_array);
                    break;
                case 'del':
                    tep_db_query("delete from shipping_fee where shipping_fee_id = '" . (int) $method_value . "'");
                    break;
                default:
                    break;
            }
        }

        $updates = \Yii::$app->request->post('percent_prefix', array());
        if (is_array($updates)) {
            foreach ($updates as $key => $value) {
                tep_db_query("update shipping_fee set percent_prefix = '" . $value . "' where shipping_fee_id = '" . $key . "'");
            }
        }

        $updates = \Yii::$app->request->post('percent_value', array());
        if (is_array($updates)) {
            foreach ($updates as $key => $value) {
                tep_db_query("update shipping_fee set percent_value = '" . $value . "' where shipping_fee_id = '" . $key . "'");
            }
        }

        $updates = \Yii::$app->request->post('fixed_prefix', array());
        if (is_array($updates)) {
            foreach ($updates as $key => $value) {
                tep_db_query("update shipping_fee set fixed_prefix = '" . $value . "' where shipping_fee_id = '" . $key . "'");
            }
        }

        $updates = \Yii::$app->request->post('fixed_value', array());
        if (is_array($updates)) {
            foreach ($updates as $key => $value) {
                tep_db_query("update shipping_fee set fixed_value = '" . $value . "' where shipping_fee_id = '" . $key . "'");
            }
        }

        $html = '';
        if (!\Yii::$app->request->isAjax) {
            $html .= '<div id="modules_extra_params">';
        }

        \common\helpers\Translation::init('shipping');

        $directory_array = $this->directoryList();

        $modules_files = [];
        $avaiable_files = [];
        for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
            $file = $directory_array[$i];

            require_once(DIR_FS_CATALOG_MODULES . 'shipping/' . $file);

            $class = substr($file, 0, strrpos($file, '.'));
            if (class_exists($class) && is_subclass_of($class, "common\\classes\\modules\\ModuleShipping")) {
                $module = new $class;

                $modules_files[$module->code] = $module->title;
                $check_query = tep_db_query("select * from shipping_fee where platform_id='" . $platform_id . "' and shipping_code='" . $module->code . "'");
                if (tep_db_num_rows($check_query) == 0) {
                    $avaiable_files[] = array('id' => $module->code, 'text' => $module->title);
                }
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
        $shipping_modules_query = tep_db_query("select * from shipping_fee where platform_id='" . $platform_id . "' order by shipping_code");
        while ($shipping_modules = tep_db_fetch_array($shipping_modules_query)) {
            $html .= '<tr><td><span class="delMethod" onclick="delShipMethod(\'' . $shipping_modules['shipping_fee_id'] . '\')"></span></td><td>';
            $html .= $modules_files[$shipping_modules['shipping_code']];
            $html .= '</td><td>';
            $html .= TEXT_PERCENT . ':' . tep_draw_pull_down_menu('percent_prefix[' . $shipping_modules['shipping_fee_id'] . ']', $prefixes, $shipping_modules['percent_prefix'], 'style="width: 44px;"') . tep_draw_input_field('percent_value[' . $shipping_modules['shipping_fee_id'] . ']', $shipping_modules['percent_value']) . '<br>';
            $html .= TEXT_FIXED . ':' . tep_draw_pull_down_menu('fixed_prefix[' . $shipping_modules['shipping_fee_id'] . ']', $prefixes, $shipping_modules['fixed_prefix'], 'style="width: 44px;"') . tep_draw_input_field('fixed_value[' . $shipping_modules['shipping_fee_id'] . ']', $shipping_modules['fixed_value']) . '<br>';
            $html .= '</td></tr>';
        }
        $html .= '</table><br><br>';


        if (count($avaiable_files) > 0) {
            $html .= '<div>' . tep_draw_pull_down_menu('ship_method_code', $avaiable_files) . ' <span class="btn" onclick="return addShipMethod();">' . TEXT_ADD_SHIPPING_METHOD . '</span></div>';
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

    private function directoryList() {
        $directory_array = array();
        if ($dir = @dir(DIR_FS_CATALOG_MODULES . 'shipping/')) {
            while ($file = $dir->read()) {
                if (!is_dir(DIR_FS_CATALOG_MODULES . 'shipping/' . $file)) {
                    if (in_array(substr($file, strrpos($file, '.') + 1), array('php'))) {
                        $directory_array[] = $file;
                    }
                }
            }
            sort($directory_array);
            $dir->close();
        }
        return $directory_array;
    }

}