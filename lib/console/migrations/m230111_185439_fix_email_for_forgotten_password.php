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
 * Class m230111_185439_fix_email_for_forgotten_password
 */
class m230111_185439_fix_email_for_forgotten_password extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // fix link descrription
        $this->removeTranslation('account/password-forgotten', ['TEXT_PASSWORD_INVITATION_LINK']);
        $this->addTranslation('account/password-forgotten', [
            'TEXT_PASSWORD_INVITATION_LINK' => 'Please follow this link to reset your password',
            'TEXT_NEW_PASSWORD_SENTENCE'    => 'Your new password is:',
        ]);
        $this->removeEmailTemplate('Admin Password Forgotten');
        $this->addEmailTemplate('Admin Password Forgotten', [
            'html' => [
                'subject' => '##STORE_NAME## - New Password',
                'body' => '<p><span style="font-size:12px;">Dear ##CUSTOMER_FIRSTNAME##,</span></p>

<p><span style="font-size:12px;">New password to access the back end of&nbsp;&#39;##STORE_NAME##&#39; has just been requested.</span></p>

<p><span style="font-size:12px;">##NEW_PASSWORD_SENTENCE##</span></p>

<p><span style="font-size:12px;"><strong>##NEW_PASSWORD##</strong></span></p>

<p><span style="font-size:12px;">Please don&#39;t hesitate to contact us at&nbsp;##STORE_OWNER_EMAIL_ADDRESS## should you have any questions.</span></p>

<p><span style="font-size:12px;">Kind regards,<br />
The team of&nbsp;##STORE_NAME##</span></p>
'
            ],
            'text' => [
                'subject' => '##STORE_NAME## - New Password',
                'body' => "Dear ##CUSTOMER_FIRSTNAME##,

New password to access the back end of '##STORE_NAME##' has just been requested.

##NEW_PASSWORD_SENTENCE##
##NEW_PASSWORD##

Please don't hesitate to contact us at ##STORE_OWNER_EMAIL_ADDRESS## should you have any questions.

Kind regards,
The team of ##STORE_NAME##
"
            ],
        ]);


    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230111_185439_fix_email_for_forgotten_password cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230111_185439_fix_email_for_forgotten_password cannot be reverted.\n";

        return false;
    }
    */
}
