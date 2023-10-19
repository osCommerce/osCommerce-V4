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

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Search extends Widget
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

        $link = tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '');

        return IncludeTpl::widget(['file' => 'boxes/search.tpl', 'params' => [
            'id' => $this->id,
            'link' => $link,
            'searchSuggest' => Yii::$app->urlManager->createUrl('catalog/search-suggest'),
            'keywords' => \common\helpers\Output::output_string(isset($_GET['keywords'])?tep_db_prepare_input($_GET['keywords']):''),
            'extra_form_fields' => (SEARCH_IN_DESCRIPTION == 'true'?'<input type="hidden" name="search_in_description" value="1">':''),
            'searchHistory' => $this->settings[0]['search_history'],
            'historyItems' => $this->settings[0]['history_items'] ?? 5,
        ]]);
    }
}