{\backend\assets\ChartAsset::register($this)|void}
<div class="widget box">
    <div class="widget-header">
        <h4><i class="icon-area-chart"></i> {$smarty.const.TEXT_SALES_MONTH}</h4>
        <div class="toolbar no-padding">
            <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
            </div>
        </div>
    </div>
    <div class="widget-content">
        <div class="row">
            <div class="col-md-12">
                <div id="chart_multiple" class="chart"></div>
            </div>
        </div>
    </div>
    <div class="divider"></div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var series_multiple = [
            {
                label: "{$smarty.const.TEXT_TOTAL_ORDERS}",
                data: data_blue2,
                color: '#0060be',
                lines: {
                    fill: true,
                    fillColor: { colors: ['rgba(148,175,252,0.1)', 'rgba(148,175,252,0.45)']}
                },
                points: {
                    show: true
                },
                hideLegend: true,
                yaxis: 1
            }, {
                label: "{$smarty.const.TEXT_TOTAL_ORDERS}",
                data: data_blue,
                color: '#0060bf',
                lines: {
                    fill: true,
                    fillColor: { colors: ['rgba(148,175,252,0.1)', 'rgba(148,175,252,0.45)']}
                },
                points: {
                    show: true
                },
                yaxis: 1
            }, {
                label: "{$smarty.const.TEXT_AVERAGE_ORDER_AMOUNT}",
                data: data_green2,
                hideLegend: true,
                color: '#1bb900',
                yaxis: 2,
            }, {
                label: "{$smarty.const.TEXT_AVERAGE_ORDER_AMOUNT}",
                data: data_green,
                color: '#1bb901',
                yaxis: 2,
            }, {
                label: "{$smarty.const.TEXT_TOTAL_AMOUNT}",
                data: data_red2,
                hideLegend: true,
                color: '#f43c10',
                yaxis: 3
            }, {
                label: "{$smarty.const.TEXT_TOTAL_AMOUNT}",
                data: data_red,
                color: '#f43c11',
                yaxis: 3
            }
        ];
        var someFunc = function (val, axis) {
            return "{$currcode_left}" + Math.ceil(val) + "{$currcode_right}" + '<span class="sep">/</span>';
        }
        var someFunc1 = function (val, axis) {
            return Math.ceil(val);
        }
        // Initialize flot
        var plot = $.plot("#chart_multiple", series_multiple, $.extend(true, { }, Plugins.getFlotDefaults(), {
            yaxes: [{
                position: 'right',
                tickFormatter: someFunc1,
                min: 0,
                ticks: false
            }, {
                position: 'left',
                alignTicksWithAxis: 1,
                tickFormatter: someFunc,
                color: 'transparent',
                axisMargin: 100,
                min: 0
            }, {
                position: 'left',
                alignTicksWithAxis: 1,
                tickFormatter: someFunc,
                color: 'transparent',
                min: 0

            }],
            xaxis: {
                mode: "time",
                timeformat: "%b"
            },
            series: {
                lines: { show: true, lineWidth: 2},
                points: { show: true},
                grow: { active: false},
                dashes: {
                    show: true,
                    lineWidth: 2,
                    dashLength: 5,
                    toColor: ['#0060be', '#1bb900', '#f43c10']
                }
            },
            grid: {
                hoverable: true,
                clickable: true,
                axisMargin: -10
            },
            tooltip: true,
            tooltipOpts: {
                content: '%s: %y %x'
            },

            legend: {
                labelFormatter: function (label, series) {
                    if (series.hideLegend) {
                        return null;
                    } else {
                        return label;
                    }
                }
            }

        }));
    });
</script>