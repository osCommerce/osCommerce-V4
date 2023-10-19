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
 * Class m230914_093529_main_styles
 */
class m230914_093529_main_styles extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->isTableExists('themes_styles_main')) {
            if (!$this->isFieldExists('main_style', 'themes_styles_main')) {
                $this->addColumn('themes_styles_main', 'main_style', $this->integer(1)->notNull()->defaultValue(0));
            }
        }

        $this->addTranslation('admin/design', [
            'CHANGE_MAIN_COLORS' => 'Change main colors'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
