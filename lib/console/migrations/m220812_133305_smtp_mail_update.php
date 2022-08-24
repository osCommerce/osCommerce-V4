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
 * Class m220812_133305_smtp_mail_update
 */
class m220812_133305_smtp_mail_update extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->getDb()->createCommand(
            "DELETE FROM configuration WHERE configuration_key = 'SMTP_ENCRYPTION' AND LENGTH(set_function)=0"
        )->execute();

        $this->update(
            'configuration',
            [
                'set_function' => 'multiOption(\'dropdown\', [\'\'=>\'Regular\', \'tls\'=>\'Secure to regular port (STARTTLS)\', \'ssl\'=>\'Secure to dedicated port (TLS)\'], ',
            ],
            [
                'configuration_key' => 'SMTP_ENCRYPTION',
            ]
        );
        $this->addTranslation('configuration', [
            'SMTP_ENCRYPTION_VALUE_' => 'Regular',
            'SMTP_ENCRYPTION_VALUE_TLS' => 'Secure to regular port (STARTTLS)',
            'SMTP_ENCRYPTION_VALUE_SSL' => 'Secure to dedicated port (TLS)',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220812_133305_smtp_mail_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220812_133305_smtp_mail_update cannot be reverted.\n";

        return false;
    }
    */
}
