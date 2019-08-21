/*!
* json-highlight v0.1.2
* Copyright (c) 2019 Bowen Zhao
* Released under the MIT License.
*/
!function(e,n){"object"==typeof exports&&"undefined"!=typeof module?module.exports=n():"function"==typeof define&&define.amd?define(n):(e=e||self).jsonHighlight=n()}(this,function(){"use strict";return function(e){return e?("string"!=typeof e&&(e=JSON.stringify(e,null,2)),e.replace(/("(\\u[a-zA-Z0-9]{4,5}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g,function(e){if(/^(".+):$/.test(e))return'<span class="json-key">'+RegExp.$1+"</span>:";var n="number";return/true|false/.test(e)&&(n="boolean"),/null/.test(e)&&(n="null"),/^".+"$/.test(e)&&(n="string"),'<span class="json-'+n+'">'+e+"</span>"})):""}});
