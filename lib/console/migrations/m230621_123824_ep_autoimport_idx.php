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
 * Class m230621_123824_ep_autoimport_idx
 */
class m230621_123824_ep_autoimport_idx extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createIndex('idx_direction_file', 'ep_job', [
//          'directory_id',
          'direction',
          'job_state',
          'file_name',
          'job_id',
          'run_frequency'
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m230621_123824_ep_autoimport_idx cannot be reverted.\n";

//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230621_123824_ep_autoimport_idx cannot be reverted.\n";

        return false;
    }
    */
}
