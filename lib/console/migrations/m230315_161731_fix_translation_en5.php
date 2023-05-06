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
 * Class m230315_161731_fix_translation_en5
 */
class m230315_161731_fix_translation_en5 extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {


        $this->addTranslation('admin/coupon_admin', ['COUPON_AMOUNT_WITH_TAX_HELP' => 'Is used to determine taxed or not if Discount is Fixed'],true);
        $this->addTranslation('admin/reviews', ['ENTRY_REVIEW_RATING' => 'Edit review/rating'],true);
        $this->addTranslation('admin/design', ['SHOW_PAGENATION' => 'Show pagination'],true);

        $this->addTranslation('admin/main',
            [
                'ERROR_TEMPLATE_IMAGE_DIRECTORY_DOES_NOT_EXIST' => "Error: Templates image directory doesn't exists:",
                'TEXT_ADJUST_EXPLANATION' => 'Order has difference happened in rounding. You may adjust it hiding difference it tax',
                'TEXT_CONFIRM_SORT_GROUPPED' => 'Do you really want to sort groupped products and place them together',
                'TEXT_MESSEAGE_SUCCESS_ADDED' => 'Successfully added',
            ],true);

        $this->addTranslation('admin/orders',
            [
                'NOT_FULL_ADDRESS' => 'Full address is not available. Authorization is not complete. Click on auth ID to check/update its status',
                'TEXT_CUSTOMER_COMMENTS' => 'Customers Comments',
            ],true);

        $this->addTranslation('main', [
                'TEXT_LOYALTY_NEED_SUBSCRIBE_TS' => 'Subscribe with email notifications and take a part in our loyalty programme',
                'TEXT_WEDDING_EXISTS' => 'Your partner already has registered wedding',
            ],true);

        if (!$this->isOldProject() && !$this->isOldExtension('WeddingRegistry')) {
            $this->removeTranslation('main', ['TEXT_WEDDING_EXISTS']);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230315_161731_fix_translation_en5 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230315_161731_fix_translation_en5 cannot be reverted.\n";

        return false;
    }
    */
}
