<?php

namespace common\models\queries;

use yii\db\ActiveQuery;

class GoogleSettingsQuery extends ActiveQuery {

    public function platform($platform_id = 0){
        if ($platform_id){
            $this->andWhere(['platform_id' => $platform_id]);
        }
        return $this;
    }
    
    public function status ($status = null){
        if (!is_null($status)){
            $this->andWhere(['status' => (int)$status]);
        }
        return $this;
    }
    
    public function modules (array $modules = []){
        if ($modules){
            $this->andWhere(['module' => $modules]);
        }
        return $this;
    }
}