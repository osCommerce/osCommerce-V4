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

namespace common\models\queries;


use yii\db\ActiveQuery;
use paulzi\nestedsets\NestedSetsQueryTrait;
use common\models\Categories;

class CategoriesQuery extends ActiveQuery{

  use NestedSetsQueryTrait;

    public function withListDescription( ) {
      $languages_id = \Yii::$app->settings->get('languages_id');
      return $this->withDescription($languages_id);
    }



/**
 * link to products_to_categories
 * @return \yii\db\ActiveQuery
 */
    public function withProductIds() {
       return $this->joinWith('productIds');
    }

    public function withDescription( $language  = null) {

        if(!$language){
            return $this->joinWith( [ 'descriptions'] );
        }

        return $this->joinWith( [ 'descriptions' => function (ActiveQuery $query ) USE ($language) {
            $query->andWhere(['categories_description.language_id' => $language ]);
        }]);
    }
    public function active()
    {
        return $this->andWhere(['categories_status' => 1]);
    }

    public function withNestedCategories($aliasMain = 'c', $aliasNested = 'c1')
    {
        return $this->leftJoin(Categories::tableName() . " $aliasNested", "$aliasMain.categories_left <= $aliasNested.categories_left AND $aliasMain.categories_right >= $aliasNested.categories_right");
    }

}
