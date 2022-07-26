{\suppliersarea\assets\AppAsset::register($this)|void}
{use class="yii\helpers\Html"}

<div class="message">{$message}</div>


{$list}

   <script>
   
   switchProductStatus = function (sid, uprid, status){
        $.post('change-status', {
            sid: sid,
            uprid: uprid,
            value: status,
            _csrf: $('input[name=_csrf]').val()
        }, function(data, status){
            //$('.message').html(data);
        }, 'html');
   }
   
    $(document).ready(function(){
        
        $('body').on('click', '.edit-price, .edit-quantity, .edit-discount', function(e){
            var box = $(this).parents('.value-box');
            e.preventDefault();
            $(box).hide();
            $(box).next().show();
        });
        
        function postProcess(handler){
            var href = $(handler).attr('href');            
            var box = $(handler).parents('.edit-box');
            var children = $(box).children();            
            var data = [];
            $.each(children, function(i,e){
                data.push({ 'name' : e.name, 'value': e.value });
            });            
            
            $.post(href, data, function(data, status){
                $(box).closest('td').html(data);
            })
        }
        
        $('body').on('keypress', 'input[name*=suppliers_data]', function(e){
            if (e.keyCode == 13){                
                postProcess($(e.target).parent().find('a'));
            }
        });
        
        
        $('body').on('click', '.save-price, .save-quantity, .save-discount', function(e){
            e.preventDefault();
            postProcess(this);            
        });
        
        $(document).on('pjax:complete', function(event, xhr, settings) {
            initSwitchers();
        });
        
        initSwitchers();
    
        function initSwitchers(){
            $('.check_on_off').bootstrapSwitch({
            onSwitchChange: function (event, checked) {                
                switchProductStatus(event.target.attributes['data-sid'].value, event.target.attributes['data-uprid'].value, checked);
                return true;
            },
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        });
        }
        
    })
   </script>