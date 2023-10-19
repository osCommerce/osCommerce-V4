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

namespace backend\services;

use backend\models\forms\catalogpages\CatalogsPageContainerForm;
use common\models\CatalogPages;
use common\models\CatalogPagesDescription;
use common\models\CatalogPagesToInformation;
use common\models\Information;
use common\models\repositories\CatalogPagesRepository;
use common\models\repositories\InformationRepository;
use common\models\repositories\NotFoundException;
use common\services\TransactionManager;
use yii\db\ActiveQuery;
use common\classes\Images;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use Yii;

/**
 * Class CatalogPagesService
 * @package backend\services
 */
class CatalogPagesService
{
    /** @var CatalogPagesRepository  */
    protected $catalogPagesRepository;
    /** @var TransactionManager  */
    protected $transaction;
    /** @var InformationRepository */
    private $informationRepository;

    /**
     * CatalogPagesService constructor.
     * @param CatalogPagesRepository $catalogPagesRepository
     * @param InformationRepository $informationRepository
     * @param TransactionManager $transaction
     */
    public function __construct(
        CatalogPagesRepository $catalogPagesRepository,
        InformationRepository $informationRepository,
        TransactionManager $transaction
    )
    {
        $this->catalogPagesRepository = $catalogPagesRepository;
        $this->transaction = $transaction;
        $this->informationRepository = $informationRepository;
    }

    /**
     * @param int $platformId
     * @param int $parentId
     * @param int $languageId
     * @param null $keyword
     * @param null $sort
     * @return ActiveQuery
     */
    public function getQueryByParams(int $platformId, int $parentId, int $languageId, $keyword = null, $sort = null): ActiveQuery
    {
        return $this->catalogPagesRepository->getQueryByParams($platformId, $parentId, $languageId, $keyword, $sort);
    }

    /**
     * @param CatalogPages $catalogPage
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function setActive(CatalogPages $catalogPage): bool
    {
        if (!is_object($catalogPage)) {
            throw new \RuntimeException('Catalog Page error data.');
        }
        if ($catalogPage->status) {
            return true;
        }
        return $this->catalogPagesRepository->edit($catalogPage, ['status' => CatalogPages::STATUS_ACTIVE]);
    }

    /**
     * @param CatalogPages $catalogPage
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function setDisable(CatalogPages $catalogPage): bool
    {
        if (!is_object($catalogPage)) {
            throw new \RuntimeException('Catalog Page error data.');
        }
        if (!$catalogPage->status) {
            return true;
        }
        return $this->catalogPagesRepository->edit($catalogPage, ['status' => CatalogPages::STATUS_DISABLE]);
    }

    /**
     * @param $id
     * @param string $sortField
     * @return array|CatalogPages|CatalogPages[]|null
     */
    public function getById($id, $sortField = '')
    {
        return $this->catalogPagesRepository->get($id, $sortField);
    }

    /**
     * @param array|int $id
     * @param int $languageId
     * @param bool $asArray
     * @return array|CatalogPages|CatalogPages[]|null
     */
    public function getShortInfo($id, int $languageId, bool $asArray = false)
    {
        $catalogPage = $this->catalogPagesRepository->getInfo($id, $languageId, false, $asArray);
        if (empty($catalogPage)) {
            throw new NotFoundException('Catalog Page not found.');
        }
        return $catalogPage;
    }

    /**
     * @return string
     */
    public function imagesLocation(): string
    {
        return $this->catalogPagesRepository->imagesLocation();
    }

    /**
     * @param $id
     * @param int $languageId
     * @param bool $asArray
     * @return array|CatalogPages|null
     */
    public function getFullInfo($id, int $languageId = 0, bool $asArray = false)
    {
        $catalogPage = $this->catalogPagesRepository->getInfo($id, $languageId, true, false, $asArray);
        if (empty($catalogPage)) {
            throw new NotFoundException('Catalog Page not found.');
        }
        return $catalogPage;
    }

    /**
     * @param $id
     * @param int $languageId
     * @param bool $asArray
     * @return array|CatalogPages|null|\yii\db\ActiveRecord
     */
    public function getFullInfoAll($id, int $languageId = 0, bool $asArray = false)
    {
        $catalogPage = $this->catalogPagesRepository->getInfo($id, $languageId, true, true, $asArray);
        if (empty($catalogPage)) {
            throw new NotFoundException('Catalog Page not found.');
        }
        return $catalogPage;
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function delete($id)
    {
        $catalogPage = $this->getById($id);
        $this->deleteByEntity($catalogPage);
    }

    /**
     * @param CatalogPages $catalogPage
     * @throws \Exception
     */
    public function deleteByEntity(CatalogPages $catalogPage)
    {
        $this->transaction->wrap(function () use ($catalogPage) {
            $this->catalogPagesRepository->remove($catalogPage);
        });

    }

    /**
     * @param CatalogPages $catalogPage
     * @param $order
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function changeSortOrder(CatalogPages $catalogPage, $order)
    {
        return $this->catalogPagesRepository->edit($catalogPage, ['sort_order' => $order]);
    }

    /**
     * @param ActiveQuery $categoryPagesQuery
     * @param array $sort
     * @throws \Exception
     */
    public function sortBySourceQuery(ActiveQuery $categoryPagesQuery, array $sort)
    {
        $this->transaction->wrap(function () use ($categoryPagesQuery, $sort) {
            $counter = 0;
            foreach ($categoryPagesQuery->batch() as $dataSort) {
                foreach ($dataSort as $data) {
                    $this->changeSortOrder($data, $counter);
                    $counter++;
                }
            }
            $dataSortOffset = $this->catalogPagesRepository->findById($sort, 'sort_order');
            $ref_so = [];
            foreach ($dataSortOffset as $data) {
                $ref_so[] = $data->sort_order;
            }
            foreach ($sort as $_idx => $id) {
                $this->changeSortOrder($dataSortOffset[$id], (int)$ref_so[$_idx]);
            }
        });
    }

    /**
     * @param string $string
     * @return string
     */
    public function slugify(string $string): string
    {
        $string = \transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $string);
        $string = preg_replace('/[-\s]+/', '-', $string);
        return trim($string, '-');
    }

    /**
     * @param string $path
     * @return array|bool
     */
    public function pathinfo(string $path)
    {
        if (strpos($path, '/') !== false)
            $basename = end(explode('/', $path));
        elseif (strpos($path, '\\') !== false)
            $basename = end(explode('\\', $path));
        else
            return false;
        if (!$basename)
            return false;
        $dirname = substr($path, 0,
            strlen($path) - strlen($basename) - 1);
        if (strpos($basename, '.') !== false) {
            $extension = end(explode('.', $path));
            $filename = substr($basename, 0,
                strlen($basename) - strlen($extension) - 1);
        } else {
            $extension = '';
            $filename = $basename;
        }
        return array(
            'dirname' => $dirname,
            'basename' => $basename,
            'extension' => $extension,
            'filename' => $filename
        );
    }

    /**
     * @param string $originalFileName
     * @param string $newFileName
     * @param bool $copy
     * @return string
     * @throws \yii\base\Exception
     */
    public function moveFile(string $originalFileName, string $newFileName, bool $copy = false):string
    {
        $pathParts = $this->pathinfo($newFileName);
        if (is_dir($originalFileName) || is_dir($newFileName)) {
            throw new NotFoundException('File not found.');
        }
        if (!is_dir($pathParts['dirname'])) {
            FileHelper::createDirectory($pathParts['dirname'], 0777, true);
        }
        $name = $this->slugify($pathParts['filename']);
        $ext = '';
        if (isset($pathParts['extension'])) {
            $ext = '.' . $pathParts['extension'];
        }
        $tmpName = $name . $ext;
        $i = 1;
        while (is_file($pathParts['dirname'] . DIRECTORY_SEPARATOR . $tmpName)) {
            $tmpName = $name . '-' . $i . $ext;
            $i++;
        }
        if ($copy) {
            @copy($originalFileName, $pathParts['dirname'] . DIRECTORY_SEPARATOR . $tmpName);
        } else {
            @rename($originalFileName, $pathParts['dirname'] . DIRECTORY_SEPARATOR . $tmpName);
        }
        return $tmpName;
    }

    /**
     * @param int $id
     * @param CatalogsPageContainerForm $catalogPageForm
     * @param int $languageId
     * @return int
     * @throws \Exception
     */
    public function saveByCatalogPageForm(int $id, CatalogsPageContainerForm $catalogPageForm, int $languageId): int
    {
        $this->transaction->wrap(function () use (&$id, $catalogPageForm, $languageId) {
            $created_at = null;
            if (!empty($catalogPageForm->catalogPageForm->created_at)) {
                $created_at = strtotime($catalogPageForm->catalogPageForm->created_at);
            }
            if ((int)$id < 1) {
                if ($catalogPageForm->catalogPageForm->image || $catalogPageForm->catalogPageForm->imageGallery) {
                    if ($catalogPageForm->catalogPageForm->image) {
                        $catalogPageForm->catalogPageForm->image = $this->moveFile(Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $catalogPageForm->catalogPageForm->image, Images::getFSCatalogImagesPath() . $this->imagesLocation() . $catalogPageForm->catalogPageForm->image);
                    }
                    if ($catalogPageForm->catalogPageForm->imageGallery) {
                        $catalogPageForm->catalogPageForm->image = $this->moveFile(Yii::getAlias('@webroot') . '/../' . $catalogPageForm->catalogPageForm->imageGallery, Images::getFSCatalogImagesPath() . $this->imagesLocation() . $this->basename($catalogPageForm->catalogPageForm->imageGallery), true);
                    }
                }
                $catalogPage = CatalogPages::create(
                    $catalogPageForm->catalogPageForm->platform_id,
                    $catalogPageForm->catalogPageForm->parent_id,
                    $catalogPageForm->catalogPageForm->image,
                    $catalogPageForm->catalogPageForm->status,
                    $created_at);
                $this->catalogPagesRepository->save($catalogPage);
                $defaultData = $this->findDefaultNameAndSlug($catalogPageForm->catalogPageDescriptionForm, $languageId);
                foreach ($catalogPageForm->catalogPageDescriptionForm as $description) {
                    $name = $description->name;
                    if (empty($name)) {
                        $name = $defaultData['defaultName'];
                    }
                    $slug = $this->slugify($description->slug);
                    if (empty($slug)) {
                        $slug = $defaultData['defaultSlug'];
                    }
                    $catalogPageDescription = CatalogPagesDescription::create($catalogPage->catalog_pages_id, $description->languages_id, $name, $description->description_short, $description->description, $slug, $description->meta_title, $description->meta_description, $description->meta_keyword, $description->h1_tag, $description->h2_tag, $description->h3_tag);
                    $this->catalogPagesRepository->saveDescription($catalogPageDescription);
                }
            } else {
                $catalogPage = $this->catalogPagesRepository->get($id);
                if ($catalogPageForm->catalogPageForm->image_delete && is_file(Images::getFSCatalogImagesPath() . $this->imagesLocation() . $catalogPage->image)) {
                    @unlink(Images::getFSCatalogImagesPath() . $this->imagesLocation() . $catalogPage->image);
                    $catalogPageForm->catalogPageForm->image = '';
                } elseif ($catalogPageForm->catalogPageForm->image && $catalogPageForm->catalogPageForm->imageGallery) {
                    @unlink(Images::getFSCatalogImagesPath() . $this->imagesLocation() . $catalogPage->image);
                }
                if ($catalogPageForm->catalogPageForm->image) {
                    $catalogPageForm->catalogPageForm->image = $this->moveFile(Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $catalogPageForm->catalogPageForm->image, Images::getFSCatalogImagesPath() . $this->imagesLocation() . $catalogPageForm->catalogPageForm->image);
                }
                if (!$catalogPageForm->catalogPageForm->image && $catalogPageForm->catalogPageForm->imageGallery) {
                    $catalogPageForm->catalogPageForm->image = $this->moveFile(Yii::getAlias('@webroot') . '/../' . $catalogPageForm->catalogPageForm->imageGallery, Images::getFSCatalogImagesPath() . $this->imagesLocation() . $this->basename($catalogPageForm->catalogPageForm->imageGallery), true);
                }
                $catalogPage->edit($catalogPageForm->catalogPageForm->platform_id, $catalogPageForm->catalogPageForm->parent_id, $catalogPageForm->catalogPageForm->image, $catalogPageForm->catalogPageForm->status, $created_at);
                $this->catalogPagesRepository->save($catalogPage);
                $defaultData = $this->findDefaultNameAndSlug($catalogPageForm->catalogPageDescriptionForm, $languageId);

                foreach ($catalogPageForm->catalogPageDescriptionForm as $description) {
                    $name = $description->name;
                    if (empty($name)) {
                        $name = $defaultData['defaultName'];
                    }
                    try {
                        $catalogPageDescription = $this->catalogPagesRepository->getDescription($catalogPage->catalog_pages_id, $description->languages_id);
                    } catch (NotFoundException $exception) {
                        $catalogPageDescription = CatalogPagesDescription::create($catalogPage->catalog_pages_id, $description->languages_id);
                    }
                    $slug = $this->slugify($description->slug);
                    if (empty($slug)) {
                        $slug = $defaultData['defaultSlug'];
                    }
                    $catalogPageDescription->edit(null, null, $name, $description->description_short, $description->description, $slug, $description->meta_title, $description->meta_description, $description->meta_keyword, $description->h1_tag, $description->h2_tag, $description->h3_tag);
                    $this->catalogPagesRepository->saveDescription($catalogPageDescription);
                }
            }
            $this->clearRelationInformation($catalogPage);
            foreach ($catalogPageForm->assignInformationForm as $assign) {
                $this->addInformationByInfoId($catalogPage, (int)$assign->information_id);
            }
            $id = $catalogPage->catalog_pages_id;
        });
        return $id;
    }

    /**
     * @param CatalogPages $catalogPage
     * @return int
     */
    public function clearRelationInformation(CatalogPages $catalogPage): int
    {
        return $this->catalogPagesRepository->clearRelationInformation($catalogPage);
    }

    /**
     * @param int $id
     * @param int $languageId
     * @param bool $limit
     * @return array|CatalogPages[]
     */
    public function getNestedCatalogPagesById(int $id, int $languageId = 1, bool $limit = false)
    {
        return $this->catalogPagesRepository->getNestedCatalogPagesById($id, $languageId, $limit);
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
     * @param CatalogPages $catalogPage
     * @param Information $information
     */
    public function assignInformation(CatalogPages $catalogPage, Information $information)
    {
        $catalogPage->link('information', $information);
    }

    /**
     * @param Information $information
     * @return int
     */
    public function clearInformationRelation(Information $information): int
    {
        return $this->catalogPagesRepository->clearInformationRelation($information);
    }

    /**
     * @param int $languageId
     * @param int $platformId
     * @param bool $active
     * @return array
     */
    public function getAllNames(int $languageId, int $platformId, bool $active = true): array
    {
        return $this->catalogPagesRepository->getAllNames($languageId, $platformId, $active);
    }

    /**
     * @param int $languageId
     * @param int $platformId
     * @param bool $active
     * @param string $delimiter
     * @return array
     */
    public function getAllNamesDropDown(int $languageId, int $platformId, bool $active = true, string $delimiter = '-- '): array
    {
        $catalogPagesList = ArrayHelper::map($this->getAllNames($languageId, $platformId, $active), 'catalog_pages_id', static function (array $catalog) use ($delimiter) {
            return ($catalog['lvl'] > 0 ? str_repeat($delimiter, $catalog['lvl'] + 1) . ' ' : '') . $catalog['name'];
        });
        return $catalogPagesList;
    }

    public function addInformationByInfoId(CatalogPages $catalogPage, int $informationId)
    {

        $catalogPagesToInformation = CatalogPagesToInformation::create(
            $catalogPage->catalog_pages_id,
            $informationId
        );
        $this->catalogPagesRepository->saveRelationInformation($catalogPagesToInformation);
    }

    public function findByInformationId(int $informationId, int $platformId, bool $asArray = false)
    {
        return $this->catalogPagesRepository->findByInformationId($informationId, $platformId, $asArray);
    }

    /**
     * @param $catalogPageForm
     * @param int $languageId
     * @return array
     */
    private function findDefaultNameAndSlug(array $catalogPageForm, int $languageId): array
    {
        $default = $catalogPageForm[$languageId];
        $defaultName = $default->name;
        $defaultSlug = $default->slug;
        if (empty($defaultSlug) && !empty($defaultName)) {
            $defaultSlug = $defaultName;
        }
        if (empty($defaultName)) {
            foreach ($catalogPageForm as $description) {
                if (!empty($description->name)) {
                    $defaultName = $description->name;
                    $defaultSlug = $this->slugify($defaultName);
                }
            }
        }
        if (empty($defaultName)) {
            $defaultName = CatalogPages::tableName() . '-' . time();
            $defaultSlug = $this->slugify($defaultName);
        }
        return ['defaultName' => $defaultName, 'defaultSlug' => $defaultSlug];
    }

    /**
     * @return array
     */
    private function getQueryCount(): array
    {
        $logger = \Yii::getLogger();
        if ($logger === null) {
            $this->markTestSkipped('I has no logger');
        }
        return $logger->getDbProfiling();
    }

    /**
     * @param string $param
     * @param null|string $suffix
     * @param string $charset
     * @return mixed|string
     */
    private function basename(string $param, $suffix = null, string $charset = 'utf-8')
    {
        if ($suffix) {
            $tmpStr = ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, 0, $charset), null, $charset), DIRECTORY_SEPARATOR);
            if ((mb_strpos($param, $suffix, null, $charset) + mb_strlen($suffix, $charset)) === mb_strlen($param, $charset)) {
                return str_ireplace($suffix, '', $tmpStr);
            } else {
                return ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, 0, $charset), null, $charset), DIRECTORY_SEPARATOR);
            }
        } else {
            return ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, 0, $charset), null, $charset), DIRECTORY_SEPARATOR);
        }
    }
}
