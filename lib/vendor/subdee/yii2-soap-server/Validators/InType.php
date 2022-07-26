<?php
namespace subdee\soapserver\Validators;

/**
 * The same as tokentype as far as wsdl is concerned, but because we have a simple matcher for types, this needs to exist
 */
class InType extends TokenType
{
    /** @inheritdoc */
    public function getName()
    {
        return 'token';
    }
}
