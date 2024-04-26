<?php

if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
    if ($ext::useWithBanners() && !\frontend\design\Info::isAdmin()) {
        $customerGroupsId = Yii::$app->storage->get('customer_groups_id');
        $andWhere .= " and (nb2p.user_groups like '%#" . $customerGroupsId . "#%' or nb2p.user_groups like '%#0#%')";
    }
}

