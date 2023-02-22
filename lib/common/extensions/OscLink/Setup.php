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

namespace common\extensions\OscLink;

class Setup extends \common\classes\modules\SetupExtensions {

    const TRANSLATION = [
                'EXTENSION_OSCLINK_CONFIGURATION_KEY'   => 'Key',
                'EXTENSION_OSCLINK_CONFIGURATION_VALUE' => 'Value',

                'EXTENSION_OSCLINK_API_URL'     => 'OSCommerce bridge URL',
                'EXTENSION_OSCLINK_API_METHOD'  => 'OSCommerce secure method',
                'EXTENSION_OSCLINK_API_KEY'     => 'OSCommerce secure key (please read Authentication topic of readme file into OSC bridge dirrectory)',

                'EXTENSION_OSCLINK_TAB_CONNECTION' => 'Connection settings',
                'EXTENSION_OSCLINK_TAB_MAPPING'    => 'Mapping settings',
                'EXTENSION_OSCLINK_TAB_ACTIONS'    => 'Run import actions',
                'EXTENSION_OSCLINK_TAB_CLEANING'   => 'Clean imported data',

                'EXTENSION_OSCLINK_MSG_CONNECT_START'    => 'Connecting...',
                'EXTENSION_OSCLINK_MSG_CONNECT_SUCCESS'  => 'Connection successful!',
                'EXTENSION_OSCLINK_MSG_CONNECT_ERROR'    => 'Error while establishing connection',

                'EXTENSION_OSCLINK_API_PLATFORM'            => 'Import to sales chanel',
                'EXTENSION_OSCLINK_API_PLATFORM_DEFAULT'    => 'Default platform',
                'EXTENSION_OSCLINK_API_STATUS_MAP'          => 'Order status mapping',
                'EXTENSION_OSCLINK_TEXT_DESCRIPTION'        => 'Description',
                'EXTENSION_OSCLINK_TEXT_ACTION'             => 'Action',

                'EXTENSION_OSCLINK_TEXT_GROUP'              => 'Group',
                'EXTENSION_OSCLINK_TEXT_ENTITY'             => 'Entity',
                'EXTENSION_OSCLINK_TEXT_DESCRIPTION'        => 'Description',
                'EXTENSION_OSCLINK_TEXT_ACTION'             => 'Actions',
                'EXTENSION_OSCLINK_TEXT_REWRITE_BUTTON'     => 'Import',
                'EXTENSION_OSCLINK_TEXT_CLEAN_BUTTON'       => 'Remove',
                'EXTENSION_OSCLINK_TEXT_IMPORT_ALL_BUTTON'  => 'Import All',

                'EXTENSION_OSCLINK_TEXT_CANCEL_CONFIRM'      => 'Do you really wish to cancel migration?',
                'EXTENSION_OSCLINK_TEXT_EXECUTE_CONFIRM'     => 'Do you really wish to execute migrate Request?',
                'EXTENSION_OSCLINK_TEXT_CLEAN_CONFIRM'       => 'Do you really wish to delete imported rows from selected entities?',
                'EXTENSION_OSCLINK_TEXT_CLEAN_CONFIRM_ALL'   => 'Do you really wish to delete all imported rows and mapped data?',
                'EXTENSION_OSCLINK_TEXT_CANCEL_BUTTON'       => 'Cancel',

                'EXTENSION_OSCLINK_TEXT_GROUP_TAXES'     => 'Taxes',
                'EXTENSION_OSCLINK_TEXT_GROUP_PRODUCTS'  => 'Products',
                'EXTENSION_OSCLINK_TEXT_GROUP_CUSTOMERS' => 'Customers & Orders',

                'EXTENSION_OSCLINK_TEXT_MAPPING_ITEM_SKIPPED'     => "Will be skipped",
                'EXTENSION_OSCLINK_TEXT_MAPPING_ORDER_STATUS_DESC_OSC' => "OSCommerce order status",
                'EXTENSION_OSCLINK_TEXT_MAPPING_ORDER_STATUS_DESC_OUR' => "Will be mapped to:",

                'EXTENSION_OSCLINK_TEXT_DESCRIPTION_IMPORT_ALL'     => "Download and import all entities from OSCommerce",
                'EXTENSION_OSCLINK_TEXT_DESCRIPTION_COMMON'         => "If you always imported the some records before, they will be updated. The process won't affect any existing not-imported records.",
                'EXTENSION_OSCLINK_TEXT_IMPORT_BY_PARTS'            => "or import entities by parts:",

                'EXTENSION_OSCLINK_TEXT_CLEAN_RADIO_ALL'        => 'Remove all imported entities and mapped data',
                'EXTENSION_OSCLINK_TEXT_CLEAN_RADIO_SELECTED'   => 'Remove only selected imported entities:',

                'EXTENSION_OSCLINK_TEXT_ERROR_NOT_FOUND'        => 'OSCommerce bridge does not found',
                'EXTENSION_OSCLINK_TEXT_ERROR_OLD_VERSION'      => 'Please upgrade your OSCommerce bridge to version %s',
                'EXTENSION_OSCLINK_TEXT_ERROR_OLD_VER_FOUND'    => 'old version found: %s',

                'EXTENSION_OSCLINK_API_MEASUREMENT' => 'System of measurement into osCommerce 2.x',
        ];

    public static function getDescription()
    {
        return 'This extension allows to import the data from OSCommerce 2.x.';
    }

    public static function getVersionHistory()
    {
        return [
            '1.1.0' => [
                'added Import All button',
                'fixed Removing order mapping is not saved',
            ],
            '1.0.0' => 'Initial release'];
    }

    public static function getAdminMenu()
    {
        return [
            [
                'parent' => 'BOX_HEADING_MODULES',
                'removeIfExists' => false,
                'sort_order' => '50',
                'box_type' => '1',
                'title' => 'BOX_MODULES_CONNECTORS',
                'child' => [
                    [
                        'sort_order' => '5',
                        'acl_check' => 'OscLink,allowed',
                        'path' => 'extensions?module=OscLink',
                        'title' => 'BOX_MODULES_CONNECTORS_OSCLINK',
                    ],
                ]
            ],
        ];
    }

    public static function getAclArray()
    {
        return ['default' => ['BOX_HEADING_MODULES', 'BOX_MODULES_CONNECTORS', 'BOX_MODULES_CONNECTORS_OSCLINK']];
    }

    public static function getTranslationArray()
    {
        $translation = self::TRANSLATION;
        foreach (OscLink::FEEDS as $key => $feed) {
            $feed_name = OscLink::FEEDS_NAMES[$key];
            $translation['EXTENSION_OSCLINK_TEXT_ENTITY_' . strtoupper($feed)] = $feed_name;
            $translation['EXTENSION_OSCLINK_TEXT_DESCRIPTION_' . strtoupper($feed)] = "Download and import $feed_name structure from OSCommerce.";
        }

        return [
            'admin/main' => [
                'BOX_MODULES_CONNECTORS' => 'Connectors',
                'BOX_MODULES_CONNECTORS_OSCLINK' => 'OSCommerce Connector Service',
            ],
            'extensions/osclink' => $translation
        ];
    }

    public static function install($platform_id, $migrate)
    {
        $migrate->createTableIfNotExists('connector_osclink_configuration', [
            'cmc_key' => $migrate->string(128)->notNull()->append('PRIMARY KEY'),
            'cmc_value' => $migrate->string(250)->notNull()->defaultValue(''),
            'cmc_upd_date' => $migrate->datetime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'cmc_upd_admin' => $migrate->integer(10)->unsigned()->notNull(),
        ]);

        $migrate->createTableIfNotExists('connector_osclink_entity', [
                'id'          => $migrate->primaryKey(11)->append('AUTO_INCREMENT'),
                'project_id'  => $migrate->integer(11)->unsigned()->notNull(),
                'entity_name' => $migrate->string(128)->notNull(),
            ],
            null, //primary key
            ['project_id', 'entity_name']
        );

        $migrate->createTableIfNotExists('connector_osclink_mapping', [
                'entity_id'     => $migrate->integer(11)->unsigned()->notNull(),
                'external_id'   => $migrate->integer(11)->unsigned()->notNull(),
                'internal_id'   => $migrate->integer(11)->unsigned()->notNull(),
            ],
            'entity_id, external_id' // primary
        );
    }

    public static function remove($platform_id, $migrate)
    {

    }

    public static function getDropDatabasesArray()
    {
        return [
            'connector_osclink_configuration',
            'connector_osclink_entity',
            'connector_osclink_mapping',
        ];
    }

}
