<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use common\classes\Migration;

/**
 * Class m221117_115255_pos_restricted_ips
 */
class m221117_115255_pos_restricted_ips extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'POS_STRICT_ACCESS_STATUS'])->exists();
        if (!$check) {
            $this->insert('configuration', [
                'configuration_title' => 'POS restrict access - Status',
                'configuration_key' => 'POS_STRICT_ACCESS_STATUS',
                'configuration_value' => 'TRUE',
                'configuration_description' => 'Restrict access to the POS by IP',
                'configuration_group_id' => 'BOX_CONFIGURATION_ADMIN',
                'sort_order' => 100,
                'date_added' => new \yii\db\Expression('NOW()'),
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'),',
            ]);
        }
        $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'POS_STRICT_ACCESS_ALLOWED_IP'])->exists();
        if (!$check) {
            $this->insert('configuration', [
                'configuration_title' => 'POS restrict access - Allowed IPs',
                'configuration_key' => 'POS_STRICT_ACCESS_ALLOWED_IP',
                'configuration_value' => '127.0.0.1',
                'configuration_description' => 'Comma separated list of allowed IP addresses (subnet is allowed also)',
                'configuration_group_id' => 'BOX_CONFIGURATION_ADMIN',
                'sort_order' => 110,
                'date_added' => new \yii\db\Expression('NOW()'),
                'set_function' => '',
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m221117_115255_pos_restricted_ips cannot be reverted.\n";

  //      return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221117_115255_pos_restricted_ips cannot be reverted.\n";

        return false;
    }
    */
}
