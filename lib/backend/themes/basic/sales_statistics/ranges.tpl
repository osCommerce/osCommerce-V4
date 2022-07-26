{use class="\yii\helpers\Html"}
<div class="wl-td">
 <label>{$smarty.const.TEXT_RANGE}:</label>
 {Html::dropDownList('range', $current_range, $range, ['class' =>'form-control'])}
</div>

<script>
  $(document).ready(function(){
    changeRange = function(){
        var current_range = $('select[name=range]').val();
        var type = $('select[name=type]').val();

         $.get('sales_statistics/load-options',
            {
                'range': current_range,
                'type': type,
                {if isset($smarty.get.start_custom)}
                'start_custom' : '{$smarty.get.start_custom}',
                {/if}

                {if isset($smarty.get.end_custom)}
                'end_custom' : '{$smarty.get.end_custom}',
                {/if}

                {if isset($smarty.get.day)}
                'day' : '{$smarty.get.day}',
                {/if}

                {if isset($smarty.get.day_cmp)}
                'day_cmp' : '{$smarty.get.day_cmp}',
                {/if}

                {if isset($smarty.get.year)}
                'year' : '{$smarty.get.year}',
                {/if}

                {if isset($smarty.get.year_cmp)}
                'year_cmp' : '{$smarty.get.year_cmp}',
                {/if}

                {if isset($smarty.get.week)}
                'week' : '{$smarty.get.week}',
                {/if}

                {if isset($smarty.get.week_cmp)}
                'week_cmp' : '{$smarty.get.week_cmp}',
                {/if}

                {if isset($smarty.get.month_year)}
                'month_year' : '{$smarty.get.month_year}',
                {/if}

                {if isset($smarty.get.month_year_cmp)}
                'month_year_cmp' : '{$smarty.get.month_year_cmp}',
                {/if}

                {if isset($smarty.get.start_custom_quarter)}
                'start_custom_quarter' : '{$smarty.get.start_custom_quarter}',
                {/if}

                {if isset($smarty.get.end_custom_quarter)}
                'end_custom_quarter' : '{$smarty.get.end_custom_quarter}',
                {/if}

                {if isset($smarty.get.start_custom_year)}
                'start_custom_year' : '{$smarty.get.start_custom_year}',
                {/if}

                {if isset($smarty.get.end_custom_year)}
                'end_custom_year' : '{$smarty.get.end_custom_year}',
                {/if}
            },
            function(data, status){
                if (status == 'success'){
                    $('.report-details-options').html(data.options);
                    if (Array.isArray(data.undisabled)){
                        if (data.undisabled.indexOf($('select[name=type]').val()) == -1){
                            $('input[name="chart_group_item[orders_avg]"]').attr('disabled', true);
                            $('input[name="chart_group_item[total_avg]"]').attr('disabled', true);
                            $('input[name="chart_group_item[orders_avg]"]').prop('checked', false);
                            $('input[name="chart_group_item[total_avg]"]').prop('checked', false);
                            $('input[name="chart_group_item[orders_avg]"]').next().css('background-color', '#caccd3');
                            $('input[name="chart_group_item[total_avg]"]').next().css('background-color', '#caccd3');
                        } else {
                            $('input[name="chart_group_item[orders_avg]"]').attr('disabled', false);
                            $('input[name="chart_group_item[total_avg]"]').attr('disabled', false);
                            $('input[name="chart_group_item[orders_avg]"]').next().css('background-color', $('input[name="chart_group_item[orders_avg]"]').next().attr('data-color'));
                            $('input[name="chart_group_item[total_avg]"]').next().css('background-color', $('input[name="chart_group_item[total_avg]"]').next().attr('data-color'));

                            $settings.setDependance();
                        }
                    }
                }
            }, 'json');
    }
    changeRange();
    $('select[name=range]').change(function(){
        changeRange();
    })
  })
</script>