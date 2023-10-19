<?php
namespace subdee\soapserver\tests;

use Codeception\TestCase\Test;
use subdee\soapserver\SoapService;
use subdee\soapserver\tests\Controllers\DualRuleSoapController;

/**
 * @description Test for double rules on a field, eg. required and date, or required and number
 * @package subdee\soapserver\tests
 */
class DualRuleTest extends Test
{
    /**
     * Test to see how the xml looks when we use some simpleTypes
     */
    public function testGeneratedXmlForDoubleValidators()
    {
        $controller = new DualRuleSoapController();
        $soapService = new SoapService($controller, 'http://wsdl-url/', 'http://test-url/');
        $wsdl = $soapService->generateWsdl();

        $xml = simplexml_load_string($wsdl);
        $this->assertTrue($xml instanceof \SimpleXMLElement);
        $this->assertSame((string)$xml->getName(), 'definitions');

        $patternValue = $xml->xpath('//xsd:simpleType/xsd:restriction/xsd:pattern/@value');
        $this->assertEquals('.{2,10}', $patternValue[0]['value']);

        $typeValue = $xml->xpath('//xsd:schema/xsd:complexType/xsd:sequence/xsd:element[@name="field2"]');
        $this->assertEquals('tns:dualruletestmodelField2', $typeValue[0]['type']);
    }
}
