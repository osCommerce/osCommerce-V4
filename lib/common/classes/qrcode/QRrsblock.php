<?php

namespace common\classes\qrcode;
require_once('init.php');
//---- qrencode.php -----------------------------




/*
 * PHP QR Code encoder
 *
 * Main encoder classes.
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
 
    class QRrsblock {
        public $dataLength;
        public $data = array();
        public $eccLength;
        public $ecc = array();
        
        public function __construct($dl, $data, $el, &$ecc, QRrsItem $rs)
        {
            $rs->encode_rs_char($data, $ecc);
        
            $this->dataLength = $dl;
            $this->data = $data;
            $this->eccLength = $el;
            $this->ecc = $ecc;
        }
    };
    