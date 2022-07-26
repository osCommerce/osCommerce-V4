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
use common\classes\platform;
use frontend\models\repositories\CatalogPagesReadRepository;
use frontend\models\repositories\InformationReadRepository;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class CategoryPagesLastListBlock extends Widget
{

	public $file;
	public $params;
	public $content;
	public $settings;

	private $limit = 0;
	private $informationRepository;
	private $platformId;

	public function __construct(InformationReadRepository $informationRepository, $config = [])
	{
		parent::__construct($config);
		$this->informationRepository = $informationRepository;
	}

	public function init()
	{
		parent::init();
		$this->limit = (int)$this->settings[0]['limitInformationLastListBlock'];
		if($this->limit < 1){
            $this->limit = 6;
        }
        $this->platformId = (bool)platform::currentId()?(int)platform::currentId():(int)platform::currentId();
	}

	public function run()
	{
		$languages_id = \Yii::$app->settings->get('languages_id');
		$infoPages = $this->informationRepository->getLastList($languages_id,$this->platformId,$this->limit,true,false,true);
		if(empty($infoPages)){
			return '';
		}

        $imageInformationPath = Images::getWSCatalogImagesPath().$this->informationRepository->imagesLocation();
		return IncludeTpl::widget(['file' => 'boxes/category-pages/category-pages-last-list-block.tpl', 'params' => [
			'infoPages' => $infoPages,
            'imageInformationPath' => $imageInformationPath,
		]]);
	}
}
