/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/Bundle/ContentBundle/Resources/assets/js/turbo_navigation.js"
/*!**************************************************************************!*\
  !*** ./src/Bundle/ContentBundle/Resources/assets/js/turbo_navigation.js ***!
  \**************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   reloadWithTurbo: () => (/* binding */ reloadWithTurbo),
/* harmony export */   visitWithTurbo: () => (/* binding */ visitWithTurbo)
/* harmony export */ });
function visitWithTurbo(url, options) {
  if (window.Turbo && typeof window.Turbo.visit === 'function') {
    window.Turbo.visit(url, options);
    return;
  }
  window.location.href = url;
}
function reloadWithTurbo() {
  if (window.Turbo && typeof window.Turbo.visit === 'function') {
    window.Turbo.visit(window.location.href, {
      action: 'replace'
    });
    return;
  }
  window.location.reload();
}

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Check if module exists (development only)
/******/ 		if (__webpack_modules__[moduleId] === undefined) {
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!********************************************************************!*\
  !*** ./src/Bundle/ContentBundle/Resources/assets/js/edit_panel.js ***!
  \********************************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _turbo_navigation__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./turbo_navigation */ "./src/Bundle/ContentBundle/Resources/assets/js/turbo_navigation.js");

function initEditPanel() {
  var wrapper = document.querySelector('#editimagewrapper');
  var panel = document.querySelector('#media-edit-panel');
  if (!wrapper || !panel) {
    return;
  }
  if (window.document.body.dataset.boundEditImageClick === 'true') {
    return;
  }
  window.document.addEventListener('editImageClick', handleEvent, false);
  window.document.body.dataset.boundEditImageClick = 'true';
  function handleEvent() {
    var mediaId = panel.dataset.mediaId;
    var editImagePath = wrapper.dataset.editimagepath;
    var editImageIframePath = wrapper.dataset.editimageiframepath;
    if (!mediaId || !editImagePath || !editImageIframePath) {
      return;
    }

    // If selected modus == media gallery, we should go to the page with a redirect,
    // If selected_modus is something else (select_multiple, select_one) then this is loaded via an iframe
    // And then we load the same page without sidebar and header
    var selectedModus = typeof selected_modus !== 'undefined' && selected_modus && selected_modus !== 'undefined' ? selected_modus : 'media_gallery';
    if (selectedModus == 'media_gallery') {
      (0,_turbo_navigation__WEBPACK_IMPORTED_MODULE_0__.visitWithTurbo)(editImagePath.replace('REPLACE', mediaId));
    } else {
      (0,_turbo_navigation__WEBPACK_IMPORTED_MODULE_0__.visitWithTurbo)(editImageIframePath.replace('REPLACE', mediaId));
    }
  }
}
document.addEventListener('DOMContentLoaded', initEditPanel);
document.addEventListener('turbo:load', initEditPanel);
document.addEventListener('turbo:render', initEditPanel);
})();

/******/ })()
;