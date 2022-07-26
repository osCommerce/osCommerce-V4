<?php
namespace subdee\soapserver\tests\Controllers;

/**
 * @description test for StringArrayModel
 */
class StringSoapController
{
    /** @var bool */
    public $enableCsrfValidation = false;

    /**
     * List of all actions which are supported by this soapserver
     * @return array
     */
    public function actions()
    {
        return [
            'getString' => [
                'class' => 'subdee\soapserver\SoapAction',
                'classMap' => [
                    'StringArrayModel' => '\sudbee\soapserver\tests\models\StringArrayModel',
                ],
            ],
        ];
    }

    /**
     * Simple test which returns a StringArrayModel in order to see how the wsdl pans out
     * @return \subdee\soapserver\tests\models\StringArrayModel
     * @soap
     */
    public function getRules()
    {
        return new \subdee\soapserver\tests\models\StringArrayModel();
    }
}
