<?php
namespace subdee\soapserver\tests;

use Codeception\TestCase\Test;
use subdee\soapserver\Validators\IntegerType;
use yii\validators\NumberValidator;
use yii\i18n\Formatter;

/**
 * @description Test for IntegerTypeValidator
 * @package subdee\soapserver\tests
 */
class IntegerTypeValidatorTest extends Test
{
    /** @var \subdee\soapserver\Validators\SimpleType */
    private $validator;

    public function setUp()
    {
        $data = ['parameters' =>
            [
                'min' => 5,
                'max' => 10
            ]
        ];
        $this->validator = new IntegerType($data);

        new \yii\console\Application(\yii\helpers\ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'timeZone' => 'UTC',
            'language' => 'en-EN',
            'vendorPath' => __DIR__ . '/../../vendor/',
        ], []));

        $this->formatter = new Formatter(['locale' => 'en-US']);

        return parent::setUp();
    }

    public function testGeneratedName()
    {
        $this->assertEquals('integer', $this->validator->getName());
    }


    public function testVeryLargeNumber()
    {
        $data = ['parameters' =>
            [
                'min' => 5,
                'max' => '99999999999999999999'
            ]
        ];
        $this->validator = new IntegerType($data);
        $simpleTypeData = $this->validator->generateSimpleType();
        $this->assertEquals('99999999999999999999', $simpleTypeData['restriction']['maxInclusive']);
    }

    public function testGeneratedSimpleTypeData()
    {
        $simpleTypeData = $this->validator->generateSimpleType();
        $this->assertInstanceOf('subdee\soapserver\Validators\IntegerType', $this->validator);
        $this->assertArrayHasKey('restriction', $simpleTypeData);
        $this->assertArrayHasKey('minInclusive', $simpleTypeData['restriction']);
    }

    public function testVeryLargeInteger()
    {
        $val = new NumberValidator(['min' => 1, 'max' => '99999999999999999999']);
        $this->assertTrue($val->validate(30));

        $this->assertTrue($val->validate(100000));
    }
}
