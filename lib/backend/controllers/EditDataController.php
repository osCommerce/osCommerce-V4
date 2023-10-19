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

class EditDataController extends Sceleton {

    public $acl = [];

    public function actionInfo() {

        \common\helpers\Acl::checkAccess(\frontend\design\EditData::getAccessRule('info'));

        $splitTags = ['information_h2_tag', 'information_h3_tag'];

        $fieldName = Yii::$app->request->get('field', false);
        $pageId = Yii::$app->request->get('id', false);
        $platformId = Yii::$app->request->get('platform_id', false);
        $languageId = Yii::$app->request->get('language_id', false);
        $split = Yii::$app->request->get('split', false);

        if (!$fieldName || !$pageId) {
            return '';
        }

        $languages = \common\helpers\Language::get_languages();
        $platforms = \common\classes\platform::getList(false);

        if (Yii::$app->request->isPost) {
            $fields = Yii::$app->request->post('field');
            foreach( $platforms as $platform ) {
                foreach ($languages as $i => $language) {
                    if (!$fields[$platform['id']][$language['id']]) continue;
                    $page = \common\models\Information::findOne([
                        'platform_id' => $platform['id'],
                        'languages_id' => $language['id'],
                        'information_id' => $pageId
                    ]);
                    if (in_array($fieldName, $splitTags)) {

                        $arr = explode("\n", $page->attributes[$fieldName]);
                        $arr[$split] = $fields[$platform['id']][$language['id']];
                        $value = implode("\n", $arr);

                    } else {
                        $value = $fields[$platform['id']][$language['id']];
                    }
                    $page->attributes = [
                        $fieldName => $value
                    ];
                    $page->save(false);

                }
            }
        }


        $fields = [];
        foreach( $platforms as $platform ) {
            foreach ($languages as $i => $language) {
                $data = \backend\components\Information::read_data($pageId, $language['id'], $platform['id']);
                if (in_array($fieldName, $splitTags)) {

                    $arr = explode("\n", $data[$fieldName]);
                    $value = $arr[$split];

                } else {
                    $value = $data[$fieldName] ?? '';
                }
                $fields[$platform['id']][$language['id']] = $value;
            }
        }


        if (!is_array($fields)) return false;

        $ckEditor = false;
        if ($fieldName == 'description') {
            $ckEditor = true;
        }

        $this->layout = 'iframe.tpl';
        return $this->render('index.tpl', [
            'action' => Yii::$app->urlManager->createUrl(['edit-data/info', 'field' => $fieldName, 'id' => $pageId]),
            'fieldName' => $fieldName,
            'pageId' => $pageId,
            'fields' => $fields,
            'platforms' => $platforms,
            'languages' => $languages,
            'platformId' => $platformId,
            'languageId' => $languageId,
            'ckEditor' => $ckEditor,
        ]);
    }

    public function actionSeo()
    {
        \common\helpers\Acl::checkAccess(\frontend\design\EditData::getAccessRule('seo'));

        $splitTags = ['HEAD_H2_TAG_DEFAULT', 'HEAD_H3_TAG_DEFAULT'];

        $fieldName = Yii::$app->request->get('field', false);
        $pageId = Yii::$app->request->get('id', false);
        $split = Yii::$app->request->get('split', false);
        $platformId = Yii::$app->request->get('platform_id', false);
        $languageId = Yii::$app->request->get('language_id', false);

        if (!$fieldName) {
            return '';
        }

        $languages = \common\helpers\Language::get_languages();
        $platforms = \common\classes\platform::getList(false);

        if (Yii::$app->request->isPost) {
            $fields = Yii::$app->request->post('field');
            foreach( $platforms as $platform ) {
                foreach ($languages as $i => $language) {
                    if (!$fields[$platform['id']][$language['id']]) continue;
                    $data = \common\models\MetaTags::findOne([
                        'meta_tags_key' => $fieldName,
                        'platform_id' => $platform['id'],
                        'language_id' => $language['id']
                    ]);
                    if (in_array($fieldName, $splitTags)) {

                        $arr = explode("\n", $data->meta_tags_value);
                        $arr[$split] = $fields[$platform['id']][$language['id']];
                        $value = implode("\n", $arr);

                    } else {
                        $value = $fields[$platform['id']][$language['id']];
                    }
                    $data->meta_tags_value = $value;
                    $data->save(false);

                }
            }
        }


        $fields = [];
        foreach( $platforms as $platform ) {
            foreach ($languages as $i => $language) {
                $data = \common\models\MetaTags::findOne([
                    'meta_tags_key' => $fieldName,
                    'platform_id' => $platform['id'],
                    'language_id' => $language['id']
                ]);
                if (in_array($fieldName, $splitTags)) {

                    $arr = explode("\n", $data['meta_tags_value']);
                    $value = $arr[$split];

                } else {
                    $value = $data['meta_tags_value'];
                }
                $fields[$platform['id']][$language['id']] = $value;
            }
        }

        if (!is_array($fields)) return false;

        $this->layout = 'iframe.tpl';
        return $this->render('index.tpl', [
            'action' => Yii::$app->urlManager->createUrl(['edit-data/seo', 'field' => $fieldName, 'id' => $pageId, 'split' => $split]),
            'fieldName' => $fieldName,
            'pageId' => $pageId,
            'fields' => $fields,
            'platforms' => $platforms,
            'languages' => $languages,
            'platformId' => $platformId,
            'languageId' => $languageId,
            'ckEditor' => false,
            'input' => true,
        ]);
    }

    public function actionMenu()
    {
        \common\helpers\Acl::checkAccess(\frontend\design\EditData::getAccessRule('menu'));

        $fieldName = Yii::$app->request->get('field', false);
        $pageId = Yii::$app->request->get('id', false);
        $languageId = Yii::$app->request->get('language_id', false);
        $platformId = Yii::$app->request->get('platform_id', false);
        $translationKey = Yii::$app->request->get('key', false);
        $translationEntity = Yii::$app->request->get('entity', false);
        $isGuest = Yii::$app->request->get('is_guest', false);

        $languages = \common\helpers\Language::get_languages();

        $fields = [];
        $menuItems = [];
        $menuItem = \common\models\MenuItems::find()
            ->where(['id' => $pageId,])
            ->select(['link_type', 'link_id'])
            ->asArray()
            ->one();
        $linkType = $menuItem['link_type'];
        $linkId = $menuItem['link_id'];
        $linkTypeText = '';


        if (Yii::$app->request->isPost) {
            $postFields = Yii::$app->request->post('field');
            $postMenuItems = Yii::$app->request->post('menu_item');
            foreach ($languages as $i => $language) {

                $menuTitle = \common\models\MenuTitles::findOne(['item_id' => $pageId, 'language_id' => $language['id']]);
                if ($postMenuItems[$language['id']]) {
                    if (!$menuTitle) {
                        $menuTitle = new \common\models\MenuTitles();
                        $menuTitle->language_id = $language['id'];
                        $menuTitle->item_id = $pageId;
                    }
                    $menuTitle->title = $postMenuItems[$language['id']];
                    $menuTitle->save(false);
                } elseif ($menuTitle) {
                    $menuTitle->delete();
                }

                switch ($linkType) {
                    case 'info':
                        $page = \common\models\Information::findOne([
                            'platform_id' => $platformId,
                            'languages_id' => $language['id'],
                            'information_id' => $linkId
                        ]);
                        if ($page) {
                            if ($page->info_title || !$page->page_title) {
                                $page->info_title = $postFields[$language['id']];
                            } else {
                                $page->page_title = $postFields[$language['id']];
                            }
                            $page->save(false);
                        }
                        break;
                    case 'categories':
                        $category = \common\models\CategoriesDescription::findOne([
                            'categories_id' => $linkId,
                            'language_id' => $language['id'],
                        ]);
                        $category->categories_name = $postFields[$language['id']];
                        $category->save(false);
                        break;
                    case 'all-products':
                    case 'default':
                        $data = \common\models\Translation::findOne([
                            'language_id' => $language['id'],
                            'translation_key' => $translationKey,
                            'translation_entity' => $translationEntity,
                        ]);
                        $data->translation_value = $postFields[$language['id']];
                        $data->save(false);
                        \common\helpers\Translation::resetCache();
                        break;
                }
            }

            if ($linkType == 'brands') {
                $postBrandField = Yii::$app->request->post('brand_field', false);
                $data = \common\models\Manufacturers::findOne($linkId);
                $data->manufacturers_name = $postBrandField;
                $data->save();
            }
        }

        $hideField = true;
        $brandField = false;

        foreach ($languages as $i => $language) {

            $menuTitle = \common\models\MenuTitles::find()
                ->where(['item_id' => $pageId, 'language_id' => $language['id']])
                ->select(['title'])
                ->asArray()
                ->one();


            $menuItems[$language['id']] = $menuTitle['title'] ?? '';

            switch ($linkType) {
                case 'info':
                    $data = \backend\components\Information::read_data($linkId, $language['id'], $platformId);
                    if ($data['info_title']) {
                        $fields[$language['id']] = $data['info_title'];
                    } elseif ($data['page_title']) {
                        $fields[$language['id']] = $data['page_title'];
                    }
                    $linkTypeText = 'Information page name';
                    $hideField = false;
                    break;
                case 'categories':
                    $data = \common\models\CategoriesDescription::findOne([
                        'categories_id' => $linkId,
                        'language_id' => $language['id'],
                    ]);
                    $fields[$language['id']] = $data->categories_name;
                    $linkTypeText = 'Category name';
                    $hideField = false;
                    break;
                case 'default':
                    $linkTypeText = 'Edit translation, key: ' . $translationKey . '; entity: ' . $translationEntity;
                    $data = \common\models\Translation::findOne([
                        'language_id' => $language['id'],
                        'translation_key' => $translationKey,
                        'translation_entity' => $translationEntity,
                    ]);
                    $fields[$language['id']] = $data->translation_value;
                    $hideField = false;
                    break;
            }
        }

        if ($linkType == 'brands') {
            $data = \common\models\Manufacturers::findOne($linkId);
            $brandField = $data->manufacturers_name;
            $linkTypeText = 'Brand name';
        }


        $actionParams = [
            'edit-data/menu',
            'field' => $fieldName,
            'id' => $pageId,
            'language_id' => $languageId,
            'platform_id' => $platformId,
        ];
        if ($translationKey) {
            $actionParams['key'] = $translationKey;
        }
        if ($translationEntity) {
            $actionParams['entity'] = $translationEntity;
        }

        $this->layout = 'iframe.tpl';
        return $this->render('menu.tpl', [
            'action' => Yii::$app->urlManager->createUrl($actionParams),
            'fieldName' => $fieldName,
            'pageId' => $pageId,
            'fields' => $fields,
            'menuItems' => $menuItems,
            'languages' => $languages,
            'languageId' => $languageId,
            'linkTypeText' => $linkTypeText,
            'hideField' => $hideField,
            'brandField' => $brandField,
        ]);
    }

}
