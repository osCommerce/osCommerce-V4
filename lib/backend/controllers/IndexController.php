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

namespace backend\controllers;
use backend\models\ProductNameDecorator;
use common\helpers\Html;

/**
 * default controller to handle user requests.
 */
class IndexController extends Sceleton {

    private function chooseMenuItem($tree) {
        foreach ($tree as $menuItem) {
            if ($menuItem['box_type'] == 1) {
                foreach ($menuItem['child'] as $subMenuItem) {
                    if ($subMenuItem['dis_module'] == false && !empty($subMenuItem['path'])) {
                        return $subMenuItem;
                    }
                }
            }
        }
        return false;
    }
    /**
     * Index action is the default action in a controller.
     */
    public function actionIndex() {
        if (!\common\helpers\Acl::rule(['TEXT_DASHBOARD'])) {
            $queryResponse = \common\models\AdminBoxes::find()
                ->orderBy(['sort_order' => SORT_ASC])
                ->asArray()
                ->all();
            $Navigation = new \backend\components\Navigation();
            $tree = $Navigation->buildTree(0, $queryResponse, []);
            $menuItem = $this->chooseMenuItem($tree);
            if (isset($menuItem['path'])) {
                $redirectUrl = \Yii::$app->urlManager->createUrl([$menuItem['path']]);
                return $this->redirect($redirectUrl);
            }
            die('Access denied.');
        }
        $lang_var = '';
        $languages = \common\helpers\Language::get_languages();
        foreach ($languages as $lItem) {
            $lang_var .= '<a href="' . \Yii::$app->urlManager->createUrl(['index?language=']) . $lItem['code'] . '">' . $lItem['image_svg'] . '</a>';
        }
        $this->topButtons[] = '<div class="admin_top_lang">' . $lang_var . '</div>';

        $messageSystemStatusCheck = '';
        $message = (defined('WARNING_SESSION_AUTO_START')
            ? constant('WARNING_SESSION_AUTO_START')
            : 'Warning: session.auto_start is enabled - please disable this php feature in php.ini and restart the web server.'
        );
        if (function_exists('ini_get') AND (ini_get('session.auto_start') == '1')) {
            $messageSystemStatusCheck .= ($message . "\n");
        }
        unset($message);
        $message = (defined('MESSAGE_SEC_KEY_GLOBAL')
            ? constant('MESSAGE_SEC_KEY_GLOBAL')
            : 'Warning: Security keys were generated for a different security store key! Update required. Security store key for this domain is [%1$s]. Please update \'secKey.global\' key value in [lib/common/config/params-local.php] and flush OPcache in "Settings" -> "Cache control".'
        );
        $secKeyGlobal = md5(\Yii::$app->db->dsn . (defined('INSTALLED_MICROTIME') ? INSTALLED_MICROTIME : ''));
        if (!isset(\Yii::$app->params['secKey.global']) OR (\Yii::$app->params['secKey.global'] != $secKeyGlobal)) {
            $messageSystemStatusCheck .= (sprintf($message, $secKeyGlobal) . "\n");
        }
        unset($secKeyGlobal);
        unset($message);
        $message = (defined('WARNING_INSTALL_DIRECTORY_EXISTS')
            ? constant('WARNING_INSTALL_DIRECTORY_EXISTS')
            : 'Warning: Installation directory [%1$s/install] exists. Please remove this directory for security reasons.'
        );
        $file = \Yii::getAlias('@site_root') . '/install';
        if (file_exists($file)) {
            $messageSystemStatusCheck .= (sprintf($message, dirname(realpath($file))) . "\n");
        }
        unset($message);
        unset($file);
        $message = (defined('WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT')
            ? constant('WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT')
            : 'Warning: The downloadable products directory [%1$s] does not exist. Downloadable products will not work until this directory is valid.'
        );
        if (!is_dir(DIR_FS_DOWNLOAD)) {
            $messageSystemStatusCheck .= (sprintf($message, DIR_FS_DOWNLOAD) . "\n");
        }
        unset($message);
        /*$message = (defined('WARNING_CONFIG_FILE_WRITEABLE')
            ? constant('WARNING_CONFIG_FILE_WRITEABLE')
            : 'Warning: Configuration file is writable [%1$s]. Please, set the correct user permissions for this file.'
        );
        foreach (
            [
                (\Yii::getAlias('@webroot') . '/includes/configure.php'),
                (\Yii::getAlias('@webroot') . '/includes/local/configure.php'),
                (\Yii::getAlias('@site_root') . '/includes/configure.php'),
                (\Yii::getAlias('@site_root') . '/includes/local/configure.php')
            ] as $file
        ) {
            if (file_exists($file) AND is_writeable($file)) {
                $messageSystemStatusCheck .= (sprintf($message, realpath($file)) . "\n");
            }
        }
        unset($file);
        foreach (
            ['common', 'backend', 'frontend', 'console', 'pos', 'rest'] as $route
        ) {
            foreach (glob(\Yii::getAlias('@site_root') . '/lib/' . $route . '/config/*') as $file
            ) {
                if (is_file($file) AND is_writeable($file)) {
                    $messageSystemStatusCheck .= (sprintf($message, realpath($file)) . "\n");
                }
            }
        }
        unset($route);
        unset($file);
        unset($message);*/
        $message = (defined('MESSAGE_SEC_KEY_EMPTY')
            ? constant('MESSAGE_SEC_KEY_EMPTY')
            : 'Warning: Security key for [%1$s] cannot be empty. Password encryption service for [%1$s] is not available!'
        );
        foreach (['backend', 'frontend'] as $secKeyType) {
            if (!isset(\Yii::$app->params['secKey.' . $secKeyType]) OR (trim(\Yii::$app->params['secKey.' . $secKeyType]) == '')) {
                $messageSystemStatusCheck .= (sprintf($message, $secKeyType) . "\n");
            }
        }
        unset($secKeyType);
        unset($message);
        $this->view->messageSystemStatusCheck = nl2br(trim($messageSystemStatusCheck));
        unset($messageSystemStatusCheck);
        return $this->render('index');
    }

    public function actionLocations() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $this->layout = false;

        if (\Yii::$app->request->isPost) {
            $order_id = \Yii::$app->request->post('order_id', 0);
            $lat = \Yii::$app->request->post('lat', 0);
            $lng = \Yii::$app->request->post('lng', 0);

            if ($order_id > 0) {
                tep_db_query("update " . TABLE_ORDERS . " set lat = '" . (float) $lat . "', lng = '" . (float) $lng . "' where orders_id = '" . (int) $order_id . "'");
            }
        } else {
            $date_from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') + 1, 1, date('Y') - 1));
            $orders_query = tep_db_query("select o.lat, o.lng, o.customers_street_address, o.customers_suburb, o.customers_city, o.customers_postcode, o.customers_state, o.customers_country from " . TABLE_ORDERS . " o where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.lat not in (0 , 9999) and o.lng not in (0 , 9999)");
            $founded = [];
            while ($orders = tep_db_fetch_array($orders_query)) {
                $orders['title'] = $orders['customers_street_address'] . "\n" . $orders['customers_city'] . "\n" . $orders['customers_postcode'] . "\n" . $orders['customers_state'] . "\n" . $orders['customers_country'];
                $founded[] = $orders;
            }

            echo json_encode(array(
                'to_search' => $to_search ?? null,
                'founded' => $founded,
                'orders_count' => count($founded),
            ));
        }
    }

    public function actionError403()
    {
        $exception = new \yii\web\ForbiddenHttpException();
        if (isset($exception->statusCode)) {
            $code = $exception->statusCode;
        } else {
            $code = $exception->getCode();
        }

        if ($exception instanceof Exception) {
            $name = $exception->getName();
        } else {
            $name = \Yii::t('yii', 'Error');
        }
        if ($code) {
            $name .= " (#$code)";
        }

        if ($code == 403) {
            $this->view->headingTitle = $name;
            $this->navigation[] = array('link' => '', 'title' => $name);
            \Yii::$app->response->statusCode = 403;
            header('HTTP/1.0 403 Forbidden');
            return $this->render('403');
        }

        if ($code == 404) {
            $this->view->headingTitle = $name;
            $this->navigation[] = array('link' => '', 'title' => $name);
            header('HTTP/1.0 404 Not Found');
            return $this->render('404');
        }

        if ($exception instanceof \yii\base\UserException) {
            $message = $exception->getMessage();
        } else {
            $message = \Yii::t('yii', 'An internal server error occurred.');
        }

        if (\Yii::$app->getRequest()->getIsAjax()) {
            return "$name: $message \n$exception";
        } else {
            $this->layout = 'error.tpl';
            return $this->render('error', [
                'name' => $name,
                'message' => $message,
                'exception' => $exception,
            ]);
        }
    }

    public function actionError() {
        $exception = null;
        if ( isset($_GET['code']) ){
            if ( $_GET['code']==403 ){
                $exception = new \yii\web\ForbiddenHttpException();
            }
        }

        if ($exception===null && ($exception = \Yii::$app->getErrorHandler()->exception) === null) {
            $exception = new HttpException(404, \Yii::t('yii', 'Page not found.'));
        }

        if (isset($exception->statusCode)) {
            $code = $exception->statusCode;
        } else {
            $code = $exception->getCode();
        }

        if ($exception instanceof Exception) {
            $name = $exception->getName();
        } else {
            $name = \Yii::t('yii', 'Error');
        }
        if ($code) {
            $name .= " (#$code)";
        }

        if ($code == 403) {
            $this->view->headingTitle = $name;
            $this->navigation[] = array('link' => '', 'title' => $name);
            header('HTTP/1.0 403 Forbidden');
            return $this->render('403');
        }

        if ($code == 404) {
            $this->view->headingTitle = $name;
            $this->navigation[] = array('link' => '', 'title' => $name);
            header('HTTP/1.0 404 Not Found');
            return $this->render('404');
        }

        if ($exception instanceof \yii\base\UserException) {
            $message = $exception->getMessage();
        } else {
            $message = \Yii::t('yii', 'An internal server error occurred.');
        }

        if (\Yii::$app->getRequest()->getIsAjax()) {
            return "$name: $message \n$exception";
        } else {
            $this->layout = 'error.tpl';
            return $this->render('error', [
                        'name' => $name,
                        'message' => $message,
                        'exception' => $exception,
            ]);
        }
    }

    public function actionOrder() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $responseList = array();
        if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
            $currencies = \Yii::$container->get('currencies');
            $departments_query = tep_db_query("SELECT * FROM " . TABLE_DEPARTMENTS . " WHERE departments_status > 0");
            while ($department = tep_db_fetch_array($departments_query)) {
                $orders = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where department_id=" . (int)$department['departments_id']));
                $customers = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id = ci.customers_info_id where customers_status = '1' and c.departments_id=" . (int)$department['departments_id']));
                $orders_amount = tep_db_fetch_array(tep_db_query("select sum(ot.value) as total_sum from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ot.class = 'ot_total' and o.department_id=" . (int)$department['departments_id']));

                $responseList[] = [
                    $department['departments_store_name'] . '<input class="cell_identify" type="hidden" value="' . $department['departments_id'] . '">',
                    number_format($customers['count']),
                    number_format($orders['count']),
                    $currencies->format($orders_amount['total_sum'])
                ];
            }
            $response = array(
                'data' => $responseList,
                'columns' => [
                    BOX_HEADING_DEPARTMENTS,
                    TEXT_CLIENTS,
                    BOX_CUSTOMERS_ORDERS,
                    TEXT_AMOUNT_FILTER
                ]
            );
        } else {
            $orders_query = tep_db_query(
                "select o.orders_id, o.customers_name, o.customers_email_address, o.delivery_postcode, ".
                " o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ".
                " ot.text as order_total ".
                "from " . TABLE_ORDERS_STATUS . " s, " . TABLE_ORDERS . " o ".
                "  left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) ".
                "where o.orders_status = s.orders_status_id and s.language_id = '" . (int) $languages_id . "' and ot.class = 'ot_total' ".
                "/*group by o.orders_id*/ ".
                "order by o.date_purchased desc limit 6"
            );
            while ($orders = tep_db_fetch_array($orders_query)) {
                $responseList[] = array(
                    Html::encode($orders['customers_name']) . '<input class="cell_identify" type="hidden" value="' . $orders['orders_id'] . '">',
                    strip_tags($orders['order_total']),
                    $orders['orders_id'],
                    Html::encode($orders['delivery_postcode']),
                );
            }
            $response = array(
                'data' => $responseList,
                'columns' => [
                    'Customers',
                    'Order Total',
                    'Order Id',
                    'Post Code'
                ]
            );
        }
        echo json_encode($response);
    }

    public function actionDashboardOrderStat()
    {
        $exclude_order_statuses_array = \common\helpers\Order::extractStatuses(DASHBOARD_EXCLUDE_ORDER_STATUSES);
        $currencies = \Yii::$container->get('currencies');

        $order_stats_query =
            "SELECT ".
            "  COUNT(o.orders_id) AS orders, " .
            "  SUM(IF(o.orders_status=1,1,0)) AS orders_new, ".
            "  SUM(ott.value) as total_sum, AVG(ots.value) as total_avg ".
            "FROM " . TABLE_ORDERS . " o ".
            "  LEFT JOIN " . TABLE_ORDERS_TOTAL . " ott ON (o.orders_id = ott.orders_id) AND ott.class = 'ot_total' ".
            "  LEFT JOIN " . TABLE_ORDERS_TOTAL . " ots ON (o.orders_id = ots.orders_id) and ots.class = 'ot_subtotal' ".
            "WHERE 1=1 ".
            "  AND o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "') ";
        $range_stat = tep_db_fetch_array(tep_db_query($order_stats_query));
        $stats['all']['orders'] = number_format($range_stat['orders']);
        $stats['all']['orders_not_processed'] = number_format($range_stat['orders_new']);
        $stats['all']['orders_avg_amount'] = $currencies->format($range_stat['total_avg']);
        $stats['all']['orders_amount'] = $currencies->format($range_stat['total_sum']);
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        \Yii::$app->response->data = $stats;
    }

    private function getProduct($categories_id = '0') {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $currencies = \Yii::$container->get('currencies');

        $productList = [];
        $products_query = tep_db_query("select p.products_id, ".ProductNameDecorator::instance()->listingQueryExpression('pd','')." AS products_name from " . TABLE_PRODUCTS . " p LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " as pd on p.products_id = pd.products_id LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " as p2c on p.products_id = p2c.products_id where pd.language_id = '" . (int) $languages_id . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and p2c.categories_id=" . $categories_id . " group by p.products_id order by p2c.sort_order, pd.products_name");
        while ($products = tep_db_fetch_array($products_query)) {
            $productList[] = [
                'id' => $products['products_id'],
                'value' => $products['products_name'],
                'image' => \common\classes\Images::getImageUrl($products['products_id'], 'Small'),
                'title' => $products['products_name'],
                'price' => $currencies->format(\common\helpers\Product::get_products_price($products['products_id'], 1, 0, $currencies->currencies[DEFAULT_CURRENCY]['id'])),
            ];
        }
        return $productList;
    }

    private function getTree($parent_id = '0') {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $categoriesTree = [];
        $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int) $languages_id . "' and c.parent_id = '" . (int) $parent_id . "' and affiliate_id = 0 order by c.sort_order, cd.categories_name");
        while ($categories = tep_db_fetch_array($categories_query)) {
            $products = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES . " c1, " . TABLE_PRODUCTS . " p  where p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c1.categories_id = '" . (int) $categories['categories_id'] . "' and (c.categories_left >= c1.categories_left and c.categories_right <= c1.categories_right) "));
            if ($products['total'] > 0) {
                $categoriesTree[] = [
                    'id' => $categories['categories_id'],
                    'text' => $categories['categories_name'],
                    'child' => $this->getTree($categories['categories_id']),
                    'products' => $this->getProduct($categories['categories_id']),
                ];
            }
        }
        return $categoriesTree;
    }

    private function renderTree($response, $spacer = '') {
        $html = '';
        if (is_array($response)) {
            foreach ($response as $key => $value) {
                $html .= '<strong>' . $spacer . $value['text'] . '</strong>';
                if (isset($value['products'])) {
                    foreach ($value['products'] as $pkey => $pvalue) {
                        $html .= '<a href="javascript:void(0)" ' . ($_GET['no_click'] ? '' : ' onclick="return searchSuggestSelected(' . $pvalue['id'] . ', \'' . $pvalue['value'] . '\');" ') . ' class="item" data-id="' . $pvalue['id'] . '">
        <span class="suggest_table">
            <span class="td_image"><img src="' . $pvalue['image'] . '" alt=""></span>
            <span class="td_name">' . $pvalue['title'] . '</span>
            <span class="td_price">' . $pvalue['price'] . '</span>
        </span>
    </a>';
                    }
                }
                if (isset($value['child'])) {
                    $html .= $this->renderTree($value['child'], $spacer . ' ' . $value['text'] . ' > ');
                }
            }
        }
        return $html;
    }

/**
 * should be deprecated (incorrect query for special price, slow query)
 * @return type
 */
    public function actionSearchSuggest() {
        $this->layout = false;
        $languages_id = \Yii::$app->settings->get('languages_id');

        $get = \Yii::$app->request->get();
        $lang_id = $get['languages_id'] ?? $languages_id;
        $customer_groups_id = 0;
        $currency_id = 0;

        $currencies = \Yii::$container->get('currencies');

        $response = array();

        if (isset($_GET['keywords']) && $_GET['keywords'] != '') {
            $_SESSION['keywords'] = \common\helpers\Output::output_string(\Yii::$app->request->get('keywords', ''));
            //Add slashes to any quotes to avoid SQL problems.
            $search = preg_replace("/\//", '', \common\helpers\Output::output_string(\Yii::$app->request->get('keywords', '')));
            $where_str_categories = "";
            $where_str_gapi = "";
            $where_str_products = "";
            $where_str_manufacturers = "";
            $where_str_information = "";
            $replace_keywords = array();

            if (\common\helpers\Output::parse_search_string($search, $search_keywords, false)) {
                $where_str_categories .= " and (";
                $where_str_gapi .= " and (";
                $where_str_products .= " and (";
                $where_str_manufacturers .= " (";
                $where_str_information .= " and (";
                for ($i = 0, $n = sizeof($search_keywords); $i < $n; $i++) {
                    switch ($search_keywords[$i]) {
                        case '(':
                        case ')':
                        case 'and':
                        case 'or':
                            $where_str_gapi .= " " . $search_keywords[$i] . " ";
                            $where_str_categories .= " " . $search_keywords[$i] . " ";
                            $where_str_products .= " " . $search_keywords[$i] . " ";
                            $where_str_manufacturers .= " " . $search_keywords[$i] . " ";
                            $where_str_information .= " " . $search_keywords[$i] . " ";
                            break;
                        default:

                            $keyword = tep_db_prepare_input($search_keywords[$i]);
                            $replace_keywords[] = $search_keywords[$i];
                            $where_str_gapi .= " gs.gapi_keyword like '%" . tep_db_input($keyword) . "%' or  gs.gapi_keyword like '%" . tep_db_input($keyword) . "%' ";

                            $where_str_products .= "(p.products_id='" . tep_db_input($keyword) . "' or if(length(pd1.products_name), pd1.products_name, pd.products_name) like '%" . tep_db_input($keyword) . "%' or pd.products_internal_name like '%" . tep_db_input($keyword) . "%' or p.products_model like '%" . tep_db_input($keyword) . "%' or m.manufacturers_name like '%" . tep_db_input($keyword) . "%'  or if(length(pd1.products_head_keywords_tag), pd1.products_head_keywords_tag, pd.products_head_keywords_tag) like '%" . tep_db_input($keyword) . "%' )";
                            $where_str_categories .= "(if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) like '%" . tep_db_input($keyword) . "%' or if(length(cd1.categories_description), cd1.categories_description, cd.categories_description) like '%" . tep_db_input($keyword) . "%')";

                            $where_str_manufacturers .= "(manufacturers_name like '%" . tep_db_input($keyword) . "%')";

                            $where_str_information .= "(if(length(i1.info_title), i1.info_title, i.info_title) like '%" . tep_db_input($keyword) . "%' or if(length(i1.description), i1.description, i.description) like '%" . tep_db_input($keyword) . "%' or if(length(i1.page_title), i1.page_title, i.page_title) like '%" . tep_db_input($keyword) . "%')";
                            break;
                    }
                }
                $where_str_categories .= ") ";
                $where_str_gapi .= ") ";
                $where_str_products .= ") ";
                $where_str_manufacturers .= ") ";
                $where_str_information .= ") ";
            } else {
                $replace_keywords[] = $search;
                $where_str_gapi .= "and gs.gapi_keyword like ('%" . $search . "%')))";
                $where_str_products .= "and (p.products_id='" . $search . "' or if(length(pd1.products_name), pd1.products_name like ('%" . $search . "%'), pd.products_name like ('%" . $search . "%'))  or if(length(pd1.products_head_keywords_tag), pd1.products_head_keywords_tag, pd.products_head_keywords_tag) like '%" . $search . "%'  or gs.gapi_keyword like ('%" . $search . "%'))";
                $where_str_categories .= "and (if(length(cd1.categories_name), cd1.categories_name like ('%" . $search . "%'), cd.categories_name like ('%" . $search . "%')) or if(length(cd1.categories_description), cd1.categories_description like ('%" . $search . "%'), cd.categories_description like ('%" . $search . "%'))  )";
                $where_str_manufacturers .= " (manufacturers_name like '%" . $search . "%')";
                $where_str_information .= "and (if(length(i1.info_title), i1.info_title, i.info_title) like '%" . $search . "%' or if(length(i1.description), i1.description, i.description) like '%" . $search . "%' or if(length(i1.page_title), i1.page_title, i.page_title) like '%" . $search . "%')";
            }

            $useAffiliate = isset($_SESSION['affiliate_ref']) && $_SESSION['affiliate_ref'] > 0 && \common\helpers\Acl::checkExtensionAllowed('Affiliate');
            $from_str = "select c.categories_id, if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) as categories_name,  (if(length(cd1.categories_name), if(position('" . $search . "' IN cd1.categories_name), position('" . $search . "' IN cd1.categories_name), 100), if(position('" . $search . "' IN cd.categories_name), position('" . $search . "' IN cd.categories_name), 100))) as pos, 1 as is_category  from " . TABLE_CATEGORIES . " c " . ($useAffiliate ? " LEFT join " . TABLE_CATEGORIES_TO_AFFILIATES . " c2a on c.categories_id = c2a.categories_id  and c2a.affiliate_id = '" . (int) $_SESSION['affiliate_ref'] . "' " : '') . " left join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on cd1.categories_id = c.categories_id and cd1.language_id='" . $lang_id . "' and cd1.affiliate_id = '" . ($useAffiliate ? (int)$_SESSION['affiliate_ref'] : 0) . "', " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_status = 1 " . ($useAffiliate ? " and c2a.affiliate_id is not null " : '') . " and cd.affiliate_id = 0 and cd.categories_id = c.categories_id and cd.language_id = '" . $lang_id . "' " . $where_str_categories . " and c.quick_find = 1 order by pos limit 0, 3";

            $gapi_enabled = false;
            if (\common\helpers\Extensions::isAllowed('GoogleAnalyticsTools') ){
                $gapi_enabled = true;
            }
            $sql = "
      select  p.products_status, p.products_id, ".ProductNameDecorator::instance()->listingQueryExpression('pd','')." AS products_name, m.manufacturers_name,
          (if(length(pd1.products_name),
            if(position('" . $search . "' IN pd1.products_name),
              position('" . $search . "' IN pd1.products_name),
              100
            ),
            if(position('" . $search . "' IN pd.products_name),
              position('" . $search . "' IN pd.products_name),
              100
            )
          )) as pos, 0 as is_category,
		  p.products_image,
		  s.specials_id, s.specials_new_products_price
      from   " . TABLE_PRODUCTS . " p
          left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int) $lang_id . "'
                                              and pd1.platform_id = '" . intval(\common\classes\platform::defaultId()) . "'
          left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int) $customer_groups_id . "'
                                          and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int) $currency_id : '0') . "'
		LEFT JOIN " . TABLE_INVENTORY . " i on p.products_id = i.prid
		".($gapi_enabled?
		"left join gapi_search_to_products gsp on p.products_id = gsp.products_id
		left join gapi_search gs on gsp.gapi_id = gs.gapi_id":'')."
        left join " . TABLE_MANUFACTURERS . " m on m.manufacturers_id = p.manufacturers_id
        left join " . TABLE_SPECIALS . " s on s.products_id = p.products_id,
        " . TABLE_PRODUCTS_DESCRIPTION . " pd
    where  /* p.products_status = 1*/ 1
    " . ($useAffiliate ? " and p2a.affiliate_id is not null " : '') . "
      and   p.products_id = pd.products_id
      and   pd.language_id = '" . (int) $lang_id . "'
      and   if(pp.products_group_price is null, 1, pp.products_group_price != -1 )
      and   pd.platform_id = '" . intval(\common\classes\platform::defaultId()) . "'
    " . $where_str_products . "
	group by p.products_id
    order by p.products_status desc, ".($gapi_enabled?"gapi_keyword desc, gsp.sort, ":'')." products_name, pos
    limit   0, 10
  ";

            /**
             * Set XML HTTP Header for ajax response
             */
            reset($replace_keywords);
            foreach ($replace_keywords as $k => $v) {
                $patterns[] = "/" . preg_quote($v) . "/i";
                $replace[] = str_replace('$', '/$/', '<span class="typed">' . $v . '</span>');
            }

            $re = array();
            foreach ($replace_keywords as $k => $v)
                $re[] = preg_quote($v);
            $re = "/(" . join("|", $re) . ")/i";
            $replace = '<span class="typed">\1</span>';

            $product_query = tep_db_query($sql);

            $json = \Yii::$app->request->get('json', 0);
            $platform_id = \Yii::$app->request->get('platform_id', intval(\common\classes\platform::defaultId()));
            while ($product_array = tep_db_fetch_array($product_query)) {
                if ($json) {
                    $link = tep_catalog_href_link('catalog/product', 'products_id=' . $product_array['products_id'], '', $platform_id);
                } else {
                    $link = \Yii::$app->urlManager->createUrl(['catalog/product', 'products_id' => $product_array['products_id']]);
                }
                $specials_product_price = '';
                if( USE_MARKET_PRICES == 'True' ) {
                    if ($product_array['specials_id']) {
                        $specials_product_price = $currencies->format(\common\helpers\Product::get_specials_price($product_array['specials_id'], $currencies->currencies[DEFAULT_CURRENCY]['id']));
                    }
                } else {
                    if ($product_array['specials_new_products_price']) {
                        $specials_product_price = $currencies->format($product_array['specials_new_products_price']);
                    }
                }
                $response[] = array(
                    'id' => $product_array['products_id'],
                    'status' => $product_array['products_status'],
                    'value' => addslashes($product_array['products_name']),
                    'link' => $link,
                    'image' => \common\classes\Images::getImageUrl($product_array['products_id'], 'Small'),
                    'title' => preg_replace($re, $replace, strip_tags($product_array['products_name'])),
                    'price' => $currencies->format(\common\helpers\Product::get_products_price($product_array['products_id'], 1, 0, $currencies->currencies[DEFAULT_CURRENCY]['id'])),
                    'special_price' => $specials_product_price,
                );
            }

            if ($json) {
                return json_encode($response);
            }
            return $this->render('search.tpl', ['list' => $response, 'no_click' => \Yii::$app->request->get('no_click')]);
        } else {
            $response = $this->getTree();
            return $this->renderTree($response);
        }
    }

    public function actionEnableMap() {
        $configuration_id = \Yii::$app->request->get('configuration_id', 0);
        $status = \Yii::$app->request->get('status', 'false');

        if ($configuration_id) {
            tep_db_query('update ' . TABLE_CONFIGURATION . ' set configuration_value = "' . tep_db_input($status) . '" where configuration_id = "' . (int) $configuration_id . '"');
            echo 'ok';
            exit();
        }
        return false;
    }

    public function actionLoadLanguagesJs() {
        $this->layout = false;
        $list = \common\helpers\Translation::loadJS('admin/js');
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSONP;
        \Yii::$app->response->content = \common\widgets\JSLanguage::widget(['list' => $list]);
        return;
    }

}
