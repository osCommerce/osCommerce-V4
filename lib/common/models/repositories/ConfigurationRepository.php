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
use common\models\Configuration;


/**
 * Class ConfigurationRepository
 * @package common\models\repositories
 */
final class ConfigurationRepository
{
    /**
     * @param array|int $id
     * @return array|Configuration|null
     */
    public function findById($id)
    {
        $configuration = Configuration::find()->where(['configuration_id'=> $id])->limit(1)->one();
        return $configuration;
    }
    
    /**
     * @param array|int $id
     * @return array|Configuration|null
     */
    public function get($id)
    {
        if (!$configuration = $this->findById($id)) {
            throw new NotFoundException('Configuration is not found.');
        }
        return $configuration;
    }
    /**
     * @param Configuration $configuration
     */
    public function save(Configuration $configuration)
    {
        if (!$configuration->save()) {
            throw new \RuntimeException('Configuration saving  error.');
        }
    }

    /**
     * @param Configuration $configuration
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove(Configuration $configuration)
    {
        if (!$configuration->delete()) {
            throw new \RuntimeException('Configuration remove error.');
        }
    }

    /**
     * @param Configuration $configuration
     * @param array $params
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(Configuration $configuration, $params = [], $safeOnly = false)
    {
        foreach ($params as $attribute => $param){
            if(!$configuration->hasAttribute($attribute)){
                unset($params[$attribute]);
            }
        }
        $configuration->setAttributes($params, $safeOnly);
        if(!$configuration->update(false, array_keys($params))){
            return $configuration->getErrors();
        }
        return true;
    }

    /**
     * @param string $key
     * @param bool $asArray
     * @return array|Configuration|null
     */
    public function findByKey(string $key, bool $asArray = false)
    {
        $configuration = Configuration::find()
            ->where(['configuration_key' => $key])
            ->limit(1)
            ->asArray($asArray)
            ->one();
        return $configuration;
    }

    /**
     * @param string $key
     * @param string $value
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function updateByKey(string $key, string $value){
        $configuration = $this->findByKey($key);
        if (!$configuration) {
            return false;
        }
        return $this->edit($configuration, ['configuration_value' => $value]);
    }
}
