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
declare(strict_types=1);

namespace frontend\models\repositories;


use common\models\Information;
use common\models\queries\InformationQuery;
use common\models\repositories\InformationRepository;
use common\classes\Images;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;


class InformationReadRepository
{
    /** @var InformationRepository */
    private $informationRepository;

    public function __construct(InformationRepository $informationRepository)
    {
        $this->informationRepository = $informationRepository;
    }

    /**
     * @return string
     */
    public function imagesLocation(): string
    {
        return $this->informationRepository->imagesLocation();
    }

    /**
     * @param array|int $id
     * @param int $platformId
     * @param int $languages_id
     * @return array|bool
     */
    public function imagesData($id, int $platformId, int $languages_id)
    {
        if (($platformId < 1 || $languages_id < 1) || ((is_array($id) && count($id) < 1) || (int)$id < 1)) {
            return false;
        }
        $information = $this->informationRepository->get($id, $platformId, $languages_id, true);
        $baseImageUrl = Images::getWSCatalogImagesPath();
        if (is_array($information)) {
            $response = [];
            foreach ($information as $key => $info) {
                $response[$key] = [
                    'title' => $information->page_title,
                    'id' => $information->information_id
                ];
                if (empty($information['image'])) {
                    $response[$key]['imageUrl'] = false;
                } else {
                    $response[$key]['imageUrl'] = $baseImageUrl . $information['image'];
                }
            }
        } elseif (is_object($information)) {
            if (empty($information->image)) {
                return false;
            }
            return ['imageUrl' => $baseImageUrl . $information->image, 'title' => $information->page_title, 'id' => $information->information_id];
        }
        return false;
    }

    /**
     * @param array|int $id
     * @param int $platformId
     * @param int $languages_id
     * @param bool $isArray
     * @return array|Information|Information[]|null
     */
    public function findById($id, int $platformId, int $languages_id, bool $isArray = false)
    {
        return $this->informationRepository->findById($id, $platformId, $languages_id, $isArray);
    }

    /**
     * @param int $languageId
     * @param int $platformId
     * @param bool $limit
     * @param bool $isBlog
     * @param int $id
     * @param bool $isArray
     * @return array|Information[]
     */
    public function getLastList(int $languageId, int $platformId, $limit = false, bool $isBlog = false, int $id = 0, bool $isArray = false)
    {
        $information = Information::find()->alias('i');
        $information->select(['i.*', 'catalog_pages_description.name']);
        if ($isBlog) {
            $information->blog(Information::TYPE_CATALOG_PAGES);
        }
        $information->joinWith(['catalogPages.descriptions' => function (ActiveQuery $query) use ($id, $languageId) {
            if ($id > 0) {
                $query->andWhere(['catalog_pages.catalog_pages_id' => $id]);
            }
            return $query->andWhere(['catalog_pages_description.languages_id' => $languageId]);

        }], false);
        $information->andWhere(['AND',
            ['i.languages_id' => $languageId],
            ['i.platform_id' => $platformId],
        ])
            ->orderBy(['date_added' => SORT_DESC])
            ->groupBy('i.information_id')
            ->limit($limit)
            ->asArray($isArray);
        return $information->all();
    }

    /**
     * @param int $id
     * @param int $languageId
     * @param int $platformId
     * @param bool $limit
     * @param bool $offset
     * @param bool $isBlog
     * @param bool $isArray
     * @return array|Information[]
     */
    public function getListByCategory(int $id, int $languageId, int $platformId, $limit = false, $offset = false, bool $isBlog = false, bool $isArray = false)
    {
        $information = $this->getListByCategoryQuery($id, $languageId, $platformId, $limit, $offset, $isBlog, $isArray);
        return $information->all();
    }

    /**
     * @param int $id
     * @param int $languageId
     * @param int $platformId
     * @param bool $limit
     * @param bool $offset
     * @param bool $isBlog
     * @param bool $isArray
     * @return InformationQuery
     */
    public function getListByCategoryQuery(int $id, int $languageId, int $platformId, $limit = false, $offset = false, bool $isBlog = false, bool $isArray = false): InformationQuery
    {
        $information = Information::find()->alias('i');
        if ($isBlog) {
            $information->blog(Information::TYPE_CATALOG_PAGES);
        }
        $information->innerJoinWith(['catalogPagesToInformations cpti' => function (ActiveQuery $query) use ($id, $languageId) {
            return $query->onCondition(['cpti.catalog_pages_id' => $id]);

        }], false);
        $information->andWhere(['AND',
            ['i.languages_id' => $languageId],
            ['i.platform_id' => $platformId],
        ]);
        if ($isBlog) {
            $information->blog(Information::TYPE_CATALOG_PAGES);
        }
        $information->orderBy(['date_added' => SORT_DESC])
            ->groupBy('i.information_id')
            ->offset($offset)
            ->limit($limit)
            ->asArray($isArray);
        return $information;
    }

    /**
     * @param int $id
     * @param int $languageId
     * @param int $platformId
     * @param bool $limit
     * @param bool $offset
     * @param bool $isBlog
     * @param bool $isArray
     * @return ActiveDataProvider
     */
    public function getInformationProvider(int $id, int $languageId, int $platformId, $limit = false, $offset = false, bool $isBlog = false, bool $isArray = false)
    {
        $information = $this->getListByCategoryQuery($id, $languageId, $platformId, $limit, $offset, $isBlog, $isArray);
        return new ActiveDataProvider([
            'query' => $information,
            'pagination' => [
                'defaultPageSize' => 15,
                'pageParam' => 'pageNum',
                'forcePageParam' => false,
                'pageSizeParam' => false,
            ],
            'sort' => [
                'defaultOrder' => [
                    'date_added' => SORT_ASC,
                ]
            ],
        ]);
    }
}
