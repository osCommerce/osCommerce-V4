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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Socials extends Widget
{

  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
    {
        /* Facebook Posts */
        $vendorDir = dirname(dirname(dirname(__FILE__)));
        $baseDir = dirname($vendorDir);
      
        if(defined('FB_APPLICATION_ID') && !empty(FB_APPLICATION_ID) && defined('FB_APPLICATION_SECRET') && !empty(FB_APPLICATION_SECRET) && defined('FB_PAGE_ID') && !empty(FB_PAGE_ID)) {
            include_once ($baseDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'facebookphpsdk' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'facebook.php');

            $config = array();
            $config['appId'] = FB_APPLICATION_ID;
            $config['secret'] = FB_APPLICATION_SECRET;
            $config['fileUpload'] = false;
            if($this->settings[0]['fb_count']){
                $fcount = $this->settings[0]['fb_count'];
            }else{
                $fcount = 10;
            }
            
            
            try {
                $facebook = new \Facebook($config);
                $pageid = FB_PAGE_ID;
                $return_fb = $facebook->api("/" . $pageid . "?fields=posts.limit(".$fcount."){full_picture,permalink_url,name,message}");
            } catch (\Exception  $e) {
                $return_fb = false;
            }
                        
        }

        /* End Facebook Posts */
        
        /* Instagram Posts */
        
        if(defined('IN_ACCESS_TOKEN') && !empty(IN_ACCESS_TOKEN) && defined('IN_USERNAME') && !empty(IN_USERNAME)) {
            function rudr_instagram_api_curl_connect($api_url) {
                $connection_c = curl_init(); // initializing
                curl_setopt($connection_c, CURLOPT_URL, $api_url); // API URL to connect
                curl_setopt($connection_c, CURLOPT_RETURNTRANSFER, 1); // return the result, do not print
                curl_setopt($connection_c, CURLOPT_TIMEOUT, 20);
                $json_return = curl_exec($connection_c); // connect and get json data
                curl_close($connection_c); // close connection
                return json_decode($json_return); // decode and return
            }

            $access_token = IN_ACCESS_TOKEN;
            $username = IN_USERNAME;
            if($this->settings[0]['insta_count']){
                $count_insta = $this->settings[0]['insta_count'];
            }else{
                $count_insta = 10;
            }         
            if ($this->settings[0]['insta_hashtag']) {
                $return_insta = rudr_instagram_api_curl_connect("https://api.instagram.com/v1/tags/" . $this->settings[0]['insta_hashtag'] . "/media/recent?access_token=" . $access_token . "&count=" . $count_insta);
            } else {
                $user_search = rudr_instagram_api_curl_connect("https://api.instagram.com/v1/users/search?q=" . $username . "&access_token=" . $access_token);
                $user_id = $user_search->data[0]->id; // or use string 'self' to get your own media            
                $return_insta = rudr_instagram_api_curl_connect("https://api.instagram.com/v1/users/" . $user_id . "/media/recent?access_token=" . $access_token . "&count=" . $count_insta);
            }
        }        

        /* End Instagram Posts */
        
        /* Twitter Posts */
        
        if(defined('TW_ACCESS_TOKEN') && !empty(TW_ACCESS_TOKEN) && defined('TW_ACCESS_TOKEN_SECRET') && !empty(TW_ACCESS_TOKEN_SECRET) && defined('TW_CONSUMER_KEY') && !empty(TW_CONSUMER_KEY)
            && defined('TW_CONSUMER_SECRET') && !empty(TW_CONSUMER_SECRET)) {
            include_once ($baseDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'twitter' . DIRECTORY_SEPARATOR . 'TwitterAPIExchange.php');

            $settings = array(
                'oauth_access_token' => TW_ACCESS_TOKEN, ///YOUR_OAUTH_ACCESS_TOKEN
                'oauth_access_token_secret' => TW_ACCESS_TOKEN_SECRET, ///YOUR_OAUTH_ACCESS_TOKEN_SECRET
                'consumer_key' => TW_CONSUMER_KEY, ///YOUR_CONSUMER_KEY
                'consumer_secret' => TW_CONSUMER_SECRET ///YOUR_CONSUMER_SECRET
            );
            
            if($this->settings[0]['tw_hashtag']){
                $url = "https://api.twitter.com/1.1/search/tweets.json";
            }else{
                $url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
            }
            $requestMethod = "GET";
            if($this->settings[0]['tw_count']){
                $tw_count = $this->settings[0]['tw_count'];
            }else{
                $tw_count = 10;
            }    
            if($this->settings[0]['tw_hashtag']){
                $getfield = '?q=%23' . $this->settings[0]['tw_hashtag'] . '&count=' . $tw_count;
            }else{
                $getfield = '?screen_name=' . TW_SCREEN_NAME . '&count=' . $tw_count;
            }
            $twitter = new \TwitterAPIExchange($settings);
            $return_twitter = json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest(), $assoc = TRUE);            
        }

        /* End Twitter Posts */
		
		/* YouTube Video */
        if (defined('YT_API_KEY') && !empty($this->settings[0]['yt_playlist_id'])){
            $api_key = YT_API_KEY;
            $playlist_id =  $this->settings[0]['yt_playlist_id']; 
            $maxResults = $this->settings[0]['yt_count'];
            if (!(int)$maxResults){
                $maxResults = 10;
            }
            $api_url = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=' . $maxResults . '&playlistId='. $playlist_id . '&key=' . $api_key;
            $playlist = null;
            $content = '';
            try {
                $content = file_get_contents($api_url);
            } catch (\Exception $e){
                if (\frontend\design\Info::isAdmin()){
                    echo "Youtube Error:" . $e->getMessage();
                }
            }
            if (!empty($content)){
                $playlist = json_decode($content);
            }
        }
        
		/* End YouTube Video */

        return IncludeTpl::widget([
                    'file' => 'boxes/socials.tpl',
                    'params' => [
                        'settings' => $this->settings,
                        'return_fb' => $return_fb,
                        'return_insta' => $return_insta,
                        'return_twitter' => $return_twitter,
                        'playlist' => $playlist,
                    ],
        ]);
    }

}