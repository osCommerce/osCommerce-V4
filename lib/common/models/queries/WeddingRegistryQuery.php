<?php

namespace common\models\queries;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[WeddingRegistry]].
 *
 * @see WeddingRegistry
 */
class WeddingRegistryQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return WeddingRegistry[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    public function withShipCountry($language_id){
	    return $this->joinWith( [ 'shippingCountry' => function (ActiveQuery $query ) USE ($language_id) {
		    $query->where(['countries.language_id' => $language_id ]);
	    }]);
    }

    public function withProducts($language_id){
	    return $this->joinWith( [ 'products.product.descriptions' => function (ActiveQuery $query ) USE ($language_id) {
		    $query->where(['products_description.language_id' => $language_id ]);
	    }]);
    }

    /**
     * @inheritdoc
     * @return WeddingRegistry|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
