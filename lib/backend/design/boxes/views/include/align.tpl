
<div class="block-type box-align">
  <label class="item align-1">
    <input type="radio" name="setting[0][box_align]" value="1"{if $settings[0].box_align == '1'} checked{/if}/>
    <div>
      <span></span>
    </div>
  </label>
  <label class="item align-2">
    <input type="radio" name="setting[0][box_align]" value="2"{if $settings[0].box_align == '2'} checked{/if}/>
    <div>
      <span></span>
    </div>
  </label>
  <label class="item align-3">
    <input type="radio" name="setting[0][box_align]" value="3"{if $settings[0].box_align == '3'} checked{/if}/>
    <div>
      <span></span>
    </div>
  </label>
  <label class="item align-4">
    <input type="radio" name="setting[0][box_align]" value="4"{if $settings[0].box_align == '4'} checked{/if}/>
    <div>
      <span></span>
    </div>
  </label>


</div>
<script type="text/javascript">
  $(function(){
    $('.box-align input:checked').parent().addClass('active');
    $('.box-align').on('click', function(){
      $('.box-align .active').removeClass('active');
      $('input:checked', this).parent().addClass('active')
    })
  })
</script>