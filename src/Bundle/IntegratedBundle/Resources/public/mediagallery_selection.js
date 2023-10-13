<<<<<<< Updated upstream
/*! For license information please see mediagallery_selection.js.LICENSE.txt */
(()=>{function e(t){return e="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},e(t)}function t(e){return function(e){if(Array.isArray(e))return r(e)}(e)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(e)||function(e,t){if(!e)return;if("string"==typeof e)return r(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return r(e,t)}(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function r(e,t){(null==t||t>e.length)&&(t=e.length);for(var r=0,n=new Array(t);r<t;r++)n[r]=e[r];return n}function n(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function o(t,r,n){return(r=function(t){var r=function(t,r){if("object"!==e(t)||null===t)return t;var n=t[Symbol.toPrimitive];if(void 0!==n){var o=n.call(t,r||"default");if("object"!==e(o))return o;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===r?String:Number)(t)}(t,"string");return"symbol"===e(r)?r:String(r)}(r))in t?Object.defineProperty(t,r,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[r]=n,t}function i(){"use strict";i=function(){return r};var t,r={},n=Object.prototype,o=n.hasOwnProperty,a=Object.defineProperty||function(e,t,r){e[t]=r.value},c="function"==typeof Symbol?Symbol:{},l=c.iterator||"@@iterator",u=c.asyncIterator||"@@asyncIterator",s=c.toStringTag||"@@toStringTag";function f(e,t,r){return Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}),e[t]}try{f({},"")}catch(t){f=function(e,t,r){return e[t]=r}}function d(e,t,r,n){var o=t&&t.prototype instanceof _?t:_,i=Object.create(o.prototype),c=new k(n||[]);return a(i,"_invoke",{value:P(e,r,c)}),i}function h(e,t,r){try{return{type:"normal",arg:e.call(t,r)}}catch(e){return{type:"throw",arg:e}}}r.wrap=d;var p="suspendedStart",y="suspendedYield",m="executing",v="completed",g={};function _(){}function w(){}function b(){}var S={};f(S,l,(function(){return this}));var E=Object.getPrototypeOf,L=E&&E(E(T([])));L&&L!==n&&o.call(L,l)&&(S=L);var O=b.prototype=_.prototype=Object.create(S);function j(e){["next","throw","return"].forEach((function(t){f(e,t,(function(e){return this._invoke(t,e)}))}))}function x(t,r){function n(i,a,c,l){var u=h(t[i],t,a);if("throw"!==u.type){var s=u.arg,f=s.value;return f&&"object"==e(f)&&o.call(f,"__await")?r.resolve(f.__await).then((function(e){n("next",e,c,l)}),(function(e){n("throw",e,c,l)})):r.resolve(f).then((function(e){s.value=e,c(s)}),(function(e){return n("throw",e,c,l)}))}l(u.arg)}var i;a(this,"_invoke",{value:function(e,t){function o(){return new r((function(r,o){n(e,t,r,o)}))}return i=i?i.then(o,o):o()}})}function P(e,r,n){var o=p;return function(i,a){if(o===m)throw new Error("Generator is already running");if(o===v){if("throw"===i)throw a;return{value:t,done:!0}}for(n.method=i,n.arg=a;;){var c=n.delegate;if(c){var l=q(c,n);if(l){if(l===g)continue;return l}}if("next"===n.method)n.sent=n._sent=n.arg;else if("throw"===n.method){if(o===p)throw o=v,n.arg;n.dispatchException(n.arg)}else"return"===n.method&&n.abrupt("return",n.arg);o=m;var u=h(e,r,n);if("normal"===u.type){if(o=n.done?v:y,u.arg===g)continue;return{value:u.arg,done:n.done}}"throw"===u.type&&(o=v,n.method="throw",n.arg=u.arg)}}}function q(e,r){var n=r.method,o=e.iterator[n];if(o===t)return r.delegate=null,"throw"===n&&e.iterator.return&&(r.method="return",r.arg=t,q(e,r),"throw"===r.method)||"return"!==n&&(r.method="throw",r.arg=new TypeError("The iterator does not provide a '"+n+"' method")),g;var i=h(o,e.iterator,r.arg);if("throw"===i.type)return r.method="throw",r.arg=i.arg,r.delegate=null,g;var a=i.arg;return a?a.done?(r[e.resultName]=a.value,r.next=e.nextLoc,"return"!==r.method&&(r.method="next",r.arg=t),r.delegate=null,g):a:(r.method="throw",r.arg=new TypeError("iterator result is not an object"),r.delegate=null,g)}function A(e){var t={tryLoc:e[0]};1 in e&&(t.catchLoc=e[1]),2 in e&&(t.finallyLoc=e[2],t.afterLoc=e[3]),this.tryEntries.push(t)}function N(e){var t=e.completion||{};t.type="normal",delete t.arg,e.completion=t}function k(e){this.tryEntries=[{tryLoc:"root"}],e.forEach(A,this),this.reset(!0)}function T(r){if(r||""===r){var n=r[l];if(n)return n.call(r);if("function"==typeof r.next)return r;if(!isNaN(r.length)){var i=-1,a=function e(){for(;++i<r.length;)if(o.call(r,i))return e.value=r[i],e.done=!1,e;return e.value=t,e.done=!0,e};return a.next=a}}throw new TypeError(e(r)+" is not iterable")}return w.prototype=b,a(O,"constructor",{value:b,configurable:!0}),a(b,"constructor",{value:w,configurable:!0}),w.displayName=f(b,s,"GeneratorFunction"),r.isGeneratorFunction=function(e){var t="function"==typeof e&&e.constructor;return!!t&&(t===w||"GeneratorFunction"===(t.displayName||t.name))},r.mark=function(e){return Object.setPrototypeOf?Object.setPrototypeOf(e,b):(e.__proto__=b,f(e,s,"GeneratorFunction")),e.prototype=Object.create(O),e},r.awrap=function(e){return{__await:e}},j(x.prototype),f(x.prototype,u,(function(){return this})),r.AsyncIterator=x,r.async=function(e,t,n,o,i){void 0===i&&(i=Promise);var a=new x(d(e,t,n,o),i);return r.isGeneratorFunction(t)?a:a.next().then((function(e){return e.done?e.value:a.next()}))},j(O),f(O,s,"Generator"),f(O,l,(function(){return this})),f(O,"toString",(function(){return"[object Generator]"})),r.keys=function(e){var t=Object(e),r=[];for(var n in t)r.push(n);return r.reverse(),function e(){for(;r.length;){var n=r.pop();if(n in t)return e.value=n,e.done=!1,e}return e.done=!0,e}},r.values=T,k.prototype={constructor:k,reset:function(e){if(this.prev=0,this.next=0,this.sent=this._sent=t,this.done=!1,this.delegate=null,this.method="next",this.arg=t,this.tryEntries.forEach(N),!e)for(var r in this)"t"===r.charAt(0)&&o.call(this,r)&&!isNaN(+r.slice(1))&&(this[r]=t)},stop:function(){this.done=!0;var e=this.tryEntries[0].completion;if("throw"===e.type)throw e.arg;return this.rval},dispatchException:function(e){if(this.done)throw e;var r=this;function n(n,o){return c.type="throw",c.arg=e,r.next=n,o&&(r.method="next",r.arg=t),!!o}for(var i=this.tryEntries.length-1;i>=0;--i){var a=this.tryEntries[i],c=a.completion;if("root"===a.tryLoc)return n("end");if(a.tryLoc<=this.prev){var l=o.call(a,"catchLoc"),u=o.call(a,"finallyLoc");if(l&&u){if(this.prev<a.catchLoc)return n(a.catchLoc,!0);if(this.prev<a.finallyLoc)return n(a.finallyLoc)}else if(l){if(this.prev<a.catchLoc)return n(a.catchLoc,!0)}else{if(!u)throw new Error("try statement without catch or finally");if(this.prev<a.finallyLoc)return n(a.finallyLoc)}}}},abrupt:function(e,t){for(var r=this.tryEntries.length-1;r>=0;--r){var n=this.tryEntries[r];if(n.tryLoc<=this.prev&&o.call(n,"finallyLoc")&&this.prev<n.finallyLoc){var i=n;break}}i&&("break"===e||"continue"===e)&&i.tryLoc<=t&&t<=i.finallyLoc&&(i=null);var a=i?i.completion:{};return a.type=e,a.arg=t,i?(this.method="next",this.next=i.finallyLoc,g):this.complete(a)},complete:function(e,t){if("throw"===e.type)throw e.arg;return"break"===e.type||"continue"===e.type?this.next=e.arg:"return"===e.type?(this.rval=this.arg=e.arg,this.method="return",this.next="end"):"normal"===e.type&&t&&(this.next=t),g},finish:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var r=this.tryEntries[t];if(r.finallyLoc===e)return this.complete(r.completion,r.afterLoc),N(r),g}},catch:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var r=this.tryEntries[t];if(r.tryLoc===e){var n=r.completion;if("throw"===n.type){var o=n.arg;N(r)}return o}}throw new Error("illegal catch attempt")},delegateYield:function(e,r,n){return this.delegate={iterator:T(e),resultName:r,nextLoc:n},"next"===this.method&&(this.arg=t),g}},r}function a(e,t,r,n,o,i,a){try{var c=e[i](a),l=c.value}catch(e){return void r(e)}c.done?t(l):Promise.resolve(l).then(n,o)}function c(e){return function(){var t=this,r=arguments;return new Promise((function(n,o){var i=e.apply(t,r);function c(e){a(i,n,o,c,l,"next",e)}function l(e){a(i,n,o,c,l,"throw",e)}c(void 0)}))}}var l={};function u(){var e=document.querySelectorAll(".select_multimedia_button"),t=document.querySelectorAll(".selected_images");e.forEach((function(e){e.addEventListener("click",(function(e){var t=e.target.dataset.relationid;selected_relation=l[t],h(selected_relation)}))})),t.forEach((function(e){e.addEventListener("click",(function(e){var t=e.target.closest("li"),r=e.target.closest(".remove");if(t&&!r){var n=t.closest(".selected_images").dataset.relationid;selected_relation=l[n],h(selected_relation)}}))}))}function s(){selected_relation.selected_images.forEach((function(e){var t,r,n,o;!function(e){var t=document.querySelector(selected_relation.selected_images_selector);"select_one"===selected_relation.modus&&(t.innerHTML=""),t.appendChild(e)}((t=e,r=document.querySelector("#selected_image").cloneNode(!0),n=r.querySelector("img"),o=r.querySelector(".remove_link"),r.id=t.id,r.classList.remove("hidden"),n.src=t.thumbnail,o.setAttribute("onclick","removeImage(event)"),r))}))}function f(){document.querySelector(selected_relation.selected_images_selector).innerHTML="",s(),document.querySelector(selected_relation.input_selector).value=selected_relation.selected_images.map((function(e){return e.id})).join(",")}function d(){var e;(e=document.querySelector(selected_relation.iframe_selector)).src=e.src,document.querySelector(selected_relation.wrap_selector).classList.remove("show"),document.querySelector("#dropdown_overlay").classList.add("hide"),window.popupShown=!1}function h(e){window.popupShown=!0,document.querySelector(e.wrap_selector).classList.add("show"),document.querySelector("#dropdown_overlay").classList.remove("hide")}window.onload=c(i().mark((function e(){return i().wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return document.querySelectorAll(".mediagallery_selector").forEach((function(e){var t=e.getAttribute("id"),r=e.parentNode.classList.contains("relation")?"integrated_content[relations][".concat(t,"]"):e.querySelector(".selected_images").getAttribute("data-fieldName");l[t]={modus:e.querySelector(".select_multimedia_button").dataset.multiple?"select_multiple":"select_one",selected_images:[],relationid:t,types:JSON.parse(e.querySelector(".select_multimedia_button").dataset.types),input_selector:'input[name="'.concat(r,'"]'),selected_images_selector:"#".concat(t," .selected_images"),wrap_selector:".".concat(t,".wrap"),iframe_selector:".".concat(t,".iframe")},l[t].types_url=l[t].types.reduce((function(e,t){return e+"available_contenttypes[]="+t.type+"&"}),"");var n=document.createElement("div");n.className="wrap media-library iframe-wrapper close-outside ".concat(t);var o=document.createElement("iframe");o.className="iframe ".concat(t),n.appendChild(o),document.body.appendChild(n)})),Object.values(l).forEach((function(e){var t="".concat("/admin/media/").concat(e.modus,"?page=1&").concat(e.types_url);document.querySelector(e.iframe_selector).setAttribute("src",t)})),e.next=4,new Promise((function(e){document.querySelectorAll(".mediagallery_selector").forEach((function(e){var t=e.getAttribute("id"),r=e.querySelectorAll(".previously_selected_images li");r.forEach((function(e){l[t].selected_images.push(function(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?n(Object(r),!0).forEach((function(t){o(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):n(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}({},e.dataset))}))})),e()}));case 4:Object.values(l).forEach((function(e){selected_relation=e,f()})),u();case 6:case"end":return e.stop()}}),e)}))),window.removeImage=function(e){e.preventDefault();var t=e.target.closest("li").id,r=e.target.closest(".mediagallery_selector").id;selected_relation=l[r],selected_relation.selected_images=selected_relation.selected_images.filter((function(e){return e.id!==t})),f()},window.addEventListener("message",(function(e){var r,n;if("cancel"!==e.data){if("string"==typeof e.data&&e.data.length>0){var o=(r=JSON.parse(e.data),n=selected_relation.types.map((function(e){return e.type})),r.filter((function(e){return n.includes(e.content_type)})));o.length>0?(!function(e){"select_one"==selected_relation.modus?selected_relation.selected_images=e:selected_relation.selected_images=[].concat(t(selected_relation.selected_images),t(e)).filter((function(e,t,r){return r.findIndex((function(t){return t.id===e.id}))===t}))}(o),f(),d()):console.log("no image selected")}}else d()}))})();
=======
/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!********************************************************************************!*\
  !*** ./src/Bundle/ContentBundle/Resources/assets/js/mediagallery_selection.js ***!
  \********************************************************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw new Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator["return"] && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw new Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, "catch": function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }
var form_relations = {}; //this holds all the form relation objects with an id
var mediagallery_link = '/admin/media/';
window.onload = /*#__PURE__*/_asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
  return _regeneratorRuntime().wrap(function _callee$(_context) {
    while (1) switch (_context.prev = _context.next) {
      case 0:
        populateFormRelations();
        generateSrcAttributeForIframes();
        _context.next = 4;
        return populateSelectedImages();
      case 4:
        // wait for populateSelectedImages() to finish
        setupFormRelations();
        addEventListeners();
      case 6:
      case "end":
        return _context.stop();
    }
  }, _callee);
}));
function setupFormRelations() {
  Object.values(form_relations).forEach(function (form_relation) {
    selected_relation = form_relation;
    rebuildDOM();
  });
}
function populateSelectedImages() {
  return new Promise(function (resolve) {
    document.querySelectorAll('.mediagallery_selector').forEach(function (relation) {
      var relationid = relation.getAttribute('id');
      var given_images = relation.querySelectorAll('.previously_selected_images li');
      given_images.forEach(function (given_image) {
        form_relations[relationid].selected_images.push(_objectSpread({}, given_image.dataset));
      });
    });
    resolve();
  });
}
function populateFormRelations() {
  document.querySelectorAll('.mediagallery_selector').forEach(function (item) {
    var id = item.getAttribute('id');
    var inputIdentifier = item.parentNode.classList.contains('relation') ? "integrated_content[relations][".concat(id, "]") : item.querySelector('.selected_images').getAttribute('data-fieldName');
    form_relations[id] = {
      modus: item.querySelector('.select_multimedia_button').dataset.multiple ? 'select_multiple' : 'select_one',
      selected_images: [],
      relationid: id,
      types: JSON.parse(item.querySelector('.select_multimedia_button').dataset.types),
      input_selector: "input[name=\"".concat(inputIdentifier, "\"]"),
      selected_images_selector: "#".concat(id, " .selected_images"),
      wrap_selector: "#".concat(id, " .wrap"),
      iframe_selector: "#".concat(id, " iframe")
    };
    form_relations[id].types_url = getTypesUrl(form_relations[id].types);
  });
}
function getTypesUrl(types) {
  return types.reduce(function (accumulator, currentValue) {
    return accumulator + 'available_contenttypes[]=' + currentValue.type + '&';
  }, '');
}
function generateSrcAttributeForIframes() {
  Object.values(form_relations).forEach(function (form_relation) {
    var link = "".concat(mediagallery_link).concat(form_relation.modus, "?page=1&").concat(form_relation.types_url);
    document.querySelector(form_relation.iframe_selector).setAttribute('src', link);
  });
}
function addEventListeners() {
  var selectButtons = document.querySelectorAll('.select_multimedia_button');
  var selectedImages = document.querySelectorAll('.selected_images');
  selectButtons.forEach(function (selectButton) {
    selectButton.addEventListener('click', function (event) {
      var relationid = event.target.dataset.relationid;
      selected_relation = form_relations[relationid];
      showMediaGallery(selected_relation);
    });
  });
  selectedImages.forEach(function (selectedImage) {
    selectedImage.addEventListener('click', function (event) {
      var imageItem = event.target.closest('li');
      var removeButton = event.target.closest('.remove');
      if (imageItem && !removeButton) {
        var relationid = imageItem.closest('.selected_images').dataset.relationid;
        selected_relation = form_relations[relationid];
        showMediaGallery(selected_relation);
      }
    });
  });
}
window.removeImage = function (event) {
  event.preventDefault();
  var closest_image_id = event.target.closest('li').id;
  var closest_media_gallery_selector = event.target.closest('.mediagallery_selector').id;
  selected_relation = form_relations[closest_media_gallery_selector];
  selected_relation.selected_images = selected_relation.selected_images.filter(function (item) {
    return item.id !== closest_image_id;
  });
  rebuildDOM();
};
function createClone(selectedImage) {
  var clone = document.querySelector('#selected_image').cloneNode(true);
  var clone_img = clone.querySelector('img');
  var remove_link = clone.querySelector('.remove_link');
  clone.id = selectedImage.id;
  clone.classList.remove('hidden');
  clone_img.src = selectedImage.thumbnail;
  remove_link.setAttribute('onclick', 'removeImage(event)');
  return clone;
}
function placeClone(clone) {
  var selectedImagesContainer = document.querySelector(selected_relation.selected_images_selector);
  if (selected_relation.modus === 'select_one') {
    selectedImagesContainer.innerHTML = '';
  }
  selectedImagesContainer.appendChild(clone);
}
function filterImages(selection) {
  var allowed_types = selected_relation.types.map(function (item) {
    return item.type;
  });
  return selection.filter(function (item) {
    return allowed_types.includes(item.content_type);
  });
}
function addImageIDsToInputField() {
  document.querySelector(selected_relation.input_selector).value = selected_relation.selected_images.map(function (item) {
    return item.id;
  }).join(',');
}
function emptyShownImagesInDOM() {
  document.querySelector(selected_relation.selected_images_selector).innerHTML = '';
}
function selectImagesToShow(response_from_iframe) {
  if (selected_relation.modus == 'select_one') {
    selected_relation.selected_images = response_from_iframe;
  } else {
    //concat AND filter for unique values:
    selected_relation.selected_images = [].concat(_toConsumableArray(selected_relation.selected_images), _toConsumableArray(response_from_iframe)).filter(function (v, i, a) {
      return a.findIndex(function (v2) {
        return v2.id === v.id;
      }) === i;
    });
  }
}
function populateDOMWithImages() {
  selected_relation.selected_images.forEach(function (item) {
    placeClone(createClone(item));
  });
}
window.addEventListener('message', function (e) {
  if (e.data === 'cancel') {
    closeMediaGallery();
    return;
  }
  if (typeof e.data === 'string') {
    var response_from_iframe = filterImages(JSON.parse(e.data));
    if (response_from_iframe.length > 0) {
      selectImagesToShow(response_from_iframe);
      rebuildDOM();
      closeMediaGallery();
    } else {
      console.log('no image selected');
    }
  }
});
function rebuildDOM() {
  emptyShownImagesInDOM();
  populateDOMWithImages();
  addImageIDsToInputField();
}
function closeMediaGallery() {
  reloadMediaLibrary();
  document.querySelector(selected_relation.wrap_selector).classList.remove('show');
  document.querySelector('#dropdown_overlay').classList.add('hide');
  window.popupShown = false;
}
function reloadMediaLibrary() {
  var iframe = document.querySelector(selected_relation.iframe_selector);
  iframe.src = iframe.src;
}
function showMediaGallery(selected_relation) {
  window.popupShown = true;
  document.querySelector(selected_relation.wrap_selector).classList.add('show');
  document.querySelector('#dropdown_overlay').classList.remove('hide');
}
/******/ })()
;
>>>>>>> Stashed changes
