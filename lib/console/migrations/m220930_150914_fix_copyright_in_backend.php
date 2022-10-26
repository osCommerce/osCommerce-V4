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
 * Class m220930_150914_fix_copyright_in_backend
 */
class m220930_150914_fix_copyright_in_backend extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeTranslation('admin/main', ['TEXT_COPYRIGHT']);
        $copyright = \common\helpers\Translation::getTranslationValue('TEXT_COPYRIGHT', 'main', 1);
        $this->addTranslation('admin/main', ['TEXT_COPYRIGHT' => $copyright]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220930_150914_fix_copyright_in_backend cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220930_150914_fix_copyright_in_backend cannot be reverted.\n";

        return false;
    }
    */
}
