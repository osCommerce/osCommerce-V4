/*!
 * vue3-sortablejs v1.0.7
 * (c) 2023 Eliott Vincent
 * @license MIT
 */
var sortablejs=function(t){"use strict";function e(t){return t&&"object"==typeof t&&"default"in t?t:{default:t}}var n=e(t),o=function(t,e){!0!==(e.value||{}).disabled?(t.$s={},t.$s.sortable=null,t.$s.options=(e.value||{}).options||null,t.$s.sortable=new n.default(t,{...t.$s.options}),s(t,"ready",{sortable:t.$s.sortable})):l(t)},s=function(t,e,n){const o=new CustomEvent(e);for(let t in n)o[t]=n[t];t.dispatchEvent(o)},l=function(t){(t.$s||{}).sortable&&(t.$s.sortable.destroy(),t.$s.sortable=null),t.$s={}},r={beforeMount:(t,e)=>o(t,e),updated:(t,e)=>function(t,e){JSON.stringify(e.value||{})!==JSON.stringify(e.oldValue||{})&&o(t,e)}(t,e),beforeUnmount:t=>function(t){l(t)}(t)};return{install:function(t){t.directive("sortable",r)},directive:r}}(Sortable);
