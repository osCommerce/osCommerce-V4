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

  function tep_draw_calendar_jquery($html_field_name, $value='', $params = ''){
            $format = DATE_FORMAT;
            $s = preg_split("/[\d]/", date(DATE_FORMAT));
            $s = array_diff($s, array(''));
            $s = array_unique($s);
            sort($s);
            if (sizeof($s) == 1 ){
              $ex = explode($s[0], DATE_FORMAT);
              $_new_fomat = $_new_fomat_js = array();
              foreach($ex as $item){
                switch($item){
                  case 'F':case'm':case'M':case'n': $_new_fomat[] = 'm'; $_new_fomat_js[] = 'mm';break;
                  case 'd':case'D':case'j': case'l': case'N':  case'w': $_new_fomat[] = 'd';$_new_fomat_js[] = 'dd'; break;
                  case 'Y':case'y':case'o': $_new_fomat[] = 'Y'; $_new_fomat_js[] = 'yy'; break;
                  default: break;
                }
              }
              $_new_fomat_str = implode($s[0], $_new_fomat);
              $_new_fomat_str_js = implode($s[0], $_new_fomat_js);
              echo tep_draw_input_field($html_field_name, ($value == '0000-00-00' || is_null($value)? '':date($_new_fomat_str, strtotime($value)))). "\n";
              if($html_field_name[0] == '.' || $html_field_name[0] == '#'){
                $field = $html_field_name;
              } else {
                $field = 'input[name="'.$html_field_name.'"]';
              }
              ?>
              <script>
                $(document).ready(function(){
                  $('<?=$field?>').datepicker({
                    dateFormat: '<?=$_new_fomat_str_js?>',
                    changeMonth: true,
                    changeYear: true,
                    minDate :0,
                    <?=$params;?>
                  });
                  //$('<?=$field?>').attr('readonly',true);
                })
              </script>
              <?php
              echo '<noscript>'.tep_draw_input_field( $html_field_name, $ctl_value,' size="12"').'</noscript>';
            }
  }
