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

use common\models\Information;
use common\models\queries\InformationQuery;


/**
 * Class InformationRepository
 * @package common\models\repositories
 */
class InformationRepository
{
    /** @var AdminRepository */
    private $adminRepository;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * @param int|array $id
     * @param int $platformId
     * @param int $languageId
     * @param bool $isArray
     * @return array|Information|Information[]|null
     */
    public function findById($id, int $platformId, int $languageId, bool $isArray = false)
    {
        $information = Information::find()->where(['AND',
            ['information_id' => $id],
            ['platform_id' => $platformId],
            ['languages_id' => $languageId]
        ]);
        if (is_array($id)) {
            return $information->indexBy('platform_id')->orderBy('date_added')->asArray($isArray)->all();
        }
        return $information->limit(1)->one();
    }

    /**
     * @param int|array $id
     * @param int $platformId
     * @param int $languageId
     * @param bool $isArray
     * @return array|Information|Information[]|null
     */
    function get($id, int $platformId, int $languageId, bool $isArray = false)
    {
        if (!$information = $this->findById($id, $platformId, $languageId, $isArray)) {
            throw new NotFoundException('Information is not found.');
        }
        return $information;
    }

    /**
     * @param int $adminId
     * @return int
     */
    public function canViewHide(int $adminId): int
    {
        $hide = 1;
        $admin = $this->adminRepository->get($adminId);
        $email = explode('@', $admin->admin_email_address);
        if ($email[0] !== 'trueloaded' && $email[1] === 'holbi.co.uk') {
            $hide = 0;
        }
        return $hide;
    }

    /**
     * @param int $platformId
     * @param int $languageId
     * @param bool $active
     * @param bool $isArray
     * @param bool $isBlog
     * @param int $adminId
     * @return array|Information[]
     */
    public function getAllByPlatformAndLanguageInAdmin(int $platformId, int $languageId, bool $active = true, bool $isArray = false, bool $isBlog = false, int $adminId = 0): array
    {
        return $this->getAll($platformId, $languageId, $active, $isArray, $isBlog, true, $adminId);
    }

    /**
     * @param int $platformId
     * @param int $languageId
     * @param bool $active
     * @param bool $isArray
     * @param bool $isBlog
     * @return array|Information[]
     */
    public function getAllByPlatformAndLanguage(int $platformId, int $languageId, bool $active = true, bool $isArray = false, bool $isBlog = false): array
    {
        return $this->getAll($platformId, $languageId, $active, $isArray, $isBlog);
    }

    /**
     * @param int $platformId
     * @param int $languageId
     * @param string $term
     * @param bool $active
     * @param bool $isArray
     * @param bool $isBlog
     * @param int $adminId
     * @return InformationQuery
     */
    public function getAllByPlatformAndLanguageInAdminByTerm(int $platformId, int $languageId, string $term = '', bool $active = true, bool $isArray = false, bool $isBlog = false, int $adminId = 0): InformationQuery
    {
        return $this->getAllByTerm($platformId, $languageId, $term, $active, $isArray, $isBlog, true, $adminId);
    }

    /**
     * @param int $platformId
     * @param int $languageId
     * @param string $term
     * @param bool $active
     * @param bool $isArray
     * @param bool $isBlog
     * @return InformationQuery
     */
    public function getAllByPlatformAndLanguageByTerm(int $platformId, int $languageId, string $term = '', bool $active = true, bool $isArray = false, bool $isBlog = false): InformationQuery
    {
        return $this->getAllByTerm($platformId, $languageId, $term, $active, $isArray, $isBlog);
    }

    /**
     * @param Information $information
     */
    public function save(Information $information)
    {
        if (!$information->save()) {
            throw new \RuntimeException('Language saving  error.');
        }
    }

    /**
     * @param Information $information
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove(Information $information)
    {
        if (!$information->delete()) {
            throw new \RuntimeException('Language remove error.');
        }
    }

    /**
     * @param Information $information
     * @param array $params
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(Information $information, array $params = [], bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param) {
            if (!$information->hasAttribute($attribute)) {
                unset($params[$attribute]);
            }
        }
        $information->setAttributes($params, $safeOnly);
        if ($information->update(false, array_keys($params)) === false) {
            return $information->getErrors();
        }
        return true;
    }

    /**
     * @return string
     */
    public function imagesLocation(): string
    {
        return 'information' . DIRECTORY_SEPARATOR;
    }

    /**
     * @param int $platformId
     * @param int $languageId
     * @param bool $active
     * @param bool $isArray
     * @param bool $isBlog
     * @param bool $admin
     * @param int $adminId
     * @return array|Information[]
     */
    private function getAll(int $platformId, int $languageId, bool $active = true, bool $isArray = false, bool $isBlog = false, bool $admin = false, int $adminId = 0)
    {
        $information = Information::find()
            ->where(['AND',
                ['platform_id' => $platformId],
                ['languages_id' => $languageId],
            ]);
        if ($admin) {
            $information->hide($this->canViewHide($adminId));
        } else {
            $information->hide();
        }
        if ($isBlog) {
            $information->blog(Information::TYPE_CATALOG_PAGES);
        }
        if ($active) {
            $information->active();
        }

        return $information->orderBy('date_added')->asArray($isArray)->all();
    }

    /**
     * @param int $platformId
     * @param int $languageId
     * @param string $term
     * @param bool $active
     * @param bool $isArray
     * @param bool $isBlog
     * @param bool $admin
     * @param int $adminId
     * @return InformationQuery
     */
    private function getAllByTerm(int $platformId, int $languageId, string $term = '', bool $active = true, bool $isArray = false, bool $isBlog = false, bool $admin = false, int $adminId = 0): InformationQuery
    {
        $information = Information::find()
            ->where(['AND',
                ['platform_id' => $platformId],
                ['languages_id' => $languageId],
            ])
            ->andFilterWhere(['OR',
                ['LIKE', 'info_title', $term],
                ['LIKE', 'description', $term],
                ['LIKE', 'page', $term],
                ['LIKE', 'page_title', $term],
            ]);
        if ($admin) {
            $information->hide($this->canViewHide($adminId));
        } else {
            $information->hide();
        }
        if ($isBlog) {
            $information->blog(Information::TYPE_CATALOG_PAGES);
        }
        if ($active) {
            $information->active();
        }
        $information->orderBy('date_added')->asArray($isArray)->all();
        return $information;
    }

}
