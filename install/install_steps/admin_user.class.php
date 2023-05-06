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

class admin_user extends install_generic {

    public static $before = 'inst_settings';
    public $next_button = 'create_user';

    private $username = '';
    private $useremail = '';

    private $admin_firstname = '';
    private $admin_lastname = '';

    public static function before() {
        return self::$before;
    }

    public function get_output() {
        $content = '<table width="100%" border="0" cellspacing="1" cellpadding="2" class="no-borders table-db-access">
                    <tr>
                        <td align="right" width="25%">' . $this->lang['admin_firstname'] . ':<span class="requared">*</span></td>
                        <td align="left" width="25%"><input type="text" name="admin_firstname" value="' . $this->admin_firstname . '" class="input" required /></td>
                        <td align="right" width="25%">' . $this->lang['admin_lastname'] . ':<span class="requared">*</span></td>
                        <td align="left" width="25%"><input type="text" name="admin_lastname" value="' . $this->admin_lastname . '" class="input" size="30" required /></td>
                    </tr>
                    <tr>
                        <td align="right" width="25%">' . $this->lang['username'] . ':<span class="requared">*</span></td>
                        <td align="left" width="25%"><input type="text" name="username" value="' . $this->username . '" class="input" required /></td>
                        <td align="right" width="25%">' . $this->lang['user_email'] . ':<span class="requared">*</span></td>
                        <td align="left" width="25%"><input type="text" name="user_email" value="' . $this->useremail . '" class="input" size="30" required /></td>
                    </tr>
                    <tr>
                        <td align="right" width="25%"><a href="javascript:void(0)" onclick="generate_password();"><span class="pass_confirm">Generate</span><span class="pass_manual" style="display:none;">Regenerate</span> password</a></td>
                        <td align="left" width="25%"><a class="pass_manual" style="display:none;" href="javascript:void(0)" onclick="manual_pass();">Manual input</a></td>
                        <td align="right" width="25%"></td>
                        <td align="left" width="25%"></td>
                    </tr>
                    <tr>
                        <td align="right" width="25%">' . $this->lang['user_password'] . ':<span class="requared">*</span></td>
                        <td align="left" width="25%"><input onkeyup="check()" type="password" name="user_password1" class="user_password1" id="user_password1" value="" class="input"  required /><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAARCAYAAAAyhueAAAAACXBIWXMAAAsTAAALEwEAmpwYAAADdklEQVR42l2US09TURSFT3vbKiUBiTxDIigkwtBEJ8wM1dJggATCm4I82goEKMQC5f0uz4KQEJ3o0L/g1J+hI4eCUQkjB1LXdwOkOGhve+7ea6+99trHLCwsOJeWlkwsFnvc3t6+vbOzY2ZnZy2dm+XlZbO1tWUODw/N0dGR/dzc3DS8U4xLT8fi4qL9n+fVx8zPz1srKysmGo0+zcjI+NvS0jIE0NzcnFufvHA4HGhsbBxWwZn6+vrBoaGhZ2tra7eIAUz5zv+B7S+9cKyurholRhwOR6q1tXUwmUyampqaj9nZ2Rf5+fkn5eXl33Nzc39mZWWlCgsLfzU0NCREJps8Fb8BfP2Dlvf29kxPT0+HMSalAqH19XX35ORkmZ6u4+NjB4UEdKe5uTlWXFz8o6io6GRsbOw5kqQD24AcbGxsmIGBgZcCzxdwEMYCHjg4ODBqO1laWnpaUlLyRV285kzx3urq6ndutzvV3d09nkgkwLFspjMzMy700Yuox+NJRSKRrt3dXdPW1mZL0dnZGdLAvB0dHbHa2tr9zMzMi6qqqk9i74RhV1dXlLj+/n57FsKzzPb2NkN6YVlWSolRKk5PT9+m1WAw2IMUAu7d39+3J69OHnq93gt1EZcUToD0fhzGwgmQb1TxUV5e3q9AIPAWO8XjcUvTdYlpUq1kikkvwCr4Suc2cF1d3ZvKysqvzGBqaspDHvkFBQVnOrtnKioqvgn0XMF38Z50IdGtQfwR0xEYCzDsdDoBDl92MMJ74ognT4xz5IxzSfQZcz+RZX77fL4PaEllGMAERjCDIUxhDHM6oBM6ojPylP9eljvTEB8YqMsWATSRNhNoJO850Qzt0BBgNEVbgNEaxmiPhoqduNS0BhehoZth9fX1DTNFMRkHRFpbTJlpM3WmjwtwA3G4g45CodBLXCM7hq6nf+lTiwqylV0R/4mBFz/iS/yJTyVHEsb4F2D8jK8FHCT/agGuN4oDGI6Ojvq1hqdsjO6B12wQrbJRKuRSy+XS0i3mIaRgA2HMRt7YqHRgdpmd1hYl2HF2nZ0vKyv7rv8nOTk5Kb/f/5FC3BEwpgDD5A65vlDSgbl1+I0+CvToVvLp8ohIy3hTU9Ogbi2/iudyixHDrYZk6tDHbUf+jXsw/fpSkksBtp2wDC7hqQGmx1hIpqmj7f0rjH9upA5bbnfKaAAAAABJRU5ErkJggg==" class="eye" id="togglePassword" /></td>
                        <td align="right" width="25%"><span class="pass_confirm">' . $this->lang['user_pw_confirm'] . ':<span class="requared">*</span></span></td>
                        <td align="left" width="25%"><input onkeyup="check()" type="password" name="user_password2" class="pass_confirm" id="pass_confirm" value="" class="input" required /><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAARCAYAAAAyhueAAAAACXBIWXMAAAsTAAALEwEAmpwYAAADdklEQVR42l2US09TURSFT3vbKiUBiTxDIigkwtBEJ8wM1dJggATCm4I82goEKMQC5f0uz4KQEJ3o0L/g1J+hI4eCUQkjB1LXdwOkOGhve+7ea6+99trHLCwsOJeWlkwsFnvc3t6+vbOzY2ZnZy2dm+XlZbO1tWUODw/N0dGR/dzc3DS8U4xLT8fi4qL9n+fVx8zPz1srKysmGo0+zcjI+NvS0jIE0NzcnFufvHA4HGhsbBxWwZn6+vrBoaGhZ2tra7eIAUz5zv+B7S+9cKyurholRhwOR6q1tXUwmUyampqaj9nZ2Rf5+fkn5eXl33Nzc39mZWWlCgsLfzU0NCREJps8Fb8BfP2Dlvf29kxPT0+HMSalAqH19XX35ORkmZ6u4+NjB4UEdKe5uTlWXFz8o6io6GRsbOw5kqQD24AcbGxsmIGBgZcCzxdwEMYCHjg4ODBqO1laWnpaUlLyRV285kzx3urq6ndutzvV3d09nkgkwLFspjMzMy700Yuox+NJRSKRrt3dXdPW1mZL0dnZGdLAvB0dHbHa2tr9zMzMi6qqqk9i74RhV1dXlLj+/n57FsKzzPb2NkN6YVlWSolRKk5PT9+m1WAw2IMUAu7d39+3J69OHnq93gt1EZcUToD0fhzGwgmQb1TxUV5e3q9AIPAWO8XjcUvTdYlpUq1kikkvwCr4Suc2cF1d3ZvKysqvzGBqaspDHvkFBQVnOrtnKioqvgn0XMF38Z50IdGtQfwR0xEYCzDsdDoBDl92MMJ74ognT4xz5IxzSfQZcz+RZX77fL4PaEllGMAERjCDIUxhDHM6oBM6ojPylP9eljvTEB8YqMsWATSRNhNoJO850Qzt0BBgNEVbgNEaxmiPhoqduNS0BhehoZth9fX1DTNFMRkHRFpbTJlpM3WmjwtwA3G4g45CodBLXCM7hq6nf+lTiwqylV0R/4mBFz/iS/yJTyVHEsb4F2D8jK8FHCT/agGuN4oDGI6Ojvq1hqdsjO6B12wQrbJRKuRSy+XS0i3mIaRgA2HMRt7YqHRgdpmd1hYl2HF2nZ0vKyv7rv8nOTk5Kb/f/5FC3BEwpgDD5A65vlDSgbl1+I0+CvToVvLp8ohIy3hTU9Ogbi2/iudyixHDrYZk6tDHbUf+jXsw/fpSkksBtp2wDC7hqQGmx1hIpqmj7f0rjH9upA5bbnfKaAAAAABJRU5ErkJggg==" class="eye pass_confirm" id="togglePassword1" /></td>
                    </tr>
                    <tr>
                      <td colspan="4" style="text-align: right"><span id="message"></span></td>
                    </tr>
            </table>';
        $content .= '<script>
                function generate_password(){
                    var randomstring = Math.random().toString(36).slice(-12);
                    $(".user_password1").val(randomstring);
                    $("input.pass_confirm").val(randomstring);
                    $(".pass_confirm").hide();
                    $(".pass_manual").val("").show();
                    return false;
                }
                function manual_pass(){
                    $(".user_password1").val("");
                    $("input.pass_confirm").val("");
                    $(".pass_confirm").show();
                    $(".pass_manual").val("").hide();
                }
                const togglePassword = document.querySelector("#togglePassword");
                const password = document.querySelector("#user_password1");
                const togglePassword1 = document.querySelector("#togglePassword1");
                const password1 = document.querySelector("#pass_confirm");

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

                togglePassword1.addEventListener("click", function (e) {
                    const type = password1.getAttribute("type") === "password" ? "text" : "password";
                    password1.setAttribute("type", type);
                    this.classList.toggle("active");

                    if(togglePassword1.classList.contains("active")){
                      this.src = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAARCAYAAAAyhueAAAAACXBIWXMAAAsTAAALEwEAmpwYAAACjklEQVR42qVUu48ScRD+LQcekCyRgmB4FMRAQbSnAxKagxAhx4FESCjARAkUGMPyhuUNy+Nf8E+wtbewsLM4KxMbbNTO7jjn27BkOfHUSLLMznvmm5llnU6HdbtdBqo8rVZLJwjCqSiKbLFYsOVyydbrtUzBQw497NR+Spz9CyieZrOpBT+bzeDoyOVyjxKJxItUKiWBgoccetjBXvFV4hxkaTQa2slkwtrttjMSiUztdvvGbDZ/s1qt791u9xtQ8JBDDzvY1+t1rbpbdUAO2Uul0rnNZvvsdDo/kGOpUqncm06nctug4CGHHnawn8/nsv++ffrjKBMHB2qtbjAYrv1+f5/w4hEoFos9p8reUYBL0Hg8/gzYQh8IBETYZ7PZ1ng8RsUaOSjho0emfD7/Uq/XX6XT6RQSDIdD5vP5XvE8/yUajQqFQuEMFDzkg8FArhz2Op3uigoSdhWfygMpFotJCrh9TD8Y9vt9RkN5SgE21O790WgkTxwUvMlk2kAPOyUwVbwlKC56vR5DwDPCZxMKhXqSJLFarXZCrd3xeDxvw+Fwc7VayesDmEDBQw497GCPCsm/43A4vhIED5jX6/1hsVg+EiY8DJCpWq3edblcl1T4EwKfI2d5ujvKQQ497GBPgU4IDhN19onwff3flSIgKg0GgyIN8jvxDxkEO0yvb2IK7I5hCqwVTDET+BmNxm25XE7ImCrTp+keTB/T/Yfpbw+mf3NPkZH2VKRr4dEq9lK9p9hb7C/02FN0mMlk2thTCqg5elHUwl9dFE36l4s6OFP17SMjbpqGcevtQ//b27/tK0WbYCNIoslkskKzkEDBQ/7Hr5Q6y+5dg6ppZfZTx9kqWwA59LA74st+Ar8TAcCyFjiKAAAAAElFTkSuQmCC";
                    }else{
                      this.src = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAARCAYAAAAyhueAAAAACXBIWXMAAAsTAAALEwEAmpwYAAADdklEQVR42l2US09TURSFT3vbKiUBiTxDIigkwtBEJ8wM1dJggATCm4I82goEKMQC5f0uz4KQEJ3o0L/g1J+hI4eCUQkjB1LXdwOkOGhve+7ea6+99trHLCwsOJeWlkwsFnvc3t6+vbOzY2ZnZy2dm+XlZbO1tWUODw/N0dGR/dzc3DS8U4xLT8fi4qL9n+fVx8zPz1srKysmGo0+zcjI+NvS0jIE0NzcnFufvHA4HGhsbBxWwZn6+vrBoaGhZ2tra7eIAUz5zv+B7S+9cKyurholRhwOR6q1tXUwmUyampqaj9nZ2Rf5+fkn5eXl33Nzc39mZWWlCgsLfzU0NCREJps8Fb8BfP2Dlvf29kxPT0+HMSalAqH19XX35ORkmZ6u4+NjB4UEdKe5uTlWXFz8o6io6GRsbOw5kqQD24AcbGxsmIGBgZcCzxdwEMYCHjg4ODBqO1laWnpaUlLyRV285kzx3urq6ndutzvV3d09nkgkwLFspjMzMy700Yuox+NJRSKRrt3dXdPW1mZL0dnZGdLAvB0dHbHa2tr9zMzMi6qqqk9i74RhV1dXlLj+/n57FsKzzPb2NkN6YVlWSolRKk5PT9+m1WAw2IMUAu7d39+3J69OHnq93gt1EZcUToD0fhzGwgmQb1TxUV5e3q9AIPAWO8XjcUvTdYlpUq1kikkvwCr4Suc2cF1d3ZvKysqvzGBqaspDHvkFBQVnOrtnKioqvgn0XMF38Z50IdGtQfwR0xEYCzDsdDoBDl92MMJ74ognT4xz5IxzSfQZcz+RZX77fL4PaEllGMAERjCDIUxhDHM6oBM6ojPylP9eljvTEB8YqMsWATSRNhNoJO850Qzt0BBgNEVbgNEaxmiPhoqduNS0BhehoZth9fX1DTNFMRkHRFpbTJlpM3WmjwtwA3G4g45CodBLXCM7hq6nf+lTiwqylV0R/4mBFz/iS/yJTyVHEsb4F2D8jK8FHCT/agGuN4oDGI6Ojvq1hqdsjO6B12wQrbJRKuRSy+XS0i3mIaRgA2HMRt7YqHRgdpmd1hYl2HF2nZ0vKyv7rv8nOTk5Kb/f/5FC3BEwpgDD5A65vlDSgbl1+I0+CvToVvLp8ohIy3hTU9Ogbi2/iudyixHDrYZk6tDHbUf+jXsw/fpSkksBtp2wDC7hqQGmx1hIpqmj7f0rjH9upA5bbnfKaAAAAABJRU5ErkJggg==";
                    }
                });

                let check = function() {
                  if (document.getElementById("user_password1").value ==
                    document.getElementById("pass_confirm").value) {
                    document.getElementById("message").style.color = "green";
                    document.getElementById("message").innerHTML = "matching";
                  } else {
                    document.getElementById("message").style.color = "red";
                    document.getElementById("message").innerHTML = "not matching";
                  }
                }

		</script>';
        return $content;
    }

    public function get_filled_output() {
        return $this->get_output();
    }

    public function parse_input() {
        if (isset($_POST['prev']) && $_POST['prev'] == 'inst_settings') {
            return true;
        }
        $this->admin_firstname = $_POST['admin_firstname'];
        $this->admin_lastname = $_POST['admin_lastname'];
        $this->username = $_POST['username'];
        $this->useremail = $_POST['user_email'];
        if ($_POST['user_password1'] == '' || empty($this->username) || empty($this->useremail)) {
            $this->log('install_error', $this->lang['user_required']);
            return false;
        }
        if ($_POST['user_password1'] != $_POST['user_password2']) {
            $this->log('install_error', $this->lang['no_pw_match']);
            return false;
        }

        include_once $this->root_path . 'includes/local/configure.php';
        $link = mysqli_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
        if (!$link) {
            $this->log('install_error', 'Can\'t connect to database server.', mysqli_connect_error());
            return false;
        }
        $db_selected = mysqli_select_db($link, DB_DATABASE);
        if (!$db_selected)
        {
            $this->log('install_error', 'Wrong database name.');
            return false;
        }
        
        $plain = $this->prepare_input($_POST['user_password1']);
        $secKeyBackend = '';
        $secKeyFrontend = '';
        $specialCharArray = ['~','!','@','#','$','%','^','&','*','(',')','_','+','`','-','=','[',']','{','}','|','"',';',':','?','/','.','>',',','<'];
        $symbolArray = array_merge($specialCharArray, range('a', 'z'), range('A', 'Z'), range('0', '9'));
        $max = (count($symbolArray) - 1);
        mt_srand();
        for ($i = 0;$i < 64;$i++) {
            $secKeyBackend .= $symbolArray[mt_rand(0, $max)];
            $secKeyFrontend .= $symbolArray[mt_rand(0, $max)];
        }
        unset($symbolArray);
        $secKeyGlobal = md5('mysql:host='.DB_SERVER.';dbname='.DB_DATABASE . INSTALLED_MICROTIME);

        $content  = '<?php' . "\n";
        $content  .= "return [" . "\n";
        $content  .= "    'secKey.global' => '" . $secKeyGlobal . "'," . "\n";
        $content  .= "    'secKey.backend' => '" . $secKeyBackend . "'," . "\n";
        $content  .= "    'secKey.frontend' => '" . $secKeyFrontend . "'" . "\n";
        $content  .= "];" . "\n";
        $result = file_put_contents($this->root_path . '/lib/common/config/params-local.php', $content);
        if ($result === false) {
            $this->log('install_error', 'Can\'t save local params file.', error_get_last()['message']??null);
            return false;
        }
        
        $query = "INSERT INTO `configuration` (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`) VALUES ('Date of installation', 'INSTALLED_DATE', now(), '', '', NULL, NULL, now());";
        mysqli_query($link, $query);
        
        $query = "INSERT INTO `configuration` (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`) VALUES ('Date of last update', 'UPDATED_DATE', '', '', '', NULL, NULL, now());";
        mysqli_query($link, $query);

        $query = "UPDATE admin SET " .
                "admin_username='" . $this->prepare_input($this->username) . "'" .
                ", admin_email_address='" . $this->prepare_input($this->useremail) . "'" .
                ", admin_firstname='" . $this->prepare_input($this->admin_firstname) . "'" .
                ", admin_lastname='" . $this->prepare_input($this->admin_lastname) . "'" .
                ", admin_password='" . password_hash(($plain . $secKeyBackend), PASSWORD_BCRYPT, ['cost' => 13]) . "'" .
                ", admin_email_token='" . password_hash(($this->useremail . $secKeyBackend), PASSWORD_BCRYPT, ['cost' => 13]) . "'" .
                ", admin_created=now()" .
                ", admin_modified=now()" .
                " WHERE admin_id=28;";
        $result = mysqli_query($link, $query);
        if (!$result) {
            $this->log('install_error', 'Can\'t update settings.');
            return false;
        } else {
            $hostname = $_SERVER['HTTP_HOST'];
            $pathname = rtrim(trim(dirname(dirname($_SERVER['SCRIPT_NAME']) . '../'), '/'), '/');

            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

            $message = '
            <html>
            <head>
              <title>New Administrator</title>
            </head>
            <body>

	<table width="100%" cellspacing="0" cellpadding="0" border="0">
	  <tbody>
		<tr>
		  <td>
			<div style="max-width:800px; margin:0 auto; background-color:#ffffff;">
			
			
			  <div style="font-weight:normal;background-color:#101622;text-align:center;padding-top:10px;padding-bottom:10px">
				<div> 
				<a href="https://www.oscommerce.com" target="_blank">
				<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQkAAAApCAYAAAAms9zmAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA4RpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQ4IDc5LjE2NDAzNiwgMjAxOS8wOC8xMy0wMTowNjo1NyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDphOGZkMmY5ZC00MzNmLWNlNDEtOGYxMi1mYjg0MGI4OGE4NzYiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QTk5OEFBQzQ4MzRBMTFFQzlBRDlDOEJCRDUxQzUzQUEiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QTk5OEFBQzM4MzRBMTFFQzlBRDlDOEJCRDUxQzUzQUEiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTcgKFdpbmRvd3MpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6OWMyNzI4MzMtZTM1Yi0yMTQ5LWE1NWQtNjJhYTk4YzU3NzYxIiBzdFJlZjpkb2N1bWVudElEPSJhZG9iZTpkb2NpZDpwaG90b3Nob3A6YWYyNjQ1ODktNzllYS0xMWVjLTk5ZTEt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" alt="osCommerce LTD" style="border:none" />
				</a> 
				</div>
			  </div>  
			  
			<div style="font-weight:normal;color:#444444;font-size:14px;font-family:Arial;padding:20px 10px">

              <p>Your account has created</p>
              <table>
                <tr>
                  <td>Website:</td>
                  <td><a href="https://' . $hostname . '/'. $pathname . '/admin/' . '">view</a></td>
                </tr>
                <tr>
                  <td>Username:</td>
                  <td>' . $this->prepare_input($this->username) . '</td>
                </tr>
                <tr>
                  <td>Password:</td>
                  <td>' . $plain . '</td>
                </tr>
              </table>
              
				  <br>
				  <p>Please keep this account information confidential!</p>
			</div>
			  
			<div style="color:#444444;font-size:12px;font-family:Arial;border-top:1px solid #cccccc;padding-top:20px;padding-bottom:20px">
				<div>Copyright © 2022 osCommerce ltd.</div>
			</div>
			  
			</div>
		  </td>
		</tr>
	  </tbody>
	</table>

            </body>
            </html>
            ';
            @mail($this->prepare_input($this->useremail), 'New Administrator', $message, $headers);
        }

        mysqli_close($link);
        return true;
    }

}
