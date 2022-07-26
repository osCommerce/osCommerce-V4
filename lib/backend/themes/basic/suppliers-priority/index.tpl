{use class="common\helpers\Html"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="content-container dis_module">
    <!--=== Page Content ===-->
    <div class="row">
        <div class="col-md-12">
            <div class="widget-content">
                {Html::beginForm(['suppliers-priority/index'],'post', ['id'=>'frmMain'])}

                <div id="priorityList">
                </div>
                <div class="btn-bar">
                    <div class="pull-right">
                        {Html::submitButton(TEXT_APPLY,['class'=>'btn btn-primary'])}
                    </div>
                </div>
                {Html::endForm()}
            </div>
        </div>
    </div>
    <!-- /Page Content -->
</div>
