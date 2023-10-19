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
 * Class m230820_181852_add_extension_overwrite_config_translate
 */
class m230820_181852_add_extension_overwrite_config_translate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('configuration', ['TEXT_EXTENSION_OVERWRITE_CONFIG_KEY' => 'The extension <strong>%s</strong> enhances this option</a>']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230820_181852_add_extension_overwrite_config_translate cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230820_181852_add_extension_overwrite_config_translate cannot be reverted.\n";

        return false;
    }
    */
}
