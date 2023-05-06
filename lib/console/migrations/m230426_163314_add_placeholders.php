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
 * Class m230426_163314_add_placeholders
 */
class m230426_163314_add_placeholders extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $placeholders = [
            '74338'           => 'header-content-small',
            '117219'           => 'header-content-small',

            '74339'           => 'header-content-small-left',
            '117220'           => 'header-content-small-left',

            '74332'           => 'header-content-left',
        ];

        foreach ($placeholders as $microtime => $placeholder) {
            $this->update('design_boxes', ['widget_params' => $placeholder], ['microtime' => $microtime]);
            $this->update('design_boxes_tmp', ['widget_params' => $placeholder], ['microtime' => $microtime]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
