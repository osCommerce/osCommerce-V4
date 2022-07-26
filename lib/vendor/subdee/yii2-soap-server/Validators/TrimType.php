<?php
namespace subdee\soapserver\Validators;

/**
 * Empty, we can't tell the wsdl user what to do here, so we ignore this model validation type
 */
class TrimType extends SimpleType
{
    /**
     * returns the data used in the creation of the wsdl
     * @return array
     */
    public function generateSimpleType()
    {
        return null;
    }

    /**
     * Generates a domElement and inserts it into the given DomDocument
     * @param \DOMDocument $dom
     * @param string $fieldName Which field are we building an XSD for
     * @return \DOMElement $dom
     */
    public function generateXsd(\DOMDocument $dom, $fieldName)
    {
        return null;
    }
}
