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
 * Class m221204_203334_add_translation_elv_fix
 */
class m221204_203334_add_translation_elv_fix extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('extensions/error-log-viewer', [
            'EXT_ELV_ERR_EXT_DIR_NOT_WRITABLE' => 'Extension dir must be writable. Set chmod (linux) 0777',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221204_203334_add_translation_elv_fix cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221204_203334_add_translation_elv_fix cannot be reverted.\n";

        return false;
    }
    */
}
