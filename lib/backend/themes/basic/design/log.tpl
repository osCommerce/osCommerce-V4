{use class="backend\assets\DesignAsset"}
{DesignAsset::register($this)|void}
{include 'menu.tpl'}
<div class="drop-list log-list">

  <div class="log-filter">
    <label>{$smarty.const.TEXT_FROM}</label>
    <input id="from_date" type="text" value="{$from}" name="from" class="datepicker form-control">
    <label>{$smarty.const.TEXT_TO}</label>
    <input id="to_date" type="text" value="{$to}" autocomplete="off" name="to" class="datepicker form-control">
    <span class="btn btn-apple">{$smarty.const.TEXT_APPLY}</span>
  </div>

{function name=menuTree}
  {foreach $tree as $item}
    {if $item.branch_id == $parent}


      <li>
        <div class="item-handle">
          <div class="checkbox-item">
            {if in_array($item.event, ['cssSave', 'boxAdd', 'blocksMove', 'boxSave', 'boxDelete', 'settings', 'javascriptSave', 'addPage', 'removePageTemplate', 'addPageSettings', 'importBlock', 'stylesChange', 'copyPage', 'styleSave', 'setGroup', 'setStyles'])}
                {*  'removeClass' *}
            <input type="checkbox" class="uniform step-id" name="id[{$item.steps_id}]" data-id="{$item.steps_id}">
            {else}
              &nbsp;
            {/if}
          </div>
          <div class="item-close closed" style="display: block"></div>
          {*<div class="restore" data-id="{$item.steps_id}">{$smarty.const.IMAGE_RESTORE}</div>*}
          <div class="date">{$item.date_added}</div>
          <a href="{Yii::$app->urlManager->createUrl(['design/log-details', 'id' => $item.steps_id])}" class="details">{$smarty.const.IMAGE_DETAILS}</a>

          <span class="design-mode">{$item.mode}</span>

          <span class="no-link"><span title="{$admins[$item.admin_id].admin_email_address}">{$admins[$item.admin_id].admin_firstname} {$admins[$item.admin_id].admin_lastname}</span> {$item.text}</span>


        </div>


        <ul>
          {call menuTree parent=$item.steps_id}
        </ul>
      </li>


    {/if}
  {/foreach}
{/function}

<ul>
{call menuTree parent=0}
</ul>
  <div class="migration-buttons">
      <span class="btn btn-apply-update">{$smarty.const.APPLY_MIGRATION}</span>
      <span class="btn btn-create-update">{$smarty.const.CREATE_MIGRATION}</span>
  </div>
</div>
<script type="text/javascript">
  (function($){
    var getLog = function(){
      $.get('design/log', {
        'theme_name': '{$theme_name}',
        'from': $('#from_date').val(),
        'to': $('#to_date').val(),
      }, function(data){
        $('.content-container *').off();
        $('.content-container').html(data)
      })
    };

    $(function(){

      $( ".datepicker" ).datepicker({
        changeMonth: true,
        changeYear: true,
        showOtherMonths:true,
        autoSize: false,
        dateFormat: 'dd M yy',
        onSelect: function (e) {
          if ($(this).val().length > 0) {
            $(this).siblings('span').addClass('active_options');
          }else{
            $(this).siblings('span').removeClass('active_options');
          }
        }
      });


      $('.btn-apple').on('click', getLog);

      $('.log-list .details').popUp();


      $('.btn-create-update').on('click', function(){

        var stepIdItems = $('.step-id:checked');
        if (stepIdItems.length > 0) {
          var idArray = [];
          stepIdItems.each(function(i){
            idArray[i] = $(this).data('id')
          });
          $.post('design/create-update', {
            'theme_name': '{$theme_name}',
            'steps': idArray
          }, function(result){
            const moonLanding = new Date();
            var blob = new Blob([result]);
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = "migration-{$theme_name}-" + moonLanding.getTime() + ".json";
            link.click();
          })
        }
      });

      /*$('.btn-apply-update').on('click', function(){
        $.get('design/apply-update', {
          'theme_name': '{$theme_name}'
        }, function(data){
          alertMessage(data)
        })
      });*/

      $('.btn-apply-update').each(function(){
        $(this).dropzone({
          url: `design/apply-migration?theme_name={$theme_name}`,
          timeout: 300000,
          success: function(e){
            //$container.removeClass('hided-box');
            //$('.hided-box-holder', $container).remove()
            //location.reload();
            alertMessage('<div class="alert-message">Migration applied</div>')
          },
          sending: function(){
            //$container.addClass('hided-box').append('<div class="hided-box-holder"><div class="preloader"></div></div>')
          },
          error: function(){
            //$container.removeClass('hided-box');
            $('.hided-box-holder').remove();
            alertMessage('<div class="alert-message">Error</div>')
          },
          acceptedFiles: '.json'
        })
      });

      $('.log-list li').each(function(){
        var _this = $(this);
        if ($('> ul > li', _this).length > 0){
          _this.addClass('has-sub');
        }
        $('> div > .item-close', _this).on('click', function(){
          if ($(this).hasClass('closed')){
            $(this).removeClass('closed');
            $('> ul', _this).slideDown(200)
          } else {
            $(this).addClass('closed')
            $('> ul', _this).slideUp(200)
          }
        })
      });
      $('.log-list li ul').hide();

      var redo_buttons = $('.redo-buttons');
      redo_buttons.on('click', '.btn-undo', function(){
        $(redo_buttons).hide();
        $.get('design/undo', { 'theme_name': '{$theme_name}'}, function(){
          location.reload();
        })
      });
      redo_buttons.on('click', '.btn-redo', function(){
        $(redo_buttons).hide();
        $.get('design/redo', { 'theme_name': '{$theme_name}', 'steps_id': $(this).data('id')}, function(){
          location.reload();
        })
      });
      $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
        redo_buttons.html(data)
      });


      $('.restore').on('click', function(){
        $.get('design/step-restore', { 'id': $(this).data('id')}, function(data){
          if (data != '') {
            $('body').append(data);
          } else {
            location.reload();
          }
        })
      })



      if (!$('.drop-list.log-list .has-sub').length){
        $('.drop-list.log-list .item-close').hide();
      }
    })
  })(jQuery)
</script>