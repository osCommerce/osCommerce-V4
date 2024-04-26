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

}
