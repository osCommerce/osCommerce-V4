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

namespace frontend\design\boxes\account;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class CombinedField extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if (!\common\helpers\Acl::checkExtensionAllowed('CustomerAdditionalFields')) return '';

        $content = preg_replace_callback('|[a-zA-Z\-\_]+|', function($matches){
            global $languages_id;

            $field = \common\extensions\CustomerAdditionalFields\models\CustomersAdditionalFields::find()
                ->alias('cf')
                ->select('cf.value')
                ->leftJoin('additional_fields f', 'cf.additional_fields_id = f.additional_fields_id')
                ->where([
                    'f.additional_fields_code' => $matches[0],
                ])
                ->asArray()
                ->one();

            return  $field['value'];

        }, $this->settings[0]['fields']);

        return $content;
    }
}
