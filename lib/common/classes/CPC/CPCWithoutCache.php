<?php

namespace common\classes\CPC;

class CPCWithoutCache extends CPCBase
{
    /**
     * @inheritDoc
     */
    public static function getCategories($categoriesIds, $platformId, $groupId = 0): array
    {
        return static::runQuery($platformId, $groupId, $categoriesIds);
    }

}