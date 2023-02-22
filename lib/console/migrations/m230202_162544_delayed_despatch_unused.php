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
 * Class m230202_162544_delayed_despatch_unused
 */
class m230202_162544_delayed_despatch_unused extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if(method_exists($this, 'isOldExtension'))
        {
            if(!$this->isOldExtension('DelayedDespatch'))
            {
                $this->removeTranslation('admin/orders', [
                    'TEXT_DESPATCH_HOVER',
                    'ENTRY_DELIVERY_DATE_ERROR',
                    'HEADING_SHIPPING_DELIVERY_DATE',
                    'TEXT_DELIVERY_DATE',
                ]);

                $this->removeTranslation('checkout', [
                    'TEXT_DESPATCH_HOVER',
                    'ENTRY_DELIVERY_DATE_ERROR',
                    'TEXT_DESPATCH_DAY',
                    'JS_DELIVERY_DATE_ERROR',
                ]);

                $this->removeTranslation('main', [
                    'HEADING_SHIPPING_DELIVERY_DATE',
                    'TEXT_DELIVERY_DATE',
                    'EMAIL_TEXT_DISPATCH_DATE_T',
                ]);

                $this->removeTranslation('admin/main', [
                    'EMAIL_TEXT_DISPATCH_DATE_T',
                ]);

                $this->removeConfigurationKeys([
                    'DELIVERY_DATE_STATUS',
                    'MODERATOR_DELIVERY_DATE_STATUS',
                    'DELIVERY_DATE_DISABLED_DAYS',
                    'DELIVERY_DATE_DISABLED_EXCLUSION',
                ]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        if(!defined(TABLE_CONFIGURATION)) define(TABLE_CONFIGURATION, 'configuration');

        $this->addTranslation('admin/orders', [
            'TEXT_DESPATCH_HOVER' => 'Selecting a date will mean we delay the despatch of your order until your preferred despatch date. Your order will then be shipped as per our delivery schedule. So, for example, if you want your Express Delivery order delivered on Saturday you should select Friday as your preferred despatch date.',
            'ENTRY_DELIVERY_DATE_ERROR' => 'Delivery day not valid',
            'HEADING_SHIPPING_DELIVERY_DATE' => 'Preferred despatch date',
            'TEXT_DELIVERY_DATE' => 'Delivery Date',
        ]);

        $this->addTranslation('checkout', [
            'TEXT_DESPATCH_HOVER' => 'Selecting a date will mean we delay the despatch of your order until your preferred despatch date. Your order will then be shipped as per our delivery schedule. So, for example, if you want your Express Delivery order delivered on Saturday you should select Friday as your preferred despatch date.',
            'ENTRY_DELIVERY_DATE_ERROR' => 'Delivery day not valid',
            'TEXT_DESPATCH_DAY' => 'If you require us to delay the despatch of your order (away on holiday or similar) please select your preferred despatch day from the calendar drop-down',
            'JS_DELIVERY_DATE_ERROR' => 'Select Delivery day',
        ]);

        $this->addTranslation('main', [
            'HEADING_SHIPPING_DELIVERY_DATE' => 'PREFERRED DESPATCH DATE (IF REQUIRED)',
            'TEXT_DELIVERY_DATE' => 'Delivery Date',
            'EMAIL_TEXT_DISPATCH_DATE_T' => 'Despatch Date: <delivery_date>. Delayed Despatch selected - if incorrect please let us know by reply asap. Your order will not be shipped until <delivery_date>.',
        ]);

        $this->addTranslation('admin/main', [
            'EMAIL_TEXT_DISPATCH_DATE_T' => 'Despatch Date: <delivery_date>. Delayed Despatch selected - if incorrect please let us know by reply asap. Your order will not be shipped until <delivery_date>.',
        ]);

        $this->insert(TABLE_CONFIGURATION, [
            'configuration_title' => 'Delivery Date',
            'configuration_key' => 'DELIVERY_DATE_STATUS',
            'configuration_value' => 'Enabled - Optional',
            'configuration_description' => '',
            'configuration_group_id' => 'BOX_CONFIGURATION_SHIPPING_PACKAGING',
            'sort_order' => 200,
            'date_added' => new \yii\db\Expression('NOW()'),
            'set_function' => 'tep_cfg_select_option(array(\'Disabled\', \'Enabled - Optional\', \'Enabled - Required\'), ',
        ]);

        $this->insert(TABLE_CONFIGURATION, [
            'configuration_title' => 'Delivery Date Management',
            'configuration_key' => 'MODERATOR_DELIVERY_DATE_STATUS',
            'configuration_value' => 'True',
            'configuration_description' => 'Group Adminsitrators can set Delivery Date before order pay',
            'configuration_group_id' => 'BOX_CONFIGURATION_SHIPPING_PACKAGING',
            'sort_order' => 201,
            'date_added' => new \yii\db\Expression('NOW()'),
            'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        ]);

        $this->insert(TABLE_CONFIGURATION, [
            'configuration_title' => 'Disabled dates',
            'configuration_key' => 'DELIVERY_DATE_DISABLED_DAYS',
            'configuration_value' => 'Saturday, Sunday',
            'configuration_description' => 'Disabled dates',
            'configuration_group_id' => 'BOX_CONFIGURATION_SHIPPING_PACKAGING',
            'sort_order' => 204,
            'date_added' => new \yii\db\Expression('NOW()'),
            'set_function' => 'tep_cfg_select_multioption(array(\'Monday\', \'Tuesday\', \'Wednesday\', \'Thursday\', \'Friday\', \'Saturday\', \'Sunday\'), ',
        ]);

        $this->insert(TABLE_CONFIGURATION, [
            'configuration_title' => 'Disabled dates exclusion',
            'configuration_key' => 'DELIVERY_DATE_DISABLED_EXCLUSION',
            'configuration_value' => '2017-09-07',
            'configuration_description' => 'Disabled dates',
            'configuration_group_id' => 'BOX_CONFIGURATION_SHIPPING_PACKAGING',
            'sort_order' => 206,
            'date_added' => new \yii\db\Expression('NOW()'),
            'set_function' => 'tep_cfg_textarea(',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230202_162544_delayed_despatch_unused cannot be reverted.\n";

        return false;
    }
    */
}
