<?php
namespace subdee\soapserver\tests\models;

use yii\base\Model;

/**
 * @description RuleTest model
 */
class RulesTestModel extends Model
{

    const SCENARIO_WSDL = 'wsdl';

    /**
     * @var integer
     * @soap
     */
    public $integerValue;

    /**
     * @var string
     * @soap
     */
    public $stringValue;

    /**
     * @var integer
     * @soap
     */
    public $rangeValue;

    /**
     * @var string
     * @soap
     */
    public $regExpValue;

    /**
     * @var date
     * @soap
     */
    public $dateValue;

    /**
     * @var string
     * @soap
     */
    public $emailValue;

    /**
     * @var double
     * @soap
     */
    public $numberValue;

    /**
     * @var subdee\soapserver\tests\models\UnboundClass[] {minOccurs = 0, maxOccurs = unbounded}
     * @soap
     */
    public $unboundClass = [];

    /**
     * @var integer[]
     * @soap
     */
    public $integerArray;

    /**
     * @var string
     * @soap
     */
    public $maxLengthValue;

    /**
     * @var double
     * @soap
     */
    public $largeNumberValue;

    /**
     * @var double
     * @soap
     */
    public $largeNumberNoDecimalValue;

    /**
     * List of rules for this class
     * @return array
     */
    public function rules()
    {
        return [
            ['integerValue', 'integer', 'on' => self::SCENARIO_WSDL, 'min' => 1, 'integerOnly' => true, 'max' => 9999],
            [['stringValue', 'regExpValue'], 'trim', 'on' => self::SCENARIO_WSDL],
            ['stringValue', 'string', 'on' => self::SCENARIO_WSDL, 'length' => [13, 37]],
            ['emailValue', 'email', 'on' => self::SCENARIO_WSDL, 'allowName' => true],
            ['rangeValue', 'in', 'on' => self::SCENARIO_WSDL, 'range' => [1, 2, 3]],
            ['regExpValue', 'match', 'on' => self::SCENARIO_WSDL, 'pattern' => '/[a-z]*/i'],
            ['regExpValue', 'InvalidValidator', 'on' => self::SCENARIO_WSDL],
            ['numberValue', 'number', 'on' => self::SCENARIO_WSDL, 'integerOnly' => false, 'min' => 1.11, 'max' => 999],
            ['largeNumberNoDecimalValue', 'number', 'on' => self::SCENARIO_WSDL, 'integerOnly' => false, 'min' => 1, 'max' => 999],
            ['largeNumberValue', 'number', 'on' => self::SCENARIO_WSDL, 'integerOnly' => false, 'min' => 1.11, 'max' => '99999999999999999999.999'],
            ['regExpValue', 'InvalidValidator', 'on' => self::SCENARIO_WSDL],
            [['dateValue'], 'date', 'on' => self::SCENARIO_WSDL],
            ['maxLengthValue', 'string', 'on' => self::SCENARIO_WSDL, 'length' => 1337],
        ];
    }
}
