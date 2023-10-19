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
 * Class m230424_083825_registration
 */
class m230424_083825_registration extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTableIfNotExists('customers_email_validation', [
            'cev_id' => $this->primaryKey(11)->append('AUTO_INCREMENT'),
            'cev_email' => $this->string(64)->notNull(),
            'cev_code' => $this->string(64)->notNull(),
        ]);
        
        $this->addTranslation('main',[
            'TEXT_EMAIL_VERIFICATION' => 'Start email validation',
            'TEXT_VERIFICATION_CODE' => 'Verification code for email address',
            'EMPTY_EMAIL_ERROR' => 'Email address cant be empty',
            'ENTRY_VERIFICATION_CODE_ERROR' => 'Wrong email verification code',
        ]);
        
        $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'FLAG_EMAIL_VERIFICATION'])->exists();
        if (!$check) {
            $this->insert('configuration',[
                'configuration_title' => 'Email verification before registration',
                'configuration_key' => 'FLAG_EMAIL_VERIFICATION',
                'configuration_value' => 'False',
                'configuration_description' => 'Check email address with help sending code',
                'configuration_group_id' => 'BOX_CONFIGURATION_CUSTOMER_DETAILS',
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'),',
                'sort_order' => 201,
                'date_added' => new \yii\db\Expression('NOW()'),
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230424_083825_registration cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230424_083825_registration cannot be reverted.\n";

        return false;
    }
    */
}
