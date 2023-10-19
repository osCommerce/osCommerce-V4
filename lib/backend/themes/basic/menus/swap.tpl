
<form action="" class="form-copy-from">
    <input type="hidden" name="menu_id" value="{$menuId}"/>
    <input type="hidden" name="platform_id" value="{$platformId}"/>

    <div class="popup-heading">{$smarty.const.TEXT_SWAP_MENU}</div>
    <div class="popup-content">

        <div class="row align-items-center">
            <div class="col-5">

                {if $isMultiPlatforms}
                    <label>{$smarty.const.TEXT_PLATFORM}</label>
                    <div class="mb-3">
                        {$platformName}
                    </div>
                {/if}

                <label>{$smarty.const.TEXT_MENU}</label>
                <div>
                    {$menuName}
                </div>

            </div>
            <div class="col-2 text-center">
                <i class="icon-exchange" style="font-size: 20px"></i>
            </div>
            <div class="col-5">

                {if $isMultiPlatforms}
                    <label>{$smarty.const.TEXT_PLATFORM}</label>
                    <div class="mb-3">
                        <select name="platform_id_2" id="" class="form-control">
                            {foreach $platforms as $item}
                                <option value="{$item.id}">{$item.text}</option>
                            {/foreach}
                        </select>
                    </div>
                {else}
                    <input type="hidden" name="platform_id_2" value="{$platformId}"/>
                {/if}

                <label>{$smarty.const.TEXT_MENU}</label>
                <div>
                    <select name="menu_id_2" id="" class="form-control">
                        {foreach $menus as $item}
                            <option value="{$item.id}">{$item.menu_name}</option>
                        {/foreach}
                    </select>
                </div>

            </div>
        </div>
    </div>
    <div class="noti-btn">
        <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
        <div><button class="btn btn-primary btn-save">{$smarty.const.TEXT_SWAP}</button></div>
    </div>
</form>
<script type="text/javascript">
    $(function(){
        $('.form-copy-from').on('submit', function(e){
            e.preventDefault();
            $('.popup-content', this).append('<div class="preloader"></div>');
            $.post('menus/swap-action', $(this).serializeArray(), function(){
                $.get(window.location.href, function(d){
                    $('.popup-box-wrap').remove();
                    $('.content-container').html(d);
                })
            })
        })
    })
</script>