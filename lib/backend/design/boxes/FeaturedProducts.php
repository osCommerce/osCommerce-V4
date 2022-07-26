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

namespace backend\design\boxes;

use Yii;
use yii\base\Widget;

class FeaturedProducts extends Widget
{

    public $id;
    public $params;
    public $settings;
    public $visibility;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $languages_id = (int)\Yii::$app->settings->get('languages_id');
        $featuredTypes = \common\models\FeaturedTypes::find()->where([
            'language_id' => $languages_id
        ])->asArray()->all();

        $featuredTypesArr = [];
        $featuredTypesArr[0] = BOX_CATALOG_FEATURED;
        foreach ($featuredTypes as $featuredType) {
            $featuredTypesArr[$featuredType['featured_type_id']] = $featuredType['featured_type_name'];
        }

        $sortingOptions = \common\helpers\Sorting::getPossibleSortOptions();
        $sortingOptions[''] = TEXT_RANDOM;
        $sorting = \common\helpers\Html::dropDownList('setting[0][sort_order]',
            $this->settings[0]['sort_order'],
            $sortingOptions,
            ['class' => 'form-control']);

        return $this->render('featured-products.tpl', [
            'id' => $this->id,
            'params' => $this->params,
            'settings' => $this->settings,
            'visibility' => $this->visibility,
            'featuredTypes' => $featuredTypesArr,
            'sorting' => $sorting,
        ]);
    }
}