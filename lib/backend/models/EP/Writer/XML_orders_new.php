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

namespace backend\models\EP\Writer;

use backend\models\EP\Exception;
use yii\base\BaseObject;

class XML_orders_new extends BaseObject implements WriterInterface
{
    public $column_separator = "\t";
    public $line_separator = "\r\n";

    public $filename;
    protected $file_handle;
    protected $_first_write = true;

    protected $columns = [];

    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        }catch (\Exception $ex){}
    }

    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }
    public function getFilenameFull() {
        return $this->filename_full;
    }
    public function write(array $writeData)
    {
        $order = $writeData['data'];
        if ( $this->_first_write ) {
            if ( strpos($this->filename,'php://')===false ) {
                if ( !is_dir(dirname($this->filename)) ) {
                    try{
                        \yii\helpers\FileHelper::createDirectory(dirname($this->filename), 0777, true);

                    }catch(\yii\base\Exception $ex){

                    }
                }
            }
            $this->file_handle = @fopen($this->filename,'w');
            if ( !$this->file_handle ) {
                throw new Exception('Can\'t open file', 21);
            }
            fwrite($this->file_handle, '<?xml version="1.0" encoding="utf-8"?>');
            fwrite($this->file_handle, '<Orders>');
            $this->_first_write = false;
        }
        $country='';
        $addr = '';
        $suburb = '';
        $city = '';
        $iso_code_2 = '';
        $postcode= '';
        if(isset($order->delivery['suburb'])){
            $suburb = htmlspecialchars($order->delivery['suburb']);
        }
        if(isset($order->delivery['street_address'])){
            $addr = htmlspecialchars($order->delivery['street_address']);
        }
        if(isset($order->delivery['country']['title'])){
            $country = htmlspecialchars($order->delivery['country']['title']);
        }
        if(isset($order->delivery['city'])){
            $city = htmlspecialchars($order->delivery['city']);
        }
        if(isset($order->delivery['country']['iso_code_2'])){
            $iso_code_2 = htmlspecialchars($order->delivery['country']['iso_code_2']);
        }
        if(isset($order->delivery['postcode'])){
            $postcode = htmlspecialchars($order->delivery['postcode']);
        }

        $line = "<Order>";
        $line .= "<EF_reference>".$order->info['orders_id']."</EF_reference>";
        $line .= "<EF_item_no>".$order->info['orders_id']."</EF_item_no>";
        $line .= "<Ship-to_Salutation>".htmlspecialchars($order->delivery['gender'])."</Ship-to_Salutation>";
        $line .= "<Ship-to_Firstname>".htmlspecialchars($order->delivery['firstname'])."</Ship-to_Firstname>";
        $line .= "<Ship-to_Lastname>".htmlspecialchars($order->delivery['lastname'])."</Ship-to_Lastname>";
        $line .= "<Ship-to_Telephone>".htmlspecialchars($order->delivery['landline'])."</Ship-to_Telephone>";
        $line .= "<Ship-to_Telephone_Mobile>".htmlspecialchars($order->delivery['telephone'])."</Ship-to_Telephone_Mobile>";
        $line .= "<Ship-to_Street>".$addr."</Ship-to_Street>";
        $line .= "<Ship-to_Address_2>".$suburb."</Ship-to_Address_2>";
        $line .= "<Ship-to_City>".$city."</Ship-to_City>";
        $line .= "<Ship-to_Country>".$country."</Ship-to_Country>";
        $line .= "<Ship-to_Country_Code>".$iso_code_2."</Ship-to_Country_Code>";
        $line .= "<Ship-to_Postcode>".$postcode."</Ship-to_Postcode>";
        $line .= "<Ship-to_Delivery_Details1></Ship-to_Delivery_Details1>";
        $line .= "<Requested_Delivery_Date>".htmlspecialchars(date('Ymd',strtotime($order->info['delivery_date'])))."</Requested_Delivery_Date>";
        $line .= "<Personal_Message>".htmlspecialchars($order->info['comments'])."</Personal_Message>";
        $line .= "<Export_Date>".date('Ymd',strtotime($order->info['date_purchased']))."</Export_Date>";
        $line .= "<Export_Time>".date('H:s',strtotime($order->info['date_purchased']))."</Export_Time>";
        foreach ($order->products as $product) {
            $line .= "<OrderLine>";
            $line .= "<Item>".htmlspecialchars($product['model'])."</Item>";
            $line .= "<Quantity>".htmlspecialchars($product['qty'])."</Quantity>";
            $line .= "<Unit_Price>".round($product['final_price'],2)."</Unit_Price>";
            $line .= "</OrderLine>";
        }
        $line .= "</Order>";
        unset($order);

        fwrite($this->file_handle, $line);
        fflush($this->file_handle);
    }

    public function close()
    {
        fwrite($this->file_handle, '</Orders>');
        fflush($this->file_handle);
    }

}