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

namespace backend\controllers;

use Yii;
use yii\helpers\Html;
use common\helpers\Address;

class AddressFormatsController extends Sceleton {

    public $acl = ['BOX_HEADING_CONFIGURATION', 'BOX_ADDRESS_FORMATS'];

    public function __construct($id, $module = null) {
        \common\helpers\Translation::init('admin/address-formats');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('address-formats/index'), 'title' => BOX_ADDRESS_FORMATS);
        parent::__construct($id, $module);
    }

    public function actionIndex() {

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#frmMain\').trigger(\'submit\')">' . TEXT_APPLY . '</span>';

        $formats = Address::getFormats();

        if (Yii::$app->request->isPost) {
            $formats = Yii::$app->request->post('formats');
            if (is_array($formats)) {
                $message = TEXT_MESSEAGE_SUCCESS;
                $status = 'ok';
                $currenIds = [];
                $_titles = Yii::$app->request->post('formats_titles');
                foreach ($formats as $format_id => $format) {
                    if ($fM = Address::saveAddressFormat($format_id, $format)) {
                        $currenIds[] = $format_id;
                        $fM->address_format_title = $_titles[$format_id];
                        $fM->save();
                    }
                }

                if ($currenIds) {
                    $_toDelete = \common\models\AddressFormat::find()->where(['not in', 'address_format_id', $currenIds])->all();
                    if ($_toDelete) {
                        foreach ($_toDelete as $fid) {
                            if (!Address::checkBuisyAddressFormats([$fid->address_format_id])) {
                                $fid->delete();
                            } else {
                                $message = ERROR_FORMAT_IS_BUSY;
                                $status = 'bad';
                            }
                        }
                    }
                }
            }
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                Yii::$app->response->data = [
                    'status' => $status,
                    'message' => $message,
                ];
                return;
            } else {
                return $this->redirect(['address-formats/index']);
            }
        }

        return $this->render('index', [
                    'formats' => $formats
        ]);
    }

    public function actionNew() {
        $format = new \common\models\AddressFormat();
        $format->address_format_title = 'Untitled Format';
        $format->save();
        return $this->renderAjax('format', [
                    'format' => $format
        ]);
    }

}
