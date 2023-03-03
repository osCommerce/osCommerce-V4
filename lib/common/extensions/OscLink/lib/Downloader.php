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

namespace OscLink;

use \common\helpers\Assert;
use \common\helpers\Php8;

class Downloader
{
    private $apiUrlBase = '';
    private $apiUrl = '';
    private $apiMethod = '';
    private $apiKey = '';

    private $workingDir;

    public function __construct(array $configurationArray)
    {
        $this->workingDir = dirname(__DIR__) . '/temp/';

        $this->apiUrlBase = trim($configurationArray['api_url']['cmc_value'] ?? '');
        $pos = strpos($this->apiUrlBase, 'index.php');
        if ($pos) {
            $this->apiUrlBase = substr($this->apiUrlBase, 0, $pos);
        }
        $this->apiUrlBase = rtrim($this->apiUrlBase, '/');
        $this->apiUrl = $this->apiUrlBase . '/index.php';


        $this->apiMethod = trim($configurationArray['api_method']['cmc_value'] ?? 'bearer');
        $this->apiKey = trim($configurationArray['api_key']['cmc_value'] ?? '');
    }

    private function checkVars()
    {
        Assert::assert(is_dir($this->workingDir), 'Temp dir does not exists');

        Assert::assertNotEmpty($this->apiUrl, 'Url is empty');
        Assert::assert(in_array( substr($this->apiUrl, 0, 7), ['http://', 'https:/']), 'Url is invalid');

        Assert::assertNotEmpty($this->apiKey, 'Secure key is empty');
    }

    public function testConnection()
    {
        $this->checkVersion();
    }

    private function getStreamContext()
    {
        $stream_context_params = ['http' => ['timeout' => 1200]];
        switch ($this->apiMethod) {
            case 'get':
                $stream_context_params['http']['method']  = 'GET';
                $stream_context_params['http']['header']  = 'Cache-Control: no-store';
                break;
            case 'post':
                $stream_context_params['http']['method']  = 'POST';
                $stream_context_params['http']['header']  = 'Content-Type: application/x-www-form-urlencoded';
                $stream_context_params['http']['content'] = 'key=' . $this->apiKey; // . '&feed=' . urlencode($feed);
                break;
            case 'bearer':
                $stream_context_params['http']['header'] = 'Authorization: Bearer ' . $this->apiKey;
                break;
            default:
                throw new \Exception('Secure method is invalid: ' . $this->apiMethod);
        }
//        if (YII_ENV=='dev') { // disable checking self-signed cert
            $stream_context_params['ssl'] = ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true];
//        }
        return stream_context_create($stream_context_params);
    }


    private function internal_download($params)
    {
        $this->checkVars();
        Assert::assert(is_array($params));

        $url = $this->apiUrl . '?';
        foreach($params as $name => $value) {
            $url .=  $name . '=' . urlencode($value) . '&';
        }
        if ($this->apiMethod == 'get') {
            $url .= 'key=' . $this->apiKey;
        }

        $feed = $params['feed'] ?? null;
        if (!empty($feed) && empty($params['r'])) {
            $suffix = isset($params['offset']) ? '_offset'.$params['offset'] : '';
            $filename = $this->workingDir . urlencode($feed) . $suffix . '.xml';
            // download XML feed to working folder
            Assert::assert(
                    copy($url, $filename, $this->getStreamContext()),
                    error_get_last()['message'] ?? ''
            );
            return $filename;
        } else {
            $result = @file_get_contents($url, false, $this->getStreamContext());
            Assert::assert(false !== $result, error_get_last()['message'] ?? '' );
            return json_decode($result, true);
        }
    }

    public function getCount($feed, &$errorMsg)
    {
        Assert::assertNotEmpty($feed, 'Feed param is empty');
        $result = $this->internal_download([
            'r' => 'site/count',
            'feed' => $feed,
        ]);
        $count = $result['count'] ?? -1;
        $errorMsg = $count >= 0? '' : $result['error'] ?? 'Unknown error';
        return $result['count'];
    }

    public function getStatus()
    {
        return $this->internal_download([
            'r' => 'site/status',
        ]);
    }

    public function checkVersion()
    {
        $required_ver = '1.56';  // oscb/compat/configure.php
        $required_msg = sprintf(Php8::getConst('EXTENSION_OSCLINK_TEXT_ERROR_OLD_VERSION'), $required_ver);
        
        // check access and auth
        $this->internal_download([]);

        try {
            $status = $this->getStatus();
        } catch( \Exception $e) {
            if (false !== strpos($e->getMessage(), '404 Not Found')) {
                throw new \Exception($required_msg);
            } else {
                throw $e;
            }
        }
        Assert::assert(isset($status['version']), Php8::getConst('EXTENSION_OSCLINK_TEXT_ERROR_NOT_FOUND'));
        $required_msg .= ' (' . sprintf(Php8::getConst('EXTENSION_OSCLINK_TEXT_ERROR_OLD_VER_FOUND'), $status['version']) . ')';
        Assert::assert(version_compare($status['version'], $required_ver) >= 0 , $required_msg);
    }


    public function getFeed($feed, $offset = null, $limit = null)
    {
        Assert::assertNotEmpty($feed, 'Feed param is empty');

        $params = ['feed' => $feed];
        if (!empty($offset)) {
            $params['offset'] = $offset;
        }
        if (!empty($limit)) {
            $params['limit'] = $limit;
        }
        return $this->internal_download($params);
    }

}