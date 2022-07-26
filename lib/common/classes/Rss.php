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

use Yii;
use DOMDocument;

class Rss
{
    private $channel;
    private $platform;
    private $item;
    private $item_count = 0;
    private $chanel_xml;

    public function __construct($lang="en", $link = "/")
    {
        $this->platform = \Yii::$app->get('platform')->config()->getId();
        $this->channel = new \stdClass();
        $this->getHeader($lang, $link);
    }

    private function getHeader($lang="en", $link = "/")
    {

        $platform = \common\models\Platforms::findOne((int)$this->platform);
        $this->channel->title = $platform->platform_name;
        $this->channel->link = ($platform->ssl_enabled == 0) ? "http://".$platform->platform_url : "https://".$platform->platform_url;
        $this->channel->atom = $this->channel->link.$link."?lang=".$lang;
        $this->channel->generator = $this->channel->link."/v=1.0";
        $this->channel->description = $platform->platform_name;
        $this->channel->lastBuildDate = date(DATE_RSS);
        $this->channel->updatePeriod = "hourly";
        $this->channel->updateFrequency = 1;
        $locale = \common\models\Languages::find()->where(['code' => $lang])->one();
        $this->channel->language = strtolower(str_replace("_", "-", $locale->locale));
        $this->channel->creator = $platform->platform_owner;

    }

    public function setItem($array)
    {
        if (count($array) > 0)
        {
            $data = new \stdClass();
            foreach ($array as $key => $value)
            {
                $data->$key = $value;
            }
            $this->item($data);
        }
        $this->channel();

    }

    public function setItems($items)
    {
        if (count($items) > 0)
        {
            foreach ($items as $item) {
                $this->setItem($item);
            }
        }
    }

    private function channel()
    {
        $this->chanel_xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><channel></channel>', LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_ERR_FATAL);
        $this->chanel_xml->addChild('title', $this->channel->title);

        $atom = $this->chanel_xml->addChild('atom:link', '', 'http://www.w3.org/2005/Atom');
        $atom->addAttribute('href', $this->channel->atom);
        $atom->addAttribute('type', 'application/rss+xml');
        $atom->addAttribute('rel', 'self');

        $this->chanel_xml->addChild('link', $this->channel->link);
        $this->chanel_xml->addChild('description', $this->channel->description);
        $this->chanel_xml->addChild('lastBuildDate', $this->channel->lastBuildDate);
        $this->chanel_xml->addChild('language', $this->channel->language);
        $this->chanel_xml->addChild('sy:updatePeriod', $this->channel->updatePeriod, 'http://purl.org/rss/1.0/modules/syndication/');
        $this->chanel_xml->addChild('sy:updateFrequency', $this->channel->updateFrequency, 'http://purl.org/rss/1.0/modules/syndication/');
        $this->chanel_xml->addChild('generator', $this->channel->generator);
        if(isset($this->item) && $this->item !== null) {
            foreach ($this->item as $item) {
                $toDom = dom_import_simplexml($this->chanel_xml);
                $fromDom = dom_import_simplexml($item);
                $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
            }
        }
        return $this->chanel_xml;
    }

    private function item($data)
    {
        $this->item[$this->item_count] = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><item></item>', LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_ERR_FATAL);
        if(isset($data->title)) $this->item[$this->item_count]->addChild('title', htmlspecialchars($data->title));
        if(isset($data->link)) $this->item[$this->item_count]->addChild('link', $data->link);
        if($this->channel->creator) $this->addCdataChild($this->item[$this->item_count], 'dc:creator', $this->channel->creator, 'http://purl.org/dc/elements/1.1/');
        if(isset($data->pubDate)) $this->item[$this->item_count]->addChild('pubDate', \DateTime::createFromFormat("Y-m-d H:i:s", $data->pubDate)->format(DATE_RSS));
        if(isset($data->categories)){
            foreach ($data->categories as $category)
            {
                if(strlen($category['name']) > 0)
                {
                    $this->addCdataChild($this->item[$this->item_count], 'category', $category['name']);
                }
            }
        }
        if(isset($data->guid))
        {
            $guid = $this->item[$this->item_count]->addChild('guid', $data->guid);
            $guid->addAttribute('isPermaLink', empty($data->itemPermalink) ? "false" : "true");
        }
        if(isset($data->description)) $this->addCdataChild($this->item[$this->item_count],'description', $data->description);
        if(isset($data->enclosureUrl) && isset($data->enclosureLength) && isset($data->enclosureType))
        {
            $enclosure = $this->item[$this->item_count]->addChild('enclosure');
            $enclosure->addAttribute('url', $data->enclosureUrl);
            $enclosure->addAttribute('length', $data->enclosureLength);
            $enclosure->addAttribute('type', $data->enclosureType);
        }
        if(isset($data->content)) $this->addCdataChild($this->item[$this->item_count],'content:encoded', $data->content, 'http://purl.org/rss/1.0/modules/content/');

        $this->item_count=$this->item_count+1;

    }

    public function build()
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><rss version="2.0" 
            xmlns:content="http://purl.org/rss/1.0/modules/content/"
            xmlns:wfw="http://wellformedweb.org/CommentAPI/"
            xmlns:dc="http://purl.org/dc/elements/1.1/"
            xmlns:atom="http://www.w3.org/2005/Atom"
            xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
            xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
            />', LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_ERR_FATAL);

        $toDom = dom_import_simplexml($xml);
        $fromDom = dom_import_simplexml($this->channel());
        $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->appendChild($dom->importNode(dom_import_simplexml($xml), true));
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    private function addCdataChild($xml, $name, $value = null, $namespace = null)
    {
        $element = $xml->addChild($name, null, $namespace);
        $dom = dom_import_simplexml($element);
        $elementOwner = $dom->ownerDocument;
        $dom->appendChild($elementOwner->createCDATASection($value));
        return $element;
    }
}