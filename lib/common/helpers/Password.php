<?php
/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\helpers;

use common\classes\PasswordHash;

class Password {

    public static function randomize($frontend = false) {
        $passwordLength = 8;
        $chars = "abchefghjkmnpqrstuvwxyz0123456789";
        $checkType = 'None';
        if ($frontend) {
            if (defined('ENTRY_PASSWORD_MIN_LENGTH')) {
                $passwordLength = (int) ENTRY_PASSWORD_MIN_LENGTH;
            }
            if (defined('PASSWORD_STRONG_REQUIRED')) {
                $checkType = PASSWORD_STRONG_REQUIRED;
                switch ($checkType) {
                    case 'ULN':
                        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                        break;
                    case 'ULNS':
                        $chars =  'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789`-=~!@#$%^&*()_+,./<>?;:[]{}\|';
                        break;
                }
            }
        } else {
            if (defined('ADMIN_PASSWORD_MIN_LENGTH')) {
                $passwordLength = (int) ADMIN_PASSWORD_MIN_LENGTH;
            }
            if (defined('ADMIN_PASSWORD_STRONG')) {
                $checkType = ADMIN_PASSWORD_STRONG;
                switch ($checkType) {
                    case 'ULN':
                        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                        break;
                    case 'ULNS':
                        $chars =  'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789`-=~!@#$%^&*()_+,./<>?;:[]{}\|';
                        break;
                }
            }
        }
        $charsLen = strlen($chars);

        do {

            $checkPassed = false;

            srand((double) microtime() * 1000000);
            $pass = '';
            while (strlen($pass) < $passwordLength) {
                $num = rand() % $charsLen;
                $tmp = substr($chars, $num, 1);
                $pass = $pass . $tmp;
            }

            switch ($checkType) {
                case 'ULN':
                    if (preg_match('/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{' . $passwordLength . ',}/', $pass)) {
                        $checkPassed = true;
                    }
                    break;
                case 'ULNS':
                    if (preg_match('/(?=.*\d)(?=.*\W+)(?=.*[a-z])(?=.*[A-Z]).{' . $passwordLength . ',}/', $pass)) {
                        $checkPassed = true;
                    }
                    break;
                default :
                    $checkPassed = true;
                    break;
            }
        } while (!$checkPassed);

        return $pass;
    }

    private static function getSecurityKeyByType($secKeyType = '')
    {
        $return = '';
        $secKeyType = ((trim($secKeyType) != '') ? ('secKey.' . strtolower(trim($secKeyType))) : '');
        if (($secKeyType != '') AND isset(\Yii::$app->params[$secKeyType])) {
            $return = trim(\Yii::$app->params[$secKeyType]);
        }
        return $return;
    }

    /**
     * validate password hash using appropriate hash method (by hash format)
     * @param string $plain
     * @param string $encrypted
     * @param string $secKeyType
     * @return boolean|0
     */
    public static function validate_password($plain, $encrypted, $secKeyType = 'frontend') {
        if (tep_not_null($plain) && tep_not_null($encrypted)) {
            $type = static::password_type($encrypted);
            if ($type  == 'salt') {
                $stack = explode(':', $encrypted);
                if (sizeof($stack) != 2)
                    return false;

            if ((md5($stack[1] . $plain) == $stack[0]) || (md5($stack[1] . $plain . self::getSecurityKeyByType($secKeyType)) == $stack[0])) {
                    return 0;
                }
            } elseif ($type == 'phpass') {
                $hasher = new PasswordHash(10, true);
                if ($hasher->CheckPassword($plain, $encrypted)) {
                    return 0;
                }
                return false;
            
            } else {
                return password_verify(($plain . self::getSecurityKeyByType($secKeyType)), $encrypted);
            }
        }
        return false;
    }

    public static function rand($min = null, $max = null) {
        static $seeded;

        if (!isset($seeded)) {
            mt_srand( intval(microtime(true) * 1000000) );
            $seeded = true;
        }

        if (isset($min) && isset($max)) {
            if ($min >= $max) {
                return $min;
            } else {
                return mt_rand($min, $max);
            }
        } else {
            return mt_rand();
        }
    }

    public static function encrypt_password($plain, $secKeyType = 'frontend') {
        return static::encrypt_password_p4($plain, $secKeyType);
    }


    public static function encrypt_password_p4($plain, $secKeyType = 'frontend') {
        $secKey = self::getSecurityKeyByType($secKeyType);
        if ($secKey == '') {
            throw new \InvalidArgumentException('Encryption error! Security key cannot be empty!');
        }
        //Caution! Using the PASSWORD_BCRYPT as the algorithm, will result in the password parameter being truncated to a maximum length of 72 bytes.
        $password = password_hash(($plain . $secKey), PASSWORD_BCRYPT, ['cost' => 13]);
        return $password;
    }

    public static function encrypt_password_phpass($plain) {
        $hasher = new PasswordHash(10, true);
        return $hasher->HashPassword($plain, 'phpass');
    }

    public static function encrypt_password_salt($plain, $secKeyType = 'frontend') {
        $secKey = self::getSecurityKeyByType($secKeyType);
        if ($secKey == '') {
            throw new \InvalidArgumentException('Encryption error! Security key cannot be empty!');
        }
        $password = '';
        for ($i = 0; $i < 10; $i++) {
            $password .= self::rand();
        }
        $salt = substr(md5($password), 0, 2);
        $password = md5($salt . $plain . $secKey) . ':' . $salt;
        return $password;
    }

    public static function create_random_value($length, $type = 'mixed') {
        if (($type != 'mixed') && ($type != 'chars') && ($type != 'digits'))
            return false;

        $rand_value = '';
        while (strlen($rand_value) < $length) {
            if ($type == 'digits') {
                if (empty($rand_value)){
                    $char = self::checkFirst(self::rand(0, 9));
                } else {
                    $char = self::rand(0, 9);
                }
            } else {
                $char = chr(self::rand(0, 255));
            }
            if ($type == 'mixed') {
                if (preg_match('/^[a-z0-9]$/i', $char))
                    $rand_value .= $char;
            } elseif ($type == 'chars') {
                if (preg_match('/^[a-z]$/i', $char))
                    $rand_value .= $char;
            } elseif ($type == 'digits') {
                if (preg_match('/^[0-9]$/', $char))
                    $rand_value .= $char;
            }
        }

        return $rand_value;
    }

    public static function checkFirst($value){
        if (!$value){
            do {
                $value = self::rand(0, 9);
            } while(!$value);
        }
        return $value;
    }

    public static function decryptAuthUserParam($auth_param)
    {
        $encrypted = false;
        $sc = new \yii\base\Security();
        if ($aup = $sc->decryptByKey($auth_param,date('\s\me\c\rYkd\ey'))){
            $aup = explode("\t",$aup,4);
            $encrypted = [
                'customers_id' => $aup[0],
                'customers_email' => $aup[1],
                'auth_type' => $aup[2],
                'auth_key' => $aup[3],
            ];
        }
        return $encrypted;
    }
    public static function encryptAuthUserParam($customer_id, $customers_email_address, $auth_type='login', $auth_key = '')
    {
        $sc = new \yii\base\Security();
        $aup = base64_encode($sc->encryptByKey(
            strval($customer_id)."\t".
            strval($customers_email_address)."\t".
            strval($auth_type)."\t".
            strval($auth_key),
            date('\s\me\c\rYkd\ey')));
        return $aup;
    }
/**
 * This function returns the type of the encrypted password
 * p4 and outdated (phpass or salt)
 */
    public static function password_type($encrypted):string {
        if (preg_match('/^[A-Z0-9]{32}\:[A-Z0-9]{2}$/i', $encrypted) === 1) {
            return 'salt';
        }
        if (substr($encrypted, 0, 3) === '$P$') {
            return 'phpass';
        }

        return 'p4';
    }

}
