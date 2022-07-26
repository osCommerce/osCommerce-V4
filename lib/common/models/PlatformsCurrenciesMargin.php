<?php

namespace common\models;

use yii\db\ActiveRecord;

class PlatformsCurrenciesMargin extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{platform_currencies_margin}}';
    }
    
    public static function create(int $platformId, int $currenciesId, $useCustomCurrencyValue, $currencyValue, $marginValue, $marginType)
    {
        $platformsCurrenciesMargin = new static();
        $platformsCurrenciesMargin->platform_id	 = $platformId;
        $platformsCurrenciesMargin->currencies_id = $currenciesId;
        $platformsCurrenciesMargin->use_custom_currency_value = $useCustomCurrencyValue;
        $platformsCurrenciesMargin->currency_value = $currencyValue;
        $platformsCurrenciesMargin->margin_value = $marginValue;
        $platformsCurrenciesMargin->margin_type = $marginType;
        
        return $platformsCurrenciesMargin;
    }
}
