<?php
namespace subdee\soapserver\Validators;

/**
 * @description SimpleType implementation for Integers
 */
class IntegerType extends SimpleType
{

    /**
     * Generates a Integer wsdl array
     * @return array
     */
    public function generateSimpleType()
    {
        $simpleType = [];

        $gmp = false;
        if (function_exists('gmp_init')) {
            $gmp = true;
        }
        if (array_key_exists('min', $this->data['parameters'])) {
            $minInclusive = ($gmp ? gmp_init($this->data['parameters']['min']) : $this->data['parameters']['min']);
            $simpleType['restriction']['minInclusive'] = ($gmp ? gmp_strval($minInclusive) : $minInclusive);
        }
        if (array_key_exists('max', $this->data['parameters'])) {
            $maxInclusive = ($gmp ? gmp_init($this->data['parameters']['max']) : $this->data['parameters']['max']);
            $simpleType['restriction']['maxInclusive'] = ($gmp ? gmp_strval($maxInclusive) : $maxInclusive);
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
        $restriction->setAttribute('base', 'xsd:' . $this->getName());

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

        $simpleTypeElement->appendChild($restriction);

        return $simpleTypeElement;
    }
}
