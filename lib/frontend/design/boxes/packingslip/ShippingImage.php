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

namespace frontend\design\boxes\packingslip;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class ShippingImage extends Widget
{

  public $id;
  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run() {
      if (isset($this->params['order'])) {
        $order = $this->params['order'];
        $orders_id = (int)$order->info['orders_id'];
        if (class_exists('\Imagick') && $orders_id > 0) {
            $oLabel = \common\models\OrdersLabel::find()
                ->where([/*'orders_label_id' => $orders_label_id,*/ 'orders_id' => $orders_id])
                ->andWhere(['label_status'=>\common\models\OrdersLabel::LABEL_STATUS_DONE])
                ->orderBy(['orders_label_id'=>SORT_DESC])
                ->one();
            if (is_object($oLabel) && !empty($oLabel->parcel_label_pdf)) {
                $parcel_label = base64_decode($oLabel->parcel_label_pdf);
                try {
                    $image = new \Imagick();
                    $image->readImageBlob($parcel_label);
                    $image->setImageFormat('png');
                    //TODO: need setting in widget
                    $image->rotateImage(new \ImagickPixel(), -90);
                    if (false) {
                        $fspath = \common\classes\Images::getFSCatalogImagesPath();
                        $pathToImage = $fspath . '/tmp/label_' . $orders_id . '.jpg';
                        file_put_contents($pathToImage, $image);
                        return '<img width="283" height="200" src="' . tep_catalog_href_link('images/') . 'tmp/label_' . $orders_id . '.jpg">';
                    }else {
                        //return '<img src="data:image/png;base64,' . base64_encode($image->getImageBlob()) . '">';
                        return '<img width="283" height="200" src="@' . base64_encode($image->getImageBlob()) . '">';
                    }
                    die();
                }catch (\Exception $ex){ }
            }
        }
      }
      return '';
      //return '<img src="data:image/png;base64,'.base64_encode('').'">';    
  }
}