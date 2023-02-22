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
 * Class m230131_195925_merge_customers_ext
 */
class m230131_195925_merge_customers_ext extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if(method_exists($this, 'isOldExtension'))
        {
            if (!$this->isOldExtension('MergeCustomers'))
            {
                $this->removeTranslation('admin/customers', [
                    'TEXT_THE_PHONE',
                    'TEXT_AND_EMAIL',
                    'TEXT_MERGING_CUSTOMER',
                    'TEXT_CHOOSE_CUSTOMER',
                    'TEXT_ADDRESS',
                    'TEXT_CHOOSE_TYPE',
                    'IMAGE_MERGE',
                    'HEADING_TITLE_MERGE',
                ]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addTranslation('admin/customers', [
            'TEXT_THE_PHONE' => 'The phone number',
            'TEXT_AND_EMAIL' => 'and the email address',
            'TEXT_MERGING_CUSTOMER' => 'will stay the same after merging customers. All orders will be merged to one account.',
            'TEXT_CHOOSE_CUSTOMER' => 'Choose customer and addresses for merging accounts',
            'TEXT_ADDRESS' => 'Address',
            'TEXT_CHOOSE_TYPE' => 'Type to choose customer',
            'IMAGE_MERGE' => 'Merge',
            'HEADING_TITLE_MERGE' => 'Merge customer',
            '' => '',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230131_195925_merge_customers_ext cannot be reverted.\n";

        return false;
    }
    */
}
