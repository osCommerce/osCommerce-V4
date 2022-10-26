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

namespace common\extensions\ErrorLogViewer;

class LogReader
{
    private $file;
    private $handle;
    private $headers;

    protected $pattern = '|^([\d\-: ]{19}) \[([\d\.\-]*)\]\[([\d\-]*)\]\[([\d\w\-]*)\]\[(.*)\]\[(.*)\] (.*$)|u';
    protected $matches = [
        0 => 'origin',
        1 => 'date',
        2 => 'ip',
        3 => 'user_id',
        4 => 'session_id',
        5 => 'level',
        6 => 'category',
        7 => 'text',
    ];

    public function __construct($file)
    {
        $sourceList = array('backend', 'frontend', 'console');
        $tmp = explode("/", $file);
        if(count($tmp) > 2 or !in_array($tmp[0], $sourceList)){
            throw new \Exception("Undefined source/file");
        }
        $source = trim($tmp[0]);
        $fileName = trim($tmp[1]);
        $this->file = \Yii::getAlias('@'.$source).DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."logs".DIRECTORY_SEPARATOR.$fileName;
        $this->open();
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    private function open()
    {
        $this->handle = file($this->file);
        foreach ($this->handle as $id => $line)
        {
            if($this->isHeader($line))
            {
                $this->collectHeaders($id, $line);
            }
        }
    }

    public function getDetails($id)
    {
        $data = "";
        for($i = (int)$id+1; $i < count($this->handle); $i++){
            if($this->isHeader($this->handle[$i])) break;
            $data .= htmlspecialchars($this->handle[$i]);
        }
        return $data;

    }

    private function isHeader($line)
    {
        return preg_match($this->pattern, $line);
    }

    private function collectHeaders($id, $line)
    {
        if(!preg_match($this->pattern, $line, $matches)) return false;
        $this->headers[$id] = $matches;
    }


    private static function str_starts_with($haystack, $needle) // for support php v7
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}