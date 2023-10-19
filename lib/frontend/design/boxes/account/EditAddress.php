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

namespace frontend\design\boxes\account;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\forms\AddressForm;

class EditAddress extends Widget
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
        global $breadcrumb, $navigation;

        \common\helpers\Translation::init('account/address-book-process');

        if (Yii::$app->user->isGuest) {
            $navigation->set_snapshot();
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }
        $messageStack = \Yii::$container->get('message_stack');

// error checking when updating or adding an entry
        $process = false;
        
        $deleteAction = (int)Yii::$app->request->get('delete', 0);
        $editAction = (int)Yii::$app->request->get('edit', 0);
        
        $customer = Yii::$app->user->getIdentity();
        
        $type = Yii::$app->request->get('type', '');
        switch ($type) {
            case 'billing':
                $scenario = AddressForm::BILLING_ADDRESS;
                break;
            case 'shipping':
                $scenario = AddressForm::SHIPPING_ADDRESS;
                break;
            default:
                $scenario = AddressForm::CUSTOM_ADDRESS;
                $type = '';
                break;
        }
        
        $bookModel = new AddressForm(['scenario' => $scenario]);
        $bookModel->type = $bookModel->addressType;

        if ($editAction > 0) {
            $entry = $customer->getAddressBook((int) $editAction);
            
            if (!$entry) {
                $messageStack->add_session(ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY, 'addressbook');

                tep_redirect(tep_href_link('account/address-book', '', 'SSL'));
            }
            $bookModel->preload($entry);
        } else {
            $bookModel->preloadDefault();
        }


        $action = tep_href_link('account/address-book-process', ($editAction > 0 ? 'edit=' . $editAction : ''), 'SSL');
        $title = ($editAction > 0 ? HEADING_TITLE_MODIFY_ENTRY : ($deleteAction > 0 ? HEADING_TITLE_DELETE_ENTRY : HEADING_TITLE_ADD_ENTRY));
        $message = '';
        if ($messageStack->size('addressbook') > 0) {
            $message = $messageStack->output('addressbook');
        }
        $address_label = '';
        if ($deleteAction > 0) {
            $address_label = \common\helpers\Address::address_label(Yii::$app->user->getId(), $deleteAction, true, ' ', '<br>');
        }
        $link_address_book = tep_href_link('account/address-book', '', 'SSL');
        $link_address_delete = tep_href_link('account/address-book-process', 'delete=' . $deleteAction . '&action=deleteconfirm', 'SSL');
                
        $links = array();
        if ($editAction > 0) {
            $links['back_url'] = tep_href_link('account/address-book', '', 'SSL');
            $links['back_text'] = IMAGE_BUTTON_BACK;
            $links['update'] = tep_draw_hidden_field('action', 'update') . tep_draw_hidden_field('edit', $editAction) . '<button class="btn-2">' . IMAGE_BUTTON_UPDATE . '</button>';
        } else {
            if (sizeof($navigation->snapshot) > 0) {
                $back_link = tep_href_link($navigation->snapshot['page'], \common\helpers\Output::array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
            } else {
                $back_link = tep_href_link('account/address-book', '', 'SSL');
            }
            $links['back_url'] = $back_link;
            $links['back_text'] = IMAGE_BUTTON_BACK;
            $links['update'] = tep_draw_hidden_field('action', 'process') . '<button class="btn-2">' . IMAGE_BUTTON_CONTINUE . '</button>';
        }

        $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        $breadcrumb->add(TEXT_ADDRESS_BOOK, tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));

        if ($editAction > 0) {
            $breadcrumb->add(NAVBAR_TITLE_MODIFY_ENTRY, tep_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'edit=' . $editAction, 'SSL'));
        } elseif ($deleteAction > 0) {
            $breadcrumb->add(NAVBAR_TITLE_DELETE_ENTRY, tep_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'delete=' . $deleteAction, 'SSL'));
        } else {
            $breadcrumb->add(NAVBAR_TITLE_ADD_ENTRY, tep_href_link(FILENAME_ADDRESS_BOOK_PROCESS, '', 'SSL'));
        }
        
        $postcoder = ($ext = \common\helpers\Acl::checkExtensionAllowed('AddressLookup')) ? $ext::getTool() : null;

        return IncludeTpl::widget(['file' => 'boxes/account/edit-address.tpl', 'params' => [
            'settings' => $this->settings,
            'id' => $this->id,
            'action' => $action,
            'title' => $title,
            'link_address_book' => $link_address_book,
            'link_address_delete' => $link_address_delete,
            'model' => $bookModel,
            'set_primary' => $bookModel->address_book_id != $customer->customers_default_address_id,
            'links' => $links,
            'message' => $message,
            'postcoder' => $postcoder,
            'type' => $type,
        ]]);
    }
}