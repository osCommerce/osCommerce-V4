<?php

declare (strict_types=1);


namespace common\services\BonusPointsService\Clear;

class YearlyCustomerRegistration implements ClearStrategy
{
    public const CLEAR_RULE = 'Yearly(Registration)';

    public function allow(string $rule): bool
    {
        return $rule === self::CLEAR_RULE;
    }

    public function getSQLWhere(): string
    {
        return "
        CASE WHEN
            DATE_FORMAT(ci.customers_info_date_account_created,'%m') = '00' OR DATE_FORMAT(ci.customers_info_date_account_created,'%d')= '00' OR ci.customers_info_date_account_created IS NULL THEN '01-01'= DATE_FORMAT(NOW(),'%m-%d') 
        ELSE 
	        DATE_FORMAT(ci.customers_info_date_account_created,'%m-%d') = DATE_FORMAT(NOW(),'%m-%d')
        END";
    }
}
