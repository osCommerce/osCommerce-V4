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

namespace common\extensions\UserGroups;

class Setup extends \common\classes\modules\SetupExtensions
{
    public static function getAdminHooks()
    {
        return [
            [ 'page_name' => 'banner_manager/banneredit' ],
            [ 'page_name' => 'banner_manager/banneredit', 'page_area' => 'platform-table-heading-cell' ],
            [ 'page_name' => 'banner_manager/banneredit', 'page_area' => 'platform-table-cell' ],
            [ 'page_name' => 'banner_manager/submit' ],
            [ 'page_name' => 'box/banner' ],
            [ 'page_name' => 'box/block/hide-widget' ],
            [ 'page_name' => 'design/box-edit', 'page_area' => 'hide-widget' ],
        ];
    }

    public static function getTranslationArray()
    {
        return [
            'extensions/user-groups' => [
                'USER_GROUPS' => 'User Groups'
            ],
        ];
    }

    public static function getVersionHistory()
    {
        return [
            '1.0.1' => 'Added user groups to widgets',
            '1.0.1' => 'Added user groups to banner',
            '1.0.0' => 'User groups',
        ];
    }

    public static function install($platform_id, $migrate)
    {
        if ( $migrate->isTableExists('banners_to_platform') &&
            !$migrate->isFieldExists('user_groups', 'banners_to_platform'
        )) {
            $migrate->addColumn('banners_to_platform', 'user_groups', $migrate->string(255)->notNull()->defaultValue('#0#'));
        }
    }

    public static function remove($platform_id, $migrate, $drop = false)
    {
        if ($drop &&
            $migrate->isTableExists('banners_to_platform') &&
            $migrate->isFieldExists('user_groups', 'banners_to_platform')
        ) {
            $migrate->dropColumn('banners_to_platform', 'user_groups');
        }
    }

    public static function getDropDatabasesArray() {
        return [];
    }

    public static function getConfigureKeys()
    {
        return [
            'USER_GROUPS_WITH_BANNERS' => [
                'title' => 'Use User Groups on banners',
                'description' => '',
                'value' => 'false',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ],
        ];
    }
}