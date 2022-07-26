<?php

declare (strict_types=1);


namespace common\services\BonusPointsService\Clear;


use common\services\FileService;

class ClearStrategyFactory
{
    /** @var FileService */
    private $fileService;

    public function __construct(
        FileService $fileService
    )
    {
        $this->fileService = $fileService;
    }

    public function createFromRule(string $rule = 'Never'): ClearStrategy
    {
        $strategies = $this->getStrategies();
        if (!$strategies) {
            throw new \RuntimeException('Clear Strategies not found');
        }
        foreach ($strategies as $strategy) {
            if ($strategy::CLEAR_RULE === $rule) {
                return \Yii::createObject($strategy);
            }
        }
        throw new \RuntimeException('Allowed Clear Strategies not found');
    }

    private function getStrategies()
    {
        static $classes = null;
        if ($classes !== null) {
            return $classes;
        }
        $classes = [];
        $classGenerator = $this->fileService->getClassesIterator([__DIR__], static function (string $className){
            try{
                $object = \Yii::createObject($className);
                return  $object instanceof ClearStrategy
                    ? $className
                    : false;
            } catch (\Exception $e) {
                return false;
            }
        }, 0);
        foreach ($classGenerator as $class) {
            $classes[] = $class;
        }
        return $classes;
    }
}
