<?php
namespace common\modules\analytic;

trait adTrait {
    
    public function collectCookie(){
return <<<EOD
if (typeof Promise == 'undefined'){ var s=document.createElement('script');s.setAttribute('src', '//cdnjs.cloudflare.com/ajax/libs/bluebird/3.3.4/bluebird.min.js');document.head.appendChild(s); s.onload = function(){ loadGA(); } } else { loadGA(); }
'use strict';
function loadGA(){
var pGa = new Promise(function(resolve, reject){setInterval(function(){return typeof ga == 'function'&&ga.loaded?resolve():null; })});pGa.then(function(){ gaCookieTracker(); })
function gaCookieTracker(){if (localStorage.ga_cookie == 'false'){if (typeof ga == 'function'){var tracker = ga.getAll();var item = {};var ref, gclid, clientId, campaign, keyword;if (Array.isArray(tracker)){
$.each(tracker, function(i, _tracker){ref = _tracker.get('referrer');gclid = _tracker.get('_gclid');clientId = _tracker.get('clientId');campaign = _tracker.get('campaignName');
keyword = _tracker.get('campaignKeyword');if (ref != undefined && ref.length > 0 && !item.hasOwnProperty('utmcsr')){item.utmcsr = ref;}else if (gclid != undefined && gclid.length > 0 && !item.hasOwnProperty('utmgclid')){
item.utmgclid = gclid;}if (campaign != undefined && campaign.length > 0 && !item.hasOwnProperty('utmccn')){item.utmccn = campaign;}if (keyword != undefined && keyword.length > 0 && !item.hasOwnProperty('utmctr')){
item.utmctr = keyword;}if (clientId != undefined && clientId.length > 0 && !item.hasOwnProperty('utmcmd')){item.utmcmd = clientId;}
});var _c = [];$.each(item, function(i, e){_c.push(i+"="+e);});if(_c.length>0){document.cookie = "__utmz="+_c.join("|");}localStorage.ga_cookie = 'true';}}}}
}
EOD;
    }
}