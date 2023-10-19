<?php
namespace subdee\soapserver\tests\models;

/**
 * @description Unbounded test model
 * @package subdee\soapserver\tests\models
 */
class UnboundedTestModel
{
    /**
     * * @var UnboundClass[] {nillable = 0, minOccurs=1, maxOccurs = unbounded}
     */
    public $test = [];
}
