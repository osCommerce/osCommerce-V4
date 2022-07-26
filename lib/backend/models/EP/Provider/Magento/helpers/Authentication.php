<?php

namespace backend\models\EP\Provider\Magento;

use Yii;
use yii\authclient\OAuth1;

class Authentication extends OAuth1 {
    
    public $authUrl = '/oauth_authorize';
    /**
     * @inheritdoc
     */
    public $requestTokenUrl = '/oauth/initiate';
    /**
     * @inheritdoc
     */
    public $requestTokenMethod = 'POST';
    /**
     * @inheritdoc
     */
    public $accessTokenUrl = '/oauth/token';
    /**
     * @inheritdoc
     */
    public $accessTokenMethod = 'POST';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = '';
    
    public $callbackUrl = '';
    
    public $accessToken = '';
    
    public $accessTokenSecret = '';
    
    public $restPath = '/api/rest';
    
    
    
    public function __construct($config = array()) {
        if (substr($config['location'],-1) == '/') $config['location'] = substr($config['location'],0 ,-1);
        $this->apiBaseUrl = $config['location'];
        $this->authUrl = $config['location'] . $config['admin_url'] . $this->authUrl;
        $this->requestTokenUrl = $config['location'] . $this->requestTokenUrl;
        $this->accessTokenUrl = $config['location'] . $this->accessTokenUrl;
        $this->consumerKey = $config['consumer_key'];
        $this->consumerSecret = $config['consumer_secret'];
        $this->callbackUrl = $config['callback_url'];
        parent::__construct();
    }
      
    public function prepare(){
        if ($_SESSION['state']) return;
        if (!isset($_REQUEST['oauth_token'])){
            $_SESSION['state'] = false;
            try{
                $_SESSION['requestToken'] = $requestToken = $this->fetchRequestToken();
            } catch (\Exception $e){
                echo $e->getMessage();
                exit();
            }
            $url = $this->buildAuthUrl($requestToken);
            header("Location: ".$url);
            exit();
        } else {
            $token = $this->fetchAccessToken($_REQUEST['oauth_token']);
            $_SESSION['requestToken']->setToken($token->getToken());
            $_SESSION['AccessToken'] = $token;
            $_SESSION['state'] = true;
            header("Location: " . HTTPS_CATALOG_SERVER . DIR_WS_ADMIN . Yii::$app->controller->getRoute() . '?id=' . Yii::$app->getRequest()->get('id'));
            exit();
        }              
        return;        
    }
        
    protected function initUserAttributes()
    {
        return [];
    }
    
    public function getReturnUrl(){
        return $this->callbackUrl;
    }
    
    public function setCallbackUrl($url){
        $this->callbackUrl = $url;
    }
    
    public function getAccessToken(){
        return $_SESSION['AccessToken'];
    }
           
    public function api($apiSubUrl, $method = 'GET', $data = [], $headers = []){
        
        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = '*/*';
        
        $defaultParams = [
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_token' => $_SESSION['requestToken']->getToken(),
        ];
        $defaultParams = array_merge($defaultParams, $this->generateCommonRequestParams());
        
        $url =$this->apiBaseUrl . $this->restPath . $apiSubUrl;
        
        $request = $this->createApiRequest()
            ->setMethod($method)
            ->setUrl($url)
            ->addHeaders($headers);
                
        $request->setData(array_merge($defaultParams, $data));
        
        $this->signRequest($request, $_SESSION['AccessToken']);       
        
        $str = [];        
        foreach($request->getData() as $k=>$h){
            $str[] = $k.'="'.$h.'",';
            unset($headers[$k]);
        }
        $s = substr("Authorization: OAuth " . implode(" ", $str), 0, -1);
        foreach($headers as $k=>$h){
            $s .= "\r\n" . $k.': '.$h;
        }
        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'header'=> $s
            )
        );

        $context = stream_context_create($opts);

        try{
            $fp = file_get_contents($url, false, $context);
        } catch(\Exception $e){
            echo $e->getMessage();
        }
        return json_decode($fp);
    }
}