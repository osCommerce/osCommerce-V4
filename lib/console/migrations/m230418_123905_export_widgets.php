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
 * Class m230418_123905_export_widgets
 */
class m230418_123905_export_widgets extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/design',[
            'BLOCK_CONTAINS_EXTENSION_WIDGETS' => 'This block contains the extension widgets',
            'EXTENSIONS_YOU_DONT_HAVE' => 'This widget group contains widgets from extensions you don\'t have',
            'WIDGETS_NOT_INSTALLED_EXTENSIONS' => 'This widget group contains widgets from not installed extensions',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
