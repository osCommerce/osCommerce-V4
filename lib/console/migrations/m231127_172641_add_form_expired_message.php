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
 * Class m231127_172641_add_form_expired_message
 */
class m231127_172641_add_form_expired_message extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('main', ['FORM_EXPIRED_ERROR_MESSAGE' => 'The form is expired. Please go back and refresh the page']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m231127_172641_add_form_expired_message cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231127_172641_add_form_expired_message cannot be reverted.\n";

        return false;
    }
    */
}
