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

namespace common\helpers;

use common\models\MenuTitles;
use Yii;
use common\models\Menus;
use common\models\MenuItems;


class Banner {

    public static function groupData($group)
    {
        $bannersData = [];
        $banners = \common\models\Banners::find()->where(['banners_group' => $group])->asArray()->all();

        foreach ($banners as $banner) {
            $languages = [];
            $BannerLanguages = \common\models\BannersLanguages::find()
                ->where(['banners_id' => $banner['banners_id']])
                ->asArray()->all();
            foreach ($BannerLanguages as $language) {

                $groupsImages = [];
                $bannersGroupsImages = \common\models\BannersGroupsImages::find()->where([
                    'banners_id' => $banner['banners_id'],
                    'language_id' => $language['language_id']
                ])->asArray()->all();
                foreach ($bannersGroupsImages as $groupsImage) {
                    $groupsImages[] = [
                        'image_width' => $groupsImage['image_width'],
                        'image' => $groupsImage['image'],
                    ];
                }

                $languageKey = \common\helpers\Language::get_language_code($language['language_id']);
                $languages[$languageKey['code']] = [
                    'banners_title' => $language['banners_title'],
                    'banners_url' => $language['banners_url'],
                    'banners_image' => $language['banners_image'],
                    'banners_html_text' => $language['banners_html_text'],
                    'target' => $language['target'],
                    'banner_display' => $language['banner_display'],
                    'text_position' => $language['text_position'],
                    'svg' => $language['svg'],
                    'groupsImages' => $groupsImages,
                ];
            }

            $bannersData[] = [
                'expires_impressions' => $banner['expires_impressions'],
                'expires_date' => $banner['expires_date'],
                'date_scheduled' => $banner['date_scheduled'],
                'status' => $banner['status'],
                'sort_order' => $banner['sort_order'],
                'banner_type' => $banner['banner_type'],
                'languages' => $languages,
            ];
        }

        return $bannersData;
    }

    public static function groupSettings($group)
    {
        $groupData = [];
        $groupSettings = \common\models\BannersGroups::find()->where(['banners_group' => $group])->asArray()->all();
        foreach ($groupSettings as $groupSetting) {
            $groupData[$groupSetting['image_width']] = [
                'width_from' => $groupSetting['width_from'],
                'width_to' => $groupSetting['width_to'],
            ];
        }

        return $groupData;
    }

    public static function groupImages($groupData, $images = [], $imagesToKeys = false)
    {
        foreach ($groupData as $bannerKey => $banner) {
            foreach ($banner['languages'] as $languageKey => $language) {

                $mainImg = $groupData[$bannerKey]['languages'][$languageKey]['banners_image'];
                $mainImgKey = self::imageKey($mainImg, $images);
                $images[$mainImgKey] = $mainImg;
                if ($imagesToKeys) {
                    $groupData[$bannerKey]['languages'][$languageKey]['banners_image'] = $mainImgKey;
                }

                foreach ($groupData[$bannerKey]['languages'][$languageKey]['groupsImages'] as $groupKey => $group) {

                    $groupImg = $group['image'];
                    $groupImgKey = self::imageKey($groupImg, $images);
                    $images[$groupImgKey] = $groupImg;
                    if ($imagesToKeys) {
                        $groupData[$bannerKey]['languages'][$languageKey]['groupsImages'][$groupKey]['image'] = $groupImgKey;
                    }

                }
            }
        }


        return [$images, $groupData];
    }

    private static function imageKey($imagePath, $images)
    {
        $imgKey = array_pop( explode('/', $imagePath) );
        if ($images[$imgKey] && $imagePath != $images[$imgKey]) {
            $imgKeyArr = explode('.', $imgKey);
            $fileName = $imgKeyArr[0];
            $ext = $imgKeyArr[1];

            $count = 1;
            $imgKey = $fileName . '-' . $count . '.' . $ext;
            while ($images[$imgKey] && $imagePath != $images[$imgKey]) {
                $count++;
                $imgKey = $fileName . '-' . $count . '.' . $ext;
            }
        }
        $images[$imgKey] = $imagePath;
        return $imgKey;
    }

    public static function setupBanners($bannerData, $imagePath, $platformIds)
    {
        $bannersIds = [];
        foreach ($bannerData as $groupName => $groupData) {
            if ($groupName == 'groupSettings') {
                foreach ($groupData as $settingsGroupName => $settingsGroupData) {
                    self::setupGroupSettings($settingsGroupName, $settingsGroupData);
                }
                continue;
            }

            foreach ($groupData as $banner) {
                $bannersIds[] = self::setupBanner($banner, $groupName, $imagePath, $platformIds);
            }
        }
        return $bannersIds;
    }

    public static function setupGroupSettings($groupName, $groupData)
    {
        foreach ($groupData as $imageWidth => $banner) {
            $group = \common\models\BannersGroups::findOne([
                'banners_group' => $groupName,
                'image_width' => $imageWidth
            ]);
            if ($group) continue;

            $group = new \common\models\BannersGroups();
            $group->attributes = [
                'banners_group' => $groupName,
                'width_from' => $banner['width_from'],
                'width_to' => $banner['width_to'],
                'image_width' => $imageWidth,
            ];
            $group->save();
        }
    }

    public static function setupBanner($banner, $groupName, $imagePath, $platformIds = [])
    {
        foreach ($banner['languages'] as $languageKey => $language) {
            $languageData = Language::get_language_id($languageKey);
            $bannerData = \common\models\Banners::find()->alias('b')
                ->select(['b.banners_id'])
                ->leftJoin(\common\models\BannersLanguages::tableName(). ' bl', 'b.banners_id = bl.banners_id')
                ->where([
                    'banners_group' => $groupName,
                    'banners_title' => $language['banners_title'],
                    'language_id' => $languageData['languages_id'],
                ])
                ->asArray()->one();
            if ($bannerData['banners_id']) {
                return $bannerData['banners_id'];
            }
        }

        $bannerModel = new \common\models\Banners();
        $bannerModel->attributes = [
            'banners_group' => $groupName,
            'expires_impressions' => $banner['expires_impressions'],
            'expires_date' => $banner['expires_date'],
            'date_scheduled' => $banner['date_scheduled'],
            'date_added' => new \yii\db\Expression('NOW()'),
            'status' => $banner['status'],
            'sort_order' => $banner['sort_order'],
            'banner_type' => $banner['banner_type'],
        ];
        $bannerModel->save(false);
        $bannerId = $bannerModel->getPrimaryKey();
        if (!$bannerId) return '';

        foreach ($banner['languages'] as $languageKey => $language) {

            $bannerImage = \common\classes\Images::moveImage($imagePath . $language['banners_image'], 'banners' . DIRECTORY_SEPARATOR . $bannerId, false);
            $languageData = Language::get_language_id($languageKey);
            $bannersLanguages = new \common\models\BannersLanguages();
            $bannersLanguages->attributes = [
                'banners_id' => $bannerId,
                'banners_title' => $language['banners_title'],
                'banners_url' => $language['banners_url'],
                'banners_image' => $bannerImage,//
                'banners_html_text' => $language['banners_html_text'],
                'language_id' => (int)$languageData['languages_id'],
                'target' => $language['target'],
                'banner_display' => $language['banner_display'],
                'text_position' => $language['text_position'],
                'svg' => $language['svg'],
            ];
            $bannersLanguages->save();

            /*foreach ($platformIds as $platformId) {
                if (!\common\models\BannersToPlatform::findOne([])) {
                    $bannersToPlatform = new \common\models\BannersToPlatform();
                    $bannersToPlatform->banners_id = $bannerId;
                    $bannersToPlatform->platform_id = $platformId;
                    $bannersToPlatform->save();
                }
            }*/

            foreach ($language['groupsImages'] as $groupsImage) {

                $bannerImageGroup = \common\classes\Images::moveImage($imagePath . $groupsImage['image'], 'banners' . DIRECTORY_SEPARATOR . $bannerId, false);

                $groupsImageModel = new \common\models\BannersGroupsImages();
                $groupsImageModel->attributes = [
                    'banners_id' => $bannerId,
                    'language_id' => (int)$languageData['languages_id'],
                    'image_width' => $groupsImage['image_width'],
                    'image' => $bannerImageGroup
                ];
                $groupsImageModel->save();
            }
        }
        return $bannerId;
    }

    public static function addBannerImages($banner, $groupName, $imagePath)
    {
        $path = DIR_FS_CATALOG . DIR_WS_IMAGES;
        foreach ($banner['languages'] as $languageKey => $language) {

            $imageInDb = \common\models\BannersLanguages::find()->where(['banners_image' => $language['banners_image']])->count();
            if ($imageInDb && !is_file($path . $language['banners_image']) && is_file($imagePath . $language['banners_image'])) {

                $destination = substr($language['banners_image'], 0, strrpos($language['banners_image'], "/"));
                \yii\helpers\FileHelper::createDirectory($path . $destination, 0777);

                copy($imagePath . $language['banners_image'], $path . $language['banners_image']);
            }

            foreach ($language['groupsImages'] as $groupsImage) {
                $imageInDb = \common\models\BannersGroupsImages::find()->where(['image' => $groupsImage['image']])->count();
                if ($imageInDb && !is_file($path . $groupsImage['image']) && is_file($imagePath . $groupsImage['image'])) {

                    $destination = substr($groupsImage['image'], 0, strrpos($groupsImage['image'], "/"));
                    \yii\helpers\FileHelper::createDirectory($path . $destination, 0777);

                    copy($imagePath . $groupsImage['image'], $path . $groupsImage['image']);
                }
            }
        }
    }
}
