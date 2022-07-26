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

namespace backend\models\EP\Provider\Trueloaded;

use Yii;
use backend\models\EP\Messages;
use backend\models\EP\Provider\DatasourceInterface;

abstract class ImportXmlBase implements DatasourceInterface {

    protected $feed;
    protected $providerClass;

    protected $row_count = 0;

    protected $config = [];

    /**
     * @var Reader
     */
    protected $readerObj;

    /**
     * @var Provider
     */
    protected $providerObj;

    function __construct($config) {
        $this->config = $config;
    }

    public function allowRunInPopup() {
        return true;
    }

    public function getProgress() {
        return $this->readerObj->getProgress();
    }

    public function prepareProcess(Messages $message) {

        if (empty($this->feed)) {
            throw new \Exception('XML feed is not defined');
        }
        $url = $this->config['base_url'] . '?feed=' . urlencode($this->feed);
        $secure_method = $this->config['secure_method'] ?? null;
        $secure_key = trim($this->config['secure_key'] ?? '');

        $stream_context_params = ['http' => ['timeout' => 1200]];
        switch ($secure_method) {
            case 'get':
                $stream_context_params['http']['method']  = 'GET';
                $url .= '&key='.$secure_key;
                break;
            case 'post':
                $stream_context_params['http']['method']  = 'POST';
                $stream_context_params['http']['content'] = 'key=' . $secure_key . '&feed=' . urlencode($this->feed);
                break;
            case 'bearer':
                $stream_context_params['http']['header'] = 'Authorization: Bearer '.$secure_key;
                break;
            default:
                throw new \Exception('Secure method is invalid: '.$secure_method);
        }
        if (YII_ENV=='dev') { // disable cheking self-signed cert
            $stream_context_params['ssl'] = ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true];
        }
        // download XML feed to working folder
        copy($url, $this->config['workingDirectory'] . urlencode($this->feed) . '.xml', stream_context_create($stream_context_params));

        if (empty($this->providerClass)) {
            throw new \Exception('Provider Class is not defined');
        }
        $this->providerObj = new $this->providerClass([
            'job_configure' => (isset($this->job_configure['import']) && is_array($this->job_configure['import']) ? $this->job_configure['import'] : [])
        ]);

        $readerConfig = array_merge([
            'class' => 'backend\\models\\EP\\Reader\\XML',
            'filename' => $this->config['workingDirectory'] . urlencode($this->feed) . '.xml',
        ], (isset($this->job_configure['import']) && is_array($this->job_configure['import']) ? $this->job_configure['import'] : []));
        foreach ($this->providerObj->exchangeXml() as $versionInfo) {
            $readerConfig = array_merge($versionInfo, $readerConfig);
        }
        $this->readerObj = Yii::createObject($readerConfig);

        // Clear local data
        if (method_exists($this->providerObj, 'clearLocalData')) {
            $this->providerObj->clearLocalData();
        }
    }

    public function processRow(Messages $message) {
        if ($data = $this->readerObj->read()) {
            $this->providerObj->importRow($data, $message);
            $this->row_count++;
            return true;
        }
        return false;
    }

    public function postProcess(Messages $message) {
        if ($this->row_count > 0) {
            $message->info('Row(s) Imported: ' . $this->row_count);
        }
    }
}
