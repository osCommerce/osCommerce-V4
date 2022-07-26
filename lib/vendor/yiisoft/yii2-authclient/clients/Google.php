<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * Google allows authentication via Google OAuth.
 *
 * In order to use Google OAuth you must create a project at <https://console.developers.google.com/project>
 * and setup its credentials at <https://console.developers.google.com/apis/credentials?project=[yourProjectId]>.
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'google' => [
 *                 'class' => 'yii\authclient\clients\Google',
 *                 'clientId' => 'google_client_id',
 *                 'clientSecret' => 'google_client_secret',
 *             ],
 *         ],
 *     ]
 *     // ...
 * ]
 * ```
 *
 * @see https://console.developers.google.com/project
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
use Yii;
class Google extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $authUrl = 'https://accounts.google.com/o/oauth2/auth';
    /**
     * {@inheritdoc}
     */
    public $tokenUrl = 'https://accounts.google.com/o/oauth2/token';
    /**
     * {@inheritdoc}
     */
    public $apiBaseUrl = 'https://www.googleapis.com/oauth2/v1';


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->scope === null) {
            $this->scope = implode(' ', [
                'https://www.googleapis.com/auth/userinfo.profile',
                'https://www.googleapis.com/auth/userinfo.email',
            ]);
        }
    }

    public static function getAddonSettings(){
        return [
        /*'recaptcha' => [
                'RECAPTCHA_PUBLIC_KEY' => ['value' => '', 'description' => 'Public key'],
                'RECAPTCHA_SECRET_KEY' => ['value' => '', 'description' => 'Secret Key'],
            ],
        'analytics' => [
                'GAPI_SETTINGS' => ['value' => '', 'description' => 'Service account credentials (service-account-credentials.json) file', 'type' => 'file'],
                'GAPI_VIEW_ID' => ['value' => '', 'description' => 'google Analytics View ID'],
            ]*/
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function initUserAttributes()
    {
        return $this->api('userinfo', 'GET');
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultName()
    {
        return 'google';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return 'Google';
    }
    
    public function prepareAttributes($attributes) {
        $prepared = [];
        if (is_array($attributes)) {
            //$prepared['gender'] = @$attributes['gender'] == 'male' ? 'm' : 'f';
            $prepared['email'] = @$attributes['email'];
            $prepared['firstname'] = @$attributes['given_name'];
            $prepared['lastname'] = @$attributes['family_name'];
            $prepared['avatar'] = @$attributes['picture'];
        }

        return $prepared;
    }
}
