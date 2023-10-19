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

namespace backend\design;

use common\classes\Images;
use common\classes\platform as Platform;
use common\models\BannersLanguages;
use common\models\Categories;
use common\models\CategoriesDescription;
use common\models\Products2Categories;
use common\models\ProductsDescription;
use common\models\ProductsImages;
use common\models\ProductsImagesDescription;
use common\models\Themes;
use yii\helpers\FileHelper;

class FileManager
{
    public static $validFileTypes = ['image', 'video', 'pdf', 'txt', 'svg', 'svg+xml'];
    public static $validDirectories = ['main', 'themes', 'banners', 'products', 'categories', 'pages'];
    public static $fileTypes = [];

    public static function getFiles($directory = ['main'], $fileTypes = '')
    {
        self::$fileTypes = self::convertFileTypes($fileTypes);

        if (isset($directory[1]['name']) && in_array($directory[1]['name'], self::$validDirectories) &&
            method_exists(self::class, $directory[1]['name'])
        ) {
            [$allFiles, $directories] = call_user_func([self::class, $directory[1]['name']], $directory);
        } else {
            [$allFiles, $directories] = self::main();
        }

        foreach (\common\helpers\Hooks::getList('design/file-manager') as $filename) {
            include($filename);
        }

        $filteredFiles = [];
        $counter = 0;

        foreach ($allFiles as $file) {
            $type = self::getFileType((is_array($file) ? $file['fileHash'] : $file));
            if (!in_array($type, self::$fileTypes)) {
                continue;
            }

            $counter++;

            $filteredFiles[] = [
                'file' => (is_array($file) ? $file['file'] : $file),
                'fileHash' => (is_array($file) ? $file['fileHash'] : ''),
                'fileName' => (is_array($file) ? $file['fileName'] : pathinfo($file, PATHINFO_BASENAME)),
                'type' => $type,
            ];
        }

        return [
            'allCount' => $counter,
            'files' => $filteredFiles,
            'directories' => $directories,
        ];
    }

    public static function main()
    {
        $allFiles = scandir(Images::getFSCatalogImagesPath());

        $directories = [
            ['name' => 'banners', 'title' => 'Banners'],
            ['name' => 'products', 'title' => 'Products'],
            ['name' => 'categories', 'title' => 'Categories'],
            ['name' => 'pages', 'title' => 'Info Pages'],
        ];

        $themesImages = false;
        foreach (Themes::find()->asArray()->all() as $theme) {
            if (is_dir(Images::getFSCatalogImagesPath() . '../themes/' . $theme['theme_name'] . '/img')) {
                $themesImages = true;
                break;
            }
        }
        if ($themesImages) {
            $directories[] = ['name' => 'themes', 'title' => 'Themes'];
        }

        return [$allFiles, $directories];
    }

    public static function themes($directory)
    {
        $allFiles = [];
        $directories = [];
        if (isset($directory[2]['name'])) {
            if (is_dir(Images::getFSCatalogImagesPath() . '../themes/' . $directory[2]['name'] . '/img')) {
                $files = scandir(Images::getFSCatalogImagesPath() . '../themes/' . $directory[2]['name'] . '/img');
                foreach ($files as $file) {
                    $allFiles[] = '../themes/' . $directory[2]['name'] . '/img/' . $file;
                }
            }
        } else {
            foreach (Themes::find()->asArray()->all() as $theme) {
                if (is_dir(Images::getFSCatalogImagesPath() . '../themes/' . $theme['theme_name'] . '/img')) {
                    $directories[] = ['name' => $theme['theme_name'], 'title' => $theme['title']];
                }
            }
        }

        return [$allFiles, $directories];
    }

    public static function banners($directory)
    {
        $languageId = \Yii::$app->settings->get('languages_id');
        $allFiles = [];
        $directories = [];
        if (isset($directory[2]['name'])) {
            if (is_dir(Images::getFSCatalogImagesPath() . 'banners/' . $directory[2]['name'])) {
                $files = scandir(Images::getFSCatalogImagesPath() . 'banners/' . $directory[2]['name']);
                foreach ($files as $file) {
                    $allFiles[] = 'banners/' . $directory[2]['name'] . '/' . $file;
                }
            }
        } else {
            $banners = BannersLanguages::find()->where(['language_id' => $languageId])->asArray()->all();
            foreach ($banners as $banner) {
                if (is_dir(Images::getFSCatalogImagesPath() . 'banners/' . $banner['banners_id'])) {
                    $directories[] = ['name' => $banner['banners_id'], 'title' => $banner['banners_title']];
                }
            }
        }

        return [$allFiles, $directories];
    }

    public static function pages($directory)
    {
        $allFiles = [];
        $directories = [];

        if (is_dir(Images::getFSCatalogImagesPath() . 'information')) {
            $files = scandir(Images::getFSCatalogImagesPath() . 'information/');
            foreach ($files as $file) {
                $allFiles[] = 'information/' . $file;
            }
        }

        return [$allFiles, $directories];
    }

    public static function categories($directory)
    {
        $languageId = \Yii::$app->settings->get('languages_id');
        $allFiles = [];
        $directories = [];
        $directoryEnd = end($directory);

        if (isset($directoryEnd['name'])) {
            foreach (['', '/hero', '/gallery', '/homepage', '/menu'] as $folder) {
                if (is_dir(Images::getFSCatalogImagesPath() . 'categories/' . $directoryEnd['name'] . $folder)) {
                    $files = scandir(Images::getFSCatalogImagesPath() . 'categories/' . $directoryEnd['name'] . $folder);
                    foreach ($files as $file) {
                        $allFiles[] = 'categories/' . $directoryEnd['name'] . $folder . '/' . $file;
                    }
                }
            }
        }

        $categoryId = 0;
        if (isset($directory[2]) && isset($directoryEnd['name'])) {
            $categoryId = $directoryEnd['name'];
        }

        $categories = Categories::find()->alias('c')
            ->select(['title' => 'cd.categories_name', 'name' => 'c.categories_id'])
            ->leftJoin(CategoriesDescription::tableName() . ' cd',
                "c.categories_id = cd.categories_id and cd.language_id = '" . $languageId . "'")
            ->andWhere(['c.parent_id' => $categoryId])
            ->asArray()->all();

        foreach ($categories as $category) {
            if (self::hasCategoryImages($category['name'])) {
                $directories[] = $category;
            }
        }

        return [$allFiles, $directories];
    }

    public static function products($directory)
    {
        $languageId = \Yii::$app->settings->get('languages_id');
        $allFiles = [];
        $directories = [];
        $directoryEnd = end($directory);

        $split = explode('-', $directoryEnd['name']);
        if ($split[0] == 'product') {

            if (isset($split[1])) {

                $productsImages = ProductsImagesDescription::find()->alias('pid')
                    ->select('pid.*, pi.products_id')
                    ->leftJoin(ProductsImages::tableName() . ' pi',
                        'pi.products_images_id = pid.products_images_id')
                    ->andWhere(['pi.products_id' => $split[1]])
                    ->andWhere(['not', ['pid.hash_file_name' => '']])
                    ->asArray()->all();

                foreach ($productsImages as $image) {
                    $allFiles[] = [
                        'file' => 'products/' . $image['products_id'] . '/' . $image['products_images_id'] . '/' . $image['orig_file_name'],
                        'fileHash' => 'products/' . $image['products_id'] . '/' . $image['products_images_id'] . '/' . $image['hash_file_name'],
                        'fileName' => $image['orig_file_name'],
                    ];
                }
            }

        } else {
            $categoryId = 0;
            if (isset($directory[2]) && isset($directoryEnd['name'])) {
                $categoryId = $directoryEnd['name'];
            }

            $categories = Categories::find()->alias('c')
                ->select(['title' => 'cd.categories_name', 'name' => 'c.categories_id'])
                ->leftJoin(CategoriesDescription::tableName() . ' cd',
                    "c.categories_id = cd.categories_id and cd.language_id = '" . $languageId . "'")
                ->andWhere(['c.parent_id' => $categoryId])
                ->asArray()->all();

            foreach ($categories as $category) {
                $directories[] = $category;
            }

            $products = Products2Categories::find()->alias('p2c')
                ->select(['pd.products_name', 'pd.products_id'])
                ->leftJoin(ProductsDescription::tableName() . ' pd',
                    "p2c.products_id = pd.products_id and pd.language_id = '" . $languageId . "' and pd.platform_id = '" . Platform::defaultId() . "'")
                ->andWhere(['p2c.categories_id' => $categoryId])
                ->asArray()->all();

            foreach ($products as $product) {
                $directories[] = ['name' => 'product-' . $product['products_id'], 'title' => $product['products_name']];
            }
        }

        return [$allFiles, $directories];
    }

    public static function hasCategoryImages($categoryId)
    {
        foreach (['', '/hero', '/gallery', '/homepage', '/menu'] as $folder) {
            if (is_dir(Images::getFSCatalogImagesPath() . 'categories/' . $categoryId . $folder)) {
                $files = scandir(Images::getFSCatalogImagesPath() . 'categories/' . $categoryId . $folder);
                foreach ($files as $file) {
                    if (!is_dir(Images::getFSCatalogImagesPath() . 'categories/' . $categoryId . $folder . '/' . $file)) {
                        return true;
                    }
                }
            }
        }

        $categories = Categories::find()->select(['categories_id'])
            ->where(['parent_id' => $categoryId])->asArray()->all();

        foreach ($categories as $category) {
            if (self::hasCategoryImages($category['categories_id'])) {
                return true;
            }
        }

        return false;
    }

    public static function createThumbnails($file)
    {
        $thumbnailImg = 'thumbnails/' . $file;
        $thumbnail = Images::getFSCatalogImagesPath() . $thumbnailImg;
        FileHelper::createDirectory(dirname($thumbnail));

        if (!is_file($thumbnail)) {

            if (!is_file(Images::getFSCatalogImagesPath() . $file)) {
                $splitFile = explode('/', $file);
                if ($splitFile[0] == 'products') {
                    $fileName = end($splitFile);
                    $img = ProductsImagesDescription::find()->where([
                        'orig_file_name' => $fileName,
                        'products_images_id' => $splitFile[2],
                    ])->asArray()->one();
                    $hash = (isset($img['hash_file_name']) ? $img['hash_file_name'] : $fileName);
                    $splitFile[count($splitFile) - 1] = $hash;
                    $file = implode('/', $splitFile);
                }
            }

            if (is_file(Images::getFSCatalogImagesPath() . $file)) {
                Images::tep_image_resize(Images::getFSCatalogImagesPath() . $file, $thumbnail, 150, 110);
            }
        }
        return ['thumbnail' => $thumbnailImg];
    }

    public static function validateFileTypes($fileTypes)
    {
        if (!$fileTypes || !is_array($fileTypes)) {
            return self::$validFileTypes;
        }
        $types = [];
        foreach ($fileTypes as $type) {
            if (in_array($type, self::$validFileTypes)){
                $types[] = $type;
            }
        }
        return $types;
    }

    public static function getFileType($file)
    {
        if (!is_file(Images::getFSCatalogImagesPath() . $file)) {
            return '';
        }
        $fullType = explode('/', mime_content_type(Images::getFSCatalogImagesPath() . $file));

        if (in_array($fullType[0], ['application', 'text'])) {
            return $fullType[1];
        }
        if (isset($fullType[1]) && in_array($fullType[1], ['svg', 'svg+xml'])) {
            return $fullType[1];
        }
        return $fullType[0];
    }

    public static function convertFileTypes($fileTypes)
    {
        if (!$fileTypes) {
            return self::$validFileTypes;
        }
        $types = [];
        $typesArr = explode(',', $fileTypes);
        foreach ($typesArr as $type) {
            $type = trim($type, ' .');
            $chunks = explode('/', $type);

            if (in_array($chunks[0], ['application', 'text'])) {
                $types[] = $chunks[1];
            } elseif (isset($chunks[0]) && isset($chunks[1]) && $chunks[0] == 'image' && $chunks[1] == '*') {
                $types[] = 'image';
                $types[] = 'svg';
                $types[] = 'svg+xml';
            } elseif (isset($chunks[1]) && in_array($chunks[1], ['svg', 'svg+xml'])) {
                $types[] = $chunks[1];
            } else {
                $types[] = $chunks[0];
            }
        }

        return self::validateFileTypes($types);
    }

}
