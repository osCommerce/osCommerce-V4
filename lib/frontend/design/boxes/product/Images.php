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

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\classes\Images as cImages;
use common\helpers\Product;
use frontend\design\Info;

class Images extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        Info::includeJsFile('boxes/product/mediaItem');
        Info::includeJsFile('reducers/products');
        Info::includeJsFile('reducers/widgets');

        if (!is_array($this->params) ) $this->params = array();
        parent::init();
    }

    public function run()
    {
        \frontend\design\Info::includeJsFile('boxes/product/ImagesAdditional');

        global $request_type;
        $languageId = (int)\Yii::$app->settings->get('languages_id');

        if ( isset($this->params['uprid']) && $this->params['uprid']>0 ) {
            $show_uprid = $this->params['uprid'];
        }else {
            $show_uprid = Yii::$app->request->get('products_id',0);
        }

        if (!$show_uprid) {
            return '';
        }
        $this->settings[0]['align_position'] = (isset($this->settings[0]['align_position']) ? $this->settings[0]['align_position'] : 0);
        $this->settings[0]['hide_additional'] = (isset($this->settings[0]['hide_additional']) ? $this->settings[0]['hide_additional'] : 0);
        \frontend\design\Info::addJsData(['widgets' => [
            $this->id => [
                'alignPosition' => $this->settings[0]['align_position'],
            ]]
        ]);

        $product = Yii::$container->get('products')->getProduct($show_uprid);

        $products_name = $product['products_name'];
        if (!$products_name){
            $products_name = Product::get_products_name($show_uprid);
        }
        $imageId = cImages::getImageId($show_uprid);
        $imageDescription = cImages::getImageTags($show_uprid, $imageId, $languageId);

        $main_image = cImages::getImageUrl($show_uprid, 'Medium', -1, 0, false);
        $srcsetSizes = cImages::getImageSrcsetSizes($show_uprid, 'Medium');

        if (substr($main_image, -7) != '/na.png') {
            $main_image_info = cImages::getImageUrl($show_uprid, 'Medium', -1, 0, false, false);
            if (stripos($main_image, 'http') === 0){
                $main_image_url = $main_image_info;
            } else {
                $main_image_url = (($request_type == 'SSL') ? HTTPS_SERVER  : HTTP_SERVER) . $main_image_info;
            }
            Yii::$app->getView()->registerMetaTag([
                'property' => 'og:image',
                'content' => $main_image_url
            ],'og:image');

            \frontend\design\JsonLd::addData(['Product' => [
                'image' => $main_image_url
            ]], ['Product', 'image']);
        }

        return IncludeTpl::widget(['file' => 'boxes/product/images.tpl', 'params' => [
            'params' => $this->params,
            'img' => $main_image,
            'srcset' => $srcsetSizes['srcset'],
            'sizes' => $srcsetSizes['sizes'],
            'main_image_alt' => $imageDescription['alt_tag'] ? $imageDescription['alt_tag'] : $products_name,
            'main_image_title' => $imageDescription['title_tag'] ? $imageDescription['title_tag'] : $products_name,
            'settings' => $this->settings,
            'product' => $product,
        ]]);
    }
}