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

namespace common\components;

use Yii;

/**
 * Search Bulder class.

 */
class SearchBuilder {

    private $_addtionalSearch = null;
    private $_userKeywordsContainError = false;
    private $_searchKeywords = [];
    private $_parsedKeywords = [];
    private $_msearchKeywords = [];
    private $_relevance_keywords = [];
    private $_search_in_description = false;
    private $_search_internal = false;
    private $_typeSearch = 'simple';
    public $replaceWords = [];
    public $relevanceWords = [];
    public $relevance_order = false; //faster

    public function __construct($typeSeach = 'simple') {
        /**
         * @var $ext \common\extensions\PlainProductsDescription\PlainProductsDescription
         */
        if ($ext = \common\helpers\Extensions::isAllowedAnd('PlainProductsDescription', 'optionSearchByElements')) {
            $this->_addtionalSearch = $ext::optionSearchByElements();
        }
        $this->_typeSearch = $typeSeach;
    }

    public function setSearchInDesc($value = false) {
        $this->_search_in_description = (bool) $value;
    }
    
    public function useSearchInDesc() {
        return $this->_search_in_description;
    }
    
    public function setSearchInternal($value = false) {
        $this->_search_internal = (bool) $value;
    }

    public function useSearchInternal() {
        return $this->_search_internal;
    }

    public $categoriesArray = [];
    public $gapisArray = [];
    public $productsArray = [];    
    public $manufacturesArray = [];
    public $informationArray = [];
    
    public $searchInProperty = false;
    public $searchInAttributes = false;

    public function prepareRequest(string $keywords) {
        if (tep_not_null($keywords)) {
            if (defined('MSEARCH_ENABLE') && strtolower(MSEARCH_ENABLE)=='soundex') {
                if (!\common\helpers\Output::parse_search_string($keywords, $this->_searchKeywords, false) || !\common\helpers\Output::parse_search_string($keywords, $this->_msearchKeywords, MSEARCH_ENABLE)) {
                    $this->_userKeywordsContainError = true;
                }
            } else {
                if (!\common\helpers\Output::parse_search_string($keywords, $this->_searchKeywords)) {
                    $this->_userKeywordsContainError = true;
                }
            }
        }
        if ($this->_userKeywordsContainError) {
            $this->_searchKeywords = [$keywords];
            $this->_msearchKeywords = [$keywords];
        }
        
        $this->prepareKeywordsRequest();
    }
    
    public function parseKeywords(string $keywords) {
      $this->_parsedKeywords = [];
      $keywords = trim(strtolower($keywords));
      $keywords = preg_replace(array('/[\(\)\'`]/', '/"/'), array(' ', ' " '), $keywords);
      /* @var $ext \common\extensions\SearchPlus\SearchPlus */
      if($sp = \common\helpers\Acl::checkExtensionAllowed('SearchPlus')) {
        $keywords = $sp::replaceKeywords($keywords);
      }
        foreach (\common\helpers\Hooks::getList('products-search/alter-keywords') as $filename) {
            include($filename);
        }
      if($ext = \common\helpers\Acl::checkExtensionAllowed('PlainProductsDescription', 'allowed')){
        if (tep_not_null($keywords)) {

          $pieces = preg_split('/[\s]+/', $keywords,-1,PREG_SPLIT_NO_EMPTY);

          $started = false;
          $phrase = '';

          foreach ($pieces as $kw) {
            if ($kw == '"') {
              if ($started) {
                $started = false;
                $this->_parsedKeywords[] = trim($phrase);
                $phrase = '';
              } else {
                $started = true;
              }
            } elseif (!$started) {
              $this->_parsedKeywords[] = $kw;
            } else {
              $phrase .= ' ' . $kw;
            }

          }
          if ($phrase!='') {
            // not closed "
            $this->_parsedKeywords[] = trim($phrase);
          }
        }
      } else {
          $this->_parsedKeywords[] = trim($keywords);
          $this->prepareRequest($keywords);
      }
    }

/**
 * for admin only - search by plain table, no platform restriction
 * @param \common\models\queries\ProductsQuery $q
 * @param type $params
 * @return type
 */
    public function addProductsRestriction(\common\models\queries\ProductsQuery &$q, $params = []){

      /** @var \common\extensions\GoogleAnalyticsTools\GoogleAnalyticsTools $ext */
      if ( $ext = \common\helpers\Extensions::isAllowed('GoogleAnalyticsTools') ) {
          $ext::attachJoin($q);
      }

        /** @var \common\extensions\PlainProductsDescription\PlainProductsDescription $ext */
      $ext = \common\helpers\Acl::checkExtensionAllowed('PlainProductsDescription', 'allowed');
      if ($ext && $ext::isEnabled()) {
        $kws = $this->getParsedKeywords();
        if (defined('MSEARCH_ENABLE') && strtolower(MSEARCH_ENABLE)=='fulltext') {
          $kws = \common\extensions\PlainProductsDescription\PlainProductsDescription::validateKeywords($kws, true);
        } else {
          $kws = \common\extensions\PlainProductsDescription\PlainProductsDescription::validateKeywords($kws);
        }
        if (!is_array($kws) || empty($kws)) {
          // all keywords too short or common
          return;
        }
        
        if (\frontend\design\Info::isTotallyAdmin() && version_compare($ext::getVersion(), '1.0.3', '>=')) {
            $_searchField = '{{%plain_products_name_search}}.search_details_be';
            $_searchFieldSoundEx = '{{%plain_products_name_search}}.search_soundex_be';

        } else {
            $_searchField = '{{%plain_products_name_search}}.search_details';
            $_searchFieldSoundEx = '{{%plain_products_name_search}}.search_soundex';
        }

        $q->joinWith('anyListingName', false);
        $params = $kws;

        if (defined('MSEARCH_ENABLE') && strtolower(MSEARCH_ENABLE)=='fulltext') {

          if (is_array($params)) {
            $q->andWhere('match( ' . $_searchField . ' ) against(:kw)', [':kw' => implode(' ', $params)]);
          }

        } else {
          //always like by "search" field
          $params = \common\extensions\PlainProductsDescription\PlainProductsDescription::validateKeywords($params);

          if (is_array($params)) {
            $f = ['like', '' . $_searchField . '', $params];
            //highest/extra relevance by name (all keywords in the name)

            if ($this->relevance_order) {
                $relevanceF = ['like', '{{%plain_products_name_search}}.products_name', $params];
                $tmp = $tmpF = [];
                foreach ($params as $param) {
                  $tmpF[] = \Yii::$app->db->createCommand($relevanceF[1] . ' '. $relevanceF[0] . ' :kw', [':kw' => '%'. $param . '%'])->rawSql;
                  $tmp[] = \Yii::$app->db->createCommand('-100/if(LOCATE( :kw , ' . $f[1] . ')>0, LOCATE( :kw , ' . $f[1] . '), -100)', [':kw' => $param])->rawSql;
                }
            }

            if (defined('MSEARCH_ENABLE')  && (strtolower(MSEARCH_ENABLE)=='true' || strtolower(MSEARCH_ENABLE)=='soundex')) {
              //+ or like by soundex field
              $fs = ['like', $_searchFieldSoundEx, $params];
              $tmps = \common\extensions\PlainProductsDescription\PlainProductsDescription::getSoundex(implode(' ', $params), false);
              if (is_array($tmps )) {
                $tmps = array_map(function ($el) {return ',' . $el . ',';}, $tmps );
                $f = ['or',
                      $f,
                      ['like', $_searchFieldSoundEx, $tmps]
                     ];
                if ($this->relevance_order) {
                    foreach ($tmps as $param) {
                         $tmp[] = \Yii::$app->db->createCommand('-10/if(LOCATE( :kw , ' . $fs[1] . ')>0, LOCATE( :kw , ' . $fs[1] . '), -10)', [':kw' => $param])->rawSql;
                    }
                }
              }
            }

            if ($this->relevance_order) {
                $q->addOrderBy(new \yii\db\Expression( '(' . implode(' and ', $tmpF) . ') desc, (' .implode(' + ', $tmp) . ')'));
            }
            $q->andWhere($f);
          }

        }


      } else {
        
        $filters_where = $this->getProductsArray(false);
        $q->andWhere($filters_where);
      }
    }

    public function prepareKeywordsRequest(){
        if (sizeof($this->_searchKeywords) > 0) {
            for ($i = 0, $n = sizeof($this->_searchKeywords); $i < $n; $i++) {
                switch ($this->_searchKeywords[$i]) {
                    //case '(':
                    //case ')':
                    case 'and':
                    case 'or':
                        $this->productsArray['regulator'] = $this->_searchKeywords[$i];
                        if ($this->_typeSearch != 'simple') {
                            $this->categoriesArray['regulator'] = $this->informationArray['regulator'] = $this->manufacturesArray['regulator'] = $this->gapisArray['regulator'] = $this->_searchKeywords[$i];
                        }
                        break;
                    default:

                        $keyword = $this->_searchKeywords[$i];
                        $this->replaceWords[] = $this->_searchKeywords[$i];
                        $this->relevanceWords[] = $this->_searchKeywords[$i];

                        $pArray = ['or',
                            ['like', 'if(length(pd1.products_name), pd1.products_name, pd.products_name)', $keyword],
                            ['like', 'm.manufacturers_name', $keyword],
                            ['like', 'if(length(pd1.products_head_keywords_tag), pd1.products_head_keywords_tag, pd.products_head_keywords_tag)', $keyword],
                        ];
                        if ($this->useSearchInDesc()) {
                            $pArray[] = ['like', 'if(length(pd1.products_description), pd1.products_description, pd.products_description)', $keyword];
                        }
                        
                        if ($this->useSearchInternal()) {
                            $pArray[] = ['like', 'if(length(pd1.products_internal_name), pd1.products_internal_name, pd.products_internal_name)', $keyword];
                        }
                        
                        if (defined('MSEARCH_ENABLE') && strtolower(MSEARCH_ENABLE)=='soundex' && isset($this->_msearchKeywords[$i])) {
                            $mkeyword = $this->_msearchKeywords[$i];
                            if (!empty($mkeyword)){
                                $pArray[] = ['like', 'if(length(pd1.products_name_soundex), pd1.products_name_soundex, pd.products_name_soundex)', $mkeyword];
                                if ($this->useSearchInDesc()) {
                                    $pArray[] = ['like', 'if(length(pd1.products_description_soundex), pd1.products_description_soundex, pd.products_description_soundex)', $mkeyword];
                                }
                            }
                        }

                        $this->_checkProductAdditionalFileds($pArray, $keyword);

                        /**
                         * @var $ext \common\extensions\GoogleAnalyticsTools\GoogleAnalyticsTools
                         */
                        if ( $ext = \common\helpers\Extensions::isAllowed('GoogleAnalyticsTools') ){
                            $__condition = $ext::searchBuilderCondition($keyword);
                            if ( $__condition ) {
                                $pArray[] = $__condition;
                            }
                        }

                        if (PRODUCTS_PROPERTIES == 'True' && $this->searchInProperty) {
                            global $languages_id;
                            $pArray[] = ['and',
                                    ['pvk.language_id' => (int)$languages_id ],
                                    ['like', 'pvk.values_text', $keyword]
                                ];
                        }

                        if ($this->searchInAttributes) {
                            global $languages_id;
                            $pArray[] = ['and',
                                    ['pok.language_id' => (int)$languages_id],
                                    ['povk.language_id' => (int)$languages_id],
                                    ['like', 'povk.products_options_values_name', $keyword]
                                ];
                        }

                        $this->productsArray[] = $pArray;

                        if ($this->_typeSearch != 'simple') {
                            if ( $ext = \common\helpers\Extensions::isAllowed('GoogleAnalyticsTools') ) {
                                $this->gapisArray[] = ['like', 'gs.gapi_keyword', $keyword];
                            }

                            $this->categoriesArray[] = ['or',
                                ['like', 'if(length(cd1.categories_name), cd1.categories_name, cd.categories_name)', $keyword],
                                ['like', 'if(length(cd1.categories_description), cd1.categories_description, cd.categories_description)', $keyword]
                            ];

                            $this->manufacturesArray[] = ['like', 'manufacturers_name', $keyword];

                            $this->informationArray[] = ['or',
                                ['like', 'if(length(i1.info_title), i1.info_title, i.info_title)', $keyword],
                                ['like', 'if(length(i1.description), i1.description, i.description)', $keyword],
                                ['like', 'if(length(i1.page_title), i1.page_title, i.page_title)', $keyword]
                            ];
                        }
                        break;
                }
            }
        }
    }
    
    public function getParsedKeywords(){
        return $this->_parsedKeywords;
    }

    public function getSearchKeywords(){
        return $this->_searchKeywords;
    }
    
    public function getMsSearchKeywords(){
        return $this->_msearch_keywords;
    }

    protected function _checkProductAdditionalFileds(&$pArray, $keyword) {
        if (is_array($this->_addtionalSearch) && count($this->_addtionalSearch)) {
            foreach ($this->_addtionalSearch as $item) {
                switch ($item) {
                    case 'SKU':
                        $pArray[] = ['like', 'p.products_model', $keyword];
                        break;
                    case 'ASIN':
                        $pArray[] = ['like', 'p.products_asin', $keyword];
                        break;
                    case 'EAN':
                        $pArray[] = ['like', 'p.products_ean', $keyword];
                        break;
                    case 'UPC':
                        $pArray[] = ['like', 'p.products_upc', $keyword];
                        break;
                    case 'ISBN':
                        $pArray[] = ['like', 'p.products_isbn', $keyword];
                        break;
                }
                if (\common\helpers\Extensions::isAllowed('Inventory')) {
                    $_ids = $this->getInventoryIds($keyword, $item);
                    if (is_array($_ids) && count($_ids))
                        $pArray[] = ['in', 'p.products_id', $_ids];
                }
            }
        }
    }
    
    protected function getInventoryIds($keyword, $searchIn){
        static $_cache = [];
        if (!isset($_cache[$keyword. '_' . $searchIn])){
            $iQuery = \common\models\Inventory::find()->select('prid')->distinct();
            switch($searchIn){
                case 'SKU':
                        $iQuery->orWhere(['like', 'products_model', $keyword]);
                        break;
                    case 'ASIN':
                        $iQuery->orWhere(['like', 'products_asin', $keyword]);
                        break;
                    case 'EAN':
                        $iQuery->orWhere(['like', 'products_ean', $keyword]);
                        break;
                    case 'UPC':
                        $iQuery->orWhere(['like', 'products_upc', $keyword]);
                        break;
                    case 'ISBN':
                        $iQuery->orWhere(['like', 'products_isbn', $keyword]);
                        break;
                    default:
                        return [];
                    break;
            }
            $ids = \yii\helpers\ArrayHelper::getColumn($iQuery->asArray()->all(), 'prid');
            $_cache[$keyword. '_' . $searchIn] = $ids;
        }
        return $_cache[$keyword. '_' . $searchIn];
    }

    public function getCategoriesArray($toString = true) {
        if ($toString) {
            return $this->_toString($this->categoriesArray);
        } else {
            return $this->_getArrayToModel($this->categoriesArray);
        }
    }
    
    private function _getArrayToModel(array $array){
        if (isset($array['regulator'])){
            $regulator = $array['regulator'];
            unset($array['regulator']);
        } else {
            $regulator = 'and';
        }
        array_unshift($array, $regulator);
        return $array;
    }

    public function getProductsArray($toString = true) {
        if ($toString) {
            return $this->_toString($this->productsArray);
        } else {
            return $this->_getArrayToModel($this->productsArray);
        }
    }
    
    public function getInformationsArray($toString = true) {
        if ($toString) {
            return $this->_toString($this->informationArray);
        } else {
            return $this->_getArrayToModel($this->informationArray);
        }
    }

    public function getManufacturersArray($toString = true) {
        if ($toString) {
            return $this->_toString($this->manufacturesArray);
        } else {
            return $this->_getArrayToModel($this->manufacturesArray);
        }
    }

    public function getGoogleKeywordsArray($toString = true) {
        if ($toString) {
            return $this->_toString($this->gapisArray);
        } else {
            return $this->_getArrayToModel($this->gapisArray);
        }
    }

    private function _toString(array $arrays) {
        
        if (empty($arrays)) return '';
        
        if (isset($arrays['regulator'])) {
            $reg = $arrays['regulator'];
            unset($arrays['regulator']);
        } else {
            $reg = ' and ';
        }
        $qBuilder = Yii::$app->getDb()->getQueryBuilder();
        $params = [];
        $result = [];
        if (!empty($arrays)) {
          foreach ($arrays as $array) {
              $result[] = $qBuilder->buildCondition($array, $params);// . (count($array) == count($array, COUNT_RECURSIVE) ? '' : ') ');
          }
        } else {// no search words, only and/or
          $result[] = 0;
        }
        $subQuery = '(' . implode(') ' . $reg . ' (', $result) . ')';
        $subQuery = Yii::$app->getDb()->createCommand($subQuery, $params)->rawSql;

        return " and (" . $subQuery . ") ";
    }

    private function _prepareSqlParams(&$values) {
        foreach ($values as $key => &$value) {
            $value = "'" . tep_db_input(tep_db_prepare_input($value)) . "'";
        }
    }

}
