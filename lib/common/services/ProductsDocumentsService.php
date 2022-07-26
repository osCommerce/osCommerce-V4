<?php

namespace common\services;

use common\models\Product\ProductsDocuments;
use common\models\repositories\ProductsDocumentsRepository;

final class ProductsDocumentsService
{

    /** @var ProductsDocumentsRepository */
    private $productsDocumentsRepository;

    public function __construct(ProductsDocumentsRepository $productsNotesRepository)
    {
        $this->productsDocumentsRepository = $productsNotesRepository;
    }

    /**
     * @param ProductsDocuments $productsNotes
     * @return bool
     */
    public function save(ProductsDocuments $productsNotes)
    {
        return $this->productsDocumentsRepository->save($productsNotes);
    }

    /**
     * @param ProductsDocuments $productsNotes
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove(ProductsDocuments $productsNotes)
    {
        return $this->productsDocumentsRepository->remove($productsNotes);
    }

    /**
     * @param ProductsDocuments $productsNotes
     * @param array $params
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(ProductsDocuments $productsNotes, array $params = [], bool $safeOnly = false)
    {
        return $this->productsDocumentsRepository->edit($productsNotes, $params, $safeOnly);
    }

    /**
     * @param int $productId
     * @param bool $asArray
     * @return array|ProductsDocuments[]
     */
    public function findByProductId(int $productId, bool $asArray = false)
    {
        return $this->productsDocumentsRepository->findByProductId($productId, $asArray);
    }

    /**
     * @param int|array $id
     * @param bool $asArray
     * @return array|ProductsDocuments|\yii\db\ActiveRecord|null
     */
    public function getById($id, bool $asArray = false)
    {
        return $this->productsDocumentsRepository->getById($id, $asArray);
    }

    /**
     * @param string $filename
     * @param int $languageId
     * @param bool $isSuggest
     * @param int $limit
     * @param bool $asArray
     * @return array|ProductsDocuments[]|null
     */
    public function findByFileName(string $filename, int $languageId, bool $isSuggest = false, int $limit = 10, bool $asArray = false)
    {
        return $this->productsDocumentsRepository->findByFileName($filename, $languageId, $isSuggest, $limit, $asArray);
    }
}
