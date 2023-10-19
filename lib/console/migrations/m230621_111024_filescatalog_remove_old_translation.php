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
 * Class m230621_111024_filescatalog_remove_old_translation
 */
class m230621_111024_filescatalog_remove_old_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension'))
        {
            if (!$this->isOldExtension('FilesCatalog'))
            {
                $this->removeTranslation('admin/filescatalog');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230621_111024_filescatalog_remove_old_translation cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230621_111024_filescatalog_remove_old_translation cannot be reverted.\n";

        return false;
    }
    */
}
