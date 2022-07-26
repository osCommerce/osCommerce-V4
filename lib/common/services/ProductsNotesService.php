<?php

namespace common\services;

use backend\models\forms\ProductsNotesForm;
use common\models\Product\ProductsNotes;
use common\models\repositories\ProductsNotesRepository;

final class ProductsNotesService
{

    /** @var ProductsNotesRepository */
    private $productsNotesRepository;

    public function __construct(ProductsNotesRepository $productsNotesRepository)
    {
        $this->productsNotesRepository = $productsNotesRepository;
    }

    /**
     * @param ProductsNotes $productsNotes
     * @return bool
     */
    public function save(ProductsNotes $productsNotes)
    {
        return $this->productsNotesRepository->save($productsNotes);
    }

    /**
     * @param ProductsNotes $productsNotes
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove(ProductsNotes $productsNotes)
    {
        return $this->productsNotesRepository->remove($productsNotes);
    }

    /**
     * @param ProductsNotes $productsNotes
     * @param array $params
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(ProductsNotes $productsNotes, array $params = [], bool $safeOnly = false)
    {
        return $this->productsNotesRepository->edit($productsNotes, $params, $safeOnly);
    }

    /**
     * @param int $productId
     * @param bool $asArray
     * @return array|ProductsNotes[]
     */
    public function findByProductId(int $productId, bool $asArray = false)
    {
        return $this->productsNotesRepository->findByProductId($productId, $asArray);
    }

    /**
     * @param int|array $id
     * @param bool $asArray
     * @return array|ProductsNotes|\yii\db\ActiveRecord|null
     */
    public function getById($id, bool $asArray = false)
    {
        return $this->productsNotesRepository->getById($id, $asArray);
    }

    /**
     * @param int $productId
     * @param string $note
     * @return ProductsNotes
     */
    public function newProductNote(int $productId, string $note = '')
    {
        return $this->newProductsNoteByArray([
            'products_id' => $productId,
            'note' => $note,
        ]);
    }

    /**
     * @param array $data
     * @return ProductsNotes
     */
    public function newProductsNoteByArray(array $data = [])
    {
        return new ProductsNotes($data);
    }

    /**
     * @param array $data
     * @return ProductsNotesForm
     */
    public function newProductsNoteFormByArray(array $data = [])
    {
        return new ProductsNotesForm($data);
    }
}
