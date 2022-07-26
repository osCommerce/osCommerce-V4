{foreach $attributes as $option}
<div class="widget box box-no-shadow js-option" data-option_id="{$option['products_options_id']}">
    <input type="hidden" name="products_option_values_sort_order[{$option['products_options_id']}]" value="">
    <div class="widget-header">
        <h4>{$option['products_options_name']}</h4>
        <div class="toolbar no-padding">
            <div class="btn-group">
              <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
            </div>
        </div>
    </div>
    <div class="widget-content">
        <table class="table assig-attr-sub-table attr-option-{$option['products_options_id']}" id="attr-option-{$option['products_options_id']}">
            <thead>
                <tr role="row">
                    <th></th>
                    <th>{$smarty.const.TEXT_LABEL_NAME}</th>
                    <th class="set-ditails">{$smarty.const.TEXT_DEFAULT}</th>
                    <th class="ast-price one-attribute-force inventory-price-title" colspan="2">{$smarty.const.TEXT_PRICE}</th>
                    <th class="set-ditails"></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
{$productNewAttributeIncluded=true }
{include file='./product-new-attribute.tpl'}
{call newOptionValue options=$option['values'] options_templates_id=$options_templates_id products_options_id=$option['products_options_id'] isIncluded=true }

        </tbody>
    </table>
<script type="text/javascript">
$(document).ready(function() {
   $( ".attr-option-{$option['products_options_id']} tbody" ).sortable({
      handle: ".sort-pointer",
      axis: 'y',
     update: function( event, ui ) {
       var order_ids = [''];
       $(this).find('.js-option-value').each(function() {
         order_ids.push($(this).attr('data-option_value_id'));
       });
       order_ids.push('');
       $('.js-option[data-option_id="{$option['products_options_id']}"]').find('input[name="products_option_values_sort_order[{$option['products_options_id']}]"]').val(order_ids.join(','));
     }
   });
   

    $('.widget .toolbar .widget-collapse').click(function() {
            var widget         = $(this).parents(".widget");
            var widget_content = widget.children(".widget-content");
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
    </div>
</div>
{/foreach}