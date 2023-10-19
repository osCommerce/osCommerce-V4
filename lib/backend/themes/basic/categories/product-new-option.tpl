{include file='../assets/tabs.tpl' scope="global"}
{foreach $attributes as $option}
<div class="widget box box-no-shadow js-option" data-option_id="{$option['products_options_id']}">
    <input type="hidden" name="products_option_values_sort_order[{$option['products_options_id']}]" value="">
    <div class="widget-header">
        <h4>{$option['products_options_name']}</h4>
        <div class="toolbar no-padding">
            <div class="btn-group">
              <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
            </div>
        </div>
    </div>
        <div class="widget-content">
        <table class="table assig-attr-sub-table attr-option-{$option['products_options_id']} {if $option['is_virtual_option']}is-virtual{/if}" id="attr-option-{$option['products_options_id']}">
            <thead>
                <tr role="row">
                    <th></th>
                    <th>{$smarty.const.TEXT_IMG}</th>
                    <th>{$smarty.const.TEXT_LABEL_NAME}</th>
                    <th class="set-ditails">{$smarty.const.TEXT_DEFAULT}</th>
                    <th class="ast-price one-attribute-force inventory-price-title" colspan="2">{$smarty.const.TEXT_PRICE}</th>
                    <th class="set-ditails one-attribute-force"></th>
                    <th class="set-ditails"></th>
                </tr>
            </thead>
            <tbody>
{$productNewAttributeIncluded=true }
{include file='./product-new-attribute.tpl'}
{call newVirtualOptionValue
        options=$option['values']
        products_id=$products_id
        products_options_id=$option['products_options_id']
        isIncluded=true }

        </tbody>
    </table>
<script type="text/javascript">
$(document).ready(function() {
   $( ".attr-option-{$option['products_options_id']} tbody" ).sortable({
      handle: ".sort-pointer",
      axis: 'y',
     update: function( event, ui ) {
       var order_ids = [''];
       $(this).find('.js-option-value').each(function() {
         order_ids.push($(this).attr('data-option_value_id'));
       });
       order_ids.push('');
       $('.js-option[data-option_id="{$option['products_options_id']}"]').find('input[name="products_option_values_sort_order[{$option['products_options_id']}]"]').val(order_ids.join(','));
     }
   });
//2do translation
{if !defined('ADMIN_TOO_MANY_IMAGES') || (is_array($app->controller->view->images) && $app->controller->view->images|@count < intval(ADMIN_TOO_MANY_IMAGES))}
    $('.divselktr-{$option['products_options_id']}').multiselect({
        multiple: true,
        height: '205px',
        header: 'See the images in the rows below:',
        noneSelectedText: 'Select',
        selectedText: function(numChecked, numTotal, checkedItems){
          return numChecked + ' of ' + numTotal;
        },
        selectedList: false,
        show: ['blind', 200],
        hide: ['fade', 200],
        position: {
            my: 'left top',
            at: 'left bottom'
        }
    });
{/if}
    $('.widget .toolbar .widget-collapse').click(function() {
            var widget         = $(this).parents(".widget");
            var widget_content = widget.children(".widget-content");
            var widget_chart   = widget.children(".widget-chart");
            var divider        = widget.children(".divider");

            if (widget.hasClass('widget-closed')) {
                    // Open Widget
                    $(this).children('i').removeClass('icon-angle-up').addClass('icon-angle-down');
                    widget_content.slideDown(200, function() {
                            widget.removeClass('widget-closed');
                    });
                    widget_chart.slideDown(200);
                    divider.slideDown(200);
            } else {
                    // Close Widget
                    $(this).children('i').removeClass('icon-angle-down').addClass('icon-angle-up');
                    widget_content.slideUp(200, function() {
                            widget.addClass('widget-closed');
                    });
                    widget_chart.slideUp(200);
                    divider.slideUp(200);
            }
    });
//2do speedup - init on popup shown
    $('.attachment-upload-container-attr').not('.inited').each(function() {
      var _attach = $(this);
      var id = $(this).attr('data-id')
      $('.upload-file', _attach).dropzone({
        url: "{Yii::$app->urlManager->createUrl('upload/index')}",
        maxFiles: 1,
        uploadMultiple: false,
        sending:  function(e, data) {
          $('.upload-remove', _attach).on('click', function(){
            $('.dz-details', _attach).remove()
          })
        },
        dataType: 'json',
        previewTemplate: '<div class="dz-details" style="display: none;"></div>',
        drop: function(){
          $('.upload-file', _attach).html('')
        },
        success: function(e, data) {
          $('#upload-file-name' + id).text(e.name);
          $('#attr_file' + id).val(e.name);
          //console.log( e.name );
          //$('.upload-file', _this).html('');

        },
      });
      $(this).addClass('inited');
    });

    $('ul[id^="invPrice"] [data-bs-toggle="tab"]').on('shown.bs.tab', invPriceTabsShown);
    $('ul[id^="attr_popup"] [data-bs-toggle="tab"]').on('shown.bs.tab', invPriceTabsShown);
    $('.inventory-popup-link').off('click').on('click', function(){
      var popup = $($(this).attr('href'));
      //save all vals for cancel button functionality
      var _vals = { };
      popup.find("input").each(function() {
        if (this.type == 'text' && !this.disabled && typeof(this.name) !== 'undefined' && this.name != '') {
          if ( this.name.substr(-2,2) == '[]') {
            if (typeof _vals[this.name] !== 'object') {
              _vals[this.name] = new Array();
            }
            _vals[this.name].push(this.value);
          } else {
           _vals[this.name] = this.value;
          }
        }
        if (this.type == 'checkbox' && !this.disabled && typeof(this.name) !== 'undefined' && this.name != '') {
          _vals[this.name] = this.checked;
        }
      });
      //saved

      popup.show();
      //init visible elements.
      invPriceTabsShown(popup);
//2do move to popup
      $('#content, .content-container').css({ 'position': 'relative', 'z-index': '100'});
      $('.w-or-prev-next > .tabbable').css({ 'z-index': '5'});

      var height = function(){
        var h = $(window).height() - $('.popup-heading', popup).height() - $('.popup-buttons', popup).height() - 120;
        $('.popup-content', popup).css('max-height', h);
      };
      height();
      $(window).on('resize', height);

      $('.pop-up-close-page, .btn-cancel', popup).off('click').on('click', function(){
        //Cancel button - Reset changes
        popup.find("input").each(function() {
          if (!$(this).is('[readonly]')) {
            if (this.type == 'text') {
              if(_vals[this.name] !== 'undefined') {
                if (typeof _vals[this.name]  === 'object') { // array
                  this.value = _vals[this.name].shift();
                } else {
                  this.value = _vals[this.name];//this.defaultValue;
                }
              } else {
                this.value = this.defaultValue;
              }
            }
            if (this.type == 'checkbox') {
              if(_vals[this.name] !== undefined) {
                try {
                  if ($(this).parent().is('div.bootstrap-switch-container'))
                    $(this).bootstrapSwitch('state', _vals[this.name]);
                } catch (err) { }
                this.checked = _vals[this.name];
              }
            }
          }
        });
        ///VL cancell button - don't update prices.
        $('.js_inventory_group_price', popup).each(function() {
          $(this).removeClass("inited");
        });


        popup.hide();
        $(window).off('resize', height);
        $('#content, .content-container').css({ 'position': '', 'z-index': ''});
        $('.w-or-prev-next > .tabbable').css({ 'z-index': ''});
      });

      $('.btn-save2', popup).off('click').on('click', function(){
        //update default currency "main" (0) group  prices in lists
        //
        fullPrice = $('#full_add_price').val();
        uprid=$(this).attr('data-upridsuffix');
        updateInvListPrices(fullPrice, uprid);

        popup.hide();
        $(window).off('resize', height);
        $('#content, .content-container').css({ 'position': '', 'z-index': ''});
        $('.w-or-prev-next > .tabbable').css({ 'z-index': ''});
      });

      return false
    });

    
});
</script>
    </div>
</div>
{/foreach}