<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\classes\modules;

/**
 * Base class for Setup class of extension
 *
 * @author pandrei
 */
class SetupExtensions {

    public static function getDescription() {
        return '';
    }

    public static function getVersionHistory() {
//        return [
//            '1.1.0' => [
//                'added Import All button',
//                'fixed Removing order mapping is not saved',
//            ],
//            '1.0.0' => 'Initial release'];
    }

    /**
     * Not needed for overriding if getVersionHistory is implemented */
    public static function getVersion()
    {
        $arr = static::getVersionHistory();
        if (!empty($arr)) {
            if (!function_exists('array_key_first')) { // PHP < 7.3
                foreach($arr as $key => $unused) {
                    return $key;
                }
            } else {
                return array_key_first($arr);
            }
        }
        return '0.1.0';
    }

    public static function getRequiredModules()
    {
        // maybe: return [ 'ext' => [
        //                      'hard' => ['ClassName1', 'ClassName2'], // extention can't work without classes
        //                      'soft' => ['ClassName3', 'ClassName4'], // extention can work without classes, but has additional features it they installed
        //                      ];
    }

// implement optionally
//    public static function getAdminMenu() {
//        return [
//        ];
//    }

// implement optionally 
//    public static function getConfigureKeys($code)
//    {
//        return [
//            $code . '_EXTENSION_SHOW_STOCK_INDICATOR' => [
//                'title' => 'Use additional Stock Indicator status',
//                'description' => 'Limited by Asset Indicator',
//                'value' => 'False',
//                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
//            ];
//    }

    public static function getTranslationArray()
    {
//        return [
//            'admin/main' => ['BOX_MODULES_CONNECTORS' => 'Connectors'],
//            'admin/old_key' => [
//                '__config__' => ['default'], // rules for install, remove and init
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



// implement it here or override Extension::install
//    public static function install($platform_id, $migrate) {
//        $migrate->createTableIfNotExists('connector_osclink_configuration', [
//            'cmc_key' => $migrate->string(128)->notNull()->append('PRIMARY KEY'),
//            'cmc_value' => $migrate->string(250)->notNull()->defaultValue(''),
//            'cmc_upd_date' => $migrate->datetime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
//            'cmc_upd_admin' => $migrate->integer(10)->unsigned()->notNull(),
//        ]);
//    }

// implement it here or override Extension::remove
//    public static function remove($platform_id, $migrate) {
//    }


}
