yii2-soap-server
================

A SOAP server for Yii 2 based on the implementation of Yii 1 framework.

This extension is an adaptation of the SOAP Web Service available in Yii1, to Yii2. 
It contains features available up to v1.1.15.

Note: Things might not work as Yii2 progresses but I'll keep it updated as much as I can.

[![Build Status](https://travis-ci.org/subdee/yii2-soap-server.png)](https://travis-ci.org/subdee/yii2-soap-server)

##Requirements

- [Yii2](http://github.com/yiisoft/yii2)
- [php soap](php.net/soap)

##Installation

Use composer and add the following line to your composer.json file:

```
"subdee/yii2-soap-server": "*"
```

##Usage

Usage is similar to the explanation in the [Yii guide](http://www.yiiframework.com/doc/guide/1.1/en/topics.webservice)

Define the custom action in your controller:

```php
public function actions()
{
    return [
        ...,
        'myService' => [
            'class' => 'subdee\soapserver\SoapAction',
        ],
        ...,
    ];
}
```

Add the @soap keyword to the properly documented actions you want:

```php
/**
* Returns hello and the name that you gave
*
* @param string $name Your name
* @return string
* @soap
*/
public function getHello($name)
{
    return 'Hello ' . $name;
}
```

Also, remember to turn off CSRF validation for the controller by setting its property:

```php
public $enableCsrfValidation = false;
```
