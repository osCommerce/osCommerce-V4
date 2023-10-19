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
use frontend\design\IncludeTpl;
use frontend\design\ListingSql;
use frontend\design\SplitPageResults;
use frontend\design\Info;

class ListingLook extends Widget
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

        $this->params['listing_type'] = (isset($this->params['listing_type']) ? $this->params['listing_type'] : '');
        $this->params['listing_type_rows'] = (isset($this->params['listing_type_rows']) ? $this->params['listing_type_rows'] : '');
      if (Info::widgetSettings('Listing', 'listing_type') != 'no' && $this->params['listing_type'] != 'no') $grid_link = tep_href_link($this->params['this_filename'], \common\helpers\Output::get_all_get_params(array('gl')) . 'gl=grid');
      if (Info::widgetSettings('Listing', 'listing_type_rows') != 'no' && $this->params['listing_type_rows'] != 'no') $list_link = tep_href_link($this->params['this_filename'], \common\helpers\Output::get_all_get_params(array('gl')) . 'gl=list');
      if (Info::widgetSettings('Listing', 'listing_type_b2b')) $b2b_link = tep_href_link($this->params['this_filename'], \common\helpers\Output::get_all_get_params(array('gl')) . 'gl=b2b');
      if(isset($this->params['gl'])){
          $_SESSION['gl'] = $this->params['gl'];
      }
      return IncludeTpl::widget([
        'file' => 'boxes/catalog/listing-look.tpl',
        'params' => [
          'box_id' => $this->id,
          'grid_link' => $grid_link,
          'list_link' => $list_link,
          'b2b_link' => $b2b_link,
          'gl' => $_SESSION['gl'],
          'fbl' => Info::widgetSettings('Listing', 'fbl', ($this->params['page_name'] ?? false)),
          'settings' => $this->settings,
        ]
      ]);
    }



  }
}