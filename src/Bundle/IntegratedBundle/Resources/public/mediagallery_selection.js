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
    var isRelationField = item.parentNode.classList.contains('relation');
    var multipleValue = (item.querySelector('.select_multimedia_button').dataset.multiple || '').toLowerCase();
    var isMultiple = multipleValue === 'true' || multipleValue === '1' || multipleValue === '' && isRelationField;
    var inputSelector = isRelationField ? "input[name=\"integrated_content[relations][".concat(id, "]\"]") : "#".concat(id, " .mediagallery_selector_input");
    form_relations[id] = {
      modus: isMultiple ? 'select_multiple' : 'select_one',
      selected_images: [],
      relationid: id,
      types: JSON.parse(item.querySelector('.select_multimedia_button').dataset.types),
      input_selector: inputSelector,
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
    return String(item.type || '').toLowerCase();
  }).filter(Boolean);
  if (allowed_types.length === 0) {
    return selection;
  }
  return selection.filter(function (item) {
    var contentType = String(item.content_type || item.contentType || item.contenttype || item.type || '').toLowerCase();

    // Keep items without type metadata instead of silently dropping valid selections.
    if (!contentType) {
      return true;
    }
    return allowed_types.includes(contentType);
  });
}
function addImageIDsToInputField() {
  var input = document.querySelector(selected_relation.input_selector);
  if (!input) {
    return;
  }
  var selectedIds = selected_relation.selected_images.map(function (item) {
    return item.id;
  }).filter(Boolean);
  input.value = selected_relation.modus === 'select_one' ? selectedIds[selectedIds.length - 1] || '' : selectedIds.join(',');
}
function emptyShownImagesInDOM() {
  document.querySelector(selected_relation.selected_images_selector).innerHTML = '';
}
function selectImagesToShow(response_from_iframe) {
  if (selected_relation.modus == 'select_one') {
    var selected = response_from_iframe.filter(function (item) {
      return item && item.id;
    });
    selected_relation.selected_images = selected.length > 0 ? [selected[selected.length - 1]] : [];
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
    var parsedResponse = JSON.parse(e.data);
    var filteredResponse = filterImages(parsedResponse);
    var response_from_iframe = filteredResponse.length > 0 ? filteredResponse : parsedResponse;
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