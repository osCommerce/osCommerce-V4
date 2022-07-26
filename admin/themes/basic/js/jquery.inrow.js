jQuery.fn.inrow = function(options){
  var options = jQuery.extend({
    item1: false,
    item2: false,
    item3: false,
    item4: false,
    item5: false,
    item6: false
  },options);
  return this.each(function(j) {
    if (options.item1 != false){
      heightItem = 0;
      jQuery(this).find(options.item1).each(function(){
        if (heightItem < $(this).height()){
          heightItem = $(this).height();
        }
      });
      jQuery(this).find(options.item1).css('min-height',heightItem+'px');
    }
    if (options.item2 != false){
      heightItem = 0;
      jQuery(this).find(options.item2).each(function(){
        if (heightItem < $(this).height()){
          heightItem = $(this).height();
        }
      });
      jQuery(this).find(options.item2).css('min-height',heightItem+'px');
    }
    if (options.item3 != false){
      heightItem = 0;
      jQuery(this).find(options.item3).each(function(){
        if (heightItem < $(this).height()){
          heightItem = $(this).height();
        }
      });
      jQuery(this).find(options.item3).css('min-height',heightItem+'px');
    }
    if (options.item4 != false){
      heightItem = 0;
      jQuery(this).find(options.item4).each(function(){
        if (heightItem < $(this).height()){
          heightItem = $(this).height();
        }
      });
      jQuery(this).find(options.item4).css('min-height',heightItem+'px');
    }
    if (options.item5 != false){
      heightItem = 0;
      jQuery(this).find(options.item5).each(function(){
        if (heightItem < $(this).height()){
          heightItem = $(this).height();
        }
      });
      jQuery(this).find(options.item5).css('min-height',heightItem+'px');
    }
    if (options.item6 != false){
      heightItem = 0;
      jQuery(this).find(options.item6).each(function(){
        if (heightItem < $(this).height()){
          heightItem = $(this).height();
        }
      });
      jQuery(this).find(options.item6).css('min-height',heightItem+'px');
    }
    
  });
};
