<?php
if ($TabAccess->tabDataSave('TEXT_MAIN_DETAILS')) {
    if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductTemplates', 'allowed')) {
        $ext::productSubmit($products_id);
    }
}
