{use class="common\helpers\Html"}
<style>
  .address-format-rows .row.address-row{ min-height:50px; position:relative;border: 2px dotted #ccc;padding-top: 3px; }  
  .item, .teplate-item{ border: 1px solid #ccc;padding: 10px;display:inline-block!important; }
  .item{
      margin: 0 5px 0 5px;
      cursor: pointer;
  }
  .rows{ padding: 10px; }
  #formatsList{ padding-top:10px; }
  .remove:before { content:'\f014';font-family:FontAwesome;position: absolute; top: -10px;right: -5px; }
  .add-row { float:right; }
  .address-holder .rows{  }
  .btn-remove {
      margin-right: 20px;
      cursor: pointer;
      color: var(--color-danger)
  }
</style>
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="content-container">
    <!--=== Page Content ===-->
    <div class="row">
        <div class="col-md-12">
            <div class="widget-content">
                {foreach \common\helpers\Address::$allowed_fields as $field}
                    <div class="teplate-item">{$field}</div>
                {/foreach}
                
                {Html::beginForm(['address-formats/index'],'post', ['id'=>'frmMain'])}
                <div id="formatsList">
                
                {foreach $formats as $format}                    
                    {include file="format.tpl"}
                {/foreach}
                </div>                
                <div class="btn-bar">
                    <div class="pull-left">
                        {Html::a(TEXT_NEW, 'javascript:void(0);', ['class'=>'btn btn-primary new-format'])}
                    </div>
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
<script type="text/javascript">
    $(document).ready(function(){
        
        $('body').on('click', '.add-row', function(e){
            e.preventDefault();
            let box = $(this).prev();
            let row = document.createElement('div');
            row.className = 'rows';
            row.setAttribute('data-row', $('.rows',  box).length);
            setHoverRow($(row));
            let subrow = document.createElement('div');
            subrow.className = 'row address-row';
            row.append(subrow);
            setSortable($(subrow));            
            box.append(row);
        })
        
        setSortable = function(obj){
            obj.sortable({
                axis: 'x',
                //forceHelperSize: true,
                beforeStop:function(event, ui){                    
                    $(':hidden', ui.item[0]).attr('name', 'formats['+currentHanle+']['+$(ui.item[0]).closest('.rows').data('row')+'][]');
                },
                update:  function(event, ui){ 
                    $(ui.item[0]).css('position', 'relative');
                    setDraggable($('.item'));
                }
            });
        }
        
        setSortable($('.row.address-row'));
        
        sortItems = function (holder, index){
            let currentHolderId = $(holder).closest('.address-format-rows').data('format-id');
            if (currentHolderId != undefined){
                $.each($(holder).find('input:hidden'), function(i, e){
                    $(e).attr('name', 'formats['+currentHolderId+']['+index+'][]');
                })
            }
        }
        
        setSortableRows = function(obj){
            obj.sortable({
                axis: 'y',
                update:  function(event, ui){ 
                    let row = ui.item[0];                    
                    if ($(row).hasClass('rows')){
                        $.each($(row).closest('.address-holder').find('.rows'), function(i, e){                            
                            $(e).attr('data-row', $(e).index());
                            sortItems(e, $(e).index());
                        })
                        
                    }                    
                }
            });
        }
        
        setSortableRows($('.address-holder'));
        
        setDraggable = function(obj){
            obj.draggable({
                connectToSortable: '.row.address-row',
                appendTo: ".address-format-rows .row.address-row",
            });
        }
        
        setDraggable($('.item'));
        
        renderDelete = function(obj){
            $(obj).find('span.remove').remove();
            $(obj).prepend('<span class="remove"></div>');
            $('body').on('click', '.remove', function(){
                $(this).parent().remove();
            })
        }        
        
        renderDelete($('.item'));
        
        var currentHanle = null;
       
        setHoverBox = function(obj){
            obj.hover(function(e){
                currentHanle = $(e.target).parents('.address-format-rows').data('format-id') || $(e.target).data('format-id');             
            }, function(){            
            })
        }
        
        setHoverBox($('.address-format-rows'));
        
        var currentRow = null;
        
        setHoverRow = function(obj){            
            obj.hover(function(e){                
                currentRow = $(e.target).parents('.rows').data('row') || $(e.target).data('row');
            })
        }
        setHoverRow($('.rows'));
        
        $('.teplate-item').draggable({
            //appendTo: ".address-format-rows",            
            helper:'clone',
            stop: function(event, ui){
                setTimeout(function(){                    
                    if (currentRow == undefined) currentRow = 0;
                    console.log(currentHanle, currentRow);
                    if (currentHanle != undefined && currentRow != undefined){                        
                        
                        let item = document.createElement('div');
                        item.className = "item";                        
                        item.innerText=ui.helper[0].innerText;
                        let hidden = document.createElement('input');
                        hidden.type='hidden';                        
                        hidden.name = 'formats['+currentHanle+']['+currentRow+'][]';
                        hidden.value = item.innerText;
                        item.append(hidden);
                        renderDelete(item);
                        $('.address-format-rows[data-format-id='+currentHanle+'] .rows[data-row='+currentRow+'] .row').append(item);                        
                        setDraggable($('.item'));
                        
                    }
                }, 100);
                
            }            
        })
        
        $('body').on('click', '.btn-remove', function(){
            let holder = $(this).closest('.format-box ');
            bootbox.confirm({
                message: "{$smarty.const.CONFIRMATION_DELETE_FORMAT}",
                buttons: {
                    confirm: {
                        label: '{$smarty.const.YES}',
                        className: 'btn-primary'
                    },
                    cancel: {
                        label: '{$smarty.const.NO}',
                        className: 'btn-cancel'
                    },
                },
                callback: function (result) {
                    if (result){
                        $(holder).remove();
                    }                    
                }
            })
        })
        
        $('body').on('click', '.icon-pencil', function(){
            let holder = $(this).closest('h4');
            let name = holder.find('span').html();
            console.log(name);
            bootbox.prompt({
                title: "Change title: " + name,
                inputType: 'text',                
                callback: function (result) {
                    if(result){
                        $(holder).find('span').html(result);
                        $(holder).find(':hidden').val(result);
                    }
                }
            });
        })
        
        $('.new-format').click(function(){
            $.get('address-formats/new', {}, function(data){
                $('#formatsList').append(data);
                setHoverBox($('.address-format-rows:last'));
                setSortableRows($('.address-holder:last'));
            })
        })
    
        $('.widget-collapse').trigger('click');

        $('#frmMain').on('submit',function () {
            var $form = $(this);
            $.ajax({
                type: $form.attr('method'),
                url: $form.attr('action'),
                data: $form.serializeArray(),
                success: function (data) {
                    console.log(data.message);
                    if ( data && data.message  ) {
                        bootbox.alert({
                            message: data.message,
                            //size: 'small',
                            backdrop: true,
                            callback: function(){
                                if (data.status != 'ok'){
                                    window.location.reload();
                                }
                            }
                        });
                    }
                }
            });
            return false;
        });
    });
</script>