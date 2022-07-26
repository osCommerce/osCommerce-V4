<?php

namespace common\extensions\OscLink\models; 

use Yii;

/**
 * This is the model class for table "connector_osclink_entity".
 *
 * @property int $id
 * @property int $project_id
 * @property string $entity_name
 */
class Entity extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'connector_osclink_entity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'entity_name'], 'required'],
            [['project_id'], 'integer'],
            [['entity_name'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'Project ID',
            'entity_name' => 'Entity Name',
        ];
    }


    public function getMapping()
    {
        return $this->hasMany(Mapping::class, ['entity_id' => 'id']);
    }

    public static function cleanMapping()
    {
        $statusId = self::returnEntityId('@order_status');
        $condition_mapping = empty($statusId) ? '' : "entity_id <> $statusId";
        $condition_entity = empty($statusId) ? '' : "id <> $statusId";

        \common\extensions\OscLink\models\Mapping::deleteAll($condition_mapping);
        \common\extensions\OscLink\models\Entity::deleteAll($condition_entity);
    }



    public static function isMappedExist()
    {
        $statusId = self::returnEntityId('@order_status');
        $condition_mapping = empty($statusId) ? '' : "entity_id <> $statusId";
        return !empty(Mapping::find()->where($condition_mapping)->one());
    }

    public static function returnEntityId($name, $project_id = 1)
    {
        $row = self::findOne(['project_id' => $project_id, 'entity_name' => $name]);
        return empty($row)? null : $row->id;
    }

    public static function forceEntityId($name, $project_id = 1)
    {
        $res = self::returnEntityId($name, $project_id);
        if (empty($res)) {
            $row = new self();
            $row->project_id = $project_id;
            $row->entity_name = $name;
            $row->save(false);
            $row->refresh();
            $res = $row->id;
        }
        return $res;
    }

}
