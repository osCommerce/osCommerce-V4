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
 * Class m230104_140128_banners
 */
class m230104_140128_banners extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/banner_manager',[
            'ENTER_BANNER_TITLE' => 'Please enter Banner Title',
            'CHOOSE_BANNER_GROUP' => 'Please choose Banner Group',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
