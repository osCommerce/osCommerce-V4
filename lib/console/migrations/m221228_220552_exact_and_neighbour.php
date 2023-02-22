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
 * Class m221228_220552_exact_and_neighbour
 */
class m221228_220552_exact_and_neighbour extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if(method_exists($this, 'isOldExtension'))
        {
            // Removing unused data for ExactOnline
            if(!$this->isOldExtension('ExactOnline'))
            {
                // Removing translation
                $this->removeTranslation('admin/exact_online');
                $this->removeTranslation('admin/main', 'BOX_HEADING_EXACT_ONLINE');
                // Removing config keys
                $this->removeConfigurationKeysInGroup('BOX_CONFIGURATION_EXACT');

                if(!\common\helpers\Extensions::isInstalled('ExactOnline'))
                {
                    // Removing unused columns
                    $this->dropColumnIfExists('tax_rates', 'tax_type');
                    $this->dropColumnIfExists('products', 'exact_id');
                    // Removing datatable
                    $this->dropTableIfExists('exact_crons');
                    // Removing ACL
                    $this->removeAcl(['TEXT_SETTINGS', 'BOX_HEADING_EXACT_ONLINE']);
                    // Removing Admin Menu
                    $this->removeAdminMenu('BOX_HEADING_EXACT_ONLINE');
                }
            }

            if(!$this->isOldExtension('Neighbour'))
            {
                // Removing translation
                $this->removeTranslation('admin/orders', [
                    'TEXT_NEIGHBOUR_COMMENT',
                    'TABLE_HEADING_NEIGHBOUR',
                ]);
                $this->removeTranslation('main', ['WOULD_LIKE_LEAVE_NEIGHBOUR']);

                if(!\common\helpers\Extensions::isInstalled('Neighbour'))
                {
                    // Removing datatable
                    $this->dropTableIfExists('orders_to_neighbour');
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221228_220552_exact_and_neighbour cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221228_220552_exact_and_neighbour cannot be reverted.\n";

        return false;
    }
    */
}
