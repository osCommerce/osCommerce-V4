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
 * Class m230714_135902_remove_old_bonusactions_translations
 */
class m230714_135902_remove_old_bonusactions_translations extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeTranslation('admin/customers' , [
            'ENTRY_BONUS_AMOUNT',
            'ENTRY_BONUS_HISTORY',
        ]);
        $this->removeTranslation('main' , [
            'TRANSFER_BONUS_POINTS_WARNING',
        ]);
        $this->removeTranslation('admin/main' , [
            'TRANSFER_BONUS_POINTS_TO_CREDIT_AMOUNT_TEXT',
            'TRANSFER_BONUS_POINTS_NOTIFY',
            'TRANSFER_BONUS_POINTS_WARNING',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addTranslation('admin/customers' , [
            'ENTRY_BONUS_AMOUNT' => 'Bonus amount',
            'ENTRY_BONUS_HISTORY' => 'Bonus editing history',
        ]);
        $this->addTranslation('main' , [
            'TRANSFER_BONUS_POINTS_WARNING' => 'Transfer %s bonus(es)',
        ]);
        $this->addTranslation('admin/main' , [
            'TRANSFER_BONUS_POINTS_TO_CREDIT_AMOUNT_TEXT' => 'Transfer To Credit Amount',
            'TRANSFER_BONUS_POINTS_NOTIFY' => '*Notification of the user according to the checked checkboxes in the corresponding blocks.',
            'TRANSFER_BONUS_POINTS_WARNING' => 'Transfer %s bonus(es)',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230714_135902_remove_old_bonusactions_translations cannot be reverted.\n";

        return false;
    }
    */
}
