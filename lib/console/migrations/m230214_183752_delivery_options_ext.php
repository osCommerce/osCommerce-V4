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
 * Class m230214_183752_delivery_options_ext
 */
class m230214_183752_delivery_options_ext extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension'))
        {
            if (!$this->isOldExtension('DeliveryOptions'))
            {
                $this->removeTranslation('checkout', [
                    'TEXT_DELIVERY_OPTIONS',
                    'TEXT_STANDARD_DELIVERY',
                    'TEXT_SELECT_SLOT',
                ]);
                $this->removeTranslation('admin/categories/productedit', ['TEXT_ALLOW_DELIVERY_OPTIONS']);
                $this->removeConfigurationKeys('DeliveryOptions_EXTENSION_TAX_CLASS');
            }
            if (!\common\helpers\Extensions::isInstalled('DeliveryOptions'))
            {
                $this->dropTables([
                    'delivery_options',
                    'delivery_options_description',
                    'orders_delivery_options',
                    'orders_products_delivery_options',
                    'delivery_options_products',
                ]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230214_183752_delivery_options_ext cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230214_183752_delivery_options_ext cannot be reverted.\n";

        return false;
    }
    */
}
