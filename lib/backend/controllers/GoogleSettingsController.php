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

namespace backend\controllers;

use Yii;
use common\components\Socials;

class GoogleSettingsController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_GOOGLE_SETTINGS', 'BOX_GOOGLE_MAIN_SETTINGS'];

    public function __construct($id, $module) {
        parent::__construct($id, $module);
    }

    public function actionIndex() {

        $this->selectedMenu = array('settings', 'google-settings', 'google-settings');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('google-settings/index'), 'title' => BOX_GOOGLE_MAIN_SETTINGS);
        $this->view->headingTitle = BOX_GOOGLE_MAIN_SETTINGS;
        
        $googleTools = new \common\components\GoogleTools();
        
        $selected = null;
        $messages = [];
        if (Yii::$app->request->isPost){
            $provider = $googleTools->getProvider(Yii::$app->request->post('provider'));
            if ($provider){
                $platform_id = (int)Yii::$app->request->post('platform_id');
                if ($googleTools->updateProviderConfig($provider, Yii::$app->request->post($provider->getClassName()), $platform_id)){
                    $messages[] = [
                        'type' => 'alert-success',
                        'info' => $provider->getName() .' saved successfully',
                    ];
                } else {
                    $messages[] = [
                        'type' => 'alert-danger',
                        'info' => $provider->getName() . ' error',
                    ];
                }
            }
        }
        
        $platforms = [];
        $platforms[] = [
            'id' => 0,
            'text' => (defined('TEXT_DEFAULT') ? TEXT_DEFAULT : 'Default'),
        ];
        $platforms = array_merge($platforms, \common\classes\platform::getList(false, true));
        

        
        $_provider = $googleTools->getCaptchaProvider();
        $_provider->platforms = $platforms;
        
        $providers = [
            'independed' => [
                $googleTools->getMapProvider(),
            ],
            'platformed' => [
                $_provider,
            ],
            'selected' => $selected,
        ];
        
        unset($platforms[0]);
        
        foreach([$googleTools->getAnalyticsProvider(), ] as $_provider){
            $_provider->platforms = $platforms;
            $providers['platformed'][] = $_provider;
        }
        
        return $this->render('index', [
            'providers' => $providers,
            'messages' => $messages,
            'platforms' => $platforms,
        ]);
    }

}
