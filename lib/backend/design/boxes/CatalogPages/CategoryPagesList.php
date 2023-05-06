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
use Yii;
use yii\base\Widget;

class CategoryPagesList extends Widget
{

    public $id;
    public $params;
    public $settings;
    public $visibility;
	public $catalogPagesService;

	public function __construct( CatalogPagesService $catalogPagesService, $config = [])
	{
		parent::__construct($config);
		$this->catalogPagesService = $catalogPagesService;
	}

    public function init()
    {
        parent::init();
    }

    public function run()
    {
	    global $languages_id;
	    $catalogPages = $this->catalogPagesService->getAllNamesDropDown($languages_id,1,false);

	    if (!isset($this->settings[0]['selectCatalogPage'])) {
		    $this->settings[0]['selectCatalogPage'] = key($catalogPages);
	    }

        return $this->render('../../views/category-page-list.tpl', [
            'id' => $this->id,
            'params' => $this->params,
            'settings' => $this->settings,
            'visibility' => $this->visibility,
            'catalogPages' => $catalogPages,
        ]);
    }
}