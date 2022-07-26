<div class="popup-box-wrap pop-mess">
    <div class="around-pop-up"></div>
    <div class="popup-box">
        <div class="pop-up-close pop-up-close-alert"></div>
        <div class="pop-up-content">
            <div class="popup-heading">{if $heading}{$heading}{else}{$smarty.const.TEXT_NOTIFIC}{/if}</div>
            <div class="popup-content pop-mess-cont pop-mess-cont-{$messageType}">
                {$message}
            </div>
        </div>
        <div class="noti-btn">
            <div></div>
            <div><span class="btn btn-primary">{$smarty.const.TEXT_BTN_OK}</span></div>
        </div>
    </div>
    <script>
        //$('body').scrollTop(0);
        $('.popup-box-wrap.pop-mess').css('top',(window.scrollY+200)+'px');
        $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
            $(this).parents('.pop-mess').remove();
            //location.reload();

            {$clickJs}

        });
    </script>
</div>