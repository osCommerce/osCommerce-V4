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
 * Class m220822_093740_info_and_invoices
 */
class m220822_093740_info_and_invoices extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
       $this->addWidget('invoice', 'lib/console/widgets/invoice.zip', 'Logo', 'splash');
	   $this->addWidget('packingslip', 'lib/console/widgets/packingslip.zip', 'Logo', 'splash');
	   $this->addWidget('info', 'lib/console/widgets/info-page.zip', 'info\Title', 'splash');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220822_093740_info_and_invoices cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220822_093740_info_and_invoices cannot be reverted.\n";

        return false;
    }
    */
}
