<i class="icon-download"></i><span id="easypopulate_download_files_title">{$smarty.const.TITLE_SELECT_EXPORT_FIELDS}</span>

<div class="scroll-table-workaround">
    <table class="js-export_columns table -table-striped -table-hover -table-responsive -table-ordering -no-footer">
        <thead>
        <tr>
            <th width="30">{Html::checkbox('select_all')}</th>
            <th>{$smarty.const.TEXT_FILE_FIELD_TITLE}</th>
        </tr>
        </thead>
    </table>
</div>
<div class="tl_filters_title tl_filters_title_border"></div>
<div class="row">
    <div class="col-md-5">Save As Custom export:</div>
    <div class="col-md-7"><input name="customer_type" value="" class="form-control" placeholder="Export name"></div>
</div>
<div class="btn-bar">
    <div class="btn-left"><a class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a></div>
    <div class="btn-right">
        <!--label>{Html::checkbox('remember_choice')} {$smarty.const.TEXT_REMEMBER_EXPORT_FIELDS}</label-->
        <button class="btn btn-primary js-confirm-fields">{$smarty.const.IMAGE_DOWNLOAD}</button>
    </div>
</div>
