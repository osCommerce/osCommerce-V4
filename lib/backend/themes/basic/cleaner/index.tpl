{use class="\yii\helpers\Html"}{use class="\yii\helpers\ArrayHelper"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<script>
var starten_group = 0;
var groups = new Array();
</script>
<div>
<div class="">
    <div class="col-md-12">
        <div class="widget-content-cleaner">
         <div class="tabbable tabbable-custom" id="tab_1">
                <ul class="nav nav-tabs">
                    <li class="active" data-bs-toggle="tab" data-bs-target="#main_tab"><a><span>Main Configuration</span></a></li>
                    <li data-bs-toggle="tab" data-bs-target="#trash_tab"><a><span>Trash Configuration</span></a></li>
                </ul>          
           <div class="tab-content">
              <div  class="tab-pane active" id="main_tab">
                <ul>
                  {foreach $params['groups'] as $group_id => $groups}
                    <script>groups.push({$group_id});</script>
                    <li data-group="{$group_id}">{$groups['title']}
                      <div id="holder_{$group_id}" class="holder"></div>
                    </li>
                  {/foreach}
                </ul>
              </div> 
              
              <div class="tab-pane" id="trash_tab">
              <table width="100%">
                <tr><td>Trashed Params</td><td>Move back to</td><td></td></tr>
                {foreach $trash['trashed'] as $ttrash}
                  <tr><td><label>{$ttrash['title']}</td><td>{Html::dropDownList('moveto[]', $ttrash['source'], $trash['destination'])}</td><td><a href="{Yii::$app->urlManager->createUrl(['cleaner/move-back', 'id'=>$ttrash['configuration_id']])}" class="btn btn-move">{$smarty.const.IMAGE_MOVE}</a></td></tr>
                {/foreach}
                </table>
              </div>             
          </div>
        </div>          
    </div>

</div>

<script>

loadResult = function(id, href){
      
      if (parseInt(id) > 0){
        $('.holder#holder_'+id).addClass('preloader');
        
        $.get(href, {
            group_id : id
          }, function(data, status){
          if (status == "success") {
            $('.holder#holder_'+id).removeClass('preloader');
            if (data.not_found.length > 0){
              $('.holder#holder_'+id).html("searched: " + data.search.replace(/\|/g, ", ") +"<br>");
              $('.holder#holder_'+id).append(data.not_found);
            } else {
              $('.holder#holder_'+id).append('<b>All Key Founded</b>');
            }
            
            starten_group = groups.shift();
            loadResult(starten_group, href);
            
          } else {
            alert("Request error.");
          }				
        }, 'json');

      }

}

$(document).ready(function(){
	$('.update_config').on('click', function(e){
		var ev = e||window.event;
		ev.preventDefault();
		var href = $(this).attr('href');
		if (href.length > 0 && groups.length > 0){
      starten_group = groups.shift();
      loadResult(starten_group, href);
		}
	});
  
  $('body').on('change', '.move-to', function(){
    var group_id = this.options[this.selectedIndex].value;  
    if (group_id > 0){
     if (confirm('Are you sure to move this KEY to selected group?')){
      var key = $(this).parent().find('.nfdata').attr('data-key');

      var that = this;
      $.post('cleaner/movekey',
        {
          'key' : key,
          'group_id' : group_id
        }, 
        function (data, status){
          if (status == "success") {
            if (data.result){
              alert('Transferred');
              $(that).parents('.row').remove();
            }
          } else {
            alert("Request error.");
          }				          
        },'json'
      )     
     }
    }
  });
  
  $('.btn-move').click(function(e){
    var event = window.event||e;
    event.preventDefault();
    var that = this;
    var href = $(this).attr('href');
    $.post(href, {
      'group_id': $(that).parents('tr').find('select').val(),
      function(data){
        if (window.location.href.indexOf('#trash_tab') == -1){
          window.location.href = window.location.href + '#trash_tab';
          window.location.reload();
        } else {
          window.location.reload();
        }
        
      }
    })
  })
})



function onClickEvent (obj, table){
	var event_id = $(obj).find('input.cell_identify').val();
	 viewModule(event_id);
}

function onUnclickEvent(obj, table){
}

    function resetStatement() {

        //switchOnCollapse('groups_list_box_collapse');
        //switchOffCollapse('groups_management_collapse');

        $('#backup_management_data .scroll_col').html('');
        $('#backup_management').hide();

        var table = $('.table').DataTable();
        table.draw(false);

        $(window).scrollTop(0);

        return false;
    }
</script>
