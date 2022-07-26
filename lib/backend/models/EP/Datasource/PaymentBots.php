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

namespace backend\models\EP\Datasource;

use backend\models\EP\DatasourceBase;

class PaymentBots extends DatasourceBase
{

    public function getName()
    {
        return 'Payment Bots';
    }

    public function prepareConfigForView($configArray)
    {   
        
        $configArray['platforms_list'] = \common\classes\platform::getList(false);
        $manager = \common\services\OrderManager::loadManager(new \common\classes\shopping_cart);
        $allModules = [];
        $builder = new \common\classes\modules\ModuleBuilder($manager);
            
        foreach($configArray['platforms_list'] as &$platform){
            $installed = (new \yii\db\Query())->select('configuration_value')->from("platforms_configuration")
                    ->where(['configuration_key' => 'MODULE_PAYMENT_INSTALLED', 'platform_id' => $platform['id']])
                    ->one();
            $platform['modules'] = [];
            if ($installed){
                $modules = explode(";", $installed['configuration_value']);
                foreach($modules as $module){
                    $class = pathinfo($module, PATHINFO_FILENAME);
                    if (!isset($allModules[$class]) && class_exists("\common\modules\orderPayment\\{$class}")){
                        $allModules[$class] = $builder(['class' => "\\common\\modules\\orderPayment\\{$class}"]);
                    }
                    if (is_object($allModules[$class]) && $allModules[$class] instanceof \common\classes\modules\TransactionSearchInterface ){
                        $platform['modules'][$class] = [
                            'class' => $class,
                            'title' => $allModules[$class]->title,
                            'fields'=> $allModules[$class]->getFields()
                        ];
                    }
                }
            }
        }
        return parent::prepareConfigForView($configArray);
    }


    public function getViewTemplate()
    {
        return 'datasource/payments.tpl';
    }

}
