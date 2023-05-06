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
 * Class m230302_111052_fix_translation_en4
 */
class m230302_111052_fix_translation_en4 extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main',
            [
                'TEXT_PROMPT_DELETE' => 'Are you sure you want to delete this transaction (locally)?',
                'TEXT_PROMPT_VOID' => 'Are you sure you want to void this transaction?',
                'TEXT_REINDEX_GROUPPED' => 'Place grouped products together',
            ],
            true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230302_111052_fix_translation_en4 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230302_111052_fix_translation_en4 cannot be reverted.\n";

        return false;
    }
    */
}
