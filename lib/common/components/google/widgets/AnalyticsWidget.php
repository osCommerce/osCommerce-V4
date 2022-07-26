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

namespace common\components\google\widgets;

use Yii;

class AnalyticsWidget extends \yii\base\Widget
{
    public $jsonFile;
    public $viewId;
    public $owner;
    public $description;
    public $platformId;
    
    public function init(){
        parent::init();
    }
    
    public function run(){
        
        Yii::$app->getView()->registerJsFile(Yii::$app->request->baseUrl . '/plugins/fileupload/jquery.fileupload.js');
        
        return $this->render('analytics-config', [
            'jsonFile' => $this->jsonFile,
            'viewId' => $this->viewId,
            'platformId' => $this->platformId,
            'owner' => $this->owner,
            'description' => $this->description,
        ]);
    }
}
