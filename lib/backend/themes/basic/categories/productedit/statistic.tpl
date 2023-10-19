{use class="\common\classes\Images"}
<div class="widget box box-no-shadow" style="margin-bottom: 0;">
  <div class="widget-header">
    <h4><span class="click-main">{$pInfo->products_name}</span></h4>
  </div>
  <div class="widget-content">
    <div class="widget-content-stat after">
      <div class="stat-img">
        <span class="click-images"><img src="{Images::getImageUrl($pInfo->products_id, 'Small')}" /></span>
        <div>
          <label>{$smarty.const.TEXT_PRODUCTS_PRICE_INFO}</label><span class="click-price">{$app->controller->view->statistic->price}</span>
        </div>
        <div>
          <label>{$smarty.const.TEXT_TOTAL_VIEW}</label>{$app->controller->view->statistic->products_viewed}
        </div>
        <div>
          <label>{$smarty.const.TEXT_DATE_ADDED}</label>{$app->controller->view->statistic->products_date_added}
        </div>
        <div>
          <label>{$smarty.const.TEXT_LAST_MODIFIED}</label>{$app->controller->view->statistic->products_last_modified}
        </div>
        {if $app->controller->view->showInventory == true}
          <div class="st-t-pe after">
            <table class="table">
              <thead>
              <tr>
                <th>{$smarty.const.TEXT_INVENTORY}</th>
                <th>{$smarty.const.TEXT_LEGEND_PRICE}</th>
              </tr>
              </thead>
              <tbody>
              {foreach $app->controller->view->statistic->inventory as $Item}
                <tr>
                  <td>{$Item.label}</td>
                  <td>{$Item.price}</td>
                </tr>
              {/foreach}
              </tbody>
            </table>
          </div>
        {/if}
      </div>
      <div class="widget box box-no-shadow">
        <div class="widget-header" style="overflow: hidden; *height: 1%;">
          <h4>{$smarty.const.TEXT_PURCHASES}</h4>
        </div>
        <div class="widget-content">
          <div id="chart_multiple" class="chart"></div>
          <script>
            var someFunc = function(val, axis){
              return "&pound;" + Math.ceil(val) + '<span class="sep">/</span>';
            }
            var someFunc1 = function(val, axis){
              return Math.ceil(val);
            }

            var data_total= [ {$app->controller->view->statistic->orderedGrid} ];

            $(document).ready(function(){
              var series_multiple1 = [
                {
                  label: "Total ordered",
                  data: data_total,
                  color: '#0060bf',
                  lines: {
                    fill: true,
                    fillColor: {  colors: ['rgba(148,175,252,0.1)', 'rgba(148,175,252,0.45)'] }
                  },
                  points: {
                    show: true
                  },
                  yaxis : 1
                }
              ];
              //$('[data-bs-target]').on('click', initPlot);
              function initPlot(){
                if (typeof($('#chart_multiple:visible')) != 'undefined' && $('#chart_multiple:visible').length && $('#chart_multiple').width() && $('#chart_multiple').height()) {
                  var plot1 = $.plot("#chart_multiple", series_multiple1, $.extend(true, { }, Plugins.getFlotDefaults(), {
                    yaxes: [ {
                      position : 'left',
                      tickFormatter: someFunc1,
                    } ],
                    xaxis: {
                      mode: "time"
                    },
                    series: {
                      lines: { show: true },
                      points: { show: true },
                      grow: { active: true }
                    },
                    grid: {
                      hoverable: true,
                      clickable: true,
                      axisMargin: -10
                    },
                    tooltip: true,
                    tooltipOpts: {
                      content: '%s: %y'
                    }
                  }));
                }
              }
              initPlot();

            });
          </script>
        </div>
      </div>
      <div class="widget box box-no-shadow" style="margin-bottom: 0;">
        <div class="widget-header" style="overflow: hidden;">
          <h4>{$smarty.const.TEXT_PRODUCTS_PRICE_INFO}</h4>
        </div>
        <div class="widget-content">
          <div id="chart_multiple_price" class="chart"></div>
          <script>
            var data_price= [ {$app->controller->view->statistic->priceGrid} ];
            $(document).ready(function(){

              var series_multiple2 = [
                {
                  label: "Price cost",
                  data: data_price,
                  color: '#0060bf',
                  lines: {
                    fill: true,
                    fillColor: {  colors: ['rgba(148,175,252,0.1)', 'rgba(148,175,252,0.45)'] }
                  },
                  points: {
                    show: true
                  },
                  yaxis : 1
                }
              ];

              // Initialize flot

              //$('[data-bs-target]').on('click', initPlot1);
              function initPlot1(){
                if (typeof($('#chart_multiple:visible')) != 'undefined' && $('#chart_multiple:visible').length && $('#chart_multiple').width() && $('#chart_multiple').height()) {
                  var plot2 = $.plot("#chart_multiple_price", series_multiple2, $.extend(true, { }, Plugins.getFlotDefaults(), {
                    yaxes: [ {
                      position : 'left',
                      tickFormatter: someFunc1,
                    } ],
                    xaxis: {
                      mode: "time"
                    },
                    series: {
                      lines: { show: true },
                      points: { show: true },
                      grow: { active: true }
                    },
                    grid: {
                      hoverable: true,
                      clickable: true,
                      axisMargin: -10
                    },
                    tooltip: true,
                    tooltipOpts: {
                      content: '%s: %y'
                    }

                  }));
                }
              }
              initPlot1();
            });
          </script>
        </div>
      </div>
    </div>
  </div>
</div>