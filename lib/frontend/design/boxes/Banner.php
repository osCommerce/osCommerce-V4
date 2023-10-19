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

namespace frontend\design\boxes;

use common\classes\Images;
use frontend\design\Info;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use common\models\Banners;
use common\models\BannersGroups;
use common\models\BannersGroupsImages;
use common\models\BannersGroupsSizes;

class Banner extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();

        Info::includeJsFile('reducers/widgets');
        Info::addJsData(['widgets' => [
            $this->id => [ 'lazyLoad' => @$this->settings[0]['lazy_load']]
        ]]);
    }

    public function run()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $banners = array();
        $banner_speed = '';

        Info::addBlockToWidgetsList('banner');
        Info::includeJsFile('Banner');

        if (!@$this->settings[0]['banners_group'] && @$this->settings[0]['params'])
            $this->settings[0]['banners_group'] = $this->settings[0]['params'];
        $_platform_id = \common\classes\platform::currentId();

        if (@$this->params['banner_group']) {
            $this->settings[0]['banners_group'] = $this->params['banner_group'];
        }

        if (
            isset($this->settings[0]['banners_group']) &&
            $this->settings[0]['banners_group'] == 'page_setting' &&
            isset($this->params['banners_group'])
        ) {
            $this->settings[0]['banners_group'] = $this->params['banners_group'];
        }
        $groupId = 0;
        if (preg_match("/^[0-9]+$/", $this->settings[0]['banners_group'])) {
            $groupId = $this->settings[0]['banners_group'];
        } else {
            $bannersGroups = BannersGroups::findOne(['banners_group' => $this->settings[0]['banners_group']]);
            if ($bannersGroups) {
                $groupId = $bannersGroups->id;
            }
        }

        $andWhere = '';
        if (($this->settings[0]['ban_id'] ?? false) && isset($this->settings[0]['banners_type']) && $this->settings[0]['banners_type'] == 'single') {
            $andWhere = ' and bl.banners_id = ' . $this->settings[0]['ban_id'] . ' ';
        }
        foreach (\common\helpers\Hooks::getList('box/banner') as $filename) {
            include($filename);
        }

        $use_phys_platform = true;
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('AdditionalPlatforms', 'allowed')){
            if ($ext::checkSattelite()){
                $s_platform_id = $ext::getSatteliteId();
                $sql = tep_db_query("select * from " . TABLE_BANNERS_TO_PLATFORM .
                        " nb2p, " . Banners::tableName() . " nb, " . TABLE_BANNERS_LANGUAGES .
                        " bl where bl.banners_id = nb.banners_id AND bl.language_id='" . $languages_id . "' AND nb2p.banners_id=nb.banners_id "
                        . "AND nb2p.platform_id='" . $s_platform_id . "'  and nb.group_id = '" . $groupId . "' "
                        . $andWhere
                        ." and (nb.expires_date is null or nb.expires_date >= now()) and (nb.date_scheduled is null or nb.date_scheduled <= now()) "
                        . "AND (bl.banners_html_text!='' OR bl.banners_image!='' OR bl.banners_url)
                        and nb.status = '1'
                         order by " . ($this->settings[0]['banners_type'] == 'random' ? " RAND() LIMIT 1" : " nb.sort_order"));
                if (tep_db_num_rows($sql)){
                    $use_phys_platform = false;
                    $_platform_id = $s_platform_id;
                }
            }
        }
        if ($use_phys_platform){
            $sql = tep_db_query("select * from " . TABLE_BANNERS_TO_PLATFORM . " nb2p, " . Banners::tableName() . " nb, " . TABLE_BANNERS_LANGUAGES .
                    " bl where bl.banners_id = nb.banners_id AND bl.language_id='" . $languages_id . "' AND nb2p.banners_id=nb.banners_id AND"
                    . " nb2p.platform_id='" . $_platform_id . "'  and nb.group_id = '" . $groupId . "' "
                    . $andWhere
                    ." AND (nb.expires_date is null or nb.expires_date >= now()) and (nb.date_scheduled is null or nb.date_scheduled <= now()) and "
                    . "(bl.banners_html_text!='' OR bl.banners_image!='' OR bl.banners_url)
                        and nb.status = '1'
                        order by " . ($this->settings[0]['banners_type'] == 'random' ? " RAND() LIMIT 1" : " nb.sort_order"));
        }
        if (@$this->settings[0]['banners_type'] == 'random') {
            $this->settings[0]['banners_type'] = 'banner';
        }
        
        if (!@$this->settings[0]['banners_type']) {
            $type_sql_query = tep_db_query("select nb.banner_type from " . TABLE_BANNERS_TO_PLATFORM . " nb2p, " . Banners::tableName() . " nb where nb.group_id = '" . $groupId . "' AND nb2p.banners_id=nb.banners_id AND nb2p.platform_id='" . $_platform_id . "' limit 1");
            if (tep_db_num_rows($type_sql_query) > 0) {
                $type_sql = tep_db_fetch_array($type_sql_query);
                $type_array = $type_sql['banner_type'];
                $type_exp = explode(';', $type_array);
                if (isset($type_exp) && !empty($type_exp)) {
                    $this->settings[0]['banners_type'] = $type_exp[0];
                } else {
                    $this->settings[0]['banners_type'] = $type_sql['banner_type'];
                }
            }
        }

        $bannerGroupSettings = [];
        $groupSettings = BannersGroupsSizes::find()
            ->where([ 'group_id' => $groupId])
            ->asArray()
            ->all();
        if (is_array($groupSettings)) {
            foreach ($groupSettings as $group) {
                $bannerGroupSettings[$group['image_width']] = $group;
            }
        }
        while ($row = tep_db_fetch_array($sql)) {
            $row['is_banners_image_valid'] = (!empty($row['banners_image']) && is_file(Images::getFSCatalogImagesPath().$row['banners_image']));

            if (!ArrayHelper::getValue($this->settings, [0,'dont_use_webp'])) {
                $row['banners_image'] = \common\classes\Images::getWebp($row['banners_image']);
            }
            $row['banners_image_url'] = \common\helpers\Media::getAlias('@webCatalogImages/'.$row['banners_image']);
            if ($row['svg']) {
                $row['image'] = self::bannerGroupSvg(
                    $bannerGroupSettings,
                    $row['banners_id'],
                    $row['svg']
                );
            } else {
                $row['image'] = self::bannerGroupImages(
                    $bannerGroupSettings,
                    $row['banners_id'],
                    $row['banners_image'],
                    $row['banners_title'],
                    ArrayHelper::getValue($this->settings, [0,'lazy_load']),
                    ArrayHelper::getValue($this->settings, [0,'dont_use_webp'])
                );
            }

            $row['text_position'] = self::textPosition($row['text_position']);
            if (
                substr($row['banners_url'], 0, 4) != 'http' &&
                substr($row['banners_url'], 0, 3) != 'www' &&
                substr($row['banners_url'], 0, 2) != '//' &&
                strpos($row['banners_url'], '?')
            ) {
                $arr = explode('?', $row['banners_url']);
                $paramsStr = explode('&', $arr[1]);
                $params = [];
                if (is_array($paramsStr)) {
                    foreach ($paramsStr as $str) {
                        $vals = explode('=', $str);
                        $params[$vals[0]] = $vals[1];
                    }
                }
                $row['banners_url'] = Yii::$app->urlManager->createUrl(array_merge([$arr[0]], $params));
                $row['banners_html_text'] = \common\classes\TlUrl::replaceUrl($row['banners_html_text']);
            }

            $banners[] = $row;
        }

        if (@$this->settings[0]['preload']) {
            \Yii::$app->view->registerLinkTag(['rel' => 'preload', 'href' => $banners[0]['banners_image_url'], 'as' => 'image']);
        }

        if (count($banners) == 0) return '';

        $settings = array_merge(self::$defaultSettings, $this->settings[0]);
        $template = '';
        $this->settings[0]['template'] = $this->settings[0]['template'] ?? null;
        if ((!$this->settings[0]['template'] && $this->params['microtime'] > '1675836928') || !$this->params['microtime']) {
            $this->settings[0]['template'] = 1;
        }
        if ($this->settings[0]['template']) {
            $template = '-' . $this->settings[0]['template'];

            Info::addBoxToCss('slick');
            if ($this->id) {
                Info::addJsData(['widgets' => [ $this->id => [
                    'settings' => $settings,
                    'colInRowCarousel' => $this->settings['colInRowCarousel']
                ]]]);
            }
        }

        return IncludeTpl::widget(['file' => 'boxes/banner' . (string)$template . '.tpl', 'params' => [
            'id' => $this->id,
            'banners' => $banners,
            'banner_type' => $this->settings[0]['banners_type'],
            'banner_speed' => $banner_speed,
            'settings' => $settings
        ]]);
    }

    public static function bannerGroupImages ($bannerGroupSettings, $bannersId, $mainImage, $title = '', $lazyLoad = false, $dontUseWebp = false){
        $languages_id = \Yii::$app->settings->get('languages_id');

        $naBanner = Info::themeSetting('base64_banner');
        if (!$naBanner) {
            $naBanner = Info::themeSetting('na_banner', 'hide');
        }

        $bannerGroupImages = BannersGroupsImages::find()
            ->where([ 'banners_id' => $bannersId, 'language_id' => $languages_id])
            ->asArray()
            ->all();

        $firstType = self::getMediaType($mainImage);

        if ($firstType == 'image') {
            $size = @getimagesize(Images::getFSCatalogImagesPath() . $mainImage);
            if (is_array($size)) {
                $heightPer = round($size[1] * 100 / $size[0] , 4);
            } else {
                $heightPer = 100;
            }
            Info::setScriptCss('
                #banner-' . $bannersId . ' {
                    padding-top: ' . $heightPer . '%;
                } .banner-box-' . $bannersId . ' picture {
                    padding-top: ' . $heightPer . '%;
                }');
        }

        $mainType = $firstType;
        foreach ($bannerGroupImages as $image){
            if (self::getMediaType($image['image']) == 'video') {
                $mainType = 'video';
            }
        }

        $sources = '';
        foreach ($bannerGroupImages as $image){
            if (!$bannerGroupSettings[$image['image_width']]) continue;

            $imageMedia = $image['image'];

            $mediaType = self::getMediaType($imageMedia);

            if (!$dontUseWebp && $mediaType == 'image') {
                $image['image'] = \common\classes\Images::getWebp($image['image']);
            }
            $image['image'] = \common\helpers\Media::getAlias('@webCatalogImages/' . $image['image']);

            $media = '';
            if ($bannerGroupSettings[$image['image_width']]['width_from']) {
                $media .= '(min-width: ' . $bannerGroupSettings[$image['image_width']]['width_from'] . 'px)';
            }
            if ($bannerGroupSettings[$image['image_width']]['width_from'] && $bannerGroupSettings[$image['image_width']]['width_to']) {
                $media .= ' and ';
            }
            if ($bannerGroupSettings[$image['image_width']]['width_to']) {
                $media .= '(max-width: ' . $bannerGroupSettings[$image['image_width']]['width_to'] . 'px)';
            }

            $sourcesAttr = [
                'srcset' => str_replace(' ', '%20', $image['image']),
                'media' => $media,
                'data-max' => $bannerGroupSettings[$image['image_width']]['width_to'],
                'data-min' => $bannerGroupSettings[$image['image_width']]['width_from'],
                'data-type' => $mediaType,
            ];

            if ($bannerGroupSettings[$image['image_width']]['image_width'] &&
                $bannerGroupSettings[$image['image_width']]['image_height']) {
                $heightPer = round($bannerGroupSettings[$image['image_width']]['image_height'] * 100 / $bannerGroupSettings[$image['image_width']]['image_width'], 4);
                $fit = $image['fit'] ? $image['fit'] : 'cover';
                $position = $image['position'] ? $image['position'] : 'center';
                Info::setScriptCss('@media ' . $media . ' {
                    .banner-box-' . $bannersId . ' {padding-top: ' . $heightPer . '%;display: block; position: relative}
                    .banner-box-' . $bannersId . ' > *, .banner-box-' . $bannersId . ' img {position: absolute!important; top: 0; left: 0; width: 100%; height: 100%; padding-top: 0!important; object-fit: ' . $fit . '; object-position: ' . $position . '}
                }');
            } elseif (is_file(Images::getFSCatalogImagesPath() . $imageMedia) && $mediaType == 'image') {
                $size = getimagesize(Images::getFSCatalogImagesPath() . $imageMedia);
                $heightPer = round($size[1] * 100 / $size[0], 4);
                Info::setScriptCss('@media ' . $media . ' {
                picture#banner-' . $bannersId . ' {
                    padding-top: ' . $heightPer . '%;
                } .banner-box-' . $bannersId . ' picture {
                    padding-top: ' . $heightPer . '%;
                }}');
            } elseif ($mediaType == 'video') {
                Info::setScriptCss('@media ' . $media . ' {video#banner-' . $bannersId . ' {padding-top: 0}}');
            }

            if ($lazyLoad && $mainType == 'image') {
                $sourcesAttr['data-srcset'] = $image['image'];
                $sourcesAttr['srcset'] = $naBanner;
                $sourcesAttr['class'] = 'na-banner';
            }
            $sources .= Html::tag('source', '', $sourcesAttr);
        }

        $pictureAttributes = [
            'id' => 'banner-' . $bannersId
        ];

        if (!$dontUseWebp) {
            $mainImage = \common\classes\Images::getWebp($mainImage);
        }
        $mainImage = \common\helpers\Media::getAlias('@webCatalogImages/' . $mainImage);

        $attributes = [
            'title' => $title
        ];
        if ($lazyLoad && $mainType == 'image') {
            $attributes['data-src'] = $mainImage;
            $attributes['class'] = 'na-banner';
            $mainImage = $naBanner;
        }

        if ($mainType == 'image') {
            $img = Html::img($mainImage, $attributes);
            return Html::tag('picture', $sources . $img, $pictureAttributes);
        } elseif ($mainType == 'video') {

            if ($firstType == 'image') {
                $pictureAttributes['poster'] = $mainImage;
            }
            $pictureAttributes['autoplay'] = 'autoplay';
            $pictureAttributes['muted'] = 'muted';
            $pictureAttributes['loop'] = 'loop';
            $pictureAttributes['controls'] = 'controls';

            $attributes['src'] = $mainImage;
            $attributes['class'] = 'main';
            $attributes['data-type'] = $firstType;
            $img = Html::tag('source', '', $attributes);
            return Html::tag('video', $sources . $img, $pictureAttributes);
        }
    }

    public static function getMediaType ($imageMedia)
    {
        $mediaType = 'image';
        if (is_file(Images::getFSCatalogImagesPath() . $imageMedia)) {
            $mediaType = explode('/', mime_content_type(Images::getFSCatalogImagesPath() . $imageMedia));
            $mediaType = $mediaType [0];
        } elseif (is_file(DIR_FS_CATALOG . $imageMedia)) {
            $mediaType = explode('/', mime_content_type(DIR_FS_CATALOG . $imageMedia));
            $mediaType = $mediaType [0];
        }

        return $mediaType;
    }

    public static function bannerGroupSvg ($bannerGroupSettings, $bannersId, $mainImage){
        $languages_id = \Yii::$app->settings->get('languages_id');

        $bannerGroupImages = BannersGroupsImages::find()
            ->where([ 'banners_id' => $bannersId, 'language_id' => $languages_id])
            ->asArray()
            ->all();

        $images = '<div class="banner-svg-' . $bannersId . ' banner-svg-' . $bannersId . '-main">' . $mainImage . '</div>';
        $styles = '.banner-svg-' . $bannersId . '{display:none}.banner-svg-' . $bannersId . '-main{display:block}';
        foreach ($bannerGroupImages as $image){
            if (!isset($bannerGroupSettings[$image['image_width']]) || !$image['svg']) continue;

            $images = $images . '<div class="banner-svg-' . $bannersId . ' banner-svg-' . $bannersId . '-' . $image['image_width'] . '">' . $image['svg'] . '</div>';

            $from = $bannerGroupSettings[$image['image_width']]['width_from'];
            $to = $bannerGroupSettings[$image['image_width']]['width_to'];

            $styles .= ' @media ';
            if ($from) {
                $styles .= '(min-width: ' . $from . 'px)';
            }
            if ($from && $to) {
                $styles .= ' and ';
            }
            if ($to) {
                $styles .= '(max-width: ' . $to . 'px)';
            }

            $styles .= '{.banner-svg-' . $bannersId . '{display:none}.banner-svg-' . $bannersId . '-' . $image['image_width'] . '{display:block}}';
        }

        Info::setScriptCss($styles);

        return $images;
    }

    public static $defaultSettings = [
        'effect' => 'random',
        'slices' => 15,
        'boxCols' => 8,
        'boxRows' => 4,
        'animSpeed' => 500,
        'pauseTime' => 3000,
        'directionNav' => 'true',
        'controlNav' => 'true',
        'controlNavThumbs' => 'false',
        'pauseOnHover' => 'true',
        'manualAdvance' => 'false',
    ];

    public static function textPosition ($key) {

        switch ($key) {
            case '0':
                return 'top-left';
            case '1':
                return 'top-center';
            case '2':
                return 'top-right';
            case '3':
                return 'middle-left';
            case '4':
                return 'middle-center';
            case '5':
                return 'middle-right';
            case '6':
                return 'bottom-left';
            case '7':
                return 'bottom-center';
            case '8':
                return 'bottom-right';
            case '9':
                return 'bottom-text';
        }
        return '';
    }

}
