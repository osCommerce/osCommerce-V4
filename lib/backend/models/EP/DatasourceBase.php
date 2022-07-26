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

namespace backend\models\EP;

use yii\base\BaseObject;

abstract class DatasourceBase extends BaseObject
{

    public $code = '';
    public $className = 'DatasourceBase';
    public $settings = [];

    public function __construct(array $config = [])
    {
        if ( isset($config['settings']) && is_string($config['settings']) ) $config['settings'] = json_decode($config['settings'],true);
        if ( !is_array($config['settings'] ?? null) ) $config['settings'] = array();
        $initConfig = [];
        foreach ($config as $key => $val) {
            if (isset($this->$key)) {
                $initConfig[$key] = $val;
            }
        }
        parent::__construct($initConfig);
    }

    /**
     * [
     *   '{ProviderClass}\\{DatasourceClass}' => [
     *     'group' => '{ProviderName}',
     *     'name' => '{Datasource Name}',
     *     'class' => 'Provider\\{ProviderClass}\\{DatasourceClass}',
     *     'export' =>[
     *       'disableSelectFields' => true,
     *     ],
     *   ],
     *   ....
     * ]
     *
     * @return array
     */
    public static function getProviderList()
    {
        return [];
    }

    abstract public function getName();

    abstract public function getViewTemplate();

    public function orderView($orderId)
    {
        return false;
    }

    /**
     * @deprecated
     * @param $configArray
     * @return mixed
     */
    static public function configureArray($configArray)
    {
        return $configArray;
    }

    public function prepareConfigForView($configArray)
    {
        return $configArray;
    }


    /**
     * @param $data
     * @return array
     * @throws \InvalidArgumentException
     */
    static public function beforeSettingSave($data)
    {
        $settings = is_array($data) ? $data : [];

        return $settings;
    }
    
    static public function afterSettingSave()
    {

    }

    public function update($settings)
    {
        $settings = static::beforeSettingSave($settings);
        $this->settings = $settings;
        tep_db_query("UPDATE ep_datasources SET settings='".tep_db_input(json_encode($this->settings))."' WHERE code='".tep_db_input($this->code)."' ");
        static::afterSettingSave();
    }

    public function configureView()
    {
        $settings = $this->prepareConfigForView($this->settings);
        $settings['code'] = $this->code;
        return [
            $this->getViewTemplate(),
            $settings,
        ];
    }

    public function getJobConfig()
    {
        return $this->settings;
    }

    public function updateSettingKey($key, $value)
    {
        $changed = false;
        if ( is_null($value) ) {
            if ( array_key_exists($key,$this->settings) ) {
                unset($this->settings[$key]);
                $changed = true;
            }
        }else{
            if (!isset($this->settings[$key]) || $this->settings[$key] != $value ) {
                $this->settings[$key] = $value;
                $changed = true;
            }
        }
        if ( $changed ) {
            tep_db_query("UPDATE ep_datasources SET settings='" . tep_db_input(json_encode($this->settings)) . "' WHERE code='" . tep_db_input($this->code) . "' ");
        }
    }

    public function allowProductView()
    {
        return false;
    }

    public function productView($config)
    {
        return '';
    }

    public function productSave(Directory $directory, $product)
    {

    }

}