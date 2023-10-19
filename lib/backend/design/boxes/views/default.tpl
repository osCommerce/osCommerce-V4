{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">&nbsp;
  </div>
  <div class="popup-content">



    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">
        {if isset($settings['tabs'])}
          {if isset($settings['tabs']['class']) && isset($settings['tabs']['method'])}
            {assign var="method" value = $settings['tabs']['method']}
            {assign var="tabs" value = $settings['tabs']['class']::$method($settings)}
            {$count = 0}
            {foreach $tabs as $t}
                <li{if $count == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#{$t['title']}"><a>{$t['title']}</a></li>
                {$count = $count + 1}
            {/foreach}
           {/if}
        {elseif isset($settings['class']) && isset($settings['method']) }
            <li class="active" data-bs-toggle="tab" data-bs-target="#{$settings['method']}"><a>{$smarty.const.TEXT_SETTINGS}</a></li>
            <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        {else}
            <li class="active" data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        {/if}
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        {if $block_type == 'header' || $block_type == 'footer'}
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>
        {/if}
        <li data-bs-toggle="tab" data-bs-target="#ajax"><a>{$smarty.const.TEXT_AJAX}</a></li>

      </ul>
      <div class="tab-content">

          {if isset($settings['tabs'])}
              {if isset($settings['tabs']['class']) && isset($settings['tabs']['method'])}
                  {assign var="method" value = $settings['tabs']['method']}
                  {assign var="tabs" value = $settings['tabs']['class']::$method($settings)}
                  {$counter = 0}
                  {foreach $tabs as $t}
                    <div class="tab-pane{if $counter == 0} active{/if}" id="{$t['title']}">
                        {include $t['path']}
                    </div>
                    {$counter = $counter + 1}
                  {/foreach}
              {/if}
          {elseif isset($settings['class']) && isset($settings['method'])}
            <div class="tab-pane active" id="{$settings['method']}">
              {assign var="method" value = $settings['method']}
              {$settings['class']::$method($settings)}
            </div>
            <div class="tab-pane" id="style">
            {include 'include/style.tpl'}
            </div>
        {else}
        <div class="tab-pane active" id="style">
          {include 'include/style.tpl'}
        </div>
        {/if}

        <div class="tab-pane" id="align">
          {include 'include/align.tpl'}
        </div>
        {if $block_type == 'header' || $block_type == 'footer'}
        <div class="tab-pane" id="visibility">
          {include 'include/visibility.tpl'}
        </div>
          {else}

          <input type="hidden" name="setting[0][visibility_first_view]" value="1"/>
          <input type="hidden" name="setting[0][visibility_more_view]" value="1"/>
          <input type="hidden" name="setting[0][visibility_logged]" value="1"/>
          <input type="hidden" name="setting[0][visibility_not_logged]" value="1"/>

          <input type="hidden" name="setting[0][visibility_home]" value="1"/>
          <input type="hidden" name="setting[0][visibility_product]" value="1"/>
          <input type="hidden" name="setting[0][visibility_catalog]" value="1"/>
          <input type="hidden" name="setting[0][visibility_info]" value="1"/>
          <input type="hidden" name="setting[0][visibility_cart]" value="1"/>
          <input type="hidden" name="setting[0][visibility_checkout]" value="1"/>
          <input type="hidden" name="setting[0][visibility_success]" value="1"/>
          <input type="hidden" name="setting[0][visibility_account]" value="1"/>
          <input type="hidden" name="setting[0][visibility_login]" value="1"/>
          <input type="hidden" name="setting[0][visibility_other]" value="1"/>
        {/if}

        <div class="tab-pane" id="ajax">
          {include 'include/ajax.tpl'}
        </div>

      </div>
    </div>




  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
</form>