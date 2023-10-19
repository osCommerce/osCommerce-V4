{$type=''}

{foreach $list as $item}
  {if $type==''}
    <span class="type-block {$item.type_class}">
	<strong class="items-title">{$item.type}</strong>
  {/if}

  {if $item.type != $type}
    
    {if $type!=''}
      </span><span class="type-block {$item.type_class}">
	  <strong class="items-title">{$item.type}</strong>
    {/if}
    {$type = $item.type}
  {/if}

  <span class="item">
    <a href="{$item.link}" >
      <span class="image">{if isset($item.image)}<img src="{$item.image}" alt="">{/if}</span>
      <span class="name">{$item.title}</span>
    </a>
    {if $item.extra}
    <span class="extra">{$item.extra}</span>
    {/if}
  </span>

{/foreach}
</span>
<script type="text/javascript">
  tl(function(){  
		var box = $('.search');
	$(window).resize(function(){	  
		if($(window).width() > 720){			
			$('.categories,.info-suggest,.brands, .history', box).wrapAll('<div class="wrap"></div>');
		}else{
			$('.categories,.info-suggest,.brands', box).wrapAll('<div class="wrap"></div>');
		}	
	})
	if($(window).width() > 720){			
		$('.categories,.info-suggest,.brands, .history', box).wrapAll('<div class="wrap"></div>');
	}else{
		$('.categories,.info-suggest,.brands', box).wrapAll('<div class="wrap"></div>');
	}
  })
 </script>