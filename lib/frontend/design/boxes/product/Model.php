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
use yii\helpers\ArrayHelper;
use frontend\design\IncludeTpl;

class Model extends Widget
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
        $params = Yii::$app->request->get();

        if (!$params['products_id']) {
            return '';
        }

        $products = Yii::$container->get('products');
        $data = $products->getProduct($params['products_id']);

        if ($data['model'] && ArrayHelper::getValue($this->settings, [0,'show_model']) != 'no') {
            \frontend\design\JsonLd::addData(['Product' => [
                'sku' => $data['model']
            ]], ['Product', 'sku']);
        }
        if ($data['ean'] && ArrayHelper::getValue($this->settings, [0,'show_ean']) != 'no') {
            \frontend\design\JsonLd::addData(['Product' => [
                'gtin13' => $data['ean']
            ]], ['Product', 'gtin13']);
        }
        if ($data['isbn'] && ArrayHelper::getValue($this->settings, [0,'show_isbn']) != 'no') {
            \frontend\design\JsonLd::addData(['Product' => [
                'isbn' => $data['isbn']
            ]], ['Product', 'isbn']);
        }
        if ($data['upc'] && ArrayHelper::getValue($this->settings, [0,'show_upc']) != 'no') {
            \frontend\design\JsonLd::addData(['Product' => [
                'upc' => $data['upc']
            ]], ['Product', 'upc']);
        }

        return IncludeTpl::widget(['file' => 'boxes/product/model.tpl', 'params' => [
            'data' => $data,
            'settings' => $this->settings[0]
        ]]);

    }
}