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

class SetupExtensions {

    use VersionTrait;

    // required
    public static function getDescription() {
        return '';
    }

    // override optionally if extension require other extension(s)
    public static function getRequiredModules()
    {
//        return [
//           'osCommerce' => ['version' => '121211', 'version_applicable' => 'equal' or 'greater-equal' or 'less-equal'],
//            REQUIRE_TYPE => [
//                MODULE_TYPE => [
//                    /* simple variant: */ 'ModuleClass1', 'ModuleClass2',
//                    /* variant with conditions min */ 'ModuleClass' => ['version_min' => '1.1.0']
//                    /* variant with conditions min+max*/ 'ModuleClass' => ['version_min' => '1.1.0', 'version_max' => '2.0.0']
//                ],
//            ]
//        ];
//        MODULE_TYPE: 'ext'/'payment'/'shipping'/'label'/'analytic'
//        REQUIRE_TYPE:
//          'hard'   - extention can't work without classes
//          'soft'   - extention can work without classes, but has additional features it they installed
//          'usedby' - extension used by classes
//
//        Sample:
//        return ['osCommerce' => ['version' => '54127', "version_applicable" => "greater-equal"],
//            'used' => [
//                'ext' => ['Rma'],
//            ],
//            'soft' => [
//                'ext' => ['Sample'],
//            ],
//            'hard' => [
//                'ext' => [
//                    'Quotations' => ['version_min' => '1.1.0'],
//                ],
//                'payment' => ['Quotations'],
//            ],
//        ];
    }

// implement optionally if extension add backend menu
//    public static function getAdminMenu() {
//        return [
//            [
//                'parent' => 'BOX_HEADING_CUSTOMERS',
//                'sort_order' => '11',
//                'box_type' => '0',
//                'acl_check' => 'Communication,allowed',
//                'config_check' => '',
//                'path' => 'extensions?module=Communication',
//                'title' => 'BOX_COMMUNICATION',
//                'filename' => '',
//            ],
//        ];
//    }

// implement optionally to intercept important calls (see list in extensions/methodology.txt)
//    public static function getAdminHooks()
//    {
//        $path = \Yii::getAlias('@common') . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'MyExtClass' . DIRECTORY_SEPARATOR . 'hooks' . DIRECTORY_SEPARATOR;
//        return [
//            [
//                'page_name' => 'categories/categoryedit',
//                'page_area' => 'main-tab',
//                'extension_file' => $path . 'categories.categoryedit.main-tab.tpl',
//            ],
//            [
//                'page_name' => 'categories/categoryedit',
//                'page_area' => '',
//                'extension_file' => $path . 'categories.categoryedit.php',
//            ],
//        ];
//    }


// implement optionally if extension used configuration keys
//    public static function getConfigureKeys($code)
//    {
//        return [
//            $code . '_EXTENSION_SHOW_STOCK_INDICATOR' => [
//                'title' => 'Use additional Stock Indicator status',
//                'description' => 'Limited by Asset Indicator',
//                'value' => 'False',
//                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
//            ]
//        ];
//    }

// return array with translations keys
//
// read this only if you want to customize managing:
// magic key __config__ allows to manage rules for installing/uninstalling/initializing
// list of rules:
//  'install' - will be installed at begining of extension installation
//  'remove_entity' - full entity will be removed in the end of uninstallation
//  'remove_keys' - only specified keys will be removed in the end of uninstallation
//  'init_constructor' - will be initialized (Translation::init) at object construction
//  'init_always', - will be initialized before using static functions (actions, etc)
//  'init_widget' - will be initialized before using widgets
//  'init_directcall' - will be initialized while calling self::initTranslation()
//  By default these rules are applied:
//    - for 'extensions/*' entities:  ['install', 'remove_entity', 'init', 'init_static', 'widget'] // extension keys
//    - for others:                   ['install', 'init', 'init_static', 'widget'], // main keys (won't be removed by default because still present in menu and acl)

    public static function getTranslationArray()
    {
//        return [
//            'main' => ['BOX_MODULES_CONNECTORS' => 'Connectors'],
//            'admin/main' => ['BOX_MODULES_CONNECTORS' => 'Connectors'],
//            'admin/old_key' => [
//                '__config__' => ['default'], // optional rules for install, remove and init
//                'BOX_CATALOG_PDF_CATALOGUES' => 'PDF Catalogues',
//            ],
//            'extensions/your_ext' => [EXTENSION_YOUREXT_TEXT => 'English text']
//        ];
    }

// implement optionally
//    protected static function getAclArray()
//    {
//        return ['default' => ['BOX_HEADING_MODULES', 'BOX_MODULES_CONNECTORS', 'BOX_MODULES_CONNECTORS_OSCLINK'],
//                'actionExtentionSecure1' => [...],
//        ];
//    }

// implement optionally
//    public static function getWidgets($type = 'general') {
//        return [
//            ['name' => 'Communication\NewTopic', 'title' => 'New Topic', 'description' => '', 'type' => 'communication'],
//            ['name' => 'Communication\NewTopicButton', 'title' => 'New Topic Button', 'description' => '', 'type' => 'communication'],
//            ['name' => 'Communication\Topic', 'title' => 'Topic list', 'description' => '', 'type' => 'communication'],
//            ['name' => 'Communication\Post', 'title' => 'Post list', 'description' => '', 'type' => 'communication'],
//            ['name' => 'Communication\NewTopic', 'title' => 'New Topic', 'description' => '', 'type' => 'account'],
//        ];
//    }

// implement optionally
//    public static function getEpDatasources()
//    {
//        return [[
//            'class' => 'PdfCatalogues',
//            'className' => '\common\extensions\PdfCatalogues\EP\Datasources\PdfCatalogues',
//            'name' => 'PDF Catalogues',
//        ]];
//
//    }
//
// implement optionally
//    public static function getEpProviders()
//    {
//        return ['PdfCatalogues\\PdfCatalogues' => [
//                'group' => 'PdfCatalogues',
//                'name' => 'PDF Catalogues Generator',
//                'class' => '\common\extensions\PdfCatalogues\EP\Providers\PdfCatalogGen',
//                'export' => [
//                    'disableSelectFields' => true,
//                ]
//        ]];
//    }

// implement it here or override Extension::install
//    public static function install($platform_id, $migrate) {
//        $migrate->createTableIfNotExists('connector_osclink_configuration', [
//            'cmc_key' => $migrate->string(128)->notNull()->append('PRIMARY KEY'),
//            'cmc_value' => $migrate->string(250)->notNull()->defaultValue(''),
//            'cmc_upd_date' => $migrate->datetime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
//            'cmc_upd_admin' => $migrate->integer(10)->unsigned()->notNull(),
//        ]);
//    }

// optional
//    public static function getDropDatabasesArray()
//    {
//        return ['table1', 'table2'];
//    }

// implement it here or override Extension::remove
//    public static function remove($platform_id, $migrate, $removeDBTables) {
//    }


}
