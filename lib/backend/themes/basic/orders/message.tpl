         <div class="popup-box-wrap pop-mess">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading">{$smarty.const.TEXT_NOTIFIC}</div>
                    {foreach $messagestack->messages as $messages}
                        {foreach $messages as $message}
                            <div class="popup-content pop-mess-cont pop-mess-cont-{$message['type']}">
                                {$message['text']}
                            </div>
                        {/foreach}
                    {/foreach}
                </div>  
                <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary">{$smarty.const.TEXT_BTN_OK}</span></div>
                </div>
            </div> 
            <script>
            $(window).scrollTop(0);
            $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
               $(this).parents('.pop-mess').remove();
            });
        </script>
        </div>