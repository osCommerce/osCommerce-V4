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
use frontend\design\ListingSql;
use frontend\design\SplitPageResults;
use frontend\design\Info;

class ListingFunctionality extends Widget
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
    if ($listing_split->number_of_rows > 0){

      if (!isset($this->params['sorting_id'])) {
        $this->params['sorting_id'] = Info::sortingId();
      }


      $sorting_link = tep_href_link($this->params['this_filename'], \common\helpers\Output::get_all_get_params(array('sort')));

      $sorting = \common\helpers\Sorting::getSorting($this->settings[0], false, true);

      $searchResults = Info::widgetSettings('Listing', 'items_on_page', 'products');
      if (!$searchResults) $searchResults = SEARCH_RESULTS_1;

      $view = array();
      $view[] = $searchResults * 1;
      $view[] = $searchResults * 2;
      $view[] = $searchResults * 4;
      $view[] = $searchResults * 8;

      if (Info::widgetSettings('Listing', 'listing_type') != 'no') $grid_link = tep_href_link($this->params['this_filename'], \common\helpers\Output::get_all_get_params(array('gl')) . 'gl=grid');
      if (Info::widgetSettings('Listing', 'listing_type_rows') != 'no') $list_link = tep_href_link($this->params['this_filename'], \common\helpers\Output::get_all_get_params(array('gl')) . 'gl=list');
      if (Info::widgetSettings('Listing', 'listing_type_b2b')) $b2b_link = tep_href_link($this->params['this_filename'], \common\helpers\Output::get_all_get_params(array('gl')) . 'gl=b2b');
      Info::sortingId();
      return IncludeTpl::widget([
        'file' => 'boxes/catalog/listing-functionality.tpl',
        'params' => [
          'view' => $view,
          'view_id' => $_SESSION['max_items'],
          'sorting_link' => $sorting_link,
          'sorting' => $sorting,
          'sorting_id' => $this->params['sorting_id'],
          'hidden_fields' => \common\helpers\Output::get_all_get_params(array('sort','max_items'), true),
          'grid_link' => $grid_link,
          'list_link' => $list_link,
          'b2b_link' => $b2b_link,
          'gl' => $_SESSION['gl'],
          'fbl' => Info::widgetSettings('Listing', 'fbl', ($this->params['page_name'] ?? false)),
          'compare_button' => $this->settings[0]['compare_button']
        ]
      ]);
    }



  }
}