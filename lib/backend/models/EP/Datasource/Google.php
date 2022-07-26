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
use common\helpers\Categories;

class Google extends DatasourceBase
{

    public function getName()
    {
        return 'Google';
    }

    public function prepareConfigForView($configArray)
    {
        $orderStatusesSelect = [
            '*' => '[Any order status]',
        ];
        foreach( \common\helpers\Order::getStatusesGrouped(true) as $option){
            $orderStatusesSelect[$option['id']] = html_entity_decode($option['text'],null,'UTF-8');
        }
        $configArray['order']['export_statuses'] = [
            'items' => $orderStatusesSelect,
            'value' => $configArray['order']['export_statuses'],
            'options' => [
                'class' => 'form-control',
                'multiple' => true,
                'size' => 9,
                'options' => [],
            ],
        ];

        $configArray['delays']['latencity'] = isset($configArray['delays']['latencity'])?(int)$configArray['delays']['latencity']:2;
        $configArray['delays']['outdated'] = isset($configArray['delays']['outdated'])?(int)$configArray['delays']['outdated']:3;

        return parent::prepareConfigForView($configArray);
    }


    public function getViewTemplate()
    {
        return 'datasource/ecommerce-tracking';
    }

}
