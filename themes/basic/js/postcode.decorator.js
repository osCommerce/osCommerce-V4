function Decorator() {
    var obj = {
        init: function() {
            this.builType = 'inline';
            this.promises = [];
            this.func;
            this.params;
            this.url = "#postcode-box";
            this.source = '';
            this.prompt = '';
        },
        setPopupBuildType: function(){
            this.builType = 'popup';
        },
        setInlineBuildType: function(){
            this.builType = 'inline';
        },
        setControlFields: function(fields, outsideControl){
            var holder = this;
            holder.outsideControl = outsideControl;
            if (Array.isArray(fields)){
                holder.controlFields = fields;
                holder.controlFields.forEach(function(field){
                    if (holder.builType == 'popup'){
                        holder.createButton(field);
                    } else {
                        return field;
                    }
                })
            }
            return {};
        },
        setControlFunction: function(func, params){
            var holder = this;
            if (typeof func == 'function'){
                holder.controlFields.forEach(function(field){
                    if (holder.builType == 'popup'){
                        var _id = holder.getFieldId(field);
                        if (typeof holder.promises[_id].promise == 'object'){
                            holder.promises[_id].promise.then(function(response){
                                holder.promises[holder.getFieldId(field)].attributes = { 'func':func, 'params':params, 'container':response.container };
                                if (holder.fire){
                                    $(response.container).on('click', '.btn', function(){
                                        func.call(this, $('.suggest-input', response.container), params);
                                    })
                                } else {
                                    func.call(this, $('.suggest-input', response.container), params);
                                }
                            });
                        }
                    }else{
                        func.call(this, field, params);
                    }
                })
            }
            return holder;
        },
        getFieldId: function(field){
            return $(field).attr('id');
        },
        setSource: function(sourcename){
            this.source = sourcename;
        },
        getSuggest: function(){
            if (this.containerBox == null){
                this.containerBox = document.createElement('div');
                this.containerBox.setAttribute('id', 'postcode-box');
                var suggest = document.createElement('input'),
                head = document.createElement('h3');
                head.innerText = this.source;
                head.style.padding = '5px';
                this.containerBox.append(head);
                suggest.setAttribute('type', 'text');
                suggest.setAttribute('placeholder', this.prompt);
                suggest.style.width = "97%";
                suggest.style.margin = '5px';
                suggest.className = 'form-control suggest-input';
                if (typeof this.outsideControl == 'function'){
                    this.outsideControl(suggest);
                }
                this.containerBox.append(suggest);
                if (this.fire){
                    var button = document.createElement('button');
                    button.className = 'btn';
                    button.style.margin = '5px';
                    button.innerText = this.btn;
                    this.containerBox.append(button);
                }
            }
            return this.containerBox;
        },
        createButton: function(field){
            var holder = this;
            holder.promises[holder.getFieldId(field)] = { promise:{}, attributes:{} };
            holder.promises[holder.getFieldId(field)].promise = new Promise(function(resolve, reject){
                var a = document.createElement('a'),
                box = holder.getSuggest();
                a.className = 'icon-search';
                a.innerText = "?";
                a.setAttribute("href", holder.url);
                $(a).popUp({
                    'one_popup':false,
                    'opened': function(){ 
                        $('input', box).val('');
                        $('.pop-up-content:last').html(box);
                        if (holder.promises[holder.getFieldId(field)].attributes.hasOwnProperty('func')){                            
                            holder.promises[holder.getFieldId(field)].attributes.func.call(this, $('.suggest-input', holder.promises[holder.getFieldId(field)].attributes.container), holder.promises[holder.getFieldId(field)].attributes.params);
                        } else
                        resolve({ 'link':a, 'container':box });
                    }
                });
                field.before(a);
            })
        },
        done:function(){
            if (this.builType == 'popup'){
                $('.pop-up-close:last').trigger('click');
            }
        },
        setPrompt:function(prompt){
            this.prompt = prompt;
        },
        setImageName:function(btnName){
            this.btn = btnName;
        },
        buttonFire:function(){
            this.fire = true;
        }
    }
    obj.init();
    return obj;
}