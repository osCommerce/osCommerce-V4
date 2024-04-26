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
        $languageId = (int) \Yii::$app->settings->get('languages_id');
        return $this->addIfDescription(
            $products_name_field,
            $languageId,
            (int) \Yii::$app->get('platform')->config()->getPlatformToDescription(),
            'pd1',
            $languageId,
        );
    }

    public function addIfDescription($products_name_field, $languageId, $platformId, $alias = 'pd1', $languageIdDef = null, $platformIdDef = null, $aliasDef = 'pd2')
    {
        if (is_null($languageIdDef)) {
            $languageIdDef = (int) \common\helpers\Language::get_default_language_id();
        }
        if (is_null($platformIdDef)) {
            $platformIdDef = (int) \common\classes\platform::defaultId();
        }

        return $this->wDescription($alias, $languageId, $platformId)
            ->wDescription($aliasDef, $languageIdDef, $platformIdDef)
            ->addSelect([$products_name_field => new \yii\db\Expression("if(length({$alias}.products_name), {$alias}.products_name, {$aliasDef}.products_name)")] );
    }

    public function active()
    {
        return $this->andWhere(['products_status' => 1]);
    }
}
