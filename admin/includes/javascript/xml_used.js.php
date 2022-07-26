<script language="javascript">
function setCookie (name, value, expires, path, domain, secure) {
      document.cookie = name + "=" + escape(value) +
        ((expires) ? "; expires=" + expires : "") +
        ((path) ? "; path=" + path : "") +
        ((domain) ? "; domain=" + domain : "") +
        ((secure) ? "; secure" : "");
}

function getCookie(name) {
     var dc = document.cookie;
     var prefix = name + "=";
     var begin = dc.indexOf("; " + prefix);
     if (begin == -1) {
       begin = dc.indexOf(prefix);
       if (begin != 0) return null;
     } else
       begin += 2;
     var end = document.cookie.indexOf(";", begin);
     if (end == -1)
       end = dc.length;
     return unescape(dc.substring(begin + prefix.length, end));
   }


 function setflagcookie(value,cname,addon) {
   checked_not = document.getElementById(addon+value).checked;
   //cname=xml_products/categories/customers/orders
   cookie_name = getCookie(cname);
   if (cookie_name == null  || cookie_name == '') {
       cookie_name = '';
   }

   if (checked_not == false) {
     str = "_" + value;
     cookie_name = cookie_name.replace(str,"");
     setCookie(cname, cookie_name, "Mon, 01-Jan-<?php echo (date("Y")+10);?> 00:00:00 GMT", "/");
   } else {
    setCookie(cname, cookie_name+"_"+value, "Mon, 01-Jan-<?php echo (date("Y")+10);?> 00:00:00 GMT", "/");
   }


 }

 //checks if some data for backup selected or 0
 function check_selected_datas(cname,datatype) {
     cookie_name = getCookie(cname);
     if (cookie_name == null || cookie_name == '') {
       if (datatype == "products") message = "<?php echo TEXT_NO_SELECTED_PRODUCTS?>";
       if (datatype == "categories") message = "<?php echo TEXT_NO_SELECTED_CATEGORIES?>";
       if (datatype == "customers") message = "<?php echo TEXT_NO_SELECTED_CUSTOMERS?>";
       if (datatype == "orders") message = "<?php echo TEXT_NO_SELECTED_ORDERS?>";         
       alert(message);
       return false;
     } else {
       location.href = "<?php echo tep_href_link(FILENAME_BACKUP_XML_DATA,'action=selected&datatype=')?>" + datatype;
       return true;
     }

 }

  function restoreBoxes(cname,addon) {
     cookie_name = getCookie(cname);
     if (cookie_name == null  || cookie_name == '') {
       cookie_name = '';
     }
     cookie_name = cookie_name.split('_');
     n=0;
     while(n != cookie_name.length) {
       if (document.getElementById(addon+cookie_name[n]) != null) {
         document.getElementById(addon+cookie_name[n]).checked = true;
       }
       n++;
     }


 }
 </script>

