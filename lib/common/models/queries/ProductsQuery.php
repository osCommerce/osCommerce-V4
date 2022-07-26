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

class ProductsQuery extends ActiveQuery{
    use \common\helpers\SqlTrait;

    public function withDescription( $language  = null) {

        if(!$language){
            return $this->joinWith( [ 'descriptions'] );
        }

        return $this->joinWith( [ 'descriptions' => function (ActiveQuery $query ) USE ($language) {
            $query->where(['products_description.language_id' => $language ]);
        }]);
    }

    public function wDescription(string $alias = 'pd', int $languageId = null, $platformId = null )
    {
        if (empty($languageId)) {
            $languageId = (int) \Yii::$app->settings->get('languages_id');
        }
        if (is_null($platformId)) {
            $platformId = \common\classes\platform::currentId();
        }
            
        $alias_suffix = $alias_prefix = '';
        if (!empty($alias)) {
            $alias_suffix = " $alias";
            $alias_prefix = "$alias.";
        }
        return $this->joinWith(['productsDescriptions' . $alias_suffix => function (ActiveQuery $query) USE ($languageId, $alias_prefix, $platformId) {
                        $query->andOnCondition([$alias_prefix . 'language_id' => $languageId, $alias_prefix . 'platform_id' => $platformId]);
                    }], false);
    }

    public function addFrontendDescription($products_name_field = 'products_name')
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $platform_id_pri = (int) \Yii::$app->get('platform')->config()->getPlatformToDescription();
        $platform_id_def = intval(\common\classes\platform::defaultId());
        return $this->wDescription('pd1', $languages_id, $platform_id_pri)
                    ->wDescription('pd2', $languages_id, $platform_id_def)
                    ->addSelect([$products_name_field => new \yii\db\Expression('if(length(pd1.products_name), pd1.products_name, pd2.products_name)')] );
    }


    public function active()
    {
        return $this->andWhere(['products_status' => 1]);
    }
}
