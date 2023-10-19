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
 * Class m230614_122146_notify_products_date_remove_old_translation
 */
class m230614_122146_notify_products_date_remove_old_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension')) {
            if (!$this->isOldExtension('NotifyProductsDate')) {

                $this->removeTranslation('admin/main', ['TEXT_NOTIFY_PRODUCT_DATE']);

                $this->removeTranslation('main', [
                    'TEXT_NOTIFY_PRODUCT_DATE_CHANGED',
                    'TEXT_NOTIFY_PRODUCT_DATE_CHANGED_PLAIN',
                ]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addTranslation('admin/main', [
            'TEXT_NOTIFY_PRODUCT_DATE' => 'Notify subscribers about date change',
        ]);
        $this->addTranslation('main', [
            'TEXT_NOTIFY_PRODUCT_DATE_CHANGED' => '<p><a href="%s"><img alt="%s" src="%s" /></a>%s<br>'
                . 'We are currently awaiting a stock delivery from our supplier. We are expecting stock to arrive on %s</p>',
            'TEXT_NOTIFY_PRODUCT_DATE_CHANGED_PLAIN' => '%s %s \r\n'
                . 'We are currently awaiting a stock delivery from our supplier. We are expecting stock to arrive on %s',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230614_122146_notify_products_date_remove_old_translation cannot be reverted.\n";

        return false;
    }
    */
}
