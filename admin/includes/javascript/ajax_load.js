
                                $.fn.load = function(options){
                                  var _options = jQuery.extend({
                                    'format': "html",
                                  }, options);
                                  
                                  return this.each(function(){
                                    $(this).click(function(event){
                                      var e = window.event||event;
                                      e.preventDefault();
                                      var href = $(this).attr('href');
                                      $.get(href,'', function(data){
                                        $(_options.container).html(data);
                                        set_plugin(options.container);
                                      }, _options.format);
                                      return false;
                                    })
                                  })
                                }
                                
                                function set_plugin(container){
                                  $('a.btn').load({
                                  'container':container
                                  });
                                }
                                /*
                                $.fn.fileUpload = function(options){
                                  var _option = jQuery.extend({
                                    container : {}
                                  }, options);
                                  
                                  return this.each(function(){
                                    $(this).change(function(event){
                                       var e = window.event||event;
                                       return false;
                                    })
                                  })
                                }
                                */
                                function collectData(form_id, filesContainer){
                                  form = new FormData();
                                  $.each($('#'+form_id+' :input'), function(i, e){
                                    if ( $(e).attr('type') != 'file' && $(e).attr('name') != 'undefined' ) {
                                      if ($(e).attr('type') == 'radio' && !$(e).prop('checked')) return;
                                      if ($(e).attr('type') == 'checkbox' && !$(e).prop('checked')) return;  
                                      if ($(e).attr('name') == 'undefined') return;      
                                      form.append($(e).attr('name'), $(e).val());
                                    } 
                                  });

                                  if ( $(filesContainer).size() > 0 ){
                                    $.each(filesContainer, function(i, e){
                                      form.append(e.input, e, e.name);  
                                    })
                                  }
                                  return form;
                                }
                                
                                function fileUpload(ev, input, filesContainer){
                                       var name = $(input).attr('name');
                                       if (isNaN(filesContainer.length) || typeof(filesContainer.length) != 'number') {
                                          filesContainer.length = 0;
                                       }                                       
                                       if (ev.target.files.length > 0){
                                        var k =0;
                                        $.each(filesContainer, function(i, e){
                                          if( e instanceof File && e['input'] == name){
                                            delete(filesContainer[i]);
                                            filesContainer.length--;
                                            k = i;
                                          } else {
                                            filesContainer[k] = filesContainer[i];
                                            k++;
                                          }
                                          
                                        });
                                        $.each(ev.target.files, function(i, fitem){
                                          fitem['input'] = name;
                                          filesContainer[filesContainer.length] = fitem;
                                          filesContainer.length++;
                                        });
                                       }                                  
                                }
                                
                                function addFileListeners(obj, filesContainer){
                                  $.each($(obj), function(i, e){ 
                                    if (e.addEventListener){
                                      e.addEventListener('change', function(ev){
                                        var ev = window.event||ev;
                                        fileUpload(ev, e, filesContainer)
                                      }, true)
                                    } else {
                                      e.attachEvent("onchange", function(ev){
                                        var ev = window.event||ev;
                                        fileUpload(ev, e, filesContainer)
                                      });
                                    }
                                   });
                                }

