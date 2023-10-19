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
use common\models\PlatformsConfiguration;
use yii\db\ActiveQuery;


/**
 * Class PlatformsConfigurationRepository
 * @package common\models\repositories
 */
final class PlatformsConfigurationRepository
{
    /**
     * @param int|array $id
     * @param bool $asArray
     * @return array|PlatformsConfiguration|PlatformsConfiguration[]|null
     */
    public function findById($id, bool $asArray = false)
    {
        $configuration = PlatformsConfiguration::find()
            ->where(['configuration_id'=> $id])
            ->asArray($asArray)
        ;
        if (is_array($id)) {
            return $configuration->all();
        }
        return $configuration->limit(1)->one();
    }

    /**
     * @param int|array $id
     * @param bool $asArray
     * @return array|PlatformsConfiguration|PlatformsConfiguration[]|null
     */
    public function get($id, bool $asArray = false)
    {
        if (!$configuration = $this->findById($id, $asArray)) {
            throw new NotFoundException('Configuration is not found.');
        }
        return $configuration;
    }

    /**
     * @param PlatformsConfiguration $configuration
     * @param bool $validation
     */
    public function save(PlatformsConfiguration $configuration, bool $validation = false)
    {
        if (!$configuration->save($validation)) {
            throw new \RuntimeException('Configuration saving  error.');
        }
    }

    /**
     * @param PlatformsConfiguration $configuration
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove(PlatformsConfiguration $configuration)
    {
        if ($configuration->delete() === false) {
            throw new \RuntimeException('Configuration remove error.');
        }
    }

    /**
     * @param PlatformsConfiguration $configuration
     * @param array $params
     * @param bool $validation
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(PlatformsConfiguration $configuration, array $params = [], bool $validation = false, bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param){
            if(!$configuration->hasAttribute($attribute)){
                unset($params[$attribute]);
            }
        }
        $configuration->setAttributes($params, $safeOnly);
        if($configuration->update($validation, array_keys($params)) === false){
            return $configuration->getErrors();
        }
        return true;
    }

    /**
     * @param string $key
     * @param int|null $platformId
     * @param bool $asArray
     * @return array|PlatformsConfiguration[]|\yii\db\ActiveRecord[]
     */
    public function findByKey(string $key, ?int $platformId = null, bool $asArray = false)
    {
        $configuration = $this->searchByKeyQuery($key, $platformId, $asArray);
        if ($platformId !== null) {
            return $configuration->limit(1)->one();
        }
        return $configuration->all();
    }

    /**
     * @param string $key
     * @param int|null $platformId
     * @return bool
     */
    public function existByKey(string $key, ?int $platformId = null)
    {
        $configuration = $this->searchByKeyQuery($key, $platformId);
        return $configuration->exists();
    }
    /**
     * @param string $key
     * @param string $value
     * @param int|null $platformId
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function updateByKey(string $key, string $value, ?int $platformId = null)
    {
        $configuration = $this->findByKey($key, $platformId);
        if (!$configuration) {
            return false;
        }
        if (is_array($configuration)) {
            $result = true;
            foreach ($configuration as $conf) {
                $result = $result && $this->edit($conf, ['configuration_value' => $value]);
            }
            return $result;
        }
        return $this->edit($configuration, ['configuration_value' => $value]);
    }

    /**
     * @param string $key
     * @param int|null $platformId
     * @param bool $asArray
     * @return ActiveQuery
     */
    private function searchByKeyQuery(string $key, ?int $platformId = null, bool $asArray = false): ActiveQuery
    {
        $configuration = PlatformsConfiguration::find()
            ->where([
                'configuration_key' => $key,
            ])
            ->asArray($asArray)
            ->andFilterWhere(['platform_id' => $platformId]);
        return $configuration;
    }

}
