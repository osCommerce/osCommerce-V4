{use class="yii\helpers\Url"}
<div class="widget box widget-closed">
    <div class="widget-header">
        <h4><i class="icon-gear"></i><span>XML Projects</span></h4>
        <div class="toolbar no-padding">
            <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
            </div>
        </div>
    </div>
    <div class="widget-content" id="blkIoProject">
        <div>
            <table id="tblIoProjects" class="ep-file-list table table-striped table-selectable table-checkable table-hover table-responsive table-bordered -datatable tab-cust tabl-res double-grid"
                   checkable_list="" data_ajax="io-projects-list">
                <thead>
                <tr>
                    <th>Project Code</th>
                    <th>{$smarty.const.TABLE_HEADING_ACTION}</th>
                </tr>
                </thead>
            </table>
        </div>
        <div class="row">
            <div class="col-md-12 text-right">
                <button type="button" class="btn btn-primary js-io-project-create">Create new project</button>
            </div>
        </div>
    </div>
</div>
<script type="text/html" id="frmIoProjectForm">
    <div class="row">
        <div class="col-md-4">Project Code</div>
        <div class="col-md-8"><input type="text" name="project_code" class="form-control"></div>
    </div>
    <div class="row">
        <div class="col-md-4">Description</div>
        <div class="col-md-8"><input type="text" name="description" class="form-control"></div>
    </div>
    <div class="row">
        <div class="col-md-4">Is local?</div>
        <div class="col-md-8"><select name="is_local" class="form-control"><option value="1">Yes</option><option value="0">No</option></select></div>
    </div>
    <input type="hidden" name="project_id">
</script>

<script type="text/javascript">
    $(document).ready(function() {
        var table = $('#tblIoProjects').DataTable({
            "serverSide": true,
            "processing": true,
            "ajax": {
                "url": '{Url::to('easypopulate/io-projects-list')}',
                "data": function (data, settings) {
                    /*data.directory_id = $('#tblFiles').attr('data-directory_id');*/
                }
            },
            "ordering": false
        });

        $('#frmIoProjectForm').on('form_open',function( event, data ){
            var $_self;
            var dialog = bootbox.dialog({
                show:false,
                animate:false,
                message: $('#frmIoProjectForm').html(),
                buttons: {
                    confirm: {
                        label: '{$smarty.const.TEXT_UPDATE|escape:'javascript'}',
                        className: 'btn-success',
                        callback: function() {
                            $.post('{Url::to('easypopulate/io-project')}', $_self.find(':input').serializeArray(), function(data){
                                $(dialog).modal('hide');
                                //$('#frmIoProjectForm').trigger('form_open',[data.formData]);
                            });
                        }
                    },
                    cancel: {
                        label: '{$smarty.const.IMAGE_CANCEL|escape:'javascript'}',
                        className: 'btn-danger'
                    }
                }
            }).on("show.bs.modal", function() {
                var dialogData = data || { };
                $_self = $(this);
                for(var field in dialogData) {
                    if ( !dialogData.hasOwnProperty(field) ) continue;
                    $_self.find('[name="'+field+'"]').val(dialogData[field]);
                }
                //$(this).find();;
            }).modal('show');
        });

        $('.js-io-project-create').on('click',function(){
            $('#frmIoProjectForm').trigger('form_open');
        });

        $(document).on('click','.js-project-edit',function(event){
            var $_target = $(event.currentTarget);
            var project_id = $_target.data('project_id');
            $.getJSON('{Url::to('easypopulate/io-project')}',{ 'project_id': project_id }, function(data){
                $('#frmIoProjectForm').trigger('form_open',[data.formData]);
            });
            return event.stopPropagation();
        });


    });
</script>