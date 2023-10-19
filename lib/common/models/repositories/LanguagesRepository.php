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
namespace common\models\repositories;
use common\models\Languages;


/**
 * Class LanguagesRepository
 * @package common\models\repositories
 */
class LanguagesRepository
{
    /**
     * @param $id
     * @param bool $asArray
     * @return array|Languages|Languages[]|null
     */
    public function findById($id, bool $asArray = false)
    {
        $language = Languages::find()
            ->where(['languages_id'=> $id])
            ->asArray($asArray);
        if(is_array($id)){
            return $language->indexBy('languages_id')->all();
        }

        return $language->limit(1)->one();
    }

    /**
     * @param $id
     * @param bool $asArray
     * @return array|Languages|Languages[]|null
     */
    public function get($id, bool $asArray = false)
    {
        if (!$language = $this->findById($id, $asArray)) {
            throw new NotFoundException('Language is not found.');
        }
        return $language;
    }

    public function getAll()
    {
        $languages = Languages::find()
            ->with('languageData')
            ->active()
            ->orderBy('sort_order')
            ->indexBy('languages_id')
            ->all();
        return $languages;
    }
    /**
     * @param Languages $language
     */
    public function save(Languages $language)
    {
        if (!$language->save()) {
            throw new \RuntimeException('Language saving  error.');
        }
    }

    /**
     * @param Languages $language
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove(Languages $language)
    {
        if (!$language->delete()) {
            throw new \RuntimeException('Language remove error.');
        }
    }

    /**
     * @param Languages $language
     * @param array $params
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(Languages $language,$params = [], $safeOnly = true)
    {
        foreach ($params as $attribute => $param){
            if(!$language->hasAttribute($attribute)){
                unset($params[$attribute]);
            }
        }
        $language->setAttributes($params, $safeOnly);
        if(!$language->update(true,array_keys($params))){
            return $language->getErrors();
        }
        return true;
    }
}
