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
 * Class m221031_194901_tax_rate_company_Address
 */
class m221031_194901_tax_rate_company_Address extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumnIfMissing('tax_rates', 'company_address', $this->string(1024)->notNull()->defaultValue(''));
        //$this->db->createCommand("update tax_rates set company_address=''")->execute();
        $this->db->createCommand("update tax_rates set company_address='Rokicinska 168, 92412, Lodz, Poland <br>Company Registration No. 64641' where company_address='' and company_number<>'' and tax_description LIKE '%poland%'")->execute();
        $this->db->createCommand("update tax_rates set company_address='Unit 21 Fonthill Business Park, Fonthill Road, Clondalkin, D22 FR82, Ireland' where company_address='' ")->execute();


    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m221031_194901_tax_rate_company_Address cannot be reverted.\n";

//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221031_194901_tax_rate_company_Address cannot be reverted.\n";

        return false;
    }
    */
}
