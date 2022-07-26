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

namespace common\api\Classes;

abstract class AbstractClass
{
    protected $messageArray = array();
    /**
     * Returns a object property names
     * @return array
     */
    private function getPublicProperties()
    {
        static $properties;
        $className = get_class($this);
        if (!isset($properties) OR !is_array($properties) OR !isset($properties[$className])) {
            $properties = (is_array($properties) ? $properties : array());
            foreach ((new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                $properties[$className][] = $property->name;
            }
        }
        return $properties[$className];
    }

    /**
     * Returns a value indicating whether a property is defined.
     * @param string $name
     * @return bool
     */
    public function hasProperty($name)
    {
        return property_exists($this, $name);
    }

    /**
     * Returns a value indicating whether a method is defined.
     * @param string $name
     * @return bool
     */
    public function hasMethod($name)
    {
        return method_exists($this, $name);
    }

    /**
     * Clear values of an object property.
     * @return $this
     */
    public function clear()
    {
        foreach ($this->getPublicProperties() as $property) {
            if (preg_match('/(Array$)|(Record$)/', $property)) {
                $this->$property = [];
            } else {
                $this->$property = 0;
            }
        }
        return $this;
    }

    /**
     * Returns the value of an object property.
     * @param string $propertyName
     * @return mixed
     */
    public function get($propertyName = null)
    {
        if (is_null($propertyName)) {
            $response = [];
            foreach ($this->getPublicProperties() as $property) {
                $response[$property] = $this->$property;
            }
        } elseif ($this->hasProperty($propertyName)) {
            $response = $this->$propertyName;
        }
        return $response;
    }

    /**
     * Sets value of an object property.
     * @param string $propertyValue
     * @param string $propertyName
     * @param bool $add
     * @return $this
     */
    public function set($propertyValue, $propertyName = null, $isAdd = false)
    {
        if (is_null($propertyName)) {
            foreach ($this->getPublicProperties() as $property) {
                if (isset($propertyValue[$property])) {
                    $this->set($propertyValue[$property], $property);
                }
            }
        } elseif ($this->hasProperty($propertyName)) {
            if (preg_match('/Array$/', $propertyName)) {
                if (is_array($propertyValue)) {
                    if ((int)$isAdd > 0) {
                        $this->$propertyName[] = $propertyValue;
                    } else {
                        $this->$propertyName = $propertyValue;
                    }
                }
            } elseif (preg_match('/Record$/', $propertyName)) {
                if (is_array($propertyValue)) {
                    $this->$propertyName = $propertyValue;
                }
            } elseif (is_scalar($propertyValue)) {
                $this->$propertyName = $propertyValue;
            }
        }
        return $this;
    }

    /**
     * Add values of an object property.
     * @param string $propertyValue
     * @param string $propertyName
     * @return $this
     */
    public function add($propertyValue, $propertyName = null)
    {
        return $this->set($propertyValue, $propertyName, true);
    }

    /**
     * Check and prepares data before insertion.
     * @return bool true if valid
     */
    public function validate()
    {
        return true;
    }

    /**
     * Clear relation ids from related data.
     * @return $this
     */
    public function unrelate()
    {
        return $this;
    }

    protected function messageAdd($message = '', $type = 'error')
    {
        $return = false;
        if (is_array($message)) {
            foreach ($message as $line) {
                $this->messageAdd($line, $type);
            }
            $return = true;
        } elseif (is_scalar($message)) {
            $message = trim($message);
            if ($message != '') {
                $this->messageArray[$message] = $type;
                $return = true;
            }
        }
        unset($message);
        unset($type);
        unset($line);
        return $return;
    }

    public function messageGet($isClear = false)
    {
        $return = $this->messageArray;
        if ((int)$isClear > 0) {
            $this->messageArray = array();
        }
        return $return;
    }

    protected static function getLanguageIdByCode($languageCode = '', $defaultLanguageId = 0, $returnSystemDefaultIfZero = false)
    {
        $languageId = \common\models\Languages::find()->where(['code' => trim($languageCode)])->asArray(true)->one();
        if (is_array($languageId)) {
            $languageId = (int)$languageId['languages_id'];
        } else {
            $languageId = (int)$defaultLanguageId;
        }
        if (((int)$returnSystemDefaultIfZero > 0) AND ($languageId <= 0)) {
            $languageId = (int)\common\classes\language::defaultId();
        }
        return $languageId;
    }
}