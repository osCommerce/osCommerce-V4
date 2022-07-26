<?php
namespace subdee\soapserver\tests;

use Codeception\TestCase\Test;
use subdee\soapserver\tests\Controllers\SoapController;
use subdee\soapserver\WsdlGenerator;

/**
 * @description WSDL Generator Test
 */
class WsdlGeneratorTest extends Test
{
    public function testGenerateWsdl()
    {
        $wsdlGenerator = new WsdlGenerator();
        $controller = new SoapController();
        $wsdl = $wsdlGenerator->generateWsdl(get_class($controller), 'http://test-url/');

        $xml = simplexml_load_string($wsdl);
        $this->assertTrue($xml instanceOf \SimpleXMLElement);
        $this->assertSame((string)$xml->getName(), 'definitions');

        $operation = $xml->xpath('//wsdl:operation[@name="getHello"]');
        $this->assertTrue($xml instanceOf \SimpleXMLElement);

        $address = $xml->xpath('//soap:address');
        $location = (string)$address[0]->attributes()->location;
        $this->assertEquals('http://test-url/', $location);

    }

}
