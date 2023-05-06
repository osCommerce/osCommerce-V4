<?php


namespace frontend\models\repositories;


use common\models\CatalogPages;
use common\models\Information;
use common\models\repositories\CatalogPagesRepository;
use yii\db\ActiveQuery;
use yii\db\Query;

class CatalogPagesReadRepository
{
    /** @var CatalogPagesRepository  */
    private $catalogPagesRepository;

    public function __construct(CatalogPagesRepository $catalogPagesRepository, array $config = [])
    {
        $this->catalogPagesRepository = $catalogPagesRepository;
    }

    /**
     * @param array|int $id
     * @param int $languageId
     * @param bool $asArray
     * @return array|CatalogPages|CatalogPages[]|null
     */
    public function getShortInfo($id, int $languageId, bool $asArray = false)
    {
        return $this->catalogPagesRepository->getInfo($id, $languageId, false, $asArray);
    }

    /**
     * @param array|int $id
     * @param int $languageId
     * @param bool $asArray
     * @return array|CatalogPages|CatalogPages[]|null
     */
    public function getFullInfo($id, int $languageId, bool $asArray = false)
    {
        return $this->catalogPagesRepository->getInfo($id, $languageId, true, false, $asArray, true);
    }

    public function getInfoBySlug(string $slug, int $languageId, int $platformId, bool $full = false, bool $isArray = false)
    {
        $catalogPages = CatalogPages::find()
            ->alias('cp')
            ->joinWith([
                'descriptionLanguageId cd' => static function (ActiveQuery $q1) use ($languageId) {
                    return $q1->onCondition(['cd.languages_id' => $languageId]);
                }
            ]);
        if($full){
            $catalogPages->joinWith([
                'information i' => static function (ActiveQuery $q2) use ($languageId) {
                    $q2->onCondition(['i.languages_id' => $languageId])
                        ->andOnCondition(['i.visible' => Information::STATUS_ACTIVE])
                        ->andOnCondition(['i.hide' => Information::STATUS_SHOW])
                        ->andOnCondition(['i.type' => Information::TYPE_CATALOG_PAGES])
                        ->groupBy('i.information_id');
                    return $q2;
                }
            ]);
        }
        $catalogPages->where([
                'AND',
                ['cd.slug' => $slug],
                ['cp.platform_id' => $platformId],
            ])
            ->active()
            ->asArray($isArray);
        return $catalogPages->one();
    }

    /**
     * @param int|array $id
     * @param int $languageId
     * @param bool $asArray
     * @return array|CatalogPages|CatalogPages[]|null
     */
    public function getFullInfoList($id, int $languageId = 1, bool $asArray = false)
    {
        return $this->catalogPagesRepository->getInfo($id, $languageId, true, false, $asArray, true);
    }

    /**
     * @return string
     */
    public function imagesLocation(): string
    {
        return $this->catalogPagesRepository->imagesLocation();
    }

    /**
     * @param int $id
     * @param int $languageId
     * @return array
     */
    public function getBreadcrumbsById(int $id, int $languageId = 1): array
    {
        return $this->catalogPagesRepository->getBreadcrumbsById($id, $languageId);
    }


    /**
     * @param $id
     * @param int $languageId
     * @param int $platformId
     * @param bool $asArray
     * @return array|CatalogPages[]
     */
    public function getCatalog($id, int $languageId, int $platformId, bool $asArray = false)
    {
        return CatalogPages::find()
            ->alias('cp')
            ->joinWith([
                'descriptionLanguageId cd' => static function (ActiveQuery $q1) use ($languageId) {
                    return $q1->onCondition(['cd.languages_id' => $languageId]);
                }
            ])
            ->where([
                'AND',
                ['cp.parent_id' => $id],
                ['cp.platform_id' => $platformId],
            ])
            ->active()
            ->asArray($asArray)
            ->all();
    }

    /**
     * @param int|array $id
     * @return array|CatalogPages|CatalogPages[]|null
     */
    public function findById($id) {
        return $this->catalogPagesRepository->findById($id);
    }

    /**
     * @param int $languageId
     * @param int $platformId
     * @return array
     */
    public function getRootsSlugs(int $languageId, int $platformId): array
    {
        return CatalogPages::find()
            ->alias('cp')
            ->select('cd.slug')
            ->joinWith([
                'descriptionLanguageId cd' => static function (ActiveQuery $q1) use ($languageId) {
                    return $q1->onCondition(['cd.languages_id' => $languageId]);
                }
            ], false)
            ->where([
                'AND',
                ['cp.parent_id' => 0],
                ['cp.platform_id' => $platformId],
            ])
            ->active()
            ->asArray()
            ->column();
    }

    /**
     * @param string $slug
     * @param int $languageId
     * @param int $platformId
     * @return string
     */
    public function getRootSlugBySlug(string $slug, int $languageId, int $platformId)
    {
        $page =  (new Query())
            ->select('cdp.catalog_pages_id')
            ->from('catalog_pages_description cdp')
            ->where([
                'cdp.languages_id' => $languageId,
                'cdp.slug' => $slug
            ]);
        return (string)(new Query())
            ->select('cd.slug')
            ->from('catalog_pages node')
            ->innerJoin('catalog_pages parent', 'node.lft >= parent.lft AND node.rgt <= parent.rgt AND parent.lvl = 0')
            ->leftJoin('catalog_pages_description cd', "cd.languages_id = {$languageId} AND parent.catalog_pages_id = cd.catalog_pages_id")
            ->where([
                'node.catalog_pages_id' => $page,
                'node.platform_id' => $platformId,
                'parent.platform_id' => $platformId,
            ])
            ->limit(1)
            ->column()[0];
    }
}
