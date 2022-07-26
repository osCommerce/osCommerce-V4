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

namespace frontend\design\boxes\contact;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\forms\contact\Contact;

class ContactForm extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        \common\helpers\Translation::init('js');
        $action = filter_var(Yii::$app->request->get('action', ''), FILTER_SANITIZE_STRING);
        $contact = new Contact(['captcha' => $this->settings[0]['show_captcha'] ]);
        $customer = null;
        if (!Yii::$app->user->isGuest) {
            $customer = Yii::$app->user->getIdentity();
            $contact->preloadCustomersData($customer);
        }

        $info = [];
        if (Yii::$app->request->isPost && $action == 'send'){
            if ( $contact->load(Yii::$app->request->post()) && $contact->validate() ){
                $contact->sendMessage();                
                tep_redirect(tep_href_link(FILENAME_CONTACT_US, 'action=success'));
            } else {
                foreach($contact->getErrors() as $error){
                    $info[] = $error[0] ?? '';
                }
            }
        }

        $productId = Yii::$app->request->get('products_id', false);
        if ($productId) {
            $products = Yii::$container->get('products');
            $product = $products->getProduct($productId);
            $contact->content = $product['products_name'];
        }
        
        return IncludeTpl::widget(['file' => 'boxes/contact/contact-form.tpl', 'params' => [
            'info' => $info,
            'action' => $action,
            'settings' => $this->settings,
            'id' => $this->id,            
            'contact' => $contact,
            'info_id' => Yii::$app->request->get('info_id'),
        ]]);
    }
}