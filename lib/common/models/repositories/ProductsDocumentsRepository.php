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
use common\models\Product\ProductsDocuments;


/**
 * Class ProductsDocumentsRepository
 * @package common\models\repositories
 */
final class ProductsDocumentsRepository
{
    /**
     * @param int|array $id
     * @param bool $asArray
     * @return array|ProductsDocuments|\yii\db\ActiveRecord|null
     */
    public function findById($id,  bool $asArray = false)
    {
        $productsDocuments = ProductsDocuments::find()
            ->where(['products_documents_id' => $id])->asArray($asArray);
        if (is_array($id)) {
            $productsDocuments->orderBy(['sort_order' => SORT_DESC])->all();
        }
        return $productsDocuments->limit(1)->one();
    }

    /**
     * @param int|array $id
     * @param bool $asArray
     * @return array|ProductsDocuments|\yii\db\ActiveRecord|null
     */
    public function getById($id, bool $asArray = false)
    {
        $productsDocuments = $this->findById($id, $asArray);
        if(!$productsDocuments){
            throw new NotFoundException('Products Notes not found');
        }
        return $productsDocuments;
    }

    /**
     * @param ProductsDocuments $productsDocuments
     * @return bool
     */
    public function save(ProductsDocuments $productsDocuments)
    {
        if (!$productsDocuments->save()) {
            throw new \RuntimeException('Products Notes saving  error.');
        }
        return true;
    }

    /**
     * @param ProductsDocuments $productsDocuments
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove(ProductsDocuments $productsDocuments)
    {
        if (!$productsDocuments->delete()) {
            throw new \RuntimeException('Products Notes remove error.');
        }
        return true;
    }

    /**
     * @param ProductsDocuments $productsDocuments
     * @param array $params
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(ProductsDocuments $productsDocuments, array $params = [], bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param){
            if(!$productsDocuments->hasAttribute($attribute)){
                unset($params[$attribute]);
            }
        }
        $productsDocuments->setAttributes($params, $safeOnly);
        if(!$productsDocuments->update(false, array_keys($params))){
            return $productsDocuments->getErrors();
        }
        return true;
    }

    /**
     * @param int $productId
     * @param bool $asArray
     * @return array|ProductsDocuments[]
     */
    public function findByProductId(int $productId, bool $asArray = false)
    {
        $productsDocuments = ProductsDocuments::find()
            ->where(['products_id' => $productId])
            ->orderBy(['sort_order' => SORT_DESC])
            ->indexBy('products_documents_id');
        return $productsDocuments->asArray($asArray)->all();
    }

    /**
     * @param string $filename
     * @param null|int $languageId
     * @param bool $isSuggest
     * @param int $limit
     * @param bool $asArray
     * @return array|ProductsDocuments[]|null
     */
    public function findByFileName(string $filename, ?int $languageId = null, bool $isSuggest = false, int $limit = 10, bool $asArray = false)
    {
        $productsDocuments = ProductsDocuments::find()
            ->alias('pd')
            ->orderBy(['sort_order' => SORT_DESC])
            ->indexBy('products_documents_id');
        if ($languageId > 0) {
            $productsDocuments->withDescription($languageId);
        }
        if ($isSuggest) {
            $suggests = explode(' ', $filename);
            foreach ($suggests as $suggest) {
                $productsDocuments->orFilterWhere(['LIKE', 'filename', $suggest]);
                if ($languageId > 0) {
                    $productsDocuments->orFilterWhere(['LIKE', 'pdt.title', $suggest]);
                }
            }
        } else {
            $productsDocuments->where(['filename' => $filename]);
        }
        return $productsDocuments->asArray($asArray)->limit($limit)->all();
    }
}
