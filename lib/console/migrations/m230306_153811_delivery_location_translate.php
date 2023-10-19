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
 * Class m230306_153811_delivery_location_translate
 */
class m230306_153811_delivery_location_translate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension'))
        {
            if (!$this->isOldExtension('DeliveryLocation'))
            {
                $this->removeTranslation('admin/seo-delivery-location');
            }
            
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230306_153811_delivery_location_translate cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230306_153811_delivery_location_translate cannot be reverted.\n";

        return false;
    }
    */
}
