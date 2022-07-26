<?php
namespace subdee\soapserver\Validators;

/**
 * @description Number validator
 * @package subdee\soapserver\Validators
 */
class NumberType extends SimpleType
{
    /** @var IntegerType|DoubleType */
    private $validator;

    /** @noinspection MagicMethodsValidityInspection */
    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * NumberType constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $fractionDigits = 0;
        if (array_key_exists('min', $data['parameters'])) {
            $fractionDigits = max($fractionDigits, $this->numberOfDecimals($data['parameters']['min']));
        }
        if (array_key_exists('max', $data['parameters'])) {
            $fractionDigits = max($fractionDigits, $this->numberOfDecimals($data['parameters']['max']));
        }

        if ($fractionDigits === 0 || (array_key_exists('integerOnly', $data) && $data['integerOnly'] === true)) {
            $this->validator = new IntegerType($data);
        } else {
            $this->validator = new DoubleType($data, $fractionDigits);
        }
    }

    /**
     * returns the data used in the creation of the wsdl
     * @return array
     */
    public function generateSimpleType()
    {
        return $this->validator->generateSimpleType();
    }

    /**
     * Generates a domElement and inserts it into the given DomDocument
     * @param \DOMDocument $dom
     * @param string $fieldName Which field are we building an XSD
     * @return \DOMElement $dom
     */
    public function generateXsd(\DOMDocument $dom, $fieldName)
    {
        return $this->validator->generateXsd($dom, $fieldName);
    }

    /** @inheritdoc */
    public function getName()
    {
        return $this->validator->getName();
    }

    /**
     * Returns the number of decimals of a number
     * @param $value
     * @return bool|int
     */
    private function numberOfDecimals($value)
    {
        if (!is_string($value)) {
            $value = (string)$value;
        }
        if ((int)$value === $value) {
            return 0;
        } else if (!is_numeric($value)) {
            return false;
        }

        if (strrpos($value, '.') === false) {
            return 0;
        }
        return strlen($value) - strrpos($value, '.') - 1;
    }
}
