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

class CaptchaWidget extends \yii\base\Widget
{
    public $publicKey;
    public $privateKey;
    public $version;
    public $owner;
    public $description;
    
    public function init(){
        parent::init();
    }
    
    public function run(){
        return $this->render('captcha-config', [
            'publicKey' => $this->publicKey,
            'privateKey' => $this->privateKey,
            'owner' => $this->owner,
            'version' => $this->version,
            'description' => $this->description,
        ]);
    }
}
