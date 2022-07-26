<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 14.02.18
 * Time: 12:08
 */
namespace common\models\queries;

use yii\db\ActiveQuery;

class Properties2ProductsQuery extends ActiveQuery {

    public function withPropertiesDescription(int $languages_id = null, string $alias = 'prd')
    {
        if (is_null($languages_id)) {
            $languages_id = $languages_id = (int) \Yii::$app->settings->get('languages_id');
        }

        return $this->joinWith(["propertiesDescriptions $alias" => function ($query) use ($alias, $languages_id) { $query->andOnCondition(["$alias.language_id" => $languages_id]); }], false);
    }

    public function withPropertiesValue(int $languages_id = null, string $alias = 'pv')
    {
        if (is_null($languages_id)) {
            $languages_id = $languages_id = (int) \Yii::$app->settings->get('languages_id');
        }

        return $this->joinWith(["values $alias" => function ($query) use ($alias, $languages_id) { $query->andOnCondition(["$alias.language_id" => $languages_id]); }], false);
    }

}