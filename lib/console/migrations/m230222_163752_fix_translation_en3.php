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
 * Class m230222_163752_fix_translation_en3
 */
class m230222_163752_fix_translation_en3 extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('ordertotal', ['ERROR_TOO_LONG_MESSAGE' => 'Too long message'], true);
        $this->addTranslation('admin/main', ['IMAGE_DUBLICATE' => 'Duplicate'], true);
        $this->addTranslation('admin/easypopulate', ['MESSAGE_KEY_DOMAIN_OK' => 'Your store successfully connected to our <a target="_blank" href="%1$s">application shop</a>. You \'security store key\' for this shop is [%2$s].'], true);
        $this->addTranslation('admin/easypopulate', ['MESSAGE_KEY_DOMAIN_ERROR' => 'Error: Your \'storage\' key is wrong. Please login at <a target="_blank" href="%1$s">application shop</a> with your credentials and copy it from there. You \'security store key\' for this shop is [%2$s].'], true);
        $this->addTranslation('admin/main', ['NOTICE_EMAILS_SENT' => 'Mail(s) was sent successfully'], true);
        $this->addTranslation('admin/main', ['TEXT_ERROR_ON_SAVE' => 'Error on save occurred'], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230222_163752_fix_translation_en3 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230222_163752_fix_translation_en3 cannot be reverted.\n";

        return false;
    }
    */
}
