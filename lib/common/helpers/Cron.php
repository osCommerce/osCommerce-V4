<?php

namespace common\helpers;

class Cron
{

    private const DEFAULTS = [
//        'code' => will be set as array key
        'description' => '',
        'command' => '##PHP##', // cd ##SITE_ROOT## && - don't needed. CronSheduler sets site_root when command runs. Also && doesn't work on Windows
        'max_execution_time' => 300,
        'debug_to_file' => 0,
        'debug_to_email' => '',
        'sort' => 0,
        'active' => 0,
//        'options' => [
//            'command' => [
//                'visible' => false,
//                'editable' => false,
//            ],
//            'arguments' => [
//                0 => [
//                    'label' => 'Name',
//                    'visible' => false,
//                    'editable' => true,
//                ],
//            ]
//        ],
    ];

    /** All cron jobs in the core without extensions.
    *   Extension jobs must be declarated into Setup::getCronJobs()
    */
    private const JOBS = [

        /**  easy populate */
        // it makes sense to set these tasks directly in the crontab due to multithreading
        'ep_export' => [
            'group' => 'Import/Export',
            'name' => 'Automatic export',
            'schedule' => '* * * * *',
            'arguments' => ["yii.php","events/export"],
        ],
        'ep_import' => [
            'group' => 'Import/Export',
            'name' => 'Automatic import',
            'schedule' => '* * * * *',
            'arguments' => ["yii.php","events/import"],
        ],
        'ep_datasource' => [
            'group' => 'Import/Export',
            'name' => 'Automatic run datasource',
            'schedule' => '* * * * *',
            'arguments' => ["yii.php","events/datasource"],
        ],

        /** products */
        // supplier price
        'products_calc_price' => [
            'group' => 'Products',
            'name' => 'Calculate products prices',
            'description' => 'Automatic calculation of product prices based on the suppliers prices and formula',
            'schedule' => '*/5 * * * *',
            'arguments' => ["yii.php","events/auto-calc-price-by-supplier"],
        ],
        'notify_back_in_stock' => [
            'group' => 'Products',
            'name' => 'Notify back in stock',
            'description' => 'Sends back-in-stock emails to the customers',
            'schedule' => '* */1 * * *',
            'command' => '##WGET##',
            'arguments' => ["##SITE_URL##/cron/notify-back-in-stock"],
        ],
        'check_guest_accounts' => [
            'group' => 'Customers',
            'name' => 'Check guest accounts',
            'description' => 'If account created 3 months ago and over then delete. If account created 2 weeks ago then notify',
            'schedule' => '* */1 * * *',
            'command' => '##WGET##',
            'arguments' => ["##SITE_URL##/cron/check-guest-accounts"],
        ],
        'delete_old_accounts' => [
            'group' => 'Customers',
            'name' => 'Delete old accounts',
            'description' => 'Delete accounts when not used over 7 years',
            'schedule' => '* */1 * * *',
            'command' => '##WGET##',
            'arguments' => ["##SITE_URL##/cron/check-old-accounts"],
        ],
        'check_regular_offers' => [
            'group' => 'Customers',
            'name' => 'Check regular offers',
            'description' => 'Sends regular offers email to the customers',
            'schedule' => '* */1 * * *',
            'command' => '##WGET##',
            'arguments' => ["##SITE_URL##/cron/check-regular-offers"],
        ],
//        'reminder' => [
//            'group' => 'Customers',
//            'name' => 'Remind for orders',
//            'description' => 'Remind for orders when orders date is over 1 year (Needed activate in configuration "Use reminder")',
//            'schedule' => '* */1 * * *',
//            'command' => '##WGET##',
//            'arguments' => ["##SITE_URL##/cron/reminder"],
//        ],

        /** specials and featured products */
        // status
        'sfproducts_update_status' => [
            'group' => 'Specials and featured products',
            'name' => 'Updates status of specials and featured products',
            'description' => '',
            'schedule' => '* * * * *',
            'arguments' => ["yii.php","events/marketing-status"],
            'active' => 1,
        ],
        'sproducts_update_status' => [
            'group' => 'Specials and featured products',
            'name' => 'Updates status of specials products',
            'description' => '',
            'schedule' => '* * * * *',
            'arguments' => ["yii.php","events/specials-status"],
        ],
        'fproducts_update_status' => [
            'group' => 'Specials and featured products',
            'name' => 'Updates status of featured products',
            'description' => '',
            'schedule' => '* * * * *',
            'arguments' => ["yii.php","events/featured-status"],
        ],
        // clean up
        'sfproducts_clean_up' => [
            'group' => 'Specials and featured products',
            'name' => 'Clean up specials and featured products',
            'description' => '',
            'schedule' => '*/15 * * * *',
            'arguments' => ["yii.php","events/marketing-cleanup"],
            'active' => 1,
        ],
        'sproducts_clean_up' => [
            'group' => 'Specials and featured products',
            'name' => 'Clean up only specials products',
            'description' => '',
            'schedule' => '*/15 * * * *',
            'arguments' => ["yii.php","events/specials-cleanup"],
        ],
        'fproducts_clean_up' => [
            'group' => 'Specials and featured products',
            'name' => 'Clean up only featured products',
            'description' => '',
            'schedule' => '*/15 * * * *',
            'arguments' => ["yii.php","events/featured-cleanup"],
        ],
    ];

    public static function getExtensionJobs($extCode, $checkAllowed = true)
    {
        $res = self::getExtensionJobsInternal($extCode, $checkAllowed);
        self::normalizeJobs($res);
        return $res;
    }

    public static function getAllJobs()
    {
        $jobs = array_merge(self::JOBS, self::getAllExtensionsJobs());
        self::normalizeJobs($jobs);
        return $jobs;
    }

    // <editor-fold defaultstate="collapsed" desc="private functions">
    private static function normalizeJobs(array &$jobs)
    {
        foreach($jobs as $code => &$job) {
            $job['code'] ??= $code;

            foreach (self::DEFAULTS as $key=>$value) {
                $job[$key] ??= $value;
            }
        }
    }

    private static function getExtensionJobsInternal($extCode, $checkAllowed = true)
    {
        $jobs = [];
        if (($ext = \common\helpers\Acl::checkExtension($extCode, 'enabled')) && (!$checkAllowed || $ext::allowed())) {
            $jobs = $ext::getCronJobs(false);
            if (is_array($jobs)) {
                foreach ($jobs as $key => &$job) {
                    $job['extension'] = $ext::getModuleCode();
                    $job['group'] = 'Extensions';
                }
            }
        }
        return $jobs;
    }

    private static function getAllExtensionsJobs($className = null)
    {
        $res = [];
        foreach(new \DirectoryIterator(\Yii::getAlias('@common/extensions')) as $extDir){
            $jobs = self::getExtensionJobsInternal($extDir->getFilename());
            $res = array_merge($res, $jobs);
        }
        return $res;
    }
// </editor-fold>

}