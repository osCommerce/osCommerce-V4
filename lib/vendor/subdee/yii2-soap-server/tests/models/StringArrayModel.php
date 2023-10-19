<?php
namespace subdee\soapserver\tests\models;

use yii\base\Model;

/**
 * @description Model needed for tests
 * @package subdee\soapserver\tests\models
 */
class StringArrayModel extends Model
{
    /**
     * @var string[] Array of string
     * @soap
     */
    public $stringParameter = [];
}
