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

class Tax extends AbstractClass
{
    public $classId = null;

    public $classRecord = [];
    public $zoneRecordArray = [];

    public function getId()
    {
        return $this->classId;
    }

    public function setId($classId)
    {
        $classId = (int)$classId;
        if ($classId >= 0) {
            $this->classId = $classId;
            return true;
        }
        return $this;
    }

    public function load($classId)
    {
        $this->clear();
        $classId = (int)$classId;
        $classRecord = \common\models\TaxClass::find()->where(['tax_class_id' => $classId])->one();
        if ($classRecord instanceof \common\models\TaxClass) {
            $this->classId = $classId;
            $this->classRecord = $classRecord->toArray();
            unset($classRecord);
            // ZONE
            foreach (\common\models\TaxRates::find()->where(['tax_class_id' => $this->classId])
                ->groupBy('tax_zone_id')->asArray(true)->all() as $zoneArray
            ) {
                $zoneRecord = (\common\models\TaxZones::find()
                    ->where(['geo_zone_id' => (int)$zoneArray['tax_zone_id']])->asArray(true)->one()
                );
                // RATE
                $zoneRecord['rateRecordArray'] = (\common\models\TaxRates::find()
                    ->where(['tax_class_id' => $this->classId, 'tax_zone_id' => (int)$zoneArray['tax_zone_id']])->asArray(true)->all()
                );
                // EOF RATE
                // ZONE TO GEO ZONE
                foreach (\common\models\ZonesToTaxZones::find()
                    ->where(['geo_zone_id' => (int)$zoneRecord['geo_zone_id']])
                    ->asArray(true)->all() as $zoneToGeoRecord
                ) {
                    $zoneToGeoRecord['zone_id'] = (int)$zoneToGeoRecord['zone_id'];
                    $zoneToGeoRecord['zone_country_id'] = (int)$zoneToGeoRecord['zone_country_id'];
                    // GEO ZONE
                    $geoZoneRecord = ((array)\common\models\Zones::find()
                        ->where(['zone_country_id' => (int)$zoneToGeoRecord['zone_country_id'], 'zone_id' => (int)$zoneToGeoRecord['zone_id']])
                        ->asArray(true)->one()
                    );
                    $zoneToGeoRecord['zone_code'] = trim(isset($geoZoneRecord['zone_code']) ? $geoZoneRecord['zone_code'] : '');
                    $zoneToGeoRecord['zone_name'] = trim(isset($geoZoneRecord['zone_name']) ? $geoZoneRecord['zone_name'] : '');
                    unset($geoZoneRecord);
                    // EOF GEO ZONE
                    // COUNTRY
                    $languageId = \common\classes\language::defaultId();
                    $languageCode = \common\classes\language::get_code($languageId, true);
                    $countryRecord = ((array)\common\models\Countries::find()
                        ->where(['countries_id' => (int)$zoneToGeoRecord['zone_country_id'], 'language_id' => $languageId])->asArray(true)->one()
                    );
                    $zoneToGeoRecord['countries_name'] = trim(isset($countryRecord['countries_name']) ? $countryRecord['countries_name'] : '');
                    $zoneToGeoRecord['countries_iso_code_2'] = trim(isset($countryRecord['countries_iso_code_2']) ? $countryRecord['countries_iso_code_2'] : '');
                    $zoneToGeoRecord['countries_iso_code_3'] = trim(isset($countryRecord['countries_iso_code_3']) ? $countryRecord['countries_iso_code_3'] : '');
                    $zoneToGeoRecord['address_format_id'] = trim(isset($countryRecord['address_format_id']) ? $countryRecord['address_format_id'] : '');
                    $zoneToGeoRecord['language_id'] = trim(isset($countryRecord['language_id']) ? $countryRecord['language_id'] : '');
                    $zoneToGeoRecord['language_code'] = trim(isset($countryRecord['language_id']) ? $languageCode : '');
                    unset($countryRecord);
                    unset($languageCode);
                    unset($languageId);
                    // EOF COUNTRY
                    $zoneRecord['zoneToGeoRecordArray'][] = $zoneToGeoRecord;
                }
                unset($zoneToGeoRecord);
                // EOF ZONE TO GEO ZONE
                $this->zoneRecordArray[] = $zoneRecord;
                unset($zoneRecord);
            }
            // EOF ZONE
            return true;
        }
        return false;
    }

    public function validate()
    {
        $this->classId = (int)(((int)$this->classId > 0) ? $this->classId : 0);
        if (!is_array($this->classRecord)) {
            return false;
        }
        if (!parent::validate()) {
            return false;
        }
        unset($this->classRecord['tax_class_id']);
        $this->zoneRecordArray = (is_array($this->zoneRecordArray) ? $this->zoneRecordArray : array());
        foreach ($this->zoneRecordArray as $keyZ => &$zoneRecord) {
            $zoneRecord['rateRecordArray'] = ((isset($zoneRecord['rateRecordArray']) AND is_array($zoneRecord['rateRecordArray']))
                ? $zoneRecord['rateRecordArray'] : array()
            );
            foreach ($zoneRecord['rateRecordArray'] as $keyR => &$rateRecord) {
                if (!isset($rateRecord['tax_rate'])) {
                    unset($zoneRecord['rateRecordArray'][$keyR]);
                }
            }
            unset($rateRecord);
            unset($keyR);
            if (count($zoneRecord['rateRecordArray']) > 0) {
                $zoneRecord['zoneToGeoRecordArray'] = ((isset($zoneRecord['zoneToGeoRecordArray']) AND is_array($zoneRecord['zoneToGeoRecordArray']))
                    ? $zoneRecord['zoneToGeoRecordArray'] : array()
                );
                foreach ($zoneRecord['zoneToGeoRecordArray'] as &$zoneToGeoRecord) {
                    unset($zoneToGeoRecord['association_id']);
                }
                unset($zoneToGeoRecord);
                continue;
            }
            unset($this->zoneRecordArray[$keyZ]);
        }
        unset($zoneRecord);
        unset($keyZ);
        if (count($this->zoneRecordArray) == 0) {
            return false;
        }
        return true;
    }

    public function create()
    {
        $this->classId = 0;
        return $this->save();
    }

    public function save($isReplace = false)
    {
        $return = false;
        if (!$this->validate()) {
            return $return;
        }
        $classClass = \common\models\TaxClass::find()->where(['tax_class_id' => $this->classId])->one();
        /*if (!($classClass instanceof \common\models\TaxClass)) {
            SEARCH CLASS BY TITLE?
        }*/
        if (!($classClass instanceof \common\models\TaxClass)) {
            $classClass = new \common\models\TaxClass();
            $classClass->loadDefaultValues();
            $classClass->date_added = date('Y-m-d H:i:s');
            $classClass->last_modified = date('Y-m-d H:i:s');
            if ($this->classId > 0) {
                $classClass->tax_class_id = $this->classId;
            } else {
                $this->unrelate();
            }
        }
        $classClass->setAttributes($this->classRecord, false);
        if ($classClass->save(false)) {
            $this->classRecord = $classClass->toArray();
            $this->classId = (int)$classClass->tax_class_id;
            // ZONE
            foreach ($this->zoneRecordArray as $keyZ => &$zoneRecord) {
                $isSaveZ = false;
                $zoneId = (int)(isset($zoneRecord['geo_zone_id']) ? $zoneRecord['geo_zone_id'] : 0);
                unset($zoneRecord['geo_zone_id']);
                try {
                    $zoneClass = \common\models\TaxZones::find()->where(['geo_zone_id' => $zoneId])->one();
                    if (!($zoneClass instanceof \common\models\TaxZones)) {
                        $zoneName = trim(isset($zoneRecord['geo_zone_name']) ? $zoneRecord['geo_zone_name'] : '');
                        if ($zoneName != '') {
                            $zoneClass = \common\models\TaxZones::find()->where(['geo_zone_name' => $zoneName])->all();
                            if (count($zoneClass) > 1) {
                                unset($this->zoneRecordArray[$keyZ]);
                                continue;
                            }
                            $zoneClass = ((count($zoneClass) == 1) ? $zoneClass[0] : false);
                        }
                        unset($zoneName);
                    }
                    if (!($zoneClass instanceof \common\models\TaxZones)) {
                        $zoneClass = new \common\models\TaxZones();
                        $zoneClass->loadDefaultValues();
                        $zoneClass->date_added = date('Y-m-d H:i:s');
                        $zoneClass->last_modified = date('Y-m-d H:i:s');
                        $zoneClass->geo_zone_id = $zoneId;
                    }
                    $zoneClass->setAttributes($zoneRecord, false);
                    if ($zoneClass->save(false)) {
                        $isSaveZ = true;
                        $zoneId = (int)$zoneClass->geo_zone_id;
                        $zoneRecord = ($zoneClass->toArray() + $zoneRecord);
                        // RATE
                        foreach ($zoneRecord['rateRecordArray'] as $keyR => &$rateRecord) {
                            $isSaveR = false;
                            $rateId = (int)(isset($rateRecord['tax_rates_id']) ? $rateRecord['tax_rates_id'] : 0);
                            unset($rateRecord['tax_rates_id']);
                            $rateRecord['tax_class_id'] = $this->classId;
                            $rateRecord['tax_zone_id'] = $zoneId;
                            try {
                                $rateClass = \common\models\TaxRates::find()->where(['tax_rates_id' => $rateId])->one();
                                if (!($rateClass instanceof \common\models\TaxRates)) {
                                    $rateDescription = trim(isset($rateRecord['tax_description']) ? $rateRecord['tax_description'] : '');
                                    $rateClass = \common\models\TaxRates::find()->where(['tax_class_id' => $this->classId, 'tax_zone_id' => $zoneId, 'tax_description' => $rateDescription])->all();
                                    if (count($rateClass) > 1) {
                                        unset($zoneRecord['rateRecordArray'][$keyR]);
                                        continue;
                                    }
                                    $rateClass = ((count($rateClass) == 1) ? $rateClass[0] : false);
                                    unset($rateDescription);
                                }
                                if (!($rateClass instanceof \common\models\TaxRates)) {
                                    $rateClass = new \common\models\TaxRates();
                                    $rateClass->loadDefaultValues();
                                    $rateClass->date_added = date('Y-m-d H:i:s');
                                    $rateClass->last_modified = date('Y-m-d H:i:s');
                                    $rateClass->tax_rates_id = $rateId;
                                }
                                $rateClass->setAttributes($rateRecord, false);
                                if ($rateClass->save(false)) {
                                    $isSaveR = true;
                                    $rateRecord = $rateClass->toArray();
                                } else {
                                    $this->messageAdd($rateClass->getErrorSummary(true));
                                }
                            } catch (\Exception $exc) {
                                $this->messageAdd($exc->getMessage());
                            }
                            unset($rateClass);
                            unset($rateId);
                            if ($isSaveR != true) {
                                unset($zoneRecord['rateRecordArray'][$keyR]);
                            }
                            unset($isSaveR);
                        }
                        unset($rateRecord);
                        unset($keyR);
                        // EOF RATE
                        // ZONE TO GEO ZONE
                        foreach ($zoneRecord['zoneToGeoRecordArray'] as $keyG => &$zoneToGeoRecord) {
                            $isSaveG =  false;
                            $countryId = (int)(isset($zoneToGeoRecord['zone_country_id']) ? $zoneToGeoRecord['zone_country_id'] : 0);
                            $geoZoneId = (int)(isset($zoneToGeoRecord['zone_id']) ? $zoneToGeoRecord['zone_id'] : 0);
                            unset($zoneToGeoRecord['zone_country_id']);
                            unset($zoneToGeoRecord['geo_zone_id']);
                            unset($zoneToGeoRecord['zone_id']);
                            if ($countryId <= 0) {
                                $countryIso2 = trim(isset($zoneToGeoRecord['countries_iso_code_2']) ? $zoneToGeoRecord['countries_iso_code_2'] : '');
                                if ($countryIso2 != '') {
                                    foreach (\common\models\Countries::find()->where(['countries_iso_code_2' => $countryIso2])->all() as $countryClass) {
                                        $countryId = (int)$countryClass->countries_id;
                                        break;
                                    }
                                    unset($countryClass);
                                }
                                unset($countryIso2);
                            }
                            if ($geoZoneId <= 0) {
                                $zoneCode = trim(isset($zoneToGeoRecord['zone_code']) ? $zoneToGeoRecord['zone_code'] : '');
                                if ($zoneCode != '') {
                                    $countryRecord = \common\models\Zones::find()->where(['zone_country_id' => $countryId, 'zone_code' => $zoneCode])->all();
                                    if (count($countryRecord) > 1) {
                                        unset($zoneRecord['zoneToGeoRecordArray'][$keyG]);
                                        continue;
                                    }
                                    $geoZoneId = (int)((count($countryRecord) == 1) ? $countryRecord[0]->zone_id : 0);
                                    unset($countryRecord);
                                }
                                unset($zoneCode);
                            }
                            if ($countryId > 0) {
                                try {
                                    $zoneToTaxZoneClass = (\common\models\ZonesToTaxZones::find()
                                        ->where(['geo_zone_id' => $zoneId, 'zone_country_id' => $countryId, 'zone_id' => $geoZoneId])->one()
                                    );
                                    if (!($zoneToTaxZoneClass instanceof \common\models\ZonesToTaxZones)) {
                                        $zoneToTaxZoneClass = new \common\models\ZonesToTaxZones();
                                        $zoneToTaxZoneClass->loadDefaultValues();
                                        $zoneToTaxZoneClass->geo_zone_id = $zoneId;
                                        $zoneToTaxZoneClass->zone_country_id = $countryId;
                                        $zoneToTaxZoneClass->zone_id = $geoZoneId;
                                        $zoneToTaxZoneClass->date_added = date('Y-m-d H:i:s');
                                        $zoneToTaxZoneClass->last_modified = date('Y-m-d H:i:s');
                                    }
                                    $zoneToTaxZoneClass->setAttributes($zoneToGeoRecord, false);
                                    if ($zoneToTaxZoneClass->save(false)) {
                                        $isSaveG = true;
                                        $zoneToGeoRecord = ($zoneToTaxZoneClass->toArray() + $zoneToGeoRecord);
                                    } else {
                                        $this->messageAdd($zoneToTaxZoneClass->getErrorSummary(true));
                                    }
                                } catch (\Exception $exc) {
                                    $this->messageAdd($exc->getMessage());
                                }
                                unset($zoneToTaxZoneClass);
                                if ($isSaveG != true) {
                                    unset($zoneRecord['zoneToGeoRecordArray'][$keyG]);
                                }
                                unset($isSaveG);
                            }
                            unset($geoZoneId);
                            unset($countryId);
                        }
                        unset($zoneToGeoRecord);
                        unset($keyG);
                        // EOF ZONE TO GEO ZONE
                    } else {
                        $this->messageAdd($zoneClass->getErrorSummary(true));
                    }
                } catch (\Exception $exc) {
                    $this->messageAdd($exc->getMessage());
                }
                unset($zoneClass);
                unset($zoneId);
                if ($isSaveZ != true) {
                    unset($this->zoneRecordArray[$keyZ]);
                }
                unset($isSaveZ);
            }
            unset($zoneRecord);
            unset($keyZ);
            // EOF ZONE
            $return = $this->classId;
        } else {
            $this->messageAdd($classClass->getErrorSummary(true));
        }
        unset($classClass);
        unset($isReplace);
        return $return;
    }
}