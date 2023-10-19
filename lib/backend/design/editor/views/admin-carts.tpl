{if is_array($admin_choice) && count($admin_choice)}
 <div class="dropdown">
    <a href="#" class="btn btn-link-create dropdown-toggle" data-bs-toggle="dropdown" title="{$smarty.const.TEXT_UNSAVED_CARTS}">
        {$smarty.const.TEXT_UNSAVED_CARTS}
    </a>
    <ul class="dropdown-menu">
        {foreach $admin_choice as $choice}
        <li>{$choice}</li>
        {/foreach}
    </ul>
</div>
{/if}
{if !$saved}
 <script>
    (function($){
        $('.unstored_carts a').click(function(e){
            var href = $(this).attr('href');
            e.preventDefault();
            bootbox.dialog({
                closeButton: false,
                message: "Do You want to save current Cart?",
                title: "{$smarty.const.ICON_WARNING}",
                buttons: {
                    success: {
                        label: "{$smarty.const.TEXT_BTN_YES}",
                        className: "btn-confirm",
                        callback: function() {
                            order.saveCart(function(){
                                window.location.href = href;
                            });
                        }
                    },
                    main: {
                        label: "{$smarty.const.TEXT_BTN_NO}",
                        className: "btn-cancel",
                        callback: function() {
                            window.location.href = href;
                        }
                    }
                }
            });     
        })
    })(jQuery)
 </script>
{/if}