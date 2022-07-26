<?php
namespace subdee\soapserver\Validators;

/**
 * @description baseclass for all simpletypes
 */
abstract class SimpleType
{
    /** @var array */
    protected $data;

    /** @var string */
    protected $xsdName;

    /**
     * ValidatorBase constructor.
     * @param $data array
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * returns name of the class without namespacing
     * @return string
     */
    public function getName()
    {
        $classname = substr(strtolower(get_called_class()), 0, -4);
        if ($pos = strrpos($classname, '\\')) {
            return substr($classname, $pos + 1);
        }
        return $pos;
    }

    /**
     * returns the data used in the creation of the wsdl
     * @return array
     */
    abstract public function generateSimpleType();

    /**
     * Generates a domElement and inserts it into the given DomDocument
     * @param \DOMDocument $dom
     * @param string $fieldName Which field are we building an XSD
     * @return \DOMElement $dom
     */
    abstract public function generateXsd(\DOMDocument $dom, $fieldName);
}
