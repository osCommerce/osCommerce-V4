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

class KnowledgeAnswersIntentQueryFunctionCallSignals extends \Google\Collection
{
  protected $collection_key = 'signalsFallbackIntents';
  /**
   * @var string
   */
  public $argumentComposingMid;
  /**
   * @var KnowledgeAnswersIntentQueryAttributeSignal[]
   */
  public $attributeSignals;
  protected $attributeSignalsType = KnowledgeAnswersIntentQueryAttributeSignal::class;
  protected $attributeSignalsDataType = 'array';
  /**
   * @var string
   */
  public $conceptEntityMid;
  /**
   * @var KnowledgeAnswersIntentQueryConceptSignals
   */
  public $conceptSignals;
  protected $conceptSignalsType = KnowledgeAnswersIntentQueryConceptSignals::class;
  protected $conceptSignalsDataType = '';
  /**
   * @var string
   */
  public $confidenceLevel;
  /**
   * @var KnowledgeAnswersIntentQueryFunctionCall[]
   */
  public $dedupedFuncalls;
  protected $dedupedFuncallsType = KnowledgeAnswersIntentQueryFunctionCall::class;
  protected $dedupedFuncallsDataType = 'array';
  /**
   * @var NlpSemanticParsingExpressionStatus
   */
  public $expressionStatus;
  protected $expressionStatusType = NlpSemanticParsingExpressionStatus::class;
  protected $expressionStatusDataType = '';
  /**
   * @var string
   */
  public $freefolksTrigger;
  /**
   * @var KnowledgeAnswersIntentQueryGroundingSignals
   */
  public $groundingSignals;
  protected $groundingSignalsType = KnowledgeAnswersIntentQueryGroundingSignals::class;
  protected $groundingSignalsDataType = '';
  /**
   * @var bool
   */
  public $highConfidence;
  /**
   * @var string[]
   */
  public $intentAnnotationSources;
  /**
   * @var string
   */
  public $intentComposingMid;
  /**
   * @var KnowledgeAnswersIntentQueryArgumentProvenance[]
   */
  public $intentProvenance;
  protected $intentProvenanceType = KnowledgeAnswersIntentQueryArgumentProvenance::class;
  protected $intentProvenanceDataType = 'array';
  /**
   * @var string[]
   */
  public $intentRelevantMid;
  /**
   * @var bool
   */
  public $isCloseInterpretation;
  /**
   * @var bool
   */
  public $isDisambiguationCardIntent;
  /**
   * @var bool
   */
  public $isDisambiguationIntent;
  /**
   * @var bool
   */
  public $isNeuralCategoricalInterpretation;
  /**
   * @var bool
   */
  public $isRefinedMeaning;
  /**
   * @var bool
   */
  public $isUiCompositionIntent;
  /**
   * @var KnowledgeAnswersIntentQueryLocalSignals
   */
  public $localSignals;
  protected $localSignalsType = KnowledgeAnswersIntentQueryLocalSignals::class;
  protected $localSignalsDataType = '';
  /**
   * @var string
   */
  public $osrpJourneyTag;
  /**
   * @var string[]
   */
  public $parsedDueToExperiment;
  /**
   * @var KnowledgeAnswersIntentQueryParsingSignals
   */
  public $parsingSignals;
  protected $parsingSignalsType = KnowledgeAnswersIntentQueryParsingSignals::class;
  protected $parsingSignalsDataType = '';
  /**
   * @var float
   */
  public $prefulfillmentRankingScore;
  /**
   * @var AssistantPrefulfillmentRankerPrefulfillmentSignals
   */
  public $prefulfillmentSignals;
  protected $prefulfillmentSignalsType = AssistantPrefulfillmentRankerPrefulfillmentSignals::class;
  protected $prefulfillmentSignalsDataType = '';
  /**
   * @var KnowledgeAnswersDialogReferentialResolution
   */
  public $referentialResolution;
  protected $referentialResolutionType = KnowledgeAnswersDialogReferentialResolution::class;
  protected $referentialResolutionDataType = '';
  /**
   * @var string
   */
  public $refxSummaryNodeId;
  /**
   * @var KnowledgeAnswersIntentQueryResponseMeaningSignalsResponseMeaningSignals
   */
  public $responseMeaningSignals;
  protected $responseMeaningSignalsType = KnowledgeAnswersIntentQueryResponseMeaningSignalsResponseMeaningSignals::class;
  protected $responseMeaningSignalsDataType = '';
  /**
   * @var UniversalsearchNewPackerKnowledgeResultSupport[]
   */
  public $resultSupport;
  protected $resultSupportType = UniversalsearchNewPackerKnowledgeResultSupport::class;
  protected $resultSupportDataType = 'array';
  /**
   * @var string
   */
  public $role;
  /**
   * @var bool
   */
  public $selectedByPrefulfillmentRanking;
  /**
   * @var KnowledgeAnswersIntentQueryShoppingIds
   */
  public $shoppingIds;
  protected $shoppingIdsType = KnowledgeAnswersIntentQueryShoppingIds::class;
  protected $shoppingIdsDataType = '';
  /**
   * @var KnowledgeAnswersIntentQuerySignalComputationFallbackIntent[]
   */
  public $signalsFallbackIntents;
  protected $signalsFallbackIntentsType = KnowledgeAnswersIntentQuerySignalComputationFallbackIntent::class;
  protected $signalsFallbackIntentsDataType = 'array';
  /**
   * @var bool
   */
  public $usesPrefulfillmentRanker;

  /**
   * @param string
   */
  public function setArgumentComposingMid($argumentComposingMid)
  {
    $this->argumentComposingMid = $argumentComposingMid;
  }
  /**
   * @return string
   */
  public function getArgumentComposingMid()
  {
    return $this->argumentComposingMid;
  }
  /**
   * @param KnowledgeAnswersIntentQueryAttributeSignal[]
   */
  public function setAttributeSignals($attributeSignals)
  {
    $this->attributeSignals = $attributeSignals;
  }
  /**
   * @return KnowledgeAnswersIntentQueryAttributeSignal[]
   */
  public function getAttributeSignals()
  {
    return $this->attributeSignals;
  }
  /**
   * @param string
   */
  public function setConceptEntityMid($conceptEntityMid)
  {
    $this->conceptEntityMid = $conceptEntityMid;
  }
  /**
   * @return string
   */
  public function getConceptEntityMid()
  {
    return $this->conceptEntityMid;
  }
  /**
   * @param KnowledgeAnswersIntentQueryConceptSignals
   */
  public function setConceptSignals(KnowledgeAnswersIntentQueryConceptSignals $conceptSignals)
  {
    $this->conceptSignals = $conceptSignals;
  }
  /**
   * @return KnowledgeAnswersIntentQueryConceptSignals
   */
  public function getConceptSignals()
  {
    return $this->conceptSignals;
  }
  /**
   * @param string
   */
  public function setConfidenceLevel($confidenceLevel)
  {
    $this->confidenceLevel = $confidenceLevel;
  }
  /**
   * @return string
   */
  public function getConfidenceLevel()
  {
    return $this->confidenceLevel;
  }
  /**
   * @param KnowledgeAnswersIntentQueryFunctionCall[]
   */
  public function setDedupedFuncalls($dedupedFuncalls)
  {
    $this->dedupedFuncalls = $dedupedFuncalls;
  }
  /**
   * @return KnowledgeAnswersIntentQueryFunctionCall[]
   */
  public function getDedupedFuncalls()
  {
    return $this->dedupedFuncalls;
  }
  /**
   * @param NlpSemanticParsingExpressionStatus
   */
  public function setExpressionStatus(NlpSemanticParsingExpressionStatus $expressionStatus)
  {
    $this->expressionStatus = $expressionStatus;
  }
  /**
   * @return NlpSemanticParsingExpressionStatus
   */
  public function getExpressionStatus()
  {
    return $this->expressionStatus;
  }
  /**
   * @param string
   */
  public function setFreefolksTrigger($freefolksTrigger)
  {
    $this->freefolksTrigger = $freefolksTrigger;
  }
  /**
   * @return string
   */
  public function getFreefolksTrigger()
  {
    return $this->freefolksTrigger;
  }
  /**
   * @param KnowledgeAnswersIntentQueryGroundingSignals
   */
  public function setGroundingSignals(KnowledgeAnswersIntentQueryGroundingSignals $groundingSignals)
  {
    $this->groundingSignals = $groundingSignals;
  }
  /**
   * @return KnowledgeAnswersIntentQueryGroundingSignals
   */
  public function getGroundingSignals()
  {
    return $this->groundingSignals;
  }
  /**
   * @param bool
   */
  public function setHighConfidence($highConfidence)
  {
    $this->highConfidence = $highConfidence;
  }
  /**
   * @return bool
   */
  public function getHighConfidence()
  {
    return $this->highConfidence;
  }
  /**
   * @param string[]
   */
  public function setIntentAnnotationSources($intentAnnotationSources)
  {
    $this->intentAnnotationSources = $intentAnnotationSources;
  }
  /**
   * @return string[]
   */
  public function getIntentAnnotationSources()
  {
    return $this->intentAnnotationSources;
  }
  /**
   * @param string
   */
  public function setIntentComposingMid($intentComposingMid)
  {
    $this->intentComposingMid = $intentComposingMid;
  }
  /**
   * @return string
   */
  public function getIntentComposingMid()
  {
    return $this->intentComposingMid;
  }
  /**
   * @param KnowledgeAnswersIntentQueryArgumentProvenance[]
   */
  public function setIntentProvenance($intentProvenance)
  {
    $this->intentProvenance = $intentProvenance;
  }
  /**
   * @return KnowledgeAnswersIntentQueryArgumentProvenance[]
   */
  public function getIntentProvenance()
  {
    return $this->intentProvenance;
  }
  /**
   * @param string[]
   */
  public function setIntentRelevantMid($intentRelevantMid)
  {
    $this->intentRelevantMid = $intentRelevantMid;
  }
  /**
   * @return string[]
   */
  public function getIntentRelevantMid()
  {
    return $this->intentRelevantMid;
  }
  /**
   * @param bool
   */
  public function setIsCloseInterpretation($isCloseInterpretation)
  {
    $this->isCloseInterpretation = $isCloseInterpretation;
  }
  /**
   * @return bool
   */
  public function getIsCloseInterpretation()
  {
    return $this->isCloseInterpretation;
  }
  /**
   * @param bool
   */
  public function setIsDisambiguationCardIntent($isDisambiguationCardIntent)
  {
    $this->isDisambiguationCardIntent = $isDisambiguationCardIntent;
  }
  /**
   * @return bool
   */
  public function getIsDisambiguationCardIntent()
  {
    return $this->isDisambiguationCardIntent;
  }
  /**
   * @param bool
   */
  public function setIsDisambiguationIntent($isDisambiguationIntent)
  {
    $this->isDisambiguationIntent = $isDisambiguationIntent;
  }
  /**
   * @return bool
   */
  public function getIsDisambiguationIntent()
  {
    return $this->isDisambiguationIntent;
  }
  /**
   * @param bool
   */
  public function setIsNeuralCategoricalInterpretation($isNeuralCategoricalInterpretation)
  {
    $this->isNeuralCategoricalInterpretation = $isNeuralCategoricalInterpretation;
  }
  /**
   * @return bool
   */
  public function getIsNeuralCategoricalInterpretation()
  {
    return $this->isNeuralCategoricalInterpretation;
  }
  /**
   * @param bool
   */
  public function setIsRefinedMeaning($isRefinedMeaning)
  {
    $this->isRefinedMeaning = $isRefinedMeaning;
  }
  /**
   * @return bool
   */
  public function getIsRefinedMeaning()
  {
    return $this->isRefinedMeaning;
  }
  /**
   * @param bool
   */
  public function setIsUiCompositionIntent($isUiCompositionIntent)
  {
    $this->isUiCompositionIntent = $isUiCompositionIntent;
  }
  /**
   * @return bool
   */
  public function getIsUiCompositionIntent()
  {
    return $this->isUiCompositionIntent;
  }
  /**
   * @param KnowledgeAnswersIntentQueryLocalSignals
   */
  public function setLocalSignals(KnowledgeAnswersIntentQueryLocalSignals $localSignals)
  {
    $this->localSignals = $localSignals;
  }
  /**
   * @return KnowledgeAnswersIntentQueryLocalSignals
   */
  public function getLocalSignals()
  {
    return $this->localSignals;
  }
  /**
   * @param string
   */
  public function setOsrpJourneyTag($osrpJourneyTag)
  {
    $this->osrpJourneyTag = $osrpJourneyTag;
  }
  /**
   * @return string
   */
  public function getOsrpJourneyTag()
  {
    return $this->osrpJourneyTag;
  }
  /**
   * @param string[]
   */
  public function setParsedDueToExperiment($parsedDueToExperiment)
  {
    $this->parsedDueToExperiment = $parsedDueToExperiment;
  }
  /**
   * @return string[]
   */
  public function getParsedDueToExperiment()
  {
    return $this->parsedDueToExperiment;
  }
  /**
   * @param KnowledgeAnswersIntentQueryParsingSignals
   */
  public function setParsingSignals(KnowledgeAnswersIntentQueryParsingSignals $parsingSignals)
  {
    $this->parsingSignals = $parsingSignals;
  }
  /**
   * @return KnowledgeAnswersIntentQueryParsingSignals
   */
  public function getParsingSignals()
  {
    return $this->parsingSignals;
  }
  /**
   * @param float
   */
  public function setPrefulfillmentRankingScore($prefulfillmentRankingScore)
  {
    $this->prefulfillmentRankingScore = $prefulfillmentRankingScore;
  }
  /**
   * @return float
   */
  public function getPrefulfillmentRankingScore()
  {
    return $this->prefulfillmentRankingScore;
  }
  /**
   * @param AssistantPrefulfillmentRankerPrefulfillmentSignals
   */
  public function setPrefulfillmentSignals(AssistantPrefulfillmentRankerPrefulfillmentSignals $prefulfillmentSignals)
  {
    $this->prefulfillmentSignals = $prefulfillmentSignals;
  }
  /**
   * @return AssistantPrefulfillmentRankerPrefulfillmentSignals
   */
  public function getPrefulfillmentSignals()
  {
    return $this->prefulfillmentSignals;
  }
  /**
   * @param KnowledgeAnswersDialogReferentialResolution
   */
  public function setReferentialResolution(KnowledgeAnswersDialogReferentialResolution $referentialResolution)
  {
    $this->referentialResolution = $referentialResolution;
  }
  /**
   * @return KnowledgeAnswersDialogReferentialResolution
   */
  public function getReferentialResolution()
  {
    return $this->referentialResolution;
  }
  /**
   * @param string
   */
  public function setRefxSummaryNodeId($refxSummaryNodeId)
  {
    $this->refxSummaryNodeId = $refxSummaryNodeId;
  }
  /**
   * @return string
   */
  public function getRefxSummaryNodeId()
  {
    return $this->refxSummaryNodeId;
  }
  /**
   * @param KnowledgeAnswersIntentQueryResponseMeaningSignalsResponseMeaningSignals
   */
  public function setResponseMeaningSignals(KnowledgeAnswersIntentQueryResponseMeaningSignalsResponseMeaningSignals $responseMeaningSignals)
  {
    $this->responseMeaningSignals = $responseMeaningSignals;
  }
  /**
   * @return KnowledgeAnswersIntentQueryResponseMeaningSignalsResponseMeaningSignals
   */
  public function getResponseMeaningSignals()
  {
    return $this->responseMeaningSignals;
  }
  /**
   * @param UniversalsearchNewPackerKnowledgeResultSupport[]
   */
  public function setResultSupport($resultSupport)
  {
    $this->resultSupport = $resultSupport;
  }
  /**
   * @return UniversalsearchNewPackerKnowledgeResultSupport[]
   */
  public function getResultSupport()
  {
    return $this->resultSupport;
  }
  /**
   * @param string
   */
  public function setRole($role)
  {
    $this->role = $role;
  }
  /**
   * @return string
   */
  public function getRole()
  {
    return $this->role;
  }
  /**
   * @param bool
   */
  public function setSelectedByPrefulfillmentRanking($selectedByPrefulfillmentRanking)
  {
    $this->selectedByPrefulfillmentRanking = $selectedByPrefulfillmentRanking;
  }
  /**
   * @return bool
   */
  public function getSelectedByPrefulfillmentRanking()
  {
    return $this->selectedByPrefulfillmentRanking;
  }
  /**
   * @param KnowledgeAnswersIntentQueryShoppingIds
   */
  public function setShoppingIds(KnowledgeAnswersIntentQueryShoppingIds $shoppingIds)
  {
    $this->shoppingIds = $shoppingIds;
  }
  /**
   * @return KnowledgeAnswersIntentQueryShoppingIds
   */
  public function getShoppingIds()
  {
    return $this->shoppingIds;
  }
  /**
   * @param KnowledgeAnswersIntentQuerySignalComputationFallbackIntent[]
   */
  public function setSignalsFallbackIntents($signalsFallbackIntents)
  {
    $this->signalsFallbackIntents = $signalsFallbackIntents;
  }
  /**
   * @return KnowledgeAnswersIntentQuerySignalComputationFallbackIntent[]
   */
  public function getSignalsFallbackIntents()
  {
    return $this->signalsFallbackIntents;
  }
  /**
   * @param bool
   */
  public function setUsesPrefulfillmentRanker($usesPrefulfillmentRanker)
  {
    $this->usesPrefulfillmentRanker = $usesPrefulfillmentRanker;
  }
  /**
   * @return bool
   */
  public function getUsesPrefulfillmentRanker()
  {
    return $this->usesPrefulfillmentRanker;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(KnowledgeAnswersIntentQueryFunctionCallSignals::class, 'Google_Service_Contentwarehouse_KnowledgeAnswersIntentQueryFunctionCallSignals');
