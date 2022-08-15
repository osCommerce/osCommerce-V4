{use class="yii\helpers\Html"}{use class="frontend\design\IncludeTpl"}{use class="Yii"}{use class="frontend\design\Block"}{use class="frontend\design\Info"}{use class="common\components\google\widgets\GoogleWidget"}{use class="common\widgets\WarningWidget"}{use class="common\components\google\widgets\GoogleTagmanger"}
<script type="text/javascript">
    var useCarousel = false;
    var tl_js = [];var tl_start = false;var tl_include_js = [];var tl_include_loaded = [];var tl = function(a, b){ var script = { };if (typeof a == 'string' && typeof b == 'function'){ script = { 'js': [a],'script': b}} else if (typeof a == 'object' && typeof b == 'function') { script = { 'js': a,'script': b}} else if (typeof a == 'function') { script = { 'script': a}}tl_js.push(script);if (tl_start){ tl_action([script])}};
</script>

{capture name="body"}
    {if !$app->controller->view->no_header_footer}
        {Block::widget(['name' => 'header', 'params' => ['type' => 'header']])}
    {/if}
    <!-- body-content -->
    {if !$app->controller->view->no_header_footer}
        {Block::widget(['name' => 'footer', 'params' => ['type' => 'footer']])}
    {/if}
{/capture}

{Info::getCss()}

{if Info::isAdmin()}
    <link rel="stylesheet" href="{Info::themeFile('/css/admin.css')}"/>
{/if}

<script type="text/javascript">
    {Info::themeSetting('javascript', 'javascript')}
</script>


{$smarty.capture.body}

{if (\common\helpers\Acl::checkExtensionAllowed('BonusActions'))}
    {\frontend\design\boxes\promotions\PromoTrigger::widget()}
    {\common\models\promotions\PromotionsBonusObserver::checkAlertPromoAction()}
{/if}
<script type="text/javascript" src="{Info::themeFile('/js/jquery.min.js')}" {$this->async}></script>
{$this->endBody()}

{strip}
    <script type="text/javascript">
        var tlSize = {
            current: [],
            dimensions: [],

            init: function(){
                tlSize.dimensions = {
                    {foreach \frontend\design\Info::themeSetting('media_query', 'extend') as $i}
                    '{$i}': '{$i}'.split('w'),
                    {/foreach}
                };

                $(window).on('layoutChange', tlSize.bodyClass);
                tlSize.resize();
                $(window).on('resize', tlSize.resize);
            },

            resize: function(){
                $.each(tlSize.dimensions, function(key, val){
                    var from = val[0]*1;
                    var to = val[1];
                    if (to) {
                        to = to*1
                    } else {
                        to = 10000
                    }
                    var data = { };
                    var w = window.innerWidth;
                    if (!w) {
                        w = $(window).width();
                    }
                    if (from <= w && w <= to) {
                        if ($.inArray(key, tlSize.current ) === -1) {
                            tlSize.current.push(key);
                            tlSize.current = tlSize.sort(tlSize.current);
                            data = {
                                key: key,
                                status: 'in',
                                from: from,
                                to: to,
                                current: tlSize.current
                            };
                            $(window).trigger('layoutChange', [data]);
                            $(window).trigger(key+'in', [data]);
                        }
                    } else {
                        var index = tlSize.current.indexOf(key);
                        if (index > -1) {
                            tlSize.current.splice(index, 1);
                            tlSize.current = tlSize.sort(tlSize.current);
                            data = {
                                key: key,
                                status: 'out',
                                from: from,
                                to: to,
                                current: tlSize.current
                            };
                            $(window).trigger('layoutChange', [data]);
                            $(window).trigger(key+'out', [data]);
                        }
                    }
                })
            },

            sort: function(arr){
                var v = [];
                var t = [];
                var tmp = [];
                var l = arr.length;
                for (var i = 0; i < l; i++) {
                    tmp[i] = '0w0';
                    $.each(arr, function (key, val) {
                        v = val.split('w');
                        v[0] = v[0]*1;
                        v[1] = v[1]*1;
                        if (!v[1]) {
                            v[1] = 10000
                        }
                        t = tmp[i].split('w');
                        t[0] = t[0]*1;
                        t[1] = t[1]*1;
                        if (t[1] < v[1]) {
                            tmp[i] = val
                        } else if (t[1] == v[1] && t[0] > v[0]) {
                            tmp[i] = val
                        }
                    });
                    var index = arr.indexOf(tmp[i]);
                    arr.splice(index, 1);
                }

                return tmp
            },

            bodyClass: function(e, d){
                if (d.status == 'in') {
                    $('body').addClass(d.key)
                }
                if (d.status == 'out') {
                    $('body').removeClass(d.key)
                }
            }

        };

        {if defined('USE_SOUCRCE_DURING_COPY')}
        {if USE_SOUCRCE_DURING_COPY == 'allow_source'}
        tl(function(){
            var grabText = function(e){
                var range = window.getSelection().toString();
                if (range.length > 0){
                    var words = range.split(" ");
                    var random =  Math.ceil(Math.random() * (words.length - 1) + 1);
                    var newStr = '';
                    /*var isIE = (navigator.userAgent.indexOf('MSIE') > -1 ? true : false);*/
                    $.each(words, function(i, word) {
                        if (i == random - 1){
                            word = word + ' '  + '{$smarty.const.TEXT_COPIED_FROM}' + ' ' + window.location.href + ' ';
                        }
                        newStr += word + ' ';
                    });
                    newStr = newStr.substr(0, newStr.length - 1);
                    if (e.clipboardData){
                        e.clipboardData.setData('text/plain', newStr);
                    } else if(window.clipboardData) {
                        window.clipboardData.setData('text', newStr);
                    }
                    e.preventDefault();
                }
            };
            if (document.addEventListener){
                document.addEventListener('copy', function(e){
                    grabText(e);
                });
            } else if (document.attachEvent){
                document.attachEvent("onCopy", function(e){
                    grabText(window);
                });
            }

        });
        {else if USE_SOUCRCE_DURING_COPY == 'disallow'}
        tl(function(){
            var clearText = function (e){
                if (e.clipboardData){
                    e.clipboardData.clearData();
                } else if(window.clipboardData) {
                    window.clipboardData.clearData();
                }
                e.preventDefault();
            };

            if (document.addEventListener){
                document.addEventListener('copy', function(e){
                    clearText(e);
                });
            } else if (document.attachEvent){
                document.attachEvent("onCopy", function(e){
                    clearText(window);
                });
            }
        });
        {/if}

        {/if}

        var tl_action = function (script) {
            if (typeof jQuery == 'function') {
                if (typeof jQuery.migrateMute==='undefined') {
                    $.ajax({
                        url: '{Info::themeFile('/js/jquery-migrate-3.0.0.min.js')}',
                        dataType: 'script'
                    });
                }
                tlSize.init();
                tl_start = true;
                var action = function (block) {
                    var key = true;
                    $.each(block.js, function (j, js) {
                        var include_index = tl_include_js.indexOf(js);
                        if (include_index == -1 || tl_include_loaded.indexOf(js) == -1) {
                            key = false;
                        }
                    });
                    if (key) {
                        block.script()
                    }
                    return key
                };
                $.each(script, function (i, block) {
                    if (!action(block)) {
                        $.each(block.js, function (j, js) {
                            var include_index = tl_include_js.indexOf(js);
                            if (include_index == -1) {
                                tl_include_js.push(js);
                                include_index = tl_include_js.indexOf(js);
                                $.ajax({
                                    url: js, success: function () {
                                        tl_include_loaded.push(js);
                                        $(window).trigger('tl_action_' + include_index);
                                    },
                                    error: function (a, b, c) {
                                        console.error('Error: "' + js + '" ' + c);
                                    },
                                    dataType: 'script'
                                });
                            }
                            $(window).on('tl_action_' + include_index, function () {
                                action(block)
                            })
                        })
                    }
                })
            } else {
                setTimeout(function () {
                    tl_action(script)
                }, 100)
            }

            document.cookie = "xwidth="+window.outerWidth+ (cookieConfig?cookieConfig:'');
            document.cookie = "xheight="+window.outerHeight+ (cookieConfig?cookieConfig:'');
        };
        tl_action(tl_js);

    </script>
{/strip}

<link rel="stylesheet" href="{Info::themeFile('/css/style.css')}"/>