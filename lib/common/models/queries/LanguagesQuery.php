<?php
namespace common\models\queries;

use common\models\Languages;
use yii\db\ActiveQuery;

class LanguagesQuery extends ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['languages_status' => Languages::STATUS_ACTIVE]);
    }

}