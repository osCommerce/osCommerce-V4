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
 * Class m220806_151620_smtp_mail_update
 */
class m220806_151620_smtp_mail_update extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('configuration',[
            'configuration_key' => 'SMTP_ENCRYPTION',
            'configuration_title' => 'SMTP Encryption',
            'set_function' => 'multiOption(\'dropdown\', [\'\'=>\'Regular\', \'ssl\'=>\'Secure to regular port (STARTTLS)\', \'tls\'=>\'Secure to dedicated port (TLS)\'], ',
            'configuration_description' => 'SMTP Encryption',
            'configuration_group_id' => 'BOX_CONFIGURATION_E_MAIL_OPTIONS',
            'sort_order' => 12,
            'date_added' => new \yii\db\Expression('NOW()'),
        ]);
        $this->update('configuration', ['configuration_value'=>'Swiftmailer'], ['configuration_key'=>'SMTP_MAILER','configuration_value'=>'']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('configuration',['configuration_key'=>'SMTP_ENCRYPTION']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220806_151620_smtp_mail_update cannot be reverted.\n";

        return false;
    }
    */
}
