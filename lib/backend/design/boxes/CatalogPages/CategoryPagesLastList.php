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

namespace backend\design\boxes\CatalogPages;

use backend\services\CatalogPagesService;
use common\classes\platform;
use yii\base\Widget;

class CategoryPagesLastList extends Widget
{

    public $id;
    public $params;
    public $settings;
    public $visibility;
	public $catalogPagesService;
    private $platformId;

    public function __construct( CatalogPagesService $catalogPagesService, $config = [])
	{
		parent::__construct($config);
		$this->catalogPagesService = $catalogPagesService;
	}

    public function init()
    {
        parent::init();
        $this->platformId = (bool)platform::currentId()?(int)platform::currentId():(int)platform::currentId();
    }

    public function run()
    {
	    global $languages_id;
	    $catalogPages = $this->catalogPagesService->getAllNamesDropDown($languages_id,$this->platformId,false);

        if (!isset($this->settings[0]['limitInformationLastList'])) {
            $this->settings[0]['limitInformationLastList'] = 6;
        }

        return $this->render('../../views/category-page-last-list.tpl', [
            'id' => $this->id,
            'params' => $this->params,
            'settings' => $this->settings,
            'visibility' => $this->visibility,
            'catalogPages' => $catalogPages,
        ]);
    }
}
