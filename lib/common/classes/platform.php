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

use common\models\Platforms;
use common\models\PlatformsAddressBook;

class platform
{
    private $config_pool = array();
    private $active_config_id = false;

  public static function name($platform_id)
  {
    static $_map = false;
    if( !is_array($_map) ) {
      $_map = array();
      foreach( self::getList(true, true) as $_platform ) $_map[ $_platform['id'] ] = $_platform['text'];
    }
    return isset($_map[$platform_id])?$_map[$platform_id]:'-';
  }

  public static function getList($withMarketplace = true, $withVirtual = false)
  {
    static $db_list = array();
    $cache_key = (!!$withMarketplace?'T':'F') . (!!$withVirtual?'V':'L');
    if ( !isset($db_list[$cache_key]) ) {
      $db_list[$cache_key] = array();
      $platformQuery = \common\models\Platforms::find()->where(['status' => 1,]);
      $filter = [
            'or',
            ['is_virtual' => 0, 'is_marketplace' => 0]
        ];
      $order = [
          "if(is_default=1,0,1)"  => SORT_ASC,
          "is_marketplace" => SORT_ASC,
          "is_virtual" => SORT_ASC,
      ];
      
      if ($withMarketplace){
          $filter[] = [
            'and',
            ['is_virtual' => 0, 'is_marketplace' => 1]
        ];
      }
      if ($withVirtual){
          $filter[] = [
            'and',
            ['is_virtual' => 1, 'is_marketplace' => 0]
        ];
      }
        global $login_id;
        if (defined('TABLE_ADMIN') && false === \common\helpers\Acl::rule(['SUPERUSER']) && $login_id > 0) {
            $ids = [];
            $platforms = \common\models\AdminPlatforms::find()->where(['admin_id' => $login_id])->asArray()->all();
            foreach ($platforms as $platform) {
                $ids[] = $platform['platform_id'];
            }
            $ids[] = 0;
            $platformQuery->andWhere(['in', 'platform_id', $ids]);
        }
      $platformQuery->orderBy(array_merge($order, [
        "sort_order" => SORT_ASC,
        "platform_name" => SORT_ASC,
      ]));
      
      $get_list_r = $platformQuery->andFilterWhere($filter)->all();
      if ($get_list_r){
          $is_default_in_list = false;
          foreach($get_list_r as $_list){
              $db_list[$cache_key][] = array(
                'id' => $_list['platform_id'],
                'text' => $_list['platform_name'],
                'is_virtual' => $_list['is_virtual'],
                'is_marketplace' => $_list['is_marketplace'],
                'is_default' => !!$_list['is_default'],
                'need_login' => $_list['need_login'],
                'platform_url' => $_list['platform_url'],
              );
              if (!!$_list['is_default']) {
                  $is_default_in_list = true;
              }
          }
          if ($is_default_in_list === false) {
              foreach ($db_list[$cache_key] as $key => $value) {
                  $db_list[$cache_key][$key]['is_default'] = true;
                  break;
              }
          }
      }
    }
    return $db_list[$cache_key];
  }

    /**
     * @return platform_config
     */
    function getConfig($id) {
        if (!isset($this->config_pool[(int) $id])) {
            $this->config_pool[(int) $id] = new platform_config((int) $id);
            if (!$this->config_pool[(int) $id]->getId()) {
                $this->config_pool[(int) $id] = new platform_config(platform::defaultId());
            }
        }
        return $this->config_pool[(int) $id];
    }

    /**
     * @return platform_config
     */
    function config($id = null) {
        if (is_numeric($id)) {
            $this->active_config_id = false;
            foreach (platform::getList(true, true) as $check_id) {
                if ($check_id['id'] == $id) {
                    $this->active_config_id = (int) $id;
                    break;
                }
            }
        }

        if (!is_numeric($this->active_config_id)) {
            $this->active_config_id = platform::currentId();
        }

        if (!isset($this->config_pool[$this->active_config_id])) {
            $this->config_pool[$this->active_config_id] = new platform_config($this->active_config_id);
        }

        return $this->config_pool[$this->active_config_id];
    }

    public static function getProductsAssignList() {
        return self::getList();
    }

    public static function getCategoriesAssignList() {
        return self::getList();
    }

    public static function realDefaultId() {
        $defaultPlatform = \common\models\Platforms::findOne(['is_default' => 1]);
        return $defaultPlatform->platform_id ?? 0;
    }

    public static function defaultId() {
        $default_id = 0;
        $platforms = self::getList();
        foreach ($platforms as $platform) {
            if ($platform['is_default']) {
                $default_id = (int) $platform['id'];
                break;
            }
        }
        return $default_id;
    }

    public static function activeId() {
        $platforms = self::getList();
        return ( count($platforms) > 1 && defined('PLATFORM_ID') && PLATFORM_ID > 0 ) ? (int) PLATFORM_ID : 0;
    }

    public static function currentId() {
        return ( defined('PLATFORM_ID') && PLATFORM_ID > 0 ) ? (int) PLATFORM_ID : self::firstId();
    }

    public static function isMulti($withMarketplace = true, $withVirtual = false) {
        $platforms = self::getList($withMarketplace, $withVirtual);
        return count($platforms) > 1;
    }

    public static function firstId() {
        $platforms = self::getList(false);
        return count($platforms) > 0 ? $platforms[0]['id'] : 0;
    }

    public static function validId($id) {
        $is_valid = false;
        $platforms = self::getList(false);
        foreach ($platforms as $platform) {
            if ($id == $platform['id'])
                $is_valid = true;
        }
        if ($is_valid) {
            return $id;
        } else {
            return self::currentId();
        }
    }

    public static function country($id) {
        $platformAddress = PlatformsAddressBook::find()->where(['platform_id' => $id])->with('country')->one();
        if ($platformAddress instanceof PlatformsAddressBook) {
            return $platformAddress->country;
        }
    }

}
