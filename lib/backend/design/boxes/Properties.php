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

namespace backend\design\boxes;

use Yii;
use yii\base\Widget;

class Properties extends Widget
{

    public $id;
    public $params;
    public $settings;
    public $visibility;

    public function init()
    {
        parent::init();
    }

    public function run()
    {

        global $languages_id;

        $product = tep_db_fetch_array(tep_db_query("
              select products_name
              from " . TABLE_PRODUCTS_DESCRIPTION . "
              where products_id = '" . (int)$this->settings[0]['products_id'] . "' and language_id = '" . (int)$languages_id . "'"));

        $properties_tree = \common\helpers\Properties::get_properties_tree('0', '&nbsp;&nbsp;&nbsp;&nbsp;', '', false);

        return $this->render('properties-list.tpl', [
            'id' => $this->id,
            'params' => $this->params,
            'settings' => $this->settings,
            'properties_tree' => $properties_tree,
        ]);
    }
}