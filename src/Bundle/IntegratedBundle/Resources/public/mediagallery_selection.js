<<<<<<< HEAD
/******/ (() => { // webpackBootstrap
/*!********************************************************************************!*\
  !*** ./src/Bundle/ContentBundle/Resources/assets/js/mediagallery_selection.js ***!
  \********************************************************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var form_relations = {}; //this holds all the form relation objects with an id
var mediagallery_link = '/admin/media/';
window.addEventListener('load', function () {
  populateFormRelations();
  populateSelectedImages().then(function () {
    setupFormRelations();
    addEventListeners();
  });
});
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
      wrap_selector: ".".concat(id, ".wrap"),
      iframe_selector: ".".concat(id, ".iframe")
    };
    form_relations[id].types_url = getTypesUrl(form_relations[id].types);
    if (!document.querySelector("iframe.iframe.".concat(id))) {
      var wrap = document.createElement('div');
      wrap.className = "wrap media-library iframe-wrapper close-outside ".concat(id);
      var iframe = document.createElement('iframe');
      iframe.className = "iframe ".concat(id);
      wrap.appendChild(iframe);
      document.body.appendChild(wrap);
    }
  });
}
function getTypesUrl(types) {
  return types.reduce(function (accumulator, currentValue) {
    return accumulator + 'available_contenttypes[]=' + currentValue.type + '&';
  }, '');
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
  if (typeof e.data === 'string' && e.data.length > 0) {
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
  var iframe = document.querySelector(selected_relation.iframe_selector);
  var selectedIds = selected_relation.selected_images.map(function (item) {
    return item.id;
  }).filter(Boolean).join(',');
  var selectedIdsQuery = selectedIds.length ? "&selected_ids=".concat(encodeURIComponent(selectedIds)) : '';
  var link = "".concat(mediagallery_link).concat(selected_relation.modus, "?page=1&").concat(selected_relation.types_url).concat(selectedIdsQuery);
  iframe.setAttribute('src', link);
  document.querySelector(selected_relation.wrap_selector).classList.add('show');
  document.querySelector('#dropdown_overlay').classList.remove('hide');
}
/******/ })()
;
=======
(()=>{function e(t){return e="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},e(t)}function t(e){return function(e){if(Array.isArray(e))return r(e)}(e)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(e)||function(e,t){if(!e)return;if("string"==typeof e)return r(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return r(e,t)}(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function r(e,t){(null==t||t>e.length)&&(t=e.length);for(var r=0,n=new Array(t);r<t;r++)n[r]=e[r];return n}function n(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function o(t,r,n){var o;return o=function(t,r){if("object"!=e(t)||!t)return t;var n=t[Symbol.toPrimitive];if(void 0!==n){var o=n.call(t,r||"default");if("object"!=e(o))return o;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===r?String:Number)(t)}(r,"string"),(r="symbol"==e(o)?o:o+"")in t?Object.defineProperty(t,r,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[r]=n,t}var c={},i="/admin/media/";function l(){selected_relation.selected_images.forEach((function(e){var t,r,n,o;!function(e){var t=document.querySelector(selected_relation.selected_images_selector);"select_one"===selected_relation.modus&&(t.innerHTML=""),t.appendChild(e)}((t=e,r=document.querySelector("#selected_image").cloneNode(!0),n=r.querySelector("img"),o=r.querySelector(".remove_link"),r.id=t.id,r.classList.remove("hidden"),n.src=t.thumbnail,o.setAttribute("onclick","removeImage(event)"),r))}))}function a(){document.querySelector(selected_relation.selected_images_selector).innerHTML="",l(),document.querySelector(selected_relation.input_selector).value=selected_relation.selected_images.map((function(e){return e.id})).join(",")}function s(){var e;(e=document.querySelector(selected_relation.iframe_selector)).src=e.src,document.querySelector(selected_relation.wrap_selector).classList.remove("show"),document.querySelector("#dropdown_overlay").classList.add("hide"),window.popupShown=!1}function u(e){window.popupShown=!0;var t=document.querySelector(e.iframe_selector);if(!t.getAttribute("src")){var r="".concat(i).concat(e.modus,"?page=1&").concat(e.types_url);t.setAttribute("src",r)}document.querySelector(e.wrap_selector).classList.add("show"),document.querySelector("#dropdown_overlay").classList.remove("hide")}window.addEventListener("load",(function(){document.querySelectorAll(".mediagallery_selector").forEach((function(e){var t=e.getAttribute("id"),r=e.parentNode.classList.contains("relation")?"integrated_content[relations][".concat(t,"]"):e.querySelector(".selected_images").getAttribute("data-fieldName");if(c[t]={modus:e.querySelector(".select_multimedia_button").dataset.multiple?"select_multiple":"select_one",selected_images:[],relationid:t,types:JSON.parse(e.querySelector(".select_multimedia_button").dataset.types),input_selector:'input[name="'.concat(r,'"]'),selected_images_selector:"#".concat(t," .selected_images"),wrap_selector:".".concat(t,".wrap"),iframe_selector:".".concat(t,".iframe")},c[t].types_url=c[t].types.reduce((function(e,t){return e+"available_contenttypes[]="+t.type+"&"}),""),!document.querySelector("iframe.iframe.".concat(t))){var n=document.createElement("div");n.className="wrap media-library iframe-wrapper close-outside ".concat(t);var o=document.createElement("iframe");o.className="iframe ".concat(t),n.appendChild(o),document.body.appendChild(n)}})),new Promise((function(e){document.querySelectorAll(".mediagallery_selector").forEach((function(e){var t=e.getAttribute("id");e.querySelectorAll(".previously_selected_images li").forEach((function(e){c[t].selected_images.push(function(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?n(Object(r),!0).forEach((function(t){o(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):n(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}({},e.dataset))}))})),e()})).then((function(){var e,t;Object.values(c).forEach((function(e){selected_relation=e,a()})),e=document.querySelectorAll(".select_multimedia_button"),t=document.querySelectorAll(".selected_images"),e.forEach((function(e){e.addEventListener("click",(function(e){var t=e.target.dataset.relationid;selected_relation=c[t],u(selected_relation)}))})),t.forEach((function(e){e.addEventListener("click",(function(e){var t=e.target.closest("li"),r=e.target.closest(".remove");if(t&&!r){var n=t.closest(".selected_images").dataset.relationid;selected_relation=c[n],u(selected_relation)}}))}))}))})),window.removeImage=function(e){e.preventDefault();var t=e.target.closest("li").id,r=e.target.closest(".mediagallery_selector").id;selected_relation=c[r],selected_relation.selected_images=selected_relation.selected_images.filter((function(e){return e.id!==t})),a()},window.addEventListener("message",(function(e){var r,n;if("cancel"!==e.data){if("string"==typeof e.data&&e.data.length>0){var o=(r=JSON.parse(e.data),n=selected_relation.types.map((function(e){return e.type})),r.filter((function(e){return n.includes(e.content_type)})));o.length>0?(!function(e){"select_one"==selected_relation.modus?selected_relation.selected_images=e:selected_relation.selected_images=[].concat(t(selected_relation.selected_images),t(e)).filter((function(e,t,r){return r.findIndex((function(t){return t.id===e.id}))===t}))}(o),a(),s()):console.log("no image selected")}}else s()}))})();
>>>>>>> release/0.90
