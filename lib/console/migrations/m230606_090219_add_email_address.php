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

use common\classes\Migration;

/**
 * Class m230606_090219_add_email_address
 */
class m230606_090219_add_email_address extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isFieldExists('entry_email_address', 'address_book')) {
            $this->addColumn('address_book','entry_email_address',$this->string(96)->notNull()->defaultValue(''));
        }


        foreach ( ['SHIPPING', 'BILLING', 'ACCOUNT'] as $k) {
            if ($k=='ACCOUNT') { // I like it .... (evil)
                $gid = 'BOX_CONFIGURATION_CUSTOMER_DETAILS';
                $title = 'Address book email';
            } else {
                $gid = 'BOX_' . $k . '_CUSTOMER_DETAILS';
                $title = 'Email address';
            }
            $key = $k . '_EMAIL_ADDRESS';
            $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => $key])->exists();
            if (!$check) {
                $this->insert('configuration', [
                    'configuration_key' => $key,
                    'configuration_title' => $title,
                    'configuration_description' => 'Display customs email address in the address details',
                    'configuration_group_id' => $gid,
                    'configuration_value' => 'visible',
                    'set_function' => "tep_cfg_select_option(array('disabled', 'visible', 'visible_register', 'required', 'required_register', 'required_company'),",
                    'sort_order' => '40',
                    'date_added' => (new yii\db\Expression('now()'))
                ]);
            }
        }

        $this->addTranslation('admin/main',[
            'ENTRY_EMAIL_ADDRESS_ADRESS_BOOK' => 'Email address',
            'ENTRY_EMAIL_ADDRESS_ADRESS_BOOK_ERROR' => 'Please enter the email address',
        ]);
        $this->addTranslation('main',[
            'ENTRY_EMAIL_ADDRESS_ADRESS_BOOK' => 'Email address',
            'ENTRY_EMAIL_ADDRESS_ADRESS_BOOK_ERROR' => 'Please enter the email address',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->removeTranslation('admin/main',[
            'ENTRY_EMAIL_ADDRESS_ADRESS_BOOK',
            'ENTRY_EMAIL_ADDRESS_ADRESS_BOOK_ERROR',
        ]);
        $this->removeTranslation('main',[
            'ENTRY_EMAIL_ADDRESS_ADRESS_BOOK',
            'ENTRY_EMAIL_ADDRESS_ADRESS_BOOK_ERROR',
        ]);
    }
}
