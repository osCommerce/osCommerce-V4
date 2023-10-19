<?php
namespace subdee\soapserver\tests;

use Codeception\TestCase\Test;
use subdee\soapserver\SoapService;
use subdee\soapserver\tests\Controllers\RulesSoapController;
use subdee\soapserver\WsdlGenerator;

/**
 * @description RulesTest
 * @package subdee\soapserver\tests
 */
class RulesTest extends Test
{
    /**
     * Test to see if we see all validators (and some get ignored,just like we want)
     */
    public function testValidatorsProcessor()
    {
        $wsdlGenerator = new WsdlGenerator();

        $validators = $wsdlGenerator->parseYiiValidators('subdee\soapserver\tests\models\RulesTestModel');

        $this->assertArrayHasKey('integerValue', $validators);
        $this->assertArrayHasKey('stringValue', $validators);

        $this->assertArrayHasKey('validator', $validators['integerValue'][0]);

        $this->assertArrayNotHasKey('on', $validators['regExpValue'][0]['parameters']);
        $this->assertEquals('trim', $validators['regExpValue'][0]['validator']);

        $this->assertEquals('/[a-z]*/i', $validators['regExpValue'][1]['parameters']['pattern']);

        $this->assertNotContains('InvalidValidator', $validators['regExpValue']);
    }

    /**
     * Test to see how the xml looks when we use some simpleTypes
     */
    public function testGeneratedXmlForSimpleTypes()
    {
        $controller = new RulesSoapController();
        $soapService = new SoapService($controller, 'http://wsdl-url/', 'http://test-url/');
        $wsdl = $soapService->generateWsdl();

        $xml = simplexml_load_string($wsdl);
        $this->assertTrue($xml instanceof \SimpleXMLElement);
        $this->assertSame((string)$xml->getName(), 'definitions');

        $rulesValue = $xml->xpath('//xsd:simpleType[@name="rulestestmodelIntegerValue"]');
        $this->assertTrue($rulesValue[0] instanceof \SimpleXMLElement);

        $patternValue = $xml->xpath('//xsd:simpleType/xsd:restriction/xsd:pattern');
        $this->assertEquals('.{13,37}', $patternValue[0]['value']);

        $tokenValue = $xml->xpath('//xsd:simpleType/xsd:restriction/xsd:token');
        $this->assertEquals('[a-z]*', $tokenValue[0]['value']);

        $fractionDigitValue = $xml->xpath('//xsd:simpleType[@name="rulestestmodelNumberValue"]/xsd:restriction/xsd:fractionDigits');
        $this->assertEquals('2', $fractionDigitValue[0]['value']);

        $numbersBehindDecimalValue = $xml->xpath('//xsd:simpleType[@name="rulestestmodelNumberValue"]/xsd:restriction/xsd:minInclusive');
        $this->assertEquals('1.11', $numbersBehindDecimalValue[0]['value']);

        $largeNumbersBehindDecimalValue = $xml->xpath('//xsd:simpleType[@name="rulestestmodelLargeNumberValue"]/xsd:restriction/xsd:maxInclusive');
        $this->assertEquals('99999999999999999999.999', $largeNumbersBehindDecimalValue[0]['value']);

        $largeNumbersNoBehindDecimalValue = $xml->xpath('//xsd:simpleType[@name="rulestestmodelLargeNumberNoDecimalValue"]/xsd:restriction/xsd:maxInclusive');

        $this->assertEquals('999', $largeNumbersNoBehindDecimalValue[0]['value']);
    }
}
