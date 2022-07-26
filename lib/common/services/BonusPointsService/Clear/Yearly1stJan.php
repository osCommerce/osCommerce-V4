<?php

declare (strict_types=1);


namespace common\services\BonusPointsService\Clear;

class Yearly1StJan implements ClearStrategy
{
    public const CLEAR_RULE = 'Yearly(1st Jan)';

    public function allow(string $rule): bool
    {
        return $rule === self::CLEAR_RULE;
    }

    public function getSQLWhere(): string
    {
        return "'01-01'= DATE_FORMAT(NOW(),'%m-%d')";
    }
}
