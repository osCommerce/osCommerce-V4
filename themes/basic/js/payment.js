/*if (typeof Promise != 'function') {var s=document.createElement('script');s.setAttribute('src', '//cdnjs.cloudflare.com/ajax/libs/bluebird/3.3.4/bluebird.min.js');document.head.appendChild(s);}*/
window.paymentCollection = {
    form: null,
    callbacks: {},
    init: function(form){
        this.form = form;
        this.addSubmitEvent();
    },
    checkoutReadyToPay:function(){
        var pc = this;
        var code = pc.getCurrentPayment();
        if (pc.needConfirmation){
            //var checkoutReady = new Promise(function(resolve, reject){
                $.ajax({
                  type: "POST",
                  url: pc.form.action,
                  data: $(pc.form).serialize(),
                  dataType: "json",
                  complete: function(jqXHR) {
                    var contentType = jqXHR.getResponseHeader("Content-Type");
                    if (jqXHR.status === 200 && contentType.toLowerCase().indexOf("text/html") >= 0) {
                        //seems there were redirect on checkout validation
                        window.location.reload();
                    }
                  },
                  success: function(d) {
                    //d = $.parseJSON(d);
                    if (d.formCheck && d.formCheck == 'OK' ) {
                      if (d.hasOwnProperty('_csrf')){
                          $('[name=_csrf]').val(d._csrf);
                          $('[name=csrf-token]').val(d._csrf);
                      }
            //          resolve(code);
                      if (pc.hasCallback(code)) { window[pc.callbacks[pc.getCallbackCode(code)]].apply(this);}
                    } else {
                      if (d.message.length > 0 || d.payment_error.length > 0) {
                        //setTimeout(function(){
                            if (d.message.length > 0) {
                                alertMessage(d.message);
                            }
                            if (d.payment_error.length > 0) {
                                var paymentError = '<div class="messageBox"><strong>' +
                                        d.payment_error.title +
                                        '</strong><br>' +
                                        d.payment_error.error + '</div>';
                                alertMessage(paymentError);
                            }
                        //}, timeSlide);
                      }
            //          reject();
                    }
                  }
                });
            //})
            //checkoutReady.then(function(code){ if (pc.hasCallback(code)) { window[pc.callbacks[code]].apply(this); } }).catch(function(){ })
        } else {
            if (pc.hasCallback(code)) { window[pc.callbacks[pc.getCallbackCode(code)]].apply(this); }
        }
        
    },
    getCurrentPayment:function(){
        return $('input[name=payment]:checked', this.form).val()||$('input[name=payment]:hidden', this.form).val();
    },
    getCallbackCode:function(code){
        var _code = (code ? code : this.getCurrentPayment());
        if (this.callbacks.hasOwnProperty(_code)) {
            return _code;
        }
        if (_code.lastIndexOf('_') && this.callbacks.hasOwnProperty(_code.substring(0, _code.lastIndexOf('_')))){
            _code = _code.substring(0, _code.lastIndexOf('_'));
        }
        return _code;
    },
    hasCallback:function(code){
        var _code = (code ? code : this.getCurrentPayment());
        var ret = this.callbacks.hasOwnProperty(_code);
        if (!ret && _code.lastIndexOf('_')){
            ret = this.callbacks.hasOwnProperty(_code.substring(0, _code.lastIndexOf('_')));
        }
        return ret;
    },
    needConfirmation:true,
    setNeedConfirmation:function(value){
        this.needConfirmation = value;
    },
    directSubmit:false,
    finishCallback:function(){
        this.directSubmit = true;
        this.form.submit();
    },
    checkoutPopUp: function(event){
        var pc=paymentCollection,frmCheckout = $(pc.form), onsubmitcnt = 1, pmt = $('input[name=payment]', pc.form);
        if (pc.directSubmit) {
            return true;
        } else if (pmt.length && (pmt.is(':checked') || pmt.is(':hidden'))) {
            /// trigger other on submit
            
            if (!pc.hasCallback()) return true;
            
            var submitRes = true;
            event.preventDefault();

            $.each($._data(frmCheckout, "events"), function(evetType, arr) {
              var n = 0, r = true;
              if (evetType != 'submit') return true;
              try {
                n = arr.length;
              } catch (error){
                n = 0;
              }
              for (i=0;i<n;i++) {
                if (onsubmitcnt++ > 300) return;
                try {
                  if (arr[i].handler.name != 'checkoutPopUp') {
                    var r = arr[i].handler();
                  }
                } catch (error){
                  r = true;
                }
                if (r === false){
                  submitRes = false;
                }
              }
            });
        
            if (!submitRes ) return false;
                        
            if (!$('.required-error', frmCheckout).length){
                pc.checkoutReadyToPay();
            }
        }

        return true;
        
    },
    addSubmitEvent:function(){
        var pc = this;
        if (pc.form === null){ console.error("Form is not initialized");return false; }
        var fc = $(pc.form);
        if (!fc.hasClass("popup-inited")) {
            fc.on('submit', pc.checkoutPopUp);
            fc.addClass("popup-inited");
        }
    },
    setCallbacks: function(json){
        var pc = this;
        try{
            this.callbacks = JSON.parse(JSON.stringify(json));            
        } catch (e) {
            console.error('Misconfigured callback list');
        }
    }
};
