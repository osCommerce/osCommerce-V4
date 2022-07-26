{use class="\yii\helpers\Html"}
{\suppliersarea\assets\AppAsset::register($this)|void}

{$messages}
<h3>{$mProductName}</h3>
{Html::beginForm(['products/'|cat:$app->controller->action->id, 'uprid' => $uprid], 'post')}
{assign var="idPart" value="{$sProduct->suppliers_id}"}
    <div class="widget-content" id="suppliers-{str_replace(['{', '}'], ['-', '-'], $sProduct->uprid)}-{$idPart}">
    {assign var=options value = ['class'=>'form-control']}
    {if !$sProduct->status}
        {$options['readonly'] = 'readonly' }
    {/if}
    {Html::hiddenInput('uprid', $sProduct->uprid, $options)}
    {Html::checkbox('suppliers_data['|cat:$uprid|cat:']['|cat:$sProduct->suppliers_id|cat:'][status]', $sProduct->status, ['class'=>'supplier-product-status'])}
    {$content}
    </div>
{Html::a('Cancel', $cancelLink, ['class' => 'btn  btn-primary'])}
{Html::submitButton('Save', ['class' => 'btn  btn-primary'])}
{Html::endForm()}

<script>

function calculatePriceOnServer (data){    
        return new Promise(function(showPrice, reject){
            if (data){
                $.post('calculate-product-price', data, function(data){
                    showPrice(data);
                }, 'json');
            }
        });
    }
    

  if (typeof updateSupplierPrices != 'function') {
    function updateSupplierPrices($suppliers_id){
        var form = $('#suppliers-{str_replace(['{', '}'], ['-', '-'], $uprid)}-'+$suppliers_id).closest('form');
        var box = $(form).find('.widget-content');
        if (form){
            calculatePriceOnServer(form.serialize())
            .then( function(data){
                    $('#supplier_price_'+$suppliers_id, box).text(data.formatedSuplPrice);
                    $('#supplier_cost_price_'+$suppliers_id, box).text(data.formatedCostPrice);
                }
            );
        }
    }
  }
  updateSupplierPrices({$sProduct->suppliers_id});
  if (typeof changeSPStatus != 'function'){
      changeSPStatus = function(target, status) {
        $.each($(target).parents('.widget-content:first').find('input, select'), function(i, el){
            $(el).attr('readonly', (status?false:'readonly'));
        });
      }
  }


    $(document).ready(function() {  
    
      $('.js-supplier-recalc').change(function(){
        updateSupplierPrices({$sProduct->suppliers_id});
      })
      
      $('.supplier-product-status').bootstrapSwitch({
          onText: "{$smarty.const.SW_ON}",
          offText: "{$smarty.const.SW_OFF}",
          handleWidth: '20px',
          labelWidth: '24px', 
          onSwitchChange: function (e, status) {
                changeSPStatus(e.target, status);return;
                if (typeof getCountSuppliersPrices == 'function') getCountSuppliersPrices();
                if (typeof countActiveSuppliers != 'undefined'){
                    if (!countActiveSuppliers){
                        changeSPStatus(e.target, true);
                        $(this).bootstrapSwitch('state', true);
                    }
                }
          }
      });
    });

</script>