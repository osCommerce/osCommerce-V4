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

namespace common\helpers;

use common\classes\SeoMetaFormatInterface;
use common\models\ProductsDescription;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class Seo {

    public static function get_seo_page_path($id, $platform_id) {
        global $languages_id;
        $info_query = tep_db_query("select seo_page_name from " . TABLE_INFORMATION . " where languages_id = '" . (int) $languages_id . "' and information_id = '" . (int) $id . "' and platform_id = '" . (int) $platform_id . "' and affiliate_id = 0");
        $info = tep_db_fetch_array($info_query);
        return $info['seo_page_name'];
    }

    public static function transliterate($input)
    {
        $gost = array(
            "Є"=>"YE","І"=>"I","Ѓ"=>"G","і"=>"i","№"=>"-","є"=>"ye","ѓ"=>"g",
            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D",
            "Е"=>"E","Ё"=>"YO","Ж"=>"ZH",
            "З"=>"Z","И"=>"I","Й"=>"J","К"=>"K","Л"=>"L",
            "М"=>"M","Н"=>"N","О"=>"O","П"=>"P","Р"=>"R",
            "С"=>"S","Т"=>"T","У"=>"U","Ф"=>"F","Х"=>"X",
            "Ц"=>"C","Ч"=>"CH","Ш"=>"SH","Щ"=>"SHH","Ъ"=>"'",
            "Ы"=>"Y","Ь"=>"","Э"=>"E","Ю"=>"YU","Я"=>"YA",
            "а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d",
            "е"=>"e","ё"=>"yo","ж"=>"zh",
            "з"=>"z","и"=>"i","й"=>"j","к"=>"k","л"=>"l",
            "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
            "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"x",
            "ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"shh","ъ"=>"",
            "ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
            " "=>"-","—"=>"-",","=>"-","!"=>"-","@"=>"-",
            "#"=>"-","$"=>"","%"=>"","^"=>"","&"=>"","*"=>"",
            "("=>"",")"=>"","+"=>"","="=>"",";"=>"",":"=>"",
            "'"=>"",'"'=>"","~"=>"","`"=>"","?"=>"","/"=>"",
            "\\"=>"","["=>"","]"=>"","{"=>"", "}"=>"","|"=>"",
            "."=>"-", "Ä"=>"A", "ä"=>"a", "Ǟ"=>"A", "ǟ"=>"a",
            "Ë"=>"E", "ë"=>"e", "Ḧ"=>"H", "ḧ"=>"h", "Ï"=>"I",
            "ï"=>"i", "Ḯ"=>"I", "ḯ"=>"i", "Ö"=>"O", "ö"=>"o",
            "Ȫ"=>"O", "ȫ"=>"o", "Ṏ"=>"O", "ṏ"=>"o", "ẗ"=>"t",
            "Ü"=>"U", "ü"=>"u", "Ǖ"=>"U", "ǖ"=>"u", "Ǘ"=>"U",
            "ǘ"=>"u", "Ǚ"=>"U", "ǚ"=>"u", "Ǜ"=>"U", "ǜ"=>"u",
            "Ṳ"=>"U", "ṳ"=>"u", "Ṻ"=>"U", "ṻ"=>"u", "Ẅ"=>"W",
            "ẅ"=>"w", "Ẍ"=>"X", "ẍ"=>"x", "Ÿ"=>"Y", "ÿ"=>"y",
            "–"=>"-", "«"=>"", "»"=>"");

        $input = strtr($input, $gost);
        $input = preg_replace("/(-){1,}/", "-", $input);
        if (substr($input, -1) == '-') $input = substr($input, 0, -1);
        $input = \yii\helpers\Inflector::slug($input);
        return $input;
    }

    public static function makeSlug($string)
    {
        $seo_name = preg_replace("/(%[\da-f]{2}|\+|_)/i", '-', urlencode(self::transliterate($string)));
        $seo_name = preg_replace('/-{2,}/','-',$seo_name);
        return strtolower($seo_name);
    }

    public static function makeProductSlug($descriptionData, $productData)
    {
        if ( is_object($descriptionData) && $descriptionData instanceof ActiveRecord ){
            $description = $descriptionData->getAttributes(['products_seo_page_name','products_name']);
        }else{
            $description = [
                'products_seo_page_name' => $descriptionData['products_seo_page_name'],
                'products_name' => $descriptionData['products_name'],
            ];
        }
        if ( is_object($productData) && $productData instanceof ActiveRecord ) {
            $product = $productData->getAttributes(['products_id', 'products_model']);
        }elseif (!is_array($productData)) {
            $product = \common\models\Products::find()
                ->where(['products_id'=>(int)$productData])
                ->select(['products_id','products_model'])
                ->asArray()
                ->one();
        }else{
            $product = [
                'products_id' => $productData['products_id'],
                'products_model' => $productData['products_model'],
            ];
        }

        $products_seo_page_name = $description['products_seo_page_name'];

        if ( empty($products_seo_page_name) || static::isProductSeoPageDuplicated($products_seo_page_name, $product['products_id']) ) {
            $slugVariants = [];
            if (!empty($description['products_name'])) {
                $slugVariants[] = static::makeSlug($description['products_name']);
                if (!empty($product['products_model'])) {
                    $slugVariants[] = static::makeSlug($product['products_model'] . '-' . $description['products_name']);
                    $slugVariants[] = static::makeSlug($product['products_model'] . '-' . $description['products_name'] . '-' . $product['products_id']);
                }
                $slugVariants[] = static::makeSlug($description['products_name'] . '-' . $product['products_id']);
            } elseif (!empty($product['products_model'])) {
                $slugVariants[] = static::makeSlug($product['products_model']);
                $slugVariants[] = static::makeSlug($product['products_model'] . '-' . $product['products_id']);
            }
            $slugVariants[] = static::makeSlug($product['products_id']);

            $usedVariants = ArrayHelper::map(ProductsDescription::find()
                ->where(['!=', 'products_id', (int)$product['products_id']])
                ->andWhere(['IN', 'products_seo_page_name', $slugVariants])
                ->select(['products_seo_page_name', new Expression('COUNT(*) AS use_count')])
                ->groupBy(['products_seo_page_name'])
                ->asArray()
                ->all(), 'products_seo_page_name', 'use_count');

            foreach ($slugVariants as $slugVariant) {
                if (!isset($usedVariants[$slugVariant])) {
                    $products_seo_page_name = $slugVariant;
                    break;
                }
            }
        }

        return $products_seo_page_name;
    }

    protected static function isProductSeoPageDuplicated($seo_page_name, $excludeProductId)
    {
        $matchedSeoCount = ProductsDescription::find()
            ->where(['!=', 'products_id', (int)$excludeProductId])
            ->andWhere(['products_seo_page_name'=>$seo_page_name])
            ->count();
        return $matchedSeoCount>0;
    }

    /**
     * @param $propertyData
     * @return string
     */
    public static function makePropertySlug($propertyData)
    {
        $prop_name = static::makeSlug($propertyData['properties_name']);
        $exist_count = \common\models\PropertiesDescription::find()
            ->where(['properties_seo_page_name'=>$prop_name])
            ->andWhere(['!=','properties_id', $propertyData['properties_id']])
            ->count('distinct properties_id');
        if ( $exist_count>0 ){
            $prop_name = static::makeSlug($propertyData['properties_name'].'-'.(int)$exist_count);
            $exist_count = \common\models\PropertiesDescription::find()
                ->where(['properties_seo_page_name'=>$prop_name])
                ->andWhere(['!=','properties_id', $propertyData['properties_id']])
                ->count('distinct properties_id');
            if ( $exist_count>0 ){
                $prop_name = static::makeSlug($propertyData['properties_name'].'-'.(int)$propertyData['properties_id']);
            }
        }
        return $prop_name;
    }

    /**
     * @param $propertyData
     * @return string
     */
    public static function makePropertyValueSlug($propertyValueData)
    {
        $prop_value_slug = static::makeSlug($propertyValueData['values_text']);
        $exist_count = \common\models\PropertiesValues::find()
            ->where(['values_seo_page_name'=>$prop_value_slug])
            ->andFilterWhere(['!=','values_id', $propertyValueData['values_id']??null])
            ->andFilterWhere(['properties_id' => $propertyValueData['properties_id']??null])
            ->andFilterWhere(['language_id' => $propertyValueData['language_id']??null])
            ->count('distinct values_id');
        if ( $exist_count>0 ){
            $prop_value_slug = static::makeSlug($propertyValueData['values_text'].'-'.(int)$exist_count);
            $exist_count = \common\models\PropertiesValues::find()
                ->where(['values_seo_page_name'=>$prop_value_slug])
                ->andFilterWhere(['!=','values_id', $propertyValueData['values_id']??null])
                ->andFilterWhere(['properties_id' => $propertyValueData['properties_id']??null])
                ->andFilterWhere(['language_id' => $propertyValueData['language_id']??null])
                ->count('distinct values_id');
            if ( $exist_count>0 ){
                $prop_value_slug = static::makeSlug($propertyValueData['values_text'].'-'.(int)($propertyValueData['values_id']??99));
            }
        }
        return $prop_value_slug;
    }

    /*public static function getSeoUrlsByRoute($route)
    {
        $urls = [];
        switch ($route) {
            case 'catalog/index':
                global $current_category_id;
                $query = tep_db_query("select categories_seo_page_name, language_id from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" .(int)$current_category_id . "' and platform_id = 0");
                if (tep_db_num_rows($query)){
                    while($row = tep_db_fetch_array($query)){
                        $urls[$row['language_id']] = $row['categories_seo_page_name'];
                    }
                }
                break;
            case 'catalog/product':
                $query = tep_db_query("select products_seo_page_name, language_id from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" .(int)$_GET['products_id'] . "' and platform_id = 0");
                if (tep_db_num_rows($query)){
                    while($row = tep_db_fetch_array($query)){
                        $urls[$row['language_id']] = $row['products_seo_page_name'];
                    }
                }
                break;
            case 'info/custom':
               
                break;
        }
        return $urls;
    }*/

    public static function setPageMetaTitle($titleConst, SeoMetaFormatInterface $formatter)
    {
        $title = $formatter->ownMetaTitle();
        if ( !empty($title) ) {
            \Yii::$app->getView()->title = $title;
        }else{
            if ( !is_array($titleConst) ) $titleConst = [$titleConst];
            foreach ($titleConst as $titleConstSingle){
                if ( defined($titleConstSingle) && constant($titleConstSingle)!='' ){
                    $meta_key = constant($titleConstSingle);
                    if ( strpos($meta_key,'##')!==false && preg_match_all('/(##([a-z_]+)##)/i', $meta_key, $match) ){
                        foreach( $match[1] as $idx=>$replaceStr ){
                            if (defined($match[2][$idx])){
                                $meta_key = str_replace($replaceStr, constant($match[2][$idx]), $meta_key);
                            }else{
                                $meta_key = str_replace($replaceStr, $formatter->getMetaFormatKey($match[2][$idx]), $meta_key);
                            }
                        }
                    }
                    \Yii::$app->getView()->title = $meta_key;
                    break;
                }
            }
        }
    }

    public static function setPageMetaDescription($descriptionConst, SeoMetaFormatInterface $formatter)
    {
        $meta_desc = $formatter->ownMetaDescription();
        if ( !empty($meta_desc) ) {
            \Yii::$app->getView()->registerMetaTag([
                'name' => 'Description',
                'content' => $meta_desc,
            ], 'Description');
        }else{
            if ( !is_array($descriptionConst) ) $descriptionConst = [$descriptionConst];
            foreach ($descriptionConst as $singleConst){
                if ( defined($singleConst) && constant($singleConst)!='' ){
                    $meta_desc = constant($singleConst);
                    if ( strpos($meta_desc,'##')!==false && preg_match_all('/(##([a-z_]+)##)/i', $meta_desc, $match) ){
                        foreach( $match[1] as $idx=>$replaceStr ){
                            if (defined($match[2][$idx])){
                                $meta_desc = str_replace($replaceStr, constant($match[2][$idx]), $meta_desc);
                            }else{
                                $meta_desc = str_replace($replaceStr, $formatter->getMetaFormatKey($match[2][$idx]), $meta_desc);
                            }
                        }
                    }
                    \Yii::$app->getView()->registerMetaTag([
                        'name' => 'Description',
                        'content' => $meta_desc,
                    ], 'Description');
                    break;
                }
            }
        }
    }

    public static function setPageMeta($title_const, $description_const, SeoMetaFormatInterface $formatter)
    {
        static::setPageMetaTitle($title_const, $formatter);
        static::setPageMetaDescription($description_const, $formatter);
    }

    public static function getNoindexTag($noindex_option = 0, $nofollow_option = 0) 
    {
        if ($noindex_option == 1) {
            $text = 'NOINDEX';
        } else {
            $text = 'INDEX';
        }
        $text .= ', ';
        if ($nofollow_option == 1) {
            $text .= 'NOFOLLOW';
        } else {
            $text .= 'FOLLOW';
        }
        return $text;
    }
    
    public static function showNoindexMetaTag($noindex_option = 0, $nofollow_option = 0) {
        $content = self::getNoindexTag($noindex_option, $nofollow_option);

        \Yii::$app->getView()->registerMetaTag([
            'name' => 'Robots',
            'content' => $content
                ], 'Robots');
    }

    public static function getMetaDefaultBreadcrumb($action)
    {
        $meta_tag_constant_name = 'DEFAULT_BREADCRUMB_'.strtoupper(preg_replace('/[-\/]/','_', $action));
        if ( defined($meta_tag_constant_name) ) {
            return constant($meta_tag_constant_name);
        }
        return '';
    }

}
