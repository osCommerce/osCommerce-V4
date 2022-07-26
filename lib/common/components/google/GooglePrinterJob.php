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

namespace common\components\google;

use Yii;

class GooglePrinterJob {

    private $printer_id;
    private $title;
    
    public function __construct($printer_id) {
        $this->printer_id = $printer_id;
    }
    
    public function getPrinterId(){
        return $this->printer_id;
    }
    
    public function setTitle($title){
        $this->title = $title;
    }
    
    public function getTitle(){
        return $this->title ? $this->title : 'Printing process '. date("Y-m-d H:i:s");
    }
    
    private $copies;
    public function setCopies($copies){
        $this->copies = (int)$copies;
    }

    public function getCopies(){
        return $this->copies ? $this->copies : 1;
    }
    
    private $contentType;
    public function setContentType($type){
        $this->contentType = $type;
    }
    
    public function getContentType(){
        return $this->contentType ? $this->contentType : false;
    }

    private $version = '1.0';
    public function getTicket(){
        return [
            'version' => $this->version,
            'print' => [
                'copies' => ['copies' => $this->getCopies()]
            ]
        ];
    }
    
    private $lastJob;
    public function setLastJob($lastJob){
        if ($lastJob['id']){
            $this->lastJob = $lastJob;
        }
    }
    
    public function getLastJob(){
        return $this->lastJob;
    }
}
