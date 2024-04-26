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
 * Class m240222_220916_texts_from_scart_to_admin
 */
class m240222_220916_texts_from_scart_to_admin extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main', [
          'TEXT_CUSTOMER_ORIGIN' => 'Customer Origin',
          'TEXT_CAMPAIGN' => 'Campaign',
          'TEXT_SEARCH_KEY' => 'Search key',
          'TEXT_BROWSER' => 'Browser',
          'TEXT_OPERATING_SYSTEM' => 'Operating System',
          'TEXT_SCREEN_RESOLUTION' => 'Screen Resolution',
          'TEXT_JAVA_SUPPORT' => 'JavaScript Support',
          'TEXT_ERRORS' => 'Errors',
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m240222_220916_texts_from_scart_to_admin cannot be reverted.\n";
//
//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240222_220916_texts_from_scart_to_admin cannot be reverted.\n";

        return false;
    }
    */
}
