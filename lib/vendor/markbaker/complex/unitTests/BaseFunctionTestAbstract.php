<?php

namespace Complex;

abstract class BaseFunctionTestAbstract extends BaseTestAbstract
{
    public function testInvalidArgument()
    {
        $this->expectException(\Exception::class);

        $invalidComplex = '*** INVALID ***';
        call_user_func([ __NAMESPACE__ . '\\Functions', static::$functionName], $invalidComplex, 1);
    }
}
