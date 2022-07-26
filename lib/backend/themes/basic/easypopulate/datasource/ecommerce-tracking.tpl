{use class="common\helpers\Html"}
{use class="backend\models\EP\Datasource\Google"}
{assign var="customUniqId" value="0"}
{$customUniqId=$customUniqId+1}
<div class="scroll-table-workaround" id="ep_datasource_config">
    <div class="widget box">
        <div class="widget-header">
            <h4>Delays</h4>
            <div class="toolbar no-padding">
              <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
              </div>
            </div>
        </div>
        <div class="widget-content">
            <div class="w-line-row w-line-row-1">
                <div class="wl-td">
                    <label>Skip new orders for (h):</label> {Html::textInput('datasource['|cat:$code|cat:'][delays][latencity]', $delays['latencity'])}
                </div>
            </div>
            <div class="w-line-row w-line-row-1">
                <div class="wl-td">
                    <label>Check orders not older (days):</label> {Html::textInput('datasource['|cat:$code|cat:'][delays][outdated]', $delays['outdated'])}
                </div>
            </div>
        </div>
    </div>


    <div class="widget box">
        <div class="widget-header">
            <h4>Orders</h4>
            <div class="toolbar no-padding">
              <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
              </div>
            </div>
        </div>
        <div class="widget-content">
            <div class="w-line-row w-line-row-1">
                <div class="wl-td">
                    <label>Export Order statuses:</label> {Html::dropDownList('datasource['|cat:$code|cat:'][order][export_statuses]', $order['export_statuses']['value'], $order['export_statuses']['items'], $order['export_statuses']['options'])}
                </div>
            </div>

        </div>
    </div>

</div>
<script type="text/javascript">
    $('.js-status-map').on('change',function(event){
        var $target = $(event.target);
        if ( $target.val().indexOf('create_')===0 ) return;
        $('.js-status-map').not($target).each(function () {
            if ( $(this).val()==$target.val() ) {
                $(this).val('');
            }
        });
    });
    $('.default_switcher').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px',
        onSwitchChange: function () {
            var $holder = $(this).parents('.js-switch-holder');
            if ($holder.hasClass('js-switch-one-on')) {
                $holder.find('input[type="checkbox"]').not(this).each(function () {
                    if ($(this).is(':checked')) {
                        this.checked = false;
                        $(this).bootstrapSwitch('state', this.checked, true);
                    }
                });
            }
            if ($(this).hasClass('js-soap_custom_flags') || $(this).hasClass('js-soap_main_flags')) {
                if ($(this).is(':checked')) {
                    $('.' + $(this).attr('data-rel')).show();
                } else {
                    if ($(this).hasClass('js-soap_main_flags')) {
                        $('.' + $(this).attr('data-rel') + ' .js-soap_custom_flags').each(function () {
                            if ($(this).is(':checked')) {
                                $(this).trigger('click');
                            }
                        });
                    }
                    $('.' + $(this).attr('data-rel')).hide();
                }
            }
        }
    });
    $('.js-soap_custom_flags, .js-soap_main_flags').each(function(){
        if (!this.checked && $(this).attr('data-rel')) $('.'+$(this).attr('data-rel')).hide();
    });

    $('.js-switch-one-on').each(function(){
        var $selectGroup = $(this);
        var $selects = $selectGroup.find('select');
        if ( $selects.length==0 ) return;
        $selects.on('change',function() {
            if ( $(this).val()!='disabled' ) $selects.not(this).val('disabled');
        })
    });

    $('#ep_datasource_config .widget-collapse').off('click').on('click', function() {
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
    //$('#ep_datasource_config .widget-collapse:gt(0)').trigger('click');

</script>