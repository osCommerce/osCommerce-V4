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
 * Class m230630_144720_cron_delete_old_translation
 */
class m230630_144720_cron_delete_old_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $ext = 'common\extensions\CronScheduler\Setup';
        if (method_exists($this, 'isOldProject'))
        {
            if (!$this->isOldProject() || !class_exists($ext) || method_exists($ext, 'isAppShop')) {
                $this->removeTranslation('admin/main',[
                    'CREATE_NEW_JOB',
                    'MAX_EXECUTION_TIME_TEXT',
                    'ARGUMENTS_TEXT',
                    'COMMAND_TEXT',
                    'SCHEDULE_TEXT',
                    'OUTPUT_TO_FILE_TEXT',
                    'OUTPUT_SEND_ON_EMAIL',
                    'APPEND_TEXT',
                    'REWRITE_TEXT',
                ]);
            }
        }


    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addTranslation('admin/main',[
            'CREATE_NEW_JOB' => 'Create new job',
            'MAX_EXECUTION_TIME_TEXT' => 'Max execution time',
            'ARGUMENTS_TEXT' => 'Arguments',
            'COMMAND_TEXT' => 'Command',
            'SCHEDULE_TEXT' => 'Schedule',
            'OUTPUT_TO_FILE_TEXT' => 'Output to file',
            'OUTPUT_SEND_ON_EMAIL' => 'Output send on email',
            'APPEND_TEXT' => 'Append',
            'REWRITE_TEXT' => 'Rewrite',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230630_144720_cron_delete_old_translation cannot be reverted.\n";

        return false;
    }
    */
}
