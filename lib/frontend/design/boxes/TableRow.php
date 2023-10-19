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

namespace frontend\design\boxes;

use common\models\ThemesSettings;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use frontend\design\IncludeTpl;
use frontend\design\ListingSql;
use frontend\design\SplitPageResults;
use frontend\design\Info;
use backend\design\Style;
use common\classes\design;

class TableRow extends Widget
{
    public $id;
    public $file;
    public $params;
    public $settings;
    public $name;

    public function init()
    {
        \common\helpers\Translation::init('admin/main');
        \common\helpers\Translation::init('admin/orders');
        \common\helpers\Translation::init('admin/design');
        parent::init();
    }

    public function run()
    {
        $cols = self::tableCols($this->name);

        return IncludeTpl::widget(['file' => 'boxes/table-row-edit.tpl', 'params' => [
            'settings' => $this->settings,
            'name' => $this->name,
            'cols' => $cols,
            'type' => $this->params['type'],
            'theme_name' => THEME_NAME,
        ]]);
    }

    public static function row($params, $pageName, $type = 'backendOrdersList')
    {
        $cols = self::tableCols($pageName);

        $response = [];

        for ($col = 1; $col <= $cols; $col++) {
            $response[] = \frontend\design\Block::widget(['name' => $pageName . '-' . $col, 'params' => ['type' => $type, 'params' => $params]]);
        }
        if ($params['DT_RowClass']) {
            $response['DT_RowClass'] = $params['DT_RowClass'];
        }

        return $response;
    }

    public static function headingRow($params, $pageName, $type = 'backendOrdersList')
    {
        $cols = self::tableCols($pageName);

        $response = '';

        for ($col = 1; $col <= $cols; $col++) {
            $response .= '<th>' . \frontend\design\Block::widget(['name' => $pageName . '_heading-' . $col, 'params' => ['type' => $type, 'params' => $params]]) . '</th>';
        }

        return $response;
    }

    public static function tableCols($pageName)
    {
        $themeSetting = ThemesSettings::findOne([
            'theme_name' => THEME_NAME,
            'setting_group' => 'tableRow',
            'setting_name' => $pageName,
        ]);
        $cols = 2;
        if ($themeSetting) {
            $cols = $themeSetting->setting_value;
        }
        return $cols;
    }
}
