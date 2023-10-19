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
 * Class m230628_142037_category_name_length
 */
class m230628_142037_category_name_length extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableSchema = $this->getDb()->getTableSchema('categories_description', true);
        if ( $tableSchema->getColumn('categories_name')->size<255 ) {
            $this->alterColumn('categories_description', 'categories_name', $this->string(255)->notNull()->defaultValue(''));
        }
        if ( $tableSchema->getColumn('categories_heading_title')->size<255 ) {
            $this->alterColumn('categories_description', 'categories_heading_title', $this->string(255)->null());
        }
        if ( $tableSchema->getColumn('rel_canonical')->size<255 ) {
            $this->alterColumn('categories_description', 'rel_canonical', $this->string(255)->notNull()->defaultValue(''));
        }

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230628_142037_category_name_length cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230628_142037_category_name_length cannot be reverted.\n";

        return false;
    }
    */
}
