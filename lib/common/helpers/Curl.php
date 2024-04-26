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

class Curl
{
    public const HEADERS_JSON = [
        'Content-Type: application/json',
    ];
    
    public static function runSafe($url, $method='GET', $data = null, $headers = null, $options = [])
    {
        try {
            $res = self::run($url, $method, $data, $headers, $options);
        } catch (\yii\base\UserException $e) {
            $res = [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            Php::logError($e, 'Curl error', 'helpers/curl');
            $res = [
                'success' => false,
                'error' => 'Internal error' . (System::isProduction() ? '' : ': ' . $e->getMessage()),
            ];
        }
        if (isset($options['dbg']) && class_exists($options['dbg'])) {
            $options['dbg']::logVar($res, 'Curl::runSafe result');
        }
        return $res;
    }
    
    /**
     * @param $url
     * @param $method - 'POST'/'GET' etc
     * @param $data - CURLOPT_POSTFIELDS value
     * @param $headers - CURLOPT_HTTPHEADER value
     * @param $options - others curl options and some specific values:
     *      $options['verify'] (bool) - verify peer
     *      $options['successHttpCodes'] array of success http codes
     *      $options['decodeResultJson'] (bool) decode result as json
     *      $options['decodeResultJsonRequired'] (array) check results required keys
     *      $options['dbg'] (class) dbg class
     * @return array
     * $res =  [
     *  'success' => bool,
     *  'error' => string,
     *  'data' => curl_exec,
     *  'extra' => curl_getinfo
     * ];
    */
    public static function run($url, $method='GET', $data = null, $headers = null, $options = [])
    {
        Assert::assert(function_exists('curl_version'), 'Curl extension is not installed');
        
        $handle = curl_init($url);
        Assert::assert($handle, 'Curl_init failed: ' . curl_error($handle));
        
        self::setOpt($handle, CURLOPT_CUSTOMREQUEST, $method);
        
        if (!is_null($data)) {
            self::setOpt($handle, CURLOPT_POSTFIELDS, $data);
        }
        if (!is_null($headers)) {
            self::setOpt($handle, CURLOPT_HTTPHEADER, $headers);
        }
        
        if (isset($options['verify'])) {
            $value = (bool) $options['verify'];
            self::setOpt($handle, CURLOPT_SSL_VERIFYPEER, $value);
            self::setOpt($handle, CURLOPT_SSL_VERIFYHOST, $value);
            if (defined('CURLOPT_SSL_VERIFYSTATUS')) { // Added in cURL 7.41.0
                self::setOpt($handle, CURLOPT_SSL_VERIFYPEER, $value);
            }
        }
        
        if (!empty($options)) {
            foreach ($options as $option => $value) {
                if (in_array($option, ['verify', 'dbg', 'dbg_prefix', 'successHttpCodes', 'decodeResultJson', 'decodeResultJsonErrorKey', 'decodeResultJsonRequiredKeys', 'decodeResultJsonReturnRaw'])) {
                    continue;
                }
                self::setOpt($handle, $option, $value);
            }
        }
        
        $result = curl_exec($handle);
        Assert::assert($result, 'curl_exec failed: ' . curl_error($handle));
        
        $info = curl_getinfo($handle);
        if (isset($options['dbg']) && class_exists($options['dbg'])) {
            if (isset($options['dbg_prefix'])) {
                $logState = $options['dbg']::logPrefix($options['dbg_prefix']);
            }
            
            $options['dbg']::logVar($result, 'CURL Result');
            $options['dbg']::logVar($info, 'CURL Info');
            if (!empty($logState)) {
                $options['dbg']::delPrefix($logState);
            }
        }
        
        curl_close($handle);
        
        $res = [
            'success' => false,
            'error' => 'Unknown error',
            'data' => $result,
            'extra' => $info,
        ];
        
        $successHttpCodes = $options['successHttpCodes'] ?? [200];
        if (in_array($info['http_code'], $successHttpCodes)) {
            
            if ($options['decodeResultJson'] ?? false) {
                $json = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
                
                $errorKey = $options['decodeResultJsonErrorKey'] ?? 'error';
                if (!empty($errorKey) && isset($json[$errorKey])) {
                    AssertUser::assert(false, $json[$errorKey]);
                }
                
                if (is_array($options['decodeResultJsonRequiredKeys'] ?? null) ) {
                    foreach ($options['decodeResultJsonRequiredKeys'] as $key) {
                        Assert::assert(isset($json[$key]), "$key not found in decoded result: " . print_r($json, true));
                    }
                }
                if ($options['decodeResultJsonReturnRaw'] ?? false) {
                    $res = $json;
                } else {
                    $res['success'] = true;
                    $res['json'] = $json;
                }
                
            } else {
                $res['success'] = true;
            }
            
        } else {
            $res['success'] = false;
            $res['error'] = 'HTTP code: ' . $info['http_code'];
        }
        return $res;
    }
    
    private static function setOpt($handle, $option, $value)
    {
        $res = curl_setopt($handle, $option, $value);
        Assert::assert($res, "curl_setopt setting option $option value " . print_r($value, true) . " failed: " . curl_error($handle));
    }
    
}