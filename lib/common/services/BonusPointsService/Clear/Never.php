<?php

declare (strict_types=1);


namespace common\services\BonusPointsService\Clear;

class Never implements ClearStrategy
{
    public const CLEAR_RULE = 'Never';

    public function allow(string $rule): bool
    {
        return $rule === self::CLEAR_RULE;
    }

    public function getSQLWhere(): string
    {
        return '0';
    }
}
