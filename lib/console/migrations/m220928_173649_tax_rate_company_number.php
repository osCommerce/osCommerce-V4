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
 * Class m220928_173649_tax_rate_company_number
 */
class m220928_173649_tax_rate_company_number extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumnIfMissing('tax_rates', 'company_number', $this->string(128)->notNull()->defaultValue(''));
        //ENTRY_BUSINESS
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
/*        echo "m220928_173649_tax_rate_company_number cannot be reverted.\n";

        return false;*/
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220928_173649_tax_rate_company_number cannot be reverted.\n";

        return false;
    }
    */
}
