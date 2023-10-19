<?php
if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
    if ($ext::useWithBanners()) {
        $ext::saveBannerUserGroup();
    }
}

