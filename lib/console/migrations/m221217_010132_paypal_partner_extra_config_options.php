<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use common\classes\Migration;

/**
 * Class m221217_010132_paypal_partner_extra_config_options
 */
class m221217_010132_paypal_partner_extra_config_options extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        try {
            if (function_exists('exec')) {
                exec('cd ' . DIR_FS_CATALOG . ' && mysqldump --opt -h' . DB_SERVER . ' -u' . DB_SERVER_USERNAME . ' -p' . DB_SERVER_PASSWORD . ' ' . DB_DATABASE . ' configuration platforms_configuration > sql/bkconfigurations.sql');
            }

            $showCheckout = (new yii\db\Query())->from(TABLE_CONFIGURATION)->where(['configuration_key' => 'EXPRESS_PAYMENTS_AT_CHECKOUT'])->select('configuration_value')->scalar();

            $pShowCheckout = (new yii\db\Query())->from(TABLE_PLATFORMS_CONFIGURATION)->where(['configuration_key' => 'EXPRESS_PAYMENTS_AT_CHECKOUT'])
                ->select('configuration_value, platform_id')
                ->indexBy('platform_id')
                ->column();
            //True/False

            $pc = common\models\PlatformsConfiguration::find()->where(['configuration_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_LAYOUT']);
            foreach($pc->all() as $d) {

                $_show = ($d->configuration_value=='vertical'?'Vertical':'Horizontal');
                $this->insert(TABLE_PLATFORMS_CONFIGURATION, [
                    'configuration_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_SHOPPING_CART',
                    'configuration_title' => 'Show PayPal button(s) on Shopping Cart',
                    'configuration_description' => 'Show PayPal buttons on Shopping Cart Page',
                    'configuration_value' => $_show,
                    'platform_id' => $d->platform_id,
                    'configuration_group_id' => 'BOX_CONFIGURATION_MODULE',
                    'sort_order' => 1000,
                    'date_added' => new \yii\db\Expression('NOW()'),
                    'use_function' => '',
                    'set_function' => 'multiOption(\'dropdown\', array(\'Horizontal\', \'Vertical\', \'False\'), '
                ]);
                $enabled = (isset($pShowCheckout[$d['platform_id']])?$pShowCheckout[$d['platform_id']]:$showCheckout)=='True';
                $this->insert(TABLE_PLATFORMS_CONFIGURATION, [
                    'configuration_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT',
                    'configuration_title' => 'Show PayPal button on Checkout',
                    'configuration_description' => 'Show PayPal buttons on Checkout Page',
                    'configuration_value' => ($enabled?$_show:'False'),
                    'platform_id' => $d->platform_id,
                    'configuration_group_id' => 'BOX_CONFIGURATION_MODULE',
                    'sort_order' => 1000,
                    'date_added' => new \yii\db\Expression('NOW()'),
                    'use_function' => '',
                    'set_function' => 'multiOption(\'dropdown\', array(\'Horizontal\', \'Vertical\', \'False\'), '
                ]);
                $this->insert(TABLE_PLATFORMS_CONFIGURATION, [
                    'configuration_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT_LOGIN',
                    'configuration_title' => 'Show PayPal button on Checkout Login',
                    'configuration_description' => 'Show PayPal buttons on Checkout Login Page',
                    'configuration_value' => 'False',
                    'platform_id' => $d->platform_id,
                    'configuration_group_id' => 'BOX_CONFIGURATION_MODULE',
                    'sort_order' => 1000,
                    'date_added' => new \yii\db\Expression('NOW()'),
                    'use_function' => '',
                    'set_function' => 'multiOption(\'dropdown\', array(\'Horizontal\', \'Vertical\', \'False\'), '
                ]);
                $d->delete();
            }

            $pProductLayout = (new yii\db\Query())->from(TABLE_PLATFORMS_CONFIGURATION)->where(['configuration_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_LAYOUT_PRODUCT'])
                ->select('configuration_value, platform_id')
                ->indexBy('platform_id')
                ->column()
                ;

            $pP = common\models\PlatformsConfiguration::find()->where(['configuration_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_BUY_IMMEDIATELLY']);
            foreach($pP->all() as $d) {
                if ($d->configuration_value == 'True') {
                    $_show =  ($pProductLayout[$d->platform_id]=='vertical'?'Vertical':'Horizontal') ;
                    $d->configuration_value = $_show;
                }
                $d->set_function = 'multiOption(\'dropdown\', array(\'Horizontal\', \'Vertical\', \'False\'), ';
                $d->save(false);
            }

            common\models\PlatformsConfiguration::deleteAll(['configuration_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_LAYOUT_PRODUCT']);
            common\models\PlatformsConfiguration::deleteAll(['configuration_key' => 'EXPRESS_PAYMENTS_AT_CHECKOUT']);
            common\models\Configuration::deleteAll(['configuration_key' => 'EXPRESS_PAYMENTS_AT_CHECKOUT']);


        } catch (\Exception $ex) {
            
            echo $ex->getMessage();

        }

        // MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_LAYOUT => MODULE_PAYMENT_PAYPAL_PARTNER_SHOPPING_CART
        // MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT_LOGIN
        // MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT (if EXPRESS_PAYMENTS_AT_CHECKOUT == True)
        //
        //
        // MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_LAYOUT_PRODUCT => MODULE_PAYMENT_PAYPAL_PARTNER_BUY_IMMEDIATELLY==True MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_LAYOUT_PRODUCT: False

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m221217_010132_paypal_partner_extra_config_options cannot be reverted.\n";

//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221217_010132_paypal_partner_extra_config_options cannot be reverted.\n";

        return false;
    }
    */
}
