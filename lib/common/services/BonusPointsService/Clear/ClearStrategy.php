<?php

declare (strict_types=1);


namespace common\services\BonusPointsService\Clear;


interface ClearStrategy
{
    public function allow(string $rule): bool;
    public function getSQLWhere(): string;
}
