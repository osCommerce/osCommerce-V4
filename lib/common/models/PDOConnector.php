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

namespace common\models;

use PDO;

class PDOConnector {

    protected static $pdo = NULL;
    protected static $stmt = NULL;
    
    private static $host = "localhost";
    private static $user = "";
    private static $password = "";
    private static $dbname = "";
    private static $charset = "utf8";

    public static function init($configure) {
        if (isset($configure['host'])) {
            self::$host = $configure['host'];
        }
        if (isset($configure['user'])) {
            self::$user = $configure['user'];
        }
        if (isset($configure['password'])) {
            self::$password = $configure['password'];
        }
        if (isset($configure['dbname'])) {
            self::$dbname = $configure['dbname'];
        }
        if (isset($configure['charset'])) {
            self::$charset = $configure['charset'];
        }
        return self::getPDO();
    }
    
    public static function close() {
        self::$pdo = NULL;
        self::$stmt = NULL;
    }

    public static function getPDO() {
        if (!self::$pdo) {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=" . self::$charset;
            $opt = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            );
            self::$pdo = new PDO($dsn, self::$user, self::$password, $opt);
        }
        return self::$pdo;
    }

    public static function query($sql) {
        self::$stmt = self::getPDO()->prepare($sql);
        self::$stmt->execute();
        return self::$stmt;
    }
    
    public static function perform($table, $data, $action = 'insert', $parameters = '') {
        reset($data);
        if ($action == 'insert') {
            $query = 'insert into ' . $table . ' (';
            if (is_array($data)) foreach ($data as $columns => $value) {
                $query .= $columns . ', ';
            }
            $query = substr($query, 0, -2) . ') values (';
            reset($data);
            if (is_array($data)) foreach ($data as $columns => $value) {
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
            if (is_array($data)) foreach ($data as $columns => $value) {
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

        return self::query($query);
    }
    
    public static function lastInsertId() {
        return self::getPDO()->lastInsertId();
    }
    
    public static function fetch($stmt = NULL) {
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return self::$stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public static function fetchAll($stmt = NULL) {
        if ($stmt) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return self::$stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
