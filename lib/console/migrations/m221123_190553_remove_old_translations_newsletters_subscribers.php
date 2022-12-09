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
use common\helpers\Acl;

/**
 * Class m221123_190553_remove_old_translations_newsletters_subscribers
 */
class m221123_190553_remove_old_translations_newsletters_subscribers extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeTranslation('admin/newsletters');
        $this->removeTranslation('admin/subscribers');
        $this->removeTranslation('admin/main', [
            'CONFIRM_NEWSLETTER_PASS',
            'CONFIRM_NEWSLETTER_PASS_DESCRIPTION',
            'CONFIRM_SUBSCRIPTION',
            'CONFIRM_SUBSCRIPTION_DESCRIPTION',
            'CONFIRM_UPDATE_NEWSLETTER_PASS_DESCRIPTION',
            'CONFIRM_UPDATE_SUBSCRIPTION_DESCRIPTION',
            'ENTRY_TRUSTPILOT_DISABLE_INVITE',
            'TEXT_EXTERNAL_NEWSLETTER_ADDED',
            'TEXT_EXTERNAL_NEWSLETTER_EXISTS',
            'TEXT_EXTERNAL_NEWSLETTER_EXISTS_SEVERAL',
            'TEXT_EXTERNAL_NEWSLETTER_UPDATE',
            'TEXT_EXTERNAL_NEWSLETTER_UPDATED',
            'TEXT_MAILUP_CLIENT_ID',
            'TEXT_MAILUP_CLIENT_SECRET_KEY',
            'TEXT_MAILUP_PASSWORD',
            'TEXT_MAILUP_USERNAME',
            'TEXT_NEWSLETTER_PASS',
            'TEXT_NEWSLETTER_PASSED',
            'TEXT_NEWSLETTER_PASS_FINISHED',
            'TEXT_NEWSLETTER_PASS_UPDATE',
            'TEXT_NEWSLETTER_PASS_UPDATED',
            'TEXT_TRUSTPILOT_INVITE_SENT',
            'TRUSTPILOT_ENTER_BUSINESS_UNIT_ID',
        ]);
        $this->removeTranslation('admin/main', ['TRUSTPILOT_REVIEWS']);

        if (Acl::checkExtensionAllowed('Subscribers')) {
            $this->reinstallExtTranslation('Subscribers');
        }
        if (Acl::checkExtensionAllowed('Newsletters')) {
            $this->reinstallExtTranslation('Newsletters');
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221123_190553_remove_old_translations_newsletters_subscribers cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221123_190553_remove_old_translations_newsletters_subscribers cannot be reverted.\n";

        return false;
    }
    */
}
