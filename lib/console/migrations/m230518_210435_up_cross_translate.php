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
 * Class m230518_210435_up_cross_translate
 */
class m230518_210435_up_cross_translate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension'))
        {
            if (!$this->isOldExtension('UpSell'))
            {
                $this->removeTranslation('admin/xsell-types');
                $this->removeTranslation('admin/categories', 'FIELDSET_ASSIGNED_UPSELL_PRODUCTS');
                $this->removeTranslation('admin/main', 'FIELDSET_ASSIGNED_UPSELL_PRODUCTS');

            }
        }

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230518_210435_up_cross_translate cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230518_210435_up_cross_translate cannot be reverted.\n";

        return false;
    }
    */
}
