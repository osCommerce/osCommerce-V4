<?php
namespace subdee\soapserver\tests\Controllers;

/**
 * @description Testcase for an unbounded array
 */
class UnboundedSoapController
{
    /** @var bool */
    public $enableCsrfValidation = false;

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'getUnbounded' => [
                'class' => 'subdee\soapserver\SoapAction',
                'classMap' => [
                    'UnboundedClass' => '\subdee\soapserver\tests\models\UnboundClass',
                ],
            ],
        ];
    }

    /**
     * Simple test which returns a unboundedtest
     * @return \subdee\soapserver\tests\models\UnboundedTestModel
     * @soap
     */
    public function getUnbounded()
    {
        return new \subdee\soapserver\tests\models\UnboundedTestModel();
    }
}
