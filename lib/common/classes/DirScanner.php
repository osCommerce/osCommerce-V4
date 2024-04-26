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

class DirScanner {
    
    private $targetDir;
    private $checksumList;
    
    public function __construct($Dir) {
        $Dir = str_replace(DIRECTORY_SEPARATOR, '/', $Dir); // for win
        $this->targetDir = $Dir;
    }
    
    private function start($dir) {

        $fullArray = glob($dir . '/*');
        foreach ($fullArray as $item) {
            $item = str_replace(DIRECTORY_SEPARATOR, '/', $item); // for win
            if (is_dir($item)) {
                $path = str_replace([$this->targetDir . '/', '/'], ['', '|'], $item);
                $this->checksumList[$path] = '';
                $this->start($item);
            } elseif (is_file($item)) {
                $crc = crc32(file_get_contents($item));
                $path = str_replace([$this->targetDir . '/', '/'], ['', '|'], $item);
                $this->checksumList[$path] = $crc;
            }
        }
    }
    
    public function run() {
        $this->checksumList = [];
        if (is_dir($this->targetDir)) {
            $this->start($this->targetDir);
        }
        return $this->checksumList;
    }
}
