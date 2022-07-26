
<div class="btn-box-inv-price btn-market after">
  <span class="btn-xl-pr active" id="btn-xl0-pr">{$smarty.const.FIELDSET_ASSIGNED_XSELL_PRODUCTS}</span>
  {foreach $app->controller->view->xsellTypes as $xsellTypeId=>$sellTypeName}
    <span class="btn-xl-pr" id="btn-xl{$xsellTypeId}-pr">{$sellTypeName}</span>
  {/foreach}
  {if !isset($category)}
  <span class="btn-up-pr" id="btn-up-pr">{$smarty.const.FIELDSET_ASSIGNED_UPSELL_PRODUCTS}</span>
  <span class="btn-gaw-pr" id="btn-gaw-pr">{$smarty.const.FIELDSET_ASSIGNED_AS_GIVEAWAY}</span>
  <span class="btn-pop-pr" id="btn-pop-pr">{$smarty.const.TEXT_POPULARITY}</span>
    {if \common\helpers\Acl::checkExtensionAllowed('Promotions')}
    <span class="btn-pop-pr" id="btn-pro-pr">{$smarty.const.TEXT_PROMOTIONS}</span>
    {/if}
  {/if}
</div>
{assign var="xsellTypeId" value="0"}
{include file="productedit/xsell.tpl"}
{foreach $app->controller->view->xsellTypes as $xsellTypeId=>$sellTypeName}
  {include file="productedit/xsell.tpl"}
{/foreach}

{if !isset($category)}
{if \common\helpers\Acl::checkExtensionAllowed('UpSell', 'allowed')}
    {\common\extensions\UpSell\UpSell::productBlock()}
{else}                           
    {include 'productedit/upsell.tpl'}
{/if}
<div class="gaw-pr-box" id="box-gaw-pr">
  {include 'give-away.tpl'}
</div>
<div class="pop-pr-box" id="box-pop-pr">
  {include './popularity.tpl'}
</div>
<div class="pop-pr-box" id="box-pro-pr">
  {include './promotions.tpl'}
</div>
{/if}

<script type="text/javascript">
  function addSelectedXSell(xsellType) {
      $( 'select#xsell'+xsellType+'-search-products option:selected' ).each(function() {
      var xsell_id = $(this).val();
      if ( $( 'input[name="xsell_id['+xsellType+'][]"][value="'+xsell_id+'"]' ).length ) {
        //already exist
      } else {
        $.post("{Yii::$app->urlManager->createUrl('categories/product-new-xsell')}", { 'products_id': xsell_id, 'xsell_type': xsellType }, function(data, status){
          if (status == "success") {
            $( ".xsell"+xsellType+"-products tbody" ).append(data);

          } else {
            alert("Request error.");
          }
        },"html");
      }
    });

    return false;
  }

  function deleteSelectedXSell(obj) {
    $(obj).parent().remove();
    return false;
  }

  function addSelectedUpsell() {
    $( 'select#upsell-search-products option:selected' ).each(function() {
      var upsell_id = $(this).val();
      if ( $( 'input[name="upsell_id[]"][value="'+upsell_id+'"]' ).length ) {
        //already exist
      } else {
        $.post("{Yii::$app->urlManager->createUrl('categories/product-new-upsell')}", { 'products_id': upsell_id }, function(data, status){
          if (status == "success") {
            $( ".upsell-products tbody" ).append(data);
          } else {
            alert("Request error.");
          }
        },"html");
      }
    });

    return false;
  }

  function deleteSelectedUpsell(obj) {
    $(obj).parent().remove();
    return false;
  }

  var color = '#ff0000';
  var phighlight = function(obj, reg){
    if (reg.length == 0) return;
    $(obj).html($(obj).text().replace( new RegExp( "(" +  reg  + ")" , 'gi' ), '<font style="color:'+color+'">$1</font>'));
    return;
  }

  var searchHighlightExisting = function(e){
    var $rows = $(e.data.rows_selector);
    var search_term = $(this).val();
    $rows.each(function(){
      var $row = $(this);
      var $value_text = $row.find(e.data.text_selector);
      var search_match = true;

      if ( !$row.data('raw-value') ) $row.data('raw-value', $value_text.html());
      var prop_value = $row.data('raw-value');
      if ( search_term.length>0 ) {
        var searchRe = new RegExp(".*" + (search_term + "").replace(/([.?*+\^\$\[\]\\(){}|-])/g, "\\$1") + ".*", 'i');
        if (searchRe.test(prop_value)) {
          phighlight($value_text, search_term);
        } else {
          $value_text.html(prop_value);
          search_match = false;
        }
      }else{
        $value_text.html(prop_value);
      }

      if ( search_match ) {
        $row.show();
      }else{
        $row.hide();
      }
    });
  }

  var searchExisting = function(e){
    var addId = e.data.add_id;
    var selector = '';
    if (addId) {
      selector = '#' + $(this).attr('data-id') + ' ' + e.data.rows_selector;
    } else {
      selector = e.data.rows_selector;
    }
    var $rows = $(selector);
    var search_term = $(this).val();
    $rows.each(function(e){
      var $row = $(this);

      var $value_text = $row.find(e.data.text_selector);
      var search_match = true;

      if ( !$row.data('raw-value') ) $row.data('raw-value', $value_text.html());
      var prop_value = $row.data('raw-value');
      if ( search_term.length>0 ) {
        var searchRe = new RegExp(".*" + (search_term + "").replace(/([.?*+\^\$\[\]\\(){}|-])/g, "\\$1") + ".*", 'i');
        if (searchRe.test(prop_value)) {
        } else {
          search_match = false;
        }
      }else{
//no highlight        $value_text.html(prop_value);
      }
      if ( search_match ) {
        $row.show();
/*        $el = $('input.uniform', $row);
        $.uniform.update($el);*/
      }else{
        $row.hide();

      }
    }, [e]);
  }


  $(document).ready(function() {
    $('#search-xp0-assigned').on('focus keyup', { rows_selector: '#xp0-assigned tr', text_selector: '.name-ast'}, searchHighlightExisting);
    $('.image-attributes-filter').on('focus keyup', { add_id:true, rows_selector: 'li.image-attribute', text_selector: 'label'}, searchExisting);

    {foreach $app->controller->view->xsellTypes as $xsellTypeId=>$sellTypeName}
    $('#search-xp{$xsellTypeId}-assigned').on('focus keyup', { rows_selector: '#xp{$xsellTypeId}-assigned tr', text_selector: '.name-ast'}, searchHighlightExisting);
    {/foreach}
    $('#search-up-assigned').on('focus keyup', { rows_selector: '#up-assigned tr', text_selector: '.name-ast'}, searchHighlightExisting);

    {$info_product_id = $pInfo->products_id|default}
    $('.xsell-search-by-products').on('focus keyup', function(e) {
      var target = $(this).attr('data-target');
      var str = $(this).val();
      $.post( "{Yii::$app->urlManager->createUrl('categories/product-search')}?q="+encodeURIComponent(str)+"&not={$info_product_id}", function( data ) {
        $( "select#"+target ).html( data );
        psearch = new RegExp(str, 'i');
        $.each($('select#'+target).find('option'), function(i, e){
          if (psearch.test($(e).text())){
            phighlight(e, str);
          }
        });
      });
    });
    $.post( "{Yii::$app->urlManager->createUrl('categories/product-search')}?q=&not={$info_product_id}", function( data ) {
        $('.xsell-search-by-products').each(function(){
            var target = $(this).attr('data-target');
            $( "select#"+target ).html( data );
        });
    });

    $( ".xsell-products tbody" ).sortable({
      handle: ".sort-pointer",
      axis: 'y',
      update: function( event, ui ) {
        var targetTypeId = $(this).attr('data-target-type-id');
        var data = $(this).sortable('serialize', { attribute: "prefix" });
        $("#xsell"+targetTypeId+"_sort_order").val(data);
      }
    }).disableSelection();

    $('#upsell-search-by-products').on('focus keyup', function(e) {
      var str = $(this).val();
      $.post( "{Yii::$app->urlManager->createUrl('categories/product-search')}?q="+encodeURIComponent(str)+"&not={$info_product_id}", function( data ) {
        $( "select#upsell-search-products" ).html( data );
        psearch = new RegExp(str, 'i');
        $.each($('select#upsell-search-products').find('option'), function(i, e){
          if (psearch.test($(e).text())){
            phighlight(e, str);
          }
        });
      });
    }).keyup();

    $( ".upsell-products tbody" ).sortable({
      handle: ".sort-pointer",
      axis: 'y',
      update: function( event, ui ) {
        var data = $(this).sortable('serialize', { attribute: "prefix" });
        $("#upsell_sort_order").val(data);
      },
    }).disableSelection();

    function clickMarketingButton() { // shows/hides appropriate divs
      $('.btn-market span').each(function() {
        var div_id = this.id.replace('btn-', 'box-');
        if ($(this).hasClass('active') ) {
          $('#'+div_id).css('display', 'block');
        }else{
          $('#'+div_id).css('display', 'none');
        }
      });
      {if !isset($category)}
      init_gwa();
      {/if}
    }
    clickMarketingButton();
    $('.btn-market span').click(function() {
      $('.btn-market span').removeClass('active');
      $(this).toggleClass('active');
      clickMarketingButton();
    });

  });


</script>