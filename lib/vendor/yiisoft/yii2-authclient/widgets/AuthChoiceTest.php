<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\widgets;

use yii\base\InvalidConfigException;
use yii\base\Widget;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\authclient\ClientInterface;

/**
 * AuthChoice prints buttons for authentication via various auth clients.
 * It opens a popup window for the client authentication process.
 * By default this widget relies on presence of [[\yii\authclient\Collection]] among application components
 * to get auth clients information.
 *
 * Example:
 *
 * ```php
 * <?= yii\authclient\widgets\AuthChoice::widget([
 *     'baseAuthUrl' => ['site/auth']
 * ]); ?>
 * ```
 *
 * You can customize the widget appearance by using [[begin()]] and [[end()]] syntax
 * along with using method [[clientLink()]] or [[createClientUrl()]].
 * For example:
 *
 * ```php
 * <?php
 * use yii\authclient\widgets\AuthChoice;
 * ?>
 * <?php $authAuthChoice = AuthChoice::begin([
 *     'baseAuthUrl' => ['site/auth']
 * ]); ?>
 * <ul>
 * <?php foreach ($authAuthChoice->getClients() as $client): ?>
 *     <li><?= $authAuthChoice->clientLink($client) ?></li>
 * <?php endforeach; ?>
 * </ul>
 * <?php AuthChoice::end(); ?>
 * ```
 *
 * This widget supports following keys for [[ClientInterface::getViewOptions()]] result:
 *
 *  - popupWidth: int, width of the popup window in pixels.
 *  - popupHeight: int, height of the popup window in pixels.
 *  - widget: array, configuration for the widget, which should be used to render a client link;
 *    such widget should be a subclass of [[AuthChoiceItem]].
 *
 * @see \yii\authclient\AuthAction
 *
 * @property array $baseAuthUrl Base auth URL configuration. This property is read-only.
 * @property ClientInterface[] $clients Auth providers. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class AuthChoiceTest extends AuthChoice
{
    /**
     * @var string name of the auth client collection application component.
     * This component will be used to fetch services value if it is not set.
     */
    public $clientCollection = 'authClientCollection';
    /**
     * @var string name of the GET param , which should be used to passed auth client id to URL
     * defined by [[baseAuthUrl]].
     */
    public $clientIdGetParamName = 'authclient';
    /**
     * @var array the HTML attributes that should be rendered in the div HTML tag representing the container element.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];
    /**
     * @var array additional options to be passed to the underlying JS plugin.
     */
    public $clientOptions = [];
    /**
     * @var bool indicates if popup window should be used instead of direct links.
     */
    public $popupMode = true;
    /**
     * @var bool indicates if widget content, should be rendered automatically.
     * Note: this value automatically set to 'false' at the first call of [[createClientUrl()]]
     */
    public $autoRender = true;

    /**
     * @var array configuration for the external clients base authentication URL.
     */
    private $_baseAuthUrl;
    /**
     * @var ClientInterface[] auth providers list.
     */
    private $_clients;
    
    public $client;
    public $socials_id;
    public $platform_id;

    /**
     * Initializes the widget.
     */
    public function init()
    {
        $view = Yii::$app->getView();
        AuthChoiceStyleAsset::register($view);
        $this->options['id'] = $this->getId();
        echo Html::beginTag('div', $this->options);
    }

    /**
     * Runs the widget.
     * @return string rendered HTML.
     */
    public function run()
    {
        $content = '';
        $modules = $this->getClients();
        
        
        $result = (new \common\components\Socials($modules[$this->client]))->test($this->socials_id, $this->platform_id);
        
        http_response_code(200);
        
        if (!$result){
            $content .= TEST_NOT_PASSED . '<br>' . TEXT_CHECK_SOCIAL_MODULE_SETTINGS;
            $content .= ' ' . \common\components\Socials::getSiteUrl($this->client);
        } else {
            $content .= TEST_PASSED;
        }
        
        $content .= "<script>resetStatement();</script>";
        echo $content;
        exit();
    }
}