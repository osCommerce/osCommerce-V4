
<form action="" class="form-copy-from">
    <input type="hidden" name="menu" value="{$menu}"/>
    <input type="hidden" name="platform_id" value="{$platform_id}"/>
    <div class="popup-heading">{$smarty.const.TEXT_COPY_FROM}</div>
    <div class="popup-content">
        <div class="" style="padding: 20px 0">
            {if $isMultiPlatforms}
                <div class=""><strong>{$smarty.const.TEXT_PLATFORM}</strong></div>
                <div class="" style="padding-bottom: 10px">
                    <select name="platform_id_from" id="" class="form-control">
                        {foreach $platforms as $item}
                            <option value="{$item.id}">{$item.text}</option>
                        {/foreach}
                    </select>
                </div>
            {/if}
            <div class=""><strong>{$smarty.const.TEXT_MENU}</strong></div>
            <div class="" style="padding-bottom: 10px">
                <select name="menu_from" id="" class="form-control">
                    {foreach $menus as $item}
                        <option value="{$item.menu_name}">{$item.menu_name}</option>
                    {/foreach}
                </select>
            </div>
            {if !$isEmpty}
                <div class="" style="text-align: center; color: #f00; padding-top: 10px;
font-weight: 600">{$smarty.const.MENU_IS_NOT_EMPTY}</div>
            {/if}
        </div>
    </div>
    <div class="noti-btn">
        <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
        <div><button class="btn btn-primary btn-save">{$smarty.const.IMAGE_COPY}</button></div>
    </div>
</form>
<script type="text/javascript">
    $(function(){
        $('.form-copy-from').on('submit', function(e){
            e.preventDefault();
            $('.popup-content', this).append('<div class="preloader"></div>');
            $.post('menus/copy-from-action', $(this).serializeArray(), function(){
                $.get(window.location.href, function(d){
                    $('.popup-box-wrap').remove();
                    $('.content-container').html(d);
                })
            })
        })
    })
</script>