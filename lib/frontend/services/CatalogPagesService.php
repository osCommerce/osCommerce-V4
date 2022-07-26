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


namespace frontend\services;


use frontend\models\repositories\CatalogPagesReadRepository;
use \yii\web\Request;

class CatalogPagesService
{
    const DEFAULT_ROUTS = [
        'catalog-pages',
        'catalog-pages/index',
        'catalog-pages/post'
    ];
    const DEFAULT_INDEX_ROUTS = [
        'catalog-pages',
        'catalog-pages/index',
    ];
    const DEFAULT_POST_ROUT = 'catalog-pages/post';

    /** @var CatalogPagesReadRepository */
    private $catalogPagesRepository;

    public function __construct(
        CatalogPagesReadRepository $catalogPagesRepository
    )
    {
        $this->catalogPagesRepository = $catalogPagesRepository;
    }

    /**
     * @param Request $request
     * @param int $languageId
     * @param int $platformId
     * @return array|bool
     * @throws \yii\base\InvalidConfigException
     */
    public function parseRequest(Request $request, int $languageId, int $platformId)
    {
        $params = $request->getQueryParams();
        $pathInfo = trim($request->getPathInfo());
        if (substr($pathInfo, -1) !== '/') {
            $pathInfo .= '/';
        }
        $path = explode('/', $pathInfo);
        if (!$this->checkUrlPrefix($path, $languageId, $platformId)) {
            return false;
        }
        $seoUrl = $this->spliceSlugFromPath($path);
        if ($seoUrl === '') {
            return ['catalog-pages/index', []];
        }
        return ['catalog-pages/post', ['page' => $seoUrl]];
    }

    /**
     * @return string
     */
    public function getUrlPrefix(): string
    {
        return trim(CATALOG_PAGES_PREFIX_URL, '/');
    }

    /**
     * @param array $path
     * @param int $languageId
     * @param int $platformId
     * @return bool
     */
    public function checkUrlPrefix(array $path, int $languageId, int $platformId): bool
    {
        $prefix = explode('/', trim($this->getUrlPrefix()));
        if ($prefix[0] === '') {
            $roots = $this->catalogPagesRepository->getRootsSlugs($languageId, $platformId);
            return in_array($path[0], $roots, true);
        }
        foreach ($prefix as $key => $subPath) {
            if ($path[$key] !== $subPath) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $path
     * @return string
     */
    public function spliceSlugFromPath(array $path): string
    {
        $prefix = explode('/', trim($this->getUrlPrefix()));
        if ($prefix[0] === '') {
            if ($path[1] === '') {
                return $path[0];
            }
            return $path[count($path)-2];
        }
        $prefix = explode('/', trim($this->getUrlPrefix()));
        return array_slice($path, count($prefix))[0];
    }

    /**
     * @param string $route
     * @return bool
     */
    public function isRoute(string $route): bool
    {
        return in_array($route, self::DEFAULT_ROUTS, true);
    }

    /**
     * @param string $route
     * @param array $params
     * @param int $languageId
     * @param int $platformId
     * @return string
     */
    public function createUrl(string $route, array $params, int $languageId, int $platformId): string
    {
        if ($this->getUrlPrefix() === '') {
            if (in_array($route, self::DEFAULT_INDEX_ROUTS, true)) {
                throw new \RuntimeException("Catalog Pages route '{$route}' no allowed");
            }
            $seoUrl = $params['page'];
            $root = $this->catalogPagesRepository->getRootSlugBySlug($seoUrl, $languageId, $platformId);
            unset($params['page']);
            $seoUrl = $root === $seoUrl ? $seoUrl : "{$root}\\{$seoUrl}";
            return sprintf('%s/%s%s', $this->getUrlPrefix(), $seoUrl, count($params) > 0 ? '?' .  http_build_query($params) : '');
        }
        if (in_array($route, self::DEFAULT_INDEX_ROUTS, true)) {
            return sprintf('%s%s', $this->getUrlPrefix(),count($params) > 0 ? '?' .  http_build_query($params) : '');
        }
        if ($route === self::DEFAULT_POST_ROUT) {
            $seoUrl = $params['page'];
            unset($params['page']);
            return sprintf('%s/%s%s', $this->getUrlPrefix(), $seoUrl, count($params) > 0 ? '?' .  http_build_query($params) : '');
        }
        throw new \RuntimeException("Catalog Pages route '{$route}' no matches allows routs");
    }

    public function getCatalog($id, int $languageId, int $platformId, bool $asArray = false)
    {
        return $this->catalogPagesRepository->getCatalog($id, $languageId, $platformId, $asArray);
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
     * @param bool $asArray
     * @return array|\common\models\CatalogPages|\common\models\CatalogPages[]|null
     */
    public function getFullInfoList($id, int $languageId = 1, bool $asArray = false)
    {
        return $this->catalogPagesRepository->getFullInfoList($id, $languageId, $asArray);
    }

    /**
     * @return string
     */
    public function imagesLocation(): string
    {
        return $this->catalogPagesRepository->imagesLocation();
    }

    /**
     * @param string $slug
     * @param int $languageId
     * @param int $platformId
     * @param bool $full
     * @param bool $isArray
     * @return array|\common\models\CatalogPages|null
     */
    public function getInfoBySlug(string $slug, int $languageId, int $platformId, bool $full = false, bool $isArray = false)
    {
        return $this->catalogPagesRepository->getInfoBySlug($slug, $languageId, $platformId, $full, $isArray);
    }
}
