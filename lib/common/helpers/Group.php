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

namespace common\helpers;

class Group {

/**
 *
 * @staticvar array $groups
 * @staticvar array $e_groups
 * @param int $code
 * @return array
 */
    public static function get_customer_groups($code = '') {
      static $groups = false;
      static $e_groups = false;

      /** @var \common\extensions\UserGroups\UserGroups $ext */
      $ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed');

      if (!$ext) {

        if (!is_array($groups)) {
            $groups = \common\models\Groups::find()->orderBy('groups_name')->indexBy('groups_id')->asArray()->all();
            if (!is_array($groups)) {
              $groups = [];
            }
        }

        $ret = $groups;

      } else {

        if (!isset($e_groups[$code]) || !is_array($e_groups[$code])) {
          $e_groups = []; //php8
          $e_groups[$code] = $ext::getGroupsArray($code);
          if (!is_array($e_groups[$code])) {
            $e_groups[$code] = [];
          }
        }

        $ret = $e_groups[$code];

      }

      return $ret;

    }

    /**
     * uses cached get_customer_groups
     * @param int $code (type id - extra groups extension)
     * @return array (id=>name)
     */
    public static function get_customer_groups_list($code = '',$empty_string = false) {
        $response = [];
        if($empty_string) {
            $response[0] = TEXT_MAIN;
        }
        return ($response +\yii\helpers\ArrayHelper::map(self::get_customer_groups($code), 'groups_id', 'groups_name'));
    }

/**
 * get group name by id
 * @param type $id
 * @return string
 */
    public static function get_user_group_name($id) {
        if ($id == 0) {
            $ret = TEXT_MAIN;
        } else {
          $ret = '';
          $group = \common\models\Groups::findOne($id);
          if ($group) {
            $ret = $group->groups_name;
          }
        }
        return $ret;
    }

    public static function isTaxApplicable($groupId = null)
    {
        if (!\common\helpers\Extensions::isAllowed('BusinessToBusiness')) return true;

        if (is_null($groupId)) {
            $groupId = (int) \Yii::$app->storage->get('customer_groups_id');
        }
        if ($groupId == 0) return true;
        return \common\helpers\Customer::check_customer_groups($groupId, 'groups_is_tax_applicable');
    }


}
