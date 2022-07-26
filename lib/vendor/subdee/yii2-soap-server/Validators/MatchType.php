<?php
namespace subdee\soapserver\Validators;

/**
 * @description regexp support
 */
class MatchType extends SimpleType
{

    /**
     * returns the data used in the creation of the wsdl
     * @return array
     * @throws \ErrorException
     */
    public function generateSimpleType()
    {
        if (array_key_exists('pattern', $this->data['parameters'])) {
            if (preg_match('/^\/(.*)\/.*/', $this->data['parameters']['pattern'], $matches)) {
                $simpleType['restriction']['pattern'] = $matches[1];
            } else {
                throw new \ErrorException('Not a correct pattern used in this type');
            }
        }

        $simpleType['restriction']['name'] = $this->getName();

        return $simpleType;
    }

    /**
     * Generates a domElement and inserts it into the given DomDocument
     * @param \DOMDocument $dom
     * @param string $fieldName Which field are we building an XSD for
     * @return \DOMDocument $dom
     */
    public function generateXsd(\DOMDocument $dom, $fieldName)
    {
        $simpleTypeElement = $dom->createElement('xsd:simpleType');
        $simpleTypeElement->setAttribute('name', $fieldName);

        $restriction = $dom->createElement('xsd:restriction');
        $restriction->setAttribute('base', 'xsd:token');

        $simpleType = $this->generateSimpleType();

        if (array_key_exists('restriction', $simpleType)) {

            $pattern = $dom->createElement('xsd:token');
            $pattern->setAttribute('value', $simpleType['restriction']['pattern']);

            $restriction->appendChild($pattern);
        }
        $simpleTypeElement->appendChild($restriction);

        return $simpleTypeElement;
    }
}
