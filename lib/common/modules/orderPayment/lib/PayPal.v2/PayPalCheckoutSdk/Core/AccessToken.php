<?php

namespace PayPalCheckoutSdk\Core;


class AccessToken
{
    public $token;
    public $tokenType;
    public $expiresIn;
    private $createDate;

    public function __construct($token, $tokenType, $expiresIn, $tokenCreateTime=null)
    {
        $this->token = $token;
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
        if (empty($tokenCreateTime)) {
            $this->createDate = time();
        } else {
            $this->createDate = $tokenCreateTime;
        }

    }

    public function isExpired()
    {
        return time() >= $this->createDate + $this->expiresIn;
    }

    public function getCreateDate() {
        return $this->createDate;
    }
}