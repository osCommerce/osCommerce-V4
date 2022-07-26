<?php
namespace subdee\soapserver\Validators;

/**
 * @description Stringtype simpletype
 */
class StringType extends SimpleType
{
    /**
     * returns the data used in the creation of the wsdl
     * @return array
     */
    public function generateSimpleType()
    {
        $simpleType = [];

        if (array_key_exists('parameters', $this->data) && is_array($this->data['parameters']) && array_key_exists('length', $this->data['parameters'])) {
            // Most of the time you don't want an empty value, but it has to contain 'something' that's why we start with 1,0 as an default value
            $values = [1, 0];
            if (!is_array($this->data['parameters']['length'])) {
                $values[1] = $this->data['parameters']['length'];
            } else {
                $values = $this->data['parameters']['length'];
            }
            $simpleType['restriction']['pattern'] = '.{' . implode(',', $values) . '}';
        }

        $simpleType['restriction']['name'] = $this->getName();
        return $simpleType;
    }

    /**
     * Generates a domElement and inserts it into the given DomDocument
     * @param \DOMDocument $dom
     * @param string $fieldName Which field are we building an XSD for
     * @return \DOMElement $dom
     */
    public function generateXsd(\DOMDocument $dom, $fieldName)
    {
        $simpleTypeElement = $dom->createElement('xsd:simpleType');
        $simpleTypeElement->setAttribute('name', $fieldName);

        $restriction = $dom->createElement('xsd:restriction');
        $restriction->setAttribute('base', 'xsd:' . $this->getName());

        $simpleType = $this->generateSimpleType();

        if (array_key_exists('restriction', $simpleType)) {
            if (array_key_exists('pattern', $simpleType['restriction'])) {
                $pattern = $dom->createElement('xsd:pattern');
                $pattern->setAttribute('value', $simpleType['restriction']['pattern']);
                $restriction->appendChild($pattern);
            }
        }
        $simpleTypeElement->appendChild($restriction);

        return $simpleTypeElement;
    }
}
