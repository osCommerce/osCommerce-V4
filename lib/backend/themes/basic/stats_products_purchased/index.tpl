
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$this->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
           <!--=== Page Content ===-->
				<div class="row">
					<div class="col-md-12">
						<div class="widget box">
							<div class="widget-header">
								<h4><i class="icon-reorder"></i> {$this->view->headingTitle}</h4>
                <!--                
								<div class="toolbar no-padding">
									<div class="btn-group">
										<span id="stats_list_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
									</div>
								</div>
              -->                
							</div>
							<div class="widget-content">
								<table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="0,1,2" data_charts ="chart-canvas" data_ajax="stats_products_purchased/list">
									<thead>
										<tr>
                                                                                    {foreach $this->view->productsTable as $tableItem}
                                                                                        <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                                                                                    {/foreach}
										</tr>
									</thead>
									
								</table>

							</div>
						</div>
					</div>
				</div>
        
        <h3>{$this->view->headingTitle} - Pie chart</h3>
        <canvas id="chart-canvas" width="400" height="400" style="float:left;"></canvas>
        <div id="chart-legend" width="400" height="400"  style="float:left;"></div>
        {if {$this->view->productsTable|@count} > 0}
        <script type="text/javascript">
          var rData = [];
          var ctx = $("#chart-canvas").get(0).getContext("2d");
          var options = {
            animateRotate : true,
            animationEasing: "easeInOutCirc",
            legendTemplate: ''
          };
          
          var chartContainer;
          
          
          onDraw = function(data){
            if (typeof chartContainer == 'object') chartContainer.destroy();
            var legend = '<ul>';
            rData = [];
            $.each(data, function(i,e ){
              rData.push({
                         value: e[2],
                         label: e[1],
                         color: "#FDB45C",
                         highlight: "#FFC870",                         
                        });
              legend += '<li>' + e[1] + ': <b>' + e[2] + '</b> purchased</li>';
            });
            legend += '</ul>';
            
            options.legendTemplate = legend;

            chartContainer = new Chart(ctx).PolarArea(rData, options); 
            var _l = chartContainer.generateLegend();
            $('#chart-legend').html('').append(_l);
          }

         function onClickEvent(obj, table) {
         }
         
         function onUnclickEvent (obj, table){
         }
         
        </script>
        {/if}