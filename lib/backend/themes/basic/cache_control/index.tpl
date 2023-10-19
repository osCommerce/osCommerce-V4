<style type="text/css">
    .select-col-1 {
        border-right: 1px solid #ccc;
        margin-bottom: 20px;
    }
    .select-all {
        font-weight: bold;
        margin-bottom: 15px;
    }
</style>
<div class="row" id="download_files" style="_display: none;">
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i><span id="cache_control_title">{$smarty.const.TEXT_FLUSH_CACHE}</span>
                </h4>

                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span id="cache_control_collapse" class="btn btn-xs widget-collapse"><i
                                    class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div id="cache_control_data">
                <div class="popup-box-wrap pop-mess" style="display: none;">
					<div class="around-pop-up"></div>
					<div class="popup-box">
						<div class="pop-up-close pop-up-close-alert"></div>
						<div class="pop-up-content">
							<div class="popup-heading">{$smarty.const.TEXT_NOTIFIC}</div>
							<div class="popup-content popup-content-data">
								
							</div>  
						</div>     
						<div class="noti-btn noti-btn-ok">
							<div><span class="btn btn-primary">{$smarty.const.TEXT_BTN_OK}</span></div>
						</div>
					</div>  
					<script>
						$('body').scrollTop(0);
						$('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
							$(this).parents('.pop-mess').hide();
						});
					</script>
				</div>
            </div>
            <div class="widget-content fields_style">
                <form name="cache_control_form" action="" method="post" onsubmit="return flushCache()">

                    <div class="row">
                        <div class="col-md-2 select-col select-col-1">

                            <label class="  select-all">
                                <input name="first-col" type="checkbox" class="checkinput" value="1"><span></span> {$smarty.const.TEXT_SELECT_ALL}
                            </label>
                            <label>
                                <input name="system" type="checkbox" class="checkinput" value="1"><span></span> {$smarty.const.TEXT_SYSTEM}
                            </label>
                            <label>
                                <input name="smarty" type="checkbox" class="checkinput" value="1"><span></span> {$smarty.const.TEXT_SMARTY}
                            </label>
                            <label>
                                <input name="debug"  type="checkbox" class="checkinput" value="1"><span></span> {$smarty.const.TEXT_DEBUG}
                            </label>
                            <label>
                                <input name="opcache_reset"  type="checkbox" class="checkinput" value="1"><span></span> OPcache
                            </label>
                            <label>
                                <input name="hooks"  type="checkbox" class="checkinput" value="1"><span></span> Hooks
                            </label>
                            <label>
                                <input name="theme"  type="checkbox" class="checkinput" value="1"><span></span> {$smarty.const.BOX_HEADING_THEMES}
                            </label>
                            <label>
                                <input name="categories_cache"  type="checkbox" class="checkinput" value="1"><span></span> {$smarty.const.PRODUCTS_IN_CATEGORIES}
                            </label>
                        </div>
                        <div class="col-md-1">&nbsp;</div>
                        <div class="col-md-2 select-col select-col-2">

                            <label class=" select-all">
                                <input name="second-col"  type="checkbox" class="checkinput" value="1"><span></span> {$smarty.const.TEXT_SELECT_ALL}
                            </label>
                            <label>
                                <input name="logs"  type="checkbox" class="checkinput" value="1"><span></span> {$smarty.const.TEXT_LOGS}
                            </label>
                            <label>
                                <input name="image_cache"  type="checkbox" class="checkinput" value="1"><span></span> {$smarty.const.TEXT_IMAGE_CACHE}
                            </label>
                            <label>
                                <input name="prod_stock_cache"  type="checkbox" class="checkinput" value="1"><span></span> Product Stock Cache
                            </label>
                            <label>
                                <input name="app_shop_cache"  type="checkbox" class="checkinput" value="1"><span></span> {$smarty.const.TEXT_INSTALL_CACHE}
                            </label>
                            <label>
                                <input name="do_migrations"  type="checkbox" class="checkinput" value="1"><span></span> Migrations apply
                            </label>
                        </div>
                    </div>

                    <input type="submit" class="btn btn-primary" value="{$smarty.const.TEXT_FLUSH}" >
                    
                    
                </form>
            </div>
        </div>
    </div>
</div>
                
<script type="text/javascript">
function flushCache() {

    $('#content > .container').addClass('hided-box').append('<div class="hided-box-holder"><div class="preloader"></div></div>');

    $.post("{$app->urlManager->createUrl('cache_control/flush')}", $('form[name=cache_control_form]').serialize(), function(data, status){
        $('#content > .container').removeClass('hided-box');
        $('.hided-box-holder').remove();

        if (status == "success") {
			$('#cache_control_data .popup-content-data').empty();
            $('#cache_control_data .popup-content-data').append(data);
			$('#cache_control_data .popup-box-wrap').show();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

    $(function(){
        $('.select-all input').on('change', function(){
            if ($(this).prop('checked')) {
                $(this).closest('.select-col').find('input').prop('checked', true)
            } else {
                $(this).closest('.select-col').find('input').prop('checked', false)
            }
        })
        $('.select-col input').on('change', function(){
            if (!$(this).prop('checked')) {
                $(this).closest('.select-col').find('.select-all input').prop('checked', false)
            }
        })
    })
</script>    