$.fn.tlTabs = $.fn.tabs = function(options){
  var options = $.extend({
    tabContainer: '.box',
    tabHeadingContainer: 'h3'
  },options);
  return this.each(function(j) {

    var _this = $(this);

    _this.addClass('w-tabs');
    $(this).prepend('<ul class="tab-navigation"></ul>');
    $(options.tabContainer, _this).each(function (i) {
      $('ul.tab-navigation').append('<li class="tab-li"><span class="tab-a" data-href="tab_'+j+'_'+i+'">'+$(this).find(options.tabHeadingContainer).text()+'</span></li>');

      $(this).find(options.tabHeadingContainer).hide();

      $(this).attr('data-id','tab_'+j+'_'+i);
    });
    var tabContainers = $(options.tabContainer, _this);
    tabContainers.hide().filter(':first').show();

    $('ul.tab-navigation span').click(function () {
      tabContainers.hide();

      $(options.tabContainer + '[data-id="'+$(this).data('href')+'"]').show();
      $('ul.tab-navigation span').removeClass('active');
      $(this).addClass('active');

      _this.trigger('tab-click');

      return false;
    }).filter(':first').click();

    if (window.location.hash){
      $('ul.tab-navigation span[data-href="'+window.location.hash.substring(1)+'"]').click()
    }


  });
};
  