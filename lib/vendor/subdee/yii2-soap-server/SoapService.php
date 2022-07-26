<?php
/**
 * SoapService class file.
 *
 * @author Konstantinos Thermos <info@subdee.org>
 */

/**
 * This class is based on the WsdlGenerator class from the Yii 1 framework.
 *
 * @link https://github.com/yiisoft/yii/blob/1.1.14/framework/web/services/SoapService.php
 *
 * SoapService encapsulates SoapServer and provides a WSDL-based web service.
 *
 * PHP SOAP extension is required.
 *
 * SoapService makes use of {@link CWsdlGenerator} and can generate the WSDL
 * on-the-fly without requiring you to write complex WSDL. However WSDL generator
 * could be customized through {@link generatorConfig} property.
 *
 * To generate the WSDL based on doc comment blocks in the service provider class,
 * call {@link generateWsdl} or {@link renderWsdl}. To process the web service
 * requests, call {@link run}.
 *
 * @property string $methodName The currently requested method name. Empty if no method is being requested.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.services
 * @since 1.0
 */

namespace subdee\soapserver;

use yii\base\Component;
use yii\base\Exception;

class SoapService extends Component
{
    const SOAP_ERROR = 1001;
    /**
     * @var string|object the web service provider class or object.
     * If specified as a class name, it can be a path alias.
     */
    public $provider;
    /**
     * @var string the URL for WSDL. This is required by {@link run()}.
     */
    public $wsdlUrl;
    /**
     * @var string the URL for the Web service. This is required by {@link generateWsdl()} and {@link renderWsdl()}.
     */
    public $serviceUrl;
    /**
     * @var integer number of seconds that the generated WSDL can remain valid in cache. Defaults to 0, meaning no caching.
     */
    public $wsdlCacheDuration = 0;
    /**
     * @var string the ID of the cache application component that is used to cache the generated WSDL.
     * Defaults to 'cache' which refers to the primary cache application component.
     * Set this property to false if you want to disable caching WSDL.
     */
    public $cacheID = 'cache';
    /**
     * @var string encoding of the Web service. Defaults to 'UTF-8'.
     */
    public $encoding = 'UTF-8';
    /**
     * @var array a list of classes that are declared as complex types in WSDL.
     * This should be an array with WSDL types as keys and names of PHP classes as values.
     * A PHP class can also be specified as a path alias.
     * @see http://www.php.net/manual/en/soapserver.soapserver.php
     */
    public $classMap = [];
    /**
     * @var string actor of the SOAP service. Defaults to null, meaning not set.
     */
    public $actor;
    /**
     * @var string SOAP version (e.g. '1.1' or '1.2'). Defaults to null, meaning not set.
     */
    public $soapVersion;
    /**
     * @var integer the persistence mode of the SOAP server.
     * @see http://www.php.net/manual/en/soapserver.setpersistence.php
     */
    public $persistence;

    /**
     * @see http://www.php.net/manual/en/soapserver.soapserver.php (features list under heading 'options')
     * @var string[]
     */
    public $features;

    /**
     * Set options like binding style and body style
     * @var array
     */
    public $wsdlOptions = [];

    private $_method;


    /**
     * Constructor.
     *
     * @param mixed $provider the web service provider class name or object
     * @param string $wsdlUrl the URL for WSDL. This is required by {@link run()}.
     * @param string $serviceUrl the URL for the Web service. This is required by {@link generateWsdl()} and {@link renderWsdl()}.
     */
    public function __construct($provider, $wsdlUrl, $serviceUrl, $wsdlOptions = [])
    {
        $this->provider = $provider;
        $this->wsdlUrl = $wsdlUrl;
        $this->serviceUrl = $serviceUrl;
        $this->wsdlOptions = $wsdlOptions;
    }

    /**
     * Generates the WSDL as defined by the provider.
     * The cached version may be used if the WSDL is found valid in cache.
     *
     * @return string the generated WSDL
     * @see wsdlCacheDuration
     */
    public function generateWsdl()
    {
        $providerClass = get_class($this->provider);
        if ($this->wsdlCacheDuration > 0 && $this->cacheID !== false) {
            $key = 'SoapService.' . $providerClass . $this->serviceUrl . $this->encoding;
            if (($wsdl = \Yii::$app->cache->get($key)) !== false) {
                return $wsdl;
            }
        }

        $generator = new WsdlGenerator();
        foreach ($this->wsdlOptions as $option => $value) {
            $generator->$option = $value;
        }
        $wsdl = $generator->generateWsdl($providerClass, $this->serviceUrl, $this->encoding);
        if (isset($key)) {
            \Yii::$app->cache->set($key, $wsdl, $this->wsdlCacheDuration);
        }
        return $wsdl;
    }

    /**
     * Handles the web service request.
     */
    public function run()
    {
        header('Content-Type: text/xml;charset=' . $this->encoding);
        if (YII_DEBUG) {
            ini_set("soap.wsdl_cache_enabled", 0);
        }

        list(, $hash) = explode(' ', \Yii::$app->getRequest()->getHeaders()->get('authorization') . ' ');
        $auth = $hash ? base64_decode($hash) . '@' : '';
        $server = new \SoapServer(str_replace('http://', 'http://' . $auth, $this->wsdlUrl), $this->getOptions());
        try {
            if ($this->persistence !== null) {
                $server->setPersistence($this->persistence);
            }
            if (is_string($this->provider)) {
                $provider = $this->provider;
                $provider = new $provider();
            } else {
                $provider = $this->provider;
            }
            $server->setObject($provider);
            ob_start();
            try {
                $server->handle();
            } catch (Exception $e) {
                var_dump($e);
                die();
            }
            $soapXml = ob_get_contents();
            ob_end_clean();
            return $soapXml;
        } catch (Exception $e) {
            if ($e->getCode() !== self::SOAP_ERROR) // non-PHP error
            {
                // only log for non-PHP-error case because application's error handler already logs it
                // php <5.2 doesn't support string conversion auto-magically
                \Yii::error($e->__toString());
            }
            $message = $e->getMessage();
            if (YII_DEBUG) {
                $message .= ' (' . $e->getFile() . ':' . $e->getLine() . ")\n" . $e->getTraceAsString();
            }

            // We need to end application explicitly because of
            // http://bugs.php.net/bug.php?id=49513
            $server->fault(get_class($e), $message);
            exit(1);
        }
    }

    /**
     * @return array options for creating SoapServer instance
     * @see http://www.php.net/manual/en/soapserver.soapserver.php
     */
    protected function getOptions()
    {
        $options = [];
        if ($this->soapVersion === '1.1') {
            $options['soap_version'] = SOAP_1_1;
        } elseif ($this->soapVersion === '1.2') {
            $options['soap_version'] = SOAP_1_2;
        }
        if ($this->actor !== null) {
            $options['actor'] = $this->actor;
        }
        $options['encoding'] = $this->encoding;

        foreach ($this->classMap as $type => $className) {
            $options['classmap'][$type] = $className;
        }

        $options['features'] = $this->features;

        return $options;
    }

    /**
     * @return string the currently requested method name. Empty if no method is being requested.
     */
    public function getMethodName()
    {
        if ($this->_method === null) {
            if (isset($HTTP_RAW_POST_DATA)) {
                $request = $HTTP_RAW_POST_DATA;
            } else {
                $request = file_get_contents('php://input');
            }
            if (preg_match('/<.*?:Body[^>]*>\s*<.*?:(\w+)/mi', $request, $matches)) {
                $this->_method = $matches[1];
            } else {
                $this->_method = '';
            }
        }
        return $this->_method;
    }
}
