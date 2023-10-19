<?php
namespace subdee\soapserver\Validators;

/**
 * @description TokenType supports regexp's
 */
class TokenType extends SimpleType
{
    /**
     * returns the data used in the creation of the wsdl
     * @return array
     */
    public function generateSimpleType()
    {
        // FIXME we don't support not, strict and allowArray

        $this->data['restriction']['name'] = 'token';
        if (array_key_exists('range', $this->data['parameters'])) {
            foreach ($this->data['parameters']['range'] as $enumeration) {
                $simpleType['restriction']['enumeration'][] = $enumeration;
            }
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

        if (array_key_exists('enumeration', $simpleType['restriction'])) {
            foreach ($simpleType['restriction']['enumeration'] as $enum) {
                $enumeration = $dom->createElement('xsd:enumeration');
                $enumeration->setAttribute('value', $enum);
                $restriction->appendChild($enumeration);
            }

            $simpleTypeElement->appendChild($restriction);
        }
        return $simpleTypeElement;
    }
}
