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
 * Class m220915_103749_translate
 */
class m220915_103749_translate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/categories',[
            'TEXT_THIS_IS_SWITCH_TO' => 'This is <b>%s</b>.<br>Switch to <b>%s</b>.',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->removeTranslation('admin/categories',[
            'TEXT_THIS_IS_SWITCH_TO',
        ]);
    }
}
