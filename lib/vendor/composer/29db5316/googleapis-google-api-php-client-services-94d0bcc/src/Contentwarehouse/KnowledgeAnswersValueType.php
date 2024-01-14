<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Google\Service\Contentwarehouse;

class KnowledgeAnswersValueType extends \Google\Collection
{
  protected $collection_key = 'viewSpecificNumberTypes';
  /**
   * @var KnowledgeAnswersAnyType
   */
  public $anyType;
  protected $anyTypeType = KnowledgeAnswersAnyType::class;
  protected $anyTypeDataType = '';
  /**
   * @var KnowledgeAnswersAttributeType
   */
  public $attributeType;
  protected $attributeTypeType = KnowledgeAnswersAttributeType::class;
  protected $attributeTypeDataType = '';
  /**
   * @var KnowledgeAnswersBooleanType
   */
  public $booleanType;
  protected $booleanTypeType = KnowledgeAnswersBooleanType::class;
  protected $booleanTypeDataType = '';
  /**
   * @var KnowledgeAnswersCollectionType
   */
  public $collectionType;
  protected $collectionTypeType = KnowledgeAnswersCollectionType::class;
  protected $collectionTypeDataType = '';
  /**
   * @var KnowledgeAnswersCompoundType
   */
  public $compoundType;
  protected $compoundTypeType = KnowledgeAnswersCompoundType::class;
  protected $compoundTypeDataType = '';
  /**
   * @var KnowledgeAnswersDateType
   */
  public $dateType;
  protected $dateTypeType = KnowledgeAnswersDateType::class;
  protected $dateTypeDataType = '';
  /**
   * @var KnowledgeAnswersDependencyType
   */
  public $dependencyType;
  protected $dependencyTypeType = KnowledgeAnswersDependencyType::class;
  protected $dependencyTypeDataType = '';
  /**
   * @var KnowledgeAnswersDurationType
   */
  public $durationType;
  protected $durationTypeType = KnowledgeAnswersDurationType::class;
  protected $durationTypeDataType = '';
  /**
   * @var KnowledgeAnswersEntityType
   */
  public $entityType;
  protected $entityTypeType = KnowledgeAnswersEntityType::class;
  protected $entityTypeDataType = '';
  /**
   * @var string
   */
  public $inputCompositionConfig;
  /**
   * @var KnowledgeAnswersMeasurementType
   */
  public $measurementType;
  protected $measurementTypeType = KnowledgeAnswersMeasurementType::class;
  protected $measurementTypeDataType = '';
  /**
   * @var KnowledgeAnswersNormalizedStringType
   */
  public $normalizedStringType;
  protected $normalizedStringTypeType = KnowledgeAnswersNormalizedStringType::class;
  protected $normalizedStringTypeDataType = '';
  /**
   * @var KnowledgeAnswersNumberType
   */
  public $numberType;
  protected $numberTypeType = KnowledgeAnswersNumberType::class;
  protected $numberTypeDataType = '';
  /**
   * @var KnowledgeAnswersOpaqueType
   */
  public $opaqueType;
  protected $opaqueTypeType = KnowledgeAnswersOpaqueType::class;
  protected $opaqueTypeDataType = '';
  /**
   * @var KnowledgeAnswersPlexityRequirement
   */
  public $plexityRequirement;
  protected $plexityRequirementType = KnowledgeAnswersPlexityRequirement::class;
  protected $plexityRequirementDataType = '';
  /**
   * @var KnowledgeAnswersPolarQuestionType
   */
  public $polarQuestionType;
  protected $polarQuestionTypeType = KnowledgeAnswersPolarQuestionType::class;
  protected $polarQuestionTypeDataType = '';
  /**
   * @var KnowledgeAnswersSemanticType
   */
  public $semanticType;
  protected $semanticTypeType = KnowledgeAnswersSemanticType::class;
  protected $semanticTypeDataType = '';
  /**
   * @var KnowledgeAnswersStateOfAffairsType
   */
  public $stateOfAffairsType;
  protected $stateOfAffairsTypeType = KnowledgeAnswersStateOfAffairsType::class;
  protected $stateOfAffairsTypeDataType = '';
  /**
   * @var KnowledgeAnswersStringType
   */
  public $stringType;
  protected $stringTypeType = KnowledgeAnswersStringType::class;
  protected $stringTypeDataType = '';
  /**
   * @var KnowledgeAnswersTimeZoneType
   */
  public $timezoneType;
  protected $timezoneTypeType = KnowledgeAnswersTimeZoneType::class;
  protected $timezoneTypeDataType = '';
  /**
   * @var KnowledgeAnswersTrackingNumberType
   */
  public $trackingNumberType;
  protected $trackingNumberTypeType = KnowledgeAnswersTrackingNumberType::class;
  protected $trackingNumberTypeDataType = '';
  /**
   * @var KnowledgeAnswersNumberType[]
   */
  public $viewSpecificNumberTypes;
  protected $viewSpecificNumberTypesType = KnowledgeAnswersNumberType::class;
  protected $viewSpecificNumberTypesDataType = 'array';

  /**
   * @param KnowledgeAnswersAnyType
   */
  public function setAnyType(KnowledgeAnswersAnyType $anyType)
  {
    $this->anyType = $anyType;
  }
  /**
   * @return KnowledgeAnswersAnyType
   */
  public function getAnyType()
  {
    return $this->anyType;
  }
  /**
   * @param KnowledgeAnswersAttributeType
   */
  public function setAttributeType(KnowledgeAnswersAttributeType $attributeType)
  {
    $this->attributeType = $attributeType;
  }
  /**
   * @return KnowledgeAnswersAttributeType
   */
  public function getAttributeType()
  {
    return $this->attributeType;
  }
  /**
   * @param KnowledgeAnswersBooleanType
   */
  public function setBooleanType(KnowledgeAnswersBooleanType $booleanType)
  {
    $this->booleanType = $booleanType;
  }
  /**
   * @return KnowledgeAnswersBooleanType
   */
  public function getBooleanType()
  {
    return $this->booleanType;
  }
  /**
   * @param KnowledgeAnswersCollectionType
   */
  public function setCollectionType(KnowledgeAnswersCollectionType $collectionType)
  {
    $this->collectionType = $collectionType;
  }
  /**
   * @return KnowledgeAnswersCollectionType
   */
  public function getCollectionType()
  {
    return $this->collectionType;
  }
  /**
   * @param KnowledgeAnswersCompoundType
   */
  public function setCompoundType(KnowledgeAnswersCompoundType $compoundType)
  {
    $this->compoundType = $compoundType;
  }
  /**
   * @return KnowledgeAnswersCompoundType
   */
  public function getCompoundType()
  {
    return $this->compoundType;
  }
  /**
   * @param KnowledgeAnswersDateType
   */
  public function setDateType(KnowledgeAnswersDateType $dateType)
  {
    $this->dateType = $dateType;
  }
  /**
   * @return KnowledgeAnswersDateType
   */
  public function getDateType()
  {
    return $this->dateType;
  }
  /**
   * @param KnowledgeAnswersDependencyType
   */
  public function setDependencyType(KnowledgeAnswersDependencyType $dependencyType)
  {
    $this->dependencyType = $dependencyType;
  }
  /**
   * @return KnowledgeAnswersDependencyType
   */
  public function getDependencyType()
  {
    return $this->dependencyType;
  }
  /**
   * @param KnowledgeAnswersDurationType
   */
  public function setDurationType(KnowledgeAnswersDurationType $durationType)
  {
    $this->durationType = $durationType;
  }
  /**
   * @return KnowledgeAnswersDurationType
   */
  public function getDurationType()
  {
    return $this->durationType;
  }
  /**
   * @param KnowledgeAnswersEntityType
   */
  public function setEntityType(KnowledgeAnswersEntityType $entityType)
  {
    $this->entityType = $entityType;
  }
  /**
   * @return KnowledgeAnswersEntityType
   */
  public function getEntityType()
  {
    return $this->entityType;
  }
  /**
   * @param string
   */
  public function setInputCompositionConfig($inputCompositionConfig)
  {
    $this->inputCompositionConfig = $inputCompositionConfig;
  }
  /**
   * @return string
   */
  public function getInputCompositionConfig()
  {
    return $this->inputCompositionConfig;
  }
  /**
   * @param KnowledgeAnswersMeasurementType
   */
  public function setMeasurementType(KnowledgeAnswersMeasurementType $measurementType)
  {
    $this->measurementType = $measurementType;
  }
  /**
   * @return KnowledgeAnswersMeasurementType
   */
  public function getMeasurementType()
  {
    return $this->measurementType;
  }
  /**
   * @param KnowledgeAnswersNormalizedStringType
   */
  public function setNormalizedStringType(KnowledgeAnswersNormalizedStringType $normalizedStringType)
  {
    $this->normalizedStringType = $normalizedStringType;
  }
  /**
   * @return KnowledgeAnswersNormalizedStringType
   */
  public function getNormalizedStringType()
  {
    return $this->normalizedStringType;
  }
  /**
   * @param KnowledgeAnswersNumberType
   */
  public function setNumberType(KnowledgeAnswersNumberType $numberType)
  {
    $this->numberType = $numberType;
  }
  /**
   * @return KnowledgeAnswersNumberType
   */
  public function getNumberType()
  {
    return $this->numberType;
  }
  /**
   * @param KnowledgeAnswersOpaqueType
   */
  public function setOpaqueType(KnowledgeAnswersOpaqueType $opaqueType)
  {
    $this->opaqueType = $opaqueType;
  }
  /**
   * @return KnowledgeAnswersOpaqueType
   */
  public function getOpaqueType()
  {
    return $this->opaqueType;
  }
  /**
   * @param KnowledgeAnswersPlexityRequirement
   */
  public function setPlexityRequirement(KnowledgeAnswersPlexityRequirement $plexityRequirement)
  {
    $this->plexityRequirement = $plexityRequirement;
  }
  /**
   * @return KnowledgeAnswersPlexityRequirement
   */
  public function getPlexityRequirement()
  {
    return $this->plexityRequirement;
  }
  /**
   * @param KnowledgeAnswersPolarQuestionType
   */
  public function setPolarQuestionType(KnowledgeAnswersPolarQuestionType $polarQuestionType)
  {
    $this->polarQuestionType = $polarQuestionType;
  }
  /**
   * @return KnowledgeAnswersPolarQuestionType
   */
  public function getPolarQuestionType()
  {
    return $this->polarQuestionType;
  }
  /**
   * @param KnowledgeAnswersSemanticType
   */
  public function setSemanticType(KnowledgeAnswersSemanticType $semanticType)
  {
    $this->semanticType = $semanticType;
  }
  /**
   * @return KnowledgeAnswersSemanticType
   */
  public function getSemanticType()
  {
    return $this->semanticType;
  }
  /**
   * @param KnowledgeAnswersStateOfAffairsType
   */
  public function setStateOfAffairsType(KnowledgeAnswersStateOfAffairsType $stateOfAffairsType)
  {
    $this->stateOfAffairsType = $stateOfAffairsType;
  }
  /**
   * @return KnowledgeAnswersStateOfAffairsType
   */
  public function getStateOfAffairsType()
  {
    return $this->stateOfAffairsType;
  }
  /**
   * @param KnowledgeAnswersStringType
   */
  public function setStringType(KnowledgeAnswersStringType $stringType)
  {
    $this->stringType = $stringType;
  }
  /**
   * @return KnowledgeAnswersStringType
   */
  public function getStringType()
  {
    return $this->stringType;
  }
  /**
   * @param KnowledgeAnswersTimeZoneType
   */
  public function setTimezoneType(KnowledgeAnswersTimeZoneType $timezoneType)
  {
    $this->timezoneType = $timezoneType;
  }
  /**
   * @return KnowledgeAnswersTimeZoneType
   */
  public function getTimezoneType()
  {
    return $this->timezoneType;
  }
  /**
   * @param KnowledgeAnswersTrackingNumberType
   */
  public function setTrackingNumberType(KnowledgeAnswersTrackingNumberType $trackingNumberType)
  {
    $this->trackingNumberType = $trackingNumberType;
  }
  /**
   * @return KnowledgeAnswersTrackingNumberType
   */
  public function getTrackingNumberType()
  {
    return $this->trackingNumberType;
  }
  /**
   * @param KnowledgeAnswersNumberType[]
   */
  public function setViewSpecificNumberTypes($viewSpecificNumberTypes)
  {
    $this->viewSpecificNumberTypes = $viewSpecificNumberTypes;
  }
  /**
   * @return KnowledgeAnswersNumberType[]
   */
  public function getViewSpecificNumberTypes()
  {
    return $this->viewSpecificNumberTypes;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(KnowledgeAnswersValueType::class, 'Google_Service_Contentwarehouse_KnowledgeAnswersValueType');
