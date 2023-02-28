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
 * Class m230227_093502_oscb_settings_inc_ver
 */
class m230227_093502_oscb_settings_inc_ver extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->isTableExists('connector_osclink_configuration')) {
            $this->db->createCommand("UPDATE connector_osclink_configuration SET cmc_value = 'https://oscommerce22.tllab.co.uk/oscb156/' WHERE cmc_key = 'api_url' AND cmc_value LIKE 'https://oscommerce22.tllab.co.uk/oscb152%'")->execute();
        }
        if (\common\helpers\Extensions::isAllowed('OscLink')) {
            $this->addTranslation('extensions/osclink', ['EXTENSION_OSCLINK_API_MEASUREMENT' => 'System of measurement into osCommerce 2.x']);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230227_093502_oscb_settings_inc_ver cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230227_093502_oscb_settings_inc_ver cannot be reverted.\n";

        return false;
    }
    */
}
