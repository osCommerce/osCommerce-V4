<?php

namespace common\models;

use common\models\Customers;

/**
 * This is the model class for table "personal_catalog_list".
 * @property integer $customers_id
 * @property integer $list_id
 * @property integer $add_flag added by customer manually
 * @property string $list_name
 */
class PersonalCatalogList extends \yii\db\ActiveRecord
{
    public static $defaltName;
    public $className;
    public $isEmpty;

    public static function tableName() {
        return 'personal_catalog_list';
    }
    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['customers_id', 'add_flag', 'list_name'], 'required'],
            [['customers_id','add_flag'], 'integer'],
            ['is_default', 'default', 'value'=>0 ],
            [['list_name'], 'string', 'max' => 128],
        ];
    }
        
    public function getCustomer()
    {
        return $this->hasOne(Customers::class, ['customers_id' => 'customers_id']);
    }
    
    public function getProducts(){
        return $this->hasMany(PersonalCatalog::class, ['list_id' => 'list_id']);
    }

    public function afterFind() {
      parent::afterFind();
      $this->isEmpty = !$this->getProducts()->exists();
    }
    /**
     * @param int $customers_id
     * @param int|string $products_id
     * @param int $add_flag
     * @param int $qty
     * @return self
     */
    public static function create(int $customers_id, int $add_flag /*= 0*/, string $list_name, int $is_default = 0): self
    {
        if($customers_id < 1 ){
            throw new \DomainException("wrong input data \$customers_id {$customers_id}");
        }
        $model = new self();
        $model->customers_id = $customers_id;
        $model->add_flag = $add_flag;
        $model->list_name = $list_name;
        $model->is_default = $is_default;
        $model->save();
        return $model;
    }

}
