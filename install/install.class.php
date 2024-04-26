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

class Log {

    private static function getFileName() {
        return __DIR__ . DIRECTORY_SEPARATOR . 'install.log';
    }

    private static function getPrefix($type) {
        return sprintf('%s [%s] ', date('Y-m-d H-i-s'), $type);
    }

    private static function getSuffix($details) {
        if (empty($details)) {
            return '';
        }
        if (is_array($details)) {
            $res = '';
            foreach ($details as $key=>$value) {
                $res .= "$key=$value\n";
            }
            return $res;
        }
        return $details . "\n";
    }

    public static function write($message, $type = 'info', $details = null) {
        if (in_array($type, ['info', 'install_success'])) return;
        file_put_contents(self::getFileName(), self::getPrefix($type) . "$message\n" . self::getSuffix($details), FILE_APPEND);
    }
}

class install {

    public $log = [];
    private $current_step = 'start';
    private $previous = 'start';
    private $steps = array();
    private $order = array();
    private $done = array();
    private $retry_step = false;
    public $data = array();
    public $langcode = 'english';

    public function log($type, $message, $details = null) {
        $this->log[] = [
            'type' => $type,
            'message' => $message,
            'details' => $details,
        ];
        Log::write($message, $type, $details);
    }

    public function init() {
        $this->current_step = $_POST['current_step'] ?? '';
        if (empty($this->current_step)) {
            $this->current_step = 'start';
        }

        if (defined('TL_INSTALLED') && TL_INSTALLED) {
            if ($this->current_step == 'end' && !isset($_POST['next'])) {

            } elseif ($this->current_step != 'end') {
                echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                    <html>
                        <head>
                            <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
                            <link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABwUlEQVQ4jY3TP0jUYRgH8M/7O7jBJCIEj9BSidb2lgipzIKCcFCDHJocgluCggiCkAi9QqIgJGpoyAYhqkmohloayq2hQyoOryQaROLod2+Dd/jrOqHv9Lzf5/k+/3jeoAXpfH5HFMeCcBSvejt6fgpOixYFDyvD5dVsfGgRT2AaOxvUTO+2nu+YarzXcBk3K8NlkGTE13A/I26HTpRwb9fzgc0E6Xx+LIqXmlFRXMIopkR3IyfxMpPonOg8hHQ+3xHFT0EoNJxPMJ4bqdWypXc9GyC4jguZcfoTnGqKo7iMs61iqBwvw0W8zoxzJsFgMygIt3MjtfWtFlAZLtdxK0MdSrA7Q7zZSpzBu4zdl6DeeHzDyn8kWMfnhl1P8ADH5tZG9+S/ftmy/SaqH9/W6mlHPw5iNuRK1QJuYAg/iPvTYuFXO3GuVN0e+RD4jQVMJ1jFAXRhH2EuV6rm/xWvdOFpoA97MYG10HAeIbzQOKzIUtjY9nsxdgphEJONIk2Mp8XuR2GzQnUSszLn3R4RrqTFwlVaPlOuVB3CHRtttkOFWEyLhcdNIrRGJDMr+RDCCeJhwkAkCSxjEQtpsfuvBf8BAruMCRP39I8AAAAASUVORK5CYII=" type="image/x-icon" />
                            <link rel="stylesheet" type="text/css" media="screen" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" />
                            <title>Installation - Error</title>
                        </head>
                        <body>
                        <div class="ui-widget">
                            <div class="ui-state-error ui-corner-all" style="padding: 0pt 0.7em;">
                                <p>
                                <strong>Alert:</strong> Already Installed. Remove the \'install\' folder, or remove two files (includes/local/configure.php,
/admin/includes/local/configure.php) to install again.</p>
                            </div>
                        </div>
                        </body>
                    </html>';
                die();
            }
        }

        $this->data = isset($_POST['step_data']) ? unserialize(base64_decode($_POST['step_data'])) : array();
        if (isset($_POST['next']) && $this->current_step == 'end')
            $this->parse_end();
        if (isset($_POST['install_done'])) {
            $this->done = (strpos($_POST['install_done'], ',') !== false) ? explode(',', $_POST['install_done']) : (($_POST['install_done'] != '') ? array($_POST['install_done']) : array());
        }

        $this->init_language();
        $this->scan_steps();
        if (!(in_array($this->current_step, $this->steps) || $this->current_step == 'start') && $this->current_step != 'end') {
            $this->log('install_error', 'invalid current step');
        }
        if (isset($_POST['select'])) {
            $this->current_step = $_POST['select'];
        } elseif (isset($_POST['next']) || isset($_POST['prev']) || $this->current_step == 'start' || isset($_POST['skip'])) {
            if ($this->current_step == 'start' || isset($_POST['skip']) || ($this->current_step != 'end' && $this->parse_step() && isset($_POST['next'])))
                $this->next_step();
            if (isset($_POST['prev']) && !$this->retry_step)
                $this->current_step = $_POST['prev'];
        }
        $this->show();
    }

    private function init_language() {
        if (!isset($_POST['inst_lang'])) {
            $usersprache = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
            $usersprache = explode(";", $usersprache[0]);

            if (strlen($usersprache[0]) == "5") {
                $code = substr($usersprache[0], 3, 2);
            } elseif (strlen($usersprache[0]) == "2") {
                $code = $usersprache[0];
            } else {
                $code = "";
            }
            $code = strtolower($code);
            $language = $this->translate_iso_langcode($code);
            if (!is_file($this->root_path . 'language/' . $language . '/lang_install.php')) {
                $language = "english";
            }
        } else {
            $language = $_POST['inst_lang'];
        }

        if (!include_once($this->root_path . '/install/language/' . $language . '/install.php')) {
            die('Could not include the language files! Check to make sure that "' . $this->root_path . 'language/' . $language . '/install.php" exists!');
        }
        $this->lang = $lang;
    }

    private function scan_steps() {
        $steps = scandir($this->root_path . 'install/install_steps');
        foreach ($steps as $file) {
            if (substr($file, -10) != '.class.php')
                continue;
            $step = substr($file, 0, -10);
            include_once($this->root_path . 'install/install_steps/' . $file);
            if (!class_exists($step)) {
                $this->log('install_error', 'invalid step-file');
            }
            if (empty($this->data[$step])) {
                $this->data[$step] = array();
            }
            $this->steps[] = $step;
            $this->order[call_user_func(array($step, 'before'))] = $step;
            $ajax = call_user_func(array($step, 'ajax'));
            if ($ajax && isset($_POST[$ajax])) {
                $_step = new $step();
                if (method_exists($_step, 'ajax_out'))
                    $_step->ajax_out();
            }
        }
        $this->order = $this->sort_steps();
    }

    private function sort_steps() {
        $arrOut = array();
        $current = 'start';
        for ($i = 0; $i < count($this->order); $i++) {
            $arrOut[$current] = $this->order[$current];
            $current = $this->order[$current];
        }
        return $arrOut;
    }

    private function parse_step() {
        $step = end($this->order);
        while ($step != $this->current_step) {
            if (in_array($step, $this->done, true)) {
                $_step = new $step();
                if (method_exists($_step, 'undo'))
                    $_step->undo();
                unset($this->done[array_search($step, $this->done)]);
            }
            $step = array_search($step, $this->order);
            if (!in_array($step, $this->steps)) {
                $this->pdl->log('install_error', $this->lang['step_order_error']);
                return false;
            }
        }
        $step = $this->current_step;
        $_step = new $step();
        $back = $_step->parse_input();
        $this->data[$this->current_step] = $_step->data;
        if ($back && !in_array($this->current_step, $this->done)) {
            $this->done[] = $this->current_step;
        }
        if (!$back && in_array($this->current_step, $this->done)){
            unset($this->done[array_search($this->current_step, $this->done)]);
        }
        if ($back && isset($_POST['prev']) && $_POST['prev'] != '') {
            unset($this->done[array_search($this->current_step, $this->done)]);
            unset($this->done[array_search($_POST['prev'], $this->done)]);
        }
        if (!$back) {
            $this->retry_step = true;
        }
        return $back;
    }

    private function next_step() {
        $old_current = $this->current_step;
        foreach ($this->steps as $step) {
            if (call_user_func(array($step, 'before')) == $this->current_step) {
                $this->current_step = $step;
                break;
            }
        }
        if ($old_current == $this->current_step)
            $this->current_step = 'end';
    }

    private function next_button() {
        if ($this->current_step == 'end')
            return $this->lang['inst_finish'];
        if ($this->retry_step)
            return $this->lang['retry'];
        $step = $this->current_step;
        $_step = new $step();
        return $this->lang[$_step->next_button];
    }

    private function end() {
        $config = file_get_contents($this->root_path . 'includes/local/configure.php');
        $config .= 'define(\'TL_INSTALLED\', true);' . "\n\n";
        error_clear_last();
        $response = file_put_contents($this->root_path . 'includes/local/configure.php', $config);
        if ($response === false) {
            $this->log('install_error', 'Cant save config file.', error_get_last()['message']??null);
            return false;
        }
        @chmod($this->root_path . 'includes/configure.php', 0444);
        @chmod($this->root_path . 'includes/local/configure.php', 0644);
        @chmod($this->root_path . 'admin/includes/local/configure.php', 0644);

        return $this->lang['install_end_text'];
    }

    private function parse_end() {
        include_once $this->root_path . 'includes/local/configure.php';
        if (defined('TL_INSTALLED') && TL_INSTALLED) {
            $path = dirname($_SERVER['SCRIPT_FILENAME']);
            @unlink($path);
        }
        header('Location: ' . $this->root_path);
        exit;
    }

    private function get_content() {
        $this->previous = array_search($this->current_step, $this->order);
        if ($this->current_step == 'end')
            return $this->end();
        $step = $this->current_step;
        $_step = new $step();
        if (in_array($this->current_step, $this->done))
            $content = $_step->get_filled_output();
        else
            $content = $_step->get_output();
        $this->data[$this->current_step] = $_step->data;
        return $content;
    }

    private function gen_menu() {
        $menu = '';
        $count_step = '1';
        foreach ($this->order as $step) {
            $class = (in_array($step, $this->done)) ? 'done' : 'notactive';
            if (in_array(array_search($step, $this->order), $this->done))
                $class .= ' done2';
            if ($step == $this->current_step)
                $class = 'now';
            $menu .= "\n\t\t\t\t\t" . '<li class="' . $class . '" id="' . $step . '"><span class="countStep">' . $count_step . '</span><span>' . $this->lang[$step] . '<input type="hidden" name="select" id="back_' . $step . '" disabled="disabled" value="' . $step . '" /></span></li>';
            $count_step++;
        }
        return $menu;
    }

    private function lang_drop() {
        $drop = '<select name="inst_lang" id="language_drop">';
        $options = array();
        $files = scandir($this->root_path . '/install/language');
        foreach ($files as $file) {
            if (file_exists($this->root_path . '/install/language/' . $file . '/install.php'))
                $options[] = $file;
        }
        sort($options);
        foreach ($options as $option) {
            $selected = ($this->langcode == $option) ? ' selected="selected"' : '';
            $drop .= '<option value="' . $option . '"' . $selected . '>' . ucfirst($option) . '</option>';
        }
        return $drop . '</select>';
    }

    private function show() {
        if (class_exists($this->current_step)) {
            $step = $this->current_step;
            $_step = new $step();
        }

        $hostname = $_SERVER['HTTP_HOST'];
        $pathname = rtrim(trim(dirname(dirname($_SERVER['SCRIPT_NAME']) . '../'), '/'), '/');
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';

        $progress = round(100 * (count($this->done) / count($this->order)), 0);
        $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
                <link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMAAAADACAYAAABS3GwHAAAACXBIWXMAAAsTAAALEwEAmpwYAAAKT2lDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAHjanVNnVFPpFj333vRCS4iAlEtvUhUIIFJCi4AUkSYqIQkQSoghodkVUcERRUUEG8igiAOOjoCMFVEsDIoK2AfkIaKOg6OIisr74Xuja9a89+bN/rXXPues852zzwfACAyWSDNRNYAMqUIeEeCDx8TG4eQuQIEKJHAAEAizZCFz/SMBAPh+PDwrIsAHvgABeNMLCADATZvAMByH/w/qQplcAYCEAcB0kThLCIAUAEB6jkKmAEBGAYCdmCZTAKAEAGDLY2LjAFAtAGAnf+bTAICd+Jl7AQBblCEVAaCRACATZYhEAGg7AKzPVopFAFgwABRmS8Q5ANgtADBJV2ZIALC3AMDOEAuyAAgMADBRiIUpAAR7AGDIIyN4AISZABRG8lc88SuuEOcqAAB4mbI8uSQ5RYFbCC1xB1dXLh4ozkkXKxQ2YQJhmkAuwnmZGTKBNA/g88wAAKCRFRHgg/P9eM4Ors7ONo62Dl8t6r8G/yJiYuP+5c+rcEAAAOF0ftH+LC+zGoA7BoBt/qIl7gRoXgugdfeLZrIPQLUAoOnaV/Nw+H48PEWhkLnZ2eXk5NhKxEJbYcpXff5nwl/AV/1s+X48/Pf14L7iJIEyXYFHBPjgwsz0TKUcz5IJhGLc5o9H/LcL//wd0yLESWK5WCoU41EScY5EmozzMqUiiUKSKcUl0v9k4t8s+wM+3zUAsGo+AXuRLahdYwP2SycQWHTA4vcAAPK7b8HUKAgDgGiD4c93/+8//UegJQCAZkmScQAAXkQkLlTKsz/HCAAARKCBKrBBG/TBGCzABhzBBdzBC/xgNoRCJMTCQhBCCmSAHHJgKayCQiiGzbAdKmAv1EAdNMBRaIaTcA4uwlW4Dj1wD/phCJ7BKLyBCQRByAgTYSHaiAFiilgjjggXmYX4IcFIBBKLJCDJiBRRIkuRNUgxUopUIFVIHfI9cgI5h1xGupE7yAAygvyGvEcxlIGyUT3UDLVDuag3GoRGogvQZHQxmo8WoJvQcrQaPYw2oefQq2gP2o8+Q8cwwOgYBzPEbDAuxsNCsTgsCZNjy7EirAyrxhqwVqwDu4n1Y8+xdwQSgUXACTYEd0IgYR5BSFhMWE7YSKggHCQ0EdoJNwkDhFHCJyKTqEu0JroR+cQYYjIxh1hILCPWEo8TLxB7iEPENyQSiUMyJ7mQAkmxpFTSEtJG0m5SI+ksqZs0SBojk8naZGuyBzmULCAryIXkneTD5DPkG+Qh8lsKnWJAcaT4U+IoUspqShnlEOU05QZlmDJBVaOaUt2ooVQRNY9aQq2htlKvUYeoEzR1mjnNgxZJS6WtopXTGmgXaPdpr+h0uhHdlR5Ol9BX0svpR+iX6AP0dwwNhhWDx4hnKBmbGAcYZxl3GK+YTKYZ04sZx1QwNzHrmOeZD5lvVVgqtip8FZHKCpVKlSaVGyovVKmqpqreqgtV81XLVI+pXlN9rkZVM1PjqQnUlqtVqp1Q61MbU2epO6iHqmeob1Q/pH5Z/YkGWcNMw09DpFGgsV/jvMYgC2MZs3gsIWsNq4Z1gTXEJrHN2Xx2KruY/R27iz2qqaE5QzNKM1ezUvOUZj8H45hx+Jx0TgnnKKeX836K3hTvKeIpG6Y0TLkxZVxrqpaXllirSKtRq0frvTau7aedpr1Fu1n7gQ5Bx0onXCdHZ4/OBZ3nU9lT3acKpxZNPTr1ri6qa6UbobtEd79up+6Ynr5egJ5Mb6feeb3n+hx9L/1U/W36p/VHDFgGswwkBtsMzhg8xTVxbzwdL8fb8VFDXcNAQ6VhlWGX4YSRudE8o9VGjUYPjGnGXOMk423GbcajJgYmISZLTepN7ppSTbmmKaY7TDtMx83MzaLN1pk1mz0x1zLnm+eb15vft2BaeFostqi2uGVJsuRaplnutrxuhVo5WaVYVVpds0atna0l1rutu6cRp7lOk06rntZnw7Dxtsm2qbcZsOXYBtuutm22fWFnYhdnt8Wuw+6TvZN9un2N/T0HDYfZDqsdWh1+c7RyFDpWOt6azpzuP33F9JbpL2dYzxDP2DPjthPLKcRpnVOb00dnF2e5c4PziIuJS4LLLpc+Lpsbxt3IveRKdPVxXeF60vWdm7Obwu2o26/uNu5p7ofcn8w0nymeWTNz0MPIQ+BR5dE/C5+VMGvfrH5PQ0+BZ7XnIy9jL5FXrdewt6V3qvdh7xc+9j5yn+M+4zw33jLeWV/MN8C3yLfLT8Nvnl+F30N/I/9k/3r/0QCngCUBZwOJgUGBWwL7+Hp8Ib+OPzrbZfay2e1BjKC5QRVBj4KtguXBrSFoyOyQrSH355jOkc5pDoVQfujW0Adh5mGLw34MJ4WHhVeGP45wiFga0TGXNXfR3ENz30T6RJZE3ptnMU85ry1KNSo+qi5qPNo3ujS6P8YuZlnM1VidWElsSxw5LiquNm5svt/87fOH4p3iC+N7F5gvyF1weaHOwvSFpxapLhIsOpZATIhOOJTwQRAqqBaMJfITdyWOCnnCHcJnIi/RNtGI2ENcKh5O8kgqTXqS7JG8NXkkxTOlLOW5hCepkLxMDUzdmzqeFpp2IG0yPTq9MYOSkZBxQqohTZO2Z+pn5mZ2y6xlhbL+xW6Lty8elQfJa7OQrAVZLQq2QqboVFoo1yoHsmdlV2a/zYnKOZarnivN7cyzytuQN5zvn//tEsIS4ZK2pYZLVy0dWOa9rGo5sjxxedsK4xUFK4ZWBqw8uIq2Km3VT6vtV5eufr0mek1rgV7ByoLBtQFr6wtVCuWFfevc1+1dT1gvWd+1YfqGnRs+FYmKrhTbF5cVf9go3HjlG4dvyr+Z3JS0qavEuWTPZtJm6ebeLZ5bDpaql+aXDm4N2dq0Dd9WtO319kXbL5fNKNu7g7ZDuaO/PLi8ZafJzs07P1SkVPRU+lQ27tLdtWHX+G7R7ht7vPY07NXbW7z3/T7JvttVAVVN1WbVZftJ+7P3P66Jqun4lvttXa1ObXHtxwPSA/0HIw6217nU1R3SPVRSj9Yr60cOxx++/p3vdy0NNg1VjZzG4iNwRHnk6fcJ3/ceDTradox7rOEH0x92HWcdL2pCmvKaRptTmvtbYlu6T8w+0dbq3nr8R9sfD5w0PFl5SvNUyWna6YLTk2fyz4ydlZ19fi753GDborZ752PO32oPb++6EHTh0kX/i+c7vDvOXPK4dPKy2+UTV7hXmq86X23qdOo8/pPTT8e7nLuarrlca7nuer21e2b36RueN87d9L158Rb/1tWeOT3dvfN6b/fF9/XfFt1+cif9zsu72Xcn7q28T7xf9EDtQdlD3YfVP1v+3Njv3H9qwHeg89HcR/cGhYPP/pH1jw9DBY+Zj8uGDYbrnjg+OTniP3L96fynQ89kzyaeF/6i/suuFxYvfvjV69fO0ZjRoZfyl5O/bXyl/erA6xmv28bCxh6+yXgzMV70VvvtwXfcdx3vo98PT+R8IH8o/2j5sfVT0Kf7kxmTk/8EA5jz/GMzLdsAAD+TaVRYdFhNTDpjb20uYWRvYmUueG1wAAAAAAA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/Pgo8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjYtYzEzOCA3OS4xNTk4MjQsIDIwMTYvMDkvMTQtMDE6MDk6MDEgICAgICAgICI+CiAgIDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+CiAgICAgIDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiCiAgICAgICAgICAgIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIKICAgICAgICAgICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICAgICAgICAgICB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIKICAgICAgICAgICAgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIKICAgICAgICAgICAgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiCiAgICAgICAgICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIKICAgICAgICAgICAgeG1sbnM6dGlmZj0iaHR0cDovL25zLmFkb2JlLmNvbS90aWZmLzEuMC8iCiAgICAgICAgICAgIHhtbG5zOmV4aWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vZXhpZi8xLjAvIj4KICAgICAgICAgPHhtcDpDcmVhdG9yVG9vbD5BZG9iZSBQaG90b3Nob3AgQ0MgMjAxNyAoV2luZG93cyk8L3htcDpDcmVhdG9yVG9vbD4KICAgICAgICAgPHhtcDpDcmVhdGVEYXRlPjIwMjEtMTEtMDVUMTE6MzA6NDgrMDE6MDA8L3htcDpDcmVhdGVEYXRlPgogICAgICAgICA8eG1wOk1ldGFkYXRhRGF0ZT4yMDIxLTExLTA1VDExOjMwOjU4KzAxOjAwPC94bXA6TWV0YWRhdGFEYXRlPgogICAgICAgICA8eG1wOk1vZGlmeURhdGU+MjAyMS0xMS0wNVQxMTozMDo1OCswMTowMDwveG1wOk1vZGlmeURhdGU+CiAgICAgICAgIDxkYzpmb3JtYXQ+aW1hZ2UvcG5nPC9kYzpmb3JtYXQ+CiAgICAgICAgIDx4bXBNTTpJbnN0YW5jZUlEPnhtcC5paWQ6N2I0MmEyODctNGFmZC1iYTQ4LWJhYzItY2RiYjQxOWU4MGQ5PC94bXBNTTpJbnN0YW5jZUlEPgogICAgICAgICA8eG1wTU06RG9jdW1lbnRJRD5hZG9iZTpkb2NpZDpwaG90b3Nob3A6NzI3OGJkZWMtM2UyMy0xMWVjLTgxZWEtOTNhOTBmMjdjNWQ3PC94bXBNTTpEb2N1bWVudElEPgogICAgICAgICA8eG1wTU06T3JpZ2luYWxEb2N1bWVudElEPnhtcC5kaWQ6ZGI4ODc4OGQtN2E1NC0xOTRiLTg1NmQtZDhhMWRiOWIxNjRiPC94bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ+CiAgICAgICAgIDx4bXBNTTpIaXN0b3J5PgogICAgICAgICAgICA8cmRmOlNlcT4KICAgICAgICAgICAgICAgPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+Y3JlYXRlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6aW5zdGFuY2VJRD54bXAuaWlkOmRiODg3ODhkLTdhNTQtMTk0Yi04NTZkLWQ4YTFkYjliMTY0Yjwvc3RFdnQ6aW5zdGFuY2VJRD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OndoZW4+MjAyMS0xMS0wNVQxMTozMDo0OCswMTowMDwvc3RFdnQ6d2hlbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OnNvZnR3YXJlQWdlbnQ+QWRvYmUgUGhvdG9zaG9wIENDIDIwMTcgKFdpbmRvd3MpPC9zdEV2dDpzb2Z0d2FyZUFnZW50PgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgICAgPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+c2F2ZWQ8L3N0RXZ0OmFjdGlvbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0Omluc3RhbmNlSUQ+eG1wLmlpZDo5NGY5NDU4NC1jYTcwLTBhNGEtOTI3NC02OGExY2Q3ZmUwY2I8L3N0RXZ0Omluc3RhbmNlSUQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDp3aGVuPjIwMjEtMTEtMDVUMTE6MzA6NTgrMDE6MDA8L3N0RXZ0OndoZW4+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDpzb2Z0d2FyZUFnZW50PkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE3IChXaW5kb3dzKTwvc3RFdnQ6c29mdHdhcmVBZ2VudD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmNoYW5nZWQ+Lzwvc3RFdnQ6Y2hhbmdlZD4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6YWN0aW9uPmNvbnZlcnRlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6cGFyYW1ldGVycz5mcm9tIGFwcGxpY2F0aW9uL3ZuZC5hZG9iZS5waG90b3Nob3AgdG8gaW1hZ2UvcG5nPC9zdEV2dDpwYXJhbWV0ZXJzPgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgICAgPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+ZGVyaXZlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6cGFyYW1ldGVycz5jb252ZXJ0ZWQgZnJvbSBhcHBsaWNhdGlvbi92bmQuYWRvYmUucGhvdG9zaG9wIHRvIGltYWdlL3BuZzwvc3RFdnQ6cGFyYW1ldGVycz4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6YWN0aW9uPnNhdmVkPC9zdEV2dDphY3Rpb24+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDppbnN0YW5jZUlEPnhtcC5paWQ6N2I0MmEyODctNGFmZC1iYTQ4LWJhYzItY2RiYjQxOWU4MGQ5PC9zdEV2dDppbnN0YW5jZUlEPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6d2hlbj4yMDIxLTExLTA1VDExOjMwOjU4KzAxOjAwPC9zdEV2dDp3aGVuPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6c29mdHdhcmVBZ2VudD5BZG9iZSBQaG90b3Nob3AgQ0MgMjAxNyAoV2luZG93cyk8L3N0RXZ0OnNvZnR3YXJlQWdlbnQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDpjaGFuZ2VkPi88L3N0RXZ0OmNoYW5nZWQ+CiAgICAgICAgICAgICAgIDwvcmRmOmxpPgogICAgICAgICAgICA8L3JkZjpTZXE+CiAgICAgICAgIDwveG1wTU06SGlzdG9yeT4KICAgICAgICAgPHhtcE1NOkRlcml2ZWRGcm9tIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgPHN0UmVmOmluc3RhbmNlSUQ+eG1wLmlpZDo5NGY5NDU4NC1jYTcwLTBhNGEtOTI3NC02OGExY2Q3ZmUwY2I8L3N0UmVmOmluc3RhbmNlSUQ+CiAgICAgICAgICAgIDxzdFJlZjpkb2N1bWVudElEPnhtcC5kaWQ6ZGI4ODc4OGQtN2E1NC0xOTRiLTg1NmQtZDhhMWRiOWIxNjRiPC9zdFJlZjpkb2N1bWVudElEPgogICAgICAgICAgICA8c3RSZWY6b3JpZ2luYWxEb2N1bWVudElEPnhtcC5kaWQ6ZGI4ODc4OGQtN2E1NC0xOTRiLTg1NmQtZDhhMWRiOWIxNjRiPC9zdFJlZjpvcmlnaW5hbERvY3VtZW50SUQ+CiAgICAgICAgIDwveG1wTU06RGVyaXZlZEZyb20+CiAgICAgICAgIDxwaG90b3Nob3A6Q29sb3JNb2RlPjM8L3Bob3Rvc2hvcDpDb2xvck1vZGU+CiAgICAgICAgIDxwaG90b3Nob3A6SUNDUHJvZmlsZT5zUkdCIElFQzYxOTY2LTIuMTwvcGhvdG9zaG9wOklDQ1Byb2ZpbGU+CiAgICAgICAgIDx0aWZmOk9yaWVudGF0aW9uPjE8L3RpZmY6T3JpZW50YXRpb24+CiAgICAgICAgIDx0aWZmOlhSZXNvbHV0aW9uPjcyMDAwMC8xMDAwMDwvdGlmZjpYUmVzb2x1dGlvbj4KICAgICAgICAgPHRpZmY6WVJlc29sdXRpb24+NzIwMDAwLzEwMDAwPC90aWZmOllSZXNvbHV0aW9uPgogICAgICAgICA8dGlmZjpSZXNvbHV0aW9uVW5pdD4yPC90aWZmOlJlc29sdXRpb25Vbml0PgogICAgICAgICA8ZXhpZjpDb2xvclNwYWNlPjE8L2V4aWY6Q29sb3JTcGFjZT4KICAgICAgICAgPGV4aWY6UGl4ZWxYRGltZW5zaW9uPjE5MjwvZXhpZjpQaXhlbFhEaW1lbnNpb24+CiAgICAgICAgIDxleGlmOlBpeGVsWURpbWVuc2lvbj4xOTI8L2V4aWY6UGl4ZWxZRGltZW5zaW9uPgogICAgICA8L3JkZjpEZXNjcmlwdGlvbj4KICAgPC9yZGY6UkRGPgo8L3g6eG1wbWV0YT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg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" type="image/x-icon" />
		<link rel="stylesheet" type="text/css" media="screen" href="libraries/jquery/core/core.min.css" />
		<script type="text/javascript" language="javascript" src="libraries/jquery/core/core.min.js"></script>
		<link href="libraries/FontAwesome/font-awesome.min.css" rel="stylesheet">
		<link rel="stylesheet" type="text/css" media="screen" href="style/install.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="style/jquery_tmpl.css" />
		<script type="text/javascript">
			//<![CDATA[
		$(function() {
			$("#language_drop").change(function(){
				$("#form_install").submit();
			});
			$("#progressbar").progressbar({
				value: ' . $progress . '
			});
			$(".done, .done2, #previous_step").click(function(){
				$("#back_"+$(this).attr("id")).removeAttr("disabled");
				$("#form_install").submit();
			});

			$("#form_install").on("submit", function(){
			    $("#content").append("<div class=\'preloader\'></div>")
			})

			' . ($_step->head_js ?? '') . '
		});
			//]]>
		</script>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>' . sprintf($this->lang['page_title'], VERSION_EXT) . '</title>
	</head>

	<body>
		<form action="index.php" method="post" id="form_install">
		<div id="outerWrapper">
			<div id="header">
				<div id="logo"></div>
				<div id="languageselect"><i class="fa fa-globe"></i> ' . $this->lang['language'] . ': ' . $this->lang_drop() . '</div>
				<div id="logotext">Installation ' . VERSION_EXT . '</div>
			</div>

		<div id="installer">
			<div id="steps">
				<ul class="steps">' . $this->gen_menu() . '</ul>
        <div id="progressbar"><span class="install_label">' . $progress . '%</span></div>
			</div>
			<div id="main">
				<div id="content">
					';
        if (count($this->log) > 0) {
            $error = "<br />";
            foreach ($this->log as $log) {
                $type = $log['type'];
                $error .= $this->$type($log['message'], $log['details'] ?? null);
            }
            $content .= '<h1 class="hicon home">' . $this->lang[$_POST['current_step']] . '</h1>' . $error;
        }

        $content .= '
					<h1 class="hicon home">' . (($this->current_step == 'licence') ? sprintf($this->lang['page_title'], VERSION_EXT) : $this->lang[$this->current_step]) . '</h1>
					' . $this->get_content() . '
					<div class="buttonbar">';

        if ($this->current_step == 'end') {
            $content .= '<br><a href="' . $protocol . '://' . $hostname . '/'. $pathname . '/admin/" target="_blank">To visit admin area click here</a><br><br>';
            /*if ($hostname != 'localhost') {
                $content .= '<iframe src="https://builtwith.com/' . $hostname . '/'. $pathname . '" width="100%" style="min-height: 380px;"></iframe><br><br> ';
            }*/
        }

        if ($this->previous != 'start' && $this->current_step != 'end' && $this->current_step != 'admin_user')
            $content .= '
						<button type="button" id="previous_step" class="prevstep">' . $this->lang['back'] . '</button>
						<input type="hidden" name="prev" value="' . $this->previous . '" id="back_previous_step" disabled="disabled" />';
        if (($_step->skippable ?? false))
            $content .= '
						<input type="submit" name="' . (($_step->parseskip ?? false) ? 'next' : 'skip') . '" value="' . $this->lang['skip'] . '" class="' . (($_step->parseskip) ? 'nextstep' : 'skipstep') . '" />';
        $content .= '
						<button type="submit" name="next" class="blue-btn" />' . $this->next_button() . '</button>
						<input type="hidden" name="current_step" value="' . $this->current_step . '" />
						<input type="hidden" name="install_done" value="' . implode(',', $this->done) . '" />
						<input type="hidden" name="step_data" value="' . base64_encode(serialize($this->data)) . '" />
					</div>
				</div>
			</div>
		</div>
		<div id="footer">
        Copyright &copy; 2000 - ' . date('Y', time()) . ' <a target="_blank" href="https://www.oscommerce.com">osCommerce Ltd</a> is provided under <a href="https://www.oscommerce.com/legal-policies" target="_blank">Terms and Conditions</a> and this <a href="https://www.oscommerce.com/legal-policies" target="_blank">Disclaimer</a><br>osCommerce the eCommerce Engine Copyright © 2000 – 2024 osCommerce Ltd<br>osCommerce ® is a Trademark owned by Holbi Group Ltd
		</div>
		</div>
		</form>
	</body>
</html>';

        echo $content;
    }

    public function htmlFromErrDetails($details)
    {
        if (empty($details)) {
            return '';
        }
        if (is_array($details)) {
            $tooltip = '';
            foreach ($details as $key=>$value) {
                $tooltip = "$key = $value\n";
            }
        } else {
            $tooltip = (string) $details;
        }
        return '<span title="'. $tooltip .'" style="cursor: help; font-family: Segoe UI, Segoe UI Emoji">&#x2139</span>';
    }

    public function install_error($log, $details = null) {
        return '<div class="infobox infobox-large infobox-red clearfix">
		<i class="fa fa-exclamation-triangle fa-4x pull-left"></i><span>' . $this->lang['error'] . '. ' . $log . $this->htmlFromErrDetails($details) .'</span>
	</div>';
    }

    public function install_warning($log, $details = null) {
        return '<div class="infobox infobox-large infobox-red clearfix">
			<i class="fa fa-exclamation-triangle fa-4x pull-left"></i><span>' . $this->lang['warning'] . '. ' . $log . $this->htmlFromErrDetails($details) . '</span>
		</div>';
    }

    public function install_success($log) {
        return '<div class="infobox infobox-large infobox-green clearfix">
		<i class="fa fa-check-circle" aria-hidden="true"></i><span>' . $this->lang['success'] . '. ' . $log . '</span>
	</div>';
    }

    public function translate_iso_langcode($isoCode) {
        $language_codes = array(
            'en' => 'English',
        );
        if (isset($language_codes[$isoCode])) {
            return mb_strtolower($str,  mb_detect_encoding($str));
//            return utf8_strtolower($language_codes[$isoCode]);
        } else {
            return "english";
        }
    }

}

abstract class install_generic {

    public static $before = 'start';
    public static $ajax = false;
    public $head_js = '';
    public $next_button = 'continue';
    public $skippable = false;
    public $parseskip = false;
    public $data = array();

    public function __construct() {
        global $install;
        $this->lang = $install->lang;
        $this->data = $install->data[get_class($this)];
        $this->root_path = $install->root_path;
    }

    public static function before() {
        return self::$before;
    }

    public static function ajax() {
        return self::$ajax;
    }

    public function log($type, $message, $details = null) {
        global $install;
        $install->log($type, $message, $details);
    }

    public function prepare_input($string) {
        $string = stripslashes($string);
        $string = preg_replace('/ +/', ' ', trim($string));
        $string = preg_replace("/[<>]/", '_', $string);
        return addslashes($string);
    }

    abstract public function get_output();

    abstract public function get_filled_output();

    abstract public function parse_input();
}
