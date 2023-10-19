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
 * Class m230906_115705_contact_us_enquiry_template
 */
class m230906_115705_contact_us_enquiry_template extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addEmailTemplate('Contact Us Enquiry', [
            'html' => [
                'subject' => 'Message from site visitor',
                'body' => '<p>##ENQUIRY_CONTENT##</p>
<p>-------------------------</p>
<p>Name: ##CUSTOMER_NAME##<br>
Email: ##CUSTOMER_EMAIL##</p>',
            ],
            'text' => [
                'subject' => 'Message from site visitor',
                'body' => '##ENQUIRY_CONTENT##

-------------------------

Name: ##CUSTOMER_NAME##
Email: ##CUSTOMER_EMAIL##',
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230906_115705_contact_us_enquiry_template cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230906_115705_contact_us_enquiry_template cannot be reverted.\n";

        return false;
    }
    */
}
