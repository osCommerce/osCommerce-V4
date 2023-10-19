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

namespace common\models;

use common\models\queries\OrdersQuery;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\ColumnSchema;

/**
 * This is the model class for table "orders".
 *
 * @property int $orders_id
 * @property int $platform_id
 * @property int $customers_id
 * @property int $basket_id
 * @property string $customers_name
 * @property string $customers_firstname
 * @property string $customers_lastname
 * @property string $customers_company
 * @property string $customers_company_vat
 * @property int $customers_company_vat_status
 * @property string $customers_customs_number
 * @property int $customers_customs_number_status
 * @property string $customers_street_address
 * @property string $customers_suburb
 * @property string $customers_city
 * @property string $customers_postcode
 * @property string $customers_state
 * @property string $customers_country
 * @property string $customers_telephone
 * @property string $customers_email_address
 * @property int $customers_address_format_id
 * @property string $delivery_gender
 * @property string $delivery_name
 * @property string $delivery_firstname
 * @property string $delivery_lastname
 * @property string $delivery_company
 * @property string $delivery_street_address
 * @property string $delivery_suburb
 * @property string $delivery_city
 * @property string $delivery_postcode
 * @property string $delivery_state
 * @property string $delivery_country
 * @property int $delivery_address_format_id
 * @property int $delivery_address_book_id
 * @property string $billing_gender
 * @property string $billing_name
 * @property string $billing_firstname
 * @property string $billing_lastname
 * @property string $billing_company
 * @property string $billing_street_address
 * @property string $billing_suburb
 * @property string $billing_city
 * @property string $billing_postcode
 * @property string $billing_state
 * @property string $billing_country
 * @property int $billing_address_format_id
 * @property int $billing_address_book_id
 * @property string $payment_method
 * @property string $payment_info
 * @property string $cc_type
 * @property string $cc_owner
 * @property string $cc_number
 * @property string $cc_expires
 * @property string $cc_cvn
 * @property string $last_modified
 * @property string $date_purchased
 * @property int $orders_status
 * @property string $orders_date_finished
 * @property string $currency
 * @property string $currency_value
 * @property string $currency_value_default
 * @property string $settlement_date
 * @property string $approval_code
 * @property string $transaction_id
 * @property string $shipping_class
 * @property string $payment_class
 * @property int $orders_type
 * @property int $admin_id
 * @property string $shipping_method
 * @property int $payment_id
 * @property int $language_id
 * @property int $search_engines_id
 * @property int $search_words_id
 * @property string $last_xml_import
 * @property string $last_xml_export
 * @property int $edit_orders_recalculate_totals
 * @property int $gv
 * @property string $customers_landline
 * @property string $tracking_number
 * @property double $lat
 * @property double $lng
 * @property string $shipping_weight
 * @property int $adjusted
 * @property int $stock_updated
 * @property int $reference_id
 * @property int $trustpilot_invited
 * @property string $trustpilot_invite_create_date
 * @property string $trustpilot_connect_via
 * @property string $delivery_date
 * @property string $bonus_points_redeem
 * @property int $bonus_applied
 * @property int $products_price_qty_round
 * @property string $external_orders_id
 * @property int $pointto
 * @property int $orders_allocate_allow
 */
class Orders extends ActiveRecord
{
	const ORDER_STATUS_PAYED = 100006;
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'orders';
    }

    public static function findByVar($orderOrId)
    {
        if ($orderOrId instanceof self) {
            return $orderOrId;
        } elseif (is_numeric($orderOrId)) {
            return self::findOne(['orders_id' => (int)$orderOrId]);
        }
    }

    /**
     * @param Orders|int $orderOrId
     * @return Orders
     * @throws \Exception
     */
    public static function findByVarCheck($orderOrId)
    {
        $res = self::findByVar($orderOrId);
        \common\helpers\Assert::instanceOf($orderOrId, self::class);
        return $res;
    }

    /**
     * one-to-many
     * @return array
     */
    public function getOrdersProducts()
    {
        return $this->hasMany(OrdersProducts::className(), ['orders_id' => 'orders_id']);
    }

    /**
     * one-to-many
     * @return array
     */
    public function getProducts()
    {
        return $this->hasMany(Products::className(), ['products_id' => 'products_id'])->via('ordersProducts');
    }

    /**
     * one-to-many
     * @return array
     */
    public function getInventory()
    {
        return $this->hasMany(Inventory::className(), ['products_id' => 'uprid'])->via('ordersProducts');
    }

    /**
     * one-to-many
     * @return array
     */
    public function getOrdersTotals()
    {
        return $this->hasMany(OrdersTotal::className(), ['orders_id' => 'orders_id'])->orderby('sort_order');
    }
    
    /**
     * one-to-many
     * @return ActiveQuery
     */
    public function getOrdersStatusHistory()
    {
        return $this->hasMany(OrdersStatusHistory::className(), ['orders_id' => 'orders_id']);
    }

    /**
     * one-to-many
     * @return array
     */
    public function getOrdersProductsAttributes()
    {
        return $this->hasMany(OrdersProductsAttributes::className(), ['orders_id' => 'orders_id']);
    }

    /**
     * one-to-one
     * @return object
     */
    public function getCustomer()
    {
        return $this->hasOne(Customers::className(), ['customers_id' => 'customers_id']);
    }


    public function changeStatus($status){
    	$this->orders_status = $status;
    	$this->save();
    }

    public function getOrdersStatus(){
        return $this->hasOne(OrdersStatus::className(), ['orders_status_id' => 'orders_status']);
    }

    public function getOrdersStatusGroup(){
        return $this->hasOne(OrdersStatusGroups::className(), ['orders_status_groups_id' => 'orders_status_groups_id'])->via('ordersStatus');
    }

	/**
	 * @inheritdoc
	 * @return OrdersQuery the active query used by this AR class.
	 *
	 */
	public static function find() {
		return new OrdersQuery( get_called_class() );
	}

	public function getNovaPoshtaShippingDeliveryAddress()
    {
        return $this->hasMany(ShippingNpOrderParams::class, ['orders_id' => 'orders_id']);
    }

    public function getTrackingNumbers()
    {
        return $this->hasMany(TrackingNumbers::className(), ['orders_id' => 'orders_id']);
    }
        
    public function getAdmin(){
        return $this->hasOne(Admin::className(), ['admin_id' => 'admin_id']);
    }
    
    public function getRedeemTrack(){
        return $this->hasMany(CouponRedeemTrack::className(), ['order_id' => 'orders_id']);
    }
    
    public function getSplinters(){
        return $this->hasMany(OrdersSplinters::className(), ['orders_id' => 'orders_id']);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ( $insert ) {
        /** @var common\extensions\InvoiceNumberFormat\InvoiceNumberFormat $ext */
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('InvoiceNumberFormat', 'allowed')) {
              if ( empty($this->order_number)) {
                $platform_id = $this->platform_id??(int)PLATFORM_ID;
                $this->order_number = $ext::getOrderNumber($platform_id);
              }
            }

            foreach ($this->getTableSchema()->columns as $column) {
                /**
                 * @var $column ColumnSchema
                 */
                if (!$column->allowNull && ($this->getAttribute($column->name) === null || $column->dbTypecast($this->getAttribute($column->name))===null) ) {
                    $defValue = $column->defaultValue;
                    if ( $column->dbTypecast($defValue)===null ) {
                        $defTypeValue = [
                            'boolean' => 0,
                            'float' => 0.0,
                            'decimal' => 0.0,
                        ];
                        if ( stripos($column->type,'int')!==false ) {
                            $defValue = 0;
                        }else{
                            $defValue = isset($defTypeValue[$column->type])?$defTypeValue[$column->type]:'';
                        }
                    }
                    $this->setAttribute($column->name, $defValue);
                }
            }
        }

        return true;
    }


}
