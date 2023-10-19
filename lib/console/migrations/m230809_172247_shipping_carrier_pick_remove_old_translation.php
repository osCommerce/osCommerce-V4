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
 * Class m230809_172247_shipping_carrier_pick_remove_old_translation
 */
class m230809_172247_shipping_carrier_pick_remove_old_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $ext = 'common\extensions\ShippingCarrierPick\Setup';
        if (method_exists($this, 'isOldProject'))
        {
            if (!$this->isOldProject() || !class_exists($ext) || method_exists($ext, 'isAppShop')) {
                $this->removeTranslation('admin/shipping-carrier-pick');
                $this->removeTranslation('admin/orders', [
                    'TEXT_BATCH_LABELS',
                    'TEXT_BATCH_LABEL_POPUP_TITLE'
                ]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addTranslation('admin/shipping-carrier-pick',[
            'HEADING_TITLE' => 'Shipping Carrier rules',
            'TEXT_CREATE_NEW_SELECTION' => 'Create new',
            'TEXT_SELECTION_NAME' => 'Selection name',
            'TEXT_CREATE_SELECTION' => 'Create Shipping Carrier rule',
            'TEXT_EDIT_SELECTION' => 'Edit Shipping Carrier rule',
            'TEXT_SHIPPING_RULE_AMOUNT_TITLE' => 'Package Amount',
            'TEXT_SHIPPING_RULE_DELIVERY_COUNTRY_TITLE' => 'Delivery Address',
            'TEXT_SHIPPING_RULE_ORDER_PAYMENT_TITLE' => 'Order Payment Method',
            'TEXT_SHIPPING_RULE_ORDER_SHIPPING_TITLE' => 'Order Shipping Method',
            'TEXT_SHIPPING_RULE_ORDER_SHIPPING_COST_TITLE' => 'Order Shipping Cost',
            'TEXT_SHIPPING_RULE_SHIPPING_WEIGHT_TITLE' => 'Package Weight',
            'TEXT_BATCH_ALL_PRODUCT_ALREADY_PROCESSED' => 'All Order Products already labeled',
            'TEXT_BATCH_NO_RULE_MATCH' =>  'Can\'t find label method automatically. No rules match.',
            'TEXT_BATCH_MODULE_NOT_FOUND' =>  'Label module "%s" not found',
        ]);

        $this->addTranslation('admin/orders',[
            'TEXT_BATCH_LABELS' => 'Shipping Label',
            'TEXT_BATCH_LABEL_POPUP_TITLE' => 'Batch create labels',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230809_172247_shipping_carrier_pick_remove_old_translation cannot be reverted.\n";

        return false;
    }
    */
}
