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

namespace frontend\design\boxes\CatalogPages;

use common\classes\Images;
use frontend\models\repositories\CatalogPagesReadRepository;
use frontend\models\repositories\InformationReadRepository;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class CategoryPagesList extends Widget
{

	public $file;
	public $params;
	public $content;
	public $settings;

	private $catalogPagesId = 0;
	private $catalogPagesRepository;
	private $informationRepository;

	public function __construct( CatalogPagesReadRepository $catalogPagesRepository, InformationReadRepository $informationRepository, $config = [])
	{
		parent::__construct($config);
		$this->catalogPagesRepository = $catalogPagesRepository;
		$this->informationRepository = $informationRepository;
	}

	public function init()
	{
		parent::init();
		$this->catalogPagesId = (int)$this->settings[0]['selectCatalogPage'];
	}

	public function run()
	{
		$languages_id = \Yii::$app->settings->get('languages_id');

		if($this->catalogPagesId < 1){
			return '';
		}

		$infoPages = $this->catalogPagesRepository->getFullInfoList($this->catalogPagesId,$languages_id);
		if(empty($infoPages)){
			return '';
		}
		$imagePageCatalogPath = Images::getWSCatalogImagesPath().$this->catalogPagesRepository->imagesLocation();
        $imageInformationPath = Images::getWSCatalogImagesPath().$this->informationRepository->imagesLocation();
		return IncludeTpl::widget(['file' => 'boxes/category-pages/category-pages-list.tpl', 'params' => [
			'infoPages' => $infoPages,
			'imagePageCatalogPath' => $imagePageCatalogPath,
            'imageInformationPath' => $imageInformationPath,
		]]);
	}
}