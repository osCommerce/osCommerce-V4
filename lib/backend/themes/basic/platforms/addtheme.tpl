<div class="themes_jcarousel">
	<div class="jcarousel">
		<ul id="th_jcarousel">
			{foreach $results as $res}
			<li data-id="{$res['id']}" data-name="{$res['theme_name']}">
				<div class="fr_theme_img">
					{if $res.theme_image}
						<img src="{DIR_WS_CATALOG}{$res.theme_image}">
					{else}
						<img src="{DIR_WS_CATALOG}themes/{$res['theme_name']}/screenshot.png">
					{/if}
				</div>
				<div class="fr_theme_name">{$res['title']}</div>
				{if $res['description']}<div class="fr_theme_desc">{$res['description']}</div>{/if}
				<div class="fr_buttons"><button class="btn">{$smarty.const.TEXT_ASSIGN}</button></div>
			</li>
			{/foreach}
		</ul>
	</div>
	<a href="#" class="jcarousel-control-prev"></a>
  <a href="#" class="jcarousel-control-next"></a>
</div>
<script type="text/javascript">
(function($) {
    $(function() {
var jcarousel = $('.jcarousel').jcarousel();
			jcarousel.on('jcarousel:reload jcarousel:create', function () {
                var carousel = $(this),
                    width = carousel.innerWidth();
                if (width >= 1099) {
                    width = width / 3;
                } else {
                    width = width / 2;
                }

                carousel.jcarousel('items').css('width', Math.ceil(width-36) + 'px');
            })
            .jcarousel({
                wrap: 'circular'
            });
        $('.jcarousel-control-prev')
            .on('jcarouselcontrol:active', function() { 
                $(this).removeClass('inactive');
            })
            .on('jcarouselcontrol:inactive', function() { 
                $(this).addClass('inactive');
            })
            .jcarouselControl({
                target: '-=1'
            });

        $('.jcarousel-control-next')
            .on('jcarouselcontrol:active', function() { 
                $(this).removeClass('inactive');
            })
            .on('jcarouselcontrol:inactive', function() { 
                $(this).addClass('inactive');
            })
            .jcarouselControl({ 
                target: '+=1'
            });


		$('.fr_buttons button').click(function(e){
			e.preventDefault();

			const $li = $(this).closest('li');
			const id = $li.data('id');
			const theme_name = $li.data('name');
			const title = $('.fr_theme_name', $li).text();
			const imgSrc = $('img', $li).attr('src');
			const platform_id = $('#save_item_form input[name="id"]').val();

			let group = '';
			$.get('platforms/theme-banners', { theme_name: theme_name, platform_id: platform_id }, function(response){
				let items = response.reduce(function(sum, current){
					let item = '';
					if (group != current.banners_group) {
						group = current.banners_group;
						item += `<div class="group"><b>${ group}</b></div>`;
					}
					item += `
						<label class="item">
							<input type="checkbox" name="banners_id[]" value="${ current.banners_id}"${ (current.theme_banner || current.assigned ? ' checked' : '')}>
							${ current.banners_title} ${ (current.assigned ? ' <span class="assigned">({$smarty.const.TEXT_ASSIGNED})</span>' : '')} ${ (current.theme_banner ? ' <span class="theme-banner">({$smarty.const.TEXT_ADDED_WITH_THEME})</span>' : '')}
						</label>`;

					return sum + item
				}, '');

				const $content = $(`<div class="assign-banners">${ items}</div>`);
				const $buttons = $(`
						<div class="noti-btn">
							<div></div>
							<div><span class="btn">{$smarty.const.TEXT_ASSIGN}</span></div>
						</div>`);

				$('.theme_popup .theme_choose').html('').append('Assign banners to this platform');
				$('.theme_popup .pop-up-content').html('')
						.append($content)
						.append($buttons);

				$('.btn', $buttons).on('click', function(){

					let banners = [];
					$('input', $content).each(function(){
						banners.push({
							id: $(this).val(),
							assigned: $(this).prop('checked') ? 1 : 0
						})
					})

					const data = {
						banners: banners,
						platform_id: platform_id
					}

					$.post('platforms/assign-banners', data, function(response){
						if (response == 'done') {
							$('.pop-up-close').trigger('click');
						}
					});

					$('input[name="theme_id"]').val(id);
					$('.theme_wr .theme_title')
							.html(`<img src="${ imgSrc}" width="100" height="80"><div class="theme_title2">${ title}</div>`)
							.addClass('act');
				})
			}, 'json');


		})

});
})(jQuery);

</script>