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
 * Class m221019_103845_departments
 */
class m221019_103845_departments extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ( $this->isTableExists('departments') ) {
            if (!$this->isFieldExists('keep_alive', 'departments')) {
                $this->addColumn('departments', 'keep_alive', $this->integer(1)->notNull()->defaultValue(0));
            }
        }
        $this->addTranslation('admin/departments', [
            'DEPARTMENT_KEEP_ALIVE' => 'Keep alive',
            'DEPARTMENT_DATE_ADDED' => 'Date added',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221019_103845_departments cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221019_103845_departments cannot be reverted.\n";

        return false;
    }
    */
}
