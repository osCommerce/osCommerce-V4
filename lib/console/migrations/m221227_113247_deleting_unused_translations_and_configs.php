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

use common\classes\Migration;

/**
 * Class m221227_113247_deleting_unused_translations_and_configs
 */
class m221227_113247_deleting_unused_translations_and_configs extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension')) {

            if (!$this->isOldExtension('CookieNotice')) {
                // Removing unused translation for CookieNotice
                $this->removeTranslation('main', ['TEXT_COOKIE_BUTTON', 'TEXT_COOKIE_NOTICE']);
                // Removing unused configuration for CookieNotice
                $this->removeConfigurationKeys('COOKIE_NOTICE_ID');
            }

            if (!$this->isOldExtension('CustomerCode')) {
                // Removing unused translation for CustomerCode
                $this->removeTranslation('main', ['ENTRY_ERP_CUSTOMER_CODE', 'ENTRY_ERP_CUSTOMER_ID']);
            }

            if (!$this->isOldExtension('ModulesZeroPrice')) {
                // Removing unused translation for ModulesZeroPrice
                $this->removeTranslation('admin/main', ['TEXT_ZERO_PRICE']);
                if (!\common\helpers\Extensions::isInstalled('ModulesZeroPrice')) {
                    $this->dropTableIfExists('modules_zero_price');
                }

            }

            if (!$this->isOldExtension('ReportEmailsHistory')) {
                // Removing unused translation for ReportEmailsHistory
                $this->removeTranslation('admin/main', [
                    'TEXT_HISTORY_CLEANUP_MONTH',
                    'TEXT_HISTORY_CLEANUP_TEXT',
                    'TEXT_HISTORY_CLEANUP_TITLE',
                    'TEXT_HISTORY_CLEANUP_YEAR',

                ]);
            }

            if (!$this->isOldExtension('WeddingRegistry')) {
                // Removing unused translation for WeddingRegistry
                $this->removeTranslation('main', [
                    'ADD_TO_WEDDING_REGISTRY',
                    'ADD_TO_WEDDING_REGISTRY_SUCCESS',
                    'CREATE_YOUR_REGISTRY',
                    'CREATE_YOUR_WEDDING_REGISTRY',
                    'DELETE_WEDDING_REGISTRY',
                    'DEL_FROM_WEDDING_REGISTRY',
                    'FIND_REGISTRY',
                    'REGISTRY_ITEMS',
                    'SHARE_YOUR_REGISTRY',
                    'VIEW_YOUR_REGISTRY',
                ]);
                $this->removeTranslation('checkout', [
                    'CREATE_YOUR_WEDDING_REGISTRY',
                ]);
            }

            if (!$this->isOldExtension('SMS')) {
                $this->removeTranslation('admin/sms');

                if (!\common\helpers\Extensions::isInstalled('SMS')) {
                    $this->removeAcl(['TEXT_SETTINGS', 'BOX_HEADING_SMS', 'BOX_SMS_MESSAGES']);
                    $this->removeAcl(['TEXT_SETTINGS', 'BOX_HEADING_SMS', 'BOX_SMS_CONFIGURATION']);
                    $this->removeAcl(['TEXT_SETTINGS', 'BOX_HEADING_SMS']);
                    $this->removeAdminMenu('BOX_SMS_MESSAGES');
                    $this->removeAdminMenu('BOX_HEADING_SMS');
                    $this->removeAdminMenu('BOX_SMS_CONFIGURATION');

                    $this->dropTableIfExists('sms_defaults');
                    $this->dropTableIfExists('sms_defaults_texts');
                }
            }

        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221227_113247_deleting_unused_translations_and_configs cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221227_113247_deleting_unused_translations_and_configs cannot be reverted.\n";

        return false;
    }
    */
}
