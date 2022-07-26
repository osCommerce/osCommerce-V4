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
namespace common\modules\orderPayment\lib\PaypalPartner\models;

use Yii;

/**
 * This is the model class for table "paypal_seller_info".
 *
 * @property int $psi_id
 * @property int $platform_id
 * @property string $partner_id
 * @property float $fee_percent
 * @property string $tracking_id
 * @property string $payer_id
 * @property string $entry_company
 * @property string $email_address
 * @property string $entry_firstname
 * @property string $entry_lastname
 * @property string $entry_street_address
 * @property string $entry_suburb
 * @property string $entry_postcode
 * @property string $entry_city
 * @property string $entry_state
 * @property int $entry_country_id
 * @property int $entry_zone_id
 * @property int $is_onboard
 * @property string $entry_telephone
 * @property int $fee_editable flag
 * @property string $own_client_id
 * @property string $own_client_secret
 * @property text $boarding_json
 * @property date $boarding_date
 */
 
class SellerInfo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'paypal_seller_info';
    }
    
    public function rules(){
        return [
            [['platform_id', 'tracking_id'], 'required'],
            [[ 'email_address', 'entry_firstname', 'entry_lastname', 'entry_street_address', 'entry_postcode', 'entry_city', 'entry_state', 'entry_country_id', 'is_onboard', 'entry_company', 'partner_id', 'fee_percent', 'entry_telephone', 'fee_editable', 'own_client_id', 'own_client_secret'], 'safe'],
            [['entry_suburb', 'entry_zone_id', 'payer_id'], 'safe'],
        ];
    }
    
    public static function find(){
        return new query\SellerInfoQuery(get_called_class());
    }
    
    public static function generateTrackingId(){
        return sha1(uniqid());
    }
    
    public function isOnBoarded(){
        return !!$this->is_onboard;
    }
    
    public function updateMerchantId($merchantId){
        if ($this->psi_id){
            $this->setAttribute('payer_id', $merchantId);
            if (!$this->save()){
                throw new \Exception("Seller: payer_id can't be updated");
            }
            return true;
        }
        trigger_error("updateMerchantId Invalid Seller");
    }
    
    public function setOnBoarded(){
        if ($this->psi_id){
            $this->setAttribute('is_onboard', 1);
            if (!$this->save()){
                throw new \Exception("Seller can't be saved as onBoarded");
            }
            return true;
        }
        trigger_error("setOnBoarded Invalid Seller");
    }

    public function beforeDelete() {
        \yii\caching\TagDependency::invalidate(\Yii::$app->cache, 'seller-'. $this->platform_id . '-' . $this->partner_id);
        return parent::beforeDelete();
    }
}
