<?php

/*
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2005 Holbi Group Ltd
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\helpers;

use Yii;
use common\components\google\GooglePrinters;

class Printers {
    
    public static function getConfigPath(){
        return Yii::getAlias('@common'). DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'google' . DIRECTORY_SEPARATOR;
    }

    /**
     * setup alone job to first found printer
     * @param type $platform_id
     * @param type $title
     * @param type $job
     * @param type $mime
     * @return boolean
     */
    public static function setFileToQueue($platform_id, $title, $job, $mime = 'text/html'){
        
        if ($platform_id){
            $service = \common\models\CloudServices::find()->alias('s')
                    ->where(['s.platform_id' => $platform_id])
                    ->joinWith(['printers p'])->one();
        
            if ($service){
                $configDir = Yii::getAlias('@common'). DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'google' . DIRECTORY_SEPARATOR;
                $file = $configDir . $service->key;
                $googlePrinters = new GooglePrinters($file);
                if ($googlePrinters){
                    $processed = self::pushJobToPrinter($googlePrinters, $service->printers[0]->cloud_printer_id, $title, $job, $mime);
                    //echo'<pre>';print_r($processed);
                    return $processed['success'];
                }
            }
        }
        return false;
    }
    
    /**
     * setup job to Cloud printer via Google Printers environment
     * @param \common\components\google\GooglePrinters $googlePrinters
     * @param type $cloud_printer_id
     * @param type $title
     * @param type $content
     * @param type $mime
     * @return type
     */
    public static function pushJobToPrinter(GooglePrinters $googlePrinters, $cloud_printer_id, $title, $content, $mime = 'text/html'){
        $gJob = $googlePrinters->createJob($cloud_printer_id);
        $gJob->setTitle($title);
        $gJob->setContentType($mime);
        $response = $googlePrinters->processJob($content);
        return $response;
    }
    
    /**
     * set Jobs to Printers
     * @param type $documentName
     * @param type $platform_id
     * @param array $job - array([$title => 'Title', 'content' => 'Print Content', 'mime' => 'text/html'], []), content may be public link to file with mime => 'url'
     */
    public static function setJobsToOptimalQueue($documentName, $platform_id, array $jobs){
        $response = [];
        if ($platform_id){
            $service = \common\models\CloudServices::find()->alias('s')
                    ->where(['s.platform_id' => $platform_id])
                    ->innerJoinWith(['printers p' => function(\yii\db\ActiveQuery $query) use ($documentName){
                        $query->innerJoinWith(['documents d' => function($query) use ($documentName){
                            $query->onCondition(['d.document_name' => $documentName]);
                        } ]);
                    }])->one();//may be some services - need additional correlation about printing ways between services
            if($service){
                $configDir = Yii::getAlias('@common'). DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'google' . DIRECTORY_SEPARATOR;
                $file = $configDir . $service->key;
                $googlePrinters = new GooglePrinters($file);
                if ($googlePrinters){
                    if (is_array($service->printers)){
                        //may be some printers for one document - need corellation about printers adjustment
                        if (!is_array($jobs[0])){
                            $jobs = [ $jobs ];
                        }
                        
                        if (count($jobs) <= count($service->printers)){ //one job to one printer
                            foreach($jobs as $index => $job){
                                $job['mime'] = $job['mime'] ? $job['mime'] : 'text/html';
                                $jobResponse = self::pushJobToPrinter($googlePrinters, $service->printers[$index]->cloud_printer_id, $job['title'], $job['content'], $job['mime']);
                                $response[$index] = $jobResponse;
                            }
                        } else if (count($jobs) > count($service->printers)){
                            //spread jobs between printers
                            foreach($jobs as $index => $job){
                                $printer_id = self::getNextPrinter($service->printers)->cloud_printer_id;
                                $job['mime'] = $job['mime'] ? $job['mime'] : 'text/html';
                                $jobResponse = self::pushJobToPrinter($googlePrinters, $printer_id, $job['title'], $job['content'], $job['mime']);
                                $response[$index] = $jobResponse;
                            }
                        }
                    }
                }
            }
        }
        return $response;
    }
    
    public static function getNextPrinter(array $printersPool){
        static $index = 0;
        $printer = $printersPool[$index];
        $index++;
        if (!isset($printersPool[$index])) $index = 0;
        return $printer;
    }
  
}
