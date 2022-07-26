{use class="Yii"}
{use class="frontend\design\Info"}

<div class="socials_box_posts">
    <ul class="wrap_socials_posts">
        {if $return_fb}
            {foreach $return_fb['posts']['data'] as $post_item}
                <li class="post_item">
                    <div class="fb_post_item">
                        {if $post_item['full_picture']}
                            <div class="post_item_img"><a href="{$post_item['permalink_url']}" target="_blank"><img src="{$post_item['full_picture']}" alt="{$post_item['name']}" /></a></div>
                                {/if}
                        <div class="post_item_name"><a href="{$post_item['permalink_url']}" target="_blank">{$post_item['name']}</a></div>
                        <div class="post_item_mess">{$post_item['message']}</div>
                    </div>
                </li>
            {/foreach}
        {else}
            {if Info::isAdmin()}
            <li class="post_item post_item_error">Facebook error</li>
            {/if}
        {/if}        
        {if $return_insta}
            {foreach $return_insta->data as $post}
                <li class="post_item">
                    <div class="insta_post_item">
                        {if $post->images->standard_resolution->url}
                            <div class="post_item_img"><a href="{$post->link}" target="_blank"><img src="{$post->images->standard_resolution->url}" alt="{$post->user->username}" /></a></div>
                                {/if}
                        <div class="post_item_name"><a href="{$post->link}" target="_blank">{$post->user->username}</a></div>
                        <div class="post_item_mess">{$post->caption->text}</div>
                    </div>                
                </li>
            {/foreach}
        {else}
            {if Info::isAdmin()}
            <li class="post_item post_item_error">Insta error</li>
            {/if}
        {/if}        
        {if $return_twitter["errors"][0]["message"]}
            <li class="post_item post_item_error">{$return_twitter["errors"][0]["message"]}</li>    
        {else}
            {if $settings[0]['tw_hashtag']}
                {foreach $return_twitter['statuses'] as $items}
                    <li class="post_item">
                        <div class="tw_post_item">
                            {if $items['entities']['media']}
                                {foreach $items['entities']['media'] as $media}
                                    <div class="post_item_img"><a href="https://twitter.com/{$settings[0]['tw_screen_name']}/status/{$items['id']}" target="_blank"><img src="{$media['media_url']}" alt="{$items['text']}" /></a></div>
                                        {/foreach}
                                    {/if}
                            <div class="post_item_name"><a href="https://twitter.com/{$settings[0]['tw_screen_name']}/status/{$items['id']}" target="_blank">{$items['user']['screen_name']}</a></div>
                            <div class="post_item_mess">{$items['text']}</div>
                        </div>
                    </li>
                {/foreach}
            {else}
                {foreach $return_twitter as $items}
                    <li class="post_item">
                        <div class="tw_post_item">
                            {if $items['entities']['media']}
                                {foreach $items['entities']['media'] as $media}
                                    <div class="post_item_img"><a href="https://twitter.com/{$settings[0]['tw_screen_name']}/status/{$items['id']}" target="_blank"><img src="{$media['media_url']}" alt="{$items['text']}" /></a></div>
                                        {/foreach}
                                    {/if}
                            <div class="post_item_name"><a href="https://twitter.com/{$settings[0]['tw_screen_name']}/status/{$items['id']}" target="_blank">{$items['user']['screen_name']}</a></div>
                            <div class="post_item_mess">{$items['text']}</div>
                        </div>
                    </li>
                {/foreach}
            {/if}            
        {/if}
        
		{if is_object($playlist) && is_array($playlist->items)}
			{foreach $playlist->items as $item}
				<li class="post_item">
					<div class="yt_post_item">
						<div class="post_item_img">
							<a href="https://www.youtube.com/watch?v={$item->snippet->resourceId->videoId}" target="_blank"><img src="{$item->snippet->thumbnails->default->url}"></a>
						</div>
						<div class="post_item_name"><a href="https://www.youtube.com/watch?v={$item->snippet->resourceId->videoId}" target="_blank">{$item->snippet->title}</a></div>
					</div>
				</li>
			{/foreach}
		{else}
            {if Info::isAdmin()}
			<li class="post_item post_item_error">Error YouTube</li>
            {/if}
		{/if}
    </ul>
</div>
<script type="text/javascript">
    tl('{Info::themeFile('/js/masonry.pkgd.min.js')}', function () {
        var randomize = function (arr) {
            var length = arr.length - 1;
            for (var i = 0; i <= length; i++) {
                var rnd1 = Math.round(Math.random() * length),
                        rnd2 = 0;
                do {
                    rnd2 = Math.round(Math.random() * length);
                } while (rnd1 == rnd2);
                var tmp = arr[rnd1];
                arr[rnd1] = arr[rnd2];
                arr[rnd2] = tmp;
            }
        }
        var items = [];
        $('.wrap_socials_posts > li').each(function () {
            items.push($(this).html());
        });
        randomize(items);
        var length = items.length;
        for (var i = 0; i < length; i++) {
            $($('.wrap_socials_posts > li')[i]).html(items[i]);
        }
        setTimeout(function () {
            $('.wrap_socials_posts').masonry({
                itemSelector: '.post_item',
                columnWidth: '.post_item',
                percentPosition: true
            })
        }, 2000);
    })
</script>