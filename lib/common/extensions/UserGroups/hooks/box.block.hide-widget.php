<?php

if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
    if ($settings[0]['user_groups'] ?? false) {
        $groupIds = explode(',', $settings[0]['user_groups']);
        $customerGroupsId = \Yii::$app->storage->get('customer_groups_id');
        if (!in_array($customerGroupsId, $groupIds)) {
            $hide = true;
        }
    }
}