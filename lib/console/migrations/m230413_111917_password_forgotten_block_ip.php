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
 * Class m230413_111917_password_forgotten_block_ip
 */
class m230413_111917_password_forgotten_block_ip extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTableIfNotExists('admin_password_forgot_log', [
            'apflDeviceId' => $this->string(64)->notNull(),
            'apflDateCreate' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], ['apflDeviceDate' => 'apflDeviceId', 'apflDateCreate']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }
}
