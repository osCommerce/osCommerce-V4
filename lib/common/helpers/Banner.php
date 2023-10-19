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

use common\models\BannersGroups;
use common\models\BannersGroupsSizes;
use common\models\BannersToPlatform;
use common\models\MenuTitles;
use Yii;
use common\models\Menus;
use common\models\MenuItems;


class Banner {

    public static function groupData($group, $platforms = [], $activeStatus = false)
    {
        $bannersData = [];
        $bannersQuery = \common\models\Banners::find()->alias('b')
            ->leftJoin(BannersGroups::tableName() . ' bg', 'bg.id = b.group_id')
            ->where(['banners_group' => $group]);
        if (count($platforms)) {
            $bannersQuery
                ->leftJoin(BannersToPlatform::tableName() . ' b2p', 'b2p.banners_id = b.banners_id')
                ->andWhere(['in', 'platform_id', $platforms]);
        }
        if ($activeStatus) {
            $bannersQuery->andWhere(['status' => '1']);
        }
        $banners = $bannersQuery->asArray()->all();

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
                if (!isset($languageKey['code'])) {
                    continue;
                }
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
        $groupSettings = \common\models\BannersGroups::find()->alias('bg')
            ->innerJoin(BannersGroupsSizes::tableName() . ' bgs', 'bg.id = bgs.group_id')
            ->where(['banners_group' => $group])
            ->asArray()->all();
        foreach ($groupSettings as $groupSetting) {
            if (isset($groupSetting['image_width']) && $groupSetting['image_width']) {
                $groupData[$groupSetting['image_width']] = [
                    'width_from' => $groupSetting['width_from'] ?? '',
                    'width_to' => $groupSetting['width_to'] ?? '',
                ];
            }
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
        if (isset($images[$imgKey]) && $images[$imgKey] && $imagePath != $images[$imgKey]) {
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

    public static function setupBanners($bannerData, $imagePath, $platformIds, $forceCreate = false)
    {
        foreach ($bannerData as $groupName => $groupData) {
            if ($groupName == 'groupSettings') {
                foreach ($groupData as $settingsGroupName => $settingsGroupData) {
                    $newGroupName = self::setupGroupSettings($settingsGroupName, $settingsGroupData, $forceCreate);
                    if ($forceCreate && $newGroupName != $settingsGroupName) {
                        $bannerData[$newGroupName] = $bannerData[$settingsGroupName];
                        unset($bannerData[$settingsGroupName]);
                    }
                }
                break;
            }
        }
        $bannersIds = [];
        foreach ($bannerData as $groupName => $groupData) {
            if ($groupName == 'groupSettings') {
                continue;
            }

            foreach ($groupData as $banner) {
                $bannersIds[] = self::setupBanner($banner, $groupName, $imagePath, $platformIds, $forceCreate);
            }
        }
        return $bannersIds;
    }

    public static function setupGroupSettings($groupName, $groupData, $forceCreate = false)
    {
        if ($forceCreate) {
            $_groupName = $groupName;
            for ($i = 1; BannersGroups::findOne(['banners_group' => $_groupName]) && $i < 100; $i++) {
                $_groupName = $groupName . '-' . $i;
            }
            $groupName = $_groupName;
        }

        $group = BannersGroups::findOne(['banners_group' => $groupName]);
        if (!$group) {
            $group = new \common\models\BannersGroups();
            $group->attributes = [
                'banners_group' => $groupName,
            ];
            $group->save();
        }
        $id = $group->getPrimaryKey();

        foreach ($groupData as $banner) {
            $groupSizes = BannersGroupsSizes::findOne([
                'group_id' => $id,
                'image_width' => $banner['image_width'],
            ]);
            if ($groupSizes) {
                continue;
            }
            $groupSizes = new BannersGroupsSizes();
            $groupSizes->attributes = [
                'group_id' => $id,
                'width_from' => $banner['width_from'],
                'width_to' => $banner['width_to'],
                'image_width' => $banner['image_width'],
                'image_height' => $banner['image_height'],
            ];
            $groupSizes->save(false);
        }

        return $groupName;
    }

    public static function setupBanner($banner, $groupName, $imagePath, $platformIds = [], $forceCreate = false)
    {
        if (!$forceCreate){
            foreach ($banner['languages'] as $languageKey => $language) {
                $languageData = Language::get_language_id($languageKey);
                if (!($languageData['languages_id'] ?? false)) {
                    continue;
                }
                $bannerData = \common\models\Banners::find()->alias('b')
                    ->select(['b.banners_id'])
                    ->leftJoin(\common\models\BannersGroups::tableName(). ' bg', 'b.group_id = bg.id')
                    ->leftJoin(\common\models\BannersLanguages::tableName(). ' bl', 'b.banners_id = bl.banners_id')
                    ->where([
                        'banners_group' => $groupName,
                        'banners_title' => $language['banners_title'],
                        'language_id' => $languageData['languages_id'],
                    ])
                    ->asArray()->one();
                if (isset($bannerData['banners_id']) && $bannerData['banners_id']) {
                    return $bannerData['banners_id'];
                }
            }
        }

        $bannersGroups = BannersGroups::findOne(['banners_group' => $groupName]);
        if (!$bannersGroups) {
            $bannersGroups = new BannersGroups();
            $bannersGroups->save();
            $groupId = $bannersGroups->getPrimaryKey();
        } else {
            $groupId = $bannersGroups->id;
        }

        $bannerModel = new \common\models\Banners();
        $bannerModel->attributes = [
            'group_id' => $groupId,
            'expires_impressions' => $banner['expires_impressions'] ?? '',
            'expires_date' => $banner['expires_date'] ?? '',
            'date_scheduled' => $banner['date_scheduled'] ?? '',
            'date_added' => new \yii\db\Expression('NOW()'),
            'status' => $banner['status'] ?? '',
            'sort_order' => $banner['sort_order'] ?? '',
            'banner_type' => $banner['banner_type'] ?? '',
        ];
        $bannerModel->group_id = $groupId;
        $bannerModel->save(false);
        $bannerId = $bannerModel->getPrimaryKey();
        if (!$bannerId) return '';

        foreach ($banner['languages'] as $languageKey => $language) {

            $bannerImage = \common\classes\Images::moveImage($imagePath . $language['banners_image'], 'banners' . DIRECTORY_SEPARATOR . $bannerId, false);
            $languageData = Language::get_language_id($languageKey);
            if (!($languageData['languages_id'] ?? false)) {
                continue;
            }
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

            if ($forceCreate) {
                foreach ($platformIds as $platformId) {
                    if (!\common\models\BannersToPlatform::findOne([])) {
                        $bannersToPlatform = new \common\models\BannersToPlatform();
                        $bannersToPlatform->banners_id = $bannerId;
                        $bannersToPlatform->platform_id = $platformId;
                        $bannersToPlatform->save();
                    }
                }
            }

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
