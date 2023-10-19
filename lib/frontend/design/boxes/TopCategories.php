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

class TopCategories extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();

        Info::includeJsFile('boxes/Categories');
        Info::addJsData(['widgets' => [
            $this->id => [ 'lazyLoad' => (isset($this->settings[0]['lazy_load']) ? $this->settings[0]['lazy_load'] : [])]
        ]]);
        if (ArrayHelper::getValue($this->settings,[0, 'view_as']) == 'carousel') {
            Info::addBoxToCss('slick');
        }
    }

    public function run()
    {
        $categories = \common\models\Categories::getHomepageCategories();
        if ($ext =\common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions'))
        {
            $categories->innerJoin('groups_categories gc')
                ->andWhere('gc.categories_id = {{%categories}}.categories_id and gc.groups_id ='.(int) \Yii::$app->storage->get('customer_groups_id'));
        }
        $categories->select('{{%categories}}.categories_id, parent_id, {{%categories}}.maps_id, {{%categories}}.categories_image, {{%categories}}.categories_image_3, {{%categories}}.show_on_home')
            ->orderBy("categories_left, sort_order, categories_name");


        if (isset($this->settings[0]['max_items']) && $this->settings[0]['max_items']) {
            if (!Info::themeSetting('show_empty_categories')) {
                $categories->limit((int)$this->settings[0]['max_items'] * 2);
            } else {
                $categories->limit((int)$this->settings[0]['max_items']);
            }
        }

        $cats = $categories->asArray()->all();


        if (!$cats || !is_array($cats)) {
            return '';
        }
        $cats_count = 0;
        foreach ($cats as $k => $category) {

            if (!Info::themeSetting('show_empty_categories') && \common\helpers\Categories::count_products_in_category($category['categories_id']) == 0) {
                unset($cats[$k]);
                continue;
            }
            if (isset($this->settings[0]['max_items']) && $this->settings[0]['max_items']) {
                $cats_count++;
                if ($cats_count > (int)$this->settings[0]['max_items']) {
                    unset($cats[$k]);
                    continue;
                }
            }

            $cats[$k]['link'] = tep_href_link('catalog', 'cPath=' . $category['categories_id']);
            if (isset($category['descriptions'][0])) {
                $cats[$k]['categories_h2_tag'] = $category['descriptions'][0]['categories_h2_tag'];
                $cats[$k]['categories_name'] = $category['descriptions'][0]['categories_name'];
                unset($cats[$k]['descriptions']);
            }

            $img = '';
            if (!empty($category['platformSettings']['categories_image_3'])) {
                $img = $category['platformSettings']['categories_image_3'];
            } elseif (!empty($category['categories_image_3'])) {
                $img = $category['categories_image_3'];
            } elseif (!empty($category['platformSettings']['categories_image'])) {
                $img = $category['platformSettings']['categories_image'];
            } elseif (!empty($category['categories_image'])) {
                $img = $category['categories_image'];
            }
            $cats[$k]['categories_h2_tag'] = \common\helpers\Html::fixHtmlTags($cats[$k]['categories_h2_tag']);
            $cats[$k]['categories_name'] = \common\helpers\Html::fixHtmlTags($cats[$k]['categories_name']);
            $cats[$k]['img'] = \common\classes\Images::getImageSet(
                $img,
                'Category homepage',
                [
                    'alt' => $cats[$k]['categories_name'],
                    'title' => $cats[$k]['categories_name'],
                ],
                Info::themeSetting('na_category', 'hide'),
                (boolean)ArrayHelper::getValue($this->settings, [0,'lazy_load'])
            );

            unset($cats[$k]['platformSettings']);
        }

        if (count($cats) == 0){
            return '';
        }

        $cats = array_values($cats);

        return IncludeTpl::widget([
            'file' => 'boxes/categories.tpl',
            'params' => [
                'categories' => $cats,
                'themeImages' => DIR_WS_THEME_IMAGES,
                'lazy_load' => ArrayHelper::getValue($this->settings, [0,'lazy_load']),
                'settings' => $this->settings,
                'id' => $this->id,
            ]
        ]);


    }
}
