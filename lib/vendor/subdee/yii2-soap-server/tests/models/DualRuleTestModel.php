<?php
namespace subdee\soapserver\tests\models;

use yii\base\Model;

/**
 * @description
 * @package subdee\soapserver\tests\models
 */
class DualRuleTestModel extends Model
{
    const SCENARIO_WSDL = 'wsdl';

    /**
     * @var  date
     * @soap
     */
    public $field1;

    /**
     * @var string
     * @soap
     */
    public $field2;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['field1','field2'], 'required', 'on' => self::SCENARIO_WSDL],
            [['field2'], 'string', 'on' => self::SCENARIO_WSDL, 'length' => [2,10]],
            [['field1'], 'date', 'on' => self::SCENARIO_WSDL, 'format' => 'yyyy-MM-dd'],
        ];
    }
}
