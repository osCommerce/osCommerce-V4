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

namespace common\classes;

/**
 * Wristband object
 */
class Wrist
{
    /**
     * Data collection for object
     * @var array  $data
     */
    public $data = [];

    /**
     * Color keys setting
     * @var array 
     */
    public $colour = [
        1 => 'white',
        2 => 'purple',
        3 => 'neon-pink',
        4 => 'red',
        5 => 'neon-orange',
        6 => 'yellow',
        7 => 'neon-yellow',
        8 => 'aqua',
        9 => 'neon-green',
        10 => 'dark-green',
        11 => 'blue',
        12 => 'sky-blue',
        13 => 'gold',
        14 => 'silver',
    ];
    
    /**
     * Color names
     * @var array 
     */
    public $colourNames = [
        1 => 'White',
        2 => 'Purple',
        3 => 'Neon Pink',
        4 => 'Red',
        5 => 'Neon Orange',
        6 => 'Yellow',
        7 => 'Neon Yellow',
        8 => 'Aqua',
        9 => 'Neon Green',
        10 => 'Dark Green',
        11 => 'Blue',
        12 => 'Sky Blue',
        13 => 'Gold',
        14 => 'Silver',
    ];
    
    /**
     * Where to store the data
     * possible values: FILE or DB
     */
    const STORAGE_TYPE = 'FILE';
    
    /**
     * Dots per inch
     */
    const DEFAULT_DPI = 96;//72
    public $DPI = 300;
    
    /**
     * Data identifier
     * @var string or numeric 
     */
    public $identifier = null;
    
    /**
     * Constructor
     * @param string or numeric $identifier
     */
    public function __construct($identifier = null) 
    {
        $this->identifier = $identifier;
    }

    /**
     * Get value from $data array
     * @param type $key
     * @return boolean
     */
    /*public function get($key) {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return false;
    }*/

    /**
     * Set value to $data array
     * @param type $key
     * @param type $value
     */
    /*public function set($key, $value) {
        $this->data[$key] = $value;
    }*/
    
    private function checkPossibleValues($lookup_value, $lookup_array) 
    {
        if (in_array($lookup_value, $lookup_array)) {
            return $lookup_value;
        }
        return current($lookup_array);
    }

        /**
     * Load params from array
     * @param array $params
     */
    public function post($params = array())
    {
        $this->data['options']['version'] = $this->checkPossibleValues($params['version'], ['simple', 'advanced']);
        $this->data['options']['material'] = $this->checkPossibleValues($params['material'], ['paper', 'vinil']);
        $this->data['options']['content'] = $this->checkPossibleValues($params['content'], ['custom', 'plain']);
        
        //if ($this->data['options']['material'] == 'vinil') {
        //    $this->data['settings']['paper-settings']['size'] = 19;
        //} else {
            $this->data['settings']['paper-settings']['size'] = $this->checkPossibleValues($params['size'], [25, 19]);
        //}
        $this->data['settings']['paper-settings']['colour'] = [];
        if (isset($this->colour[$params['colour']])) {
            $this->data['settings']['paper-settings']['colour'][$this->colour[$params['colour']]] = 10;//qty of copies
        }
        
        if ($this->data['options']['content'] == 'custom') {
            $this->data['settings']['text-settings']['use-text'] = $this->checkPossibleValues($params['use-text'], ['no', 'yes']);
            if ($this->data['settings']['text-settings']['use-text'] == 'yes') {
                $this->data['settings']['text-settings']['text'] = [
                    'font' => $params['text_font_1'],
                    'size' => $params['text_size_1'],
                    'content' => $params['text_line_1'],
                ];
                $this->data['settings']['text-settings']['text']['use-second-line'] = $this->checkPossibleValues($params['use-second-line'], ['no', 'yes']);
                if ($this->data['settings']['text-settings']['text']['use-second-line'] == 'yes') {
                    $this->data['settings']['text-settings']['text']['second-line'] = [
                        'font' => $params['text_font_2'],
                        'size' => $params['text_size_2'],
                        'content' => $params['text_line_2'],
                    ];
                }
            }
        
            $this->data['settings']['logo-settings']['type'] = $this->checkPossibleValues($params['use_logo'], ['email', 'artwork', 'upload']);
            if ($this->data['settings']['logo-settings']['type'] == 'artwork') {
                $this->data['settings']['logo-settings']['artwork'] = [
                    'position' => $this->checkPossibleValues($params['select-position'], ['left', 'right', 'both']),
                    'filename' => $params['art_filename'],
                ];
            }
            if ($this->data['settings']['logo-settings']['type'] == 'upload') {
                $this->data['settings']['logo-settings']['upload'] = [
                    'position' => $this->checkPossibleValues($params['select-position-upload'], ['left', 'right', 'both']),
                    'filename' => $params['upload_filename'],
                ];
            }
        }
        
        $this->data['settings']['branding-settings']['remove-branding'] = $this->checkPossibleValues($params['remove-branding'], ['no', 'yes']);
        
        /*foreach($params as $key => $value) {
            $this->set($key, $value);
        }*/
    }
    
    private function parseSimpleXML($xmldata)
    {
        $childNames = array();
        $children = array();

        if( count($xmldata) !== 0 ) {
            foreach( $xmldata->children() AS $child ) {
                $name = $child->getName();
                if( !isset($childNames[$name]) ) {
                    $childNames[$name] = 0;
                }
                $childNames[$name]++;
                $children[$name][] = $this->parseSimpleXML($child);
            }
        }
        $returndata = array();
        if( count($childNames) > 0 ) {
            foreach( $childNames AS $name => $count ) {
                if( $count === 1 ) {
                    $returndata[$name] = $children[$name][0];
                } else {
                    $returndata[$name] = array();
                    $counter = 0;
                    foreach( $children[$name] AS $data ) {
                        $returndata[$name][$counter] = $data;
                        $counter++;
                    }
                }
            }
        } else {
            $xmldata= iconv("utf-8", CHARSET, $xmldata);
            $returndata = (string)$xmldata;
        }
        return $returndata;
    }
    /**
     * Load params
     */
    public function load($identifier = null) 
    {
        if ($identifier != null) {
            $this->identifier = $identifier;
        }
        
        if ($this->identifier == null) {
            return false;
        }
        if (self::STORAGE_TYPE == 'FILE') {
            $filename = DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $this->identifier;
            if (file_exists($filename)) {
                $xmlstring = file_get_contents($filename);
                $xml = simplexml_load_string($xmlstring);
                $this->data = $this->parseSimpleXML($xml);
            }
        } elseif (self::STORAGE_TYPE == 'DB') {
            
        }
        return false;
    }
    
    /**
     * Save params
     */
    public function save($identifier = null) 
    {
        if ($identifier != null) {
            $this->identifier = $identifier;
        }
        
        $xml = $this->toXML();
        
        if ($this->identifier == null) {
            //add
            if (self::STORAGE_TYPE == 'FILE') {
                if (!file_exists(DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR)) {
                    mkdir(DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR, 0777, true);
                }
                $filename = DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'config.xml';
                
                $fp = fopen($filename, "w");
                fputs($fp, $xml);
                fclose ($fp);
            } elseif (self::STORAGE_TYPE == 'DB') {
                
            } else {
                return false;
            }
        } else {
            //update
            if (self::STORAGE_TYPE == 'FILE') {
                if (!file_exists(DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR)) {
                    mkdir(DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR, 0777, true);
                }
                $filename = DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $this->identifier;
                
                $fp = fopen($filename, "w");
                fputs($fp, $xml);
                fclose ($fp);
            } elseif (self::STORAGE_TYPE == 'DB') {
                
            } else {
                return false;
            }
        }
        
    }
    
    private function scaleDPI($size)
    {
        return (int) ($size * $this->DPI);
    }
    
    private function scalePPI($px)
    {
        return (int) ($px * $this->DPI / self::DEFAULT_DPI);
    }

    private function getHeight() {
        if ($this->data['settings']['paper-settings']['size'] == '25') {
            $width = $this->scaleDPI(1);
        } else {
            $width = $this->scaleDPI(3/4);
        }
        return $width;
    }
    
    private function getWidth() {
        if ($this->data['options']['material'] == 'vinil') {
            $height = $this->scaleDPI(9.25);
        } else {
            $height = $this->scaleDPI(7.5625);
        }
        return $height;
        
    }
    
    public function createImage($returnObject = false, $defaultDPI = true)
    {
        if ($defaultDPI) {
            $this->DPI = self::DEFAULT_DPI;
        }
        
        $imagine = new \Imagine\Gd\Imagine();
        
        $size  = new \Imagine\Image\Box($this->getWidth(), $this->getHeight());
        $color = new \Imagine\Image\Color('#000', 100);
        $image = $imagine->create($size, $color);
        
        
        
        if ($this->data['options']['content'] == 'custom') {
            
            $x = $this->scalePPI(57);
            
            $logoType = $this->data['settings']['logo-settings']['type'];
            if ($this->data['settings']['logo-settings']['type'] == 'artwork') {
                $artFilename = $this->data['settings']['logo-settings']['artwork']['filename'];
                if (!empty($artFilename) && file_exists(DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . $artFilename)) {
                    $artImage = $imagine->open(DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . $artFilename);
                }
            } elseif ($this->data['settings']['logo-settings']['type'] == 'upload') {
                $uploadFilename = $this->data['settings']['logo-settings']['upload']['filename'];
                if (!empty($uploadFilename) && file_exists(DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $uploadFilename)) {
                    $artImage = $imagine->open(DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $uploadFilename);
                }
            }
            if (is_object($artImage)) {

                $size      = $image->getSize();
                $wSize     = $artImage->getSize();


                $artImage->resize(new \Imagine\Image\Box($wSize->getWidth() * ($size->getHeight() / $wSize->getHeight()), $size->getHeight()));

                $wSize     = $artImage->getSize();

                //$image->paste($artImage, $bottomRight);
                $y = (int)(( $this->getHeight() - $wSize->getHeight() ) / 2);

                $x = $wSize->getWidth() + $this->scaleDPI(1/4);


                $artPosition = $this->data['settings']['logo-settings'][$logoType]['position'];
                if ($artPosition == 'left' || $artPosition == 'both') {
                    $bottomLeft = new \Imagine\Image\Point($this->scaleDPI(1/8), $y);
                    $image->paste($artImage, $bottomLeft);
                }
                if ($artPosition == 'right' || $artPosition == 'both') {
                    $bottomRight = new \Imagine\Image\Point($size->getWidth() - $wSize->getWidth() - $this->scaleDPI(1/8), $y);
                    $image->paste($artImage, $bottomRight);
                }
                
            }
            
            
            if ($this->data['settings']['text-settings']['use-text'] == 'yes') {
                $font = new \Imagine\Gd\Font(DIR_FS_CATALOG . 'includes' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $this->data['settings']['text-settings']['text']['font'] . '.ttf', $this->scalePPI($this->data['settings']['text-settings']['text']['size']), new \Imagine\Image\Color('000000', 0));
                $text = "";//"Type text here or use the options below";
                if (!empty($this->data['settings']['text-settings']['text']['content'])) {
                    $text = $this->data['settings']['text-settings']['text']['content'];
                }
                
                $textCenterY = (int)($this->getHeight() / 2);
                

                if ($this->data['settings']['paper-settings']['size'] == 25 && $this->data['settings']['text-settings']['text']['use-second-line'] == 'yes') {
                    
                    $fontsize = $font->getSize();
                    
                    $linewidth = $image->draw()->textWidth($text, $font);
                    $x = (int)( ($size->getWidth() - $linewidth) / 2);
                    
                    if ( ($x + $linewidth) > $size->getWidth() || $x <0 ) {
                        $text = 'Text too long';
                        $x = $wSize->getWidth() + $this->scaleDPI(1/4);
                    }
                    
                    $image->draw()->text($text, $font, new \Imagine\Image\Point($x, ($textCenterY - $fontsize - $this->scalePPI(3)) ));
                    
                    $font2 = new \Imagine\Gd\Font(DIR_FS_CATALOG . 'includes' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $this->data['settings']['text-settings']['text']['second-line']['font'] . '.ttf', $this->scalePPI($this->data['settings']['text-settings']['text']['second-line']['size']), new \Imagine\Image\Color('000000', 0));
                    $text2 = "";//"Use the options below - use the comments box if you want a different type of layout";
                    if (!empty($this->data['settings']['text-settings']['text']['second-line']['content'])) {
                        $text2 = $this->data['settings']['text-settings']['text']['second-line']['content'];
                    }

                    $linewidth = $image->draw()->textWidth($text2, $font2);
                    $x = (int)( ($size->getWidth() - $linewidth) / 2);
                    
                    if ( ($x + $linewidth) > $size->getWidth() || $x <0 ) {
                        $text2 = 'Text too long';
                        $x = $wSize->getWidth() + $this->scaleDPI(1/4);
                    }                    
                    
                    $image->draw()->text($text2, $font2, new \Imagine\Image\Point($x, ($textCenterY + $this->scalePPI(3)) ));
                } else {
                    
                    $fontsize = $font->getSize();
                    
                    $linewidth = $image->draw()->textWidth($text, $font);
                    $x = (int)( ($size->getWidth() - $linewidth) / 2);
                    
                    if ( ($x + $linewidth) > $size->getWidth() || $x <0 ) {
                        $text = 'Text too long';
                        $x = $wSize->getWidth() + $this->scaleDPI(1/4);
                    }
                    
                    $image->draw()->text($text, $font, new \Imagine\Image\Point($x, (int)($textCenterY - $fontsize /2) ));
                    
                    
                }
            }
            

            
        }
        
        $options = array(
            'resolution-units' => \Imagine\Image\ImageInterface::RESOLUTION_PIXELSPERINCH,
            'resolution-x' => $this->DPI,
            'resolution-y' => $this->DPI,
            'resampling-filter' => \Imagine\Image\ImageInterface::FILTER_LANCZOS,
            'png_compression_level' => 9,
        );
        
        if ($returnObject) {
            return $image;
        }
        
        if (!file_exists(DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR)) {
            mkdir(DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR, 0777, true);
        }
        $image->save(DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'test.png', $options);
        //header('Content-Type: image/png');
        //$image->show('png');
        //die();
        $path = \Yii::getAlias('@web');
        
        //echo '<div class="cus-print'.($this->get('size') == '25'? '' : ' cus-print-small').'">';
        //echo '<div class="cus-print-wrist cus-print-wrist-'.$this->get('colour').'">';
        //echo '<div class="cus-print-wrist-cont">';
        //echo '<div class="wcm_print">';
        echo '<img src="'.$path.'/images/tmp/test.png?'.  time().'" height="100%" width="100%">';
        //echo '<div>';
        //echo '<div>';
        //echo '<div>';
        //echo '<div>';
    }
    
    public function createPage()
    {
        $pattern = $this->createImage(true, false);
        
        $imagine = new \Imagine\Gd\Imagine();
        
        if ($this->data['settings']['paper-settings']['size'] == '25') {
            $size  = new \Imagine\Image\Box($this->scaleDPI(9.75), $this->scaleDPI(8));
            $qtyPerPage = 8;
        } else {
            $size  = new \Imagine\Image\Box($this->scaleDPI(9.75), $this->scaleDPI(7.5));
            $qtyPerPage = 10;
        }
        
        $color = new \Imagine\Image\Color('#000', 100);
        $image = $imagine->create($size, $color);
        
        $offset = $size->getHeight() / $qtyPerPage;
        
        for($step=0; $step< $qtyPerPage; $step++) {
            $position = new \Imagine\Image\Point($this->scaleDPI(1.25), $step*$offset);
            $image->paste($pattern, $position);
        }
        
        
        $options = array(
            'resolution-units' => \Imagine\Image\ImageInterface::RESOLUTION_PIXELSPERINCH,
            'resolution-x' => $this->DPI,
            'resolution-y' => $this->DPI,
            'resampling-filter' => \Imagine\Image\ImageInterface::FILTER_LANCZOS,
            'png_compression_level' => 9,
        );
        
        if (!file_exists(DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR)) {
            mkdir(DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR, 0777, true);
        }
        $image->save(DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'page.png', $options);
    }
    
    public function createDocument()
    {
        $this->createPage();
        
        $path = \Yii::getAlias('@vendor');
        require_once ($path.'/fpdf/fpdf.php');
        
        if ($this->data['settings']['paper-settings']['size'] == '25') {
            $pdf = new \FPDF('L','in',array(9.75, 8));
        } else {
            $pdf = new \FPDF('L','in',array(9.75, 7.5));
        }
        
        for ($page=0; $page < 10; $page++) {
            $pdf->AddPage();
            $pdf->Image(DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'page.png', 0, 0, -300);
        }
        
        $pdf->Output('F', DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'document.pdf');
        
    }

    
    

    /**
     * Return $data array
     */
    public function toArray() 
    {
        return $this->data;
    }
    
    private function array_to_xml( $data, &$xml_data ) {
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                if( is_numeric($key) ){
                    $key = 'item'.$key; //dealing with <0/>..<n/> issues
                }
                $subnode = $xml_data->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
         }
    }
    /**
     * Convert $data array to xml
     */
    public function toXML($root = null) 
    {
        $xml = new \SimpleXMLElement($root ? '<' . $root . '/>' : '<root/>');
        $this->array_to_xml($this->data, $xml);
        return $xml->asXML();
    }

    /**
     * Convert $data array to JSON
     */
    public function toJSON() 
    {
        return json_encode($this->data);
    }
    
}
