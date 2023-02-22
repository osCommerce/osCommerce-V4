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
 * Class m230207_124828_translate
 */
class m230207_124828_translate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main',[
            'DATA_FROM_NETWORK_CHANGED' => 'Some data from your network or your browser has changed, some features will not work, please re-login to fix it.',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
