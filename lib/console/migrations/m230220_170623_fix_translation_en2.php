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
 * Class m230220_170623_fix_translation_en2
 */
class m230220_170623_fix_translation_en2 extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/printers',
            [
                'ERROR_INVALID_DOCUMENT_ASSIGNMENT' => 'Invalid document assignment',
                'TEXT_ACCEPTED_ALREADY' => 'Already accepted',
                'TEXT_ACCEPTED_SUCCESSFULY' => 'Accepted successfully',
            ], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230220_170623_fix_translation_en2 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230220_170623_fix_translation_en2 cannot be reverted.\n";

        return false;
    }
    */
}
