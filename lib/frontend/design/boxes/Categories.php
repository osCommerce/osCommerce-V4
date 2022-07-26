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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class Categories extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();

        Info::addJsData(['widgets' => [
            $this->id => [ 'lazyLoad' => ArrayHelper::getValue($this->settings, [0, 'lazy_load'])]
        ]]);
        if (ArrayHelper::getValue($this->settings,[0, 'view_as']) == 'carousel') {
            Info::addBoxToCss('slick');
        }
    }

    public function run()
    {
        global $current_category_id;

        if (!$current_category_id) {
            return '';
        }

        $categories = \common\models\Categories::find()->andWhere(['parent_id' => (int)$current_category_id])->andWhere(['categories_status' => 1])
            ->joinWith('description')
            ->joinWith('currentPlatform', false)
            ->with('platformSettings')
            ->select('{{%categories}}.categories_id, parent_id, {{%categories}}.maps_id, {{%categories}}.categories_image, {{%categories}}.categories_image_3, {{%categories}}.show_on_home')
            ->orderBy("sort_order, categories_name");

        if ($this->settings[0]['max_items'] ?? null) {
            $categories->limit((int)$this->settings[0]['max_items']);
        }

        $cats = $categories->asArray()->all();

        if (!$cats || !is_array($cats)) {
            return '';
        }

        foreach ($cats as $k => $category) {
            if (!Info::themeSetting('show_empty_categories') && \common\helpers\Categories::count_products_in_category($category['categories_id']) == 0) {
                unset($cats[$k]);
                continue;
            }

            $cats[$k]['link'] = Yii::$app->urlManager->createUrl(['catalog', 'cPath' => $category['categories_id']]);
            if (isset($category['description'])) {
                $cats[$k]['categories_h2_tag'] = $category['description']['categories_h2_tag'];
                $cats[$k]['categories_name'] = $category['description']['categories_name'];
                unset($cats[$k]['description']);
            }
            $cats[$k]['categories_h2_tag'] = \common\helpers\Html::fixHtmlTags($cats[$k]['categories_h2_tag']);
            $cats[$k]['categories_name'] = \common\helpers\Html::fixHtmlTags($cats[$k]['categories_name']);
            if (!$this->settings[0]['hide_images']) {
                if (!empty($category['platformSettings']['categories_image'])) {
                    $img = $category['platformSettings']['categories_image'];
                } else {
                    $img = $category['categories_image'];
                }
                $cats[$k]['img'] = \common\classes\Images::getImageSet(
                    $img,
                    'Category gallery',
                    [
                        'alt' => $cats[$k]['categories_name'],
                        'title' => $cats[$k]['categories_name'],
                    ],
                    Info::themeSetting('na_category', 'hide'),
                    $this->settings[0]['lazy_load'] ?? null
                );
                if ( empty($this->settings[0]['skip_product_image']) && !is_file(\common\classes\Images::getFSCatalogImagesPath().$img) ) {
                    $cats[$k]['img'] = \common\helpers\Html::tag('picture',\common\helpers\Html::img($this->getCategoryImage($category['categories_id']),[
                        'alt' => $cats[$k]['categories_name'],
                        'title' => $cats[$k]['categories_name'],
                        'class' => 'category_image_from_product',
                    ]));
                }
            }else{
                $cats[$k]['img'] = '';
            }
            unset($cats[$k]['platformSettings']);
        }

        $cats = array_values($cats);

        if (count($cats) == 0) {
            return false;
        }

        return IncludeTpl::widget([
            'file' => 'boxes/categories.tpl',
            'params' => [
                'categories' => $cats,
                'themeImages' => DIR_WS_THEME_IMAGES,
                'lazy_load' => $this->settings[0]['lazy_load'] ?? null,
                'settings' => $this->settings,
                'id' => $this->id,
            ]
        ]);
    }

    protected function getCategoryImage($cid){
        $category_na_image = Info::themeSetting('na_category', 'hide');
        $image = empty($category_na_image)?'no':$category_na_image;
        $naProductImage = Info::themeSetting('na_product', 'hide');
        $ids = \common\helpers\Categories::ids_products_in_category($cid);
        if (is_array($ids) && count($ids)){
            //sort($ids);
            $enough = false;
            do{
                $id = current($ids);
                $image = \common\classes\Images::getImageUrl($id, 'Medium');
                $item = next($ids);
                if ($naProductImage!==$image)
                    $enough = true;
                if (!$item){ return $image; }
            } while(!$enough);
        } else {
            if (\common\helpers\Categories::has_category_subcategories($cid)){
                $subs = [];
                \common\helpers\Categories::get_subcategories($subs, $cid, false);
                //return $image;
                if ($subs){
                    foreach($subs as $sub){
                        $image = $this->getCategoryImage($sub);
                        if ($image!='no' && $image!=$category_na_image && $naProductImage!==$image) break;
                        //return $this->getCategoryImage($sub);
                    }
                }
            }
        }
        return $image;
    }
}
