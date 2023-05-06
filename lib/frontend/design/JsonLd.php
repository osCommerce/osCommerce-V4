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

namespace frontend\design;

use Yii;

class JsonLd
{
    private static $schema = [];

    private static function organization()
    {
        foreach (\common\helpers\Hooks::getList('frontend/jsonld-organization') as $filename){
            $result = include($filename);
            if ($result === false) {
                return $result;
            }
        }
        if (!self::hasValue(['Organization'])) {
            return false;
        }
        $data = Info::platformData();

        $organization = [
            '@id' => Yii::$app->urlManager->createAbsoluteUrl(''),
            'name' => $data['platform_name'],
        ];

        if ($data['organization_type']) {
            $organization['@type'] = $data['organization_type'];
        } else {
            $organization['@type'] = 'Organization';
        }

        if (is_file(DIR_FS_CATALOG . Info::themeSetting('logo', 'hide'))) {
            $organization['logo'] = Yii::$app->urlManager->createAbsoluteUrl(Info::themeSetting('logo', 'hide'));
            $organization['image'] = Yii::$app->urlManager->createAbsoluteUrl(Info::themeSetting('logo', 'hide'));
        }

        if ($data['organization_site']) {
            $organization['url'] = $data['organization_site'];
        } else {
            $organization['url'] = Yii::$app->urlManager->createAbsoluteUrl('');
        }

        if ($data['latitude'] != 0 && $data['longitude'] != 0) {
            $organization['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
            ];
        }

        if ($organization['@type'] == 'Store' || $organization['@type'] == 'LocalBusiness') {
            $customer_groups_id = (int)\Yii::$app->storage->get('customer_groups_id');
            $currencies = \Yii::$container->get('currencies');

            $productPrices = tep_db_fetch_array(tep_db_query("
            select 
                min(ppi.products_price_min) as min_price, 
                max(ppi.products_price_max) as max_price, ppi.products_tax_class_id
            from product_price_index ppi left join platforms_products pp on ppi.products_id = pp.products_id
            where 
                ppi.products_price_min > 0 and 
                pp.platform_id = '" . \common\classes\platform::currentId() . "' and 
                ppi.groups_id = '" . (int)$customer_groups_id . "' and 
                ppi.currencies_id = '" . (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True' ? (int)\Yii::$app->settings->get('currency_id') : 0) . "' 
                and ppi.products_status>0"));

            $minPrice = $currencies->display_price($productPrices['min_price'], \common\helpers\Tax::get_tax_rate($productPrices['products_tax_class_id']), 1, false);
            $maxPrice = $currencies->display_price($productPrices['max_price'], \common\helpers\Tax::get_tax_rate($productPrices['products_tax_class_id']), 1, false);

            $organization['priceRange'] = $minPrice . ' - ' . $maxPrice;
        }

        self::addData(['Organization' => $organization]);

        self::validateDataByOrganizationType($organization['@type']);
    }

    public static function validateDataByOrganizationType($type)
    {
        switch ($type) {
            case 'Organization':
            case 'Airline':
            case 'Consortium':
            case 'Corporation':
            case 'EducationalOrganization':
            case 'FundingScheme':
            case 'GovernmentOrganization':
            case 'LibrarySystem':
            case 'MedicalOrganization':
            case 'NGO':
            case 'NewsMediaOrganization':
            case 'PerformingGroup':
            case 'SportsOrganization':
            case 'WorkersUnion':
                unset(self::$schema['Organization']['openingHoursSpecification']);
                unset(self::$schema['Organization']['geo']);
                break;

            case 'Store':
            case 'LocalBusiness':
                break;

            case 'WebSite':
                unset(self::$schema['Organization']['openingHoursSpecification']);
                unset(self::$schema['Organization']['telephone']);
                unset(self::$schema['Organization']['email']);
                unset(self::$schema['Organization']['vatID']);
                unset(self::$schema['Organization']['logo']);
                unset(self::$schema['Organization']['address']);
                unset(self::$schema['Organization']['geo']);
                break;

        }
    }

    private static function product()
    {
        foreach (\common\helpers\Hooks::getList('frontend/jsonld-product') as $filename){
            $result = include($filename);
            if ($result === false) {
                return $result;
            }
        }
        if (!self::hasValue(['Product'])) {
            return false;
        }
        if (self::$schema['Product']['@type'] == 'Service') {
            unset(self::$schema['Product']['sku']);
            unset(self::$schema['Product']['gtin13']);

            if (self::hasValue(['Organization'])) {
                self::$schema['Product']['provider'] = self::$schema['Organization'];
            }
        }
    }

    public static function addData($data, $notAddIfExist = [])
    {
        if (!self::hasValue($notAddIfExist)) {
            self::$schema = array_merge_recursive(self::$schema, $data);
        }
    }

    public static function changeData($data)
    {
        foreach ($data as $key => $value) {
            self::$schema[$key] = $data[$key];
        }
    }

    public static function getData()
    {
        return self::$schema;
    }

    public static function hasValue($arr)
    {
        if (!isset($arr[0])){
            return false;
        }
        if (!$arr[0]){
            return false;
        }
        if (!isset(self::$schema[$arr[0]])) {
            return false;
        }
        if (!self::$schema[$arr[0]]) {
            return false;
        }
        if (!isset($arr[1])){
            return true;
        }
        if (!$arr[1]){
            return true;
        }
        if (!isset(self::$schema[$arr[0]][$arr[1]])) {
            return false;
        }
        if (!self::$schema[$arr[0]][$arr[1]] ) {
            return false;
        }
        if (!isset($arr[2])){
            return true;
        }
        if (!$arr[2]){
            return true;
        }
        if (!isset(self::$schema[$arr[0]][$arr[1]][$arr[2]])) {
            return false;
        }
        if (!self::$schema[$arr[0]][$arr[1]][$arr[2]] ) {
            return false;
        }
        if (!isset($arr[3])){
            return true;
        }
        if (!$arr[3]){
            return true;
        }
        if (!isset(self::$schema[$arr[0]][$arr[1]][$arr[2]][$arr[3]])) {
            return false;
        }
        if (!self::$schema[$arr[0]][$arr[1]][$arr[2]][$arr[3]] ) {
            return false;
        }
        if (!isset($arr[4])){
            return true;
        }
        if (!$arr[4]){
            return true;
        }
        if (!isset(self::$schema[$arr[0]][$arr[1]][$arr[2]][$arr[3]][$arr[4]])) {
            return false;
        }
        if (!self::$schema[$arr[0]][$arr[1]][$arr[2]][$arr[3]][$arr[4]] ) {
            return false;
        }
        return true;

    }

    public static function getJsonLd()
    {
        self::organization();
        self::product();

        $html = '';

        foreach (self::$schema as $type => $schemaArray) {
            if (!isset($schemaArray['@context']) || !$schemaArray['@context']) {
                $schemaArray['@context'] = 'https://schema.org';
            }
            if (!isset($schemaArray['@type']) || !$schemaArray['@type']) {
                $schemaArray['@type'] = $type;
            }
            $html .= '<script type="application/ld+json">' . "\n" . json_encode($schemaArray, JSON_UNESCAPED_SLASHES) . "\n" . '</script>' . "\n";
        }

        return $html;
    }

}