<div class="search-ico"></div>
<div class="background"></div>
<div class="search suggest-js">
    <form action="{$link}" method="get">
        <input class="search-input" type="text" name="keywords" placeholder="{$smarty.const.ENTER_YOUR_KEYWORDS}"
               value="{$keywords}"/>
        {if $smarty.const.SEARCH_IN_DESCRIPTION == 'True'}
            <input type="hidden" name="search_in_description" value="1"/>
        {/if}
        <button class="button-search" type="submit"></button>
        {$extra_form_fields}
    </form>
</div>
<script type="text/javascript">
    tl(function () {

        var box = $('#box-{$id}');
        var searchCloseKey = true;
        var closeSearch = function () {
            setTimeout(function () {
                if (searchCloseKey) {
                    $('.search', box).removeClass('opened');
                    $('body').off('click', closeSearch)
                }
                searchCloseKey = true;
            }, 100)
        };

        $('.search', box).on('click', function () {
			$('.background').addClass('show');
            if (!$(this).hasClass('opened')) {
                $(this).addClass('opened');

                setTimeout(function () {
                    $('body').on('click', closeSearch)
                }, 100)
            }
        });
        $('form', box).on('click', function () {
            searchCloseKey = false
        });
		$('.background').click(function(){
			setTimeout(function(){
				$('.background').removeClass('show');
				$('.suggest-js', box).removeClass('opened')
			}, 100)
			$('.search-ico', box).removeClass('searchOpened');
		})
        var input_s = $('.suggest-js input', box);
        input_s.attr({
            autocomplete: "off"
        });
        var ssTimeout = null;
        input_s.keyup(function (e) {
            $('.suggest', box).addClass('loading');
            if (ssTimeout != null) {
                clearTimeout(ssTimeout);
            }
            ssTimeout = setTimeout(function () {
                ssTimeout = null;
                if ($(input_s).val().length > 1) {
                    jQuery.get('{$searchSuggest}', {
                        keywords: $(input_s).val()
                    }, addSuggest);
                } else {
                    addSuggest()
                };
            }, 400);
        });
        input_s.blur(function () {
            setTimeout(function () {
                $('.suggest', box).hide()
            }, 200)
        });
        input_s.focus(function () {
            if ($('.suggest', box).text()) {
                $('.suggest', box).show()
            }
        });

        $('.search-ico', box).on('click', function () {
			$('.background').toggleClass('show');
            $('.suggest-js', box).toggleClass('opened')
			if($('.suggest-js', box).hasClass('opened')){
				input_s.focus();
				$(this).addClass('searchOpened');
			}else{
				$(this).removeClass('searchOpened');
			}
        });

        addSuggest();

        function addSuggest(data = '') {
            let $suggestContent = $('<div class="suggest">' + data + '</div>');
            if (!data) {
                $suggestContent.hide()
            }

            {if $searchHistory}
                $suggestContent = addHistory($suggestContent, data)
            {/if}

            $('.suggest', box).remove();
            $('.suggest-js', box).append($suggestContent)
        }

        {if $searchHistory}
        const historyItems = {$historyItems};
        function addHistory($suggestContent, data = '') {
            $('a', $suggestContent).on('click', function () {
                let searchHistory = localStorage.getItem('searchHistory') || '[]';
                searchHistory = JSON.parse(searchHistory);
                const html = $(this).parent().html();
                if (!searchHistory.find(i => i == html)) {
                    searchHistory.push(html);
                }
                if (searchHistory.length > historyItems) {
                    searchHistory.splice(0, searchHistory.length - historyItems)
                }
                localStorage.setItem('searchHistory', JSON.stringify(searchHistory))
            });

            let searchHistory = localStorage.getItem('searchHistory') || '[]';
            searchHistory = JSON.parse(searchHistory);
            if (searchHistory.length) {
                let $history = $(`<span class="type-block history">
                                      <strong class="items-title">{$smarty.const.SEARCH_HISTORY}</strong>
                                  </span>`);
                searchHistory.forEach(function (item) {
                    $history.append(`<span class="item">${ item }</span>`);
                })
                if (data) {
                    $suggestContent.prepend($history)
                } else {
                    const $wrap = $('<div class="wrap"></div>');
                    $wrap.append($history);
                    $suggestContent.prepend($wrap)
                }
            }
            return $suggestContent;
        }
        {/if}
    })
</script>