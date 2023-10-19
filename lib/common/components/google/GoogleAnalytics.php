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

namespace common\components\google;

use Yii;

/**
 * @note Google namespaces changed since library update
 * @see lib/vendor/google/apiclient/src/aliases.php
 */

use /*Google\*/ Google_Client;
use /*Google\*/ Google_Service_Resource;
use /*Google\*/ Google_Service_TagManager;
use /*Google\*/ Google_Service_TagManager_Account;
use /*Google\*/ Google_Service_Analytics;
use /*Google\*/ Google_Service_AnalyticsReporting;
use /*Google\*/ Google_Service_AnalyticsReporting_DateRange;
use /*Google\*/ Google_Service_AnalyticsReporting_Metric;
use /*Google\*/ Google_Service_AnalyticsReporting_ReportRequest;
use /*Google\*/ Google_Service_AnalyticsReporting_GetReportsRequest;
use /*Google\*/ Google_Service_AnalyticsReporting_GetReportsResponse;
use /*Google\*/ Google_Service_AnalyticsReporting_Dimension;
use /*Google\*/ Google_Service_AnalyticsReporting_DimensionFilter;
use /*Google\*/ Google_Service_AnalyticsReporting_DimensionFilterClause;

class GoogleAnalytics {

    private $client;
    private $reporting;
    private $config = [];
    private static $scopes = ['https://www.googleapis.com/auth/analytics.readonly'];

    public function __construct($config_file, $view_id) {
        
        if (empty($config_file)) { // avoid fatal on file_get_contents
            throw new \Exception('Configuration file is not set');
        }
        $content = @file_get_contents($config_file);
        if ( !$content ) {
            throw new \Exception('Configuration file ' . basename($config_file) . ' could not be loaded: ' . error_get_last()['message']);
        }

        try {
            $this->config['privacy'] = json_decode($content, true);
        } catch (\Exception $ex) {
            throw new \Exception('Configuration file ' . basename($config_file) . ' could not be loaded');
        }
        $this->config['view_id'] = $view_id;
        if (empty($this->config['view_id']))
            throw new \Exception('Analytics View ID is not defined');
    }

    public function prepareReporting() {
        try {
            $this->client = new Google_Client();
            $this->client->setApplicationName("Analytics Reporting");

            $this->client->setAuthConfig($this->config['privacy']);
            $this->client->setScopes(self::$scopes);
            $this->reporting = new Google_Service_AnalyticsReporting($this->client);
            return $this->reporting;
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    protected function getViewId() {
        return $this->config['view_id'];
    }

    public function getReport($filters = []) {
        // Create the DateRange object.
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        if (isset($filters['date_range'][0])) {
            $dateRange->setStartDate($filters['date_range'][0]);
        } else {
            $dateRange->setStartDate(date("Y-m-d", strtotime("-1 year")));
        }

        if (isset($filters['date_range'][1])) {
            $dateRange->setEndDate($filters['date_range'][1]);
        } else {
            $dateRange->setEndDate('today');
        }

        if (isset($filters['dimensions'])) {
            if (is_array($filters['dimensions']) && count($filters['dimensions'])) {
                $dimentions = [];
                foreach ($filters['dimensions'] as $dim) {
                    $d = new Google_Service_AnalyticsReporting_Dimension();
                    $d->setName($dim);
                    $dimentions[] = $d;
                }
            }
        }

        if (isset($filters['metrics'])) {
            if (is_array($filters['metrics']) && count($filters['metrics'])) {
                $metrics = [];
                foreach ($filters['metrics'] as $met) {
                    $m = new Google_Service_AnalyticsReporting_Metric();
                    $m->setExpression($met);
                    $metrics[] = $m;
                }
            }
        }

        $request = new Google_Service_AnalyticsReporting_ReportRequest();

        $request->setViewId($this->getViewId());

        $request->setDateRanges($dateRange);

        if (is_array($metrics??null)) {
            $request->setMetrics($metrics);
        }

        if (is_array($dimentions)) {
            $request->setDimensions($dimentions);
        }
        
        if (isset($filters['dimensionsFilter'])) {
            if (is_array($filters['dimensionsFilter']) && count($filters['dimensionsFilter'])) {
                $dimentionsFilters = [];
                foreach($filters['dimensionsFilter'] as $dFilter){
                    $filter = new \Google\Google_Service_AnalyticsReporting_DimensionFilter();
                    $filter->setDimensionName($dFilter['dimension']);
                    $filter->setExpressions($dFilter['expression']);
                    $filter->setOperator($dFilter['operator']);
                    $dimentionsFilters[] = $filter;
                }         
                if ($dimentionsFilters){
                    $filter_clause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
                    $filter_clause->setFilters($dimentionsFilters);
                    $request->setDimensionFilterClauses($filter_clause);
                }
            }
        }

        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests(array($request));

        return $this->reporting->reports->batchGet($body);
    }
    
    public function getUACode(){
        $detectedUaCode = false;
        $analytics = new Google_Service_Analytics($this->client);
        if ($analytics){
            try{
                $accounts = $analytics->management_accountSummaries->listManagementAccountSummaries();
                $models = $accounts->getModelData();
                if (isset($models['items']) && is_array($models['items'])){ // has included accounts
                    foreach($models['items'] as $acc){
                        $webProperties = $acc['webProperties'];
                        if ($webProperties){
                            foreach($webProperties as $webProperty){
                                if ($webProperty['profiles']){
                                    $viewsID = \yii\helpers\ArrayHelper::getColumn($webProperty['profiles'], 'id');
                                    
                                    if (is_array($viewsID) && in_array($this->getViewId(), $viewsID)){
                                        $detectedUaCode = $webProperty['id'];
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $ex) {
              \Yii::info($ex->getMessage(), 'Google analytics Exception');
                //var_dump($ex->getMessage());
            }
            
        }
        return $detectedUaCode;
    }

}
