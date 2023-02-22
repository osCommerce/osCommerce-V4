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

namespace frontend\design\boxes\sitemap;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Categories extends Widget {
    
    public $file;
    public $params;
    public $settings;
    public $isAjax;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $osC_CategoryTree = new \common\classes\osC_CategoryTree;
        if (!empty($this->settings[0]['show_products'])) {
            $osC_CategoryTree->withProducts(true);
        }
        $description = trim($osC_CategoryTree->buildTree());
        if (substr($description,-5)=='</ul>'){
            $description = substr($description,0,-5). '<li><a href="' . Yii::$app->urlManager->createUrl(FILENAME_ADVANCED_SEARCH_RESULT) . '">' . TEXT_ALL_PRODUCTS . '</a></li>' .'</ul>';
        }

        return $description;
    }
    
}