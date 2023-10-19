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

use common\models\Products;
use common\models\queries\ProductsQuery;
use yii\db\ActiveQuery;

class ProductsRepository {

  public function getWithDescription($ids, $lang = null, $asArray = false) {
    $products = Products::find()->withDescription($lang)->where(['products.products_id' => $ids]);
    if ($asArray) {
      $products->asArray();
    }
    return $products->all();
  }

/**
 * get all required details from product AR and map to new format Add <map[RelationName]> methods if required
 * @param array $ids
 * @param string|array $with
 * @param array $addSettings
 * @return array
 */
  public function getListDetails($ids, $with='description', $addSettings = []) {
    $products = Products::find()->alias('p')->select('p.*')->where(['p.products_id' => $ids]);
    $products->addSelect("EXISTS (select * from " . \common\models\ProductsAttributes::tableName() . " pa where pa.products_id=p.products_id) as product_has_attributes , p.products_id ");
    $this->withAdditionalSettings($products, $addSettings);
    if (!is_array($with)) {
      $with = preg_split('/[, ;]/', $with, -1, PREG_SPLIT_NO_EMPTY);
    }
    if ( !empty($with) ){
      $products->with($with);
    }
    if (!is_array($addSettings)) {
      $addSettings = [];
    }

    $products->asArray();

    $ret = $products->all();
    $ret = $this->UprIdInsteadId($ret, $ids);

    $so = array_flip($ids);

    if (is_array($ret)) {
      foreach ($ret as $k => $v) {
        $idSave = $v['products_id'];
        $extra = [];
        if (isset($so[$v['products_id']])) {
          $extra['sort_order'] = $so[$v['products_id']];
        }
        foreach ($v as $key => $value) {
          if (is_array($value)) {
            if (in_array($key, ['listingDescription'])) {
              $tmp = self::mapDescription($value);
              unset($v[$key]);
              $v = array_merge($v, $tmp);

            } elseif (method_exists($this, 'map' . ucfirst($key))) {

              $m = 'map' . ucfirst($key);
              $tmp = $this->$m($value);

              unset($v[$key]);
              $v = array_merge($v, $tmp);
            }
          }

        }
        $v['products_id'] = $idSave;
        $v['id'] = $v['products_id'];
        $v['link'] = tep_href_link('catalog/product', 'products_id=' . $v['products_id']);
        $v['link_buy'] = tep_href_link('catalog/product', 'action=buy_now&products_id=' . $v['products_id']);
        $v['action'] = \Yii::$app->urlManager->createUrl(['catalog/product', 'products_id' => $v['products_id']]);
        $v['action_buy'] = \Yii::$app->urlManager->createUrl(['catalog/product', 'products_id' => $v['products_id'], 'action' => 'add_product']);

        $ret[$k] = array_merge($v, $addSettings, $extra);
      }
    }
    usort($ret, function ($a, $b) {if (isset($a['sort_order']) && isset($b['sort_order'])) return (int)$a['sort_order'] <=> (int)$b['sort_order']; }) ;
//    echo "#### repository \$products <PRE>" .print_r(array_map(function ($el) { return [$el['products_id'], $el['sort_order']]; }, $ret), 1) ."</PRE>";
    return $ret;
  }
  

/**
 * for plain search table only (details in all languages)
 * get all required for search details from product AR and map some fields
 * @param array $ids
 * @param string|array $with
 * @param array $addSettings
 * @return array
 */
  public function getSearchDetails($ids) {
        $products = Products::find()->alias('p')->select('p.products_id, p.manufacturers_id')
            ->addSelect(array_map(function ($el) { return 'p.' . $el; }, Products::$searchFields))
            ->where(['p.products_id' => $ids])
            ->joinWith(['searchDescriptions']);

        $fields = $fieldsFe = $fieldsBe = $useFields = [];

        /** @var \common\extensions\PlainProductsDescription\PlainProductsDescription $ppd */
        if ($ppd = \common\helpers\Extensions::isAllowedAnd('PlainProductsDescription', 'optionSearchByElements')) {
            $fieldsFe = array_unique(array_map('strtolower', $ppd::optionSearchByElements()));
            if (method_exists($ppd, 'optionSearchByElementsBe')) {
                $fieldsBe = array_unique(array_map('strtolower', $ppd::optionSearchByElementsBe()));
            }
            $fields = array_unique(array_merge($fieldsFe, $fieldsBe));

            foreach( $fields as $field) {
                switch ($field) {
                    case 'asin':
                    case 'ean':
                    case 'isbn':
                    case 'sku':
                    case 'upc':
                      $useFields[] = 'searchInventories';
                      break;
                    case 'description':
                      $useFields[] = 'searchDescriptions';
                      break;
                    case 'categories':
                      $useFields[] = 'searchCategories';
                      break;
                    case 'attributes':
                      $useFields[] = 'searchAttributes';
                      break;
                    case 'properties':
                      $useFields[] = 'searchProperties';
                      break;
                    case 'manufacturer':
                      $useFields[] = 'manufacturer';
                      break;

                }
            }
            $useFields = array_unique($useFields);
            if (count($useFields)) {
                $products->joinWith($useFields);
            }
        }
      /**
       * @var $ext \common\extensions\GoogleAnalyticsTools\GoogleAnalyticsTools
       */
      if ( $ext = \common\helpers\Extensions::isAllowed('GoogleAnalyticsTools') ){
          $ext::attachJoin($products);
      }

    $products->distinct()->asArray();

    $ret = $products->all();

    $sameSearch = ($fieldsFe == $fieldsBe);
    //echo "#### <PRE>" .print_r($ret, 1) ."</PRE>";

    if (is_array($ret)) {
      foreach ($ret as $key => $value) {
        //merge inventory fields into products field

        foreach (['asin' => 'products_asin', 'ean' => 'products_ean', 'isbn' => 'products_isbn', 'sku' => 'products_model', 'upc' => 'products_upc'] as $k => $v) {
          if (in_array($k, $fields)) {
            if (!empty($ret[$key]['searchInventories'])) {
              $ret[$key][$v] .= ' ' .  implode(' ', array_unique(\yii\helpers\ArrayHelper::getColumn($ret[$key]['searchInventories'], $v)));
            }
          } else {
            unset($ret[$key][$v]);
          }
        }
        if (isset($ret[$key]['searchInventories'])) {
          unset($ret[$key]['searchInventories']);
        }

        if (!empty($ret[$key]['searchAttributes']) && is_array($ret[$key]['searchAttributes'])) {

          foreach ($ret[$key]['searchAttributes'] as $k => $aValues) {
            if (!empty($aValues) && is_array($aValues['searchProductsOptions'])) {
              foreach ($aValues['searchProductsOptions'] as $languageId => $v) {
                $ret[$key]['searchAttributes']['_' . $languageId] .=  ' ' . $v['products_options_name']
                    . ' ' . $aValues['searchProductsOptionsValues'][$languageId]['products_options_values_name']
                    . ' ' . $aValues['products_attributes_filename'];
              }

            }
            unset($ret[$key]['searchAttributes'][$k]);
          }

        }

        if (!empty($ret[$key]['searchProperties']) && is_array($ret[$key]['searchProperties'])) {
// 2do range type (no property info now).
          foreach ($ret[$key]['searchProperties'] as $k => $aValues) {
            if (!empty($aValues) && is_array($aValues['searchDescriptions'])) {
              foreach ($aValues['searchDescriptions'] as $languageId => $v) {
                if (!isset($ret[$key]['searchProperties']['_' . $languageId]))  {
                    $ret[$key]['searchProperties']['_' . $languageId] = '';
                }
                $ret[$key]['searchProperties']['_' . $languageId] .=  ' ' . $v['properties_name']
                    . ' ' . $v['properties_description']
                    . ' ' . $v['properties_seo_page_name']
                . ($aValues['values_flag'] ? '':
                    ' ' . $aValues['searchValues'][$languageId]['values_text'] .
                    ' ' . $aValues['searchValues'][$languageId]['values_alt'] .
                    ' ' . $aValues['searchValues'][$languageId]['values_seo_page_name'] .
                    ' ' . $aValues['searchValues'][$languageId]['values_number'] .
                    ' ' . $aValues['searchValues'][$languageId]['values_number_upto']
                    );
              }

            }
            unset($ret[$key]['searchProperties'][$k]);
          }

        }

        // call new function
        if (!empty($ppd) && method_exists($ppd, 'fillInDescriptionField') ) {
            $ppd::fillInDescriptionField($ret[$key], $sameSearch, $fieldsFe, $fieldsBe);
            continue;
        }

//// 2check and remove below

        //unset description if no search in description flag,
        //
        //merge all feilds into (search) description field.
        $so = array_flip($fields);
        if (!empty($ret[$key]['searchDescriptions']) && is_array($ret[$key]['searchDescriptions'])) {
          $keepDescription = ['products_name', 'language_id', 'platform_id', 'department_id', 'products_id'];
          if (in_array('searchDescriptions', $useFields)) {
            $merge = true;
            $keepDescription[] = 'products_description';
          } else {
            $merge = false;
          }
          foreach ($ret[$key]['searchDescriptions'] as $dcode => $dvalues) {
            if (is_array($dvalues)) {
              foreach ($dvalues as $dk => $dv) {
                if (in_array($dk, $keepDescription)) {
                  continue;
                } elseif ($merge) {
                  $ret[$key]['searchDescriptions'][$dcode]['products_description'] .= ' ' . $ret[$key]['searchDescriptions'][$dcode][$dk];
                }
                unset($ret[$key]['searchDescriptions'][$dcode][$dk]);

              }
            }
            if ($merge) {
              $ret[$key]['searchDescriptions'][$dcode]['products_description'] .= ' ' . $ret[$key]['products_seo_page_name']; // old one from products not description
            } else {
              $ret[$key]['searchDescriptions'][$dcode]['products_description'] = '';
            }

            $languageId = $ret[$key]['searchDescriptions'][$dcode]['language_id'];

            $prepandStr = '';
            foreach( $fields as $field) {
              $prepend = ($so[$field] < $so['description']);
              $getValue = '';
              switch ($field) {
                case 'name':
                  $getValue = $ret[$key]['searchDescriptions'][$dcode]['products_name'];
                  break;
                case 'asin':
                  $getValue = $ret[$key]['products_asin'];
                  break;
                case 'ean':
                  $getValue = $ret[$key]['products_ean'];
                  break;
                case 'isbn':
                  $getValue = $ret[$key]['products_isbn'];
                  break;
                case 'sku':
                  $getValue = $ret[$key]['products_model'];
                  break;
                case 'upc':
                  $getValue = $ret[$key]['products_upc'];
                  break;
                case 'description':
                  $getValue = false;
                  break;
                case 'categories':
                  $getValue = implode(' ',
                                  \yii\helpers\ArrayHelper::getColumn(
                                      array_filter($ret[$key]['searchCategories'],  function ($cat) use($languageId) { return  $cat['language_id'] == $languageId; } )
                                    , 'categories_name'));
                  break;
                case 'attributes':
                  $getValue = (isset($ret[$key]['searchAttributes']['_' . $languageId]) ? $ret[$key]['searchAttributes']['_' . $languageId] : '');
                  break;
                case 'properties':
                  $getValue = (isset($ret[$key]['searchProperties']['_' . $languageId]) ? $ret[$key]['searchProperties']['_' . $languageId] : '');
                  break;
                case 'manufacturer':
                  $getValue = (isset($ret[$key]['manufacturer']['manufacturers_name']) ? $ret[$key]['manufacturer']['manufacturers_name'] : '') . (isset($ret[$key]['manufacturer']['manufacturers_old_seo_page_name']) ? ' ' . $ret[$key]['manufacturer']['manufacturers_old_seo_page_name'] : '');
                  break;
                default:
                  $getValue = false;
                  break;
              }
              if ($getValue) {
                if ($prepend) {
                  $prepandStr .= ' ' . $getValue;
                } else {
                  $ret[$key]['searchDescriptions'][$dcode]['products_description'] .= ' ' . $getValue;
                }
              }
            }
            if (!empty($prepandStr)){
              $ret[$key]['searchDescriptions'][$dcode]['products_description'] = $prepandStr . ' ' . $ret[$key]['searchDescriptions'][$dcode]['products_description'];
            }
              /**
               * @var $ext \common\extensions\GoogleAnalyticsTools\GoogleAnalyticsTools
               */
            if ( $ext = \common\helpers\Extensions::isAllowed('GoogleAnalyticsTools') ){
                if ($ext::optionUseInSearch()){
                    if (!empty($ret[$key]['searchGapi']) && is_array($ret[$key]['searchGapi'])){
                        $ret[$key]['searchDescriptions'][$dcode]['products_description'] .= ' ' .
                            implode(' ',\yii\helpers\ArrayHelper::getColumn($ret[$key]['searchGapi'], 'gapi_keyword'));
                    }
                }
            }

            $ret[$key]['searchDescriptions'][$dcode]['products_description'] = \common\helpers\Product::cleanupSearch($ret[$key]['searchDescriptions'][$dcode]['products_description']);

          }
          unset($ret[$key]['searchAttributes']);
          unset($ret[$key]['searchProperties']);
          unset($ret[$key]['searchCategories']);
          unset($ret[$key]['manufacturer']);
        }

      }

    }

//echo "transformed #### <PRE>" .print_r($ret, 1) ."</PRE>";
    return $ret;
  }

  /**
   * get 1st not empty value for each key
   * @param array $desc
   * @return array
   */
  private static function mapDescription( $desc) {
    $ret = [];
    if (is_array($desc) && count($desc)>0) {
      $ret = current($desc);
      $keys = array_keys($ret);

      foreach ($keys as $key) {
        if (empty($ret[$key])) {
          foreach ($desc as $val) {
            if (!empty($val[$key])) {
              $ret[$key] = $val[$key];
              break;
            }
          }
        }
      }
    }

    return $ret;
  }

  /**
   * set categories_name & top_categories_name
   * @param array $desc
   * @return array
   */
  private function mapListingCategories( $desc) {
    $ret = [];
    
    if (is_array($desc) && count($desc)>0) {
      $ret = current($desc);
      if (!isset($ret['parent_id']) || $ret['parent_id']>0) {
        try {
          $ret['cat_path'] = \common\helpers\Categories::getCategoryParents($ret['categories_id']);
          if (!empty($ret['cat_path'][0]['text'])) {
            $ret['top_categories_name'] = $ret['cat_path'][0]['text'];
          }
        } catch (\Exception $e) {
          \Yii::warning($e->getMessage() . ' ' .$e->getTraceAsString());
        }
      } else {
        $ret['cat_path'][0] = $ret;
        $ret['cat_path'][0]['id'] = $ret['categories_id'];
        $ret['cat_path'][0]['text'] = $ret['categories_name'];
        $ret['top_categories_name'] = $ret['cat_path'][0]['text'];
      }
    }

    return $ret;
  }

  /**
   * @param $attributes
   * @param string $condition
   * @param array $params
   * @return int
   */
  public function updateAll($attributes, $condition = '', $params = []) {
    return Products::updateAll($attributes, $condition, $params);
  }

  public function findAssignedCatalog($productField = 'products_status', $active = false) {
    $products = Products::find()->alias('p')
        ->select(['p.products_id AS id', 'p2c.categories_id as cid'])
        ->innerJoinWith(['categoriesList p2c'], false)
        ->where(['p.' . $productField => 1]);
    if ($active) {
      $products->active();
      //\common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp'))
    }
    return $products->asArray(true)->all();
  }

  public function findAssignedGroupProductsCatalog(int $groupId, int $languageId, $active = false) {
    $products = Products::find()->alias('p')
        ->select(['p.products_id AS id', 'p2c.categories_id as cid'])
        ->innerJoinWith(['categoriesList p2c'], false)
        ->innerJoinWith(['descriptions pd' => function($query) use ($languageId) {
            /** @var ActiveQuery $query */
            return $query->andOnCondition(['language_id' => $languageId]);
          }], false);
    if ($model = \common\helpers\Acl::checkExtensionTableExist('UserGroupsRestrictions', 'GroupsProducts')) {
        $products->innerJoin($model::tableName() . ' p2g', 'p2g.products_id = p.products_id AND p2g.groups_id = :group_id', ['group_id' => $groupId]);
    }
    if ($active) {
      $products->active();
      //\common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp'))
    }
    return $products->asArray(true)->all();
  }

  public function findCategoryProductsKey($categoryId = 0, $languageId = 1, $active = false) {
    $products = Products::find()->alias('p')
        ->select(["CONCAT('p',p.products_id,'_',p2c.categories_id) AS 'key'", 'pd.products_name as title'])
        ->innerJoinWith(['categoriesList p2c'], false)
        ->innerJoinWith(['descriptions pd' => function($query) use ($languageId) {
        /** @var ActiveQuery $query */
        return $query->andOnCondition(['language_id' => $languageId, 'platform_id' => \common\classes\platform::currentId()]);
      }], false);
    if ($active) {
      $products->active();
      //\common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp'))
    }
    return $products->where(['p2c.categories_id' => $categoryId])->orderBy('p.sort_order, pd.products_name')->asArray(true)->all();
  }

    private function UprIdInsteadId(array $productDataArray, array $ids)
    {
        $resultProductArray = [];
        $productData = \yii\helpers\ArrayHelper::index($productDataArray,'products_id');
        foreach ($ids as $id){
            if (!isset($productData[(int)$id])) continue;

            $product = $productData[(int)$id];
            $product['products_id'] = $id;
            $resultProductArray[] = $product;
        }
        return $resultProductArray;
    }

    /**
     * !!! $products has native parent class @see ProductsQuery, to avoid conflicts, necessary check on custom interface
     * @param ActiveQuery $products
     * @param array $addSettings
     */
    private function withAdditionalSettings(ActiveQuery $products, array $addSettings)
    {
        $className = get_class($products);
        if (
            array_key_exists('settingsAdditional', $addSettings) &&
            array_key_exists($className, $addSettings['settingsAdditional'])
        ) {
            $settingsAdditional = $addSettings['settingsAdditional'][$className];
            foreach ($settingsAdditional as $method => $value) {
                if (method_exists($products, $method)) {
                    if (!is_array($value)) {
                        $value = [$value];
                    }
                    call_user_func_array([$products,$method], $value);
                }
            }
        }
    }
}
