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
 * Class m230220_093254_banners
 */
class m230220_093254_banners extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/design',[
            'BANNERS_FROM_GROUP' => 'Banners from group',
            'BANNER_TITLE' => 'Banner title',
            'ASSIGNED_SALES_CHANNELS' => 'Assigned sales channels',
            'ARE_YOU_SURE_DELETE_BANNER' => 'Are you sure you want to delete this banner?',
            'CHOOSE_SALES_CHANNEL' => 'Please choose Sales Channel',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
