{strip}
{use class="frontend\design\Info"}
{Info::addBoxToCss('cookie-notice')}
<script>
(function(){
    var settings = JSON.parse('{json_encode($settings[0])}');
    var matches = document.cookie.match(new RegExp("(?:^|; )" + "cookieNotice".replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
    var noticeTimestamp = matches ? decodeURIComponent(matches[1]) : undefined;

    if (noticeTimestamp !== undefined && noticeTimestamp >= {$revisionTimestamp}) return;

    var position = settings.position ? settings.position : 'top';

    var cookieNotice = document.createElement('div');
    cookieNotice.className = "cookie-notice " + position;

    var cookieNoticeText = document.createElement('div');
    cookieNoticeText.className = "text";
    cookieNoticeText.innerHTML = `{$smarty.const.TEXT_COOKIE_NOTICE}`;
    cookieNotice.append(cookieNoticeText);

    var cookieNoticeButtons = document.createElement('div');
    cookieNoticeButtons.className = "buttons";
    cookieNotice.append(cookieNoticeButtons);

    var cookieNoticeAccept = document.createElement('span');
    cookieNoticeAccept.className = "btn btn-accept";
    cookieNoticeAccept.innerHTML = `{$smarty.const.TEXT_COOKIE_BUTTON}`;
    cookieNoticeButtons.append(cookieNoticeAccept);
    if (!settings.cancel_button){
        var cookieNoticeClose = document.createElement('span');
        cookieNoticeClose.className = "btn btn-close";
        cookieNoticeClose.innerHTML = `{$smarty.const.TEXT_CLOSE}`;
        cookieNoticeButtons.append(cookieNoticeClose);
    }

    cookieNotice.style.position = 'fixed';
    cookieNotice.style.left = 0;
    cookieNotice.style.width = '100%';
    cookieNotice.style.zIndex = '1000';
    cookieNotice.style[position] = 0;

    document.body.append(cookieNotice);

    cookieNoticeClose.addEventListener('click', function(){
        cookieNotice.remove();
    });

    cookieNoticeAccept.addEventListener('click', function(){
        setCookies();
        function setCookies(){
            if ($ && typeof $.cookie === 'function'){
                $.cookie('cookieNotice', {$revisionTimestamp},
                    $.extend(cookieConfig || { }, { expires: settings.expires_days ? settings.expires_days : 365})
                );
            } else {
                setTimeout(function(){
                    setCookies()
                }, 1000)
            }
        }
        cookieNotice.remove();
    })
})()
</script>
{/strip}