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

namespace app\components;

/**
 * Description of MetaCannonical
 *
 * @author yuri
 */
class MetaCannonical 
{
    /**
     * @var string 
     */
    static public $cannonicalPageUri = '';

    /**
     * @var integer
     */    
    static public $status = 200;
    
    /**
     * @var obj 
     */
    static public $instance = NULL;
    
    /**
     * @param string $path
     * @return self
     */
    static public function instance($path = '')
    {
        if (is_null(self::$instance))
        {
                self::$instance = new self($path);
        }

        return self::$instance;
    }
    
    /**
     * @param string $path
     */
    public function __construct($path) {
        $this->getCannonical($path);
    }

    /**
     * 
     * @param string $path
     * @return $this
     */
    public function getCannonical($path)
    {
        $cannonicalPageUri = preg_replace('|(\?.*)|', '', $path);
        $this->setCannonical($cannonicalPageUri);
        return $this;
    }
    
    /**
     * 
     * @global type $request_type
     * @param string $cannonicalPageUri
     * @return $this
     */
    public function setCannonical($cannonicalPageUri)
    {
        global $request_type;

        /*$base = (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG;
        self::$cannonicalPageUri = $base . $cannonicalPageUri;*/
        if ( is_string($cannonicalPageUri) && strpos($cannonicalPageUri, '://') !== false ) {
            self::$cannonicalPageUri = $cannonicalPageUri;
        }else{
            \Yii::$app->urlManager->setOverrideSettings(['seo_url_parts_currency'=>false]);
            self::$cannonicalPageUri = \Yii::$app->urlManager->createAbsoluteUrl($cannonicalPageUri);
            \Yii::$app->urlManager->setOverrideSettings([]);
        }

        foreach (\common\helpers\Hooks::getList('meta-cannonical/set-cannonical') as $filename) {
            include($filename);
        }

        return $this;
    }

    public function unsetCannonical()
    {
        self::$cannonicalPageUri = '';
        return $this;
    }
    
    /**
     * echo meta tag
     */
    static public function echoMetaTag()
    {
        $objLanguage = new \common\classes\language();
        if (self::$status == 200) {
            self::$cannonicalPageUri != '' && print("<link href='".\yii\helpers\Html::encode(strip_tags(self::$cannonicalPageUri))."' rel='canonical' hreflang='" . $objLanguage->get_code() . "' />");
        }
    }
    
    /**
     * @param integer $status
     */
    static public function setStatus($status) {
        self::$status = $status;
    }
    
    static public function getStatus() {
        return self::$status;
    }
}
