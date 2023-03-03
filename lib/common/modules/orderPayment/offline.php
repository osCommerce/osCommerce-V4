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

  namespace common\modules\orderPayment;

  use common\classes\modules\ModulePayment;
  use common\classes\modules\ModuleStatus;
  use common\classes\modules\ModuleSortOrder;


class offline extends ModulePayment {
    var $code, $title, $description, $enabled;

    protected $defaultTranslationArray = [
        'MODULE_PAYMENT_OFFLINE_TEXT_TITLE' => 'Offline payments',
        'MODULE_PAYMENT_OFFLINE_TEXT_DESCRIPTION' => 'Available payments'
    ];

    function __construct() {
        parent::__construct();

        $this->code = 'offline';
        $this->title = MODULE_PAYMENT_OFFLINE_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_OFFLINE_TEXT_DESCRIPTION;
        if (!defined('MODULE_PAYMENT_OFFLINE_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->sort_order = MODULE_PAYMENT_OFFLINE_SORT_ORDER;
        $this->enabled = ((MODULE_PAYMENT_OFFLINE_STATUS == 'True') ? true : false);

        if ((int)MODULE_PAYMENT_OFFLINE_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_OFFLINE_ORDER_STATUS_ID;
        }

        $this->update_status();
    }

    function update_status() {

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_OFFLINE_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_OFFLINE_ZONE . "' and zone_country_id = '" . $this->delivery['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $this->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }

      if ($this->enabled == true) {
          if ($this->manager ){
            $this->enabled = $this->manager->isShippingNeeded() ? true : false;
          }
      }
    }

    private function _getPlatformId(){
        $platform_id = (int)$this->manager->getPlatformId();
        if ($platform_id == 0 && defined('PLATFORM_ID')) {
            $platform_id = PLATFORM_ID;
        }
        return $platform_id;
    }

    public function getTitle($method = '') {
        global $languages_id;
        $code = explode('_', $method);
        if (isset($code[1])) {
            $platform_id = $this->_getPlatformId();
            $methods_query = tep_db_query("select * from payment_offline where " . ($platform_id? "platform_id='".$platform_id."'" : " 1 ") . " and language_id='" . $languages_id . "' and payment_offline_id='" . (int)$code[1] . "' order by sort_order");
            while ($methods_fetch = tep_db_fetch_array($methods_query)) {
                return $methods_fetch['payment_offline_title'];
            }
        }
        return $this->title;
    }

    function selection() {
        global $languages_id;
        $platform_id = $this->_getPlatformId();
        $methods = [];
        $methods_query = tep_db_query("select * from payment_offline where platform_id='" . $platform_id . "' and language_id='" . $languages_id . "' and status='1' order by sort_order");
        while ($methods_fetch = tep_db_fetch_array($methods_query)) {
            $methods[] = [
                'id' => $this->code . '_' . $methods_fetch['payment_offline_id'],
                'module' => $methods_fetch['payment_offline_title'],
            ];
        }
        return array(
        'id' => $this->code,
        'module' => $this->description,
        'methods' => $methods,
        );
    }

    public function install($platform_id) {
        tep_db_query("CREATE TABLE IF NOT EXISTS `payment_offline` (
  `payment_offline_id` int(11) NOT NULL DEFAULT '0',
  `platform_id` int(11) NOT NULL DEFAULT '0',
  `language_id` int(11) NOT NULL DEFAULT '0',
  `payment_offline_title` varchar(64) DEFAULT NULL,
  `payment_offline_description` TEXT DEFAULT NULL,
  `sort_order` tinyint(1) NOT NULL DEFAULT '0',
  `status` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        return parent::install($platform_id);
    }

    function confirmation() {
        global $languages_id;

        $description = '';
        $code = explode('_', $this->manager->getPayment());
        if (isset($code[1])) {
            $platform_id = $this->_getPlatformId();
            $methods_query = tep_db_query("select * from payment_offline where platform_id='" . $platform_id . "' and language_id='" . $languages_id . "' and payment_offline_id='" . (int)$code[1] . "' order by sort_order");
            while ($methods_fetch = tep_db_fetch_array($methods_query)) {
                $description = $methods_fetch['payment_offline_description'];
                break;
            }
        }


        $confirmation = [
            'title' => $description,
        ];

        return $confirmation;
    }

    public function configure_keys(){
      return array(
        'MODULE_PAYMENT_OFFLINE_STATUS' => array (
          'title' => 'OFFLINE Enable Module',
          'value' => 'True',
          'description' => 'Do you want to accept OFFLINE payments?',
          'sort_order' => '1',
          'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        ),
        'MODULE_PAYMENT_OFFLINE_ZONE' => array(
          'title' => 'OFFLINE Payment Zone',
          'value' => '0',
          'description' => 'If a zone is selected, only enable this payment method for that zone.',
          'sort_order' => '2',
          'use_function' => '\\common\\helpers\\Zones::get_zone_class_title',
          'set_function' => 'tep_cfg_pull_down_zone_classes(',
        ),
        'MODULE_PAYMENT_OFFLINE_ORDER_STATUS_ID' => array (
          'title' => 'OFFLINE Order Status',
          'value' => '0',
          'description' => 'Set the status of orders made with this payment module to this value',
          'sort_order' => '0',
          'set_function' => 'tep_cfg_pull_down_order_statuses(',
          'use_function' => '\\common\\helpers\\Order::get_order_status_name',
        ),
        'MODULE_PAYMENT_OFFLINE_SORT_ORDER' => array (
          'title' => 'OFFLINE Sort order of display.',
          'value' => '0',
          'description' => 'Sort order of OFFLINE display. Lowest is displayed first.',
          'sort_order' => '0',
        ),
      );
  }

  public function describe_status_key()
  {
    return new ModuleStatus('MODULE_PAYMENT_OFFLINE_STATUS', 'True', 'False');
  }

  public function describe_sort_key()
  {
    return new ModuleSortOrder('MODULE_PAYMENT_OFFLINE_SORT_ORDER');
  }

    function get_extra_params($platform_id) {
        $response = [];
        foreach ((new \yii\db\Query())
                ->from('payment_offline')
                ->where('platform_id = ' . (int)$platform_id)
                ->all() as $methods) {
            unset($methods['payment_offline_id']);
            unset($methods['platform_id']);
            $response['payment_offline'][] = $methods;
        }
        return $response;
    }
    
    function set_extra_params($platform_id, $data) {
        \Yii::$app->db->createCommand('DELETE FROM payment_offline WHERE platform_id='. $platform_id)->execute();
        if (isset($data['payment_offline']) && is_array($data['payment_offline'])) {
            foreach ($data['payment_offline'] as $value) {
                $attr = (array)$value;
                $attr['platform_id'] = (int)$platform_id;
                $next_id_query = tep_db_query("select max(payment_offline_id) as payment_offline_id from payment_offline");
                $next_id = tep_db_fetch_array($next_id_query);
                $new_id = $next_id['payment_offline_id'] + 1;
                $attr['payment_offline_id'] = $new_id;
                \Yii::$app->getDb()->createCommand()->insert('payment_offline', $attr )->execute();
            }
        }
    }
    
    public function extra_params() {

        global $languages_id;
        $languages = \common\helpers\Language::get_languages();

        $platform_id = (int) \Yii::$app->request->get('platform_id');
        if ($platform_id == 0) {
            $platform_id = (int) \Yii::$app->request->post('platform_id');
        }

        $method_action = \Yii::$app->request->post('action', '');
        $method_value = \Yii::$app->request->post('id', '');
        if (!empty($method_action)) {
            switch ($method_action) {
                case 'add':
                    $next_id_query = tep_db_query("select max(payment_offline_id) as payment_offline_id from payment_offline");
                    $next_id = tep_db_fetch_array($next_id_query);
                    $payment_offline_id = $next_id['payment_offline_id'] + 1;
                    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
                        $sql_data_array = array(
                            'payment_offline_id' => $payment_offline_id,
                            'platform_id' => $platform_id,
                            'language_id' => $languages[$i]['id'],
                            'payment_offline_title' => '',
                            'payment_offline_description' => '',
                            'sort_order' => $payment_offline_id,
                            'status' => 0,
                        );
                        tep_db_perform('payment_offline', $sql_data_array);
                    }
                    break;
                case 'del':
                    tep_db_query("delete from payment_offline where payment_offline_id = '" . (int) $method_value . "'");
                    break;
                default:
                    break;
            }
        }

        $status = \Yii::$app->request->post('status');
        $payment_offline_title = \Yii::$app->request->post('payment_offline_title');
        $payment_offline_description = \Yii::$app->request->post('payment_offline_description');
        $sort_order = \Yii::$app->request->post('sort_order');
        $options_query = tep_db_query("select * from payment_offline where platform_id='" . $platform_id . "'");
        while ($options = tep_db_fetch_array($options_query)) {
            if (isset($status[$options['payment_offline_id']])) {
                tep_db_query("update payment_offline set status = '" . (int)$status[$options['payment_offline_id']] . "' where payment_offline_id = '" . (int)$options['payment_offline_id'] . "' and platform_id='" . $platform_id . "'");
            }
            if (isset($payment_offline_title[$options['payment_offline_id']][$options['language_id']])) {
                tep_db_query("update payment_offline set payment_offline_title = '" . $payment_offline_title[$options['payment_offline_id']][$options['language_id']] . "' where payment_offline_id = '" . (int)$options['payment_offline_id'] . "' and language_id='" . (int)$options['language_id'] . "' and platform_id='" . $platform_id . "'");
            }
            if (isset($payment_offline_description[$options['payment_offline_id']][$options['language_id']])) {
                tep_db_query("update payment_offline set payment_offline_description = '" . $payment_offline_description[$options['payment_offline_id']][$options['language_id']] . "' where payment_offline_id = '" . (int)$options['payment_offline_id'] . "' and language_id='" . (int)$options['language_id'] . "' and platform_id='" . $platform_id . "'");
            }
            if (isset($sort_order[$options['payment_offline_id']])) {
                tep_db_query("update payment_offline set sort_order = '" . (int)$sort_order[$options['payment_offline_id']] . "' where payment_offline_id = '" . (int)$options['payment_offline_id'] . "' and platform_id='" . $platform_id . "'");
            }
        }

        $html = '';
        if (!\Yii::$app->request->isAjax) {
            $html .= '<div id="modules_extra_params">';
        }


        $html .= '<table width="100%" class="selected-methods">';
        $html .= '<tr><th width="10%">'.TABLE_HEADING_ACTION.'</th><th width="10%">'.TABLE_HEADING_STATUS.'</th><th width="20%">'.TABLE_HEADING_TITLE.'</th><th width="60%">'.IMAGE_DETAILS.'</th><th width="10%">' . TEXT_SORT_ORDER . '</th></tr>';
        $options_query = tep_db_query("select * from payment_offline where language_id = '" . (int)$languages_id . "' and platform_id='" . $platform_id . "' order by sort_order,payment_offline_id");
        while ($options = tep_db_fetch_array($options_query)) {
            $html .= '<tr><td><span class="delMethod" onclick="delPayMethod(\'' . $options['payment_offline_id'] . '\')"></span></td><td>';
            $html .= '<input type="checkbox" class="uniform" name="status[' . $options['payment_offline_id'] . ']" value="1" ' . ($options['status'] == 1 ? 'checked' : '') . '></td><td>';
            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
              $options_value_query = tep_db_query("select payment_offline_title from payment_offline where payment_offline_id = '" . $options['payment_offline_id'] . "' and language_id = '" . (int)$languages[$i]['id'] . "' and platform_id='" . $platform_id . "'");
              $options_value = tep_db_fetch_array($options_value_query);
              $html .= $languages[$i]['image'] . '&nbsp;<input type="text" name="payment_offline_title[' . $options['payment_offline_id'] . '][' . $languages[$i]['id'] . ']" value="' . $options_value['payment_offline_title'] . '">' . '<br><br>';
            }
            $html .= '</td><td>';
            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                $options_value_query = tep_db_query("select payment_offline_description from payment_offline where payment_offline_id = '" . $options['payment_offline_id'] . "' and language_id = '" . (int)$languages[$i]['id'] . "' and platform_id='" . $platform_id . "'");
                $options_value = tep_db_fetch_array($options_value_query);
                $html .= $languages[$i]['image'] . '&nbsp;<textarea name="payment_offline_description[' . $options['payment_offline_id'] . '][' . $languages[$i]['id'] . ']" rows="2" cols="74">' . $options_value['payment_offline_description'] . '</textarea><br>';
            }

            $html .= '</td><td><input type="text" name="sort_order[' . $options['payment_offline_id'] . ']" value="' . $options['sort_order'] . '">';
            $html .= '</td></tr>';
        }
        $html .= '<tr><td><span class="addMethod" onclick="return addPayMethod();"></span></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
        $html .= '</table><br><br>';


        if (!\Yii::$app->request->isAjax) {
            $html .= '</div>';

            $html .= '<script type="text/javascript">
function delPayMethod(id) {
    $.post("' . tep_href_link('modules/extra-params') . '", {"set": "payment", "module": "' . $this->code . '", "platform_id": "' . (int) $platform_id . '", "action": "del", "id": id}, function(data, status) {
        if (status == "success") {
            $(\'#modules_extra_params\').html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
function addPayMethod() {
    $.post("' . tep_href_link('modules/extra-params') . '", {"set": "payment", "module": "' . $this->code . '", "platform_id": "' . (int) $platform_id . '", "action": "add"}, function(data, status) {
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