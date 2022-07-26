{use class="Yii"}
{use class="frontend\design\Info"}

{foreach $posts as $post}
	<div class="blogPostWrap after">
		{if $post['image']}
			<div class="blogPostImg">
				<img src="{$post['image']}" alt="{$post['post_name']}" title="{$post['post_name']}" />
			</div>
		{/if}
		<div class="blogPostContent">
			{if $post['category']}
				<div class="blogPostCategory">
					{$post['category']}
				</div>
			{/if}
			{if $post['post_title']}
				<div class="blogPostName">
					{$post['post_title']}
				</div>
			{/if}
			{if $post['post_content']}
				<div class="blogPostText">
					{$post['post_content']}
				</div>
			{/if}
			<a href="{$post['guid']}">{$smarty.const.VIEW}</a>
		</div>
	</div>
{/foreach}