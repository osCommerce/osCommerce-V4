<?php

namespace common\services;
use common\models\repositories\LanguagesRepository;


final class LanguagesService
{
    /** @var LanguagesRepository */
    private $languagesRepository;

    public function __construct(LanguagesRepository $languagesRepository)
    {
        $this->languagesRepository = $languagesRepository;
    }

    /**
     * @param $id
     * @param bool $asArray
     * @return array|\common\models\Languages|\common\models\Languages[]|null
     */
    public function findById($id, bool $asArray = false)
    {
        return $this->languagesRepository->findById($id, $asArray);
    }

    /**
     * @param int $currentLanguage
     * @param int|null $defaultLanguage
     * @param bool $asArray
     * @return \common\models\Languages[]|array
     */
    public function getLanguageInfo(int $currentLanguage, ?int $defaultLanguage = null, bool $asArray = false)
    {
        $languages = [$currentLanguage];
        if ($defaultLanguage > 0) {
            $languages[] = $languages;
        }
        $languageInfo = $this->languagesRepository->findById(array_unique([$currentLanguage, $defaultLanguage]), $asArray);
        $result = null;
        if (isset($languageInfo[$currentLanguage])) {
            $result = $languageInfo[$currentLanguage];
        } elseif (isset($languageInfo[$defaultLanguage])) {
            $result = $languageInfo[$defaultLanguage]['code'];
        } else {
            throw new \InvalidArgumentException('Language Info Not Found');
        }
        return $result;
    }
}
