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
 * Class m221204_183128_change_translation_elv
 */
class m221204_183128_change_translation_elv extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeTranslation('extensions/error-log-viewer', [
            'EXT_ELV_ERR_CREATE_ZIP'
        ]);
        $this->addTranslation('extensions/error-log-viewer', [
            'EXT_ELV_ERR_CREATE_ZIP' => 'Failure to create zip file. Check permission on dir %s',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221204_183128_change_translation_elv cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221204_183128_change_translation_elv cannot be reverted.\n";

        return false;
    }
    */
}
