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

namespace frontend\design\boxes\email;

use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use frontend\design\IncludeTpl;

class OrderTotals extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $theme_name = false;

        if (defined("THEME_NAME")) {
            $theme_name = THEME_NAME;
        } elseif ($this->params['platform_id']) {
            $theme = tep_db_fetch_array(tep_db_query("select t.theme_name from platforms_to_themes p2t, themes t where p2t.theme_id = t.id  and p2t.platform_id = '" . $this->params['platform_id'] . "'"));
            $theme_name = $theme['theme_name'];
        } else {
            $theme_name = '';
        }

        $attributesText = [];

        if ($theme_name) {
            $stylesByClasses = \backend\design\Style::getStylesByClasses($theme_name, '.b-email');
            $attributesText = $stylesByClasses['attributesText'];
        }

        $html = '<table class="totals-table"' . (isset($attributesText['.totals-table']) ? 'style="' . $attributesText['.totals-table'] . '"' : '') . ' cellpadding="0" cellspacing="0" border="0" width="100%">';
        foreach ($this->params['order_total_output'] as $item) {
            $html .= '
          <tr class="totals-row ' . $item['code'] . (ArrayHelper::getValue($item, 'show_line') ? ' totals-line' : '') . '" style="' .
                ($attributesText['.totals-row'] ?? '') .
                ($attributesText['.'.$item['code']] ?? '') .
                (ArrayHelper::getValue($item, 'show_line') ? ($attributesText['.totals-line'] ?? '') : ''). '">
            <td class="totals-title" style="' . ($attributesText['.totals-title'] ?? '') . ($attributesText['.' . $item['code'] . ' .totals-title'] ?? '') . '">' . strip_tags($item['title']) . '</td>

            <td class="totals-value" style="' . ($attributesText['.totals-value'] ?? '') . ($attributesText['.' . $item['code'] . ' .totals-value'] ?? '') . '">' . strip_tags((defined('GROUPS_IS_SHOW_PRICE') && GROUPS_IS_SHOW_PRICE==false) ? '':$item['text']) . '</td>
        </tr>';
        }
        $html .= '</table>';

        return $html;
    }
}