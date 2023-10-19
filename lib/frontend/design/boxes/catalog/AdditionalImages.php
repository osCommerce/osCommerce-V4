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
use frontend\design\Info;

class AdditionalImages extends Widget
{

    public $file;
    public $params;
    public $content;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        global $current_category_id;
        $platformId = \common\classes\platform::currentId();
        Info::addBoxToCss('slick');
        Info::addBoxToCss('fancybox');
		if($current_category_id > 0){
			$allImages = \common\helpers\Image::getCategoriesAdditionalImages($current_category_id);
	
			if (is_array($allImages[$platformId]) && count($allImages[$platformId])) {
				$images = $allImages[$platformId];
			} else {
				$images = $allImages[0];
			}
	
			if (!is_array($images) || !count($images)) {
				return '';
			}
	
			foreach ($images as $key => $image) {
				$images[$key]['img'] = \common\classes\Images::getImageSet(
					$image['image'],
					'Category gallery add',
					[
						'alt' => '',
						'title' => '',
					],
					Info::themeSetting('na_category', 'hide')
				);
			}
	
			Info::addJsData(['widgets' => [
				$this->id => [
					'colInRowCarousel' => $this->settings['colInRowCarousel'],
					'fancybox' => $this->settings[0]['fancibox'],
					'carousel' => $this->settings[0]['carousel'],
					'col_in_row' => $this->settings[0]['col_in_row'],
					'autoplay' => $this->settings[0]['autoplay'],
					'autoplaySpeed' => $this->settings[0]['autoplaySpeed'],
					'speed' => $this->settings[0]['speed'],
				]]]);
	
			return IncludeTpl::widget(['file' => 'boxes/catalog/additional-images.tpl', 'params' => [
				'images' => $images
			]]);
		}
    }
}