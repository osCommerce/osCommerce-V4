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

function getmicrotime(){
  list($usec, $sec) = explode(" ",microtime());
  return ((float)$usec + (float)$sec);
}


function tep_get_db_fields($table_name, $except = array()) {
  $result = array();
  if (!is_array($except)) $except = array();
  $fields_query = tep_db_query("show columns from ".$table_name);
  while($row = tep_db_fetch_array($fields_query)) {
    if (!in_array($row["Field"],$except)) {
      $result[] = $row["Field"];
    }
  }

  return $result;
}


function tep_prepare_xml_value($value) {
  //     $value = preg_replace("'&#(\d+);'e","chr(\\1)",$value);
  //     $value = preg_replace("'&#(\d+)'e","chr(\\1)",$value);
  //  $value = htmlspecialchars($value);
  $value = "<![CDATA[".$value."]]>";

  return $value;

}

function tep_add_xml_structure($current_table,$structure,$languages,$nested,$type,$condition = "", $skiplangs = false) {
  global $xml, $notifications, $table_counts;

  $language_mark = "language_id";
  $language_mark_ = "languages_id";
  $table_fiels_xml = "last_xml_export";
  $lng_mark = '';



  if ((in_array($language_mark,$structure) || in_array($language_mark_,$structure))&&(!$skiplangs)) {

    if (in_array($language_mark,$structure)) {
      $lng_mark = $language_mark;
    }

    if (in_array($language_mark_,$structure)) {
      $lng_mark = $language_mark_;
    }

    if ($type=="main") {
      $parent_data = tep_db_query("select distinct(".$structure[0].") from ".$current_table. " where 1=1 ".$condition);
      $table_counts[$current_table] += tep_db_num_rows($parent_data);
      while ($parent_row = tep_db_fetch_array($parent_data))  {
        if (in_array($table_fiels_xml,$structure)) {
          tep_db_query("update $current_table set ".$table_fiels_xml."=now() where ".$structure[0]."=".(int)$parent_row[$structure[0]]);
        }
        $link_flag = " and ".$structure[0]."=".$parent_row[$structure[0]];
        $xml[] = '<'.$current_table.'>';
        for ($j=0;$j<sizeof($structure);$j++) {
          $xml[] = '<'.$structure[$j].'>';
          for ($z=0;$z<sizeof($languages);$z++) {
            $xml[] = '<'.$languages[$z]["code"].'>';
            $inner_fetch = tep_db_fetch_array(tep_db_query("select ".$structure[$j]." from ".$current_table." where ".$structure[0]."=".(int)$parent_row[$structure[0]]." and ".$lng_mark."=".(int)$languages[$z]["languages_id"]));
            if ($table_fiels_xml == $structure[$j]) {
              $inner_fetch[$structure[$j]] = date("Y-m-d H:i:s");
            }
            $xml[] = tep_prepare_xml_value($inner_fetch[$structure[$j]]);
            $xml[] = '</'.$languages[$z]["code"].'>';
          }
          $xml[] = '</'.$structure[$j].'>';
        }
        for ($k=0;$k<sizeof($nested);$k++) {
          if (tep_not_null($nested[$k]["parent"])) {
            if (is_array($nested[$k]["nested"]) && sizeof($nested[$k]["nested"])>0) {
              $nested_array = $nested[$k]["nested"];
            } else {
              $nested_array = array();
            }
            tep_add_xml_structure($nested[$k]["parent"],tep_get_db_fields($nested[$k]["parent"]),$languages,$nested_array,"slave",$link_flag);
          }
        }

        $xml[] = '</'.$current_table.'>';
      }
    } else {

      $xml[] = '<'.$current_table.'>';
      for ($z=0;$z<sizeof($languages);$z++) {
        $xml[] = '<'.$languages[$z]["code"].'>';
        $parent_data = tep_db_query("select ".join(",",$structure)." from ".$current_table." where ".$lng_mark."=".(int)$languages[$z]["languages_id"]." ".$condition);
        $table_counts[$current_table] += tep_db_num_rows($parent_data);
        while ($parent_row = tep_db_fetch_array($parent_data))  {
          if (in_array($table_fiels_xml,$structure)) {
            tep_db_query("update $current_table set ".$table_fiels_xml."=now() where ".$structure[0]."=".(int)$parent_row[$structure[0]]." and ".$lng_mark."=".(int)$languages[$z]["languages_id"]);
            $parent_row[$table_fiels_xml] = date("Y-m-d H:i:s");
          }


          for ($j=0;$j<sizeof($structure);$j++) {
            if (tep_not_null($parent_row[$structure[$j]])) {
              $xml[] = '<'.$structure[$j].'>'.tep_prepare_xml_value($parent_row[$structure[$j]]).'</'.$structure[$j].'>';
            }
          }
          $link_flag = " and ".$structure[0]."=".$parent_row[$structure[0]];
          for ($k=0;$k<sizeof($nested);$k++) {
            if (tep_not_null($nested[$k]["parent"])) {
              if (is_array($nested[$k]["nested"]) && sizeof($nested[$k]["nested"])>0) {
                $nested_array = $nested[$k]["nested"];
              } else {
                $nested_array = array();
              }
              tep_add_xml_structure($nested[$k]["parent"],tep_get_db_fields($nested[$k]["parent"]),$languages,$nested_array,"slave",$link_flag);
            }
          }


        }
        $xml[] = '</'.$languages[$z]["code"].'>';
      }
      $xml[] = '</'.$current_table.'>';
    }
  } else {

    if ($type=="main") {
      $parent_data = tep_db_query("select ".join(",",$structure)." from ".$current_table. " where 1=1 ".$condition);
      $table_counts[$current_table] += tep_db_num_rows($parent_data);
      while ($parent_row = tep_db_fetch_array($parent_data))  {
        if (in_array($table_fiels_xml,$structure)) {
          tep_db_query("update $current_table set ".$table_fiels_xml."=now() where ".$structure[0]."=".(int)$parent_row[$structure[0]]);
          $parent_row[$table_fiels_xml] = date("Y-m-d H:i:s");
        }

        $xml[] = '<'.$current_table.'>';
        for ($j=0;$j<sizeof($structure);$j++) {
          if (tep_not_null($parent_row[$structure[$j]])) {
            $xml[] = '<'.$structure[$j].'>'.tep_prepare_xml_value($parent_row[$structure[$j]]).'</'.$structure[$j].'>';
          }
        }
        $link_flag = " and ".$structure[0]."=".$parent_row[$structure[0]];
        for ($k=0;$k<sizeof($nested);$k++) {
          if (tep_not_null($nested[$k]["parent"])) {
            if (is_array($nested[$k]["nested"]) && sizeof($nested[$k]["nested"])>0) {
              $nested_array = $nested[$k]["nested"];
            } else {
              $nested_array = array();
            }
            if (tep_not_null($nested[$k]["field"])){
              tep_add_xml_structure($nested[$k]["parent"],tep_get_db_fields($nested[$k]["parent"]),$languages,$nested_array,"slave"," and ".$nested[$k]["field"]."=".$parent_row[$structure[0]]);
            }else{
              tep_add_xml_structure($nested[$k]["parent"],tep_get_db_fields($nested[$k]["parent"]),$languages,$nested_array,"slave",$link_flag);
            }
          }
        }

        $xml[] = '</'.$current_table.'>';
      }
    } else {
      $parent_data = tep_db_query("select ".join(",",$structure)." from ".$current_table." where 1=1 ".$condition);
      $table_counts[$current_table] += tep_db_num_rows($parent_data);

      while ($parent_row = tep_db_fetch_array($parent_data))  {
        if (in_array($table_fiels_xml,$structure)) {
          tep_db_query("update $current_table set ".$table_fiels_xml."=now() where ".$structure[0]."=".(int)$parent_row[$structure[0]]);
          $parent_row[$table_fiels_xml] = date("Y-m-d H:i:s");
        }

        $xml[] = '<'.$current_table.'>';
        for ($j=0;$j<sizeof($structure);$j++) {
          if (tep_not_null($parent_row[$structure[$j]])) {
            $xml[] = '<'.$structure[$j].'>'.tep_prepare_xml_value($parent_row[$structure[$j]]).'</'.$structure[$j].'>';
          }
        }
        $link_flag = " and ".$structure[0]."=".$parent_row[$structure[0]];
        for ($k=0;$k<sizeof($nested);$k++) {
          if (tep_not_null($nested[$k]["parent"])) {
            if (is_array($nested[$k]["nested"]) && sizeof($nested[$k]["nested"])>0) {
              $nested_array = $nested[$k]["nested"];
            } else {
              $nested_array = array();
            }
            tep_add_xml_structure($nested[$k]["parent"],tep_get_db_fields($nested[$k]["parent"]),$languages,$nested_array,"slave",$link_flag);
          }
        }

        $xml[] = '</'.$current_table.'>';
      }

    }
  }



}

function tep_make_xml($directory, $datatype, $languages, $dbtables, $extension = ".xml") {
  global $xml, $notifications, $table_counts;
  $_sep = chr(10); //lines separator
  $notifications = array(); //information_array
  $table_counts = array(); //information_array

  $xml_filename = $directory . "/" . $datatype . $extension;
  $xml_file = @fopen($xml_filename, "w");
  $xml = array();
  $xml[] = '<?xml version="1.0" encoding="utf-8"?>';
  $time1 = getmicrotime();
  $notifications[] = sprintf(TEXT_STARTING_DUMP,$datatype,date(PHP_DATE_TIME_FORMAT));
  $xml[] = '<'.$datatype.'_set>';

  $db_structures = array();

  for ($i=0; $i<sizeof($dbtables); $i++) {
    $excludes = array();
    $db_structures[$dbtables[$i]["parent"]]  = tep_get_db_fields($dbtables[$i]["parent"],$excludes);

    $current_table = $dbtables[$i]["parent"];

    tep_add_xml_structure($current_table, $db_structures[$current_table], $languages, $dbtables[$i]["nested"], "main",$dbtables[$i]["condition"],$dbtables[$i]["skiplangs"]);


  }


  $xml[] = '</'.$datatype.'_set>';
  @fwrite($xml_file,join($_sep,$xml));
  @fclose($xml_file);
  @chmod($xml_filename,0777);


  foreach ($table_counts as $key => $val) {
    $notifications[] = sprintf(TEXT_TABLE_BACKUP,$key);
    $notifications[] = sprintf(TEXT_TABLE_BACKUP_RECORD,$val,$key);
  }
  $notifications[] = sprintf(TEXT_FINISHED_DUMP,$datatype,date(PHP_DATE_TIME_FORMAT));
  $time2 = getmicrotime();

  $notifications[] = sprintf(TEXT_TOTAL_BACKUP_TIME,round(($time2-$time1),4));

  return $notifications;
}


function tep_create_xml_query_list($datas, $parent_db_fields,$main_container, $mtype = "standard") {
  global $notifications, $languages;

  $last_import_flag = "last_xml_import";


  if ($mtype == "langs") {
    for ($p=0;$p<sizeof($languages);$p++){
      $query = "replace into ".$main_container." set ";
      $multiquery = "replace into ".$main_container." set ";
      $parent_table = true;
      $multiqueries = array();

      foreach ($datas[$languages[$p]["code"]] as $key => $val) {
        if (in_array($key,$parent_db_fields)&&$parent_table) {
          if (is_array($val)&&sizeof($val)>1) {
            for ($z=0;$z<sizeof($languages);$z++) {
              $multiqueries[$languages[$z]["code"]] .= $key . "='" . addslashes(trim($val[$languages[$z]["code"]]["text"])) . "', ";
            }
          } else {
            $query .= $key . "='".addslashes(trim($val["text"]))."', ";
          }
        } else {
          if ($key!="text")  {
            $container_new = $val;
            $structure_new = tep_get_db_fields($key);

            $is_lang = false;
            $is_simple = false;
            $is_multic = false;
            for ($c=0;$c<sizeof($languages);$c++) {
              if (is_array($container_new[$languages[$c]["code"]])) {
                $is_lang = true;
              }
            }

            if (!$is_lang) {
              for ($c=0;$c<sizeof($container_new);$c++) {
                if (is_array($container_new[$c])) $is_multic = true;
              }
            }


            if ($is_lang) {
              tep_create_xml_query_list($container_new,$structure_new,$key,"langs");
            } else {
              if ($is_multic) {
                tep_create_xml_query_list($container_new,$structure_new,$key,"standard");
              } else {
                tep_create_xml_query_list($container_new,$structure_new,$key,"single");
              }

            }
            $parent_table = false;
          }
        }

      }

      if (sizeof($multiqueries) > 0) {
        foreach ($multiqueries as $key => $val) {
          if (in_array($last_import_flag,$parent_db_fields)) {
            $notifications[] = $multiquery.$val.$last_import_flag."=now()";
          } else {
            $notifications[] = $multiquery.substr($val,0,strlen($val)-2);
          }
        }

      } else {
        if ($query != "replace into ".$main_container." set ") {
          if (in_array($last_import_flag,$parent_db_fields)) {
            $notifications[] = $query.$last_import_flag."=now()";
          } else {
            $notifications[] = substr($query,0,strlen($query)-2);
          }
        }
      }
    }



  }




  if ($mtype == "single") {
    $query = "replace into ".$main_container." set ";
    $multiquery = "replace into ".$main_container." set ";
    $parent_table = true;
    $multiqueries = array();
    foreach ($datas as $key => $val) {
      if (in_array($key,$parent_db_fields)&&$parent_table) {
        if (is_array($val)&&sizeof($val)>1) {
          for ($z=0;$z<sizeof($languages);$z++) {
            $multiqueries[$languages[$z]["code"]] .= $key . "='" . addslashes(trim($val[$languages[$z]["code"]]["text"])) . "', ";
          }
        } else {
          $query .= $key . "='".addslashes(trim($val["text"]))."', ";
        }
      } else {
        $container_new = $val;
        $structure_new = tep_get_db_fields($key);

        $is_lang = false;
        $is_simple = false;
        $is_multic = false;
        for ($c=0;$c<sizeof($languages);$c++) {
          if (is_array($container_new[$languages[$c]["code"]])) {
            $is_lang = true;
          }
        }

        if (!$is_lang) {
          for ($c=0;$c<sizeof($container_new);$c++) {
            if (is_array($container_new[$c])) $is_multic = true;
          }
        }


        if ($is_lang) {
          tep_create_xml_query_list($container_new,$structure_new,$key,"langs");
        } else {
          if ($is_multic) {
            tep_create_xml_query_list($container_new,$structure_new,$key,"standard");
          } else {
            tep_create_xml_query_list($container_new,$structure_new,$key,"single");
          }

        }
        $parent_table = false;
      }

    }

    if (sizeof($multiqueries) > 0) {
      foreach ($multiqueries as $key => $val) {
        if (in_array($last_import_flag,$parent_db_fields)) {
          $notifications[] = $multiquery.$val.$last_import_flag."=now()";
        } else {
          $notifications[] = $multiquery.substr($val,0,strlen($val)-2);
        }
      }

    } else {
      if (in_array($last_import_flag,$parent_db_fields)) {
        $notifications[] = $query.$last_import_flag."=now()";
      } else {
        $notifications[] = substr($query,0,strlen($query)-2);
      }
    }

  }

  if ($mtype == "standard") {
    for ($j=0;$j<sizeof($datas);$j++) {

      $query = "replace into ".$main_container." set ";
      $multiquery = "replace into ".$main_container." set ";
      $parent_table = true;
      $multiqueries = array();


      foreach ($datas[$j] as $key => $val) {
        if (in_array($key,$parent_db_fields)&&$parent_table) {
          if (is_array($val)&&sizeof($val)>1) {
            for ($z=0;$z<sizeof($languages);$z++) {
              $multiqueries[$languages[$z]["code"]] .= $key . "='" . addslashes(trim($val[$languages[$z]["code"]]["text"])) . "', ";
            }
          } else {
            if ($key != $last_import_flag){
              $query .= $key . "='".addslashes(trim($val["text"]))."', ";
            }
          }
        } else {
          $container_new = $val;
          $structure_new = tep_get_db_fields($key);

          $is_lang = false;
          $is_simple = false;
          $is_multic = false;
          for ($c=0;$c<sizeof($languages);$c++) {
            if (is_array($container_new[$languages[$c]["code"]])) {
              $is_lang = true;
            }
          }

          if (!$is_lang) {
            for ($c=0;$c<sizeof($container_new);$c++) {
              if (is_array($container_new[$c])) $is_multic = true;
            }
          }


          if ($is_lang) {
            tep_create_xml_query_list($container_new,$structure_new,$key,"langs");
          } else {
            if ($is_multic) {
              tep_create_xml_query_list($container_new,$structure_new,$key,"standard");
            } else {
              tep_create_xml_query_list($container_new,$structure_new,$key,"single");
            }

          }
          $parent_table = false;
        }

      }

      if (sizeof($multiqueries) > 0) {
        foreach ($multiqueries as $key => $val) {
          if (in_array($last_import_flag,$parent_db_fields)) {
            $notifications[] = $multiquery.$val.$last_import_flag."=now()";
          } else {
            $notifications[] = $multiquery.substr($val,0,strlen($val)-2);
          }
        }

      } else {
        if (in_array($last_import_flag,$parent_db_fields)) {
          $notifications[] = $query.$last_import_flag."=now()";
        } else {
          $notifications[] = substr($query,0,strlen($query)-2);
        }
      }


    }


  }

}

function tep_restore_xml_backup($directory,$files,$languages, $ext = ".xml") {
  global $notifications, $languages;
  $notifications = array();

  $last_import_flag = "last_xml_import";

  for ($i=0;$i<sizeof($files);$i++) {
    //$xml = new xml2array(DIR_FS_CATALOG_XML.$directory."/".$files[$i]);

    if ( file_exists(DIR_FS_CATALOG_XML.$directory."/".$files[$i]) )
      $contents = file_get_contents( DIR_FS_CATALOG_XML.$directory."/".$files[$i] );
    $xml_tree = new xml_tree();
    $root_node = $xml_tree->createTree($contents);
    $dataset = array();
    $root_node->get_structure($dataset);

    $main_container = str_replace($ext,"",$files[$i]);
/*    
    $dataset = $xml->getResult();
*/
    $datas  = $dataset[$main_container."_set"][$main_container];
    
    $parent_db_fields = tep_get_db_fields($main_container);

    for ($j=0;$j<sizeof($datas);$j++) {
      if (!is_array($datas[$j])) {
        $d_t = $datas;
        $datas = array();
        $datas[] = $d_t;
      }
    }
    tep_create_xml_query_list($datas,$parent_db_fields,$main_container,"standard");
  }
  return $notifications;

}


function tep_check_xml_structure($files,$directory) {
  $ok = true;
  for ($i=0;$i<sizeof($files);$i++) {
    $xml = file_get_contents(DIR_FS_CATALOG_XML.$directory."/".$files[$i]);
    //$xml = @domxml_open_mem($xml);
    $xml_parser = xml_parser_create();
    $data = xml_parse($xml_parser,$xml);
    if (!$data){
      $ok = false;
    }
    /*
    if (!is_object($xml)) {
    $ok = false;
    }
    */

  }


  return $ok;
}
