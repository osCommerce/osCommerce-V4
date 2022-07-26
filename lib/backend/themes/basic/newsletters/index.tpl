{use class="yii\helpers\Html"}
{use class="common\helpers\Acl"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<!--=== Page Content ===-->
<div class="widget-content">
    <form action="{$form_action}" method="post" id="frmConfig">
    <div class="tabbable tabbable-custom">
        {if $isMultiPlatform}
            <ul class="nav nav-tabs -tab-light-gray">
                {foreach $platforms as $platform}
                    <li {if $selected_platform_id==$platform['id']} class="active"{/if}><a class="js_link_platform_select" href="#pl_{$platform['id']}" data-toggle="tab" data-platform_id="{$platform['id']}" {if $platform['id']==$selected_platform_id} onclick="return false" {/if}><span>{$platform['text']}</span></a></li>
                {/foreach}
            </ul>
        {/if}
        <div class="tab-content {if $isMultiPlatform}tab-content1{/if}">
            {foreach $platforms as $platform}
                <div id="pl_{$platform['id']}" class="tab-pane {if $selected_platform_id==$platform['id']}active{/if}">
                    
                    {if $NewslettersClass = Acl::checkExtensionAllowed('Newsletters', 'allowed')}
                        {$NewslettersClass::viewPlatformConfigEdit($platform['id'])}
                    {/if}
                    <br>
                    {*
                    <div class="widget box">
                        <div class="widget-header">
                            <h4><span>{$smarty.const.HEADING_TRUSTPILOT_EXPORT}</span></h4>
                        </div>
                    </div>
                    *}
                </div>
            {/foreach}
        </div>
    </div>
    <div class="btn-bar">
        <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
    </div>
    </form>
</div>
<div id="bExport" style="display: none;"></div>
<!--=== Page Content ===-->
<script type="text/javascript">
    $(document).ready(function(){
        $( ".datepicker" ).datepicker({
            changeMonth: true,
            changeYear: true,
            showOtherMonths:true,
            autoSize: false,
            dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}',
            onSelect: function() { 
                if ($(this).val().length > 0) { 
                    $(this).siblings('span').addClass('active_options');
                }else{ 
                    $(this).siblings('span').removeClass('active_options');
                }
            }
        });
        $('.js-export_orders').on('click',function(){
            var target_platform_id = $(this).attr('data-platform_id');
            $('#bExport').html('<form id="frmExport" target="_blank" action="{$urlExport}&platform_id='+target_platform_id+'" method="post"></form>');
            var data = $('#frmConfig [name^="export['+target_platform_id+']"]').serializeArray();
            for( var i=0; i<data.length; i++ ) {
                $('#frmExport').append('<input type="hidden" name="'+data[i].name+'" value="'+data[i].value+'">');
            }
            $('#frmExport').trigger('submit');
            return false;
        });
        
        var check_date_ctrl = function(event)
        {
            
        }
        $('.js-disable-date-ctrl').on('click',check_date_ctrl);
    });
</script>