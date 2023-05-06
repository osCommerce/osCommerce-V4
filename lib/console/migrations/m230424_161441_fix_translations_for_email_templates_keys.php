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
 * Class m230424_161441_fix_translations_for_email_templates_keys
 */
class m230424_161441_fix_translations_for_email_templates_keys extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('keys', [
            '##CUSTOMER_LASTNAME##'     => 'Customer last name',
            '##SECURITY_KEY##'          => 'Security key',
            '##TRACKING_NUMBER##'       => 'Tracking number',
            '##TRACKING_NUMBER_URL##'   => 'Tracking number url',
        ] );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230424_161441_fix_translations_for_email_templates_keys cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230424_161441_fix_translations_for_email_templates_keys cannot be reverted.\n";

        return false;
    }
    */
}
