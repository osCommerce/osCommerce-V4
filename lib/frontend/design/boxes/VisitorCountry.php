<?php

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class VisitorCountry extends Widget {

    public $params;
    public $settings;

    public function run() {

        $selected_variant = false;
        $auto_open_popup = false;
        $plaformLinks = [];
        $currentPlatformId = Yii::$app->get('platform')->config()->getId();

//        if (!isset($_SESSION['selected_country_id'])) {
            if (isset($_COOKIE['selected_country_id']) && (int) $_COOKIE['selected_country_id']>0) {
                $selected_country_id = (int) $_COOKIE['selected_country_id'];
                $shipCountriesIds = \yii\helpers\ArrayHelper::getColumn(\common\helpers\Country::getPlatformCountries(null, 'ship'), 'id');
                if (in_array($selected_country_id, $shipCountriesIds)) {
                    $_SESSION['selected_country_id'] = (int) $_COOKIE['selected_country_id'];
                } else {
                    $auto_open_popup = true;
                }
            } else {
                $_SESSION['selected_country_id'] = $currentPlatformId;
                $auto_open_popup = true;
            }
//        }


        $backup_country = false;
        $platforms = (new \yii\db\Query())->from('platforms pl')->andWhere(' pl.status=1 and pl.is_virtual=0 and pl.is_marketplace=0 ')
            ->select('platform_id, platform_url_secure, platform_url')
            ->indexBy('platform_id')->all();
        $country_variants = \common\helpers\Country::getShippingCountriesToPlatforms();
        foreach ($country_variants as $k => $_country_variant) {
            $country_variants[$k]['iso2'] = strtolower($_country_variant['countries_iso_code_2']);
            if ($currentPlatformId != $_country_variant['platform_id']) {
                if (!isset($plaformLinks[$_country_variant['platform_id']])) {
                    /* get shit with default platform ....*/

                    // save current params
                    $HostInfo = Yii::$app->urlManager->getHostInfo();
                    $BaseUrl = Yii::$app->urlManager->getBaseUrl();

                    $pc = new \common\classes\platform_config($_country_variant['platform_id']);
                    /*if (!empty($platforms[$_country_variant['platform_id']]['platform_url_secure'])) {
                        $parsed = parse_url(
                            'https://' . rtrim($platforms[$_country_variant['platform_id']]['platform_url_secure']) . '/'
                            );
                    }
                    else*/
                    if (!empty($platforms[$_country_variant['platform_id']]['platform_url'])) {
                        $parsed = parse_url(
                            'http://' . rtrim($platforms[$_country_variant['platform_id']]['platform_url']) . '/'
                            );
                    } else {
                        $parsed = parse_url($pc->getCatalogBaseUrl(false, false));
                    }

                    \Yii::$app->urlManager->setHostInfo($parsed['scheme'] . '://' . $parsed['host'] . (!empty($parsed['port']) && ! in_array($parsed['port'], ['80', '443'])?':'.$parsed['port']:''));
                    Yii::$app->urlManager->setBaseUrl(rtrim($parsed['path']));
                    $plaformLinks[$_country_variant['platform_id']] = Yii::$app->urlManager->createAbsoluteUrl(['index/select-country', 'rdrct' => '1'], $parsed['scheme'], true);
                    // restore params
                    Yii::$app->urlManager->setHostInfo($HostInfo);
                    Yii::$app->urlManager->setBaseUrl($BaseUrl);
                    /* */

                }

                $country_variants[$k]['link'] = $plaformLinks[$_country_variant['platform_id']] . '&selected_country_id=' .  $_country_variant['id'];
            }

            if ($selected_country_id == $_country_variant['id']) {
                $country_variants[$k]['selected'] = true;
                $selected_variant = $_country_variant;
            }
            if ($backup_country === false) {
                $backup_country = $_country_variant;
            }
            if (Yii::$app->get('platform')->config()->const_value('STORE_COUNTRY') == $_country_variant['id']) {
                $backup_country = $_country_variant;
            }
            
        }

        if (empty($selected_variant) && !empty($backup_country) && $backup_country !== false) {
            $selected_variant = $backup_country;
            $_SESSION['selected_country_id'] = $backup_country['id'];
        }


        if (count($country_variants) == 0) {
            return '';
        }
        if (!empty($selected_variant)) {
            //selected variant - first in list.
            array_unshift($country_variants, $country_variants[$selected_variant['id']]);
            unset($country_variants[$selected_variant['id']]);
        }

        return IncludeTpl::widget([
              'file' => 'boxes/visitor-country.tpl',
              'params' => [
                'block_id' => rand(10000, 99999),
                'auto_open_popup' => $auto_open_popup,
                'countries_variants' => $country_variants,
                'selected_variant' => $selected_variant,
                'cookie_params' => \common\helpers\System::get_cookie_params(),
                'country_selector_store_url' => Yii::$app->urlManager->createUrl('index/select-country'),
              ],
        ]);
    }

}
