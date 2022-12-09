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

namespace frontend\design\boxes\catalog;

use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use frontend\design\IncludeTpl;
use frontend\design\ListingSql;
use frontend\design\SplitPageResults;
use frontend\design\Info;

class ItemsOnPage extends Widget
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
    if ( !isset($this->params['listing_split']) || !is_object($this->params['listing_split']) || !is_a($this->params['listing_split'], 'frontend\design\splitPageResults' ) ) {
      return '';
    }
    $listing_split = $this->params['listing_split'];
    /**
     * @var $listing_split SplitPageResults
     */
    if ($listing_split->number_of_rows > 0 && !Info::widgetSettings('Listing', 'fbl', ArrayHelper::getValue($this->params, 'page_name'))){

      $searchResults = Info::widgetSettings('Listing', 'items_on_page', $this->params['page_name']);
      if (!$searchResults) $searchResults = SEARCH_RESULTS_1;

      $view = array();
      $view[] = $searchResults * 1;
      $view[] = $searchResults * 2;
      $view[] = $searchResults * 4;
      $view[] = $searchResults * 8;

      if ($_SESSION['max_items'] && !in_array($_SESSION['max_items'], $view)) {
          unset($_SESSION['max_items']);
      }

      Info::sortingId();
      return IncludeTpl::widget([
        'file' => 'boxes/catalog/items-on-page.tpl',
        'params' => [
            'box_id' => $this->id,
            'sorting_link' => tep_href_link($this->params['this_filename'], \common\helpers\Output::get_all_get_params(array('max_items'))),
          'view' => $view,
          'view_id' => (isset($_SESSION['max_items']) ? $_SESSION['max_items'] : 0),
          'hidden_fields' => '',//\common\helpers\Output::get_all_get_params(array('max_items'), true),
          'settings' => $this->settings
        ]
      ]);
    }



  }
}