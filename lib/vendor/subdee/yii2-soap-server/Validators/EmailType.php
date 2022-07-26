<?php
namespace subdee\soapserver\Validators;

use yii\validators\EmailValidator;

/**
 * Should support the same emailaddresses Yii itself
 * @see http://www.yiiframework.com/doc-2.0/guide-tutorial-core-validators.html#email
 */
class EmailType extends MatchType
{

    /**
     * returns the data used in the creation of the wsdl
     * @return array
     */
    public function generateSimpleType()
    {
        $emailValidator = new EmailValidator();

        $emailPattern = $emailValidator->pattern;

        if (array_key_exists('allowName', $this->data['parameters']) && $this->data['parameters']['allowName'] === true) {
            $emailPattern = $emailValidator->fullPattern;
        }
        // We need to change the regexp which is used by Yii to the format used in the wsdl
        preg_match('/^\/(.*)\/.*/', $emailPattern, $matches);
        $simpleType['restriction']['pattern'] = $matches[1];

        $simpleType['restriction']['name'] = $this->getName();

        return $simpleType;
    }

    /** @inheritdoc */
    public function getName()
    {
        return 'email';
    }
}
