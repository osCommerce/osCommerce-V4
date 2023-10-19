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

class db_access extends install_generic {

    public static $shortcuts = array('pfh' => array('file_handler', array('installer')));
    public static $before = 'php_check';
    public $next_button = 'inst_db';

    private $dbhost = 'localhost';
    private $dbname = '';
    private $dbuser = '';
    private $empty_database = 0;

    public static function before() {
        return self::$before;
    }

    public function get_output() {
        $showConfirmation = false;
        if (isset($_POST['dbhost'])) {
            $this->dbhost = $_POST['dbhost'];
            $this->dbname = $_POST['dbname'];
            $this->dbuser = $_POST['dbuser'];
            $this->dbpass = $_POST['dbpass'];

            try {
                $link = mysqli_connect($this->dbhost, $this->dbuser, $this->dbpass);
            } catch (\Exception $e) {
                $link = false;
            }
            if ($link) {
                $db_selected = mysqli_select_db($link, $this->dbname);
                if ($db_selected) {
                    $db_query = mysqli_query($link, "SELECT count(table_name) as qty FROM information_schema.tables WHERE table_schema = '".$this->dbname."'");
                    $result = mysqli_fetch_array($db_query, MYSQLI_ASSOC);
                    if (isset($result['qty']) && $result['qty']>0) {
                        $showConfirmation = true;
                    }
                }
            }
        }
        $content = '
		<table width="100%" border="0" cellspacing="1" cellpadding="2" class="no-borders no-padding">
                    <tr>
                        <td align="right">' . $this->lang['dbhost'] . ':<span class="requared">*</span></td>
                        <td><input type="text" name="dbhost" size="25" value="' . $this->dbhost . '" class="input" required /></td>
                        <td align="right">' . $this->lang['dbname'] . ':<span class="requared">*</span></td>
                        <td><input type="text" name="dbname" size="25" value="' . $this->dbname . '" placeholder="must exist empty database" class="input" required /></td>
                    </tr>
                    <tr>
                        <td align="right">' . $this->lang['dbuser'] . ':<span class="requared">*</span></td>
                        <td><input type="text" name="dbuser" size="25" value="' . $this->dbuser . '" class="input" required /></td>
                        <td align="right">' . $this->lang['dbpass'] . ':<span class="requared">*</span></td>
                        <td><input type="password" name="dbpass" size="25" value="" class="input" id="db_password" required /><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAARCAYAAAAyhueAAAAACXBIWXMAAAsTAAALEwEAmpwYAAADdklEQVR42l2US09TURSFT3vbKiUBiTxDIigkwtBEJ8wM1dJggATCm4I82goEKMQC5f0uz4KQEJ3o0L/g1J+hI4eCUQkjB1LXdwOkOGhve+7ea6+99trHLCwsOJeWlkwsFnvc3t6+vbOzY2ZnZy2dm+XlZbO1tWUODw/N0dGR/dzc3DS8U4xLT8fi4qL9n+fVx8zPz1srKysmGo0+zcjI+NvS0jIE0NzcnFufvHA4HGhsbBxWwZn6+vrBoaGhZ2tra7eIAUz5zv+B7S+9cKyurholRhwOR6q1tXUwmUyampqaj9nZ2Rf5+fkn5eXl33Nzc39mZWWlCgsLfzU0NCREJps8Fb8BfP2Dlvf29kxPT0+HMSalAqH19XX35ORkmZ6u4+NjB4UEdKe5uTlWXFz8o6io6GRsbOw5kqQD24AcbGxsmIGBgZcCzxdwEMYCHjg4ODBqO1laWnpaUlLyRV285kzx3urq6ndutzvV3d09nkgkwLFspjMzMy700Yuox+NJRSKRrt3dXdPW1mZL0dnZGdLAvB0dHbHa2tr9zMzMi6qqqk9i74RhV1dXlLj+/n57FsKzzPb2NkN6YVlWSolRKk5PT9+m1WAw2IMUAu7d39+3J69OHnq93gt1EZcUToD0fhzGwgmQb1TxUV5e3q9AIPAWO8XjcUvTdYlpUq1kikkvwCr4Suc2cF1d3ZvKysqvzGBqaspDHvkFBQVnOrtnKioqvgn0XMF38Z50IdGtQfwR0xEYCzDsdDoBDl92MMJ74ognT4xz5IxzSfQZcz+RZX77fL4PaEllGMAERjCDIUxhDHM6oBM6ojPylP9eljvTEB8YqMsWATSRNhNoJO850Qzt0BBgNEVbgNEaxmiPhoqduNS0BhehoZth9fX1DTNFMRkHRFpbTJlpM3WmjwtwA3G4g45CodBLXCM7hq6nf+lTiwqylV0R/4mBFz/iS/yJTyVHEsb4F2D8jK8FHCT/agGuN4oDGI6Ojvq1hqdsjO6B12wQrbJRKuRSy+XS0i3mIaRgA2HMRt7YqHRgdpmd1hYl2HF2nZ0vKyv7rv8nOTk5Kb/f/5FC3BEwpgDD5A65vlDSgbl1+I0+CvToVvLp8ohIy3hTU9Ogbi2/iudyixHDrYZk6tDHbUf+jXsw/fpSkksBtp2wDC7hqQGmx1hIpqmj7f0rjH9upA5bbnfKaAAAAABJRU5ErkJggg==" class="eye" id="togglePassword" /></td>
                        <script>
                          const togglePassword = document.querySelector("#togglePassword");
                          const password = document.querySelector("#db_password");

                          togglePassword.addEventListener("click", function (e) {
                              const type = password.getAttribute("type") === "password" ? "text" : "password";
                              password.setAttribute("type", type);
                              this.classList.toggle("active");

                              if(togglePassword.classList.contains("active")){
                                this.src = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAARCAYAAAAyhueAAAAACXBIWXMAAAsTAAALEwEAmpwYAAACjklEQVR42qVUu48ScRD+LQcekCyRgmB4FMRAQbSnAxKagxAhx4FESCjARAkUGMPyhuUNy+Nf8E+wtbewsLM4KxMbbNTO7jjn27BkOfHUSLLMznvmm5llnU6HdbtdBqo8rVZLJwjCqSiKbLFYsOVyydbrtUzBQw497NR+Spz9CyieZrOpBT+bzeDoyOVyjxKJxItUKiWBgoccetjBXvFV4hxkaTQa2slkwtrttjMSiUztdvvGbDZ/s1qt791u9xtQ8JBDDzvY1+t1rbpbdUAO2Uul0rnNZvvsdDo/kGOpUqncm06nctug4CGHHnawn8/nsv++ffrjKBMHB2qtbjAYrv1+f5/w4hEoFos9p8reUYBL0Hg8/gzYQh8IBETYZ7PZ1ng8RsUaOSjho0emfD7/Uq/XX6XT6RQSDIdD5vP5XvE8/yUajQqFQuEMFDzkg8FArhz2Op3uigoSdhWfygMpFotJCrh9TD8Y9vt9RkN5SgE21O790WgkTxwUvMlk2kAPOyUwVbwlKC56vR5DwDPCZxMKhXqSJLFarXZCrd3xeDxvw+Fwc7VayesDmEDBQw497GCPCsm/43A4vhIED5jX6/1hsVg+EiY8DJCpWq3edblcl1T4EwKfI2d5ujvKQQ497GBPgU4IDhN19onwff3flSIgKg0GgyIN8jvxDxkEO0yvb2IK7I5hCqwVTDET+BmNxm25XE7ImCrTp+keTB/T/Yfpbw+mf3NPkZH2VKRr4dEq9lK9p9hb7C/02FN0mMlk2thTCqg5elHUwl9dFE36l4s6OFP17SMjbpqGcevtQ//b27/tK0WbYCNIoslkskKzkEDBQ/7Hr5Q6y+5dg6ppZfZTx9kqWwA59LA74st+Ar8TAcCyFjiKAAAAAElFTkSuQmCC";
                              }else{
                                this.src = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAARCAYAAAAyhueAAAAACXBIWXMAAAsTAAALEwEAmpwYAAADdklEQVR42l2US09TURSFT3vbKiUBiTxDIigkwtBEJ8wM1dJggATCm4I82goEKMQC5f0uz4KQEJ3o0L/g1J+hI4eCUQkjB1LXdwOkOGhve+7ea6+99trHLCwsOJeWlkwsFnvc3t6+vbOzY2ZnZy2dm+XlZbO1tWUODw/N0dGR/dzc3DS8U4xLT8fi4qL9n+fVx8zPz1srKysmGo0+zcjI+NvS0jIE0NzcnFufvHA4HGhsbBxWwZn6+vrBoaGhZ2tra7eIAUz5zv+B7S+9cKyurholRhwOR6q1tXUwmUyampqaj9nZ2Rf5+fkn5eXl33Nzc39mZWWlCgsLfzU0NCREJps8Fb8BfP2Dlvf29kxPT0+HMSalAqH19XX35ORkmZ6u4+NjB4UEdKe5uTlWXFz8o6io6GRsbOw5kqQD24AcbGxsmIGBgZcCzxdwEMYCHjg4ODBqO1laWnpaUlLyRV285kzx3urq6ndutzvV3d09nkgkwLFspjMzMy700Yuox+NJRSKRrt3dXdPW1mZL0dnZGdLAvB0dHbHa2tr9zMzMi6qqqk9i74RhV1dXlLj+/n57FsKzzPb2NkN6YVlWSolRKk5PT9+m1WAw2IMUAu7d39+3J69OHnq93gt1EZcUToD0fhzGwgmQb1TxUV5e3q9AIPAWO8XjcUvTdYlpUq1kikkvwCr4Suc2cF1d3ZvKysqvzGBqaspDHvkFBQVnOrtnKioqvgn0XMF38Z50IdGtQfwR0xEYCzDsdDoBDl92MMJ74ognT4xz5IxzSfQZcz+RZX77fL4PaEllGMAERjCDIUxhDHM6oBM6ojPylP9eljvTEB8YqMsWATSRNhNoJO850Qzt0BBgNEVbgNEaxmiPhoqduNS0BhehoZth9fX1DTNFMRkHRFpbTJlpM3WmjwtwA3G4g45CodBLXCM7hq6nf+lTiwqylV0R/4mBFz/iS/yJTyVHEsb4F2D8jK8FHCT/agGuN4oDGI6Ojvq1hqdsjO6B12wQrbJRKuRSy+XS0i3mIaRgA2HMRt7YqHRgdpmd1hYl2HF2nZ0vKyv7rv8nOTk5Kb/f/5FC3BEwpgDD5A65vlDSgbl1+I0+CvToVvLp8ohIy3hTU9Ogbi2/iudyixHDrYZk6tDHbUf+jXsw/fpSkksBtp2wDC7hqQGmx1hIpqmj7f0rjH9upA5bbnfKaAAAAABJRU5ErkJggg==";
                              }
                          });
                        </script>

                    </tr>';
        if ($showConfirmation) {
            $content .= '<tr>
                        <td align="left" colspan="2"><label class="pointer"><input type="checkbox" name="empty_database" value="1" class="input input-checkbox"' . ($this->empty_database == 1 ? ' checked' : '') . ' />' . $this->lang['empty_database'] . '</label></td>
                    </tr>';
        }
        $content .= '</table>';
        return $content;
    }

    public function get_filled_output() {
        if (defined('DB_SERVER')) {
            $this->dbhost = DB_SERVER;
        }
        if (defined('DB_DATABASE')) {
            $this->dbname = DB_DATABASE;
        }
        if (defined('DB_SERVER_USERNAME')) {
            $this->dbuser = DB_SERVER_USERNAME;
        }
        return $this->get_output();
    }

    public function parse_input() {
        if (isset($_POST['prev']) && $_POST['prev'] == 'php_check') {
            return true;
        }

        $this->dbhost = $_POST['dbhost'] ?? '';
        $this->dbname = $_POST['dbname'] ?? '';
        $this->dbuser = $_POST['dbuser'] ?? '';
        $this->dbpass = $_POST['dbpass'] ?? '';
        $this->empty_database = (int)($_POST['empty_database'] ?? 0);

        try {
            $link = mysqli_connect($this->dbhost, $this->dbuser, $this->dbpass);
        } catch (\Exception $e) {
            $link = false;
        }
        if (!$link) {
            $this->log('install_error', 'Can\'t connect to database server.', mysqli_connect_error());
            return false;
        }
        if (function_exists('mysqli_get_server_version') && ($ver = mysqli_get_server_version($link)) < 50610) {
            $this->log('install_error', "MySQL/MariaDB version does not meet minimum requirements: $ver");
            return false;
        }

        $db_selected = mysqli_select_db($link, $this->dbname);
        if (!$db_selected)
        {
            $this->log('install_error', 'Wrong database name.');
            return false;
        }

        if ($this->empty_database != 1) {
            $db_query = mysqli_query($link, "SELECT count(table_name) as qty FROM information_schema.tables WHERE table_schema = '".$this->dbname."'");
            $result = mysqli_fetch_array($db_query, MYSQLI_ASSOC);
            if (isset($result['qty']) && $result['qty']>0) {
                $this->log('install_error', 'Database not empty.');
                return false;
            }
        }
        
        $installed_microtime = microtime(true);

        $content  = '<?php' . "\n";
        $content .= "define('DB_SERVER', '" . $this->dbhost . "');" . "\n";
        $content .= "define('DB_SERVER_USERNAME', '" . $this->dbuser . "');" . "\n";
        $content .= "define('DB_SERVER_PASSWORD', '" . $this->dbpass . "');" . "\n";
        $content .= "define('DB_DATABASE', '" . $this->dbname . "');" . "\n";
        $content .= "define('USE_PCONNECT', 'false');" . "\n";
        $content .= "define('STORE_SESSIONS', 'mysql');" . "\n";
        $content  .= "" . "\n";
        $content  .= "define('INSTALLED_MICROTIME', '". $installed_microtime . "');" . "\n";
        error_clear_last();
        $response = file_put_contents($this->root_path . 'includes/local/configure.php', $content);
        if ($response === false) {
            $this->log('install_error', 'Can\'t save config file.', error_get_last()['message']??null);
            return false;
        }

        $hostname = $_SERVER['HTTP_HOST'];
        $pathname = rtrim(trim(dirname(dirname($_SERVER['SCRIPT_NAME'])), DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
        if (!empty($pathname)) {
            $pathname = '/' . ltrim($pathname, '/\\'); // prevent double slash
        }

        $content  = '<?php' . "\n";
        $content  .= "define('HTTP_SERVER', 'http://" . $hostname . "');" . "\n";
        $content  .= "define('HTTPS_SERVER', 'https://" . $hostname . "');" . "\n";
        $content  .= "define('HTTP_CATALOG_SERVER', 'http://" . $hostname . "');" . "\n";
        $content  .= "define('HTTPS_CATALOG_SERVER', 'https://" . $hostname . "');" . "\n";
        $content  .= "define('ENABLE_SSL', true);" . "\n";
        $content  .= "define('ENABLE_SSL_CATALOG', true);" . "\n";
        $content  .= "" . "\n";
        $content  .= 'define(\'DIR_FS_DOCUMENT_ROOT\', $_SERVER[\'DOCUMENT_ROOT\']);' . "\n";
        $content  .= "define('DIR_WS_ADMIN', '" . rtrim($pathname, '/\\') . "/admin/');" . "\n";
        $content  .= "define('DIR_FS_ADMIN', rtrim(DIR_FS_DOCUMENT_ROOT, '/\\\\') . DIR_WS_ADMIN);" . "\n";
        $content  .= "define('DIR_WS_CATALOG', '" . rtrim($pathname, '/\\') . "/');" . "\n";
        $content  .= "define('DIR_FS_CATALOG', rtrim(DIR_FS_DOCUMENT_ROOT, '/\\\\') . DIR_WS_CATALOG);" . "\n";
        $content  .= "define('DIR_WS_IMAGES', 'images/');" . "\n";
        $content  .= "define('DIR_WS_ICONS', DIR_WS_IMAGES . 'icons/');" . "\n";
        $content  .= "define('DIR_WS_CATALOG_IMAGES', DIR_WS_CATALOG . 'images/');" . "\n";
        $content  .= "define('DIR_WS_INCLUDES', 'includes/');" . "\n";
        $content  .= "define('DIR_WS_BOXES', DIR_WS_INCLUDES . 'boxes/');" . "\n";
        $content  .= "define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');" . "\n";
        $content  .= "define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');" . "\n";
        $content  .= "define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');" . "\n";
        $content  .= "define('DIR_WS_LANGUAGES', DIR_WS_INCLUDES . 'languages/');" . "\n";
        $content  .= "define('DIR_WS_CATALOG_LANGUAGES', DIR_WS_CATALOG . 'includes/languages/');" . "\n";
        $content  .= "define('DIR_FS_CATALOG_LANGUAGES', DIR_FS_CATALOG . 'includes/languages/');" . "\n";
        $content  .= "define('DIR_FS_CATALOG_IMAGES', DIR_FS_CATALOG . 'images/');" . "\n";
        $content  .= "define('DIR_FS_CATALOG_MODULES', DIR_FS_CATALOG . 'includes/modules/');" . "\n";
        $content  .= "define('DIR_FS_BACKUP', DIR_FS_ADMIN . 'backups/');" . "\n";
        $content  .= "define('DIR_FS_CATALOG_XML', DIR_FS_CATALOG . 'xml/');" . "\n";
        $content  .= "define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');" . "\n";
        $content  .= "define('DIR_WS_DOWNLOAD', DIR_WS_CATALOG . 'download/');" . "\n";
        $content  .= "define('DIR_FS_CATALOG_FONTS', DIR_FS_ADMIN . 'includes/fonts/');" . "\n";
        //$content  .= "define('DIR_FS_CATALOG_MAINPAGE_MODULES', DIR_FS_CATALOG_MODULES . 'mainpage_modules/');" . "\n";
        $content  .= "define('DIR_WS_TEMPLATES', DIR_WS_CATALOG . 'templates/');" . "\n";
        $content  .= "define('DIR_FS_TEMPLATES', DIR_FS_CATALOG . 'templates/');" . "\n";
        $content  .= "" . "\n";
        $content  .= "define('DB_SERVER', '" . $this->dbhost . "');" . "\n";
        $content  .= "define('DB_SERVER_USERNAME', '" . $this->dbuser . "');" . "\n";
        $content  .= "define('DB_SERVER_PASSWORD', '" . $this->dbpass . "');" . "\n";
        $content  .= "define('DB_DATABASE', '" . $this->dbname . "');" . "\n";
        $content  .= "define('USE_PCONNECT', 'false');" . "\n";
        $content  .= "define('STORE_SESSIONS', 'mysql');" . "\n";
        $content  .= "" . "\n";
        $content  .= "define('INSTALLED_MICROTIME', '". $installed_microtime . "');" . "\n";
        $content  .= "" . "\n";
        error_clear_last();
        $response = file_put_contents($this->root_path . 'admin/includes/local/configure.php', $content);
        if ($response === false) {
            $this->log('install_error', 'Can\'t save admin config file.', error_get_last()['message']??null);
            return false;
        }

        $path = dirname(dirname($_SERVER['SCRIPT_FILENAME']));
        $restore_from = $path . '/sql/trueloaded.sql';

        // Cleanup database
        $db_query = mysqli_query($link, "SELECT table_name FROM information_schema.tables WHERE table_schema = '".$this->dbname."'");
        $countries = [];
        while ($result = mysqli_fetch_array($db_query, MYSQLI_ASSOC)) {
            if (!isset($turnOffForeignKeys)) {
                mysqli_query($link, "SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;");
                $turnOffForeignKeys = true;
            }
            mysqli_query($link, "DROP TABLE IF EXISTS `" . $result['table_name'] . "`;");
        }

        $sqls = $this->parse_sql_file($restore_from);
        foreach($sqls as $sql) {
            $sql = trim($sql);
            if (empty($sql)) continue;
            $result = mysqli_query($link, $sql);
            if (!$result) {
                $errorMsg = 'Can\'t update database: ' . $link->error;
                if ( strpos($sql, 'SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT') !== false ) { // session may be terminated due large sql file
                    Log::write($errorMsg, 'install_error', $sql);
                } else {
                    $this->log('install_error', $errorMsg, $sql);
                    return false;
                }
            }
        }
        //exec('mysql -h' . $this->dbhost . ' -u' . $this->dbuser . ' -p' . $this->dbpass . ' ' . $this->dbname . ' < ' . $restore_from);
        

        if (isset($turnOffForeignKeys)) {
            mysqli_query($link, "SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS");
        }
        mysqli_close($link);

        $this->log('install_success', $this->lang['dbcheck_success']);

        return true;
    }

    private function parse_sql_file($filename) {
        $file = file_get_contents($filename);
        $sqls = explode(";\n", str_replace("\n\n", "\n", str_replace("\r", "\n", $file)));
        $sqls = preg_replace('/^#.*$/m', '', $sqls);
        $sqls = preg_replace('/\s{2,}/', ' ', $sqls);
        //$sqls = preg_replace('/\v/', '', $sqls);
        return $sqls;
    }

}
