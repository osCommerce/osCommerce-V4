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

namespace frontend\design\boxes\pdf;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class CategoryImage extends Widget
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

        $categoryImage = '';
        if (is_file(DIR_FS_CATALOG . 'images/' . $this->params['categoryImage'])) {
            if (function_exists('tep_catalog_href_link')) {
                $categoryImage = '<img src="' . tep_catalog_href_link('images/' . $this->params['categoryImage']) . '" border="0">';
            } else {
                $categoryImage = '<img src="' . tep_href_link('images/' . $this->params['categoryImage']) . '" border="0">';
            }
        }
        return $categoryImage;
    }
}