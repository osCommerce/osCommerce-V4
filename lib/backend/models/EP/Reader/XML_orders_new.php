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

namespace backend\models\EP\Reader;

use backend\models\EP\Exception;
use yii\base\BaseObject;

class XML_orders_new extends BaseObject implements ReaderInterface
{
    const MAX_LINE_LENGTH = 1000000;

    public $column_separator = "auto";
    public $column_enclosure = '"';
    public $data_escape = '\\';
    public $line_separator = "\r\n";
    public $input_encoding = 'auto';
    public $without_header = false;
    private $tag;
    private $doc;
    public $filename;

    protected $file_handle;

    protected $file_header;
    protected $use_config = array(
        'column_separator' => "auto",
        'column_enclosure' => '"',
        'data_escape' => '\\',
        'line_separator' => "auto",
        'input_encoding' => 'auto',
    );
    private $file_start_pointer = 0;
    private $file_end_pointer = 0;
    private $file_data_start_pointer;

    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        }catch (\Exception $ex){}
    }

    public function currentPosition()
    {
        if ($this->file_handle) {
            return ftell($this->file_handle);
        }
        return 0;
    }

    public function getProgress()
    {
        $percentDone = min(100,($this->file_start_pointer/$this->file_end_pointer)*100);
        return number_format(  $percentDone,1,'.','');
    }

    public function setDataPosition($position)
    {
        return false;
    }

    public function readColumns()
    {
        return array( 'name' => 'undefined', 'value' => 'null', );
    }

    public function read()
    {
        $data = false;
  
        if ( !$this->file_handle ) {

            $tmpReader = new \XMLReader();
            $tmpReader->open($this->filename);
            while ($tmpReader->read() && $tmpReader->name !== 'Order');
            do {
                    $this->file_end_pointer++;
                    $tmpReader->next('Order');

            } while ($tmpReader->name === 'Order');
            unset($tmpReader);
            $this->file_handle = new \XMLReader();
            $this->file_handle->open($this->filename);
            $this->doc = new \DOMDocument;
            while ($this->file_handle->read() && $this->file_handle->name !== 'Order');
        }else{
            $this->file_handle->next('Order');
        }
        

        if ($this->file_handle->name === 'Order') {
            $data = simplexml_import_dom($this->doc->importNode($this->file_handle->expand(), true));
            $data = $this->SimpleXML2Array($data);
            $data['row']=$data;
            $this->file_start_pointer++;
            //while ($this->file_handle->read() && $this->file_handle->name !== 'Order');
        }

        return $data;
    }

    public function SimpleXML2Array($xml){
        $array = (array)$xml;
        //recursive Parser
        foreach ($array as $key => $value){
            if(is_object($value) || is_array($value)){
                $array[$key] = $this->SimpleXML2Array($value);
            }
        }
        if(empty($array)){
            return '';
        }
        return $array;
    }

}