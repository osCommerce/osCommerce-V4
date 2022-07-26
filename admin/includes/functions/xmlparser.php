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

   class xml_node
   {
     var $parent_node;
     var $name;
     var $attributes;
     var $content;
     var $depth;
     var $subitems;

     function __construct()
     {
       $this->parent_node = null;
       $this->subitems = Array();
     }

     function itemcount($name_filter = "", $recursive = TRUE) //Gibt die Anzahl der Unteritems zurück, es kann nach bestimmten Tags gefiltert werden.
     {
       $count = 0;
       $name_filter = trim($name_filter);

       for ($i=0;$i<count($this->subitems);$i++)
       {
         if (($name_filter == "") || ($this->subitems[$i]->name == $name_filter))
           $count++;

         if ($recursive == TRUE)
           $count += $this->subitems[$i]->itemcount($recursive, $name_filter);
       }

       return $count;
     }

     function path_itemcount($path, $name_filter = "", $recursive = TRUE)
     {
       $count = 0;
       $node = &$this->item($path);
       if ($node != NULL)
         $count = $node->itemcount($name_filter,$recursive);
       return $count;
     }

     function &item($name, $offset = 0) //Versucht ein Item zu finden und gibt es zurück, bei mehreren Items auf der gleichen Ebene kann mit dem Offset gearbeitet werden
     {                                  //Es kann auch ein Pfad übergeben werden, Syntax: item1/item2 - Der Offset bezieht sich dann auf die Elemente des letzten Elements
                                        //Weiterhin kann auch nach einem bestimmten Attribut gefiltert werden Syntax item1/item2.attribut=wert/item3
       $node = null;
       $counter = 0;
       $name = trim($name);

       $pos = strpos($name, "/");
       if ($pos !== FALSE)
       {
         $subpath = substr($name, $pos+1);
         $name = substr($name, 0, $pos);
       }

       //Filtern nach einem evtl. vorhandenem Attribut
       $pos_attr = strpos($name, ".");
       if ($pos_attr !== FALSE)
       {
         $attr_filter = substr($name, $pos_attr+1);
         $name = substr($name, 0, $pos_attr);

         //Filter String weiter aufteilen
         $pos_filter = strpos($attr_filter, "=");
         $value = substr($attr_filter, $pos_filter+1);
         $attrib = substr($attr_filter, 0, $pos_filter);
       }

       for ($i=0;$i<count($this->subitems);$i++)
         if ($name == $this->subitems[$i]->name)
         {
           if (($pos_attr === FALSE) || ($this->subitems[$i]->attributes[$attrib] == $value)) //Filter nach Attribut
           {
             if (($counter == $offset) || ($pos != FALSE)) //Wenn ein Subpfad gefunden wurde, dann reiche Offset weiter...
             {
               $node = &$this->subitems[$i];
               if ($pos !== FALSE) //Wenn Pfadangabe gefunden, dann suche mit dem Rest vom Pfad im Unterobjekt
                 $node = &$node->item($subpath, $offset);
               break; //Item gefunden, Schleife verlassen...
             }
             else
               $counter++;
           }
         }

       return $node;
     }

     function print_structure()  //Gibt die XML Daten aus - zum Debuggen geeignet
     {
       for ($i=0;$i<count($this->subitems);$i++)
       {
         $prefix = str_repeat(".&nbsp;.&nbsp;",$this->subitems[$i]->depth-1);
         echo $prefix."<b>".$this->subitems[$i]->name."</b>: ".$this->subitems[$i]->content."<br>";

         $ausgabe = "";
         while (list ($key, $val) = each ($this->subitems[$i]->attributes))
           $ausgabe.="$key = '$val', ";
         $ausgabe = substr($ausgabe,0,strlen($ausgabe)-2);

         if (strlen($ausgabe) > 2)
           echo "$prefix Attributes: ($ausgabe)<br>";

         $this->subitems[$i]->print_structure();
       }
     }
     
     function get_structure(&$result)  //Gibt die XML Daten aus - zum Debuggen geeignet
     {
       //$result = array();
       if (!is_array($result)){
         $result = array();
       }
       $cur_level = $this->depth;
       for ($i=0;$i<count($this->subitems);$i++)
       {
         //$prefix = str_repeat(".&nbsp;.&nbsp;",$this->subitems[$i]->depth-1);
         //echo $prefix."<b>".$this->subitems[$i]->name."</b>: ".$this->subitems[$i]->content."<br>";
         $result[$this->subitems[$i]->name] = array();
         if (trim($this->subitems[$i]->content) != ''){
            $result[$this->subitems[$i]->name]['text'] = $this->subitems[$i]->content;
         }

         $this->subitems[$i]->get_structure($result[$this->subitems[$i]->name]);
       }
     }
   }

   //XML-Tree Klasse, die aus einer XML Datei die Baumstruktur erzeugt
   class xml_tree
   {
     var $parser;
     var $active_node;
     var $encoding;

     function __construct($encoding = '')
     {
       $this->encoding = $encoding;
     }

     function &_createNode($name)  //Erzeugt eine Neue Node - Kann später überschrieben werden, um Spezial Nodes zu erzeugen
     {
       //Standardmäßig wird ein xml_node Objekt erzeugt
       return new xml_node();
     }

     function _startElement($parser, $name, $attrs)   //Starttag
     {
       /*
          Neues Element gefunden:
          1. Neues Node Objekt erzeugen und Werte zuweisen
          2. Objekt als Subitem des Active Node einsetzen, Active Node als Parent des Neuen Objektes eintragen
          3. Neues Objekt als Active Node festlegen
       */
       //1.
       /*
       if ($name != ''){
          $this->active_node[$name]
       }
       */
       
       $node = &$this->_createNode($name);
       $node->name = $name;
       $node->attributes = $attrs;
       $node->depth = $this->active_node->depth + 1;

       //2.
       $node->parent_node = &$this->active_node;
       $this->active_node->subitems[] = &$node;
       
       //3.
       $this->active_node = &$node;
     }

     function _endElement($parser, $name)             //Endtag
     {
       /*
          Elementende:
          Parent des Aktiven Elements wieder zum Aktiven Element machen
       */
       $this->active_node = &$this->active_node->parent_node;
     }

     function _characterData($parser, $data)          //Mittelstück
     {
       /*
          Mittelstück:
          Inhalt zum aktiven Knoten hinzufügen:
       */

       $this->active_node->content.= $data;
     }

     function &createTree($xml_data)                   //Erzeugt einen Baum und gibt das Root Objekt als Referenz zurück
     {
       $this->parser = xml_parser_create($this->encoding);

       xml_set_object($this->parser, $this);
       xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
       xml_set_element_handler($this->parser, "_startElement", "_endElement");
       xml_set_character_data_handler($this->parser, "_characterData");

       //Erzeuge Root Objekt und setze aktive Node darauf:
       //$root = array();
       $root = new xml_node();
       $this->active_node = &$root;

       //Parse Dokument:
       if (!xml_parse($this->parser, $xml_data, sizeof($xml_data)))
         unset($root);


       xml_parser_free($this->parser);

       return $root;
     }
   }

