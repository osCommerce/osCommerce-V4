{use class="Yii"}
{use class="yii\base\Widget"}

<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_BLOCK}
  </div>
  <div class="popup-content">


    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

          <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.HEADING_TYPE}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">

          <div class="tab-pane active" id="type">
            <p>
              {$smarty.const.TEXT_SELECT_TYPE_BLOCK}
            </p>

            <div class="block-type block-type-main">
              <label class="item type-1">
                <input type="radio" name="setting[0][block_type]" value="1"{if $settings[0].block_type == '1'} checked{/if}/>
                <div>
                  <span>{$smarty.const.HEADING_SITE_CONTENT_WIDTH}</span>
                </div>
              </label>
              <label class="item type-2">
                <input type="radio" name="setting[0][block_type]" value="2"{if $settings[0].block_type == '2'} checked{/if}/>
                <div>
                  <span>1/2</span>
                  <span>1/2</span>
                </div>
              </label>
              <label class="item type-3">
                <input type="radio" name="setting[0][block_type]" value="3"{if $settings[0].block_type == '3'} checked{/if}/>
                <div>
                  <span>1/3</span>
                  <span>1/3</span>
                  <span>1/3</span>
                </div>
              </label>
              <label class="item type-14">
                <input type="radio" name="setting[0][block_type]" value="14"{if $settings[0].block_type == '14'} checked{/if}/>
                <div>
                  <span>1/4</span>
                  <span>1/4</span>
                  <span>1/4</span>
                  <span>1/4</span>
                </div>
              </label>
              <label class="item type-15">
                <input type="radio" name="setting[0][block_type]" value="15"{if $settings[0].block_type == '15'} checked{/if}/>
                <div>
                  <span>1/5</span>
                  <span>1/5</span>
                  <span>1/5</span>
                  <span>1/5</span>
                  <span>1/5</span>
                </div>
              </label>
              <label class="item type-4">
                <input type="radio" name="setting[0][block_type]" value="4"{if $settings[0].block_type == '4'} checked{/if}/>
                <div>
                  <span>2/3</span>
                  <span>1/3</span>
                </div>
              </label>
              <label class="item type-5">
                <input type="radio" name="setting[0][block_type]" value="5"{if $settings[0].block_type == '5'} checked{/if}/>
                <div>
                  <span>1/3</span>
                  <span>2/3</span>
                </div>
              </label>
              <label class="item type-6">
                <input type="radio" name="setting[0][block_type]" value="6"{if $settings[0].block_type == '6'} checked{/if}/>
                <div>
                  <span>1/4</span>
                  <span>3/4</span>
                </div>
              </label>
              <label class="item type-7">
                <input type="radio" name="setting[0][block_type]" value="7"{if $settings[0].block_type == '7'} checked{/if}/>
                <div>
                  <span>3/4</span>
                  <span>1/4</span>
                </div>
              </label>
              <label class="item type-8">
                <input type="radio" name="setting[0][block_type]" value="8"{if $settings[0].block_type == '8'} checked{/if}/>
                <div>
                  <span>1/4</span>
                  <span>1/2</span>
                  <span>1/4</span>
                </div>
              </label>
              <label class="item type-9">
                <input type="radio" name="setting[0][block_type]" value="9"{if $settings[0].block_type == '9'} checked{/if}/>
                <div>
                  <span>1/5</span>
                  <span>4/5</span>
                </div>
              </label>
              <label class="item type-10">
                <input type="radio" name="setting[0][block_type]" value="10"{if $settings[0].block_type == '10'} checked{/if}/>
                <div>
                  <span>4/5</span>
                  <span>1/5</span>
                </div>
              </label>
              <label class="item type-11">
                <input type="radio" name="setting[0][block_type]" value="11"{if $settings[0].block_type == '11'} checked{/if}/>
                <div>
                  <span>2/5</span>
                  <span>3/5</span>
                </div>
              </label>
              <label class="item type-12">
                <input type="radio" name="setting[0][block_type]" value="12"{if $settings[0].block_type == '12'} checked{/if}/>
                <div>
                  <span>3/5</span>
                  <span>2/5</span>
                </div>
              </label>
              <label class="item type-13">
                <input type="radio" name="setting[0][block_type]" value="13"{if $settings[0].block_type == '13'} checked{/if}/>
                <div>
                  <span>1/5</span>
                  <span>3/5</span>
                  <span>1/5</span>
                </div>
              </label>

            </div>

            <div class="setting-row">
              <label for="">Placeholder</label>
              <input type="text" name="params" class="form-control" value="{$params}" style="width: 300px"/>
            </div>
          </div>
          <div class="tab-pane" id="style">
            {$block_view = 1}
            {include 'include/style.tpl'}

          </div>
        <div class="tab-pane" id="visibility">
          {include 'include/visibility.tpl'}
        </div>

      </div>
    </div>


  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
</form>
<script type="text/javascript">
  $(function(){

    $('.block-type input:checked').each(function(){
      $(this).parent().addClass('active');
    });
    $('.block-type').on('click', function(){
      var name = $('input', this).attr('name');
      $('input[name="'+name+'"]').parent().removeClass('active');
      $('input:checked', this).parent().addClass('active')
    })
  });
</script>