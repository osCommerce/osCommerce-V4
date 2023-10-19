<div class="tabbable tabbable-custom box-style-tab box-listing-product">
  {if $widget_listing}
  <ul class="nav nav-tabs">

    <li class="active" data-bs-toggle="tab" data-bs-target="#columns"><a>{$smarty.const.TEXT_COLUMNS}</a></li>
    <li data-bs-toggle="tab" data-bs-target="#rows"><a>{$smarty.const.TEXT_ROWS}</a></li>
    <li data-bs-toggle="tab" data-bs-target="#b2b"><a>{$smarty.const.TEXT_B2B}</a></li>

  </ul>

  <div class="tab-content">
    <div class="tab-pane active" id="columns">
      {/if}
        <div class="listing-view">
      <div class="listing-visibility">

        <p><label>
            <input type="checkbox" name="setting[0][show_name]"{if !$settings[0].show_name} checked{/if}/>
            {$smarty.const.TEXT_SHOW_NAME}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_image]"{if !$settings[0].show_image} checked{/if}/>
            {$smarty.const.TEXT_SHOW_IMAGE}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_stock]"{if !$settings[0].show_stock} checked{/if}/>
            {$smarty.const.TEXT_SHOW_STOCK_AVAILABILITY}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_description]"{if !$settings[0].show_description} checked{/if}/>
            {$smarty.const.TEXT_SHOW_SHORT_DESCRIPTION}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_model]"{if !$settings[0].show_model} checked{/if}/>
            {$smarty.const.TEXT_SHOW_MODEL}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_properties]"{if !$settings[0].show_properties} checked{/if}/>
            {$smarty.const.TEXT_SHOW_PROPERTIES}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_rating]"{if !$settings[0].show_rating} checked{/if}/>
            {$smarty.const.TEXT_SHOW_REVIEWS_RATING}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_rating_counts]"{if !$settings[0].show_rating_counts} checked{/if}/>
            {$smarty.const.TEXT_SHOW_REVIEWS_COUNTS}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_price]"{if !$settings[0].show_price} checked{/if}/>
            {$smarty.const.TEXT_SHOW_PRICE}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_bonus_points]"{if !$settings[0].show_bonus_points} checked{/if}/>
            {$smarty.const.TEXT_SHOW_BONUS_POINTS}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_buy_button]"{if !$settings[0].show_buy_button} checked{/if}/>
            {$smarty.const.TEXT_SHOW_BUY_BUTTON}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_qty_input]"{if !$settings[0].show_qty_input} checked{/if}/>
            {$smarty.const.TEXT_SHOW_QUANTITY_INPUT}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_view_button]"{if !$settings[0].show_view_button} checked{/if}/>
            {$smarty.const.TEXT_SHOW_VIEW_BUTTON}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_wishlist_button]"{if !$settings[0].show_wishlist_button} checked{/if}/>
            {$smarty.const.TEXT_SHOW_WISHLIST_BUTTON}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_compare]"{if !$settings[0].show_compare} checked{/if}/>
            {$smarty.const.TEXT_SHOW_SELECT_TO_COMPARE}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_attributes]"{if !$settings[0].show_attributes} checked{/if}/>
            Show Attributes</label></p>
		<p><label>
            <input type="checkbox" name="setting[0][show_paypal_button]"{if $settings[0].show_paypal_button} checked{/if}/>
            {$smarty.const.TEXT_SHOW_PAYPAL_BUTTON}</label></p>
		<p><label>
            <input type="checkbox" name="setting[0][show_amazon_button]"{if $settings[0].show_amazon_button} checked{/if}/>
            {$smarty.const.TEXT_SHOW_AMAZON_BUTTON}</label></p>	
      </div>


        {$productVies = \backend\design\Theme::getThemePages('productListing', $settings['theme_name'])}
        {$productVies = array_merge(['productListing'], $productVies)}
      <div class="block-type listing-type">
          <div class="show-preview">{$smarty.const.TEXT_SHOW_PREVIEW}</div>
          <div class="hide-preview">{$smarty.const.TEXT_HIDE_PREVIEW}</div>
        {if $widget_listing}
        <label class="item list-type-0">
          <input type="radio" name="setting[0][listing_type]" value="no"{if $settings[0].listing_type == 'no'} checked{/if}/>
          <div>
            <div style="text-align: center; padding: 10px">{$smarty.const.TEXT_NO_COLUMN_LISTING}</div>
          </div>
        </label>
        {/if}
          {foreach $productVies as $view}
              <label class="item list-{$view}">
                  <div>{$view}</div>
                  <input type="radio" name="setting[0][listing_type]" value="{$view}"{if $settings[0].listing_type == $view} checked{/if}/>
                  <div class="frame-holder"></div>
                  <section></section>
              </label>
          {/foreach}
        {*<label class="item list-type-1">
            <div class="">type-1 ({$smarty.const.DEPRECATED_TEXT})</div>
          <input type="radio" name="setting[0][listing_type]" value="type-1"{if $settings[0].listing_type == 'type-1'} checked{/if}/>
          <div class="frame-holder"></div>
          <section></section>
        </label>
        <label class="item list-type-2">
            <div class="">type-2 ({$smarty.const.DEPRECATED_TEXT})</div>
          <input type="radio" name="setting[0][listing_type]" value="type-2"{if $settings[0].listing_type == 'type-2'} checked{/if}/>
          <div class="frame-holder"></div>
          <section></section>
        </label>*}
      </div>

        </div>


      {if $widget_listing}
    </div>
    <div class="tab-pane" id="rows">
        <div class="listing-view">
      <div class="listing-visibility">

        <p><label>
            <input type="checkbox" name="setting[0][show_name_rows]"{if !$settings[0].show_name_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_NAME}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_image_rows]"{if !$settings[0].show_image_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_IMAGE}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_stock_rows]"{if !$settings[0].show_stock_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_STOCK_AVAILABILITY}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_description_rows]"{if !$settings[0].show_description_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_SHORT_DESCRIPTION}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_model_rows]"{if !$settings[0].show_model_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_MODEL}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_properties_rows]"{if !$settings[0].show_properties_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_PROPERTIES}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_rating_rows]"{if !$settings[0].show_rating_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_REVIEWS_RATING}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_rating_counts_rows]"{if !$settings[0].show_rating_counts_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_REVIEWS_COUNTS}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_price_rows]"{if !$settings[0].show_price_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_PRICE}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_buy_button_rows]"{if !$settings[0].show_buy_button_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_BUY_BUTTON}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_qty_input_rows]"{if !$settings[0].show_qty_input_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_QUANTITY_INPUT}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_view_button_rows]"{if !$settings[0].show_view_button_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_VIEW_BUTTON}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_wishlist_button_rows]"{if !$settings[0].show_wishlist_button_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_WISHLIST_BUTTON}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_compare_rows]"{if !$settings[0].show_compare_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_SELECT_TO_COMPARE}</label></p>
		<p><label>
            <input type="checkbox" name="setting[0][show_paypal_button_rows]"{if $settings[0].show_paypal_button_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_PAYPAL_BUTTON}</label></p>
		<p><label>
            <input type="checkbox" name="setting[0][show_amazon_button_rows]"{if $settings[0].show_amazon_button_rows} checked{/if}/>
            {$smarty.const.TEXT_SHOW_AMAZON_BUTTON}</label></p>
      </div>


      <div class="block-type listing-type">
          <div class="show-preview">{$smarty.const.TEXT_SHOW_PREVIEW}</div>
          <div class="hide-preview">{$smarty.const.TEXT_HIDE_PREVIEW}</div>
        <label class="item list-type-0">
          <input type="radio" name="setting[0][listing_type_rows]" value="no"{if $settings[0].listing_type_rows == 'no'} checked{/if}/>
          <div>
            <div style="text-align: center; padding: 10px">{$smarty.const.TEXT_NO_ROWS_LISTING}</div>
          </div>
        </label>
          {foreach $productVies as $view}
              <label class="item list-{$view}">
                  <div>{$view}</div>
                  <input type="radio" name="setting[0][listing_type_rows]" value="{$view}"{if $settings[0].listing_type_rows == $view} checked{/if}/>
                  <div class="frame-holder"></div>
                  <section></section>
              </label>
          {/foreach}
        <label class="item list-type-1">
            <div class="">type-1_2 ({$smarty.const.DEPRECATED_TEXT})</div>
          <input type="radio" name="setting[0][listing_type_rows]" value="type-1_2"{if !$settings[0].listing_type_rows || $settings[0].listing_type_rows == 'type-1_2'} checked{/if}/>
          <div class="frame-holder"></div>
          <section></section>
        </label>
        <label class="item list-type-2">
            <div class="">type-2_2 ({$smarty.const.DEPRECATED_TEXT})</div>
          <input type="radio" name="setting[0][listing_type_rows]" value="type-2_2"{if $settings[0].listing_type_rows == 'type-2_2'} checked{/if}/>
          <div class="frame-holder"></div>
          <section></section>
        </label>
      </div>


        </div>
    </div>
    <div class="tab-pane" id="b2b">
        <div class="listing-view">
      <div class="listing-visibility">

        <p><label>
            <input type="checkbox" name="setting[0][show_name_b2b]"{if !$settings[0].show_name_b2b} checked{/if}/>
            {$smarty.const.TEXT_SHOW_NAME}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_image_b2b]"{if !$settings[0].show_image_b2b} checked{/if}/>
            {$smarty.const.TEXT_SHOW_IMAGE}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_stock_b2b]"{if !$settings[0].show_stock_b2b} checked{/if}/>
            {$smarty.const.TEXT_SHOW_STOCK_AVAILABILITY}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_description_b2b]"{if !$settings[0].show_description_b2b} checked{/if}/>
            {$smarty.const.TEXT_SHOW_SHORT_DESCRIPTION}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_model_b2b]"{if !$settings[0].show_model_b2b} checked{/if}/>
            {$smarty.const.TEXT_SHOW_MODEL}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_properties_b2b]"{if !$settings[0].show_properties_b2b} checked{/if}/>
            {$smarty.const.TEXT_SHOW_PROPERTIES}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_rating_b2b]"{if !$settings[0].show_rating_b2b} checked{/if}/>
            {$smarty.const.TEXT_SHOW_REVIEWS_RATING}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_rating_counts_b2b]"{if !$settings[0].show_rating_counts_b2b} checked{/if}/>
            {$smarty.const.TEXT_SHOW_REVIEWS_COUNTS}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_price_b2b]"{if !$settings[0].show_price_b2b} checked{/if}/>
            {$smarty.const.TEXT_SHOW_PRICE}</label></p>

        <p><label>
            <input type="checkbox" name="setting[0][show_qty_input_b2b]"{if !$settings[0].show_qty_input_b2b} checked{/if}/>
            {$smarty.const.TEXT_SHOW_QUANTITY_INPUT}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_view_button_b2b]"{if !$settings[0].show_view_button_b2b} checked{/if}/>
            {$smarty.const.TEXT_SHOW_VIEW_BUTTON}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_wishlist_button_b2b]"{if !$settings[0].show_wishlist_button_b2b} checked{/if}/>
            {$smarty.const.TEXT_SHOW_WISHLIST_BUTTON}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_compare_b2b]"{if !$settings[0].show_compare_b2b} checked{/if}/>
            {$smarty.const.TEXT_SHOW_SELECT_TO_COMPARE}</label></p>
        <p><label>
            <input type="checkbox" name="setting[0][show_attributes_b2b]"{if !$settings[0].show_attributes_b2b} checked{/if}/>
            Show Attributes</label></p>

      </div>


      <div class="block-type listing-type">
          <div class="show-preview">{$smarty.const.TEXT_SHOW_PREVIEW}</div>
          <div class="hide-preview">{$smarty.const.TEXT_HIDE_PREVIEW}</div>
        <label class="item list-type-0">
          <input type="radio" name="setting[0][listing_type_b2b]" value=""{if !$settings[0].listing_type_b2b} checked{/if}/>
          <div>
            <div style="text-align: center; padding: 10px">{$smarty.const.TEXT_NO_B2B_LISTING}</div>
          </div>
        </label>
          {foreach $productVies as $view}
              <label class="item list-{$view}">
                  <div>{$view}</div>
                  <input type="radio" name="setting[0][listing_type_b2b]" value="{$view}"{if $settings[0].listing_type_b2b == $view} checked{/if}/>
                  <div class="frame-holder"></div>
                  <section></section>
              </label>
          {/foreach}
        <label class="item list-type-1">
            <div class="">type-1_3 ({$smarty.const.DEPRECATED_TEXT})</div>
          <input type="radio" name="setting[0][listing_type_b2b]" value="type-1_3"{if $settings[0].listing_type_b2b == 'type-1_3'} checked{/if}/>
          <div class="frame-holder"></div>
          <section></section>
        </label>
      </div>


        </div>
    </div>
  </div>

  {/if}
</div>


<script type="text/javascript">
  (function(){
    $(function(){
      $('.popup-box.widget-settings').css('width', 900);

      $('.listing-type input:checked').parent().addClass('active');
      $('.listing-type').on('click', function(){
        $('.active', this).removeClass('active');
        $('input:checked', this).parent().addClass('active')
      });

        listingVisibilityInput()
      $('.listing-visibility input').on('change', listingVisibilityInput);
      function listingVisibilityInput (){
          var listing_visibility = '';

          $('.listing-visibility input').each(function(){
              if (!$(this).prop('checked'))
                  listing_visibility += '&' + $(this).attr('name').substr(11).replace(']', '') + '=1';
          });

          $('.listing-type label').each(function(){
              var type = $('input', this).attr('value');
              if (type != 'no' && type != ''){
                  var item_url =
                      '../list-demo' +
                      '?list_type=' + type +
                      listing_visibility
                  ;
                  $('> div.frame-holder', this).html('<iframe src="' + item_url + '" width="100%" height="300" frameborder="no" id="info-view"></iframe>');
                  var _frame = $('iframe', this);
                  _frame.on('load', function(){
                      var frame = _frame.contents();
                      setInterval(function(){
                          var height = $('body', frame).height();
                          if (_frame.attr('height') != height) {
                              _frame.attr('height', height)
                          }
                      }, 1000);
                      $('a', frame).removeAttr('href');
                      $('form', frame).removeAttr('action')
                  })
              }
          })
      }

      $('.box-listing-product .nav a').on('click', function(){
        $('.listing-visibility input:first').trigger('change')
      })


        $('.frame-holder').css({
            'visibility': 'hidden',
            'height': '0',
        })
        $('.show-preview').on('click', function(){
            $('.frame-holder').css({
                'visibility': '',
                'height': '',
            });
            $('.show-preview').hide();
            $('.hide-preview').show()
        })
        $('.hide-preview').on('click', function(){
            $('.frame-holder').css({
                'visibility': 'hidden',
                'height': '0',
            });
            $('.show-preview').show();
            $('.hide-preview').hide()
        });
        /*$('.listing-view').each(function(){
            var _this = $(this);
            $('input', _this).on('change', function(){
                if (['type-1', 'type-1_2', 'type-1_3', 'type-1_4', 'type-2', 'type-2_2'].includes($('.block-type input:checked', _this).val())){
                    $('.listing-visibility', _this).show();
                } else {
                    $('.listing-visibility', _this).hide();
                }
            });

        })*/



    })
  })(jQuery)
</script>