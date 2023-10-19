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

use common\models\BannersGroupsImages;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\BannersLanguages;
use common\models\BannersGroups;
use common\models\ImageTypes;

class ImageSettingsController extends Sceleton
{

    public $acl = ['TEXT_SETTINGS', 'BOX_IMAGE_SETTINGS'];
    public $banner_extension;
    public $dir_ok = false;

    public function __construct($id, $module = null)
    {
        parent::__construct($id, $module);

        \common\helpers\Translation::init('admin/main');
        \common\helpers\Translation::init('admin/banner_manager');

    }

    public function actionIndex()
    {
        $this->selectedMenu = array('marketing', 'banner_manager');

        $this->navigation[] = [
            'link' => Yii::$app->urlManager->createUrl('image-settings'),
            'title' => BOX_IMAGE_SETTINGS
        ];

        $this->view->headingTitle = BOX_IMAGE_SETTINGS;

        return $this->render('index.tpl', []);
    }

    public function actionList()
    {
        $draw = Yii::$app->request->get('draw', 1);
        $length = Yii::$app->request->get('length', 25);
        $search = Yii::$app->request->get('search');
        $start = Yii::$app->request->get('start', 0);
        $order = Yii::$app->request->get('order', 0);

        if ($length == -1) {
            $length = 10000;
        }
        if (!$search['value']) {
            $search['value'] = '';
        }

        $types = ImageTypes::find()
            ->select(['image_types_name', 'image_types_x', 'image_types_y', 'image_types_id'])
            ->where(['or',
                ['like', 'image_types_name', $search['value']],
                ['like', 'image_types_x', $search['value']],
                ['like', 'image_types_y', $search['value']]
            ])
            ->andWhere(['parent_id' => 0])
            ->limit($length)
            ->offset($start)
            ->orderBy(['image_types_id' => SORT_ASC])
            ->all();

        $responseList = [];
        foreach ($types as $type) {

            $responseList[] = [
                '<div class="type" data-type-id="'. $type->image_types_id . '">'. $type->image_types_name . '</div>',
                $type->image_types_x,
                $type->image_types_y,
            ];
        }

        $countTypes = ImageTypes::find()
            ->select('image_types_name')
            ->count();

        $response = array(
            'draw'            => $draw,
            'recordsTotal'    => $countTypes,
            'recordsFiltered' => $countTypes,
            'data'            => $responseList
        );
        echo json_encode( $response );
    }

    public function actionEdit()
    {
        $typeId = Yii::$app->request->get('image_types_id', '');

        $type = ImageTypes::find()
            ->where(['image_types_id' => $typeId])
            ->asArray()
            ->one();

        $this->selectedMenu = array('marketing', 'banner_manager');
        $this->topButtons[] = '<span class="btn btn-confirm save-type">' . IMAGE_SAVE . '</span>';
        $this->navigation[] = [
            'link' => Yii::$app->urlManager->createUrl('image-settings'),
            'title' => 'Image type: ' . $type['image_types_name']
        ];
        $this->view->headingTitle = 'Image type: ' . $type['image_types_name'];

        $typeSizes = [];
        if ($typeId) {
            $typeSizes = ImageTypes::find()
                ->where(['or',
                    ['image_types_id' => $typeId],
                    ['parent_id' => $typeId]
                ])
                ->asArray()
                ->all();
        }

        if (Yii::$app->request->isAjax) {
            $this->layout = false;
        }

        return $this->render('edit.tpl', [
            'typeId' => $typeId,
            'image_types_name' => $type['image_types_name'],
            'typeSizes' => $typeSizes
        ]);
    }

    public function actionSave()
    {
        $post = Yii::$app->request->post();

        $ids = [];
        if ($post['parent_id']) {
            $imageSizes = ImageTypes::find()
                ->select('image_types_id')
                ->where(['parent_id' => $post['parent_id']])
                ->asArray()
                ->all();
            foreach ($imageSizes as $item) {
                $ids[$item['image_types_id']] = $item['image_types_id'];
            }
        }

        $count = 0;
        foreach ($post['image_types_id'] as $id) {

            $imageTypes = false;
            if ($id != -1) {
                $imageTypes = ImageTypes::findOne($id);
            }
            if (!$imageTypes) {
                $imageTypes = new ImageTypes();
            }
            $imageTypes->attributes = [
                'image_types_id' => $id,
                'image_types_name' => $post['image_types_name'],
                'image_types_x' => (int)$post['image_types_x'][$count],
                'image_types_y' => (int)$post['image_types_y'][$count],
                'width_from' => (int)$post['width_from'][$count],
                'width_to' => (int)$post['width_to'][$count],
                'parent_id' => (int)($id != $post['parent_id'] ? $post['parent_id'] : 0),
            ];
            $imageTypes->save();

            if ($ids[$id] ?? false) {
                unset($ids[$id]);
            }

            $count++;
        }

        if (is_array($ids) && count($ids) > 0) {
            foreach ($ids as $id) {
                if ($id != 0) {
                    $imageTypes = ImageTypes::findOne($id);
                    $imageTypes->delete();
                }
            }
        }

        return MESSAGE_SAVED;
    }

    public function actionBar()
    {
        $typesId = Yii::$app->request->get('image_types_id', 0);

        if (!$typesId) {
            return '';
        }

        $type = ImageTypes::find()
            ->where(['image_types_id' => $typesId])
            ->asArray()
            ->one();

        $this->layout = false;
        return $this->render('bar.tpl', [
            'data' => $type,
        ]);
    }

}
