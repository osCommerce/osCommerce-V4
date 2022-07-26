<?php
namespace subdee\soapserver\Validators;

/**
 * @description Validator for explicit Doubles
 * @package subdee\soapserver\Validators
 */
class DoubleType extends SimpleType
{
    private $fractionDigits;

    /**
     * DoubleType constructor.
     * @param array $data
     * @param int $fractionDigits
     */
    public function __construct(array $data, $fractionDigits = 0)
    {
        parent::__construct($data);
        $this->fractionDigits = $fractionDigits;
    }

    /**
     * Generates a Integer wsdl array
     * @return array
     */
    public function generateSimpleType()
    {
        $simpleType = [];

        if (array_key_exists('min', $this->data['parameters'])) {
            $minInclusive = $this->data['parameters']['min'];
            $simpleType['restriction']['minInclusive'] = $minInclusive;
            $simpleType['restriction']['fractionDigits'] = $this->fractionDigits;
        }
        if (array_key_exists('max', $this->data['parameters'])) {
            $maxInclusive = $this->data['parameters']['max'];
            $simpleType['restriction']['maxInclusive'] = $maxInclusive;
            $simpleType['restriction']['fractionDigits'] = $this->fractionDigits;
        }

        $simpleType['restriction']['name'] = $this->getName();


        return $simpleType;
    }

    /**
     * Generates a domElement and inserts it into the given DomDocument
     * @param \DOMDocument $dom
     * @param string $fieldName
     * @return \DOMElement $dom
     */
    public function generateXsd(\DOMDocument $dom, $fieldName)
    {
        $simpleTypeElement = $dom->createElement('xsd:simpleType');
        $simpleTypeElement->setAttribute('name', $fieldName);

        $restriction = $dom->createElement('xsd:restriction');
        $restriction->setAttribute('base', 'xsd:decimal');

        $simpleType = $this->generateSimpleType();

        if (array_key_exists('minInclusive', $simpleType['restriction'])) {
            $minInclusive = $dom->createElement('xsd:minInclusive');
            $minInclusive->setAttribute('value', $simpleType['restriction']['minInclusive']);
            $restriction->appendChild($minInclusive);

        }
        if (array_key_exists('maxInclusive', $simpleType['restriction'])) {
            $maxInclusive = $dom->createElement('xsd:maxInclusive');
            $maxInclusive->setAttribute('value', $simpleType['restriction']['maxInclusive']);
            $restriction->appendChild($maxInclusive);
        }

        if (array_key_exists('fractionDigits', $simpleType['restriction'])) {
            $fraction = $dom->createElement('xsd:fractionDigits');
            $fraction->setAttribute('value', $simpleType['restriction']['fractionDigits']);
            $restriction->appendChild($fraction);
        }
        $simpleTypeElement->appendChild($restriction);

        return $simpleTypeElement;
    }
}
