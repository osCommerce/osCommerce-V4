<?php

namespace common\models\repositories;

use common\models\PlatformsCurrenciesMargin;

class CurrenciesMarginRepositoty
{
    public function deleteCurrencies(int $platformId)
    {
        return PlatformsCurrenciesMargin::deleteAll(['platform_id' => $platformId]);
    }
    
    public function save(PlatformsCurrenciesMargin $platCurrenMargin)
    {
        if (!$platCurrenMargin->save()) {
            throw new \RuntimeException('Saving error.');
        }
    }
}
