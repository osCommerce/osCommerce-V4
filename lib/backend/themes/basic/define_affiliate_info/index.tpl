<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$this->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<div class="row" id="send_gift" style="_display: none;">
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i><span id="easypopulate_download_files_title">Edit Affiliate info</span>
                </h4>

                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span id="easypopulate_download_files_collapse" class="btn btn-xs widget-collapse"><i
                                    class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div class="widget-content fields_style" id="easypopulate_download_files_data">
                <form name="form_1" enctype="multipart/form-data" action="{$this->view->action_1}" method="POST">
                        <div align = "left">
                           

                            

                                <input type="submit" class="btn btn-primary" value="Save" >

                        </div>
                </form>
            </div>
        </div>
    </div>
</div>
