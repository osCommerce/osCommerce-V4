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

namespace common\classes;

use yii\base\Component;

class MediaManager extends Component
{
    protected $groupByType = [];
    protected $previousAliasMap = [];
    protected $disableUrlTypeAlias = [];

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->loadPlatformSettings();
    }

    public function getUrlTypes()
    {
        return [
            '/' => 'Site folder',
            '/images' => 'Images folder',
            '/themes' => 'Themes folder',
        ];
    }

    public function loadPlatformSettings($platformId=null)
    {
        if ( empty($platformId) ) {
            $platformId = \Yii::$app->get('platform')->config()->getId();
        }
        $platform_config = \Yii::$app->get('platform')->config();

        $this->groupByType = [
            'themes' => [],
            'images' => [],
        ];

        // Don't use when run in console (e.g. PdfCatalogGen)
        if (\Yii::$app instanceof \yii\console\Application) return;

        $cdn_server = $platform_config->getImagesCdnUrl();
        if ( !empty($cdn_server) ) {
            \Yii::setAlias('@webCatalogImages', $cdn_server);
        }

        $isSecureRequest = \Yii::$app->request->getIsSecureConnection();
        foreach($platform_config->getAdditionalUrls() as $additionalUrl)
        {
            if ( $isSecureRequest && $additionalUrl['ssl_enabled']==0 ) continue;

            if ( $isSecureRequest || $additionalUrl['ssl_enabled']==2 ) {
                $schema = 'https';
            }else{
                $schema = 'http';
            }

            $url = $schema.'://'.rtrim($additionalUrl['url'],'/').'/';
            if ( $additionalUrl['url_type']=='/' )
            {
                $this->groupByType['themes'][] = $url . 'themes/';
                $this->groupByType['images'][] = $url.DIR_WS_IMAGES;
            }else{
                if ( $additionalUrl['url_type']=='/themes' ) {
                    $this->groupByType['themes'][] = $url;
                }else {
                    $this->groupByType[trim($additionalUrl['url_type'], '/')][] = $url;
                }
            }
        }
        $this->previousAliasMap = [];

    }

    public function allowUrlTypeAlias($type, $flag)
    {
        if ( $flag ) {
            unset($this->disableUrlTypeAlias[$type]);
        }else{
            $this->disableUrlTypeAlias[$type] = $type;
        }
    }

    public function getAlias($alias)
    {
        if ( count($this->previousAliasMap)>300 ) {
            array_shift($this->previousAliasMap);
        }
        if ( !isset($this->disableUrlTypeAlias['images']) && count($this->groupByType['images'])>0 && strpos($alias,'@webCatalogImages/')!==false ) {
            $urlTo = current($this->groupByType['images']);
            if (!next($this->groupByType['images'])){
                reset($this->groupByType['images']);
            };
            if (!isset($this->previousAliasMap[$alias])) {
                $this->previousAliasMap[$alias] = str_replace('@webCatalogImages/', $urlTo, $alias);
            }
            return $this->previousAliasMap[$alias];
        }
        if ( !isset($this->disableUrlTypeAlias['images']) && count($this->groupByType['themes'])>0 && strpos($alias,'@webThemes/')!==false ) {
            $urlTo = current($this->groupByType['themes']);
            if (!next($this->groupByType['themes'])){
                reset($this->groupByType['themes']);
            };
            if (!isset($this->previousAliasMap[$alias])) {
                $this->previousAliasMap[$alias] = preg_replace('#@webThemes/+#', $urlTo, $alias);
            }

            return $this->previousAliasMap[$alias];
        }elseif(strpos($alias,'@webThemes//')!==false){
            $alias = str_replace('@webThemes//','@webThemes/',$alias);
        }


        return \Yii::getAlias($alias);
    }
}