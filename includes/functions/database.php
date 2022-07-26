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
function tep_db_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = 'db_link') {
    global $$link;

    if (USE_PCONNECT == 'true') {
        $server = 'p:' . $server;
    }

    if ( defined('DB_SSL_CERT') && DB_SSL_CERT!='' && is_file(DB_SSL_CERT) ) {
      $$link = mysqli_init();
      mysqli_ssl_set($$link, NULL, NULL, DB_SSL_CERT, NULL, NULL);
      mysqli_real_connect($$link, $server, $username, $password, null, 3306, MYSQLI_CLIENT_SSL, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);
    }else {
      $$link = mysqli_connect($server, $username, $password);
    }

    $query = "set names 'utf8'";
    $result = tep_db_query($query);
    //mysqli_set_charset($$link, 'utf8');

    /*
    *hot fix for mysql mode. remove when active record complete
    */
    tep_db_query("SET SESSION sql_mode = '';", $link);
    /*
    *end of hot fix
    */
    if ($$link) {
        $db_selected = mysqli_select_db($$link, $database);
    }
    if (!$db_selected) {
        tep_db_close();
        $$link = false;
    }

    return $$link;
}

function tep_db_close($link = 'db_link') {
    global $$link;

    return mysqli_close($$link);
}

function tep_db_error($query, $errno, $error) {
    global $mysql_errors, $mysql_error_dump;
    $degug_info = debug_backtrace();

    // {{ log to file
    $log_error = 'URI: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '--') . "\n";
    $log_error .= 'Error: [' . $errno . '] ' . $error . "\n";
    $log_error .= 'Query: ' . $query . "\n";
    $debug_size = sizeof($degug_info) - 1;
    for ($i = $debug_size; $i > 0; $i--) {
        $log_error .= 'Line [' . ($debug_size - $i) . ']: ' . str_replace((defined('DIR_FS_CATALOG') ? DIR_FS_CATALOG : ''), '', $degug_info[$i]['file']) . ':' . $degug_info[$i]['line'] . "\n";
    }
    // }} log to file

    $file_name = '';
    $error_in_line = '';
    if (is_array($degug_info['1'])) {
        $file_name = str_replace(DIR_FS_CATALOG, '', $degug_info[1]['file']) . '<br>';
        $file_name = 'Filename: ' . $file_name;
        $error_in_line = 'Line: ' . $degug_info[1]['line'] . '<br><br>';
    }
    if (isset($mysql_error_dump)) {
        $mysql_error_dump[] = '<b>' . $errno . ' - ' . $error . '</b><br><br>' . $query . '<br><br>' . $file_name . $error_in_line;
    } else {
        $mysql_errors[] = '<b>' . $errno . ' - ' . $error . '</b><br><br>' . $query . '<br><br>' . $file_name . $error_in_line;
    }
    if (class_exists('\Yii', false)) {
        \Yii::error($log_error, 'sql_error');
    }
}

function tep_db_query($query, $link = 'db_link') {
    global $$link, $trans_count;

    if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true') && defined('STORE_PAGE_PARSE_TIME_LOG')) {
        $start = microtime(true);

        error_log('QUERY ' . $query . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }elseif ( defined('TEP_DB_TRACK_TIME') && TEP_DB_TRACK_TIME ) {
        Yii::beginProfile($query, 'tep::db_query');
    }

    $result = mysqli_query($$link, $query) or tep_db_error($query, mysqli_errno($$link), mysqli_error($$link));

    if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true') && defined('STORE_PAGE_PARSE_TIME_LOG')) {
        $result_error = mysqli_error($$link);
        error_log('RESULT ' . (is_object($result) ? $result->num_rows : $result) . ' ' . $result_error . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
        $time_end = microtime(true);
        $parse_time = $time_end - $start;
        error_log('Query execution: ' . $parse_time . ' ms' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }elseif ( defined('TEP_DB_TRACK_TIME') && TEP_DB_TRACK_TIME ) {
        Yii::endProfile($query, 'tep::db_query');
    }

    $trans_count++;

    return $result;
}

function tep_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link') {
  ///should be here and/or in AR model before save (in order not to miss sequental numbers)
  /** @var common\extensions\InvoiceNumberFormat\InvoiceNumberFormat $ext */
    if ($table==TABLE_ORDERS && $action == 'insert' && $ext = \common\helpers\Acl::checkExtensionAllowed('InvoiceNumberFormat', 'allowed')) {
      if (empty($data['order_number'])) {
        $platform_id = $data['platform_id']??(int)PLATFORM_ID;
        $data['order_number'] = $ext::getOrderNumber($platform_id);
        if (!$data['order_number']) {
          unset($data['order_number']);
        }
      }
    }
    if ($table==TABLE_ORDERS && $ext = \common\helpers\Acl::checkExtensionAllowed('InvoiceNumberFormat', 'allowed')) {
      if (empty($data['invoice_number']) && $action == 'insert' && !empty($data['orders_status'])) {
        $platform_id = $data['platform_id']??(int)PLATFORM_ID;
        $data['invoice_number'] = $ext::getInvoiceNumber($data['orders_status'], $platform_id);
        if (!$data['invoice_number']) {
          unset($data['invoice_number']);
        }
      }
    }

    if ($action == 'insert') {
        $query = 'insert into ' . $table . ' (';
        if (is_array($data))
            foreach ($data as $columns => $value) {
                $query .= $columns . ', ';
            }
        $query = substr($query, 0, -2) . ') values (';
        if (is_array($data))
            foreach ($data as $columns => $value) {
                switch ((string) $value) {
                    case 'now()':
                        $query .= 'now(), ';
                        break;
                    case 'null':
                        $query .= 'null, ';
                        break;
                    default:
                        $query .= '\'' . tep_db_input($value) . '\', ';
                        break;
                }
            }
        $query = substr($query, 0, -2) . ')';
    } elseif ($action == 'update') {
        $query = 'update ' . $table . ' set ';
        if (is_array($data))
            foreach ($data as $columns => $value) {
                switch ((string) $value) {
                    case 'now()':
                        $query .= $columns . ' = now(), ';
                        break;
                    case 'null':
                        $query .= $columns .= ' = null, ';
                        break;
                    default:
                        $query .= $columns . ' = \'' . tep_db_input($value) . '\', ';
                        break;
                }
            }
        $query = substr($query, 0, -2) . ' where ' . $parameters;
    }

    return tep_db_query($query, $link);
}

function tep_db_fetch_array($db_query) {
    return @mysqli_fetch_array($db_query, MYSQLI_ASSOC);
}

function tep_db_num_rows($db_query) {
    return @mysqli_num_rows($db_query);
}

function tep_db_data_seek($db_query, $row_number) {
    return mysqli_data_seek($db_query, $row_number);
}

function tep_db_insert_id($link = 'db_link') {
    global $$link;

    return mysqli_insert_id($$link);
}

function tep_db_free_result($db_query) {
    return mysqli_free_result($db_query);
}

function tep_db_fetch_fields($db_query) {
    return mysqli_fetch_field($db_query);
}

function tep_db_output($string) {
    return htmlspecialchars($string);
}

function tep_db_input($string, $link = 'db_link') {
    global $$link;

    if (function_exists('mysqli_real_escape_string')) {
        return mysqli_real_escape_string($$link, $string??'');
    } elseif (function_exists('mysqli_escape_string')) {
        return mysqli_escape_string($$link, $string);
    }

    return addslashes($string);
}

function tep_db_prepare_input($string) {
    if (is_string($string)) {
        $string = preg_replace('/ +/', ' ', trim($string));
        $string = preg_replace("/[<>]/", '_', $string);
        return trim($string);
    } elseif (is_array($string)) {
        foreach ($string as $key => $value) {
            $string[$key] = tep_db_prepare_input($value);
        }
        return $string;
    } else {
        return $string;
    }
}

function tep_db_affected_rows($link = 'db_link') {
    global $$link;

    return mysqli_affected_rows($$link);
}

function tep_db_get_server_info($link = 'db_link') {
    global $$link;

    return mysqli_get_server_info($$link);
}

/**
* typecast ActiveRecord attributes
*/
function yii_setup_model(\yii\db\ActiveRecord $model, $sql_data_array) {
    try {
        $schemaColumns = $model->getTableSchema()->columns;
        if (!is_array($schemaColumns))
            $schemaColumns = [];
    } catch (\yii\base\InvalidConfigException $ex) {
        $schemaColumns = [];
    }

    foreach ($sql_data_array as $column => $value) {
        if (strval($value) == 'now()')
            $value = new \yii\db\Expression('NOW()');
        if (strval($value) == 'null')
            $value = new \yii\db\Expression('NULL');

        if (isset($schemaColumns[$column])) {
            $model->setAttribute($column, $value);
            
            $tableColumn = $schemaColumns[$column];
            /**
             * @var $tableColumn \yii\db\ColumnSchema
             */
            if (!$tableColumn->allowNull && $tableColumn->dbTypecast($value) === null) {
                if (is_null($tableColumn->defaultValue)) {
                    $model->setAttribute(
                            $column, !is_null($tableColumn->phpTypecast('')) ? $tableColumn->phpTypecast('') : $tableColumn->phpTypecast(0)
                    );
                } else {
                    $model->setAttribute($column, $tableColumn->defaultValue);
                }
            }
        }
    }
}

///////////////////////////////////////////////
///////////////////////////////////////////////
///////////////////////////////////////////////
if (!function_exists('mysqli_connect')) {
    define('MYSQLI_ASSOC', MYSQL_ASSOC);

    function mysqli_connect($server, $username, $password) {
        if (substr($server, 0, 2) == 'p:') {
            $link = mysql_pconnect(substr($server, 2), $username, $password);
        } else {
            $link = mysql_connect($server, $username, $password);
        }
        return $link;
    }

    function mysqli_select_db($link, $database) {
        return mysql_select_db($database, $link);
    }

    function mysqli_connect_errno($link = null) {
        if (is_null($link)) {
            return mysql_errno();
        }

        return mysql_errno($link);
    }

    function mysqli_connect_error($link = null) {
        if (is_null($link)) {
            return mysql_error();
        }

        return mysql_error($link);
    }

    function mysqli_set_charset($link, $charset) {
        if (function_exists('mysql_set_charset')) {
            return mysql_set_charset($charset, $link);
        }
    }

    function mysqli_close($link) {
        return mysql_close($link);
    }

    function mysqli_query($link, $query) {
        return mysql_query($query);
    }

    function mysqli_errno($link = null) {
        if (is_null($link)) {
            return mysql_errno();
        }

        return mysql_errno($link);
    }

    function mysqli_error($link = null) {
        if (is_null($link)) {
            return mysql_error();
        }

        return mysql_error($link);
    }

    function mysqli_fetch_array($query, $type) {
        return mysql_fetch_array($query, $type);
    }

    function mysqli_num_rows($query) {
        return mysql_num_rows($query);
    }

    function mysqli_data_seek($query, $offset) {
        return mysql_data_seek($query, $offset);
    }

    function mysqli_insert_id($link) {
        return mysql_insert_id($link);
    }

    function mysqli_free_result($query) {
        return mysql_free_result($query);
    }

    function mysqli_fetch_field($query) {
        return mysql_fetch_field($query);
    }

    function mysqli_real_escape_string($link, $string) {
        if (function_exists('mysql_real_escape_string')) {
            return mysql_real_escape_string($string, $link);
        } elseif (function_exists('mysql_escape_string')) {
            return mysql_escape_string($string);
        }

        return addslashes($string);
    }

    function mysqli_affected_rows($link) {
        return mysql_affected_rows($link);
    }

    function mysqli_get_server_info($link) {
        return mysql_get_server_info($link);
    }

}
