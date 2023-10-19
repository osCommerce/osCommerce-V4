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

use frontend\design\Info;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\ListingSql;
use frontend\design\SplitPageResults;

class PagingBar extends Widget
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
      \frontend\design\Info::addBoxToCss('pagination');
      
    if ( !isset($this->params['listing_split']) || !is_object($this->params['listing_split']) || !is_a($this->params['listing_split'], 'frontend\design\splitPageResults' ) ) {
      return '';
    }
    $listing_split = $this->params['listing_split'];
    /**
     * @var $listing_split SplitPageResults
     */
    if ($listing_split->number_of_rows > 0){
      $links = $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, \common\helpers\Output::get_all_get_params(array('page', 'info', 'x', 'y', 'ajax', 't', 'filter', 'split')), $this->params['this_filename']);

      $format_display_count = (defined('LISTING_PAGINATION')? LISTING_PAGINATION : Yii::t('app', 'Items %s to %s of %s total'));
      if ( isset($this->params['listing_display_count_format']) && strlen($this->params['listing_display_count_format'])>0 ) {
        $format_display_count = $this->params['listing_display_count_format'];
      }

      if (Info::widgetSettings('Listing', 'fbl', ($this->params['page_name'] ?? false))){
        return '';
      }
      
      return IncludeTpl::widget([
        'file' => 'boxes/catalog/paging-bar.tpl',
        'params' => [
          'links' => $links,
          'hidden_fields' => \common\helpers\Output::get_all_get_params(array('sort'), true),
          'counts' => $listing_split->display_count($format_display_count)
        ]
      ]);
    }



  }
}