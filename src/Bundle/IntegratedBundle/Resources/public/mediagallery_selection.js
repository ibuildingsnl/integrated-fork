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
  console.log('populateFormRelations');
  document.querySelectorAll('.mediagallery_selector').forEach(function (item) {
    var id = item.getAttribute('id');
    console.log('populateFormRelations: ' + id);
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
    var wrap = document.createElement('div');
    wrap.className = "wrap media-library iframe-wrapper close-outside ".concat(id);

    // Create iframe element
    var iframe = document.createElement('iframe');
    iframe.className = "iframe ".concat(id);
    wrap.appendChild(iframe);
    document.body.appendChild(wrap);
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
      console.log(selected_relation);
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
  var currentSrc = iframe.getAttribute('src');

  // Check if the 'src' attribute is not set or empty
  if (!currentSrc) {
    var link = "".concat(mediagallery_link).concat(selected_relation.modus, "?page=1&").concat(selected_relation.types_url);
    iframe.setAttribute('src', link);
  }
  document.querySelector(selected_relation.wrap_selector).classList.add('show');
  document.querySelector('#dropdown_overlay').classList.remove('hide');
}
/******/ })()
;