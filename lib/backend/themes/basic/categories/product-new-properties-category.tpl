<div class="widget box box-no-shadow prop-option-{$categories['categories_id']}">
    <div class="widget-header">
        <h4>{$categories['categories_name']}</h4>
        <div class="toolbar no-padding">
            <div class="btn-group">
              <span class="btn btn-xs widget-collapse2"><i class="icon-angle-down"></i></span>
            </div>
        </div>
    </div>
    {foreach $categories['set_values'] as $value}
    <div class="widget-content2 widget-content-bord-bot">
        <div class="widget box box-no-shadow" style="margin-bottom: 0;">
            <div class="widget-header">
                <h4>{$value['properties_name']}</h4>
                <input type="hidden" name="properties_id[{$value['properties_id']}]" value="1" />
                <div class="toolbar no-padding">
                    <div class="prop-box-del" onclick="deleteSelectedProperty(this)"></div>
                    <div class="btn-group">
                      <span class="btn btn-xs widget-collapse1"><i class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div class="widget-content1">
                <div class="tabbable tabbable-custom">
                    <ul class="nav nav-tabs">
                        {foreach $value['description'] as $DKey => $DItem}
                        <li{if $DKey == 0} class="active"{/if}><a href="#tab_6_{$value['properties_id']}_{$DItem['id']}" class="flag-span" data-toggle="tab">{$DItem['logo']}<span>{$DItem['name']}</span></a></li>
                        {/foreach}
                    </ul>
                    <div class="tab-content">
                        {foreach $value['description'] as $DKey => $DItem}
                        <div class="tab-pane{if $DKey == 0} active{/if}" id="tab_6_{$value['properties_id']}_{$DItem['id']}">
                            <div class="w-box-prop-item">
                                {$DItem['content']}
                            </div>
                            {if $DItem['additional_info'] == 1}
                            <label>{$smarty.const.TEXT_ADDITIONAL_INFO}</label>
                            <textarea name="additional_info[{$value['properties_id']}][{$DItem['id']}]" class="form-control">{$DItem['additional_info_data']}</textarea>
                            {/if}
                        </div>
                        {/foreach}
                    </div>                    
                </div> 
            </div>
        </div>
    </div>
    {/foreach}
</div>
<script type="text/javascript">
$(document).ready(function() {
    
    $('.widget .toolbar .widget-collapse1').click(function() {
            var widget         = $(this).parents(".widget");
            var widget_content = widget.children(".widget-content1");
            var widget_chart   = widget.children(".widget-chart");
            var divider        = widget.children(".divider");

            if (widget.hasClass('widget-closed')) {
                    // Open Widget
                    $(this).children('i').removeClass('icon-angle-up').addClass('icon-angle-down');
                    widget_content.slideDown(200, function() {
                            widget.removeClass('widget-closed');
                    });
                    widget_chart.slideDown(200);
                    divider.slideDown(200);
            } else {
                    // Close Widget
                    $(this).children('i').removeClass('icon-angle-down').addClass('icon-angle-up');
                    widget_content.slideUp(200, function() {
                            widget.addClass('widget-closed');
                    });
                    widget_chart.slideUp(200);
                    divider.slideUp(200);
            }
    });
    $('.widget .toolbar .widget-collapse2').click(function() {
            var widget         = $(this).parents(".widget");
            var widget_content = widget.children(".widget-content2");
            var widget_chart   = widget.children(".widget-chart");
            var divider        = widget.children(".divider");

            if (widget.hasClass('widget-closed')) {
                    // Open Widget
                    $(this).children('i').removeClass('icon-angle-up').addClass('icon-angle-down');
                    widget_content.slideDown(200, function() {
                            widget.removeClass('widget-closed');
                    });
                    widget_chart.slideDown(200);
                    divider.slideDown(200);
            } else {
                    // Close Widget
                    $(this).children('i').removeClass('icon-angle-down').addClass('icon-angle-up');
                    widget_content.slideUp(200, function() {
                            widget.addClass('widget-closed');
                    });
                    widget_chart.slideUp(200);
                    divider.slideUp(200);
            }
    });
});
</script>