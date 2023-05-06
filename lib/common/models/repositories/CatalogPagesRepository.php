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
declare(strict_types = 1);

namespace common\models\repositories;

use common\models\CatalogPages;
use common\models\CatalogPagesDescription;
use common\models\CatalogPagesToInformation;
use common\models\Information;
use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * Class CatalogPagesRepository
 * @package common\models\repositories
 */
class CatalogPagesRepository
{
    /**
     * @param $id
     * @param string $sortField
     * @return array|CatalogPages|CatalogPages[]|null
     */
    public function findById($id, string $sortField = '')
    {
        $catalogPage = CatalogPages::find()->where(['catalog_pages_id' => $id]);
        if (is_array($id)) {
            if ($sortField !== '') {
                $catalogPage->orderBy($sortField);
            }
            return $catalogPage->indexBy('catalog_pages_id')->all();
        }

        return $catalogPage->limit(1)->one();
    }

    /**
     * @param $id
     * @param string $sortField
     * @return array|CatalogPages|CatalogPages[]|null
     */
    public function get($id, string $sortField = '')
    {
        if (!$catalogPage = $this->findById($id, $sortField)) {
            throw new NotFoundException('Catalog Page is not found.');
        }

        return $catalogPage;
    }

    /**
     * @return string
     */
    public function imagesLocation(): string
    {
        return 'catalog' . DIRECTORY_SEPARATOR;
    }

    /**
     * @param int $id
     * @param int|array $languageId
     * @return array|CatalogPagesDescription|CatalogPagesDescription[]|null
     */
    public function findDescription(int $id, $languageId)
    {
        $catalogPageDescription = CatalogPagesDescription::find()
            ->where([
                'AND',
                ['catalog_pages_id' => $id],
                ['languages_id' => $languageId],
            ]);
        if (is_array($languageId)) {
            return $catalogPageDescription->indexBy('catalog_pages_id')->all();
        }

        return $catalogPageDescription->limit(1)->one();
    }

    /**
     * @param int $id
     * @param $languageId
     * @return array|CatalogPagesDescription|CatalogPagesDescription[]|null
     */
    public function getDescription(int $id, $languageId)
    {
        if (!$catalogPageDescription = $this->findDescription($id, $languageId)) {
            throw new NotFoundException('Catalog Page Description is not found.');
        }

        return $catalogPageDescription;
    }

    /**
     * @param CatalogPages $catalogPage
     */
    public function save(CatalogPages $catalogPage)
    {
        if (!$catalogPage->save()) {
            throw new \RuntimeException('Catalog Page saving  error.');
        }
    }

    /**
     * @param CatalogPagesDescription $catalogPageDescription
     */
    public function saveDescription(CatalogPagesDescription $catalogPageDescription)
    {
        if (!$catalogPageDescription->save()) {
            throw new \RuntimeException('Catalog Page Description saving  error.');
        }
    }

    /**
     * @param CatalogPagesToInformation $catalogPagesToInformation
     */
    public function saveRelationInformation(CatalogPagesToInformation $catalogPagesToInformation)
    {
        if (!$catalogPagesToInformation->save()) {
            throw new \RuntimeException('Catalog Page Relation saving  error.');
        }
    }

    /**
     * @param CatalogPages $catalogPage
     * @return int
     */
    public function clearRelationInformation(CatalogPages $catalogPage): int
    {
        return CatalogPagesToInformation::deleteAll(['catalog_pages_id' => $catalogPage->catalog_pages_id]);
    }

    /**
     * @param CatalogPages $catalogPage
     *
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove(CatalogPages $catalogPage)
    {
        CatalogPagesToInformation::deleteAll(['catalog_pages_id' => $catalogPage->catalog_pages_id]);
        CatalogPagesDescription::deleteAll(['catalog_pages_id' => $catalogPage->catalog_pages_id]);
        if (!$catalogPage->delete()) {
            throw new \RuntimeException('Catalog Page remove error.');
        }
    }

    /**
     * @param CatalogPages $catalogPage
     * @param array $params
     * @param bool $safeOnly
     *
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(CatalogPages $catalogPage, array $params = [], bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param) {
            if (!$catalogPage->hasAttribute($attribute)) {
                unset($params[$attribute]);
            }
        }
        $catalogPage->setAttributes($params, $safeOnly);
        if ($catalogPage->update(false, array_keys($params)) === false) {
            return $catalogPage->getErrors();
        }

        return true;
    }

    /**
     * @param int $platformId
     * @param int $parentId
     * @param int $languageId
     * @param array|string|null $keyword
     * @param array|string|null $sort
     * @return ActiveQuery
     */
    public function getQueryByParams(int $platformId, int $parentId, int $languageId, $keyword = null, $sort = null): ActiveQuery
    {
        if ($sort === null) {
            $sort = 'cp.sort_order';
        }
        $query = CatalogPages::find()
            ->alias('cp')
            ->joinWith([
                'descriptionLanguageId cd' => static function (ActiveQuery $q) use ($languageId) {
                    return $q->onCondition(['cd.languages_id' => $languageId]);
                }
            ])
            ->where([
                'AND',
                ['cp.platform_id' => $platformId],
                ['cp.parent_id' => $parentId],
            ])
            ->andFilterWhere([
                'OR',
                ['like', 'cd.name', $keyword],
                ['like', 'cd.description_short', $keyword],
                ['like', 'cd.description', $keyword],
            ])
            ->orderBy($sort)
            ->groupBy('cp.catalog_pages_id');

        return $query;
    }

    /**
     * @param array|int $id
     * @param int $languageId
     * @param bool $full
     * @param bool $all
     * @param bool $asArray
     * @param bool $active
     *
     * @return array|CatalogPages|null|CatalogPages[]
     */
    public function getInfo($id, int $languageId, bool $full = true, bool $all = false, bool $asArray = false, bool $active = false)
    {
        $query = CatalogPages::find()
            ->alias('cp');
        if ($all) {
            $query->joinWith(['descriptions']);
        } else {
            $query->joinWith([
                'descriptionLanguageId cd' => static function (ActiveQuery $q1) use ($languageId) {
                    return $q1->onCondition(['cd.languages_id' => $languageId]);
                }
            ]);
        }
        if ($full) {
            $query->joinWith([
                'information i' => static function (ActiveQuery $q2) use ($languageId, $active) {
                    $q2->onCondition(['i.languages_id' => $languageId])
                        ->andOnCondition(['i.type' => Information::TYPE_CATALOG_PAGES])
                        ->groupBy('i.information_id');
                    if ($active) {
                        $q2->andOnCondition(['i.visible' => Information::STATUS_ACTIVE])
                            ->andOnCondition(['i.hide' => Information::STATUS_SHOW]);
                    }
                    return $q2;
                }
            ]);

        }
        $query->where(['cp.catalog_pages_id' => $id])->asArray($asArray);
        if (is_array($id)) {
            return $query->all();
        }
        return $query->limit(1)->one();
    }

    /**
     * @param int $id
     * @param int $languageId
     * @param bool|string|array $order
     * @param bool|int $limit
     * @return array|CatalogPages[]
     */
    public function getNestedCatalogPagesById(int $id, int $languageId = 1, $order = false, $limit = false): array
    {
        $catalogPage = CatalogPages::find()
            ->select(['lft', 'rgt'])
            ->where(['catalog_pages_id' => $id])
            ->limit(1)
            ->asArray()
            ->one();
        if (!$catalogPage) {
            return $catalogPage;
        }
        return $this->getNestedCatalogPages((int)$catalogPage['lft'], (int) $catalogPage['rgt'], $languageId, $order, $limit);
    }

    /**
     * @param int $left
     * @param int $right
     * @param int $languageId
     * @param bool|string|array $order
     * @param bool|int $limit
     * @return array|CatalogPages[]
     */
    public function getNestedCatalogPages(int $left, int $right, int $languageId = 1, $order = false, $limit = false): array
    {
        $catalogPage = CatalogPages::find()
            ->innerJoinWith(['descriptionLanguageId cd' => static function ($query) use ($languageId) {
                return $query->andWhere(['cd.languages_id' => $languageId]);
            }])
            ->where(['AND',
                ['>', 'lft', $left],
                ['<=', 'rgt', $right]
            ]);
        if ($order) {
            $catalogPage->orderBy($order);
        }
        if ($limit) {
            $catalogPage->limit($limit);
        }
        return $catalogPage->asArray()->all();
    }

    /**
     * @param int $id
     * @param int $languageId
     * @return array
     */
    public function getBreadcrumbsById(int $id, int $languageId = 1): array
    {
        return (new Query())
            ->select(['cd.name', 'cd.slug', 'ancestor.catalog_pages_id', 'ancestor.platform_id', 'ancestor.parent_id'])
            ->from('catalog_pages child')
            ->innerJoin('catalog_pages ancestor', 'child.lft >= ancestor.lft AND child.lft <= ancestor.rgt ')
            ->leftJoin('catalog_pages_description cd', 'ancestor.catalog_pages_id = cd.catalog_pages_id AND cd.languages_id=' . $languageId)
            ->where(['child.catalog_pages_id' => $id])
            ->orderBy('child.lft')
            ->all();
    }

    /**
     * @param int $languageId
     * @param int $platformId
     * @param bool $active
     * @return array
     */
    public function getAllNames(int $languageId, int $platformId, bool $active = true): array
    {
        $catalogPages = CatalogPages::find()
            ->alias('cp')
            ->select(['cp.*', 'cd.name'])
            ->joinWith([
                'descriptionLanguageId cd' => static function (ActiveQuery $q1) use ($languageId) {
                    return $q1->onCondition(['cd.languages_id' => $languageId]);
                }
            ], false)
            ->where(['cp.platform_id' => $platformId])
            ->orderBy('cp.lft');
        if ($active) {
            $catalogPages->active();
        }
        return $catalogPages->asArray()->all();
    }

    /**
     * @param int $informationId
     * @param int $platformId
     * @param bool $asArray
     * @return array|CatalogPages[]
     */
    public function findByInformationId(int $informationId, int $platformId, bool $asArray = false): array
    {
        $catalogPages = CatalogPages::find()
            ->alias('cp')
            ->innerJoinWith(['catalogPagesToInformations cptoi'], false)
            ->where([
                'cp.platform_id' => $platformId,
                'cptoi.information_id' => $informationId
            ])
            ->active();
        return $catalogPages->asArray($asArray)->all();
    }

    /**
     * @param Information $information
     * @return int
     */
    public function clearInformationRelation(Information $information): int
    {
        return CatalogPagesToInformation::deleteAll(['information_id' => $information->information_id]);
    }
}
