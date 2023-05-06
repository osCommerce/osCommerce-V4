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

class inst_settings extends install_generic {

    public static $before = 'db_access';
    public static $ajax = 'ajax';
    public $next_button = 'update_db';

    private $def_lang = '';
    private $platform_type = 0;
    private $platform_owner = '';
    private $platform_name = '';
    private $platform_url = '';
    private $enable_ssl = 2;
    private $platform_email = '';
    private $platform_from = '';
    private $platform_extra = '';
    private $platform_phone = '';
    private $platform_landline = '';
    private $platform_companyname = '';
    private $platform_companyvat = '';
    private $platform_companyno = '';
    private $platform_postcode = '';
    private $platform_street = '';
    private $platform_suburb = '';
    private $platform_city = '';
    private $platform_state = '';
    private $platform_country = 222;
    private $sample_orders = 1;

    public function __construct() {
        parent::__construct();
    }

    public static function before() {
        return self::$before;
    }

    public function get_output() {
        if (!$this->def_lang)
            $this->def_lang = $_POST['inst_lang'];
        if (!$this->platform_url)
            $this->platform_url = rtrim($_SERVER['HTTP_HOST'] . '/' . trim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'), '/');
        $cuntries = $this->get_cuntries();
        if (!is_array($cuntries)) {
            $cuntries = [
                222 => 'United Kingdom',
            ];
        }
        $content = '<table class="no-borders table-db-access" style="border-collapse: collapse;" width="100%">
						<tr>
                                                    <th class="" colspan="4">' . $this->lang['general_config'] . '</th>
						</tr>
                                                <tr>
                                                  <td width="20%" class="empty_line"></td><td  width="30%" class="empty_line"></td><td width="20%" class="empty_line"></td><td  width="30%" class="empty_line"></td>
                                                </tr>
                                                <tr>
                                                    <td align="right">' . $this->lang['platform_type'] . ':</td>
                                                    <td colspan="3">
                                                        <select name="platform_type">
                                                            <option value="0"' . ($this->platform_type == 0 ? ' selected' : '') . '>Splash page and multi sales channels</option>
                                                            <option value="7"' . ($this->platform_type == 7 ? ' selected' : '') . '>Furniture</option>
                                                            <option value="8"' . ($this->platform_type == 8 ? ' selected' : '') . '>Watch</option>
                                                            <option value="9"' . ($this->platform_type == 9 ? ' selected' : '') . '>b2b supermarket</option>
                                                            <option value="10"' . ($this->platform_type == 10 ? ' selected' : '') . '>Print Shop</option>
                                                        </select>
                                                    </td>
                                                </tr>
						<tr>
                                                    <td align="right">' . $this->lang['platform_owner'] . ':<span class="requared">*</span></td>
                                                    <td><input type="text" name="platform_owner" size="25" value="' . $this->platform_owner . '" class="input" required /></td>
                                                    <td align="right">' . $this->lang['platform_name'] . ':<span class="requared">*</span></td>
                                                    <td><input type="text" name="platform_name" size="25" value="' . $this->platform_name . '" class="input" required /></td>
						</tr>
						<tr>
                                                    <td align="right">' . $this->lang['platform_url'] . ':<span class="requared">*</span></td>
                                                    <td colspan="3"><input type="text" name="platform_url" size="25" value="' . $this->platform_url . '" class="input" required /></td>
						</tr>
						<tr>
                                                    <td align="right">' . $this->lang['enable_ssl'] . ':<span class="requared">*</span></td>
                                                    <td colspan="3">
                                                        <input name="enable_ssl" value="0"' . ($this->enable_ssl == 0 ? ' checked' : '') . ' type="radio">NoSSL
                                                        <input name="enable_ssl" value="1"' . ($this->enable_ssl == 1 ? ' checked' : '') . ' type="radio">SSL
                                                        <input name="enable_ssl" value="2"' . ($this->enable_ssl == 2 ? ' checked' : '') . ' type="radio">FullSSL
                                                    </td>
						</tr>
                                                <tr>
                                                  <td colspan="4" class="empty_line"></td>
                                                </tr>
						<tr>
                                                    <th class="" colspan="4">' . $this->lang['contact_config'] . '</th>
						</tr>
                                                <tr>
                                                  <td colspan="4" class="empty_line"></td>
                                                </tr>
						<tr>
                                                    <td align="right">' . $this->lang['platform_email'] . ':<span class="requared">*</span></td>
                                                    <td><input type="text" name="platform_email" size="25" value="' . $this->platform_email . '" class="input" required /></td>
                                                    <td align="right">' . $this->lang['platform_from'] . ':<span class="requared">*</span></td>
                                                    <td><input type="text" name="platform_from" size="25" value="' . $this->platform_from . '" class="input" required /></td>
						</tr>
						<tr>
                                                    <td align="right">' . $this->lang['platform_extra'] . ':</td>
                                                    <td colspan="3"><input type="text" name="platform_extra" size="25" value="' . $this->platform_extra . '" class="input" /></td>
						</tr>
						<tr>
                                                    <td align="right">' . $this->lang['platform_phone'] . ':</td>
                                                    <td><input type="text" name="platform_phone" size="25" value="' . $this->platform_phone . '" class="input" /></td>
                                                    <td align="right">' . $this->lang['platform_landline'] . ':</td>
                                                    <td><input type="text" name="platform_landline" size="25" value="' . $this->platform_landline . '" class="input" /></td>
						</tr>
                                                <tr>
                                                  <td colspan="4" class="empty_line"></td>
                                                </tr>
                                                <tr>
                                                    <th class="" colspan="4">' . $this->lang['address_config'] . '</th>
                                                </tr>
                                                <tr>
                                                  <td colspan="4" class="empty_line"></td>
                                                </tr>
						<tr>
                                                    <td align="right">' . $this->lang['platform_companyname'] . ':</td>
                                                    <td colspan="3"><input type="text" name="platform_companyname" size="25" value="' . $this->platform_companyname . '" class="input" /></td>
						</tr>
						<tr>
                                                    <td align="right">' . $this->lang['platform_companyvat'] . ':</td>
                                                    <td><input type="text" name="platform_companyvat" size="25" value="' . $this->platform_companyvat . '" class="input" /></td>
                                                    <td align="right">' . $this->lang['platform_companyno'] . ':</td>
                                                    <td><input type="text" name="platform_companyno" size="25" value="' . $this->platform_companyno . '" class="input" /></td>
						</tr>
						<tr>
                                                    <td align="right">' . $this->lang['platform_postcode'] . ':<span class="requared">*</span></td>
                                                    <td><input type="text" name="platform_postcode" size="25" value="' . $this->platform_postcode . '" class="input" required /></td>
                                                    <td align="right">' . $this->lang['platform_street'] . ':<span class="requared">*</span></td>
                                                    <td><input type="text" name="platform_street" size="25" value="' . $this->platform_street . '" class="input" required /></td>
						</tr>
						<tr>
                                                    <td align="right">' . $this->lang['platform_suburb'] . ':</td>
                                                    <td><input type="text" name="platform_suburb" size="25" value="' . $this->platform_suburb . '" class="input" /></td>
                                                    <td align="right">' . $this->lang['platform_city'] . ':</td>
                                                    <td><input type="text" name="platform_city" size="25" value="' . $this->platform_city . '" class="input" /></td>
						</tr>
						<tr>
                                                    <td align="right">' . $this->lang['platform_state'] . ':</td>
                                                    <td><input type="text" name="platform_state" size="25" value="' . $this->platform_state . '" class="input" /></td>
                                                    <td align="right">' . $this->lang['platform_country'] . ':</td>
                                                    <td>
                                                        <select name="platform_country">';
        foreach ($cuntries as $id => $title) {
            $content .= '<option value="' . $id . '"' . ($this->platform_country == $id ? ' selected' : '') . '>' . $title . '</option>';
        }
        $content .= '</select>
						</tr>

						<tr>
                                                    <th class="" colspan="4">' . $this->lang['sample_data'] . '</th>
						</tr>
                                                <tr>
                                                  <td colspan="4" class="empty_line"></td>
                                                </tr>
						<tr>
                                                    <td align="left" colspan="2"><label class="pointer"><input type="checkbox" name="sample_orders" value="1" class="input input-checkbox"' . ($this->sample_orders == 1 ? ' checked' : '') . ' />' . $this->lang['sample_orders'] . '</label></td>
						</tr>

					</table>';
        return $content;
    }

    public function get_cuntries() {
        include_once $this->root_path . 'includes/local/configure.php';
        $link = mysqli_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
        if (!$link) {
            $this->log('install_error', 'Cant connect to database server.', mysqli_connect_error());
            return false;
        }
        $db_selected = mysqli_select_db($link, DB_DATABASE);
        if (!$link) {
            $this->log('install_error', 'Wrong database name.');
            return false;
        }
        switch ($this->def_lang) {
            case 'english':
                $code = 'en';
                break;
            default:
                $code = 'en';
                break;
        }
        $db_query = mysqli_query($link, "select languages_id from languages where code = '" . $code . "'");
        if (!$db_query) {
            $this->log('install_error', 'Cant find requested language.');
            return false;
        }
        $result = mysqli_fetch_array($db_query, MYSQLI_ASSOC);
        if (!isset($result['languages_id'])) {
            $result['languages_id'] = 1;
        }
        $db_query = mysqli_query($link, "select countries_id, countries_name from countries where language_id = '" . (int) $result['languages_id'] . "' and status=1 order by countries_name");
        $countries = [];
        while ($result = mysqli_fetch_array($db_query, MYSQLI_ASSOC)) {
            $countries[$result['countries_id']] = $result['countries_name'];
        }
        return $countries;
    }

    public function get_filled_output() {
        return $this->get_output();
    }

    public function parse_input() {

        $this->platform_type = $_POST['platform_type'];
        $this->platform_owner = $_POST['platform_owner'];
        $this->platform_name = $_POST['platform_name'];
        $this->platform_url = $_POST['platform_url'];
        $this->enable_ssl = (int)$_POST['enable_ssl'];
        $this->platform_email = $_POST['platform_email'];
        $this->platform_from = $_POST['platform_from'];
        $this->platform_extra = $_POST['platform_extra'];
        $this->platform_phone = $_POST['platform_phone'];
        $this->platform_landline = $_POST['platform_landline'];
        $this->platform_companyname = $_POST['platform_companyname'];
        $this->platform_companyvat = $_POST['platform_companyvat'];
        $this->platform_companyno = $_POST['platform_companyno'];
        $this->platform_postcode = $_POST['platform_postcode'];
        $this->platform_street = $_POST['platform_street'];
        $this->platform_suburb = $_POST['platform_suburb'];
        $this->platform_city = $_POST['platform_city'];
        $this->platform_state = $_POST['platform_state'];
        $this->platform_country = (int)$_POST['platform_country'];
        $this->sample_orders = (int)$_POST['sample_orders'];

        include_once $this->root_path . 'includes/local/configure.php';
        $link = mysqli_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
        if (!$link) {
            $this->log('install_error', 'Can\'t connect to database server.', mysqli_connect_error());
            return false;
        }
        $db_selected = mysqli_select_db($link, DB_DATABASE);
        if (!$link) {
            $this->log('install_error', 'Wrong database name.');
            return false;
        }

        $query = "UPDATE platforms SET " .
                "platform_owner='" . $this->prepare_input($this->platform_owner) . "'" .
                ", platform_name='" . $this->prepare_input($this->platform_name) . "'" .
                ", platform_url='" . $this->prepare_input($this->platform_url) . "'" .
                ", ssl_enabled='" . (int) $this->enable_ssl . "'" .
                ", platform_email_address='" . $this->prepare_input($this->platform_email) . "'" .
                ", platform_email_from='" . $this->prepare_input($this->platform_from) . "'" .
                ", platform_email_extra='" . $this->prepare_input($this->platform_extra) . "'" .
                ", platform_telephone='" . $this->prepare_input($this->platform_phone) . "'" .
                ", platform_landline='" . $this->prepare_input($this->platform_landline) . "'" .
                " WHERE platform_id=1;";
        $result = mysqli_query($link, $query);
        if (!$result) {
            $this->log('install_error', 'Can\'t update database settings: ' . $link->error, $query);
            return false;
        }
        $query = "UPDATE platforms SET " .
                "platform_owner='" . $this->prepare_input($this->platform_owner) . "'" .
                //", platform_name='" . $this->prepare_input($this->platform_name) . "'" .
                ", platform_url='" . $this->prepare_input($this->platform_url) . "/furniture'" .
                ", ssl_enabled='" . (int) $this->enable_ssl . "'" .
                ", platform_email_address='" . $this->prepare_input($this->platform_email) . "'" .
                ", platform_email_from='" . $this->prepare_input($this->platform_from) . "'" .
                ", platform_email_extra='" . $this->prepare_input($this->platform_extra) . "'" .
                ", platform_telephone='" . $this->prepare_input($this->platform_phone) . "'" .
                ", platform_landline='" . $this->prepare_input($this->platform_landline) . "'" .
                " WHERE platform_id=7;";
        $result = mysqli_query($link, $query);
        if (!$result) {
            $this->log('install_error', 'Can\'t update database settings: ' . $link->error, $query);
            return false;
        }
        $query = "UPDATE platforms SET " .
                "platform_owner='" . $this->prepare_input($this->platform_owner) . "'" .
                //", platform_name='" . $this->prepare_input($this->platform_name) . "'" .
                ", platform_url='" . $this->prepare_input($this->platform_url) . "/watch'" .
                ", ssl_enabled='" . (int) $this->enable_ssl . "'" .
                ", platform_email_address='" . $this->prepare_input($this->platform_email) . "'" .
                ", platform_email_from='" . $this->prepare_input($this->platform_from) . "'" .
                ", platform_email_extra='" . $this->prepare_input($this->platform_extra) . "'" .
                ", platform_telephone='" . $this->prepare_input($this->platform_phone) . "'" .
                ", platform_landline='" . $this->prepare_input($this->platform_landline) . "'" .
                " WHERE platform_id=8;";
        $result = mysqli_query($link, $query);
        if (!$result) {
            $this->log('install_error', 'Can\'t update database settings: ' . $link->error, $query);
            return false;
        }
        $query = "UPDATE platforms SET " .
                "platform_owner='" . $this->prepare_input($this->platform_owner) . "'" .
                //", platform_name='" . $this->prepare_input($this->platform_name) . "'" .
                ", platform_url='" . $this->prepare_input($this->platform_url) . "/b2b-supermarket'" .
                ", ssl_enabled='" . (int) $this->enable_ssl . "'" .
                ", platform_email_address='" . $this->prepare_input($this->platform_email) . "'" .
                ", platform_email_from='" . $this->prepare_input($this->platform_from) . "'" .
                ", platform_email_extra='" . $this->prepare_input($this->platform_extra) . "'" .
                ", platform_telephone='" . $this->prepare_input($this->platform_phone) . "'" .
                ", platform_landline='" . $this->prepare_input($this->platform_landline) . "'" .
                " WHERE platform_id=9;";
        $result = mysqli_query($link, $query);
        if (!$result) {
            $this->log('install_error', 'Can\'t update database settings: ' . $link->error, $query);
            return false;
        }
        $query = "UPDATE platforms SET " .
                "platform_owner='" . $this->prepare_input($this->platform_owner) . "'" .
                //", platform_name='" . $this->prepare_input($this->platform_name) . "'" .
                ", platform_url='" . $this->prepare_input($this->platform_url) . "/printshop'" .
                ", ssl_enabled='" . (int) $this->enable_ssl . "'" .
                ", platform_email_address='" . $this->prepare_input($this->platform_email) . "'" .
                ", platform_email_from='" . $this->prepare_input($this->platform_from) . "'" .
                ", platform_email_extra='" . $this->prepare_input($this->platform_extra) . "'" .
                ", platform_telephone='" . $this->prepare_input($this->platform_phone) . "'" .
                ", platform_landline='" . $this->prepare_input($this->platform_landline) . "'" .
                " WHERE platform_id=10;";
        $result = mysqli_query($link, $query);
        if (!$result) {
            $this->log('install_error', 'Can\'t update database settings: ' . $link->error, $query);
            return false;
        }

        $query = "UPDATE platforms_address_book SET " .
                "entry_company='" . $this->prepare_input($this->platform_companyname) . "'" .
                ", entry_company_vat='" . $this->prepare_input($this->platform_companyvat) . "'" .
                ", entry_company_reg_number='" . $this->prepare_input($this->platform_companyno) . "'" .
                ", entry_postcode='" . $this->prepare_input($this->platform_postcode) . "'" .
                ", entry_street_address='" . $this->prepare_input($this->platform_street) . "'" .
                ", entry_suburb='" . $this->prepare_input($this->platform_suburb) . "'" .
                ", entry_city='" . $this->prepare_input($this->platform_city) . "'" .
                ", entry_state='" . $this->prepare_input($this->platform_state) . "'" .
                ", entry_country_id='" . (int) $this->platform_country . "'" .
                ", entry_zone_id='" . 0 . "'" .
                " WHERE platform_id IN (1, 7, 8, 9, 10);";
        $result = mysqli_query($link, $query);
        if (!$result) {
            $this->log('install_error', 'Can\'t update database settings: ' . $link->error, $query);
            return false;
        }

        $query = "UPDATE configuration SET configuration_value='" . $this->prepare_input($this->platform_email) . "' WHERE configuration_key='STORE_OWNER_EMAIL_ADDRESS';";
        $result = mysqli_query($link, $query);
        if (!$result) {
            $this->log('install_error', 'Can\'t update database settings: ' . $link->error, $query);
            return false;
        }

        $query = "UPDATE configuration SET configuration_value='" . $this->prepare_input($this->platform_from) . "' WHERE configuration_key='EMAIL_FROM';";
        $result = mysqli_query($link, $query);
        if (!$result) {
            $this->log('install_error', 'Can\'t update database settings: ' . $link->error, $query);
            return false;
        }
        
        switch ($this->platform_type) {
            case '7':// Furniture
            case '8':// Watch
            case '9':// b2b supermarket
            case '10':// Print Shop
                $query = "UPDATE platforms SET is_default='0', status='0', platform_url='".$this->prepare_input($this->platform_url)."' WHERE platform_id IN (1, 7, 8, 9, 10);";
                $result = mysqli_query($link, $query);
                if (!$result) {
                    $this->log('install_error', 'Can\'t update database settings: ' . $link->error, $query);
                    return false;
                }

                $query = "UPDATE platforms SET is_default='1', status='1' WHERE platform_id='".$this->platform_type."';";
                $result = mysqli_query($link, $query);
                if (!$result) {
                    $this->log('install_error', 'Can\'t update database settings: ' . $link->error, $query);
                    return false;
                }
                
                break;
            case '0':// Splash page and multi sales channels
            default:
                break;
        }
        

        if ($this->enable_ssl == 0) {
            error_clear_last();
            $content = file_get_contents($this->root_path . 'admin/includes/local/configure.php');
            if ($content === false) {
                $this->log('install_error', 'Cant read admin config file.', error_get_last()['message']??null);
                return false;
            }
            $content = str_replace(["define('ENABLE_SSL', true);", "define('ENABLE_SSL_CATALOG', true);"], ["define('ENABLE_SSL', false);", "define('ENABLE_SSL_CATALOG', false);"], $content);
            error_clear_last();
            $response = file_put_contents($this->root_path . 'admin/includes/local/configure.php', $content);
            if ($response === false) {
                $this->log('install_error', 'Can\'t save admin config file.', error_get_last()['message']??null);
                return false;
            }
        } else {
            error_clear_last();
            $content = file_get_contents($this->root_path . 'admin/includes/local/configure.php');
            if ($content === false) {
                $this->log('install_error', 'Cant read admin config file.', error_get_last()['message']??null);
                return false;
            }
            $content = str_replace(["define('HTTP_SERVER', 'http:", "define('HTTP_CATALOG_SERVER', 'http:"], ["define('HTTP_SERVER', 'https:", "define('HTTP_CATALOG_SERVER', 'https:"], $content);
            error_clear_last();
            $response = file_put_contents($this->root_path . 'admin/includes/local/configure.php', $content);
            if ($response === false) {
                $this->log('install_error', 'Can\'t save admin config file.', error_get_last()['message']??null);
                return false;
            }
        }

        if (preg_match('/^www/', $this->platform_url)) {
            $content = file_get_contents($this->root_path . '.htaccess');
            if ($content !== false) {
                if ($this->enable_ssl == 2) {
                    $content = str_replace(["#RewriteCond %{HTTP_HOST} !^www\.", "#RewriteCond %{HTTPS}s on(s)|offs()", "#RewriteRule ^ http%1://www.%{HTTP_HOST}%{REQUEST_URI} [NE,L,R]"], ["RewriteCond %{HTTPS}s on(s)|offs()", "RewriteCond %{HTTP_HOST} !^www\.", "RewriteRule ^ https://www.%{HTTP_HOST}%{REQUEST_URI} [NE,L,R]"], $content);
                } else {
                    $content = str_replace(["#RewriteCond %{HTTP_HOST} !^www\.", "#RewriteRule ^ http%1://www.%{HTTP_HOST}%{REQUEST_URI} [NE,L,R]"], ["RewriteCond %{HTTP_HOST} !^www\.", "RewriteRule ^ http%1://www.%{HTTP_HOST}%{REQUEST_URI} [NE,L,R]"], $content);
                }
                $response = file_put_contents($this->root_path . '.htaccess', $content);
            }
        }

        if ($this->sample_orders == 1) {
            $curlSession = curl_init();
            curl_setopt($curlSession, CURLOPT_URL, 'http' . ($this->enable_ssl == 2 ? 's' : '') . '://' . $this->platform_url . '/install/sample_orders.php');
            curl_setopt($curlSession, CURLOPT_HEADER, false);
            //curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
            curl_setopt ($curlSession, CURLOPT_SSL_VERIFYPEER, 0 );
            curl_setopt ($curlSession, CURLOPT_SSL_VERIFYHOST, 0 );
            $response = curl_exec($curlSession);
            curl_close($curlSession);
        }
        
        mysqli_close($link);
        return true;
    }

}
