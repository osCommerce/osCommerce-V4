{use class="yii\helpers\Html"}
<style>
    .apps-marketplace-filter {
        display: block;
    }
</style>
<form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
<div class="widget box box-wrapp-blue filter-wrapp apps-marketplace-filter">
    <div class="widget-header filter-title">
        <h4>{$smarty.const.TEXT_FILTER}</h4>
		<div class="pull-right">
			<div class="filters_btn">
				<a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>
			</div>
			<span class="search-title">{$smarty.const.TEXT_SEARCH}</span>
			<div class="search-box">
				<input type="text" name="search" value="{$app->controller->view->filters->search}" class="form-control" />
				<button type="submit" class="btn btn-primary search-icon"></button>
			</div>
		</div>	
    </div>
    
</div>
{Html::hiddenInput('set', $selectedRootDirectoryId)}
{Html::hiddenInput('sort_by', 'installed')}
{Html::hiddenInput('start', 0)}
</form>
                
<div class="widget box">
    <div class="widget-header">
        <h4><i class="icon-upload"></i><span id="easypopulate_upload_files_title">{$smarty.const.UPLOAD_APP_STORE}</span></h4>
    </div>
    <div class="widget-content" id="store_box">
        

    </div>
</div>

        

<style>
.ep-file-list a.remove-ast{ text-decoration: none; }
a.job-button{ text-decoration: none; margin: 0 4px; font-size: 1.1em; }
a.job-button:hover{ text-decoration: none; }
.job-button .icon-trash{ color: #ff0000 }
.job-button .icon-cog, .job-button .icon-reorder{ color: #008be8 }
.job-button .icon-play{ color: #006400 }
</style>
<script type="text/javascript">
$(".js_type_checkboxes").on('change', function(){
   if( $(this).is(':checked') ) {
      $(this).attr('checked', 'checked');
   } else {
       $(this).removeAttr('checked');
   }
   applyFilter();
});
$('.radioFilter label').on('click', function(){
    $('.radioFilter label.active').removeClass('active');
    $(this).addClass('active');
});
$('.radioFilter label').prepend('<span></span>');
  
$('.radioFilter input').each(function(){
$(this).closest('label').addClass($(this).val());
})
function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
}
function resetFilter() {
    $('input[name="search"]').val('');
    $('.js_type_checkboxes').prop("checked", false);
    $('input[name="sort_by"][value=""]').click();
    applyFilter();
}
function applyFilter() {
    $('input[name="start"]').val(0);
    setFilterState();
    $.post("{$store_list_url}", $('#filterForm').serialize() , function(data, status) {
        if (status == "success") {
            $('#store_box').html(data);
        }
    },'html');
    return false;    
}
function setPage(start) {
    $('input[name="start"]').val(start);
    setFilterState();
    $.post("{$store_list_url}", $('#filterForm').serialize() , function(data, status) {
        if (status == "success") {
            $('#store_box').html(data);
        }
    },'html');
    return false;  
}
function file_upload(id) {
    $.get('{$app->urlManager->createUrl('install/upload-file-info')}', { id: id }, function(data) {
         bootbox.dialog({
            message: '<div class="installPopupArea">'+data+'</div>',
            title: "Application info",
        });
     }, 'html');
    return false;
}
function fileUploadStart (form) {
    $('body').append('<span class="loader"></span>');
    $(".bootbox-close-button.close").click();
    $.post('{$app->urlManager->createUrl('install/upload-file')}', $(form).serialize(), function(data) {
         bootbox.dialog({
            message: '<div class="installPopupArea">'+data+'</div>',
            title: "Application info",
        });
		$('.loader').remove();
        applyFilter();
     }, 'html');
    return false;
}

$('#tblStore').on('reload',function(event, resetPage) {
    if (typeof resetPage === 'undefined') resetPage = false;
    $('#tblStore').DataTable().ajax.reload(null,resetPage);
});

var delayTimer;

$(document).ready(function(){
    
    applyFilter();
    
    var $types = $('.js_type_checkboxes');
    var check_type_checkboxes = function(){
        var checked_all = true;
        $types.not('[value=""]').each(function () {
            if (!this.checked) checked_all = false;
        });
        $types.filter('[value=""]').each(function() {
            this.checked = checked_all
        });
    };
    check_type_checkboxes();
    
    $('input[name="search"]').on('keyup', function () {
        clearTimeout(delayTimer);
        delayTimer = setTimeout(function() {
            applyFilter();
        }, 1000);
    });
});
</script>
