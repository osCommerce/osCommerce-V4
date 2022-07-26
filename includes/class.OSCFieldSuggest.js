/**
 * osCommerce: JS OSCFieldSuggest
 *
 * File: includes/class.OSCFieldSuggestjs
 * Version: 1.0
 * Date: 2007-03-28 17:49
 * Author: Timo Kiefer - timo.kiefer_(at)_kmcs.de
 * Organisation: KMCS - www.kmcs.de
 * Licence: General Public Licence 2.0
 */

/**
 * The field gots a suggestlist..
 *
 * @param id please give your fields an ID
 * @param file_layout the xslt document
 * @param file_data the xml document, that will be generated
 * @example
 *   var myFieldSuggestion = new OSCFieldSuggest('search_field_id', 'includes/search_suggest.xsl', 'searchsuggest.php');
 *   //params will be automatically added like searchsuggest.php?myformfieldname=myformfieldvalue
 */


function detectIElte6() {
	var browser=navigator.appName
	var b_version=navigator.appVersion
	var version=parseFloat(b_version)

	if ( b_version.indexOf("MSIE 6.0")!=-1 ) {
	//alert("IE"+b_version)
		return 1;
	}
	else {
		return 0;
	}
}

function OSCFieldSuggest(id, file_layout, file_data) {
  base = this;
  base.FILE_XSLT_LAYOUT = file_layout;
  base.FILE_XML_DATA = file_data;
  base._OBJ = document.getElementById(id);
  base.name = 'test';
  base.index = -1;
  base.maxindex = 0;
  base.hideSuggest = true;
  if(base._OBJ) {
    //define the functions..
    base.createXmlHttpRequest = function() {
      var requestIntance = false;
      if (window.XMLHttpRequest) { //FE
        requestIntance = new XMLHttpRequest();
        if (requestIntance.overrideMimeType) {
          requestIntance.overrideMimeType('text/xml');
        }
      } else if (window.ActiveXObject) { // IE
        try {
          requestIntance = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          try { //last chance..
            requestIntance = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {}
        }
      }
     if(!requestIntance) {
        alert("Sorry, your browser don't support a little bit AJAX");
      }
      return requestIntance;
    };
    base.loadDocument = function(file, funcAfterDocumentLoaded) {
      var myRequest = base.createXmlHttpRequest();
      myRequest.open('GET', file, true);
      try { myRequest.responseType = 'msxml-document'; } catch(e){}
      myRequest.onreadystatechange = function(e) {
        if(myRequest.readyState == 4 && myRequest.status == 200) {
          funcAfterDocumentLoaded(myRequest);
        } else if(myRequest.readyState == 4) {
          //error file isn't loaded.. 
          alert("Sorry, the file " + file + " couldn't loaded!");
        }
      };
      myRequest.send(null);
    };
    base.parseXmlDocument = function(xsltLayout, xmlData) {
      var xmlDoc = xmlData.documentElement;
      if (xmlDoc.childNodes[0].childNodes.length > 0){
        if(document.all) {
          return(xmlData.transformNode(xsltLayout));
        } else {
          var processor = new XSLTProcessor();
          processor.importStylesheet(xsltLayout);
          var result = processor.transformToDocument(xmlData);
          var xmls = new XMLSerializer();
          var str = xmls.serializeToString(result);
//          var re = new RegExp("&lt;b&gt;", "gi");
//          var re1 = new RegExp("&lt;/b&gt;", "gi");
//          return(str.replace(re, '<b>').replace(re1, '</b>'));
          var re = new RegExp("&lt;span style=\"color: #999;\"&gt;", "gi");
          var re1 = new RegExp("&lt;/span&gt;", "gi");
          return(str.replace(re, '<span style="color: #999;">').replace(re1, '</span>'));
        }
      } else {
        return '';
      }
    };
    base.getDocumentOffsetTop = function(obj) {
      return(parseInt(obj.offsetTop) + ((obj.offsetParent) ? base.getDocumentOffsetTop(obj.offsetParent) : 0));
    };
    base.getDocumentOffsetLeft = function(obj) {
      return(parseInt(obj.offsetLeft) + ((obj.offsetParent) ? base.getDocumentOffsetLeft(obj.offsetParent) : 0));
    };
    base.show = function() {
      base._OBJ_panel.style.visibility = 'visible';
    };
    base.hide = function() {
	  if (base.hideSuggest)
		base._OBJ_panel.style.visibility = 'hidden';
    };
    base.suggestList = function() {

		base.loadDocument(base.FILE_XML_DATA + "?" + base.name + "=" + base._OBJ.value, function(request) {
        //base.loadDocument(base.FILE_XML_DATA + "?" + base._OBJ.name + "=" + base._OBJ.value, function(request) {
       
        var parceResult = base.parseXmlDocument(base._xsltSheet, request.responseXML);
        if (parceResult != '') {
          base._OBJ_panel.innerHTML = parceResult;
          base._OBJ_panel.style.top = (base.getDocumentOffsetTop(base._OBJ) + base._OBJ.offsetHeight) + "px";
          base._OBJ_panel.style.left = (base.getDocumentOffsetLeft(base._OBJ) - 0) + "px";
          base.show();
        } else {
          base.hide();
        }
      });
    };
    //load xslt layout
    base.loadDocument(base.FILE_XSLT_LAYOUT, function(request) {
      base._xsltSheet = request.responseXML;
    });
    //create html panel to show
    base._OBJ_panel = document.createElement('div');
    base._OBJ_panel.style.visibility = 'hidden';
    base._OBJ_panel.style.position = 'absolute';
    base._OBJ_panel.style.overflow = 'auto';
    base._OBJ_panel.style.height = '350';
    base._OBJ_panel.style.top = 0 + "px";
    base._OBJ_panel.style.left = 0 + "px";
    base._OBJ_panel.style.zIndex = 10000;
    base._OBJ_panel.className = 'suggestBox';
    base._OBJ.parentNode.appendChild(base._OBJ_panel);
    //set the events
    base._OBJ.onkeyup = function(e) {
      if( navigator.appName.indexOf("Microsoft") == -1 ) {
        event = e;
      }
      var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
      if(base._OBJ.value.length > 0 ) {
        if( keyCode != 40 && keyCode != 38 && keyCode != 13 ) {
          base.suggestList();
          base.index = -1;
          base.maxindex = 0;
        } else {
          base.getDestinations(e);  
        }  
      } 
    };
    base._OBJ.onblur = function(e) { //lost focus
      //waiting a few milli sec. .. before hide the clicked panel ;)
      //alert('base._OBJ.onblur');
      if (base._OBJ.value == '') base._OBJ.value = text_enter_keywords;
      base.hideSuggest = true;
      setTimeout(function() {
        base.hide();
      }, 500);
    };

    base._OBJ.onfocus = function(e) { //got focus
      if (base._OBJ.value == text_enter_keywords) base._OBJ.value = '';
      if(base._OBJ.value.length > 0) {
        base.suggestList();
      }
    };

    base._OBJ_panel.onblur = function(e) {
	 base.hideSuggest = true 
      setTimeout(function() {
        base.hide();
      }, 500);
    }
    base._OBJ_panel.onfocus = function(e) { //got focus
		//alert('base._OBJ.onfocus');
		base.hideSuggest = false;
    };
    base.getDestinations = function (e) {

        if( navigator.appName.indexOf("Microsoft") == -1 ) {
          event = e;
        }        
        var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
        //if( document.getElementById('tr_'+base.index) == undefined || document.getElementById('tr_'+base.index) == null ) {
          //  base.index = 0;
        //}
        if( (keyCode == 38 || keyCode == 40) && base.index != -1 ) {
            if( document.getElementById('tr'+(base.index)) != undefined || document.getElementById('tr'+(base.index)) != null ) {
                document.getElementById('tr'+(base.index)).onmouseout(); 
            }  
        }                
        if( keyCode == 40 ) {//cursor down
            base.index++;
            if( document.getElementById('tr'+base.index) != undefined || document.getElementById('tr'+base.index) != null ) {
              document.getElementById('tr'+base.index).onmouseover();
              base.maxindex = base.index;
              base._OBJ_panel.scrollTop = base._OBJ_panel.scrollTop + 5; 
            }            
        } else if( keyCode == 38 ) {//cursor up 
          if( base.index !=0 && base.index != -1 ) {
            base.index--;
            if( document.getElementById('tr'+(base.index)) != undefined || document.getElementById('tr'+(base.index)) != null ) {
              document.getElementById('tr'+base.index).onmouseover();
            } else {
              document.getElementById('tr'+base.maxindex).onmouseover();
            }
            base._OBJ_panel.scrollTop = base._OBJ_panel.scrollTop - 5;  
          } else {
            base.index = -1;
          }              
        }                           
    };     
  } else {
    //no field found..
    alert("Field with ID " + id + " couldn't found!");
  }  
};

document.onkeyup = function(event) {
  if( event != null && event != undefined ) {
    var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
    if(keyCode == 13 && oscSearchSuggest.index != -1) {
      document.getElementById('td'+oscSearchSuggest.index).onclick();
      return false;      
    } else {
      return true;
    }
  }  
}
