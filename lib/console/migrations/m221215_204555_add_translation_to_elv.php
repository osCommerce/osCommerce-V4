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
 * Class m221215_204555_add_translation_to_elv
 */
class m221215_204555_add_translation_to_elv extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('extensions/error-log-viewer', [
            'EXT_ELV_TEXT_ERROR_DESCRIPTION' => 'Error description',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->removeTranslation('extensions/error-log-viewer', ['EXT_ELV_TEXT_ERROR_DESCRIPTION']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221215_204555_add_translation_to_elv cannot be reverted.\n";

        return false;
    }
    */
}
