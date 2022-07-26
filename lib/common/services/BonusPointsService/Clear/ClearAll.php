<?php

declare (strict_types=1);


namespace common\services\BonusPointsService\Clear;

class ClearAll implements ClearStrategy
{
    public const CLEAR_RULE = 'All';

    public function allow(string $rule): bool
    {
        return $rule === self::CLEAR_ALL;
    }

    public function getSQLWhere(): string
    {
        return 'customers_id > 1';
    }
}
