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
 * Class m230711_161326_select_products
 */
class m230711_161326_select_products extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main', [
            'BATCH_BACK_LINK_TOOLTIP_TITLE' => 'If the box is checked next to the required product the product you are editing at the moment will be added as the %s to the checked product as well.',
            'ADD_SELECTED_PRODUCTS' => 'Add selected products',
            'TEXT_ADDED' => 'Added',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
