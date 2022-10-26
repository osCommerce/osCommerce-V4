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
 * Class m221001_093359_install_errorlogviewer_and_productstemplate
 */
class m221001_093359_install_errorlogviewer_and_productstemplate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->installExt('ErrorLogViewer');
        $this->installExt('ProductTemplates');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221001_093359_install_errorlogviewer_and_productstemplate cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221001_093359_install_errorlogviewer_and_productstemplate cannot be reverted.\n";

        return false;
    }
    */
}
