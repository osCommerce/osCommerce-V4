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
 * Class m220929_165749_country_currency
 */
class m220929_165749_country_currency extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/countries', [
          'TEXT_COUNTRY_CURRENCY_TIP' => '3 characters currency code Ex. GBP, EUR, USD'
        ]);
        $this->addColumnIfMissing('countries', 'currency_code', $this->string(3)->notNull()->defaultValue(''));

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m220929_165749_country_currency cannot be reverted.\n";

//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220929_165749_country_currency cannot be reverted.\n";

        return false;
    }
    */
}
