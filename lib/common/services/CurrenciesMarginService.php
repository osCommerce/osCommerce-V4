<?php

namespace common\services;

use common\models\PlatformsCurrenciesMargin;
use common\models\repositories\CurrenciesMarginRepositoty;

class CurrenciesMarginService 
{
    private $currenciesMarginRepositoty;
    
    public function __construct(CurrenciesMarginRepositoty $currenciesMarginRepositoty) 
    {
        $this->currenciesMarginRepositoty = $currenciesMarginRepositoty;
    }
    
    public function deleteCurrenciesMargin(int $platformId)
    {
        $this->currenciesMarginRepositoty->deleteCurrencies($platformId);
    }
    
    public function saveCurrenciesMargin(array $currency_margin, int $platformId)
    {
        foreach ($currency_margin as $currencyId => $marginData) {
            $customCurrencyValue = isset($marginData['use_default']) ? 0 : 1;
            $currencyValue = floatval($marginData['currency_value']);
            $marginValue = $marginData['value'];
            $marginType = $marginData['type'];
            $platCurrenMargin = PlatformsCurrenciesMargin::create($platformId, $currencyId, $customCurrencyValue, $currencyValue, $marginValue, $marginType);
            $this->currenciesMarginRepositoty->save($platCurrenMargin);
        }
    }
}
