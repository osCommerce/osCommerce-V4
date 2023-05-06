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
namespace common\models\repositories;
use common\models\Product\ProductsNotes;


/**
 * Class ProductsNotesRepository
 * @package common\models\repositories
 */
final class ProductsNotesRepository
{
    /**
     * @param int|array $id
     * @param bool $asArray
     * @return array|ProductsNotes|\yii\db\ActiveRecord|null
     */
    public function findById($id,  bool $asArray = false)
    {
        $productsNotes = ProductsNotes::find()
            ->where(['products_notes_id' => $id])->asArray($asArray);
        if (is_array($id)) {
            $productsNotes->all();
        }
        return $productsNotes->limit(1)->one();
    }

    /**
     * @param int|array $id
     * @param bool $asArray
     * @return array|ProductsNotes|\yii\db\ActiveRecord|null
     */
    public function getById($id, bool $asArray = false)
    {
        $productsNotes = $this->findById($id, $asArray);
        if(!$productsNotes){
            throw new NotFoundException('Products Notes not found');
        }
        return $productsNotes;
    }

    /**
     * @param ProductsNotes $productsNotes
     * @param bool $validation
     * @return bool
     */
    public function save(ProductsNotes $productsNotes, bool $validation = false)
    {
        if (!$productsNotes->save($validation)) {
            throw new \RuntimeException('Products Notes saving  error.');
        }
        return true;
    }

    /**
     * @param ProductsNotes $productsNotes
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove(ProductsNotes $productsNotes)
    {
        if ($productsNotes->delete() === false) {
            throw new \RuntimeException('Products Notes remove error.');
        }
        return true;
    }

    /**
     * @param ProductsNotes $productsNotes
     * @param array $params
     * @param bool $validation
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(ProductsNotes $productsNotes, array $params = [], bool $validation = false, bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param){
            if(!$productsNotes->hasAttribute($attribute)){
                unset($params[$attribute]);
            }
        }
        $productsNotes->setAttributes($params, $safeOnly);
        if($productsNotes->update($validation, array_keys($params)) === false){
            return $productsNotes->getErrors();
        }
        return true;
    }

    /**
     * @param int $productId
     * @param bool $asArray
     * @return array|ProductsNotes[]
     */
    public function findByProductId(int $productId, bool $asArray = false)
    {
        $productsNotes = ProductsNotes::find()
            ->where(['products_id' => $productId])
            ->indexBy('products_notes_id');
        return $productsNotes->asArray($asArray)->all();
    }
}
