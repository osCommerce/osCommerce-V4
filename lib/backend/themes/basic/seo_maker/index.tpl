
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
										<span id="catalog_list_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
									</div>
								</div>
              -->                
							</div>
							<div class="widget-content">
              <div class="alert fade in" style="display:none;">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce"></span>
              </div>                
              {$this->view->content}

                                                                <p class="btn-toolbar">
                                                                    <input type="button" class="btn btn-primary" value="Update" onClick="return updateSeoDetals()">
                                                                </p>
                                                                  <script type="text/javascript">
function updateSeoDetals() {
  $.post("seo_maker/update", $('form[name=seo_maker]').serialize(), function(data, status){
    if (status == "success") {
      $('.alert #message_plce').html('');
      $('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
    } else {
        alert("Request error.");
    }
},"json");
  return false;
}
                                    
                                                                  </script>
                </form>
							</div>
						</div>
					</div>
				</div>