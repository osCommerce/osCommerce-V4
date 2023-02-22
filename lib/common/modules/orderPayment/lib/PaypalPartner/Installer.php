<?php
/**
 * Transactional Midle Ware for Paypal modules
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\modules\orderPayment\lib\PaypalPartner;

class Installer extends \common\classes\Migration {
    
    public function install(){
        $this->compact = true;
        if (!$this->isTableExists('paypal_seller_info')){
            $this->createTable('paypal_seller_info', [
                'psi_id' => $this->primaryKey(),
                'partner_id' => $this->string(32)->notNull()->defaultValue(''),
                'fee_percent' => $this->float()->notNull()->defaultValue(0),
                'platform_id' => $this->integer(11)->notNull()->defaultValue(0),
                'tracking_id' => $this->string(127)->notNull()->defaultValue(''),
                'email_address' => $this->string(64)->notNull()->defaultValue(''),
                'payer_id' => $this->string(32)->notNull()->defaultValue(''),
                'entry_company' => $this->string(32)->notNull()->defaultValue(''),
                'entry_firstname' => $this->string(32)->notNull()->defaultValue(''),
                'entry_lastname' => $this->string(32)->notNull()->defaultValue(''),                
                'entry_street_address' => $this->string(64)->notNull()->defaultValue(''),
                'entry_suburb' => $this->string(32),
                'entry_postcode' => $this->string(10)->notNull()->defaultValue(''),
                'entry_city' => $this->string(32)->notNull()->defaultValue(''),
                'entry_state' => $this->string(32),
                'entry_country_id' => $this->integer(11)->notNull()->defaultValue(0),
                'entry_zone_id' => $this->integer(11)->notNull()->defaultValue(0),
                'is_onboard' => $this->integer(1)->notNull()->defaultValue(0),
                'entry_telephone' => $this->string(32)->notNull()->defaultValue(''),
                'fee_editable' =>  $this->integer(1)->notNull()->defaultValue(0),
                'own_client_id' => $this->string(255)->notNull()->defaultValue(''),
                'own_client_secret' => $this->string(255)->notNull()->defaultValue(''),

                'paypal_partner_ccp_status' => $this->integer(1)->notNull()->defaultValue(0),
                'three_ds_settings' => $this->string(4096)->notNull()->defaultValue(''),
                'boarding_json' => $this->text()->notNull()->defaultValue(''),
                'boarding_date' => $this->date()->notNull()->defaultValue('0000-00-00'),
                'status' => $this->integer(11)->notNull()->defaultValue(0),
              
            ], 'engine=InnoDB');
        }
    }
    
    public function remove($platform_id){
        $this->compact = true;
        if ($this->isTableExists('paypal_seller_info')){
            models\SellerInfo::deleteAll(['platform_id' => $platform_id]);
            //2do? at least check if removed on all platforms $this->dropTable('paypal_seller_info');
        }
    }
}