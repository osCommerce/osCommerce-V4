<?php
namespace subdee\soapserver\tests;

use Codeception\TestCase\Test;
use subdee\soapserver\tests\Controllers\StringSoapController;
use subdee\soapserver\WsdlGenerator;

/**
 * @description Test for string arrays
 * @package subdee\soapserver\tests
 */
class StringArrayTest extends Test
{
    /**
     * Something went wrong using array notation in the model code
     */
    public function testStringArray()
    {
        $wsdlGenerator = new WsdlGenerator();
        $controller = new StringSoapController();
        $wsdl = $wsdlGenerator->generateWsdl(get_class($controller), 'http://test-url/');

        $xml = simplexml_load_string($wsdl);

        $this->assertTrue($xml instanceOf \SimpleXMLElement);
        /** @var \SimpleXMLElement[] $patternValue */
        $patternValue = $xml->xpath('///xsd:complexContent/xsd:restriction/xsd:attribute');

        $this->assertEquals('soap-enc:arrayType', $patternValue[0]->attributes()->ref);
    }
}