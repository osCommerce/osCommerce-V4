<div class="visibility">
  <div class="buttons">
    <span class="btn btn-check">{$smarty.const.TEXT_CHECK_ALL}</span>
    <span class="btn btn-uncheck">{$smarty.const.TEXT_UNCHECK_ALL}</span>
  </div>

  <div class="row">
    <div class="col-md-6">

      <p><label><input type="checkbox" name="setting[0][visibility_home]"{if !$settings[0].visibility_home} checked{/if}/> {$smarty.const.TEXT_HOME}</label></p>
      <p><label><input type="checkbox" name="setting[0][visibility_product]"{if !$settings[0].visibility_product} checked{/if}/> {$smarty.const.TEXT_PRODUCT}</label></p>
      <p><label><input type="checkbox" name="setting[0][visibility_catalog]"{if !$settings[0].visibility_catalog} checked{/if}/> {$smarty.const.TEXT_LISTING}</label></p>
      <p><label><input type="checkbox" name="setting[0][visibility_info]"{if !$settings[0].visibility_info} checked{/if}/> {$smarty.const.TEXT_INFORMATION}</label></p>
      <p><label><input type="checkbox" name="setting[0][visibility_cart]"{if !$settings[0].visibility_cart} checked{/if}/> {$smarty.const.TEXT_SHOPPING_CART}</label></p>
      <p><label><input type="checkbox" name="setting[0][visibility_checkout]"{if !$settings[0].visibility_checkout} checked{/if}/> {$smarty.const.TEXT_CHECKOUT}</label></p>
      <p><label><input type="checkbox" name="setting[0][visibility_success]"{if !$settings[0].visibility_success} checked{/if}/> {$smarty.const.TEXT_CHECKOUT_SUCCESS}</label></p>
      <p><label><input type="checkbox" name="setting[0][visibility_account]"{if !$settings[0].visibility_account} checked{/if}/> {$smarty.const.TEXT_ACCOUNT}</label></p>
      <p><label><input type="checkbox" name="setting[0][visibility_login]"{if !$settings[0].visibility_login} checked{/if}/> {$smarty.const.TEXT_LOGIN_CREATE_ACCOUNT}</label></p>
      <p><label><input type="checkbox" name="setting[0][visibility_other]"{if !$settings[0].visibility_other} checked{/if}/> {$smarty.const.TEXT_OTHER}</label></p>
    </div>
    <div class="col-md-6">

      <p><label><input type="checkbox" name="setting[0][visibility_first_view]"{if !$settings[0].visibility_first_view} checked{/if}/> First visit</label></p>
      <p><label><input type="checkbox" name="setting[0][visibility_more_view]"{if !$settings[0].visibility_more_view} checked{/if}/> More then one visit</label></p>
      <p><label><input type="checkbox" name="setting[0][visibility_logged]"{if !$settings[0].visibility_logged} checked{/if}/> Logged in</label></p>
      <p><label><input type="checkbox" name="setting[0][visibility_not_logged]"{if !$settings[0].visibility_not_logged} checked{/if}/> No logged in</label></p>
    </div>
  </div>

  <div class="buttons">
    <span class="btn btn-check">{$smarty.const.TEXT_CHECK_ALL}</span>
    <span class="btn btn-uncheck">{$smarty.const.TEXT_UNCHECK_ALL}</span>
  </div>
</div>



<script type="text/javascript">
  (function($){
    $(function(){

      function defaultChecked(){
        if ($('.page-link-home').hasClass('active')){
          $('.visibility input[name="setting[0][visibility_home]"]').prop({ 'checked': 'true', 'disabled': 'true'})
        }
        if ($('.page-link-product').hasClass('active')){
          $('.visibility input[name="setting[0][visibility_product]"]').prop({ 'checked': 'true', 'disabled': 'true'})
        }
        if ($('.page-link-catalog').hasClass('active')){
          $('.visibility input[name="setting[0][visibility_catalog]"]').prop({ 'checked': 'true', 'disabled': 'true'})
        }
        if ($('.page-link-info').hasClass('active')){
          $('.visibility input[name="setting[0][visibility_info]"]').prop({ 'checked': 'true', 'disabled': 'true'})
        }
        if ($('.page-link-cart').hasClass('active')){
          $('.visibility input[name="setting[0][visibility_cart]"]').prop({ 'checked': 'true', 'disabled': 'true'})
        }
        if ($('.page-link-success').hasClass('active')){
          $('.visibility input[name="setting[0][visibility_success]"]').prop({ 'checked': 'true', 'disabled': 'true'})
        }
      }
      defaultChecked();

      $('.btn-check').on('click', function(){
        $('.visibility input').prop('checked', 'true');
        defaultChecked()
      });
      $('.btn-uncheck').on('click', function(){
        $('.visibility input').prop('checked', 0);
        defaultChecked()
      })

    })
  })(jQuery);
</script>