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

namespace common\classes\modules;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;
use common\modules\orderShipping\np;
use common\modules\orderTotal\ot_shipping;
require_once __DIR__ . '/VersionTrait.php'; // thanks for require Module in configure.php

#[\AllowDynamicProperties]
abstract class Module{

    use VersionTrait;
/**
 * @var \common\services\OrderManager $manager
 */
    public $manager;
    public $code;
    public $sort_order = 0;

    protected $countries = [''];
    protected $visibility = [];

    protected $defaultTranslationArray = [];
    protected $encrypted_keys = [];

    public function __construct()
    {
        $this->_init();
    }
    
    public static function getDescription() {
        return '';
    }

    protected function _init()
    {
        foreach ($this->defaultTranslationArray as $define => $translation) {
            if (!defined($define)) {
                define($define, $translation);
            }
        }
    }

    public function getTitle($method = '') {
        return $this->title;
    }

    public function check( $platform_id ) {
    $keys = $this->keys();
    if ( count($keys)==0 || ((int)$platform_id==0 && !$this->isExtension)) return 0;

    $check_keys_r = tep_db_query(
      "SELECT configuration_key ".
      "FROM " . TABLE_PLATFORMS_CONFIGURATION . " ".
      "WHERE configuration_key IN('".implode("', '",array_map('tep_db_input',$keys))."') AND platform_id='".(int)$platform_id."'"
    );
    $installed_keys = array();
    while( $check_key = tep_db_fetch_array($check_keys_r) ) {
      $installed_keys[$check_key['configuration_key']] = $check_key['configuration_key'];
    }

    $check_status = isset($installed_keys[$keys[0]])?1:0;

    $install_keys = false;
    foreach( $keys as $idx=>$module_key ) {
      if ( !isset($installed_keys[$module_key]) && $check_status ) {
        // missing key
        if ( !is_array($install_keys) ) $install_keys = $this->get_install_keys($platform_id);
        $this->add_config_key($platform_id, $module_key, $install_keys[$module_key]);
      }
    }

    return $check_status;
  }

  public function install( $platform_id ) {
    $keys = $this->get_install_keys($platform_id);
    if ( count($keys)==0 || ((int)$platform_id==0 && !$this->isExtension) ) return false;

    foreach($keys as $key=>$data) {
      $this->add_config_key($platform_id, $key, $data);
    }
    if (method_exists($this, 'configure_keys_platforms')) {
        $platformKeys = $this->configure_keys_platforms();
        if (is_array($platformKeys) && !empty($platformKeys)) {
            $platformValues = \common\models\PlatformsConfiguration::find()
                    ->where(['configuration_key' => array_keys($platformKeys)])
                    ->indexBy(function($row) {
                        return $row['platform_id'].$row['configuration_key'];
                    })
                    ->asArray()
                    ->all();
            $platforms = \common\classes\platform::getList(false);
            foreach ($platformKeys as $key => $data) {
                foreach ($platforms as $platformData) {
                    $platform_id = $platformData['id'];
                    if (!isset($platformValues[$platform_id . $key])) {
                        $this->add_config_key($platform_id, $key, $data);
                    }
                }
            }
        }
    }

    $installed = self::getInstalled();
    if (empty($installed) || empty($installed->version_db)) {
        \common\helpers\Modules::changeModule($this->code, 'install', [], self::getType(), $platform_id);
    } else {
        $this->upgrade();
    }

    if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog')) {
        $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance();
        ($logUniversal
            ->setRelation($this->code)
            ->setType($logUniversal::ULT_EXTENSION_INSTALL)
            ->setBeforeArray([$platform_id => 0])
            ->setAfterArray([$platform_id => 1])
            ->doSave(true)
        );
        unset($logUniversal);
    }
  }

  protected function add_config_key($platform_id, $key, $data )
  {
    $sql_data = array(
      'platform_id' => (int)$platform_id,
      'configuration_key' => $key,
      'configuration_title' => isset($data['title'])?$data['title']:'',
      'configuration_value' => isset($data['value'])?$data['value']:'',
      'configuration_description' => isset($data['description'])?$data['description']:'',
      'configuration_group_id' => isset($data['group_id'])?$data['group_id']:'6',
      'sort_order' => isset($data['sort_order'])?$data['sort_order']:'0',
      'date_added' => 'now()',
    );
    if ( isset($data['use_function']) ) {
      $sql_data['use_function'] = $data['use_function'];
    }
    if ( isset($data['set_function']) ) {
      $sql_data['set_function'] = $data['set_function'];
    }
    $model = \common\models\PlatformsConfiguration::findOne(['platform_id' => (int)$platform_id, 'configuration_key' => $key]);
    if (empty($model))  {
        $model = new \common\models\PlatformsConfiguration();
        $model->loadDefaultValues();
    }
    $model->setAttributes($sql_data, false);
    $model->save(false);
  }

  public function remove($platform_id) {
    $keys = $this->keys();

    if ($this->userConfirmedDropDatatables ?? false) {
        \common\helpers\Modules::changeModule($this->code, 'remove_drop', [], self::getType(), $platform_id);
    } else {
        \common\helpers\Modules::changeModule($this->code, 'remove', [], self::getType(), $platform_id);
    }


    if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog')) {
        $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance();
        ($logUniversal
            ->setRelation($this->code)
            ->setType($logUniversal::ULT_EXTENSION_REMOVE)
            ->setBeforeArray(\common\models\PlatformsConfiguration::find()
                ->select(['configuration_value', 'platform_id', 'configuration_key'])
                ->where(['IN', 'configuration_key', $keys])
                ->indexBy(function($record) {
                    return ($record['platform_id'] . '|' . $record['configuration_key']);
                })
                ->asArray(true)->column()
            )
        );
    }

    if ( count($keys)>0 && ((int)$platform_id!=0 || isset($this->isExtension) )) {
      tep_db_query(
        "DELETE FROM ".TABLE_PLATFORMS_CONFIGURATION." ".
        "WHERE platform_id='".(int)$platform_id."' AND configuration_key IN('".implode("', '",$keys)."')"
      );
    }

    if (isset($logUniversal)) {
        ($logUniversal
            ->setAfterArray([])
            ->doSave(true)
        );
        unset($logUniversal);
    }
  }

  function keys(){
    return array_keys($this->configure_keys());
  }

  /**
   * @return ModuleStatus
   */
  abstract public function describe_status_key();

  /**
   * @return ModuleSortOrder
   */
  abstract public function describe_sort_key();
  /**
   * @return array
   */

  abstract public function configure_keys();

  
  public function enable_module($platform_id, $flag){
    $key_info = $this->describe_status_key();
    if ( !is_object($key_info) || !is_a($key_info,'common\classes\modules\ModuleStatus')) return false;

    if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog')) {
        $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance();
        ($logUniversal
            ->setRelation($this->code)
            ->setType($logUniversal::ULT_EXTENSION_STATUS)
            ->setBeforeArray([$platform_id => (int)$this->is_module_enabled($platform_id)])
        );
    }

    $this->update_config_key(
      $platform_id,
      $key_info->key,
      $flag?$key_info->value_enabled:$key_info->value_disabled
    );

    if (isset($logUniversal)) {
        ($logUniversal
            ->setAfterArray([$platform_id => (int)$this->is_module_enabled($platform_id)])
            ->doSave(true)
        );
        unset($logUniversal);
    }
  }

  /**
   * @param $platform_id
   * @return bool
   */
  public function is_module_enabled($platform_id){
    $key_info = $this->describe_status_key();
    if ( !is_object($key_info) || !is_a($key_info,'common\classes\modules\ModuleStatus')) return false;

    return $this->get_config_key($platform_id,$key_info->key)==$key_info->value_enabled;
  }


  public function update_sort_order($platform_id, $new_sort_order){
    $key_info = $this->describe_sort_key();
    if ( !is_object($key_info) || !is_a($key_info,'common\classes\modules\ModuleSortOrder')) return;
    $this->update_config_key($platform_id, $key_info->key, (int)$new_sort_order );
  }

  protected function update_config_key($platform_id, $key, $value){
    tep_db_query(
      "UPDATE ".TABLE_PLATFORMS_CONFIGURATION." ".
      "SET configuration_value='".tep_db_input($value)."', last_modified=NOW() " .
      "WHERE configuration_key='".tep_db_input($key)."' AND platform_id='".(int)$platform_id."'"
    );
  }

  protected function get_config_key($platform_id, $key){
    $get_key_value_r = tep_db_query(
      "SELECT configuration_value ".
      "FROM ".TABLE_PLATFORMS_CONFIGURATION." ".
      "WHERE configuration_key='".tep_db_input($key)."' AND platform_id='".(int)$platform_id."'"
    );
    if ( tep_db_num_rows($get_key_value_r)>0 ) {
      $key_value = tep_db_fetch_array($get_key_value_r);
      return $key_value['configuration_value'];
    }
    return false;
  }

  public function save_config($platform_id, $new_data_array){
    if (is_array($new_data_array)) {
      $module_keys = $this->keys();
      foreach( $new_data_array as $update_key=>$new_value ){
        if ( !in_array($update_key,$module_keys) ) continue;
        $this->update_config_key($platform_id, $update_key, $new_value);
      }
    }
  }

  protected function get_install_keys($platform_id)
  {
    return $this->configure_keys();
  }

    public function getCountries($platform_id) {
        $modulesCountries = \common\models\ModulesCountries::findOne(['platform_id' => $platform_id, 'code' => $this->code]);
        if (is_object($modulesCountries)) {
            $countries = explode(',', $modulesCountries->countries);
            return array_merge($this->countries, $countries);
        }
        return $this->countries;
    }

    public function getRestriction($platform_id, $languages_id, $ignoreVisibility = false) {
        if ( (int)$platform_id==0 ) return '';

        $countriesAccess = $this->getCountries($platform_id);

        $variants = [];
        $variants[''] = 'Worldwide';
        global $languages_id;
        $countries = tep_db_query("SELECT c.countries_name, c.countries_iso_code_3 FROM " . TABLE_PLATFORMS_ADDRESS_BOOK . " AS pab LEFT JOIN " . TABLE_COUNTRIES . " AS c ON (c.countries_id = pab.entry_country_id) where c.language_id = '" . (int) $languages_id . "' group by c.countries_id");
        while ($countriesValue = tep_db_fetch_array($countries)) {
            $variants[$countriesValue['countries_iso_code_3']] = $countriesValue['countries_name'];
        }
        foreach ($countriesAccess as $code) {
            if (!isset($variants[$code])) {
            $country = \common\models\Countries::findOne(['countries_iso_code_3' => $code, 'language_id' => $languages_id]);
                if (is_object($country)) {
                    $variants[$code] = $country->countries_name;
                } else {
                    $variants[$code] = $code;
                }
            }
        }
        ksort($variants);

        $response = '<table width="50%"><thead><tr><th>' . TEXT_FOR_COUNTRIES . '</th></thead><tbody>';
        foreach ($variants as $code => $name) {
            $response .= '<tr><td>';
            $params = 'class="uniform" ';
            if (in_array($code, $this->countries)) {
                $params .= 'disabled';
            }
            $response .= '<label>';
            $response .= tep_draw_checkbox_field('countries[' . $code . ']', '1', in_array($code, $countriesAccess), '', $params );
            $response .= $name;
            $response .= '</label>';
            $response .= '</td></tr>';
        }
        $response .= '</tbody></table>';
        if ($ignoreVisibility) {
            return $response;
        }
        $response .= '<table width="50%"><thead><tr><th>' . TEXT_VISIBILITY . '</th></thead><tbody>';
        $variants = ['shop_order' => 'Checkout','shop_quote' => 'Quotation','shop_sample' => 'Sample', 'admin' => 'Admin area', 'moderator' => 'Group Administrator', 'pos' => 'POS'];
        $visibilityAccess = $this->visibility;
        $modulesVisibility = \common\models\ModulesVisibility::findOne(['platform_id' => $platform_id, 'code' => $this->code]);
        if (is_object($modulesVisibility)) {
            $visibility = explode(',', $modulesVisibility->area);
            $visibilityAccess = array_merge($this->visibility, $visibility);
        }
        foreach ($variants as $code => $name) {
            if (!\common\helpers\Extensions::isVisibilityVariant($code)) continue;
            $response .= '<tr><td>';
            $params = 'class="uniform" ';
            if (in_array($code, $this->visibility)) {
                $params .= 'disabled';
            }
            $response .= '<label>';
            $response .= tep_draw_checkbox_field('visibility_a[' . $code . ']', '1', in_array($code, $visibilityAccess), '', $params );
            $response .= $name;
            $response .= '</label>';
            $response .= '</td></tr>';
        }
        $response .= '</tbody></table>';
        return $response;
    }

    public function setRestriction() {
        $platform_id = (int)\Yii::$app->request->post('platform_id');
        if ( (int)$platform_id==0 ) return false;

        $countries = \Yii::$app->request->post('countries');
        $selectedCountries = [];
        if (is_array($countries)) {
            foreach ($countries as $code => $checked) {
                if ($code === 0) {
                    $code = '';
                }
                if ($checked == 1) {
                    $selectedCountries[] = $code;
                }
            }
        }
        sort($selectedCountries);
        $modulesCountries = \common\models\ModulesCountries::findOne(['platform_id' => $platform_id, 'code' => $this->code]);
        if (!is_object($modulesCountries)) {
            $modulesCountries = new \common\models\ModulesCountries();
            $modulesCountries->platform_id = $platform_id;
            $modulesCountries->code = $this->code;
        }

        if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog') && \common\extensions\ReportUniversalLog\classes\LogUniversal::isInstance($this->code)) {
            $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance($this->code);
            $logUniversal->mergeBeforeArray([
                'restriction_country' => trim($modulesCountries->countries)
            ]);
        }

        $modulesCountries->countries = implode(',' , $selectedCountries);
        $modulesCountries->save();

        if (isset($logUniversal)) {
            $logUniversal->mergeAfterArray([
                'restriction_country' => trim(array_shift(\common\models\ModulesCountries::find()->select('countries')
                    ->where(['platform_id' => $platform_id, 'code' => $this->code])->asArray(true)->column())
                )
            ]);
        }

        $visibility = \Yii::$app->request->post('visibility_a');
        $selectedVisibility = $this->visibility;
        if (is_array($visibility)) {
            foreach ($visibility as $code => $checked) {
                if ($code === 0) {
                    $code = '';
                }
                if ($checked == 1) {
                    $selectedVisibility[] = $code;
                }
            }
        }
        sort($selectedVisibility);
        $modulesVisibility = \common\models\ModulesVisibility::findOne(['platform_id' => $platform_id, 'code' => $this->code]);
        if (!is_object($modulesVisibility)) {
            $modulesVisibility = new \common\models\ModulesVisibility();
            $modulesVisibility->platform_id = $platform_id;
            $modulesVisibility->code = $this->code;
        }

        if (isset($logUniversal)) {
            $logUniversal->mergeBeforeArray([
                'restriction_area' => trim($modulesVisibility->area)
            ]);
        }

        $modulesVisibility->area = implode(',' , $selectedVisibility);
        $modulesVisibility->save();

        if (isset($logUniversal)) {
            $logUniversal->mergeAfterArray([
                'restriction_area' => trim(array_shift(\common\models\ModulesVisibility::find()->select('area')
                    ->where(['platform_id' => $platform_id, 'code' => $this->code])->asArray(true)->column())
                )
            ]);
        }

        return true;
    }


    public function getVisibily($platform_id, $restrict = [])
    {
        $result = false;
        $modulesVisibility = \common\helpers\Modules::loadVisibility($platform_id, $this->code);
        $modulesVisibility = (is_array($modulesVisibility) ? $modulesVisibility : array());
        if (is_array($this->visibility)) {
            $modulesVisibility = array_merge($modulesVisibility, $this->visibility);
        }
        if (is_array($modulesVisibility)) {
            $result = (bool)count(array_intersect($restrict, $modulesVisibility));
        }
        return $result;
        /*
        $modulesVisibility = \common\models\ModulesVisibility::findOne(['platform_id' => $platform_id, 'code' => $this->code]);
        $result = false;
        if (is_object($modulesVisibility)) {
            $modulesVisibility = explode(',', $modulesVisibility->area);
            $result = (bool)count(array_intersect($restrict, $modulesVisibility));
        }
        return $result;
        */
    }

    public function getGroupRestriction($platform_id) {
        if ( (int)$platform_id==0 ) return '';

        $groups = \common\helpers\Group::get_customer_groups_list();

        $modulesGroups = \common\models\ModulesGroupsSettings::findOne(['platform_id' => $platform_id, 'code' => $this->code]);
        $visibilityAccess = [];

        if (!is_null($modulesGroups) && !empty($modulesGroups->group_list)) {
            $visibilityAccess = explode(',', $modulesGroups->group_list);
        }

        $response = '<table width="50%" id="module_group_restriction" style="max-height:350px"><thead><tr><th>' . TEXT_FOR_GROUPS . ' ' . tep_draw_checkbox_field('group_restriction', '1', !is_null($modulesGroups), '', 'onchange="return updateGroupRestriction(this);" class="uniform" ' ) . '</th></thead><tbody>';

        foreach ($groups as $id => $name) {
            $response .= '<tr><td>';
            $params = 'class="uniform" ';
            if (is_null($modulesGroups)) {
                $params .= 'disabled';
            }
            $response .= '<label>';
            $response .= tep_draw_checkbox_field('group_visibility[]', $id, in_array($id, $visibilityAccess), '', $params );
            $response .= $name;
            $response .= '</label>';
            $response .= '</td></tr>';
        }

        $response .= '</tbody></table>';
        $response .= '<script type="text/javascript">function updateGroupRestriction(obj) { if ( $(obj).is(":checked") ) { $("input[name^=\'group_visibility\']").prop("disabled", false); $("#module_group_restriction div.checker").length && $("#module_group_restriction div.checker.disabled").removeClass("disabled"); } else {$("input[name^=\'group_visibility\']").prop("disabled", true); $("#module_group_restriction div.checker").length && $("#module_group_restriction tbody div.checker").addClass("disabled"); } }</script>';
        return $response;
    }

    public function setGroupRestriction() {
        $platform_id = (int)\Yii::$app->request->post('platform_id');
        if ( (int)$platform_id==0 ) return false;

        if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog') && \common\extensions\ReportUniversalLog\classes\LogUniversal::isInstance($this->code)) {
            $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance($this->code);
            $logUniversal->mergeBeforeArray([
                'restriction_group' => trim(array_shift(\common\models\ModulesGroupsSettings::find()->select('group_list')
                    ->where(['platform_id' => $platform_id, 'code' => $this->code])->asArray(true)->column())
                )
            ]);
        }

        $modulesGroups = \common\models\ModulesGroupsSettings::findOne(['platform_id' => $platform_id, 'code' => $this->code]);

        $group_restriction = (int)\Yii::$app->request->post('group_restriction');
        if ($group_restriction == 1) {
            $group_visibility = \Yii::$app->request->post('group_visibility', []);
            if (is_null($modulesGroups)) {
                $modulesGroups = new \common\models\ModulesGroupsSettings();
                $modulesGroups->platform_id = $platform_id;
                $modulesGroups->code = $this->code;
            }
            try {
              $modulesGroups->group_list = implode(',', $group_visibility);
              $modulesGroups->save(false);
            } catch (\Exception $e) {
              \Yii::warning($e->getMessage() . ' ' . $e->getTraceAsString());
            }

        } else {
            if (!is_null($modulesGroups)) {
                $modulesGroups->delete();
            }
        }

        if (isset($logUniversal)) {
            $logUniversal->mergeAfterArray([
                'restriction_group' => trim(array_shift(\common\models\ModulesGroupsSettings::find()->select('group_list')
                    ->where(['platform_id' => $platform_id, 'code' => $this->code])->asArray(true)->column())
                )
            ]);
        }
    }

    public function getGroupVisibily($platform_id, $groups_id)
    {
        if ( (int)$platform_id==0 ) return true;
        if (\common\helpers\System::isBackend()) return true; // allow all modules in order edit
        //allow to disable for all groups if ( (int)$groups_id==0 ) return true;
        $modulesGroups = \common\models\ModulesGroupsSettings::findOne(['platform_id' => $platform_id, 'code' => $this->code]);
        if (!is_null($modulesGroups)) {
          if (!empty(trim($modulesGroups->group_list))) {
            $visibilityAccess = explode(',', $modulesGroups->group_list);
          } else {
            $visibilityAccess = [];
          }
            if (!in_array($groups_id, $visibilityAccess)) {
                return false;
            }
        }
        return true;
    }


    public $billing;
    public $delivery;

    public function setBilling(array $billing){
        $this->billing = $billing;
    }

    public function setDelivery(array $delivery){
        $this->delivery = $delivery;
    }

/**
 * get tax rate and tax description by tax class id (for current order delivery/billing address)
 * @param int $tax_class_id
 * @return array [
            'tax_class_id' => $tax_class_id,
 *
            'tax' => $tax, //Tax::get_tax_rate
 *
            'tax_description' => $tax_description
        ];
 */
    function getTaxValues($tax_class_id) {

      if (defined('TAX_ADDRESS_OPTION') && (int)TAX_ADDRESS_OPTION == 1) {
        if ($this->manager->isShippingNeeded()) {
          $delivery_tax_values = \common\helpers\Tax::getTaxValues($this->manager->getPlatformId(), $tax_class_id, $this->delivery['country']['id'] ?? null, $this->delivery['zone_id'] ?? null);
        } else {
          $delivery_tax_values = \common\helpers\Tax::getTaxValues($this->manager->getPlatformId(), $tax_class_id, $this->billing['country']['id'] ?? null, $this->billing['zone_id'] ?? null);
        }
      } elseif (defined('TAX_ADDRESS_OPTION') && (int)TAX_ADDRESS_OPTION == 0) {
        $delivery_tax_values = \common\helpers\Tax::getTaxValues($this->manager->getPlatformId(), $tax_class_id, $this->billing['country']['id'] ?? null, $this->billing['zone_id'] ?? null);
      } else {
        // Seems DAA specific - any of (on checkout only)
        $delivery_tax_values = \common\helpers\Tax::getTaxValues($this->manager->getPlatformId(), $tax_class_id, $this->delivery['country']['id'] ?? null, $this->delivery['zone_id'] ?? null);
        if ($delivery_tax_values['tax'] > 0) {
        } else {
          $delivery_tax_values = \common\helpers\Tax::getTaxValues($this->manager->getPlatformId(), $tax_class_id, $this->billing['country']['id'] ?? null, $this->billing['zone_id'] ?? null);
        }
      }
      return $delivery_tax_values;

    }

    public static function round($number, $precision) {
        if (abs($number) < (1 / pow(10, $precision + 1))) {
            $number = 0;
        }
        if (strpos($number, '.') AND (strlen(substr($number, strpos($number, '.') + 1)) > $precision)) {
            $number = substr($number, 0, strpos($number, '.') + 1 + $precision + 1);
            if (substr($number, -1) >= 5) {
                if ($precision > 1) {
                    $number = substr($number, 0, -1) + ('0.' . str_repeat(0, $precision - 1) . '1');
                } elseif ($precision == 1) {
                    $number = substr($number, 0, -1) + 0.1;
                } else {
                    $number = substr($number, 0, -1) + 1;
                }
            } else {
                $number = substr($number, 0, -1);
            }
        }
        return $number;
    }

    /**
     * Dump method for caption instead cost in order totals
     * @see np
     * @see ot_shipping
     * @return string|bool
     */
    public function costUserCaption()
    {
        return false;
    }


  /**
   * get platform Id (in admin - from POST, GET; common - current)
   * @return int
   */
  protected static function getPlatformId() {

    $platformId = null;
    if (\Yii::$app->id == 'app-backend') {
      $platformId = \Yii::$app->request->post('platform_id', false);
      $gets = \Yii::$app->request->get();
      if (!$platformId && intval($gets['platform_id'] ?? null)) {
        $platformId = intval($gets['platform_id']);
      }
      if (!$platformId && !empty($gets['filter'])) {
        $tmp = [];
        parse_str($gets['filter'], $tmp);
        if (intval($tmp['platform_id'])) {
          $platformId = intval($tmp['platform_id']);
        }
      }
    }
    if ((isset($this) && $this instanceof self) ){
        if (!$platformId && $this->manager && $this->manager->has('platform_id')) {
            $platformId = $this->manager->get('platform_id');
        }
    }
    if (!$platformId) {
      $platformId = \common\classes\platform::currentId();
    }
    return $platformId;
  }

/**
 * get module encryption key from config or false
 * @return string|false
 */
  protected function getEncryptionKey(){
    $val = false;
    //this->code - to camel case
    $key = lcfirst(str_replace(' ', '', ucwords($this->code, '_')));

    if (!empty(\Yii::$app->params[$key . 'EncryptKey']) && strlen(trim(\Yii::$app->params[$key . 'EncryptKey']))>8) {
      $val = \Yii::$app->params[$key . 'EncryptKey'];
    }
    return $val;
  }

/**
 * compare encrypted values in DB and parameters
 * @param string $key
 * @param string $value
 * @param int $platform_id
 * @return bool
 */
    protected function confChanged($key, $value, $platform_id) {
        $placeHolder = (defined('PASSWORD_HIDDEN') ? PASSWORD_HIDDEN : '--Encrypted--');
        $changed = false;
        if ($value != $placeHolder) {
            $pc = new \common\classes\platform_config($platform_id);
            $old = $pc->const_value($key, '');
            $changed = ($old != $value);
        }
        return $changed;
    }

    /**
     * encrypt value before save in dB
     * @param string $key
     * @param string $value
     * @param int $platform_id
     * @return string
     */
    public function confValueBeforeSave($key, $value, $platform_id) {
        if (in_array($key, $this->encrypted_keys)) {
            if ($this->confChanged($key, $value, $platform_id)) {
                if (!empty($value)) {
                    $key = $this->getEncryptionKey();
                    if (empty($key)) {
                        $key = \Yii::$app->params['secKey.backend'];
                    }
                    $value = utf8_encode(\Yii::$app->security->encryptByKey($value, $key));
                }
            } else {
                $pc = new \common\classes\platform_config($platform_id);
                $value = $pc->const_value($key, '');
//        $value = base64_decode($value );
            }
        }
        return $value;
    }

    /**
     * base 64 encoded encrypted value in text input (better look)
     * @param string $val
     * @param string $key
     * @return string (HTML input element)
     */
    public static function setConf($val, $key) {
        //return \common\helpers\Html::textInput('configuration[' . $key .  ']', base64_encode($val));
        return \common\helpers\Html::textInput('configuration[' . $key . ']', (empty($val)?'':(defined('PASSWORD_HIDDEN') ? PASSWORD_HIDDEN : '--Encrypted--')));
    }

    /**
   * text encrypted instead of encrypted string
   * @return string
   */
  public static function useConf() {
    return defined('PASSWORD_HIDDEN')?PASSWORD_HIDDEN:'--Encrypted--';
  }

  /**
   *
   * @param string $key
   * @return string
   */
  protected function decryptConst($key) {
    $ret = defined($key) ? constant($key) : (new \common\classes\platform_config($this->getPlatformId()))->const_value($key);
    if (!empty($ret)) {
        $key = $this->getEncryptionKey();
        if (empty($key)) {
            $key = \Yii::$app->params['secKey.backend'];
        }
        $ret = \Yii::$app->security->decryptByKey( utf8_decode($ret), $key);
    }
    return $ret;
  }

    public static function always() { return true; }

    public static function getModuleCode()
    {
        return (new \ReflectionClass(get_called_class()))->getShortName();
    }

    private const MODULE_TYPES = [
            'extension' => ['class' => ModuleExtensions::class, 'namespace' => '\common\modules\extensions'],
            'payment' => ['class' => ModulePayment::class, 'namespace' => '\common\modules\orderPayment'],
            'shipping' => ['class' => ModuleShipping::class, 'namespace' => '\common\modules\orderShipping'],
            'order_totals' => ['class' => ModuleTotal::class, 'namespace' => '\common\modules\orderTotal'],
            'label' => ['class' => ModuleLabel::class, 'namespace' => '\common\modules\label'],
    ];
    public static function getType()
    {
        $class = get_called_class();
        foreach (self::MODULE_TYPES as $type => $data) {
            if (is_a($class, $data['class'], true)) {
                return $type;
            }
        }
    }

    public static function isExtension()
    {
        return self::getType() == 'extension';
    }

    public static function getNamespace(string $type)
    {
        \common\helpers\Assert::keyExists(self::MODULE_TYPES, $type, "Unknown module type: " . $type);
        return self::MODULE_TYPES[$type]['namespace'];
    }

    public static function getClass(string $type, string $code)
    {
        $class = self::getNamespace($type) . '\\' . $code;
        return class_exists($class)? $class : null;
    }

    public static function getInstalled($platform_id = 0)
    {
        return \common\helpers\Modules::getModuleInstalled(self::getModuleCode(), self::getType(), $platform_id);
    }

    public function upgrade()
    {
        \common\helpers\ModulesMigrations::up($this->code, null, self::getType());
        \common\helpers\Modules::changeModule($this->code, 'upgrade', ['version_db' => static::getVersion()], self::getType());
    }

    public function downgrade($toVer)
    {
        \common\helpers\ModulesMigrations::down($this->code, $toVer, self::getType());
        \common\helpers\Modules::changeModule($this->code, 'downgrade', ['version_db' => $toVer], self::getType());
    }

    public static function getModule($code, $type = 'extension')
    {
        $res = null;
        switch ($type) {
            case 'extension' :
                $res = \common\helpers\Acl::checkExtension($code, 'always');
                break;
            default:
                $res = self::getClass($type, $code);
        }
        \common\helpers\Assert::isNotNull($res, "Cannot find $type: $code");
        return $res;
    }

    public static function getRevision() {}

    public static function getVersionRev() : string
    {
        return static::getVersionObj()->toCommonFormat() . (is_null($rev = static::getRevision()) ? '' : ".$rev");
    }

    /**
     *
     * @param mixed $sinceVer
     * @param mixed $toVer
     * @return array - subarray of getVersionHistory()
     */
    public static function getVersionRange($sinceVer, $toVer = null)
    {
        $sinceVer = \common\classes\modules\ModuleVer::parse($sinceVer);
        $toVer = empty($toVer)? static::getVersion() : \common\classes\modules\ModuleVer::parse($toVer);

        $history = static::getVersionHistory();
        if (empty($history)) {
            return null;
        } else {
            return array_filter($history, function ($ver) use ($sinceVer, $toVer) {
                    return $sinceVer->compareTo($ver) < 0 && $toVer->compareTo($ver) >= 0;
                },
                ARRAY_FILTER_USE_KEY
            );
        }
    }

    public static function getMigrationDir() 
    {
        $ref = new \ReflectionClass(get_called_class());
        return dirname($ref->getFilename()) . '/migrations';
    }

    public static function getMigrationClass() 
    {
        $ref = new \ReflectionClass(get_called_class());
        return $ref->getNamespaceName() . '\\migrations\\';
    }

    public static function getMigrationFileMaskFull($code, $ver = null)
    {
        $ver = empty($ver)? static::getVersion() : \common\classes\modules\ModuleVer::parse($ver);
        return static::getMigrationDir() . '/' . static::getMigrationFileMask($code, $ver);
    }

    public static function getMigrationFileMask($code, \common\classes\modules\ModuleVer $ver)
    {
        return sprintf('%s_v%s*.php', $code, $ver->toFileFormat());
    }

    public static function getMigrations($code, $ver = null)
    {
        $mask = static::getMigrationFileMaskFull($code, $ver);
        $mask = str_replace('\\\\', '/', $mask);
        $mask = str_replace('\\', '/', $mask);
        $files = glob($mask,  GLOB_NOESCAPE);
        return array_map(function($file) { return self::getMigrationClass() . basename($file, '.php'); }, $files);
    }

    public static function getMigrationsSince($code, $sinceVer, $up = true, $toVer = null)
    {
        $range = static::getVersionRange($sinceVer, $toVer);
        if (!empty($range)) {
            $versions = array_keys($range);
            if ($up) $versions = array_reverse($versions);
            $res = [];
            foreach ($versions as $ver) {
                $res = array_merge($res, static::getMigrations($code, $ver));
            }
            return $res;
        }
    }

/**
 * use in your module's update_status() overwritten in modulePayment (status by billing address)
 * @param int $zone_id
 * @param string $which delivery|billing
 * @return bool true - ok false - switch off
 */
    protected function checkStatusByZone($zone_id, $which = 'delivery') {
        $which = strtolower($which);
        if ($which != 'billing') {
            $which = 'delivery';
        }
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . $zone_id . "' and zone_country_id = '" . ($this->$which['country']['id']??0) . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
            if ($check['zone_id'] < 1) { // zone_id == 0  => all zones
                $check_flag = true;
                break;
            } elseif ($check['zone_id'] == ($this->$which['zone_id']??0)) {
                $check_flag = true;
                break;
            }
        }

        return $check_flag;

    }
    
/**
 * 
 * @param string|array $data [external_id => SSS, customers_id => NNNN]
 * @return boolean | true - success
 */
    public function saveExternalCustomersId($data) {
        $ret = $extId = $cid = false;
        if (is_scalar($data)) {
            $extId = $data;
        } elseif (is_array($data) && isset($data['external_id'])) {
            $extId = $data['external_id'];
            if (isset($data['customers_id'])) {
                $cid = $data['customers_id'];
            }
        } 
        if (empty($cid) && !empty($this->manager) && $this->manager->isCustomerAssigned()) {
           $cid = $this->manager->getCustomerAssigned();
        }
        if (!empty($extId) && !empty($cid)) {
            $model = \common\models\CustomersExternalIds::findOne([
                    'customers_id' => $cid,
                    'system_name' => $this->code,
            ]);
            if (!$model) {
                $model = new \common\models\CustomersExternalIds();
                $model->setAttributes([
                    'customers_id' => $cid,
                    'system_name' => $this->code
                ]);
            }
            $model->external_id = $extId;
            try {
                $model->save(false);
                $ret = true;
            } catch (\Exception $e) {
                \Yii::warning(" #### " .print_r($e->getMessage() . ' ' . $e->getTraceAsString(), true), 'TLDEBUG');
            }

        }
        return $ret;

    }

    public function getExternalCustomersId($cid = 0) {
        $ret = false;
        if (empty($cid) && !empty($this->manager) && $this->manager->isCustomerAssigned()) {
           $cid = $this->manager->getCustomerAssigned();
        }
        if (!empty($cid)) {
            $model = \common\models\CustomersExternalIds::findOne([
                        'customers_id' => $cid,
                        'system_name' => $this->code,
                ]);
            if (!empty($model->external_id)) {
               $ret = $model->external_id;
            }
        }
        return $ret;
    }

}
