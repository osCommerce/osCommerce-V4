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
 * Class m230217_105317_designer
 */
class m230217_105317_designer extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/design',[
            'APPLY_MIGRATION' => 'Apply Migration',
            'CREATE_MIGRATION' => 'Create Migration',
        ]);

        if (!$this->isFieldExists('mode', 'themes_steps')) {
            $this->addColumn('themes_steps', 'mode', $this->string(32)->notNull()->defaultValue(''));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
