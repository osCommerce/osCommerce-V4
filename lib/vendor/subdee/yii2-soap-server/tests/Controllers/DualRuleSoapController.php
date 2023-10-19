<?php

namespace subdee\soapserver\tests\Controllers;

/**
 * @description Test soap controller for DualRule test
 * @package subdee\soapserver\tests\Controllers
 */
class DualRuleSoapController
{
    /** @var bool  */
    public $enableCsrfValidation = false;

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'getTest' => [
                'class' => 'subdee\soapserver\SoapAction',
                'classMap' => [
                    'DualRuleTestModel' => '\sudbee\soapserver\tests\models\DualRuleTestModel',
                ],
            ],
        ];
    }

    /**
     * Simple test which returns a RulesTestModel in order to see how the wsdl pans out
     * @return \subdee\soapserver\tests\models\DualRuleTestModel
     * @soap
     */
    public function getTest()
    {
        return new \subdee\soapserver\tests\models\DualRuleTestModel();
    }
}
