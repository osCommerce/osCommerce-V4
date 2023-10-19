<?php
namespace subdee\soapserver\tests\Controllers;

/**
 * @description Very simple testcase
 */
class SoapController
{
    /** @var bool */
    public $enableCsrfValidation = false;

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'hello' => [
                'class' => 'subdee\soapserver\SoapAction'
            ],
        ];
    }

    /**
     * Returns hello and the name that you gave
     *
     * @param string $name Your name
     * @return string
     * @soap
     */
    public function getHello($name)
    {
        return 'Hello ' . $name;
    }
}
