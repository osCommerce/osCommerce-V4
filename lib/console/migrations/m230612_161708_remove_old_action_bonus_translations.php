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
 * Class m230612_161708_remove_old_action_bonus_translations
 */
class m230612_161708_remove_old_action_bonus_translations extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isOldProject()) {
            $this->removeTranslation('main', [
                'TEXT_CONVERT_FROM_BONUS_POINTS',
                'TEXT_BONUS_POINTS',
                'TEXT_POINTS_EARNT',
                'TEXT_POINTS_FOR',
                'TEXT_POINTS_ADDED',
                //'TEXT_POINTS_EARN', - into account: can't be added from install
                'TEXT_POINTS_REDEEM',
                'TEXT_CONVERT_TO_AMOUNT',
                'TEXT_TRANSFER_SELECTED_BONUS_POINTS_TO_CREDIT_AMOUNT',
            ]);
            $this->removeTranslation('admin/main', [
                'TEXT_CONVERT_FROM_BONUS_POINTS',
                'TRANSFER_BONUS_SUCCESS',
                'TEXT_BONUS_POINTS',
                'TEXT_POINTS_REDEEM',
                'TEXT_POINTS_EARN',
                'TEXT_CONVERT_TO_AMOUNT',
            ]);
            $this->removeTranslation('admin/design', [
                'TEXT_BONUS_POINTS',
                'TEXT_POINTS_EARNT',
            ]);
            $this->removeTranslation('promotions', [
                'TEXT_PROMO_AWARD' => 'Points Awarded',
            ]);
            $this->removeTranslation('promotions', [
                'TEXT_PROMO_AWARD',
            ]);
            $this->removeTranslation('admin/categories', [
                'TEXT_BONUS_POINTS',
                'TEXT_POINTS_COST',
                'TEXT_ENABLE_POINTSE',
            ]);

        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addTranslation('main', [
            'TEXT_CONVERT_FROM_BONUS_POINTS' => 'Convert from Bonus Points',
            'TEXT_BONUS_POINTS'  => 'You have %s Bonus Points',
            'TEXT_POINTS_EARNT'  => 'Points earnt',
            'TEXT_POINTS_FOR'    => 'For',
            'TEXT_POINTS_ADDED'  => 'points added',
            'TEXT_POINTS_EARN'   => 'points earn',
            'TEXT_POINTS_REDEEM' => 'points redeem',
            'TEXT_CONVERT_TO_AMOUNT' => 'Convert to Amount',
            'TEXT_TRANSFER_SELECTED_BONUS_POINTS_TO_CREDIT_AMOUNT' => 'Transfer Selected Bonus Points To Credit Amount',
        ]);
        $this->addTranslation('admin/main', [
            'TEXT_CONVERT_FROM_BONUS_POINTS' => 'Convert from Bonus Points',
            'TRANSFER_BONUS_SUCCESS' => 'Transfer %s bonus(es) to %s Credit Amount - Successfully',
            'TEXT_BONUS_POINTS'  => 'Customer have %s Bonus Points',
            'TEXT_POINTS_REDEEM' => 'points redeem',
            'TEXT_POINTS_EARN'   => 'points earn',
            'TEXT_CONVERT_TO_AMOUNT' => 'Convert to Amount',
        ]);
        $this->addTranslation('admin/design', [
            'TEXT_BONUS_POINTS' => 'Bonus Points',
            'TEXT_POINTS_EARNT' => 'Points Earned',
        ]);
        $this->addTranslation('admin/categories', [
            'TEXT_BONUS_POINTS' => 'Bonus Points Price',
            'TEXT_POINTS_COST'  => 'Points Cost<i>(Earn)</i>',
            'TEXT_ENABLE_POINTSE'  => 'Enable points to this product',
        ]);
    }
    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230612_161708_remove_old_action_bonus_translations cannot be reverted.\n";

        return false;
    }
    */
}
