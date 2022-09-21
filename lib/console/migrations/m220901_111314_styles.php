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
 * Class m220901_111314_styles
 */
class m220901_111314_styles extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('themes_styles',
            ['value' => '20'],
            [
                'selector' => '.b-bottom .notify-form',
                'attribute' => 'padding-top',
                'value' => '0',
            ]);
        $this->update('themes_styles',
            ['value' => '20'],
            [
                'selector' => '.b-bottom .notify-form',
                'attribute' => 'padding-bottom',
                'value' => '0',
            ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
