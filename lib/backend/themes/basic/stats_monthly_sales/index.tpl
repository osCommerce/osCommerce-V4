
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$this->view->headingTitle}</h3>
    </div>
                <div style="float:right">
                {$header_title_additional}
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
                    {$header_title_additional}
									</div>
								</div>
              -->                
							</div>
							<div class="widget-content">
                {$content}
							</div>
						</div>
					</div>
				</div>
        
      