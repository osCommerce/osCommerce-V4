<?php
namespace subdee\soapserver\tests;

use Codeception\TestCase\Test;
use subdee\soapserver\tests\Controllers\SoapController;
use subdee\soapserver\SoapService;

/**
 * @description Test for SoapService class
 * @package subdee\soapserver\tests
 */
class SoapServiceTest extends Test
{
    public function testGenerateWsdl()
    {
        $controller = new SoapController();
        $soapService = new SoapService($controller, 'http://wsdl-url/', 'http://test-url/');
        $wsdl = $soapService->generateWsdl();

        $xml = simplexml_load_string($wsdl);
        $this->assertTrue($xml instanceOf \SimpleXMLElement);
        $this->assertSame((string)$xml->getName(), 'definitions');

        $operation = $xml->xpath('//wsdl:operation[@name="getHello"]');
        $this->assertTrue($xml instanceOf \SimpleXMLElement);

        $address = $xml->xpath('//soap:address');
        $location = (string)$address[0]->attributes()->location;
        $this->assertEquals('http://test-url/', $location);
    }

    /**
     * We want to know for sure our new feature for ehm. features works
     */
    public function testFeatures()
    {
        $controller = new SoapController();

        $soapService = new SoapService($controller, 'http://wsdl-url', 'http://test-url');
        $soapService->features = [SOAP_SINGLE_ELEMENT_ARRAYS];

        $reflection = new \ReflectionClass($soapService);

        $method = $reflection->getMethod('getOptions');
        $method->setAccessible(true);
        $result = $method->invoke($soapService);

        $this->assertEquals(1, $result['features'][0]);
    }

}
