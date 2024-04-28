<<<<<<< HEAD
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/jquery/dist/jquery.js":
/*!********************************************!*\
  !*** ./node_modules/jquery/dist/jquery.js ***!
  \********************************************/
/***/ (function(module, exports) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/*!
 * jQuery JavaScript Library v3.7.1
 * https://jquery.com/
 *
 * Copyright OpenJS Foundation and other contributors
 * Released under the MIT license
 * https://jquery.org/license
 *
 * Date: 2023-08-28T13:37Z
 */
( function( global, factory ) {

	"use strict";

	if (  true && typeof module.exports === "object" ) {

		// For CommonJS and CommonJS-like environments where a proper `window`
		// is present, execute the factory and get jQuery.
		// For environments that do not have a `window` with a `document`
		// (such as Node.js), expose a factory as module.exports.
		// This accentuates the need for the creation of a real `window`.
		// e.g. var jQuery = require("jquery")(window);
		// See ticket trac-14549 for more info.
		module.exports = global.document ?
			factory( global, true ) :
			function( w ) {
				if ( !w.document ) {
					throw new Error( "jQuery requires a window with a document" );
				}
				return factory( w );
			};
	} else {
		factory( global );
	}

// Pass this if window is not defined yet
} )( typeof window !== "undefined" ? window : this, function( window, noGlobal ) {

// Edge <= 12 - 13+, Firefox <=18 - 45+, IE 10 - 11, Safari 5.1 - 9+, iOS 6 - 9.1
// throw exceptions when non-strict code (e.g., ASP.NET 4.5) accesses strict mode
// arguments.callee.caller (trac-13335). But as of jQuery 3.0 (2016), strict mode should be common
// enough that all such attempts are guarded in a try block.
"use strict";

var arr = [];

var getProto = Object.getPrototypeOf;

var slice = arr.slice;

var flat = arr.flat ? function( array ) {
	return arr.flat.call( array );
} : function( array ) {
	return arr.concat.apply( [], array );
};


var push = arr.push;

var indexOf = arr.indexOf;

var class2type = {};

var toString = class2type.toString;

var hasOwn = class2type.hasOwnProperty;

var fnToString = hasOwn.toString;

var ObjectFunctionString = fnToString.call( Object );

var support = {};

var isFunction = function isFunction( obj ) {

		// Support: Chrome <=57, Firefox <=52
		// In some browsers, typeof returns "function" for HTML <object> elements
		// (i.e., `typeof document.createElement( "object" ) === "function"`).
		// We don't want to classify *any* DOM node as a function.
		// Support: QtWeb <=3.8.5, WebKit <=534.34, wkhtmltopdf tool <=0.12.5
		// Plus for old WebKit, typeof returns "function" for HTML collections
		// (e.g., `typeof document.getElementsByTagName("div") === "function"`). (gh-4756)
		return typeof obj === "function" && typeof obj.nodeType !== "number" &&
			typeof obj.item !== "function";
	};


var isWindow = function isWindow( obj ) {
		return obj != null && obj === obj.window;
	};


var document = window.document;



	var preservedScriptAttributes = {
		type: true,
		src: true,
		nonce: true,
		noModule: true
	};

	function DOMEval( code, node, doc ) {
		doc = doc || document;

		var i, val,
			script = doc.createElement( "script" );

		script.text = code;
		if ( node ) {
			for ( i in preservedScriptAttributes ) {

				// Support: Firefox 64+, Edge 18+
				// Some browsers don't support the "nonce" property on scripts.
				// On the other hand, just using `getAttribute` is not enough as
				// the `nonce` attribute is reset to an empty string whenever it
				// becomes browsing-context connected.
				// See https://github.com/whatwg/html/issues/2369
				// See https://html.spec.whatwg.org/#nonce-attributes
				// The `node.getAttribute` check was added for the sake of
				// `jQuery.globalEval` so that it can fake a nonce-containing node
				// via an object.
				val = node[ i ] || node.getAttribute && node.getAttribute( i );
				if ( val ) {
					script.setAttribute( i, val );
				}
			}
		}
		doc.head.appendChild( script ).parentNode.removeChild( script );
	}


function toType( obj ) {
	if ( obj == null ) {
		return obj + "";
	}

	// Support: Android <=2.3 only (functionish RegExp)
	return typeof obj === "object" || typeof obj === "function" ?
		class2type[ toString.call( obj ) ] || "object" :
		typeof obj;
}
/* global Symbol */
// Defining this global in .eslintrc.json would create a danger of using the global
// unguarded in another place, it seems safer to define global only for this module



var version = "3.7.1",

	rhtmlSuffix = /HTML$/i,

	// Define a local copy of jQuery
	jQuery = function( selector, context ) {

		// The jQuery object is actually just the init constructor 'enhanced'
		// Need init if jQuery is called (just allow error to be thrown if not included)
		return new jQuery.fn.init( selector, context );
	};

jQuery.fn = jQuery.prototype = {

	// The current version of jQuery being used
	jquery: version,

	constructor: jQuery,

	// The default length of a jQuery object is 0
	length: 0,

	toArray: function() {
		return slice.call( this );
	},

	// Get the Nth element in the matched element set OR
	// Get the whole matched element set as a clean array
	get: function( num ) {

		// Return all the elements in a clean array
		if ( num == null ) {
			return slice.call( this );
		}

		// Return just the one element from the set
		return num < 0 ? this[ num + this.length ] : this[ num ];
	},

	// Take an array of elements and push it onto the stack
	// (returning the new matched element set)
	pushStack: function( elems ) {

		// Build a new jQuery matched element set
		var ret = jQuery.merge( this.constructor(), elems );

		// Add the old object onto the stack (as a reference)
		ret.prevObject = this;

		// Return the newly-formed element set
		return ret;
	},

	// Execute a callback for every element in the matched set.
	each: function( callback ) {
		return jQuery.each( this, callback );
	},

	map: function( callback ) {
		return this.pushStack( jQuery.map( this, function( elem, i ) {
			return callback.call( elem, i, elem );
		} ) );
	},

	slice: function() {
		return this.pushStack( slice.apply( this, arguments ) );
	},

	first: function() {
		return this.eq( 0 );
	},

	last: function() {
		return this.eq( -1 );
	},

	even: function() {
		return this.pushStack( jQuery.grep( this, function( _elem, i ) {
			return ( i + 1 ) % 2;
		} ) );
	},

	odd: function() {
		return this.pushStack( jQuery.grep( this, function( _elem, i ) {
			return i % 2;
		} ) );
	},

	eq: function( i ) {
		var len = this.length,
			j = +i + ( i < 0 ? len : 0 );
		return this.pushStack( j >= 0 && j < len ? [ this[ j ] ] : [] );
	},

	end: function() {
		return this.prevObject || this.constructor();
	},

	// For internal use only.
	// Behaves like an Array's method, not like a jQuery method.
	push: push,
	sort: arr.sort,
	splice: arr.splice
};

jQuery.extend = jQuery.fn.extend = function() {
	var options, name, src, copy, copyIsArray, clone,
		target = arguments[ 0 ] || {},
		i = 1,
		length = arguments.length,
		deep = false;

	// Handle a deep copy situation
	if ( typeof target === "boolean" ) {
		deep = target;

		// Skip the boolean and the target
		target = arguments[ i ] || {};
		i++;
	}

	// Handle case when target is a string or something (possible in deep copy)
	if ( typeof target !== "object" && !isFunction( target ) ) {
		target = {};
	}

	// Extend jQuery itself if only one argument is passed
	if ( i === length ) {
		target = this;
		i--;
	}

	for ( ; i < length; i++ ) {

		// Only deal with non-null/undefined values
		if ( ( options = arguments[ i ] ) != null ) {

			// Extend the base object
			for ( name in options ) {
				copy = options[ name ];

				// Prevent Object.prototype pollution
				// Prevent never-ending loop
				if ( name === "__proto__" || target === copy ) {
					continue;
				}

				// Recurse if we're merging plain objects or arrays
				if ( deep && copy && ( jQuery.isPlainObject( copy ) ||
					( copyIsArray = Array.isArray( copy ) ) ) ) {
					src = target[ name ];

					// Ensure proper type for the source value
					if ( copyIsArray && !Array.isArray( src ) ) {
						clone = [];
					} else if ( !copyIsArray && !jQuery.isPlainObject( src ) ) {
						clone = {};
					} else {
						clone = src;
					}
					copyIsArray = false;

					// Never move original objects, clone them
					target[ name ] = jQuery.extend( deep, clone, copy );

				// Don't bring in undefined values
				} else if ( copy !== undefined ) {
					target[ name ] = copy;
				}
			}
		}
	}

	// Return the modified object
	return target;
};

jQuery.extend( {

	// Unique for each copy of jQuery on the page
	expando: "jQuery" + ( version + Math.random() ).replace( /\D/g, "" ),

	// Assume jQuery is ready without the ready module
	isReady: true,

	error: function( msg ) {
		throw new Error( msg );
	},

	noop: function() {},

	isPlainObject: function( obj ) {
		var proto, Ctor;

		// Detect obvious negatives
		// Use toString instead of jQuery.type to catch host objects
		if ( !obj || toString.call( obj ) !== "[object Object]" ) {
			return false;
		}

		proto = getProto( obj );

		// Objects with no prototype (e.g., `Object.create( null )`) are plain
		if ( !proto ) {
			return true;
		}

		// Objects with prototype are plain iff they were constructed by a global Object function
		Ctor = hasOwn.call( proto, "constructor" ) && proto.constructor;
		return typeof Ctor === "function" && fnToString.call( Ctor ) === ObjectFunctionString;
	},

	isEmptyObject: function( obj ) {
		var name;

		for ( name in obj ) {
			return false;
		}
		return true;
	},

	// Evaluates a script in a provided context; falls back to the global one
	// if not specified.
	globalEval: function( code, options, doc ) {
		DOMEval( code, { nonce: options && options.nonce }, doc );
	},

	each: function( obj, callback ) {
		var length, i = 0;

		if ( isArrayLike( obj ) ) {
			length = obj.length;
			for ( ; i < length; i++ ) {
				if ( callback.call( obj[ i ], i, obj[ i ] ) === false ) {
					break;
				}
			}
		} else {
			for ( i in obj ) {
				if ( callback.call( obj[ i ], i, obj[ i ] ) === false ) {
					break;
				}
			}
		}

		return obj;
	},


	// Retrieve the text value of an array of DOM nodes
	text: function( elem ) {
		var node,
			ret = "",
			i = 0,
			nodeType = elem.nodeType;

		if ( !nodeType ) {

			// If no nodeType, this is expected to be an array
			while ( ( node = elem[ i++ ] ) ) {

				// Do not traverse comment nodes
				ret += jQuery.text( node );
			}
		}
		if ( nodeType === 1 || nodeType === 11 ) {
			return elem.textContent;
		}
		if ( nodeType === 9 ) {
			return elem.documentElement.textContent;
		}
		if ( nodeType === 3 || nodeType === 4 ) {
			return elem.nodeValue;
		}

		// Do not include comment or processing instruction nodes

		return ret;
	},

	// results is for internal usage only
	makeArray: function( arr, results ) {
		var ret = results || [];

		if ( arr != null ) {
			if ( isArrayLike( Object( arr ) ) ) {
				jQuery.merge( ret,
					typeof arr === "string" ?
						[ arr ] : arr
				);
			} else {
				push.call( ret, arr );
			}
		}

		return ret;
	},

	inArray: function( elem, arr, i ) {
		return arr == null ? -1 : indexOf.call( arr, elem, i );
	},

	isXMLDoc: function( elem ) {
		var namespace = elem && elem.namespaceURI,
			docElem = elem && ( elem.ownerDocument || elem ).documentElement;

		// Assume HTML when documentElement doesn't yet exist, such as inside
		// document fragments.
		return !rhtmlSuffix.test( namespace || docElem && docElem.nodeName || "HTML" );
	},

	// Support: Android <=4.0 only, PhantomJS 1 only
	// push.apply(_, arraylike) throws on ancient WebKit
	merge: function( first, second ) {
		var len = +second.length,
			j = 0,
			i = first.length;

		for ( ; j < len; j++ ) {
			first[ i++ ] = second[ j ];
		}

		first.length = i;

		return first;
	},

	grep: function( elems, callback, invert ) {
		var callbackInverse,
			matches = [],
			i = 0,
			length = elems.length,
			callbackExpect = !invert;

		// Go through the array, only saving the items
		// that pass the validator function
		for ( ; i < length; i++ ) {
			callbackInverse = !callback( elems[ i ], i );
			if ( callbackInverse !== callbackExpect ) {
				matches.push( elems[ i ] );
			}
		}

		return matches;
	},

	// arg is for internal usage only
	map: function( elems, callback, arg ) {
		var length, value,
			i = 0,
			ret = [];

		// Go through the array, translating each of the items to their new values
		if ( isArrayLike( elems ) ) {
			length = elems.length;
			for ( ; i < length; i++ ) {
				value = callback( elems[ i ], i, arg );

				if ( value != null ) {
					ret.push( value );
				}
			}

		// Go through every key on the object,
		} else {
			for ( i in elems ) {
				value = callback( elems[ i ], i, arg );

				if ( value != null ) {
					ret.push( value );
				}
			}
		}

		// Flatten any nested arrays
		return flat( ret );
	},

	// A global GUID counter for objects
	guid: 1,

	// jQuery.support is not used in Core but other projects attach their
	// properties to it so it needs to exist.
	support: support
} );

if ( typeof Symbol === "function" ) {
	jQuery.fn[ Symbol.iterator ] = arr[ Symbol.iterator ];
}

// Populate the class2type map
jQuery.each( "Boolean Number String Function Array Date RegExp Object Error Symbol".split( " " ),
	function( _i, name ) {
		class2type[ "[object " + name + "]" ] = name.toLowerCase();
	} );

function isArrayLike( obj ) {

	// Support: real iOS 8.2 only (not reproducible in simulator)
	// `in` check used to prevent JIT error (gh-2145)
	// hasOwn isn't used here due to false negatives
	// regarding Nodelist length in IE
	var length = !!obj && "length" in obj && obj.length,
		type = toType( obj );

	if ( isFunction( obj ) || isWindow( obj ) ) {
		return false;
	}

	return type === "array" || length === 0 ||
		typeof length === "number" && length > 0 && ( length - 1 ) in obj;
}


function nodeName( elem, name ) {

	return elem.nodeName && elem.nodeName.toLowerCase() === name.toLowerCase();

}
var pop = arr.pop;


var sort = arr.sort;


var splice = arr.splice;


var whitespace = "[\\x20\\t\\r\\n\\f]";


var rtrimCSS = new RegExp(
	"^" + whitespace + "+|((?:^|[^\\\\])(?:\\\\.)*)" + whitespace + "+$",
	"g"
);




// Note: an element does not contain itself
jQuery.contains = function( a, b ) {
	var bup = b && b.parentNode;

	return a === bup || !!( bup && bup.nodeType === 1 && (

		// Support: IE 9 - 11+
		// IE doesn't have `contains` on SVG.
		a.contains ?
			a.contains( bup ) :
			a.compareDocumentPosition && a.compareDocumentPosition( bup ) & 16
	) );
};




// CSS string/identifier serialization
// https://drafts.csswg.org/cssom/#common-serializing-idioms
var rcssescape = /([\0-\x1f\x7f]|^-?\d)|^-$|[^\x80-\uFFFF\w-]/g;

function fcssescape( ch, asCodePoint ) {
	if ( asCodePoint ) {

		// U+0000 NULL becomes U+FFFD REPLACEMENT CHARACTER
		if ( ch === "\0" ) {
			return "\uFFFD";
		}

		// Control characters and (dependent upon position) numbers get escaped as code points
		return ch.slice( 0, -1 ) + "\\" + ch.charCodeAt( ch.length - 1 ).toString( 16 ) + " ";
	}

	// Other potentially-special ASCII characters get backslash-escaped
	return "\\" + ch;
}

jQuery.escapeSelector = function( sel ) {
	return ( sel + "" ).replace( rcssescape, fcssescape );
};




var preferredDoc = document,
	pushNative = push;

( function() {

var i,
	Expr,
	outermostContext,
	sortInput,
	hasDuplicate,
	push = pushNative,

	// Local document vars
	document,
	documentElement,
	documentIsHTML,
	rbuggyQSA,
	matches,

	// Instance-specific data
	expando = jQuery.expando,
	dirruns = 0,
	done = 0,
	classCache = createCache(),
	tokenCache = createCache(),
	compilerCache = createCache(),
	nonnativeSelectorCache = createCache(),
	sortOrder = function( a, b ) {
		if ( a === b ) {
			hasDuplicate = true;
		}
		return 0;
	},

	booleans = "checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|" +
		"loop|multiple|open|readonly|required|scoped",

	// Regular expressions

	// https://www.w3.org/TR/css-syntax-3/#ident-token-diagram
	identifier = "(?:\\\\[\\da-fA-F]{1,6}" + whitespace +
		"?|\\\\[^\\r\\n\\f]|[\\w-]|[^\0-\\x7f])+",

	// Attribute selectors: https://www.w3.org/TR/selectors/#attribute-selectors
	attributes = "\\[" + whitespace + "*(" + identifier + ")(?:" + whitespace +

		// Operator (capture 2)
		"*([*^$|!~]?=)" + whitespace +

		// "Attribute values must be CSS identifiers [capture 5] or strings [capture 3 or capture 4]"
		"*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|(" + identifier + "))|)" +
		whitespace + "*\\]",

	pseudos = ":(" + identifier + ")(?:\\((" +

		// To reduce the number of selectors needing tokenize in the preFilter, prefer arguments:
		// 1. quoted (capture 3; capture 4 or capture 5)
		"('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|" +

		// 2. simple (capture 6)
		"((?:\\\\.|[^\\\\()[\\]]|" + attributes + ")*)|" +

		// 3. anything else (capture 2)
		".*" +
		")\\)|)",

	// Leading and non-escaped trailing whitespace, capturing some non-whitespace characters preceding the latter
	rwhitespace = new RegExp( whitespace + "+", "g" ),

	rcomma = new RegExp( "^" + whitespace + "*," + whitespace + "*" ),
	rleadingCombinator = new RegExp( "^" + whitespace + "*([>+~]|" + whitespace + ")" +
		whitespace + "*" ),
	rdescend = new RegExp( whitespace + "|>" ),

	rpseudo = new RegExp( pseudos ),
	ridentifier = new RegExp( "^" + identifier + "$" ),

	matchExpr = {
		ID: new RegExp( "^#(" + identifier + ")" ),
		CLASS: new RegExp( "^\\.(" + identifier + ")" ),
		TAG: new RegExp( "^(" + identifier + "|[*])" ),
		ATTR: new RegExp( "^" + attributes ),
		PSEUDO: new RegExp( "^" + pseudos ),
		CHILD: new RegExp(
			"^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(" +
				whitespace + "*(even|odd|(([+-]|)(\\d*)n|)" + whitespace + "*(?:([+-]|)" +
				whitespace + "*(\\d+)|))" + whitespace + "*\\)|)", "i" ),
		bool: new RegExp( "^(?:" + booleans + ")$", "i" ),

		// For use in libraries implementing .is()
		// We use this for POS matching in `select`
		needsContext: new RegExp( "^" + whitespace +
			"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\(" + whitespace +
			"*((?:-\\d)?\\d*)" + whitespace + "*\\)|)(?=[^-]|$)", "i" )
	},

	rinputs = /^(?:input|select|textarea|button)$/i,
	rheader = /^h\d$/i,

	// Easily-parseable/retrievable ID or TAG or CLASS selectors
	rquickExpr = /^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,

	rsibling = /[+~]/,

	// CSS escapes
	// https://www.w3.org/TR/CSS21/syndata.html#escaped-characters
	runescape = new RegExp( "\\\\[\\da-fA-F]{1,6}" + whitespace +
		"?|\\\\([^\\r\\n\\f])", "g" ),
	funescape = function( escape, nonHex ) {
		var high = "0x" + escape.slice( 1 ) - 0x10000;

		if ( nonHex ) {

			// Strip the backslash prefix from a non-hex escape sequence
			return nonHex;
		}

		// Replace a hexadecimal escape sequence with the encoded Unicode code point
		// Support: IE <=11+
		// For values outside the Basic Multilingual Plane (BMP), manually construct a
		// surrogate pair
		return high < 0 ?
			String.fromCharCode( high + 0x10000 ) :
			String.fromCharCode( high >> 10 | 0xD800, high & 0x3FF | 0xDC00 );
	},

	// Used for iframes; see `setDocument`.
	// Support: IE 9 - 11+, Edge 12 - 18+
	// Removing the function wrapper causes a "Permission Denied"
	// error in IE/Edge.
	unloadHandler = function() {
		setDocument();
	},

	inDisabledFieldset = addCombinator(
		function( elem ) {
			return elem.disabled === true && nodeName( elem, "fieldset" );
		},
		{ dir: "parentNode", next: "legend" }
	);

// Support: IE <=9 only
// Accessing document.activeElement can throw unexpectedly
// https://bugs.jquery.com/ticket/13393
function safeActiveElement() {
	try {
		return document.activeElement;
	} catch ( err ) { }
}

// Optimize for push.apply( _, NodeList )
try {
	push.apply(
		( arr = slice.call( preferredDoc.childNodes ) ),
		preferredDoc.childNodes
	);

	// Support: Android <=4.0
	// Detect silently failing push.apply
	// eslint-disable-next-line no-unused-expressions
	arr[ preferredDoc.childNodes.length ].nodeType;
} catch ( e ) {
	push = {
		apply: function( target, els ) {
			pushNative.apply( target, slice.call( els ) );
		},
		call: function( target ) {
			pushNative.apply( target, slice.call( arguments, 1 ) );
		}
	};
}

function find( selector, context, results, seed ) {
	var m, i, elem, nid, match, groups, newSelector,
		newContext = context && context.ownerDocument,

		// nodeType defaults to 9, since context defaults to document
		nodeType = context ? context.nodeType : 9;

	results = results || [];

	// Return early from calls with invalid selector or context
	if ( typeof selector !== "string" || !selector ||
		nodeType !== 1 && nodeType !== 9 && nodeType !== 11 ) {

		return results;
	}

	// Try to shortcut find operations (as opposed to filters) in HTML documents
	if ( !seed ) {
		setDocument( context );
		context = context || document;

		if ( documentIsHTML ) {

			// If the selector is sufficiently simple, try using a "get*By*" DOM method
			// (excepting DocumentFragment context, where the methods don't exist)
			if ( nodeType !== 11 && ( match = rquickExpr.exec( selector ) ) ) {

				// ID selector
				if ( ( m = match[ 1 ] ) ) {

					// Document context
					if ( nodeType === 9 ) {
						if ( ( elem = context.getElementById( m ) ) ) {

							// Support: IE 9 only
							// getElementById can match elements by name instead of ID
							if ( elem.id === m ) {
								push.call( results, elem );
								return results;
							}
						} else {
							return results;
						}

					// Element context
					} else {

						// Support: IE 9 only
						// getElementById can match elements by name instead of ID
						if ( newContext && ( elem = newContext.getElementById( m ) ) &&
							find.contains( context, elem ) &&
							elem.id === m ) {

							push.call( results, elem );
							return results;
						}
					}

				// Type selector
				} else if ( match[ 2 ] ) {
					push.apply( results, context.getElementsByTagName( selector ) );
					return results;

				// Class selector
				} else if ( ( m = match[ 3 ] ) && context.getElementsByClassName ) {
					push.apply( results, context.getElementsByClassName( m ) );
					return results;
				}
			}

			// Take advantage of querySelectorAll
			if ( !nonnativeSelectorCache[ selector + " " ] &&
				( !rbuggyQSA || !rbuggyQSA.test( selector ) ) ) {

				newSelector = selector;
				newContext = context;

				// qSA considers elements outside a scoping root when evaluating child or
				// descendant combinators, which is not what we want.
				// In such cases, we work around the behavior by prefixing every selector in the
				// list with an ID selector referencing the scope context.
				// The technique has to be used as well when a leading combinator is used
				// as such selectors are not recognized by querySelectorAll.
				// Thanks to Andrew Dupont for this technique.
				if ( nodeType === 1 &&
					( rdescend.test( selector ) || rleadingCombinator.test( selector ) ) ) {

					// Expand context for sibling selectors
					newContext = rsibling.test( selector ) && testContext( context.parentNode ) ||
						context;

					// We can use :scope instead of the ID hack if the browser
					// supports it & if we're not changing the context.
					// Support: IE 11+, Edge 17 - 18+
					// IE/Edge sometimes throw a "Permission denied" error when
					// strict-comparing two documents; shallow comparisons work.
					// eslint-disable-next-line eqeqeq
					if ( newContext != context || !support.scope ) {

						// Capture the context ID, setting it first if necessary
						if ( ( nid = context.getAttribute( "id" ) ) ) {
							nid = jQuery.escapeSelector( nid );
						} else {
							context.setAttribute( "id", ( nid = expando ) );
						}
					}

					// Prefix every selector in the list
					groups = tokenize( selector );
					i = groups.length;
					while ( i-- ) {
						groups[ i ] = ( nid ? "#" + nid : ":scope" ) + " " +
							toSelector( groups[ i ] );
					}
					newSelector = groups.join( "," );
				}

				try {
					push.apply( results,
						newContext.querySelectorAll( newSelector )
					);
					return results;
				} catch ( qsaError ) {
					nonnativeSelectorCache( selector, true );
				} finally {
					if ( nid === expando ) {
						context.removeAttribute( "id" );
					}
				}
			}
		}
	}

	// All others
	return select( selector.replace( rtrimCSS, "$1" ), context, results, seed );
}

/**
 * Create key-value caches of limited size
 * @returns {function(string, object)} Returns the Object data after storing it on itself with
 *	property name the (space-suffixed) string and (if the cache is larger than Expr.cacheLength)
 *	deleting the oldest entry
 */
function createCache() {
	var keys = [];

	function cache( key, value ) {

		// Use (key + " ") to avoid collision with native prototype properties
		// (see https://github.com/jquery/sizzle/issues/157)
		if ( keys.push( key + " " ) > Expr.cacheLength ) {

			// Only keep the most recent entries
			delete cache[ keys.shift() ];
		}
		return ( cache[ key + " " ] = value );
	}
	return cache;
}

/**
 * Mark a function for special use by jQuery selector module
 * @param {Function} fn The function to mark
 */
function markFunction( fn ) {
	fn[ expando ] = true;
	return fn;
}

/**
 * Support testing using an element
 * @param {Function} fn Passed the created element and returns a boolean result
 */
function assert( fn ) {
	var el = document.createElement( "fieldset" );

	try {
		return !!fn( el );
	} catch ( e ) {
		return false;
	} finally {

		// Remove from its parent by default
		if ( el.parentNode ) {
			el.parentNode.removeChild( el );
		}

		// release memory in IE
		el = null;
	}
}

/**
 * Returns a function to use in pseudos for input types
 * @param {String} type
 */
function createInputPseudo( type ) {
	return function( elem ) {
		return nodeName( elem, "input" ) && elem.type === type;
	};
}

/**
 * Returns a function to use in pseudos for buttons
 * @param {String} type
 */
function createButtonPseudo( type ) {
	return function( elem ) {
		return ( nodeName( elem, "input" ) || nodeName( elem, "button" ) ) &&
			elem.type === type;
	};
}

/**
 * Returns a function to use in pseudos for :enabled/:disabled
 * @param {Boolean} disabled true for :disabled; false for :enabled
 */
function createDisabledPseudo( disabled ) {

	// Known :disabled false positives: fieldset[disabled] > legend:nth-of-type(n+2) :can-disable
	return function( elem ) {

		// Only certain elements can match :enabled or :disabled
		// https://html.spec.whatwg.org/multipage/scripting.html#selector-enabled
		// https://html.spec.whatwg.org/multipage/scripting.html#selector-disabled
		if ( "form" in elem ) {

			// Check for inherited disabledness on relevant non-disabled elements:
			// * listed form-associated elements in a disabled fieldset
			//   https://html.spec.whatwg.org/multipage/forms.html#category-listed
			//   https://html.spec.whatwg.org/multipage/forms.html#concept-fe-disabled
			// * option elements in a disabled optgroup
			//   https://html.spec.whatwg.org/multipage/forms.html#concept-option-disabled
			// All such elements have a "form" property.
			if ( elem.parentNode && elem.disabled === false ) {

				// Option elements defer to a parent optgroup if present
				if ( "label" in elem ) {
					if ( "label" in elem.parentNode ) {
						return elem.parentNode.disabled === disabled;
					} else {
						return elem.disabled === disabled;
					}
				}

				// Support: IE 6 - 11+
				// Use the isDisabled shortcut property to check for disabled fieldset ancestors
				return elem.isDisabled === disabled ||

					// Where there is no isDisabled, check manually
					elem.isDisabled !== !disabled &&
						inDisabledFieldset( elem ) === disabled;
			}

			return elem.disabled === disabled;

		// Try to winnow out elements that can't be disabled before trusting the disabled property.
		// Some victims get caught in our net (label, legend, menu, track), but it shouldn't
		// even exist on them, let alone have a boolean value.
		} else if ( "label" in elem ) {
			return elem.disabled === disabled;
		}

		// Remaining elements are neither :enabled nor :disabled
		return false;
	};
}

/**
 * Returns a function to use in pseudos for positionals
 * @param {Function} fn
 */
function createPositionalPseudo( fn ) {
	return markFunction( function( argument ) {
		argument = +argument;
		return markFunction( function( seed, matches ) {
			var j,
				matchIndexes = fn( [], seed.length, argument ),
				i = matchIndexes.length;

			// Match elements found at the specified indexes
			while ( i-- ) {
				if ( seed[ ( j = matchIndexes[ i ] ) ] ) {
					seed[ j ] = !( matches[ j ] = seed[ j ] );
				}
			}
		} );
	} );
}

/**
 * Checks a node for validity as a jQuery selector context
 * @param {Element|Object=} context
 * @returns {Element|Object|Boolean} The input node if acceptable, otherwise a falsy value
 */
function testContext( context ) {
	return context && typeof context.getElementsByTagName !== "undefined" && context;
}

/**
 * Sets document-related variables once based on the current document
 * @param {Element|Object} [node] An element or document object to use to set the document
 * @returns {Object} Returns the current document
 */
function setDocument( node ) {
	var subWindow,
		doc = node ? node.ownerDocument || node : preferredDoc;

	// Return early if doc is invalid or already selected
	// Support: IE 11+, Edge 17 - 18+
	// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
	// two documents; shallow comparisons work.
	// eslint-disable-next-line eqeqeq
	if ( doc == document || doc.nodeType !== 9 || !doc.documentElement ) {
		return document;
	}

	// Update global variables
	document = doc;
	documentElement = document.documentElement;
	documentIsHTML = !jQuery.isXMLDoc( document );

	// Support: iOS 7 only, IE 9 - 11+
	// Older browsers didn't support unprefixed `matches`.
	matches = documentElement.matches ||
		documentElement.webkitMatchesSelector ||
		documentElement.msMatchesSelector;

	// Support: IE 9 - 11+, Edge 12 - 18+
	// Accessing iframe documents after unload throws "permission denied" errors
	// (see trac-13936).
	// Limit the fix to IE & Edge Legacy; despite Edge 15+ implementing `matches`,
	// all IE 9+ and Edge Legacy versions implement `msMatchesSelector` as well.
	if ( documentElement.msMatchesSelector &&

		// Support: IE 11+, Edge 17 - 18+
		// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
		// two documents; shallow comparisons work.
		// eslint-disable-next-line eqeqeq
		preferredDoc != document &&
		( subWindow = document.defaultView ) && subWindow.top !== subWindow ) {

		// Support: IE 9 - 11+, Edge 12 - 18+
		subWindow.addEventListener( "unload", unloadHandler );
	}

	// Support: IE <10
	// Check if getElementById returns elements by name
	// The broken getElementById methods don't pick up programmatically-set names,
	// so use a roundabout getElementsByName test
	support.getById = assert( function( el ) {
		documentElement.appendChild( el ).id = jQuery.expando;
		return !document.getElementsByName ||
			!document.getElementsByName( jQuery.expando ).length;
	} );

	// Support: IE 9 only
	// Check to see if it's possible to do matchesSelector
	// on a disconnected node.
	support.disconnectedMatch = assert( function( el ) {
		return matches.call( el, "*" );
	} );

	// Support: IE 9 - 11+, Edge 12 - 18+
	// IE/Edge don't support the :scope pseudo-class.
	support.scope = assert( function() {
		return document.querySelectorAll( ":scope" );
	} );

	// Support: Chrome 105 - 111 only, Safari 15.4 - 16.3 only
	// Make sure the `:has()` argument is parsed unforgivingly.
	// We include `*` in the test to detect buggy implementations that are
	// _selectively_ forgiving (specifically when the list includes at least
	// one valid selector).
	// Note that we treat complete lack of support for `:has()` as if it were
	// spec-compliant support, which is fine because use of `:has()` in such
	// environments will fail in the qSA path and fall back to jQuery traversal
	// anyway.
	support.cssHas = assert( function() {
		try {
			document.querySelector( ":has(*,:jqfake)" );
			return false;
		} catch ( e ) {
			return true;
		}
	} );

	// ID filter and find
	if ( support.getById ) {
		Expr.filter.ID = function( id ) {
			var attrId = id.replace( runescape, funescape );
			return function( elem ) {
				return elem.getAttribute( "id" ) === attrId;
			};
		};
		Expr.find.ID = function( id, context ) {
			if ( typeof context.getElementById !== "undefined" && documentIsHTML ) {
				var elem = context.getElementById( id );
				return elem ? [ elem ] : [];
			}
		};
	} else {
		Expr.filter.ID =  function( id ) {
			var attrId = id.replace( runescape, funescape );
			return function( elem ) {
				var node = typeof elem.getAttributeNode !== "undefined" &&
					elem.getAttributeNode( "id" );
				return node && node.value === attrId;
			};
		};

		// Support: IE 6 - 7 only
		// getElementById is not reliable as a find shortcut
		Expr.find.ID = function( id, context ) {
			if ( typeof context.getElementById !== "undefined" && documentIsHTML ) {
				var node, i, elems,
					elem = context.getElementById( id );

				if ( elem ) {

					// Verify the id attribute
					node = elem.getAttributeNode( "id" );
					if ( node && node.value === id ) {
						return [ elem ];
					}

					// Fall back on getElementsByName
					elems = context.getElementsByName( id );
					i = 0;
					while ( ( elem = elems[ i++ ] ) ) {
						node = elem.getAttributeNode( "id" );
						if ( node && node.value === id ) {
							return [ elem ];
						}
					}
				}

				return [];
			}
		};
	}

	// Tag
	Expr.find.TAG = function( tag, context ) {
		if ( typeof context.getElementsByTagName !== "undefined" ) {
			return context.getElementsByTagName( tag );

		// DocumentFragment nodes don't have gEBTN
		} else {
			return context.querySelectorAll( tag );
		}
	};

	// Class
	Expr.find.CLASS = function( className, context ) {
		if ( typeof context.getElementsByClassName !== "undefined" && documentIsHTML ) {
			return context.getElementsByClassName( className );
		}
	};

	/* QSA/matchesSelector
	---------------------------------------------------------------------- */

	// QSA and matchesSelector support

	rbuggyQSA = [];

	// Build QSA regex
	// Regex strategy adopted from Diego Perini
	assert( function( el ) {

		var input;

		documentElement.appendChild( el ).innerHTML =
			"<a id='" + expando + "' href='' disabled='disabled'></a>" +
			"<select id='" + expando + "-\r\\' disabled='disabled'>" +
			"<option selected=''></option></select>";

		// Support: iOS <=7 - 8 only
		// Boolean attributes and "value" are not treated correctly in some XML documents
		if ( !el.querySelectorAll( "[selected]" ).length ) {
			rbuggyQSA.push( "\\[" + whitespace + "*(?:value|" + booleans + ")" );
		}

		// Support: iOS <=7 - 8 only
		if ( !el.querySelectorAll( "[id~=" + expando + "-]" ).length ) {
			rbuggyQSA.push( "~=" );
		}

		// Support: iOS 8 only
		// https://bugs.webkit.org/show_bug.cgi?id=136851
		// In-page `selector#id sibling-combinator selector` fails
		if ( !el.querySelectorAll( "a#" + expando + "+*" ).length ) {
			rbuggyQSA.push( ".#.+[+~]" );
		}

		// Support: Chrome <=105+, Firefox <=104+, Safari <=15.4+
		// In some of the document kinds, these selectors wouldn't work natively.
		// This is probably OK but for backwards compatibility we want to maintain
		// handling them through jQuery traversal in jQuery 3.x.
		if ( !el.querySelectorAll( ":checked" ).length ) {
			rbuggyQSA.push( ":checked" );
		}

		// Support: Windows 8 Native Apps
		// The type and name attributes are restricted during .innerHTML assignment
		input = document.createElement( "input" );
		input.setAttribute( "type", "hidden" );
		el.appendChild( input ).setAttribute( "name", "D" );

		// Support: IE 9 - 11+
		// IE's :disabled selector does not pick up the children of disabled fieldsets
		// Support: Chrome <=105+, Firefox <=104+, Safari <=15.4+
		// In some of the document kinds, these selectors wouldn't work natively.
		// This is probably OK but for backwards compatibility we want to maintain
		// handling them through jQuery traversal in jQuery 3.x.
		documentElement.appendChild( el ).disabled = true;
		if ( el.querySelectorAll( ":disabled" ).length !== 2 ) {
			rbuggyQSA.push( ":enabled", ":disabled" );
		}

		// Support: IE 11+, Edge 15 - 18+
		// IE 11/Edge don't find elements on a `[name='']` query in some cases.
		// Adding a temporary attribute to the document before the selection works
		// around the issue.
		// Interestingly, IE 10 & older don't seem to have the issue.
		input = document.createElement( "input" );
		input.setAttribute( "name", "" );
		el.appendChild( input );
		if ( !el.querySelectorAll( "[name='']" ).length ) {
			rbuggyQSA.push( "\\[" + whitespace + "*name" + whitespace + "*=" +
				whitespace + "*(?:''|\"\")" );
		}
	} );

	if ( !support.cssHas ) {

		// Support: Chrome 105 - 110+, Safari 15.4 - 16.3+
		// Our regular `try-catch` mechanism fails to detect natively-unsupported
		// pseudo-classes inside `:has()` (such as `:has(:contains("Foo"))`)
		// in browsers that parse the `:has()` argument as a forgiving selector list.
		// https://drafts.csswg.org/selectors/#relational now requires the argument
		// to be parsed unforgivingly, but browsers have not yet fully adjusted.
		rbuggyQSA.push( ":has" );
	}

	rbuggyQSA = rbuggyQSA.length && new RegExp( rbuggyQSA.join( "|" ) );

	/* Sorting
	---------------------------------------------------------------------- */

	// Document order sorting
	sortOrder = function( a, b ) {

		// Flag for duplicate removal
		if ( a === b ) {
			hasDuplicate = true;
			return 0;
		}

		// Sort on method existence if only one input has compareDocumentPosition
		var compare = !a.compareDocumentPosition - !b.compareDocumentPosition;
		if ( compare ) {
			return compare;
		}

		// Calculate position if both inputs belong to the same document
		// Support: IE 11+, Edge 17 - 18+
		// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
		// two documents; shallow comparisons work.
		// eslint-disable-next-line eqeqeq
		compare = ( a.ownerDocument || a ) == ( b.ownerDocument || b ) ?
			a.compareDocumentPosition( b ) :

			// Otherwise we know they are disconnected
			1;

		// Disconnected nodes
		if ( compare & 1 ||
			( !support.sortDetached && b.compareDocumentPosition( a ) === compare ) ) {

			// Choose the first element that is related to our preferred document
			// Support: IE 11+, Edge 17 - 18+
			// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
			// two documents; shallow comparisons work.
			// eslint-disable-next-line eqeqeq
			if ( a === document || a.ownerDocument == preferredDoc &&
				find.contains( preferredDoc, a ) ) {
				return -1;
			}

			// Support: IE 11+, Edge 17 - 18+
			// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
			// two documents; shallow comparisons work.
			// eslint-disable-next-line eqeqeq
			if ( b === document || b.ownerDocument == preferredDoc &&
				find.contains( preferredDoc, b ) ) {
				return 1;
			}

			// Maintain original order
			return sortInput ?
				( indexOf.call( sortInput, a ) - indexOf.call( sortInput, b ) ) :
				0;
		}

		return compare & 4 ? -1 : 1;
	};

	return document;
}

find.matches = function( expr, elements ) {
	return find( expr, null, null, elements );
};

find.matchesSelector = function( elem, expr ) {
	setDocument( elem );

	if ( documentIsHTML &&
		!nonnativeSelectorCache[ expr + " " ] &&
		( !rbuggyQSA || !rbuggyQSA.test( expr ) ) ) {

		try {
			var ret = matches.call( elem, expr );

			// IE 9's matchesSelector returns false on disconnected nodes
			if ( ret || support.disconnectedMatch ||

					// As well, disconnected nodes are said to be in a document
					// fragment in IE 9
					elem.document && elem.document.nodeType !== 11 ) {
				return ret;
			}
		} catch ( e ) {
			nonnativeSelectorCache( expr, true );
		}
	}

	return find( expr, document, null, [ elem ] ).length > 0;
};

find.contains = function( context, elem ) {

	// Set document vars if needed
	// Support: IE 11+, Edge 17 - 18+
	// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
	// two documents; shallow comparisons work.
	// eslint-disable-next-line eqeqeq
	if ( ( context.ownerDocument || context ) != document ) {
		setDocument( context );
	}
	return jQuery.contains( context, elem );
};


find.attr = function( elem, name ) {

	// Set document vars if needed
	// Support: IE 11+, Edge 17 - 18+
	// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
	// two documents; shallow comparisons work.
	// eslint-disable-next-line eqeqeq
	if ( ( elem.ownerDocument || elem ) != document ) {
		setDocument( elem );
	}

	var fn = Expr.attrHandle[ name.toLowerCase() ],

		// Don't get fooled by Object.prototype properties (see trac-13807)
		val = fn && hasOwn.call( Expr.attrHandle, name.toLowerCase() ) ?
			fn( elem, name, !documentIsHTML ) :
			undefined;

	if ( val !== undefined ) {
		return val;
	}

	return elem.getAttribute( name );
};

find.error = function( msg ) {
	throw new Error( "Syntax error, unrecognized expression: " + msg );
};

/**
 * Document sorting and removing duplicates
 * @param {ArrayLike} results
 */
jQuery.uniqueSort = function( results ) {
	var elem,
		duplicates = [],
		j = 0,
		i = 0;

	// Unless we *know* we can detect duplicates, assume their presence
	//
	// Support: Android <=4.0+
	// Testing for detecting duplicates is unpredictable so instead assume we can't
	// depend on duplicate detection in all browsers without a stable sort.
	hasDuplicate = !support.sortStable;
	sortInput = !support.sortStable && slice.call( results, 0 );
	sort.call( results, sortOrder );

	if ( hasDuplicate ) {
		while ( ( elem = results[ i++ ] ) ) {
			if ( elem === results[ i ] ) {
				j = duplicates.push( i );
			}
		}
		while ( j-- ) {
			splice.call( results, duplicates[ j ], 1 );
		}
	}

	// Clear input after sorting to release objects
	// See https://github.com/jquery/sizzle/pull/225
	sortInput = null;

	return results;
};

jQuery.fn.uniqueSort = function() {
	return this.pushStack( jQuery.uniqueSort( slice.apply( this ) ) );
};

Expr = jQuery.expr = {

	// Can be adjusted by the user
	cacheLength: 50,

	createPseudo: markFunction,

	match: matchExpr,

	attrHandle: {},

	find: {},

	relative: {
		">": { dir: "parentNode", first: true },
		" ": { dir: "parentNode" },
		"+": { dir: "previousSibling", first: true },
		"~": { dir: "previousSibling" }
	},

	preFilter: {
		ATTR: function( match ) {
			match[ 1 ] = match[ 1 ].replace( runescape, funescape );

			// Move the given value to match[3] whether quoted or unquoted
			match[ 3 ] = ( match[ 3 ] || match[ 4 ] || match[ 5 ] || "" )
				.replace( runescape, funescape );

			if ( match[ 2 ] === "~=" ) {
				match[ 3 ] = " " + match[ 3 ] + " ";
			}

			return match.slice( 0, 4 );
		},

		CHILD: function( match ) {

			/* matches from matchExpr["CHILD"]
				1 type (only|nth|...)
				2 what (child|of-type)
				3 argument (even|odd|\d*|\d*n([+-]\d+)?|...)
				4 xn-component of xn+y argument ([+-]?\d*n|)
				5 sign of xn-component
				6 x of xn-component
				7 sign of y-component
				8 y of y-component
			*/
			match[ 1 ] = match[ 1 ].toLowerCase();

			if ( match[ 1 ].slice( 0, 3 ) === "nth" ) {

				// nth-* requires argument
				if ( !match[ 3 ] ) {
					find.error( match[ 0 ] );
				}

				// numeric x and y parameters for Expr.filter.CHILD
				// remember that false/true cast respectively to 0/1
				match[ 4 ] = +( match[ 4 ] ?
					match[ 5 ] + ( match[ 6 ] || 1 ) :
					2 * ( match[ 3 ] === "even" || match[ 3 ] === "odd" )
				);
				match[ 5 ] = +( ( match[ 7 ] + match[ 8 ] ) || match[ 3 ] === "odd" );

			// other types prohibit arguments
			} else if ( match[ 3 ] ) {
				find.error( match[ 0 ] );
			}

			return match;
		},

		PSEUDO: function( match ) {
			var excess,
				unquoted = !match[ 6 ] && match[ 2 ];

			if ( matchExpr.CHILD.test( match[ 0 ] ) ) {
				return null;
			}

			// Accept quoted arguments as-is
			if ( match[ 3 ] ) {
				match[ 2 ] = match[ 4 ] || match[ 5 ] || "";

			// Strip excess characters from unquoted arguments
			} else if ( unquoted && rpseudo.test( unquoted ) &&

				// Get excess from tokenize (recursively)
				( excess = tokenize( unquoted, true ) ) &&

				// advance to the next closing parenthesis
				( excess = unquoted.indexOf( ")", unquoted.length - excess ) - unquoted.length ) ) {

				// excess is a negative index
				match[ 0 ] = match[ 0 ].slice( 0, excess );
				match[ 2 ] = unquoted.slice( 0, excess );
			}

			// Return only captures needed by the pseudo filter method (type and argument)
			return match.slice( 0, 3 );
		}
	},

	filter: {

		TAG: function( nodeNameSelector ) {
			var expectedNodeName = nodeNameSelector.replace( runescape, funescape ).toLowerCase();
			return nodeNameSelector === "*" ?
				function() {
					return true;
				} :
				function( elem ) {
					return nodeName( elem, expectedNodeName );
				};
		},

		CLASS: function( className ) {
			var pattern = classCache[ className + " " ];

			return pattern ||
				( pattern = new RegExp( "(^|" + whitespace + ")" + className +
					"(" + whitespace + "|$)" ) ) &&
				classCache( className, function( elem ) {
					return pattern.test(
						typeof elem.className === "string" && elem.className ||
							typeof elem.getAttribute !== "undefined" &&
								elem.getAttribute( "class" ) ||
							""
					);
				} );
		},

		ATTR: function( name, operator, check ) {
			return function( elem ) {
				var result = find.attr( elem, name );

				if ( result == null ) {
					return operator === "!=";
				}
				if ( !operator ) {
					return true;
				}

				result += "";

				if ( operator === "=" ) {
					return result === check;
				}
				if ( operator === "!=" ) {
					return result !== check;
				}
				if ( operator === "^=" ) {
					return check && result.indexOf( check ) === 0;
				}
				if ( operator === "*=" ) {
					return check && result.indexOf( check ) > -1;
				}
				if ( operator === "$=" ) {
					return check && result.slice( -check.length ) === check;
				}
				if ( operator === "~=" ) {
					return ( " " + result.replace( rwhitespace, " " ) + " " )
						.indexOf( check ) > -1;
				}
				if ( operator === "|=" ) {
					return result === check || result.slice( 0, check.length + 1 ) === check + "-";
				}

				return false;
			};
		},

		CHILD: function( type, what, _argument, first, last ) {
			var simple = type.slice( 0, 3 ) !== "nth",
				forward = type.slice( -4 ) !== "last",
				ofType = what === "of-type";

			return first === 1 && last === 0 ?

				// Shortcut for :nth-*(n)
				function( elem ) {
					return !!elem.parentNode;
				} :

				function( elem, _context, xml ) {
					var cache, outerCache, node, nodeIndex, start,
						dir = simple !== forward ? "nextSibling" : "previousSibling",
						parent = elem.parentNode,
						name = ofType && elem.nodeName.toLowerCase(),
						useCache = !xml && !ofType,
						diff = false;

					if ( parent ) {

						// :(first|last|only)-(child|of-type)
						if ( simple ) {
							while ( dir ) {
								node = elem;
								while ( ( node = node[ dir ] ) ) {
									if ( ofType ?
										nodeName( node, name ) :
										node.nodeType === 1 ) {

										return false;
									}
								}

								// Reverse direction for :only-* (if we haven't yet done so)
								start = dir = type === "only" && !start && "nextSibling";
							}
							return true;
						}

						start = [ forward ? parent.firstChild : parent.lastChild ];

						// non-xml :nth-child(...) stores cache data on `parent`
						if ( forward && useCache ) {

							// Seek `elem` from a previously-cached index
							outerCache = parent[ expando ] || ( parent[ expando ] = {} );
							cache = outerCache[ type ] || [];
							nodeIndex = cache[ 0 ] === dirruns && cache[ 1 ];
							diff = nodeIndex && cache[ 2 ];
							node = nodeIndex && parent.childNodes[ nodeIndex ];

							while ( ( node = ++nodeIndex && node && node[ dir ] ||

								// Fallback to seeking `elem` from the start
								( diff = nodeIndex = 0 ) || start.pop() ) ) {

								// When found, cache indexes on `parent` and break
								if ( node.nodeType === 1 && ++diff && node === elem ) {
									outerCache[ type ] = [ dirruns, nodeIndex, diff ];
									break;
								}
							}

						} else {

							// Use previously-cached element index if available
							if ( useCache ) {
								outerCache = elem[ expando ] || ( elem[ expando ] = {} );
								cache = outerCache[ type ] || [];
								nodeIndex = cache[ 0 ] === dirruns && cache[ 1 ];
								diff = nodeIndex;
							}

							// xml :nth-child(...)
							// or :nth-last-child(...) or :nth(-last)?-of-type(...)
							if ( diff === false ) {

								// Use the same loop as above to seek `elem` from the start
								while ( ( node = ++nodeIndex && node && node[ dir ] ||
									( diff = nodeIndex = 0 ) || start.pop() ) ) {

									if ( ( ofType ?
										nodeName( node, name ) :
										node.nodeType === 1 ) &&
										++diff ) {

										// Cache the index of each encountered element
										if ( useCache ) {
											outerCache = node[ expando ] ||
												( node[ expando ] = {} );
											outerCache[ type ] = [ dirruns, diff ];
										}

										if ( node === elem ) {
											break;
										}
									}
								}
							}
						}

						// Incorporate the offset, then check against cycle size
						diff -= last;
						return diff === first || ( diff % first === 0 && diff / first >= 0 );
					}
				};
		},

		PSEUDO: function( pseudo, argument ) {

			// pseudo-class names are case-insensitive
			// https://www.w3.org/TR/selectors/#pseudo-classes
			// Prioritize by case sensitivity in case custom pseudos are added with uppercase letters
			// Remember that setFilters inherits from pseudos
			var args,
				fn = Expr.pseudos[ pseudo ] || Expr.setFilters[ pseudo.toLowerCase() ] ||
					find.error( "unsupported pseudo: " + pseudo );

			// The user may use createPseudo to indicate that
			// arguments are needed to create the filter function
			// just as jQuery does
			if ( fn[ expando ] ) {
				return fn( argument );
			}

			// But maintain support for old signatures
			if ( fn.length > 1 ) {
				args = [ pseudo, pseudo, "", argument ];
				return Expr.setFilters.hasOwnProperty( pseudo.toLowerCase() ) ?
					markFunction( function( seed, matches ) {
						var idx,
							matched = fn( seed, argument ),
							i = matched.length;
						while ( i-- ) {
							idx = indexOf.call( seed, matched[ i ] );
							seed[ idx ] = !( matches[ idx ] = matched[ i ] );
						}
					} ) :
					function( elem ) {
						return fn( elem, 0, args );
					};
			}

			return fn;
		}
	},

	pseudos: {

		// Potentially complex pseudos
		not: markFunction( function( selector ) {

			// Trim the selector passed to compile
			// to avoid treating leading and trailing
			// spaces as combinators
			var input = [],
				results = [],
				matcher = compile( selector.replace( rtrimCSS, "$1" ) );

			return matcher[ expando ] ?
				markFunction( function( seed, matches, _context, xml ) {
					var elem,
						unmatched = matcher( seed, null, xml, [] ),
						i = seed.length;

					// Match elements unmatched by `matcher`
					while ( i-- ) {
						if ( ( elem = unmatched[ i ] ) ) {
							seed[ i ] = !( matches[ i ] = elem );
						}
					}
				} ) :
				function( elem, _context, xml ) {
					input[ 0 ] = elem;
					matcher( input, null, xml, results );

					// Don't keep the element
					// (see https://github.com/jquery/sizzle/issues/299)
					input[ 0 ] = null;
					return !results.pop();
				};
		} ),

		has: markFunction( function( selector ) {
			return function( elem ) {
				return find( selector, elem ).length > 0;
			};
		} ),

		contains: markFunction( function( text ) {
			text = text.replace( runescape, funescape );
			return function( elem ) {
				return ( elem.textContent || jQuery.text( elem ) ).indexOf( text ) > -1;
			};
		} ),

		// "Whether an element is represented by a :lang() selector
		// is based solely on the element's language value
		// being equal to the identifier C,
		// or beginning with the identifier C immediately followed by "-".
		// The matching of C against the element's language value is performed case-insensitively.
		// The identifier C does not have to be a valid language name."
		// https://www.w3.org/TR/selectors/#lang-pseudo
		lang: markFunction( function( lang ) {

			// lang value must be a valid identifier
			if ( !ridentifier.test( lang || "" ) ) {
				find.error( "unsupported lang: " + lang );
			}
			lang = lang.replace( runescape, funescape ).toLowerCase();
			return function( elem ) {
				var elemLang;
				do {
					if ( ( elemLang = documentIsHTML ?
						elem.lang :
						elem.getAttribute( "xml:lang" ) || elem.getAttribute( "lang" ) ) ) {

						elemLang = elemLang.toLowerCase();
						return elemLang === lang || elemLang.indexOf( lang + "-" ) === 0;
					}
				} while ( ( elem = elem.parentNode ) && elem.nodeType === 1 );
				return false;
			};
		} ),

		// Miscellaneous
		target: function( elem ) {
			var hash = window.location && window.location.hash;
			return hash && hash.slice( 1 ) === elem.id;
		},

		root: function( elem ) {
			return elem === documentElement;
		},

		focus: function( elem ) {
			return elem === safeActiveElement() &&
				document.hasFocus() &&
				!!( elem.type || elem.href || ~elem.tabIndex );
		},

		// Boolean properties
		enabled: createDisabledPseudo( false ),
		disabled: createDisabledPseudo( true ),

		checked: function( elem ) {

			// In CSS3, :checked should return both checked and selected elements
			// https://www.w3.org/TR/2011/REC-css3-selectors-20110929/#checked
			return ( nodeName( elem, "input" ) && !!elem.checked ) ||
				( nodeName( elem, "option" ) && !!elem.selected );
		},

		selected: function( elem ) {

			// Support: IE <=11+
			// Accessing the selectedIndex property
			// forces the browser to treat the default option as
			// selected when in an optgroup.
			if ( elem.parentNode ) {
				// eslint-disable-next-line no-unused-expressions
				elem.parentNode.selectedIndex;
			}

			return elem.selected === true;
		},

		// Contents
		empty: function( elem ) {

			// https://www.w3.org/TR/selectors/#empty-pseudo
			// :empty is negated by element (1) or content nodes (text: 3; cdata: 4; entity ref: 5),
			//   but not by others (comment: 8; processing instruction: 7; etc.)
			// nodeType < 6 works because attributes (2) do not appear as children
			for ( elem = elem.firstChild; elem; elem = elem.nextSibling ) {
				if ( elem.nodeType < 6 ) {
					return false;
				}
			}
			return true;
		},

		parent: function( elem ) {
			return !Expr.pseudos.empty( elem );
		},

		// Element/input types
		header: function( elem ) {
			return rheader.test( elem.nodeName );
		},

		input: function( elem ) {
			return rinputs.test( elem.nodeName );
		},

		button: function( elem ) {
			return nodeName( elem, "input" ) && elem.type === "button" ||
				nodeName( elem, "button" );
		},

		text: function( elem ) {
			var attr;
			return nodeName( elem, "input" ) && elem.type === "text" &&

				// Support: IE <10 only
				// New HTML5 attribute values (e.g., "search") appear
				// with elem.type === "text"
				( ( attr = elem.getAttribute( "type" ) ) == null ||
					attr.toLowerCase() === "text" );
		},

		// Position-in-collection
		first: createPositionalPseudo( function() {
			return [ 0 ];
		} ),

		last: createPositionalPseudo( function( _matchIndexes, length ) {
			return [ length - 1 ];
		} ),

		eq: createPositionalPseudo( function( _matchIndexes, length, argument ) {
			return [ argument < 0 ? argument + length : argument ];
		} ),

		even: createPositionalPseudo( function( matchIndexes, length ) {
			var i = 0;
			for ( ; i < length; i += 2 ) {
				matchIndexes.push( i );
			}
			return matchIndexes;
		} ),

		odd: createPositionalPseudo( function( matchIndexes, length ) {
			var i = 1;
			for ( ; i < length; i += 2 ) {
				matchIndexes.push( i );
			}
			return matchIndexes;
		} ),

		lt: createPositionalPseudo( function( matchIndexes, length, argument ) {
			var i;

			if ( argument < 0 ) {
				i = argument + length;
			} else if ( argument > length ) {
				i = length;
			} else {
				i = argument;
			}

			for ( ; --i >= 0; ) {
				matchIndexes.push( i );
			}
			return matchIndexes;
		} ),

		gt: createPositionalPseudo( function( matchIndexes, length, argument ) {
			var i = argument < 0 ? argument + length : argument;
			for ( ; ++i < length; ) {
				matchIndexes.push( i );
			}
			return matchIndexes;
		} )
	}
};

Expr.pseudos.nth = Expr.pseudos.eq;

// Add button/input type pseudos
for ( i in { radio: true, checkbox: true, file: true, password: true, image: true } ) {
	Expr.pseudos[ i ] = createInputPseudo( i );
}
for ( i in { submit: true, reset: true } ) {
	Expr.pseudos[ i ] = createButtonPseudo( i );
}

// Easy API for creating new setFilters
function setFilters() {}
setFilters.prototype = Expr.filters = Expr.pseudos;
Expr.setFilters = new setFilters();

function tokenize( selector, parseOnly ) {
	var matched, match, tokens, type,
		soFar, groups, preFilters,
		cached = tokenCache[ selector + " " ];

	if ( cached ) {
		return parseOnly ? 0 : cached.slice( 0 );
	}

	soFar = selector;
	groups = [];
	preFilters = Expr.preFilter;

	while ( soFar ) {

		// Comma and first run
		if ( !matched || ( match = rcomma.exec( soFar ) ) ) {
			if ( match ) {

				// Don't consume trailing commas as valid
				soFar = soFar.slice( match[ 0 ].length ) || soFar;
			}
			groups.push( ( tokens = [] ) );
		}

		matched = false;

		// Combinators
		if ( ( match = rleadingCombinator.exec( soFar ) ) ) {
			matched = match.shift();
			tokens.push( {
				value: matched,

				// Cast descendant combinators to space
				type: match[ 0 ].replace( rtrimCSS, " " )
			} );
			soFar = soFar.slice( matched.length );
		}

		// Filters
		for ( type in Expr.filter ) {
			if ( ( match = matchExpr[ type ].exec( soFar ) ) && ( !preFilters[ type ] ||
				( match = preFilters[ type ]( match ) ) ) ) {
				matched = match.shift();
				tokens.push( {
					value: matched,
					type: type,
					matches: match
				} );
				soFar = soFar.slice( matched.length );
			}
		}

		if ( !matched ) {
			break;
		}
	}

	// Return the length of the invalid excess
	// if we're just parsing
	// Otherwise, throw an error or return tokens
	if ( parseOnly ) {
		return soFar.length;
	}

	return soFar ?
		find.error( selector ) :

		// Cache the tokens
		tokenCache( selector, groups ).slice( 0 );
}

function toSelector( tokens ) {
	var i = 0,
		len = tokens.length,
		selector = "";
	for ( ; i < len; i++ ) {
		selector += tokens[ i ].value;
	}
	return selector;
}

function addCombinator( matcher, combinator, base ) {
	var dir = combinator.dir,
		skip = combinator.next,
		key = skip || dir,
		checkNonElements = base && key === "parentNode",
		doneName = done++;

	return combinator.first ?

		// Check against closest ancestor/preceding element
		function( elem, context, xml ) {
			while ( ( elem = elem[ dir ] ) ) {
				if ( elem.nodeType === 1 || checkNonElements ) {
					return matcher( elem, context, xml );
				}
			}
			return false;
		} :

		// Check against all ancestor/preceding elements
		function( elem, context, xml ) {
			var oldCache, outerCache,
				newCache = [ dirruns, doneName ];

			// We can't set arbitrary data on XML nodes, so they don't benefit from combinator caching
			if ( xml ) {
				while ( ( elem = elem[ dir ] ) ) {
					if ( elem.nodeType === 1 || checkNonElements ) {
						if ( matcher( elem, context, xml ) ) {
							return true;
						}
					}
				}
			} else {
				while ( ( elem = elem[ dir ] ) ) {
					if ( elem.nodeType === 1 || checkNonElements ) {
						outerCache = elem[ expando ] || ( elem[ expando ] = {} );

						if ( skip && nodeName( elem, skip ) ) {
							elem = elem[ dir ] || elem;
						} else if ( ( oldCache = outerCache[ key ] ) &&
							oldCache[ 0 ] === dirruns && oldCache[ 1 ] === doneName ) {

							// Assign to newCache so results back-propagate to previous elements
							return ( newCache[ 2 ] = oldCache[ 2 ] );
						} else {

							// Reuse newcache so results back-propagate to previous elements
							outerCache[ key ] = newCache;

							// A match means we're done; a fail means we have to keep checking
							if ( ( newCache[ 2 ] = matcher( elem, context, xml ) ) ) {
								return true;
							}
						}
					}
				}
			}
			return false;
		};
}

function elementMatcher( matchers ) {
	return matchers.length > 1 ?
		function( elem, context, xml ) {
			var i = matchers.length;
			while ( i-- ) {
				if ( !matchers[ i ]( elem, context, xml ) ) {
					return false;
				}
			}
			return true;
		} :
		matchers[ 0 ];
}

function multipleContexts( selector, contexts, results ) {
	var i = 0,
		len = contexts.length;
	for ( ; i < len; i++ ) {
		find( selector, contexts[ i ], results );
	}
	return results;
}

function condense( unmatched, map, filter, context, xml ) {
	var elem,
		newUnmatched = [],
		i = 0,
		len = unmatched.length,
		mapped = map != null;

	for ( ; i < len; i++ ) {
		if ( ( elem = unmatched[ i ] ) ) {
			if ( !filter || filter( elem, context, xml ) ) {
				newUnmatched.push( elem );
				if ( mapped ) {
					map.push( i );
				}
			}
		}
	}

	return newUnmatched;
}

function setMatcher( preFilter, selector, matcher, postFilter, postFinder, postSelector ) {
	if ( postFilter && !postFilter[ expando ] ) {
		postFilter = setMatcher( postFilter );
	}
	if ( postFinder && !postFinder[ expando ] ) {
		postFinder = setMatcher( postFinder, postSelector );
	}
	return markFunction( function( seed, results, context, xml ) {
		var temp, i, elem, matcherOut,
			preMap = [],
			postMap = [],
			preexisting = results.length,

			// Get initial elements from seed or context
			elems = seed ||
				multipleContexts( selector || "*",
					context.nodeType ? [ context ] : context, [] ),

			// Prefilter to get matcher input, preserving a map for seed-results synchronization
			matcherIn = preFilter && ( seed || !selector ) ?
				condense( elems, preMap, preFilter, context, xml ) :
				elems;

		if ( matcher ) {

			// If we have a postFinder, or filtered seed, or non-seed postFilter
			// or preexisting results,
			matcherOut = postFinder || ( seed ? preFilter : preexisting || postFilter ) ?

				// ...intermediate processing is necessary
				[] :

				// ...otherwise use results directly
				results;

			// Find primary matches
			matcher( matcherIn, matcherOut, context, xml );
		} else {
			matcherOut = matcherIn;
		}

		// Apply postFilter
		if ( postFilter ) {
			temp = condense( matcherOut, postMap );
			postFilter( temp, [], context, xml );

			// Un-match failing elements by moving them back to matcherIn
			i = temp.length;
			while ( i-- ) {
				if ( ( elem = temp[ i ] ) ) {
					matcherOut[ postMap[ i ] ] = !( matcherIn[ postMap[ i ] ] = elem );
				}
			}
		}

		if ( seed ) {
			if ( postFinder || preFilter ) {
				if ( postFinder ) {

					// Get the final matcherOut by condensing this intermediate into postFinder contexts
					temp = [];
					i = matcherOut.length;
					while ( i-- ) {
						if ( ( elem = matcherOut[ i ] ) ) {

							// Restore matcherIn since elem is not yet a final match
							temp.push( ( matcherIn[ i ] = elem ) );
						}
					}
					postFinder( null, ( matcherOut = [] ), temp, xml );
				}

				// Move matched elements from seed to results to keep them synchronized
				i = matcherOut.length;
				while ( i-- ) {
					if ( ( elem = matcherOut[ i ] ) &&
						( temp = postFinder ? indexOf.call( seed, elem ) : preMap[ i ] ) > -1 ) {

						seed[ temp ] = !( results[ temp ] = elem );
					}
				}
			}

		// Add elements to results, through postFinder if defined
		} else {
			matcherOut = condense(
				matcherOut === results ?
					matcherOut.splice( preexisting, matcherOut.length ) :
					matcherOut
			);
			if ( postFinder ) {
				postFinder( null, results, matcherOut, xml );
			} else {
				push.apply( results, matcherOut );
			}
		}
	} );
}

function matcherFromTokens( tokens ) {
	var checkContext, matcher, j,
		len = tokens.length,
		leadingRelative = Expr.relative[ tokens[ 0 ].type ],
		implicitRelative = leadingRelative || Expr.relative[ " " ],
		i = leadingRelative ? 1 : 0,

		// The foundational matcher ensures that elements are reachable from top-level context(s)
		matchContext = addCombinator( function( elem ) {
			return elem === checkContext;
		}, implicitRelative, true ),
		matchAnyContext = addCombinator( function( elem ) {
			return indexOf.call( checkContext, elem ) > -1;
		}, implicitRelative, true ),
		matchers = [ function( elem, context, xml ) {

			// Support: IE 11+, Edge 17 - 18+
			// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
			// two documents; shallow comparisons work.
			// eslint-disable-next-line eqeqeq
			var ret = ( !leadingRelative && ( xml || context != outermostContext ) ) || (
				( checkContext = context ).nodeType ?
					matchContext( elem, context, xml ) :
					matchAnyContext( elem, context, xml ) );

			// Avoid hanging onto element
			// (see https://github.com/jquery/sizzle/issues/299)
			checkContext = null;
			return ret;
		} ];

	for ( ; i < len; i++ ) {
		if ( ( matcher = Expr.relative[ tokens[ i ].type ] ) ) {
			matchers = [ addCombinator( elementMatcher( matchers ), matcher ) ];
		} else {
			matcher = Expr.filter[ tokens[ i ].type ].apply( null, tokens[ i ].matches );

			// Return special upon seeing a positional matcher
			if ( matcher[ expando ] ) {

				// Find the next relative operator (if any) for proper handling
				j = ++i;
				for ( ; j < len; j++ ) {
					if ( Expr.relative[ tokens[ j ].type ] ) {
						break;
					}
				}
				return setMatcher(
					i > 1 && elementMatcher( matchers ),
					i > 1 && toSelector(

						// If the preceding token was a descendant combinator, insert an implicit any-element `*`
						tokens.slice( 0, i - 1 )
							.concat( { value: tokens[ i - 2 ].type === " " ? "*" : "" } )
					).replace( rtrimCSS, "$1" ),
					matcher,
					i < j && matcherFromTokens( tokens.slice( i, j ) ),
					j < len && matcherFromTokens( ( tokens = tokens.slice( j ) ) ),
					j < len && toSelector( tokens )
				);
			}
			matchers.push( matcher );
		}
	}

	return elementMatcher( matchers );
}

function matcherFromGroupMatchers( elementMatchers, setMatchers ) {
	var bySet = setMatchers.length > 0,
		byElement = elementMatchers.length > 0,
		superMatcher = function( seed, context, xml, results, outermost ) {
			var elem, j, matcher,
				matchedCount = 0,
				i = "0",
				unmatched = seed && [],
				setMatched = [],
				contextBackup = outermostContext,

				// We must always have either seed elements or outermost context
				elems = seed || byElement && Expr.find.TAG( "*", outermost ),

				// Use integer dirruns iff this is the outermost matcher
				dirrunsUnique = ( dirruns += contextBackup == null ? 1 : Math.random() || 0.1 ),
				len = elems.length;

			if ( outermost ) {

				// Support: IE 11+, Edge 17 - 18+
				// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
				// two documents; shallow comparisons work.
				// eslint-disable-next-line eqeqeq
				outermostContext = context == document || context || outermost;
			}

			// Add elements passing elementMatchers directly to results
			// Support: iOS <=7 - 9 only
			// Tolerate NodeList properties (IE: "length"; Safari: <number>) matching
			// elements by id. (see trac-14142)
			for ( ; i !== len && ( elem = elems[ i ] ) != null; i++ ) {
				if ( byElement && elem ) {
					j = 0;

					// Support: IE 11+, Edge 17 - 18+
					// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
					// two documents; shallow comparisons work.
					// eslint-disable-next-line eqeqeq
					if ( !context && elem.ownerDocument != document ) {
						setDocument( elem );
						xml = !documentIsHTML;
					}
					while ( ( matcher = elementMatchers[ j++ ] ) ) {
						if ( matcher( elem, context || document, xml ) ) {
							push.call( results, elem );
							break;
						}
					}
					if ( outermost ) {
						dirruns = dirrunsUnique;
					}
				}

				// Track unmatched elements for set filters
				if ( bySet ) {

					// They will have gone through all possible matchers
					if ( ( elem = !matcher && elem ) ) {
						matchedCount--;
					}

					// Lengthen the array for every element, matched or not
					if ( seed ) {
						unmatched.push( elem );
					}
				}
			}

			// `i` is now the count of elements visited above, and adding it to `matchedCount`
			// makes the latter nonnegative.
			matchedCount += i;

			// Apply set filters to unmatched elements
			// NOTE: This can be skipped if there are no unmatched elements (i.e., `matchedCount`
			// equals `i`), unless we didn't visit _any_ elements in the above loop because we have
			// no element matchers and no seed.
			// Incrementing an initially-string "0" `i` allows `i` to remain a string only in that
			// case, which will result in a "00" `matchedCount` that differs from `i` but is also
			// numerically zero.
			if ( bySet && i !== matchedCount ) {
				j = 0;
				while ( ( matcher = setMatchers[ j++ ] ) ) {
					matcher( unmatched, setMatched, context, xml );
				}

				if ( seed ) {

					// Reintegrate element matches to eliminate the need for sorting
					if ( matchedCount > 0 ) {
						while ( i-- ) {
							if ( !( unmatched[ i ] || setMatched[ i ] ) ) {
								setMatched[ i ] = pop.call( results );
							}
						}
					}

					// Discard index placeholder values to get only actual matches
					setMatched = condense( setMatched );
				}

				// Add matches to results
				push.apply( results, setMatched );

				// Seedless set matches succeeding multiple successful matchers stipulate sorting
				if ( outermost && !seed && setMatched.length > 0 &&
					( matchedCount + setMatchers.length ) > 1 ) {

					jQuery.uniqueSort( results );
				}
			}

			// Override manipulation of globals by nested matchers
			if ( outermost ) {
				dirruns = dirrunsUnique;
				outermostContext = contextBackup;
			}

			return unmatched;
		};

	return bySet ?
		markFunction( superMatcher ) :
		superMatcher;
}

function compile( selector, match /* Internal Use Only */ ) {
	var i,
		setMatchers = [],
		elementMatchers = [],
		cached = compilerCache[ selector + " " ];

	if ( !cached ) {

		// Generate a function of recursive functions that can be used to check each element
		if ( !match ) {
			match = tokenize( selector );
		}
		i = match.length;
		while ( i-- ) {
			cached = matcherFromTokens( match[ i ] );
			if ( cached[ expando ] ) {
				setMatchers.push( cached );
			} else {
				elementMatchers.push( cached );
			}
		}

		// Cache the compiled function
		cached = compilerCache( selector,
			matcherFromGroupMatchers( elementMatchers, setMatchers ) );

		// Save selector and tokenization
		cached.selector = selector;
	}
	return cached;
}

/**
 * A low-level selection function that works with jQuery's compiled
 *  selector functions
 * @param {String|Function} selector A selector or a pre-compiled
 *  selector function built with jQuery selector compile
 * @param {Element} context
 * @param {Array} [results]
 * @param {Array} [seed] A set of elements to match against
 */
function select( selector, context, results, seed ) {
	var i, tokens, token, type, find,
		compiled = typeof selector === "function" && selector,
		match = !seed && tokenize( ( selector = compiled.selector || selector ) );

	results = results || [];

	// Try to minimize operations if there is only one selector in the list and no seed
	// (the latter of which guarantees us context)
	if ( match.length === 1 ) {

		// Reduce context if the leading compound selector is an ID
		tokens = match[ 0 ] = match[ 0 ].slice( 0 );
		if ( tokens.length > 2 && ( token = tokens[ 0 ] ).type === "ID" &&
				context.nodeType === 9 && documentIsHTML && Expr.relative[ tokens[ 1 ].type ] ) {

			context = ( Expr.find.ID(
				token.matches[ 0 ].replace( runescape, funescape ),
				context
			) || [] )[ 0 ];
			if ( !context ) {
				return results;

			// Precompiled matchers will still verify ancestry, so step up a level
			} else if ( compiled ) {
				context = context.parentNode;
			}

			selector = selector.slice( tokens.shift().value.length );
		}

		// Fetch a seed set for right-to-left matching
		i = matchExpr.needsContext.test( selector ) ? 0 : tokens.length;
		while ( i-- ) {
			token = tokens[ i ];

			// Abort if we hit a combinator
			if ( Expr.relative[ ( type = token.type ) ] ) {
				break;
			}
			if ( ( find = Expr.find[ type ] ) ) {

				// Search, expanding context for leading sibling combinators
				if ( ( seed = find(
					token.matches[ 0 ].replace( runescape, funescape ),
					rsibling.test( tokens[ 0 ].type ) &&
						testContext( context.parentNode ) || context
				) ) ) {

					// If seed is empty or no tokens remain, we can return early
					tokens.splice( i, 1 );
					selector = seed.length && toSelector( tokens );
					if ( !selector ) {
						push.apply( results, seed );
						return results;
					}

					break;
				}
			}
		}
	}

	// Compile and execute a filtering function if one is not provided
	// Provide `match` to avoid retokenization if we modified the selector above
	( compiled || compile( selector, match ) )(
		seed,
		context,
		!documentIsHTML,
		results,
		!context || rsibling.test( selector ) && testContext( context.parentNode ) || context
	);
	return results;
}

// One-time assignments

// Support: Android <=4.0 - 4.1+
// Sort stability
support.sortStable = expando.split( "" ).sort( sortOrder ).join( "" ) === expando;

// Initialize against the default document
setDocument();

// Support: Android <=4.0 - 4.1+
// Detached nodes confoundingly follow *each other*
support.sortDetached = assert( function( el ) {

	// Should return 1, but returns 4 (following)
	return el.compareDocumentPosition( document.createElement( "fieldset" ) ) & 1;
} );

jQuery.find = find;

// Deprecated
jQuery.expr[ ":" ] = jQuery.expr.pseudos;
jQuery.unique = jQuery.uniqueSort;

// These have always been private, but they used to be documented as part of
// Sizzle so let's maintain them for now for backwards compatibility purposes.
find.compile = compile;
find.select = select;
find.setDocument = setDocument;
find.tokenize = tokenize;

find.escape = jQuery.escapeSelector;
find.getText = jQuery.text;
find.isXML = jQuery.isXMLDoc;
find.selectors = jQuery.expr;
find.support = jQuery.support;
find.uniqueSort = jQuery.uniqueSort;

	/* eslint-enable */

} )();


var dir = function( elem, dir, until ) {
	var matched = [],
		truncate = until !== undefined;

	while ( ( elem = elem[ dir ] ) && elem.nodeType !== 9 ) {
		if ( elem.nodeType === 1 ) {
			if ( truncate && jQuery( elem ).is( until ) ) {
				break;
			}
			matched.push( elem );
		}
	}
	return matched;
};


var siblings = function( n, elem ) {
	var matched = [];

	for ( ; n; n = n.nextSibling ) {
		if ( n.nodeType === 1 && n !== elem ) {
			matched.push( n );
		}
	}

	return matched;
};


var rneedsContext = jQuery.expr.match.needsContext;

var rsingleTag = ( /^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i );



// Implement the identical functionality for filter and not
function winnow( elements, qualifier, not ) {
	if ( isFunction( qualifier ) ) {
		return jQuery.grep( elements, function( elem, i ) {
			return !!qualifier.call( elem, i, elem ) !== not;
		} );
	}

	// Single element
	if ( qualifier.nodeType ) {
		return jQuery.grep( elements, function( elem ) {
			return ( elem === qualifier ) !== not;
		} );
	}

	// Arraylike of elements (jQuery, arguments, Array)
	if ( typeof qualifier !== "string" ) {
		return jQuery.grep( elements, function( elem ) {
			return ( indexOf.call( qualifier, elem ) > -1 ) !== not;
		} );
	}

	// Filtered directly for both simple and complex selectors
	return jQuery.filter( qualifier, elements, not );
}

jQuery.filter = function( expr, elems, not ) {
	var elem = elems[ 0 ];

	if ( not ) {
		expr = ":not(" + expr + ")";
	}

	if ( elems.length === 1 && elem.nodeType === 1 ) {
		return jQuery.find.matchesSelector( elem, expr ) ? [ elem ] : [];
	}

	return jQuery.find.matches( expr, jQuery.grep( elems, function( elem ) {
		return elem.nodeType === 1;
	} ) );
};

jQuery.fn.extend( {
	find: function( selector ) {
		var i, ret,
			len = this.length,
			self = this;

		if ( typeof selector !== "string" ) {
			return this.pushStack( jQuery( selector ).filter( function() {
				for ( i = 0; i < len; i++ ) {
					if ( jQuery.contains( self[ i ], this ) ) {
						return true;
					}
				}
			} ) );
		}

		ret = this.pushStack( [] );

		for ( i = 0; i < len; i++ ) {
			jQuery.find( selector, self[ i ], ret );
		}

		return len > 1 ? jQuery.uniqueSort( ret ) : ret;
	},
	filter: function( selector ) {
		return this.pushStack( winnow( this, selector || [], false ) );
	},
	not: function( selector ) {
		return this.pushStack( winnow( this, selector || [], true ) );
	},
	is: function( selector ) {
		return !!winnow(
			this,

			// If this is a positional/relative selector, check membership in the returned set
			// so $("p:first").is("p:last") won't return true for a doc with two "p".
			typeof selector === "string" && rneedsContext.test( selector ) ?
				jQuery( selector ) :
				selector || [],
			false
		).length;
	}
} );


// Initialize a jQuery object


// A central reference to the root jQuery(document)
var rootjQuery,

	// A simple way to check for HTML strings
	// Prioritize #id over <tag> to avoid XSS via location.hash (trac-9521)
	// Strict HTML recognition (trac-11290: must start with <)
	// Shortcut simple #id case for speed
	rquickExpr = /^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/,

	init = jQuery.fn.init = function( selector, context, root ) {
		var match, elem;

		// HANDLE: $(""), $(null), $(undefined), $(false)
		if ( !selector ) {
			return this;
		}

		// Method init() accepts an alternate rootjQuery
		// so migrate can support jQuery.sub (gh-2101)
		root = root || rootjQuery;

		// Handle HTML strings
		if ( typeof selector === "string" ) {
			if ( selector[ 0 ] === "<" &&
				selector[ selector.length - 1 ] === ">" &&
				selector.length >= 3 ) {

				// Assume that strings that start and end with <> are HTML and skip the regex check
				match = [ null, selector, null ];

			} else {
				match = rquickExpr.exec( selector );
			}

			// Match html or make sure no context is specified for #id
			if ( match && ( match[ 1 ] || !context ) ) {

				// HANDLE: $(html) -> $(array)
				if ( match[ 1 ] ) {
					context = context instanceof jQuery ? context[ 0 ] : context;

					// Option to run scripts is true for back-compat
					// Intentionally let the error be thrown if parseHTML is not present
					jQuery.merge( this, jQuery.parseHTML(
						match[ 1 ],
						context && context.nodeType ? context.ownerDocument || context : document,
						true
					) );

					// HANDLE: $(html, props)
					if ( rsingleTag.test( match[ 1 ] ) && jQuery.isPlainObject( context ) ) {
						for ( match in context ) {

							// Properties of context are called as methods if possible
							if ( isFunction( this[ match ] ) ) {
								this[ match ]( context[ match ] );

							// ...and otherwise set as attributes
							} else {
								this.attr( match, context[ match ] );
							}
						}
					}

					return this;

				// HANDLE: $(#id)
				} else {
					elem = document.getElementById( match[ 2 ] );

					if ( elem ) {

						// Inject the element directly into the jQuery object
						this[ 0 ] = elem;
						this.length = 1;
					}
					return this;
				}

			// HANDLE: $(expr, $(...))
			} else if ( !context || context.jquery ) {
				return ( context || root ).find( selector );

			// HANDLE: $(expr, context)
			// (which is just equivalent to: $(context).find(expr)
			} else {
				return this.constructor( context ).find( selector );
			}

		// HANDLE: $(DOMElement)
		} else if ( selector.nodeType ) {
			this[ 0 ] = selector;
			this.length = 1;
			return this;

		// HANDLE: $(function)
		// Shortcut for document ready
		} else if ( isFunction( selector ) ) {
			return root.ready !== undefined ?
				root.ready( selector ) :

				// Execute immediately if ready is not present
				selector( jQuery );
		}

		return jQuery.makeArray( selector, this );
	};

// Give the init function the jQuery prototype for later instantiation
init.prototype = jQuery.fn;

// Initialize central reference
rootjQuery = jQuery( document );


var rparentsprev = /^(?:parents|prev(?:Until|All))/,

	// Methods guaranteed to produce a unique set when starting from a unique set
	guaranteedUnique = {
		children: true,
		contents: true,
		next: true,
		prev: true
	};

jQuery.fn.extend( {
	has: function( target ) {
		var targets = jQuery( target, this ),
			l = targets.length;

		return this.filter( function() {
			var i = 0;
			for ( ; i < l; i++ ) {
				if ( jQuery.contains( this, targets[ i ] ) ) {
					return true;
				}
			}
		} );
	},

	closest: function( selectors, context ) {
		var cur,
			i = 0,
			l = this.length,
			matched = [],
			targets = typeof selectors !== "string" && jQuery( selectors );

		// Positional selectors never match, since there's no _selection_ context
		if ( !rneedsContext.test( selectors ) ) {
			for ( ; i < l; i++ ) {
				for ( cur = this[ i ]; cur && cur !== context; cur = cur.parentNode ) {

					// Always skip document fragments
					if ( cur.nodeType < 11 && ( targets ?
						targets.index( cur ) > -1 :

						// Don't pass non-elements to jQuery#find
						cur.nodeType === 1 &&
							jQuery.find.matchesSelector( cur, selectors ) ) ) {

						matched.push( cur );
						break;
					}
				}
			}
		}

		return this.pushStack( matched.length > 1 ? jQuery.uniqueSort( matched ) : matched );
	},

	// Determine the position of an element within the set
	index: function( elem ) {

		// No argument, return index in parent
		if ( !elem ) {
			return ( this[ 0 ] && this[ 0 ].parentNode ) ? this.first().prevAll().length : -1;
		}

		// Index in selector
		if ( typeof elem === "string" ) {
			return indexOf.call( jQuery( elem ), this[ 0 ] );
		}

		// Locate the position of the desired element
		return indexOf.call( this,

			// If it receives a jQuery object, the first element is used
			elem.jquery ? elem[ 0 ] : elem
		);
	},

	add: function( selector, context ) {
		return this.pushStack(
			jQuery.uniqueSort(
				jQuery.merge( this.get(), jQuery( selector, context ) )
			)
		);
	},

	addBack: function( selector ) {
		return this.add( selector == null ?
			this.prevObject : this.prevObject.filter( selector )
		);
	}
} );

function sibling( cur, dir ) {
	while ( ( cur = cur[ dir ] ) && cur.nodeType !== 1 ) {}
	return cur;
}

jQuery.each( {
	parent: function( elem ) {
		var parent = elem.parentNode;
		return parent && parent.nodeType !== 11 ? parent : null;
	},
	parents: function( elem ) {
		return dir( elem, "parentNode" );
	},
	parentsUntil: function( elem, _i, until ) {
		return dir( elem, "parentNode", until );
	},
	next: function( elem ) {
		return sibling( elem, "nextSibling" );
	},
	prev: function( elem ) {
		return sibling( elem, "previousSibling" );
	},
	nextAll: function( elem ) {
		return dir( elem, "nextSibling" );
	},
	prevAll: function( elem ) {
		return dir( elem, "previousSibling" );
	},
	nextUntil: function( elem, _i, until ) {
		return dir( elem, "nextSibling", until );
	},
	prevUntil: function( elem, _i, until ) {
		return dir( elem, "previousSibling", until );
	},
	siblings: function( elem ) {
		return siblings( ( elem.parentNode || {} ).firstChild, elem );
	},
	children: function( elem ) {
		return siblings( elem.firstChild );
	},
	contents: function( elem ) {
		if ( elem.contentDocument != null &&

			// Support: IE 11+
			// <object> elements with no `data` attribute has an object
			// `contentDocument` with a `null` prototype.
			getProto( elem.contentDocument ) ) {

			return elem.contentDocument;
		}

		// Support: IE 9 - 11 only, iOS 7 only, Android Browser <=4.3 only
		// Treat the template element as a regular one in browsers that
		// don't support it.
		if ( nodeName( elem, "template" ) ) {
			elem = elem.content || elem;
		}

		return jQuery.merge( [], elem.childNodes );
	}
}, function( name, fn ) {
	jQuery.fn[ name ] = function( until, selector ) {
		var matched = jQuery.map( this, fn, until );

		if ( name.slice( -5 ) !== "Until" ) {
			selector = until;
		}

		if ( selector && typeof selector === "string" ) {
			matched = jQuery.filter( selector, matched );
		}

		if ( this.length > 1 ) {

			// Remove duplicates
			if ( !guaranteedUnique[ name ] ) {
				jQuery.uniqueSort( matched );
			}

			// Reverse order for parents* and prev-derivatives
			if ( rparentsprev.test( name ) ) {
				matched.reverse();
			}
		}

		return this.pushStack( matched );
	};
} );
var rnothtmlwhite = ( /[^\x20\t\r\n\f]+/g );



// Convert String-formatted options into Object-formatted ones
function createOptions( options ) {
	var object = {};
	jQuery.each( options.match( rnothtmlwhite ) || [], function( _, flag ) {
		object[ flag ] = true;
	} );
	return object;
}

/*
 * Create a callback list using the following parameters:
 *
 *	options: an optional list of space-separated options that will change how
 *			the callback list behaves or a more traditional option object
 *
 * By default a callback list will act like an event callback list and can be
 * "fired" multiple times.
 *
 * Possible options:
 *
 *	once:			will ensure the callback list can only be fired once (like a Deferred)
 *
 *	memory:			will keep track of previous values and will call any callback added
 *					after the list has been fired right away with the latest "memorized"
 *					values (like a Deferred)
 *
 *	unique:			will ensure a callback can only be added once (no duplicate in the list)
 *
 *	stopOnFalse:	interrupt callings when a callback returns false
 *
 */
jQuery.Callbacks = function( options ) {

	// Convert options from String-formatted to Object-formatted if needed
	// (we check in cache first)
	options = typeof options === "string" ?
		createOptions( options ) :
		jQuery.extend( {}, options );

	var // Flag to know if list is currently firing
		firing,

		// Last fire value for non-forgettable lists
		memory,

		// Flag to know if list was already fired
		fired,

		// Flag to prevent firing
		locked,

		// Actual callback list
		list = [],

		// Queue of execution data for repeatable lists
		queue = [],

		// Index of currently firing callback (modified by add/remove as needed)
		firingIndex = -1,

		// Fire callbacks
		fire = function() {

			// Enforce single-firing
			locked = locked || options.once;

			// Execute callbacks for all pending executions,
			// respecting firingIndex overrides and runtime changes
			fired = firing = true;
			for ( ; queue.length; firingIndex = -1 ) {
				memory = queue.shift();
				while ( ++firingIndex < list.length ) {

					// Run callback and check for early termination
					if ( list[ firingIndex ].apply( memory[ 0 ], memory[ 1 ] ) === false &&
						options.stopOnFalse ) {

						// Jump to end and forget the data so .add doesn't re-fire
						firingIndex = list.length;
						memory = false;
					}
				}
			}

			// Forget the data if we're done with it
			if ( !options.memory ) {
				memory = false;
			}

			firing = false;

			// Clean up if we're done firing for good
			if ( locked ) {

				// Keep an empty list if we have data for future add calls
				if ( memory ) {
					list = [];

				// Otherwise, this object is spent
				} else {
					list = "";
				}
			}
		},

		// Actual Callbacks object
		self = {

			// Add a callback or a collection of callbacks to the list
			add: function() {
				if ( list ) {

					// If we have memory from a past run, we should fire after adding
					if ( memory && !firing ) {
						firingIndex = list.length - 1;
						queue.push( memory );
					}

					( function add( args ) {
						jQuery.each( args, function( _, arg ) {
							if ( isFunction( arg ) ) {
								if ( !options.unique || !self.has( arg ) ) {
									list.push( arg );
								}
							} else if ( arg && arg.length && toType( arg ) !== "string" ) {

								// Inspect recursively
								add( arg );
							}
						} );
					} )( arguments );

					if ( memory && !firing ) {
						fire();
					}
				}
				return this;
			},

			// Remove a callback from the list
			remove: function() {
				jQuery.each( arguments, function( _, arg ) {
					var index;
					while ( ( index = jQuery.inArray( arg, list, index ) ) > -1 ) {
						list.splice( index, 1 );

						// Handle firing indexes
						if ( index <= firingIndex ) {
							firingIndex--;
						}
					}
				} );
				return this;
			},

			// Check if a given callback is in the list.
			// If no argument is given, return whether or not list has callbacks attached.
			has: function( fn ) {
				return fn ?
					jQuery.inArray( fn, list ) > -1 :
					list.length > 0;
			},

			// Remove all callbacks from the list
			empty: function() {
				if ( list ) {
					list = [];
				}
				return this;
			},

			// Disable .fire and .add
			// Abort any current/pending executions
			// Clear all callbacks and values
			disable: function() {
				locked = queue = [];
				list = memory = "";
				return this;
			},
			disabled: function() {
				return !list;
			},

			// Disable .fire
			// Also disable .add unless we have memory (since it would have no effect)
			// Abort any pending executions
			lock: function() {
				locked = queue = [];
				if ( !memory && !firing ) {
					list = memory = "";
				}
				return this;
			},
			locked: function() {
				return !!locked;
			},

			// Call all callbacks with the given context and arguments
			fireWith: function( context, args ) {
				if ( !locked ) {
					args = args || [];
					args = [ context, args.slice ? args.slice() : args ];
					queue.push( args );
					if ( !firing ) {
						fire();
					}
				}
				return this;
			},

			// Call all the callbacks with the given arguments
			fire: function() {
				self.fireWith( this, arguments );
				return this;
			},

			// To know if the callbacks have already been called at least once
			fired: function() {
				return !!fired;
			}
		};

	return self;
};


function Identity( v ) {
	return v;
}
function Thrower( ex ) {
	throw ex;
}

function adoptValue( value, resolve, reject, noValue ) {
	var method;

	try {

		// Check for promise aspect first to privilege synchronous behavior
		if ( value && isFunction( ( method = value.promise ) ) ) {
			method.call( value ).done( resolve ).fail( reject );

		// Other thenables
		} else if ( value && isFunction( ( method = value.then ) ) ) {
			method.call( value, resolve, reject );

		// Other non-thenables
		} else {

			// Control `resolve` arguments by letting Array#slice cast boolean `noValue` to integer:
			// * false: [ value ].slice( 0 ) => resolve( value )
			// * true: [ value ].slice( 1 ) => resolve()
			resolve.apply( undefined, [ value ].slice( noValue ) );
		}

	// For Promises/A+, convert exceptions into rejections
	// Since jQuery.when doesn't unwrap thenables, we can skip the extra checks appearing in
	// Deferred#then to conditionally suppress rejection.
	} catch ( value ) {

		// Support: Android 4.0 only
		// Strict mode functions invoked without .call/.apply get global-object context
		reject.apply( undefined, [ value ] );
	}
}

jQuery.extend( {

	Deferred: function( func ) {
		var tuples = [

				// action, add listener, callbacks,
				// ... .then handlers, argument index, [final state]
				[ "notify", "progress", jQuery.Callbacks( "memory" ),
					jQuery.Callbacks( "memory" ), 2 ],
				[ "resolve", "done", jQuery.Callbacks( "once memory" ),
					jQuery.Callbacks( "once memory" ), 0, "resolved" ],
				[ "reject", "fail", jQuery.Callbacks( "once memory" ),
					jQuery.Callbacks( "once memory" ), 1, "rejected" ]
			],
			state = "pending",
			promise = {
				state: function() {
					return state;
				},
				always: function() {
					deferred.done( arguments ).fail( arguments );
					return this;
				},
				"catch": function( fn ) {
					return promise.then( null, fn );
				},

				// Keep pipe for back-compat
				pipe: function( /* fnDone, fnFail, fnProgress */ ) {
					var fns = arguments;

					return jQuery.Deferred( function( newDefer ) {
						jQuery.each( tuples, function( _i, tuple ) {

							// Map tuples (progress, done, fail) to arguments (done, fail, progress)
							var fn = isFunction( fns[ tuple[ 4 ] ] ) && fns[ tuple[ 4 ] ];

							// deferred.progress(function() { bind to newDefer or newDefer.notify })
							// deferred.done(function() { bind to newDefer or newDefer.resolve })
							// deferred.fail(function() { bind to newDefer or newDefer.reject })
							deferred[ tuple[ 1 ] ]( function() {
								var returned = fn && fn.apply( this, arguments );
								if ( returned && isFunction( returned.promise ) ) {
									returned.promise()
										.progress( newDefer.notify )
										.done( newDefer.resolve )
										.fail( newDefer.reject );
								} else {
									newDefer[ tuple[ 0 ] + "With" ](
										this,
										fn ? [ returned ] : arguments
									);
								}
							} );
						} );
						fns = null;
					} ).promise();
				},
				then: function( onFulfilled, onRejected, onProgress ) {
					var maxDepth = 0;
					function resolve( depth, deferred, handler, special ) {
						return function() {
							var that = this,
								args = arguments,
								mightThrow = function() {
									var returned, then;

									// Support: Promises/A+ section 2.3.3.3.3
									// https://promisesaplus.com/#point-59
									// Ignore double-resolution attempts
									if ( depth < maxDepth ) {
										return;
									}

									returned = handler.apply( that, args );

									// Support: Promises/A+ section 2.3.1
									// https://promisesaplus.com/#point-48
									if ( returned === deferred.promise() ) {
										throw new TypeError( "Thenable self-resolution" );
									}

									// Support: Promises/A+ sections 2.3.3.1, 3.5
									// https://promisesaplus.com/#point-54
									// https://promisesaplus.com/#point-75
									// Retrieve `then` only once
									then = returned &&

										// Support: Promises/A+ section 2.3.4
										// https://promisesaplus.com/#point-64
										// Only check objects and functions for thenability
										( typeof returned === "object" ||
											typeof returned === "function" ) &&
										returned.then;

									// Handle a returned thenable
									if ( isFunction( then ) ) {

										// Special processors (notify) just wait for resolution
										if ( special ) {
											then.call(
												returned,
												resolve( maxDepth, deferred, Identity, special ),
												resolve( maxDepth, deferred, Thrower, special )
											);

										// Normal processors (resolve) also hook into progress
										} else {

											// ...and disregard older resolution values
											maxDepth++;

											then.call(
												returned,
												resolve( maxDepth, deferred, Identity, special ),
												resolve( maxDepth, deferred, Thrower, special ),
												resolve( maxDepth, deferred, Identity,
													deferred.notifyWith )
											);
										}

									// Handle all other returned values
									} else {

										// Only substitute handlers pass on context
										// and multiple values (non-spec behavior)
										if ( handler !== Identity ) {
											that = undefined;
											args = [ returned ];
										}

										// Process the value(s)
										// Default process is resolve
										( special || deferred.resolveWith )( that, args );
									}
								},

								// Only normal processors (resolve) catch and reject exceptions
								process = special ?
									mightThrow :
									function() {
										try {
											mightThrow();
										} catch ( e ) {

											if ( jQuery.Deferred.exceptionHook ) {
												jQuery.Deferred.exceptionHook( e,
													process.error );
											}

											// Support: Promises/A+ section 2.3.3.3.4.1
											// https://promisesaplus.com/#point-61
											// Ignore post-resolution exceptions
											if ( depth + 1 >= maxDepth ) {

												// Only substitute handlers pass on context
												// and multiple values (non-spec behavior)
												if ( handler !== Thrower ) {
													that = undefined;
													args = [ e ];
												}

												deferred.rejectWith( that, args );
											}
										}
									};

							// Support: Promises/A+ section 2.3.3.3.1
							// https://promisesaplus.com/#point-57
							// Re-resolve promises immediately to dodge false rejection from
							// subsequent errors
							if ( depth ) {
								process();
							} else {

								// Call an optional hook to record the error, in case of exception
								// since it's otherwise lost when execution goes async
								if ( jQuery.Deferred.getErrorHook ) {
									process.error = jQuery.Deferred.getErrorHook();

								// The deprecated alias of the above. While the name suggests
								// returning the stack, not an error instance, jQuery just passes
								// it directly to `console.warn` so both will work; an instance
								// just better cooperates with source maps.
								} else if ( jQuery.Deferred.getStackHook ) {
									process.error = jQuery.Deferred.getStackHook();
								}
								window.setTimeout( process );
							}
						};
					}

					return jQuery.Deferred( function( newDefer ) {

						// progress_handlers.add( ... )
						tuples[ 0 ][ 3 ].add(
							resolve(
								0,
								newDefer,
								isFunction( onProgress ) ?
									onProgress :
									Identity,
								newDefer.notifyWith
							)
						);

						// fulfilled_handlers.add( ... )
						tuples[ 1 ][ 3 ].add(
							resolve(
								0,
								newDefer,
								isFunction( onFulfilled ) ?
									onFulfilled :
									Identity
							)
						);

						// rejected_handlers.add( ... )
						tuples[ 2 ][ 3 ].add(
							resolve(
								0,
								newDefer,
								isFunction( onRejected ) ?
									onRejected :
									Thrower
							)
						);
					} ).promise();
				},

				// Get a promise for this deferred
				// If obj is provided, the promise aspect is added to the object
				promise: function( obj ) {
					return obj != null ? jQuery.extend( obj, promise ) : promise;
				}
			},
			deferred = {};

		// Add list-specific methods
		jQuery.each( tuples, function( i, tuple ) {
			var list = tuple[ 2 ],
				stateString = tuple[ 5 ];

			// promise.progress = list.add
			// promise.done = list.add
			// promise.fail = list.add
			promise[ tuple[ 1 ] ] = list.add;

			// Handle state
			if ( stateString ) {
				list.add(
					function() {

						// state = "resolved" (i.e., fulfilled)
						// state = "rejected"
						state = stateString;
					},

					// rejected_callbacks.disable
					// fulfilled_callbacks.disable
					tuples[ 3 - i ][ 2 ].disable,

					// rejected_handlers.disable
					// fulfilled_handlers.disable
					tuples[ 3 - i ][ 3 ].disable,

					// progress_callbacks.lock
					tuples[ 0 ][ 2 ].lock,

					// progress_handlers.lock
					tuples[ 0 ][ 3 ].lock
				);
			}

			// progress_handlers.fire
			// fulfilled_handlers.fire
			// rejected_handlers.fire
			list.add( tuple[ 3 ].fire );

			// deferred.notify = function() { deferred.notifyWith(...) }
			// deferred.resolve = function() { deferred.resolveWith(...) }
			// deferred.reject = function() { deferred.rejectWith(...) }
			deferred[ tuple[ 0 ] ] = function() {
				deferred[ tuple[ 0 ] + "With" ]( this === deferred ? undefined : this, arguments );
				return this;
			};

			// deferred.notifyWith = list.fireWith
			// deferred.resolveWith = list.fireWith
			// deferred.rejectWith = list.fireWith
			deferred[ tuple[ 0 ] + "With" ] = list.fireWith;
		} );

		// Make the deferred a promise
		promise.promise( deferred );

		// Call given func if any
		if ( func ) {
			func.call( deferred, deferred );
		}

		// All done!
		return deferred;
	},

	// Deferred helper
	when: function( singleValue ) {
		var

			// count of uncompleted subordinates
			remaining = arguments.length,

			// count of unprocessed arguments
			i = remaining,

			// subordinate fulfillment data
			resolveContexts = Array( i ),
			resolveValues = slice.call( arguments ),

			// the primary Deferred
			primary = jQuery.Deferred(),

			// subordinate callback factory
			updateFunc = function( i ) {
				return function( value ) {
					resolveContexts[ i ] = this;
					resolveValues[ i ] = arguments.length > 1 ? slice.call( arguments ) : value;
					if ( !( --remaining ) ) {
						primary.resolveWith( resolveContexts, resolveValues );
					}
				};
			};

		// Single- and empty arguments are adopted like Promise.resolve
		if ( remaining <= 1 ) {
			adoptValue( singleValue, primary.done( updateFunc( i ) ).resolve, primary.reject,
				!remaining );

			// Use .then() to unwrap secondary thenables (cf. gh-3000)
			if ( primary.state() === "pending" ||
				isFunction( resolveValues[ i ] && resolveValues[ i ].then ) ) {

				return primary.then();
			}
		}

		// Multiple arguments are aggregated like Promise.all array elements
		while ( i-- ) {
			adoptValue( resolveValues[ i ], updateFunc( i ), primary.reject );
		}

		return primary.promise();
	}
} );


// These usually indicate a programmer mistake during development,
// warn about them ASAP rather than swallowing them by default.
var rerrorNames = /^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;

// If `jQuery.Deferred.getErrorHook` is defined, `asyncError` is an error
// captured before the async barrier to get the original error cause
// which may otherwise be hidden.
jQuery.Deferred.exceptionHook = function( error, asyncError ) {

	// Support: IE 8 - 9 only
	// Console exists when dev tools are open, which can happen at any time
	if ( window.console && window.console.warn && error && rerrorNames.test( error.name ) ) {
		window.console.warn( "jQuery.Deferred exception: " + error.message,
			error.stack, asyncError );
	}
};




jQuery.readyException = function( error ) {
	window.setTimeout( function() {
		throw error;
	} );
};




// The deferred used on DOM ready
var readyList = jQuery.Deferred();

jQuery.fn.ready = function( fn ) {

	readyList
		.then( fn )

		// Wrap jQuery.readyException in a function so that the lookup
		// happens at the time of error handling instead of callback
		// registration.
		.catch( function( error ) {
			jQuery.readyException( error );
		} );

	return this;
};

jQuery.extend( {

	// Is the DOM ready to be used? Set to true once it occurs.
	isReady: false,

	// A counter to track how many items to wait for before
	// the ready event fires. See trac-6781
	readyWait: 1,

	// Handle when the DOM is ready
	ready: function( wait ) {

		// Abort if there are pending holds or we're already ready
		if ( wait === true ? --jQuery.readyWait : jQuery.isReady ) {
			return;
		}

		// Remember that the DOM is ready
		jQuery.isReady = true;

		// If a normal DOM Ready event fired, decrement, and wait if need be
		if ( wait !== true && --jQuery.readyWait > 0 ) {
			return;
		}

		// If there are functions bound, to execute
		readyList.resolveWith( document, [ jQuery ] );
	}
} );

jQuery.ready.then = readyList.then;

// The ready event handler and self cleanup method
function completed() {
	document.removeEventListener( "DOMContentLoaded", completed );
	window.removeEventListener( "load", completed );
	jQuery.ready();
}

// Catch cases where $(document).ready() is called
// after the browser event has already occurred.
// Support: IE <=9 - 10 only
// Older IE sometimes signals "interactive" too soon
if ( document.readyState === "complete" ||
	( document.readyState !== "loading" && !document.documentElement.doScroll ) ) {

	// Handle it asynchronously to allow scripts the opportunity to delay ready
	window.setTimeout( jQuery.ready );

} else {

	// Use the handy event callback
	document.addEventListener( "DOMContentLoaded", completed );

	// A fallback to window.onload, that will always work
	window.addEventListener( "load", completed );
}




// Multifunctional method to get and set values of a collection
// The value/s can optionally be executed if it's a function
var access = function( elems, fn, key, value, chainable, emptyGet, raw ) {
	var i = 0,
		len = elems.length,
		bulk = key == null;

	// Sets many values
	if ( toType( key ) === "object" ) {
		chainable = true;
		for ( i in key ) {
			access( elems, fn, i, key[ i ], true, emptyGet, raw );
		}

	// Sets one value
	} else if ( value !== undefined ) {
		chainable = true;

		if ( !isFunction( value ) ) {
			raw = true;
		}

		if ( bulk ) {

			// Bulk operations run against the entire set
			if ( raw ) {
				fn.call( elems, value );
				fn = null;

			// ...except when executing function values
			} else {
				bulk = fn;
				fn = function( elem, _key, value ) {
					return bulk.call( jQuery( elem ), value );
				};
			}
		}

		if ( fn ) {
			for ( ; i < len; i++ ) {
				fn(
					elems[ i ], key, raw ?
						value :
						value.call( elems[ i ], i, fn( elems[ i ], key ) )
				);
			}
		}
	}

	if ( chainable ) {
		return elems;
	}

	// Gets
	if ( bulk ) {
		return fn.call( elems );
	}

	return len ? fn( elems[ 0 ], key ) : emptyGet;
};


// Matches dashed string for camelizing
var rmsPrefix = /^-ms-/,
	rdashAlpha = /-([a-z])/g;

// Used by camelCase as callback to replace()
function fcamelCase( _all, letter ) {
	return letter.toUpperCase();
}

// Convert dashed to camelCase; used by the css and data modules
// Support: IE <=9 - 11, Edge 12 - 15
// Microsoft forgot to hump their vendor prefix (trac-9572)
function camelCase( string ) {
	return string.replace( rmsPrefix, "ms-" ).replace( rdashAlpha, fcamelCase );
}
var acceptData = function( owner ) {

	// Accepts only:
	//  - Node
	//    - Node.ELEMENT_NODE
	//    - Node.DOCUMENT_NODE
	//  - Object
	//    - Any
	return owner.nodeType === 1 || owner.nodeType === 9 || !( +owner.nodeType );
};




function Data() {
	this.expando = jQuery.expando + Data.uid++;
}

Data.uid = 1;

Data.prototype = {

	cache: function( owner ) {

		// Check if the owner object already has a cache
		var value = owner[ this.expando ];

		// If not, create one
		if ( !value ) {
			value = {};

			// We can accept data for non-element nodes in modern browsers,
			// but we should not, see trac-8335.
			// Always return an empty object.
			if ( acceptData( owner ) ) {

				// If it is a node unlikely to be stringify-ed or looped over
				// use plain assignment
				if ( owner.nodeType ) {
					owner[ this.expando ] = value;

				// Otherwise secure it in a non-enumerable property
				// configurable must be true to allow the property to be
				// deleted when data is removed
				} else {
					Object.defineProperty( owner, this.expando, {
						value: value,
						configurable: true
					} );
				}
			}
		}

		return value;
	},
	set: function( owner, data, value ) {
		var prop,
			cache = this.cache( owner );

		// Handle: [ owner, key, value ] args
		// Always use camelCase key (gh-2257)
		if ( typeof data === "string" ) {
			cache[ camelCase( data ) ] = value;

		// Handle: [ owner, { properties } ] args
		} else {

			// Copy the properties one-by-one to the cache object
			for ( prop in data ) {
				cache[ camelCase( prop ) ] = data[ prop ];
			}
		}
		return cache;
	},
	get: function( owner, key ) {
		return key === undefined ?
			this.cache( owner ) :

			// Always use camelCase key (gh-2257)
			owner[ this.expando ] && owner[ this.expando ][ camelCase( key ) ];
	},
	access: function( owner, key, value ) {

		// In cases where either:
		//
		//   1. No key was specified
		//   2. A string key was specified, but no value provided
		//
		// Take the "read" path and allow the get method to determine
		// which value to return, respectively either:
		//
		//   1. The entire cache object
		//   2. The data stored at the key
		//
		if ( key === undefined ||
				( ( key && typeof key === "string" ) && value === undefined ) ) {

			return this.get( owner, key );
		}

		// When the key is not a string, or both a key and value
		// are specified, set or extend (existing objects) with either:
		//
		//   1. An object of properties
		//   2. A key and value
		//
		this.set( owner, key, value );

		// Since the "set" path can have two possible entry points
		// return the expected data based on which path was taken[*]
		return value !== undefined ? value : key;
	},
	remove: function( owner, key ) {
		var i,
			cache = owner[ this.expando ];

		if ( cache === undefined ) {
			return;
		}

		if ( key !== undefined ) {

			// Support array or space separated string of keys
			if ( Array.isArray( key ) ) {

				// If key is an array of keys...
				// We always set camelCase keys, so remove that.
				key = key.map( camelCase );
			} else {
				key = camelCase( key );

				// If a key with the spaces exists, use it.
				// Otherwise, create an array by matching non-whitespace
				key = key in cache ?
					[ key ] :
					( key.match( rnothtmlwhite ) || [] );
			}

			i = key.length;

			while ( i-- ) {
				delete cache[ key[ i ] ];
			}
		}

		// Remove the expando if there's no more data
		if ( key === undefined || jQuery.isEmptyObject( cache ) ) {

			// Support: Chrome <=35 - 45
			// Webkit & Blink performance suffers when deleting properties
			// from DOM nodes, so set to undefined instead
			// https://bugs.chromium.org/p/chromium/issues/detail?id=378607 (bug restricted)
			if ( owner.nodeType ) {
				owner[ this.expando ] = undefined;
			} else {
				delete owner[ this.expando ];
			}
		}
	},
	hasData: function( owner ) {
		var cache = owner[ this.expando ];
		return cache !== undefined && !jQuery.isEmptyObject( cache );
	}
};
var dataPriv = new Data();

var dataUser = new Data();



//	Implementation Summary
//
//	1. Enforce API surface and semantic compatibility with 1.9.x branch
//	2. Improve the module's maintainability by reducing the storage
//		paths to a single mechanism.
//	3. Use the same single mechanism to support "private" and "user" data.
//	4. _Never_ expose "private" data to user code (TODO: Drop _data, _removeData)
//	5. Avoid exposing implementation details on user objects (eg. expando properties)
//	6. Provide a clear path for implementation upgrade to WeakMap in 2014

var rbrace = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,
	rmultiDash = /[A-Z]/g;

function getData( data ) {
	if ( data === "true" ) {
		return true;
	}

	if ( data === "false" ) {
		return false;
	}

	if ( data === "null" ) {
		return null;
	}

	// Only convert to a number if it doesn't change the string
	if ( data === +data + "" ) {
		return +data;
	}

	if ( rbrace.test( data ) ) {
		return JSON.parse( data );
	}

	return data;
}

function dataAttr( elem, key, data ) {
	var name;

	// If nothing was found internally, try to fetch any
	// data from the HTML5 data-* attribute
	if ( data === undefined && elem.nodeType === 1 ) {
		name = "data-" + key.replace( rmultiDash, "-$&" ).toLowerCase();
		data = elem.getAttribute( name );

		if ( typeof data === "string" ) {
			try {
				data = getData( data );
			} catch ( e ) {}

			// Make sure we set the data so it isn't changed later
			dataUser.set( elem, key, data );
		} else {
			data = undefined;
		}
	}
	return data;
}

jQuery.extend( {
	hasData: function( elem ) {
		return dataUser.hasData( elem ) || dataPriv.hasData( elem );
	},

	data: function( elem, name, data ) {
		return dataUser.access( elem, name, data );
	},

	removeData: function( elem, name ) {
		dataUser.remove( elem, name );
	},

	// TODO: Now that all calls to _data and _removeData have been replaced
	// with direct calls to dataPriv methods, these can be deprecated.
	_data: function( elem, name, data ) {
		return dataPriv.access( elem, name, data );
	},

	_removeData: function( elem, name ) {
		dataPriv.remove( elem, name );
	}
} );

jQuery.fn.extend( {
	data: function( key, value ) {
		var i, name, data,
			elem = this[ 0 ],
			attrs = elem && elem.attributes;

		// Gets all values
		if ( key === undefined ) {
			if ( this.length ) {
				data = dataUser.get( elem );

				if ( elem.nodeType === 1 && !dataPriv.get( elem, "hasDataAttrs" ) ) {
					i = attrs.length;
					while ( i-- ) {

						// Support: IE 11 only
						// The attrs elements can be null (trac-14894)
						if ( attrs[ i ] ) {
							name = attrs[ i ].name;
							if ( name.indexOf( "data-" ) === 0 ) {
								name = camelCase( name.slice( 5 ) );
								dataAttr( elem, name, data[ name ] );
							}
						}
					}
					dataPriv.set( elem, "hasDataAttrs", true );
				}
			}

			return data;
		}

		// Sets multiple values
		if ( typeof key === "object" ) {
			return this.each( function() {
				dataUser.set( this, key );
			} );
		}

		return access( this, function( value ) {
			var data;

			// The calling jQuery object (element matches) is not empty
			// (and therefore has an element appears at this[ 0 ]) and the
			// `value` parameter was not undefined. An empty jQuery object
			// will result in `undefined` for elem = this[ 0 ] which will
			// throw an exception if an attempt to read a data cache is made.
			if ( elem && value === undefined ) {

				// Attempt to get data from the cache
				// The key will always be camelCased in Data
				data = dataUser.get( elem, key );
				if ( data !== undefined ) {
					return data;
				}

				// Attempt to "discover" the data in
				// HTML5 custom data-* attrs
				data = dataAttr( elem, key );
				if ( data !== undefined ) {
					return data;
				}

				// We tried really hard, but the data doesn't exist.
				return;
			}

			// Set the data...
			this.each( function() {

				// We always store the camelCased key
				dataUser.set( this, key, value );
			} );
		}, null, value, arguments.length > 1, null, true );
	},

	removeData: function( key ) {
		return this.each( function() {
			dataUser.remove( this, key );
		} );
	}
} );


jQuery.extend( {
	queue: function( elem, type, data ) {
		var queue;

		if ( elem ) {
			type = ( type || "fx" ) + "queue";
			queue = dataPriv.get( elem, type );

			// Speed up dequeue by getting out quickly if this is just a lookup
			if ( data ) {
				if ( !queue || Array.isArray( data ) ) {
					queue = dataPriv.access( elem, type, jQuery.makeArray( data ) );
				} else {
					queue.push( data );
				}
			}
			return queue || [];
		}
	},

	dequeue: function( elem, type ) {
		type = type || "fx";

		var queue = jQuery.queue( elem, type ),
			startLength = queue.length,
			fn = queue.shift(),
			hooks = jQuery._queueHooks( elem, type ),
			next = function() {
				jQuery.dequeue( elem, type );
			};

		// If the fx queue is dequeued, always remove the progress sentinel
		if ( fn === "inprogress" ) {
			fn = queue.shift();
			startLength--;
		}

		if ( fn ) {

			// Add a progress sentinel to prevent the fx queue from being
			// automatically dequeued
			if ( type === "fx" ) {
				queue.unshift( "inprogress" );
			}

			// Clear up the last queue stop function
			delete hooks.stop;
			fn.call( elem, next, hooks );
		}

		if ( !startLength && hooks ) {
			hooks.empty.fire();
		}
	},

	// Not public - generate a queueHooks object, or return the current one
	_queueHooks: function( elem, type ) {
		var key = type + "queueHooks";
		return dataPriv.get( elem, key ) || dataPriv.access( elem, key, {
			empty: jQuery.Callbacks( "once memory" ).add( function() {
				dataPriv.remove( elem, [ type + "queue", key ] );
			} )
		} );
	}
} );

jQuery.fn.extend( {
	queue: function( type, data ) {
		var setter = 2;

		if ( typeof type !== "string" ) {
			data = type;
			type = "fx";
			setter--;
		}

		if ( arguments.length < setter ) {
			return jQuery.queue( this[ 0 ], type );
		}

		return data === undefined ?
			this :
			this.each( function() {
				var queue = jQuery.queue( this, type, data );

				// Ensure a hooks for this queue
				jQuery._queueHooks( this, type );

				if ( type === "fx" && queue[ 0 ] !== "inprogress" ) {
					jQuery.dequeue( this, type );
				}
			} );
	},
	dequeue: function( type ) {
		return this.each( function() {
			jQuery.dequeue( this, type );
		} );
	},
	clearQueue: function( type ) {
		return this.queue( type || "fx", [] );
	},

	// Get a promise resolved when queues of a certain type
	// are emptied (fx is the type by default)
	promise: function( type, obj ) {
		var tmp,
			count = 1,
			defer = jQuery.Deferred(),
			elements = this,
			i = this.length,
			resolve = function() {
				if ( !( --count ) ) {
					defer.resolveWith( elements, [ elements ] );
				}
			};

		if ( typeof type !== "string" ) {
			obj = type;
			type = undefined;
		}
		type = type || "fx";

		while ( i-- ) {
			tmp = dataPriv.get( elements[ i ], type + "queueHooks" );
			if ( tmp && tmp.empty ) {
				count++;
				tmp.empty.add( resolve );
			}
		}
		resolve();
		return defer.promise( obj );
	}
} );
var pnum = ( /[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/ ).source;

var rcssNum = new RegExp( "^(?:([+-])=|)(" + pnum + ")([a-z%]*)$", "i" );


var cssExpand = [ "Top", "Right", "Bottom", "Left" ];

var documentElement = document.documentElement;



	var isAttached = function( elem ) {
			return jQuery.contains( elem.ownerDocument, elem );
		},
		composed = { composed: true };

	// Support: IE 9 - 11+, Edge 12 - 18+, iOS 10.0 - 10.2 only
	// Check attachment across shadow DOM boundaries when possible (gh-3504)
	// Support: iOS 10.0-10.2 only
	// Early iOS 10 versions support `attachShadow` but not `getRootNode`,
	// leading to errors. We need to check for `getRootNode`.
	if ( documentElement.getRootNode ) {
		isAttached = function( elem ) {
			return jQuery.contains( elem.ownerDocument, elem ) ||
				elem.getRootNode( composed ) === elem.ownerDocument;
		};
	}
var isHiddenWithinTree = function( elem, el ) {

		// isHiddenWithinTree might be called from jQuery#filter function;
		// in that case, element will be second argument
		elem = el || elem;

		// Inline style trumps all
		return elem.style.display === "none" ||
			elem.style.display === "" &&

			// Otherwise, check computed style
			// Support: Firefox <=43 - 45
			// Disconnected elements can have computed display: none, so first confirm that elem is
			// in the document.
			isAttached( elem ) &&

			jQuery.css( elem, "display" ) === "none";
	};



function adjustCSS( elem, prop, valueParts, tween ) {
	var adjusted, scale,
		maxIterations = 20,
		currentValue = tween ?
			function() {
				return tween.cur();
			} :
			function() {
				return jQuery.css( elem, prop, "" );
			},
		initial = currentValue(),
		unit = valueParts && valueParts[ 3 ] || ( jQuery.cssNumber[ prop ] ? "" : "px" ),

		// Starting value computation is required for potential unit mismatches
		initialInUnit = elem.nodeType &&
			( jQuery.cssNumber[ prop ] || unit !== "px" && +initial ) &&
			rcssNum.exec( jQuery.css( elem, prop ) );

	if ( initialInUnit && initialInUnit[ 3 ] !== unit ) {

		// Support: Firefox <=54
		// Halve the iteration target value to prevent interference from CSS upper bounds (gh-2144)
		initial = initial / 2;

		// Trust units reported by jQuery.css
		unit = unit || initialInUnit[ 3 ];

		// Iteratively approximate from a nonzero starting point
		initialInUnit = +initial || 1;

		while ( maxIterations-- ) {

			// Evaluate and update our best guess (doubling guesses that zero out).
			// Finish if the scale equals or crosses 1 (making the old*new product non-positive).
			jQuery.style( elem, prop, initialInUnit + unit );
			if ( ( 1 - scale ) * ( 1 - ( scale = currentValue() / initial || 0.5 ) ) <= 0 ) {
				maxIterations = 0;
			}
			initialInUnit = initialInUnit / scale;

		}

		initialInUnit = initialInUnit * 2;
		jQuery.style( elem, prop, initialInUnit + unit );

		// Make sure we update the tween properties later on
		valueParts = valueParts || [];
	}

	if ( valueParts ) {
		initialInUnit = +initialInUnit || +initial || 0;

		// Apply relative offset (+=/-=) if specified
		adjusted = valueParts[ 1 ] ?
			initialInUnit + ( valueParts[ 1 ] + 1 ) * valueParts[ 2 ] :
			+valueParts[ 2 ];
		if ( tween ) {
			tween.unit = unit;
			tween.start = initialInUnit;
			tween.end = adjusted;
		}
	}
	return adjusted;
}


var defaultDisplayMap = {};

function getDefaultDisplay( elem ) {
	var temp,
		doc = elem.ownerDocument,
		nodeName = elem.nodeName,
		display = defaultDisplayMap[ nodeName ];

	if ( display ) {
		return display;
	}

	temp = doc.body.appendChild( doc.createElement( nodeName ) );
	display = jQuery.css( temp, "display" );

	temp.parentNode.removeChild( temp );

	if ( display === "none" ) {
		display = "block";
	}
	defaultDisplayMap[ nodeName ] = display;

	return display;
}

function showHide( elements, show ) {
	var display, elem,
		values = [],
		index = 0,
		length = elements.length;

	// Determine new display value for elements that need to change
	for ( ; index < length; index++ ) {
		elem = elements[ index ];
		if ( !elem.style ) {
			continue;
		}

		display = elem.style.display;
		if ( show ) {

			// Since we force visibility upon cascade-hidden elements, an immediate (and slow)
			// check is required in this first loop unless we have a nonempty display value (either
			// inline or about-to-be-restored)
			if ( display === "none" ) {
				values[ index ] = dataPriv.get( elem, "display" ) || null;
				if ( !values[ index ] ) {
					elem.style.display = "";
				}
			}
			if ( elem.style.display === "" && isHiddenWithinTree( elem ) ) {
				values[ index ] = getDefaultDisplay( elem );
			}
		} else {
			if ( display !== "none" ) {
				values[ index ] = "none";

				// Remember what we're overwriting
				dataPriv.set( elem, "display", display );
			}
		}
	}

	// Set the display of the elements in a second loop to avoid constant reflow
	for ( index = 0; index < length; index++ ) {
		if ( values[ index ] != null ) {
			elements[ index ].style.display = values[ index ];
		}
	}

	return elements;
}

jQuery.fn.extend( {
	show: function() {
		return showHide( this, true );
	},
	hide: function() {
		return showHide( this );
	},
	toggle: function( state ) {
		if ( typeof state === "boolean" ) {
			return state ? this.show() : this.hide();
		}

		return this.each( function() {
			if ( isHiddenWithinTree( this ) ) {
				jQuery( this ).show();
			} else {
				jQuery( this ).hide();
			}
		} );
	}
} );
var rcheckableType = ( /^(?:checkbox|radio)$/i );

var rtagName = ( /<([a-z][^\/\0>\x20\t\r\n\f]*)/i );

var rscriptType = ( /^$|^module$|\/(?:java|ecma)script/i );



( function() {
	var fragment = document.createDocumentFragment(),
		div = fragment.appendChild( document.createElement( "div" ) ),
		input = document.createElement( "input" );

	// Support: Android 4.0 - 4.3 only
	// Check state lost if the name is set (trac-11217)
	// Support: Windows Web Apps (WWA)
	// `name` and `type` must use .setAttribute for WWA (trac-14901)
	input.setAttribute( "type", "radio" );
	input.setAttribute( "checked", "checked" );
	input.setAttribute( "name", "t" );

	div.appendChild( input );

	// Support: Android <=4.1 only
	// Older WebKit doesn't clone checked state correctly in fragments
	support.checkClone = div.cloneNode( true ).cloneNode( true ).lastChild.checked;

	// Support: IE <=11 only
	// Make sure textarea (and checkbox) defaultValue is properly cloned
	div.innerHTML = "<textarea>x</textarea>";
	support.noCloneChecked = !!div.cloneNode( true ).lastChild.defaultValue;

	// Support: IE <=9 only
	// IE <=9 replaces <option> tags with their contents when inserted outside of
	// the select element.
	div.innerHTML = "<option></option>";
	support.option = !!div.lastChild;
} )();


// We have to close these tags to support XHTML (trac-13200)
var wrapMap = {

	// XHTML parsers do not magically insert elements in the
	// same way that tag soup parsers do. So we cannot shorten
	// this by omitting <tbody> or other required elements.
	thead: [ 1, "<table>", "</table>" ],
	col: [ 2, "<table><colgroup>", "</colgroup></table>" ],
	tr: [ 2, "<table><tbody>", "</tbody></table>" ],
	td: [ 3, "<table><tbody><tr>", "</tr></tbody></table>" ],

	_default: [ 0, "", "" ]
};

wrapMap.tbody = wrapMap.tfoot = wrapMap.colgroup = wrapMap.caption = wrapMap.thead;
wrapMap.th = wrapMap.td;

// Support: IE <=9 only
if ( !support.option ) {
	wrapMap.optgroup = wrapMap.option = [ 1, "<select multiple='multiple'>", "</select>" ];
}


function getAll( context, tag ) {

	// Support: IE <=9 - 11 only
	// Use typeof to avoid zero-argument method invocation on host objects (trac-15151)
	var ret;

	if ( typeof context.getElementsByTagName !== "undefined" ) {
		ret = context.getElementsByTagName( tag || "*" );

	} else if ( typeof context.querySelectorAll !== "undefined" ) {
		ret = context.querySelectorAll( tag || "*" );

	} else {
		ret = [];
	}

	if ( tag === undefined || tag && nodeName( context, tag ) ) {
		return jQuery.merge( [ context ], ret );
	}

	return ret;
}


// Mark scripts as having already been evaluated
function setGlobalEval( elems, refElements ) {
	var i = 0,
		l = elems.length;

	for ( ; i < l; i++ ) {
		dataPriv.set(
			elems[ i ],
			"globalEval",
			!refElements || dataPriv.get( refElements[ i ], "globalEval" )
		);
	}
}


var rhtml = /<|&#?\w+;/;

function buildFragment( elems, context, scripts, selection, ignored ) {
	var elem, tmp, tag, wrap, attached, j,
		fragment = context.createDocumentFragment(),
		nodes = [],
		i = 0,
		l = elems.length;

	for ( ; i < l; i++ ) {
		elem = elems[ i ];

		if ( elem || elem === 0 ) {

			// Add nodes directly
			if ( toType( elem ) === "object" ) {

				// Support: Android <=4.0 only, PhantomJS 1 only
				// push.apply(_, arraylike) throws on ancient WebKit
				jQuery.merge( nodes, elem.nodeType ? [ elem ] : elem );

			// Convert non-html into a text node
			} else if ( !rhtml.test( elem ) ) {
				nodes.push( context.createTextNode( elem ) );

			// Convert html into DOM nodes
			} else {
				tmp = tmp || fragment.appendChild( context.createElement( "div" ) );

				// Deserialize a standard representation
				tag = ( rtagName.exec( elem ) || [ "", "" ] )[ 1 ].toLowerCase();
				wrap = wrapMap[ tag ] || wrapMap._default;
				tmp.innerHTML = wrap[ 1 ] + jQuery.htmlPrefilter( elem ) + wrap[ 2 ];

				// Descend through wrappers to the right content
				j = wrap[ 0 ];
				while ( j-- ) {
					tmp = tmp.lastChild;
				}

				// Support: Android <=4.0 only, PhantomJS 1 only
				// push.apply(_, arraylike) throws on ancient WebKit
				jQuery.merge( nodes, tmp.childNodes );

				// Remember the top-level container
				tmp = fragment.firstChild;

				// Ensure the created nodes are orphaned (trac-12392)
				tmp.textContent = "";
			}
		}
	}

	// Remove wrapper from fragment
	fragment.textContent = "";

	i = 0;
	while ( ( elem = nodes[ i++ ] ) ) {

		// Skip elements already in the context collection (trac-4087)
		if ( selection && jQuery.inArray( elem, selection ) > -1 ) {
			if ( ignored ) {
				ignored.push( elem );
			}
			continue;
		}

		attached = isAttached( elem );

		// Append to fragment
		tmp = getAll( fragment.appendChild( elem ), "script" );

		// Preserve script evaluation history
		if ( attached ) {
			setGlobalEval( tmp );
		}

		// Capture executables
		if ( scripts ) {
			j = 0;
			while ( ( elem = tmp[ j++ ] ) ) {
				if ( rscriptType.test( elem.type || "" ) ) {
					scripts.push( elem );
				}
			}
		}
	}

	return fragment;
}


var rtypenamespace = /^([^.]*)(?:\.(.+)|)/;

function returnTrue() {
	return true;
}

function returnFalse() {
	return false;
}

function on( elem, types, selector, data, fn, one ) {
	var origFn, type;

	// Types can be a map of types/handlers
	if ( typeof types === "object" ) {

		// ( types-Object, selector, data )
		if ( typeof selector !== "string" ) {

			// ( types-Object, data )
			data = data || selector;
			selector = undefined;
		}
		for ( type in types ) {
			on( elem, type, selector, data, types[ type ], one );
		}
		return elem;
	}

	if ( data == null && fn == null ) {

		// ( types, fn )
		fn = selector;
		data = selector = undefined;
	} else if ( fn == null ) {
		if ( typeof selector === "string" ) {

			// ( types, selector, fn )
			fn = data;
			data = undefined;
		} else {

			// ( types, data, fn )
			fn = data;
			data = selector;
			selector = undefined;
		}
	}
	if ( fn === false ) {
		fn = returnFalse;
	} else if ( !fn ) {
		return elem;
	}

	if ( one === 1 ) {
		origFn = fn;
		fn = function( event ) {

			// Can use an empty set, since event contains the info
			jQuery().off( event );
			return origFn.apply( this, arguments );
		};

		// Use same guid so caller can remove using origFn
		fn.guid = origFn.guid || ( origFn.guid = jQuery.guid++ );
	}
	return elem.each( function() {
		jQuery.event.add( this, types, fn, data, selector );
	} );
}

/*
 * Helper functions for managing events -- not part of the public interface.
 * Props to Dean Edwards' addEvent library for many of the ideas.
 */
jQuery.event = {

	global: {},

	add: function( elem, types, handler, data, selector ) {

		var handleObjIn, eventHandle, tmp,
			events, t, handleObj,
			special, handlers, type, namespaces, origType,
			elemData = dataPriv.get( elem );

		// Only attach events to objects that accept data
		if ( !acceptData( elem ) ) {
			return;
		}

		// Caller can pass in an object of custom data in lieu of the handler
		if ( handler.handler ) {
			handleObjIn = handler;
			handler = handleObjIn.handler;
			selector = handleObjIn.selector;
		}

		// Ensure that invalid selectors throw exceptions at attach time
		// Evaluate against documentElement in case elem is a non-element node (e.g., document)
		if ( selector ) {
			jQuery.find.matchesSelector( documentElement, selector );
		}

		// Make sure that the handler has a unique ID, used to find/remove it later
		if ( !handler.guid ) {
			handler.guid = jQuery.guid++;
		}

		// Init the element's event structure and main handler, if this is the first
		if ( !( events = elemData.events ) ) {
			events = elemData.events = Object.create( null );
		}
		if ( !( eventHandle = elemData.handle ) ) {
			eventHandle = elemData.handle = function( e ) {

				// Discard the second event of a jQuery.event.trigger() and
				// when an event is called after a page has unloaded
				return typeof jQuery !== "undefined" && jQuery.event.triggered !== e.type ?
					jQuery.event.dispatch.apply( elem, arguments ) : undefined;
			};
		}

		// Handle multiple events separated by a space
		types = ( types || "" ).match( rnothtmlwhite ) || [ "" ];
		t = types.length;
		while ( t-- ) {
			tmp = rtypenamespace.exec( types[ t ] ) || [];
			type = origType = tmp[ 1 ];
			namespaces = ( tmp[ 2 ] || "" ).split( "." ).sort();

			// There *must* be a type, no attaching namespace-only handlers
			if ( !type ) {
				continue;
			}

			// If event changes its type, use the special event handlers for the changed type
			special = jQuery.event.special[ type ] || {};

			// If selector defined, determine special event api type, otherwise given type
			type = ( selector ? special.delegateType : special.bindType ) || type;

			// Update special based on newly reset type
			special = jQuery.event.special[ type ] || {};

			// handleObj is passed to all event handlers
			handleObj = jQuery.extend( {
				type: type,
				origType: origType,
				data: data,
				handler: handler,
				guid: handler.guid,
				selector: selector,
				needsContext: selector && jQuery.expr.match.needsContext.test( selector ),
				namespace: namespaces.join( "." )
			}, handleObjIn );

			// Init the event handler queue if we're the first
			if ( !( handlers = events[ type ] ) ) {
				handlers = events[ type ] = [];
				handlers.delegateCount = 0;

				// Only use addEventListener if the special events handler returns false
				if ( !special.setup ||
					special.setup.call( elem, data, namespaces, eventHandle ) === false ) {

					if ( elem.addEventListener ) {
						elem.addEventListener( type, eventHandle );
					}
				}
			}

			if ( special.add ) {
				special.add.call( elem, handleObj );

				if ( !handleObj.handler.guid ) {
					handleObj.handler.guid = handler.guid;
				}
			}

			// Add to the element's handler list, delegates in front
			if ( selector ) {
				handlers.splice( handlers.delegateCount++, 0, handleObj );
			} else {
				handlers.push( handleObj );
			}

			// Keep track of which events have ever been used, for event optimization
			jQuery.event.global[ type ] = true;
		}

	},

	// Detach an event or set of events from an element
	remove: function( elem, types, handler, selector, mappedTypes ) {

		var j, origCount, tmp,
			events, t, handleObj,
			special, handlers, type, namespaces, origType,
			elemData = dataPriv.hasData( elem ) && dataPriv.get( elem );

		if ( !elemData || !( events = elemData.events ) ) {
			return;
		}

		// Once for each type.namespace in types; type may be omitted
		types = ( types || "" ).match( rnothtmlwhite ) || [ "" ];
		t = types.length;
		while ( t-- ) {
			tmp = rtypenamespace.exec( types[ t ] ) || [];
			type = origType = tmp[ 1 ];
			namespaces = ( tmp[ 2 ] || "" ).split( "." ).sort();

			// Unbind all events (on this namespace, if provided) for the element
			if ( !type ) {
				for ( type in events ) {
					jQuery.event.remove( elem, type + types[ t ], handler, selector, true );
				}
				continue;
			}

			special = jQuery.event.special[ type ] || {};
			type = ( selector ? special.delegateType : special.bindType ) || type;
			handlers = events[ type ] || [];
			tmp = tmp[ 2 ] &&
				new RegExp( "(^|\\.)" + namespaces.join( "\\.(?:.*\\.|)" ) + "(\\.|$)" );

			// Remove matching events
			origCount = j = handlers.length;
			while ( j-- ) {
				handleObj = handlers[ j ];

				if ( ( mappedTypes || origType === handleObj.origType ) &&
					( !handler || handler.guid === handleObj.guid ) &&
					( !tmp || tmp.test( handleObj.namespace ) ) &&
					( !selector || selector === handleObj.selector ||
						selector === "**" && handleObj.selector ) ) {
					handlers.splice( j, 1 );

					if ( handleObj.selector ) {
						handlers.delegateCount--;
					}
					if ( special.remove ) {
						special.remove.call( elem, handleObj );
					}
				}
			}

			// Remove generic event handler if we removed something and no more handlers exist
			// (avoids potential for endless recursion during removal of special event handlers)
			if ( origCount && !handlers.length ) {
				if ( !special.teardown ||
					special.teardown.call( elem, namespaces, elemData.handle ) === false ) {

					jQuery.removeEvent( elem, type, elemData.handle );
				}

				delete events[ type ];
			}
		}

		// Remove data and the expando if it's no longer used
		if ( jQuery.isEmptyObject( events ) ) {
			dataPriv.remove( elem, "handle events" );
		}
	},

	dispatch: function( nativeEvent ) {

		var i, j, ret, matched, handleObj, handlerQueue,
			args = new Array( arguments.length ),

			// Make a writable jQuery.Event from the native event object
			event = jQuery.event.fix( nativeEvent ),

			handlers = (
				dataPriv.get( this, "events" ) || Object.create( null )
			)[ event.type ] || [],
			special = jQuery.event.special[ event.type ] || {};

		// Use the fix-ed jQuery.Event rather than the (read-only) native event
		args[ 0 ] = event;

		for ( i = 1; i < arguments.length; i++ ) {
			args[ i ] = arguments[ i ];
		}

		event.delegateTarget = this;

		// Call the preDispatch hook for the mapped type, and let it bail if desired
		if ( special.preDispatch && special.preDispatch.call( this, event ) === false ) {
			return;
		}

		// Determine handlers
		handlerQueue = jQuery.event.handlers.call( this, event, handlers );

		// Run delegates first; they may want to stop propagation beneath us
		i = 0;
		while ( ( matched = handlerQueue[ i++ ] ) && !event.isPropagationStopped() ) {
			event.currentTarget = matched.elem;

			j = 0;
			while ( ( handleObj = matched.handlers[ j++ ] ) &&
				!event.isImmediatePropagationStopped() ) {

				// If the event is namespaced, then each handler is only invoked if it is
				// specially universal or its namespaces are a superset of the event's.
				if ( !event.rnamespace || handleObj.namespace === false ||
					event.rnamespace.test( handleObj.namespace ) ) {

					event.handleObj = handleObj;
					event.data = handleObj.data;

					ret = ( ( jQuery.event.special[ handleObj.origType ] || {} ).handle ||
						handleObj.handler ).apply( matched.elem, args );

					if ( ret !== undefined ) {
						if ( ( event.result = ret ) === false ) {
							event.preventDefault();
							event.stopPropagation();
						}
					}
				}
			}
		}

		// Call the postDispatch hook for the mapped type
		if ( special.postDispatch ) {
			special.postDispatch.call( this, event );
		}

		return event.result;
	},

	handlers: function( event, handlers ) {
		var i, handleObj, sel, matchedHandlers, matchedSelectors,
			handlerQueue = [],
			delegateCount = handlers.delegateCount,
			cur = event.target;

		// Find delegate handlers
		if ( delegateCount &&

			// Support: IE <=9
			// Black-hole SVG <use> instance trees (trac-13180)
			cur.nodeType &&

			// Support: Firefox <=42
			// Suppress spec-violating clicks indicating a non-primary pointer button (trac-3861)
			// https://www.w3.org/TR/DOM-Level-3-Events/#event-type-click
			// Support: IE 11 only
			// ...but not arrow key "clicks" of radio inputs, which can have `button` -1 (gh-2343)
			!( event.type === "click" && event.button >= 1 ) ) {

			for ( ; cur !== this; cur = cur.parentNode || this ) {

				// Don't check non-elements (trac-13208)
				// Don't process clicks on disabled elements (trac-6911, trac-8165, trac-11382, trac-11764)
				if ( cur.nodeType === 1 && !( event.type === "click" && cur.disabled === true ) ) {
					matchedHandlers = [];
					matchedSelectors = {};
					for ( i = 0; i < delegateCount; i++ ) {
						handleObj = handlers[ i ];

						// Don't conflict with Object.prototype properties (trac-13203)
						sel = handleObj.selector + " ";

						if ( matchedSelectors[ sel ] === undefined ) {
							matchedSelectors[ sel ] = handleObj.needsContext ?
								jQuery( sel, this ).index( cur ) > -1 :
								jQuery.find( sel, this, null, [ cur ] ).length;
						}
						if ( matchedSelectors[ sel ] ) {
							matchedHandlers.push( handleObj );
						}
					}
					if ( matchedHandlers.length ) {
						handlerQueue.push( { elem: cur, handlers: matchedHandlers } );
					}
				}
			}
		}

		// Add the remaining (directly-bound) handlers
		cur = this;
		if ( delegateCount < handlers.length ) {
			handlerQueue.push( { elem: cur, handlers: handlers.slice( delegateCount ) } );
		}

		return handlerQueue;
	},

	addProp: function( name, hook ) {
		Object.defineProperty( jQuery.Event.prototype, name, {
			enumerable: true,
			configurable: true,

			get: isFunction( hook ) ?
				function() {
					if ( this.originalEvent ) {
						return hook( this.originalEvent );
					}
				} :
				function() {
					if ( this.originalEvent ) {
						return this.originalEvent[ name ];
					}
				},

			set: function( value ) {
				Object.defineProperty( this, name, {
					enumerable: true,
					configurable: true,
					writable: true,
					value: value
				} );
			}
		} );
	},

	fix: function( originalEvent ) {
		return originalEvent[ jQuery.expando ] ?
			originalEvent :
			new jQuery.Event( originalEvent );
	},

	special: {
		load: {

			// Prevent triggered image.load events from bubbling to window.load
			noBubble: true
		},
		click: {

			// Utilize native event to ensure correct state for checkable inputs
			setup: function( data ) {

				// For mutual compressibility with _default, replace `this` access with a local var.
				// `|| data` is dead code meant only to preserve the variable through minification.
				var el = this || data;

				// Claim the first handler
				if ( rcheckableType.test( el.type ) &&
					el.click && nodeName( el, "input" ) ) {

					// dataPriv.set( el, "click", ... )
					leverageNative( el, "click", true );
				}

				// Return false to allow normal processing in the caller
				return false;
			},
			trigger: function( data ) {

				// For mutual compressibility with _default, replace `this` access with a local var.
				// `|| data` is dead code meant only to preserve the variable through minification.
				var el = this || data;

				// Force setup before triggering a click
				if ( rcheckableType.test( el.type ) &&
					el.click && nodeName( el, "input" ) ) {

					leverageNative( el, "click" );
				}

				// Return non-false to allow normal event-path propagation
				return true;
			},

			// For cross-browser consistency, suppress native .click() on links
			// Also prevent it if we're currently inside a leveraged native-event stack
			_default: function( event ) {
				var target = event.target;
				return rcheckableType.test( target.type ) &&
					target.click && nodeName( target, "input" ) &&
					dataPriv.get( target, "click" ) ||
					nodeName( target, "a" );
			}
		},

		beforeunload: {
			postDispatch: function( event ) {

				// Support: Firefox 20+
				// Firefox doesn't alert if the returnValue field is not set.
				if ( event.result !== undefined && event.originalEvent ) {
					event.originalEvent.returnValue = event.result;
				}
			}
		}
	}
};

// Ensure the presence of an event listener that handles manually-triggered
// synthetic events by interrupting progress until reinvoked in response to
// *native* events that it fires directly, ensuring that state changes have
// already occurred before other listeners are invoked.
function leverageNative( el, type, isSetup ) {

	// Missing `isSetup` indicates a trigger call, which must force setup through jQuery.event.add
	if ( !isSetup ) {
		if ( dataPriv.get( el, type ) === undefined ) {
			jQuery.event.add( el, type, returnTrue );
		}
		return;
	}

	// Register the controller as a special universal handler for all event namespaces
	dataPriv.set( el, type, false );
	jQuery.event.add( el, type, {
		namespace: false,
		handler: function( event ) {
			var result,
				saved = dataPriv.get( this, type );

			if ( ( event.isTrigger & 1 ) && this[ type ] ) {

				// Interrupt processing of the outer synthetic .trigger()ed event
				if ( !saved ) {

					// Store arguments for use when handling the inner native event
					// There will always be at least one argument (an event object), so this array
					// will not be confused with a leftover capture object.
					saved = slice.call( arguments );
					dataPriv.set( this, type, saved );

					// Trigger the native event and capture its result
					this[ type ]();
					result = dataPriv.get( this, type );
					dataPriv.set( this, type, false );

					if ( saved !== result ) {

						// Cancel the outer synthetic event
						event.stopImmediatePropagation();
						event.preventDefault();

						return result;
					}

				// If this is an inner synthetic event for an event with a bubbling surrogate
				// (focus or blur), assume that the surrogate already propagated from triggering
				// the native event and prevent that from happening again here.
				// This technically gets the ordering wrong w.r.t. to `.trigger()` (in which the
				// bubbling surrogate propagates *after* the non-bubbling base), but that seems
				// less bad than duplication.
				} else if ( ( jQuery.event.special[ type ] || {} ).delegateType ) {
					event.stopPropagation();
				}

			// If this is a native event triggered above, everything is now in order
			// Fire an inner synthetic event with the original arguments
			} else if ( saved ) {

				// ...and capture the result
				dataPriv.set( this, type, jQuery.event.trigger(
					saved[ 0 ],
					saved.slice( 1 ),
					this
				) );

				// Abort handling of the native event by all jQuery handlers while allowing
				// native handlers on the same element to run. On target, this is achieved
				// by stopping immediate propagation just on the jQuery event. However,
				// the native event is re-wrapped by a jQuery one on each level of the
				// propagation so the only way to stop it for jQuery is to stop it for
				// everyone via native `stopPropagation()`. This is not a problem for
				// focus/blur which don't bubble, but it does also stop click on checkboxes
				// and radios. We accept this limitation.
				event.stopPropagation();
				event.isImmediatePropagationStopped = returnTrue;
			}
		}
	} );
}

jQuery.removeEvent = function( elem, type, handle ) {

	// This "if" is needed for plain objects
	if ( elem.removeEventListener ) {
		elem.removeEventListener( type, handle );
	}
};

jQuery.Event = function( src, props ) {

	// Allow instantiation without the 'new' keyword
	if ( !( this instanceof jQuery.Event ) ) {
		return new jQuery.Event( src, props );
	}

	// Event object
	if ( src && src.type ) {
		this.originalEvent = src;
		this.type = src.type;

		// Events bubbling up the document may have been marked as prevented
		// by a handler lower down the tree; reflect the correct value.
		this.isDefaultPrevented = src.defaultPrevented ||
				src.defaultPrevented === undefined &&

				// Support: Android <=2.3 only
				src.returnValue === false ?
			returnTrue :
			returnFalse;

		// Create target properties
		// Support: Safari <=6 - 7 only
		// Target should not be a text node (trac-504, trac-13143)
		this.target = ( src.target && src.target.nodeType === 3 ) ?
			src.target.parentNode :
			src.target;

		this.currentTarget = src.currentTarget;
		this.relatedTarget = src.relatedTarget;

	// Event type
	} else {
		this.type = src;
	}

	// Put explicitly provided properties onto the event object
	if ( props ) {
		jQuery.extend( this, props );
	}

	// Create a timestamp if incoming event doesn't have one
	this.timeStamp = src && src.timeStamp || Date.now();

	// Mark it as fixed
	this[ jQuery.expando ] = true;
};

// jQuery.Event is based on DOM3 Events as specified by the ECMAScript Language Binding
// https://www.w3.org/TR/2003/WD-DOM-Level-3-Events-20030331/ecma-script-binding.html
jQuery.Event.prototype = {
	constructor: jQuery.Event,
	isDefaultPrevented: returnFalse,
	isPropagationStopped: returnFalse,
	isImmediatePropagationStopped: returnFalse,
	isSimulated: false,

	preventDefault: function() {
		var e = this.originalEvent;

		this.isDefaultPrevented = returnTrue;

		if ( e && !this.isSimulated ) {
			e.preventDefault();
		}
	},
	stopPropagation: function() {
		var e = this.originalEvent;

		this.isPropagationStopped = returnTrue;

		if ( e && !this.isSimulated ) {
			e.stopPropagation();
		}
	},
	stopImmediatePropagation: function() {
		var e = this.originalEvent;

		this.isImmediatePropagationStopped = returnTrue;

		if ( e && !this.isSimulated ) {
			e.stopImmediatePropagation();
		}

		this.stopPropagation();
	}
};

// Includes all common event props including KeyEvent and MouseEvent specific props
jQuery.each( {
	altKey: true,
	bubbles: true,
	cancelable: true,
	changedTouches: true,
	ctrlKey: true,
	detail: true,
	eventPhase: true,
	metaKey: true,
	pageX: true,
	pageY: true,
	shiftKey: true,
	view: true,
	"char": true,
	code: true,
	charCode: true,
	key: true,
	keyCode: true,
	button: true,
	buttons: true,
	clientX: true,
	clientY: true,
	offsetX: true,
	offsetY: true,
	pointerId: true,
	pointerType: true,
	screenX: true,
	screenY: true,
	targetTouches: true,
	toElement: true,
	touches: true,
	which: true
}, jQuery.event.addProp );

jQuery.each( { focus: "focusin", blur: "focusout" }, function( type, delegateType ) {

	function focusMappedHandler( nativeEvent ) {
		if ( document.documentMode ) {

			// Support: IE 11+
			// Attach a single focusin/focusout handler on the document while someone wants
			// focus/blur. This is because the former are synchronous in IE while the latter
			// are async. In other browsers, all those handlers are invoked synchronously.

			// `handle` from private data would already wrap the event, but we need
			// to change the `type` here.
			var handle = dataPriv.get( this, "handle" ),
				event = jQuery.event.fix( nativeEvent );
			event.type = nativeEvent.type === "focusin" ? "focus" : "blur";
			event.isSimulated = true;

			// First, handle focusin/focusout
			handle( nativeEvent );

			// ...then, handle focus/blur
			//
			// focus/blur don't bubble while focusin/focusout do; simulate the former by only
			// invoking the handler at the lower level.
			if ( event.target === event.currentTarget ) {

				// The setup part calls `leverageNative`, which, in turn, calls
				// `jQuery.event.add`, so event handle will already have been set
				// by this point.
				handle( event );
			}
		} else {

			// For non-IE browsers, attach a single capturing handler on the document
			// while someone wants focusin/focusout.
			jQuery.event.simulate( delegateType, nativeEvent.target,
				jQuery.event.fix( nativeEvent ) );
		}
	}

	jQuery.event.special[ type ] = {

		// Utilize native event if possible so blur/focus sequence is correct
		setup: function() {

			var attaches;

			// Claim the first handler
			// dataPriv.set( this, "focus", ... )
			// dataPriv.set( this, "blur", ... )
			leverageNative( this, type, true );

			if ( document.documentMode ) {

				// Support: IE 9 - 11+
				// We use the same native handler for focusin & focus (and focusout & blur)
				// so we need to coordinate setup & teardown parts between those events.
				// Use `delegateType` as the key as `type` is already used by `leverageNative`.
				attaches = dataPriv.get( this, delegateType );
				if ( !attaches ) {
					this.addEventListener( delegateType, focusMappedHandler );
				}
				dataPriv.set( this, delegateType, ( attaches || 0 ) + 1 );
			} else {

				// Return false to allow normal processing in the caller
				return false;
			}
		},
		trigger: function() {

			// Force setup before trigger
			leverageNative( this, type );

			// Return non-false to allow normal event-path propagation
			return true;
		},

		teardown: function() {
			var attaches;

			if ( document.documentMode ) {
				attaches = dataPriv.get( this, delegateType ) - 1;
				if ( !attaches ) {
					this.removeEventListener( delegateType, focusMappedHandler );
					dataPriv.remove( this, delegateType );
				} else {
					dataPriv.set( this, delegateType, attaches );
				}
			} else {

				// Return false to indicate standard teardown should be applied
				return false;
			}
		},

		// Suppress native focus or blur if we're currently inside
		// a leveraged native-event stack
		_default: function( event ) {
			return dataPriv.get( event.target, type );
		},

		delegateType: delegateType
	};

	// Support: Firefox <=44
	// Firefox doesn't have focus(in | out) events
	// Related ticket - https://bugzilla.mozilla.org/show_bug.cgi?id=687787
	//
	// Support: Chrome <=48 - 49, Safari <=9.0 - 9.1
	// focus(in | out) events fire after focus & blur events,
	// which is spec violation - http://www.w3.org/TR/DOM-Level-3-Events/#events-focusevent-event-order
	// Related ticket - https://bugs.chromium.org/p/chromium/issues/detail?id=449857
	//
	// Support: IE 9 - 11+
	// To preserve relative focusin/focus & focusout/blur event order guaranteed on the 3.x branch,
	// attach a single handler for both events in IE.
	jQuery.event.special[ delegateType ] = {
		setup: function() {

			// Handle: regular nodes (via `this.ownerDocument`), window
			// (via `this.document`) & document (via `this`).
			var doc = this.ownerDocument || this.document || this,
				dataHolder = document.documentMode ? this : doc,
				attaches = dataPriv.get( dataHolder, delegateType );

			// Support: IE 9 - 11+
			// We use the same native handler for focusin & focus (and focusout & blur)
			// so we need to coordinate setup & teardown parts between those events.
			// Use `delegateType` as the key as `type` is already used by `leverageNative`.
			if ( !attaches ) {
				if ( document.documentMode ) {
					this.addEventListener( delegateType, focusMappedHandler );
				} else {
					doc.addEventListener( type, focusMappedHandler, true );
				}
			}
			dataPriv.set( dataHolder, delegateType, ( attaches || 0 ) + 1 );
		},
		teardown: function() {
			var doc = this.ownerDocument || this.document || this,
				dataHolder = document.documentMode ? this : doc,
				attaches = dataPriv.get( dataHolder, delegateType ) - 1;

			if ( !attaches ) {
				if ( document.documentMode ) {
					this.removeEventListener( delegateType, focusMappedHandler );
				} else {
					doc.removeEventListener( type, focusMappedHandler, true );
				}
				dataPriv.remove( dataHolder, delegateType );
			} else {
				dataPriv.set( dataHolder, delegateType, attaches );
			}
		}
	};
} );

// Create mouseenter/leave events using mouseover/out and event-time checks
// so that event delegation works in jQuery.
// Do the same for pointerenter/pointerleave and pointerover/pointerout
//
// Support: Safari 7 only
// Safari sends mouseenter too often; see:
// https://bugs.chromium.org/p/chromium/issues/detail?id=470258
// for the description of the bug (it existed in older Chrome versions as well).
jQuery.each( {
	mouseenter: "mouseover",
	mouseleave: "mouseout",
	pointerenter: "pointerover",
	pointerleave: "pointerout"
}, function( orig, fix ) {
	jQuery.event.special[ orig ] = {
		delegateType: fix,
		bindType: fix,

		handle: function( event ) {
			var ret,
				target = this,
				related = event.relatedTarget,
				handleObj = event.handleObj;

			// For mouseenter/leave call the handler if related is outside the target.
			// NB: No relatedTarget if the mouse left/entered the browser window
			if ( !related || ( related !== target && !jQuery.contains( target, related ) ) ) {
				event.type = handleObj.origType;
				ret = handleObj.handler.apply( this, arguments );
				event.type = fix;
			}
			return ret;
		}
	};
} );

jQuery.fn.extend( {

	on: function( types, selector, data, fn ) {
		return on( this, types, selector, data, fn );
	},
	one: function( types, selector, data, fn ) {
		return on( this, types, selector, data, fn, 1 );
	},
	off: function( types, selector, fn ) {
		var handleObj, type;
		if ( types && types.preventDefault && types.handleObj ) {

			// ( event )  dispatched jQuery.Event
			handleObj = types.handleObj;
			jQuery( types.delegateTarget ).off(
				handleObj.namespace ?
					handleObj.origType + "." + handleObj.namespace :
					handleObj.origType,
				handleObj.selector,
				handleObj.handler
			);
			return this;
		}
		if ( typeof types === "object" ) {

			// ( types-object [, selector] )
			for ( type in types ) {
				this.off( type, selector, types[ type ] );
			}
			return this;
		}
		if ( selector === false || typeof selector === "function" ) {

			// ( types [, fn] )
			fn = selector;
			selector = undefined;
		}
		if ( fn === false ) {
			fn = returnFalse;
		}
		return this.each( function() {
			jQuery.event.remove( this, types, fn, selector );
		} );
	}
} );


var

	// Support: IE <=10 - 11, Edge 12 - 13 only
	// In IE/Edge using regex groups here causes severe slowdowns.
	// See https://connect.microsoft.com/IE/feedback/details/1736512/
	rnoInnerhtml = /<script|<style|<link/i,

	// checked="checked" or checked
	rchecked = /checked\s*(?:[^=]|=\s*.checked.)/i,

	rcleanScript = /^\s*<!\[CDATA\[|\]\]>\s*$/g;

// Prefer a tbody over its parent table for containing new rows
function manipulationTarget( elem, content ) {
	if ( nodeName( elem, "table" ) &&
		nodeName( content.nodeType !== 11 ? content : content.firstChild, "tr" ) ) {

		return jQuery( elem ).children( "tbody" )[ 0 ] || elem;
	}

	return elem;
}

// Replace/restore the type attribute of script elements for safe DOM manipulation
function disableScript( elem ) {
	elem.type = ( elem.getAttribute( "type" ) !== null ) + "/" + elem.type;
	return elem;
}
function restoreScript( elem ) {
	if ( ( elem.type || "" ).slice( 0, 5 ) === "true/" ) {
		elem.type = elem.type.slice( 5 );
	} else {
		elem.removeAttribute( "type" );
	}

	return elem;
}

function cloneCopyEvent( src, dest ) {
	var i, l, type, pdataOld, udataOld, udataCur, events;

	if ( dest.nodeType !== 1 ) {
		return;
	}

	// 1. Copy private data: events, handlers, etc.
	if ( dataPriv.hasData( src ) ) {
		pdataOld = dataPriv.get( src );
		events = pdataOld.events;

		if ( events ) {
			dataPriv.remove( dest, "handle events" );

			for ( type in events ) {
				for ( i = 0, l = events[ type ].length; i < l; i++ ) {
					jQuery.event.add( dest, type, events[ type ][ i ] );
				}
			}
		}
	}

	// 2. Copy user data
	if ( dataUser.hasData( src ) ) {
		udataOld = dataUser.access( src );
		udataCur = jQuery.extend( {}, udataOld );

		dataUser.set( dest, udataCur );
	}
}

// Fix IE bugs, see support tests
function fixInput( src, dest ) {
	var nodeName = dest.nodeName.toLowerCase();

	// Fails to persist the checked state of a cloned checkbox or radio button.
	if ( nodeName === "input" && rcheckableType.test( src.type ) ) {
		dest.checked = src.checked;

	// Fails to return the selected option to the default selected state when cloning options
	} else if ( nodeName === "input" || nodeName === "textarea" ) {
		dest.defaultValue = src.defaultValue;
	}
}

function domManip( collection, args, callback, ignored ) {

	// Flatten any nested arrays
	args = flat( args );

	var fragment, first, scripts, hasScripts, node, doc,
		i = 0,
		l = collection.length,
		iNoClone = l - 1,
		value = args[ 0 ],
		valueIsFunction = isFunction( value );

	// We can't cloneNode fragments that contain checked, in WebKit
	if ( valueIsFunction ||
			( l > 1 && typeof value === "string" &&
				!support.checkClone && rchecked.test( value ) ) ) {
		return collection.each( function( index ) {
			var self = collection.eq( index );
			if ( valueIsFunction ) {
				args[ 0 ] = value.call( this, index, self.html() );
			}
			domManip( self, args, callback, ignored );
		} );
	}

	if ( l ) {
		fragment = buildFragment( args, collection[ 0 ].ownerDocument, false, collection, ignored );
		first = fragment.firstChild;

		if ( fragment.childNodes.length === 1 ) {
			fragment = first;
		}

		// Require either new content or an interest in ignored elements to invoke the callback
		if ( first || ignored ) {
			scripts = jQuery.map( getAll( fragment, "script" ), disableScript );
			hasScripts = scripts.length;

			// Use the original fragment for the last item
			// instead of the first because it can end up
			// being emptied incorrectly in certain situations (trac-8070).
			for ( ; i < l; i++ ) {
				node = fragment;

				if ( i !== iNoClone ) {
					node = jQuery.clone( node, true, true );

					// Keep references to cloned scripts for later restoration
					if ( hasScripts ) {

						// Support: Android <=4.0 only, PhantomJS 1 only
						// push.apply(_, arraylike) throws on ancient WebKit
						jQuery.merge( scripts, getAll( node, "script" ) );
					}
				}

				callback.call( collection[ i ], node, i );
			}

			if ( hasScripts ) {
				doc = scripts[ scripts.length - 1 ].ownerDocument;

				// Re-enable scripts
				jQuery.map( scripts, restoreScript );

				// Evaluate executable scripts on first document insertion
				for ( i = 0; i < hasScripts; i++ ) {
					node = scripts[ i ];
					if ( rscriptType.test( node.type || "" ) &&
						!dataPriv.access( node, "globalEval" ) &&
						jQuery.contains( doc, node ) ) {

						if ( node.src && ( node.type || "" ).toLowerCase()  !== "module" ) {

							// Optional AJAX dependency, but won't run scripts if not present
							if ( jQuery._evalUrl && !node.noModule ) {
								jQuery._evalUrl( node.src, {
									nonce: node.nonce || node.getAttribute( "nonce" )
								}, doc );
							}
						} else {

							// Unwrap a CDATA section containing script contents. This shouldn't be
							// needed as in XML documents they're already not visible when
							// inspecting element contents and in HTML documents they have no
							// meaning but we're preserving that logic for backwards compatibility.
							// This will be removed completely in 4.0. See gh-4904.
							DOMEval( node.textContent.replace( rcleanScript, "" ), node, doc );
						}
					}
				}
			}
		}
	}

	return collection;
}

function remove( elem, selector, keepData ) {
	var node,
		nodes = selector ? jQuery.filter( selector, elem ) : elem,
		i = 0;

	for ( ; ( node = nodes[ i ] ) != null; i++ ) {
		if ( !keepData && node.nodeType === 1 ) {
			jQuery.cleanData( getAll( node ) );
		}

		if ( node.parentNode ) {
			if ( keepData && isAttached( node ) ) {
				setGlobalEval( getAll( node, "script" ) );
			}
			node.parentNode.removeChild( node );
		}
	}

	return elem;
}

jQuery.extend( {
	htmlPrefilter: function( html ) {
		return html;
	},

	clone: function( elem, dataAndEvents, deepDataAndEvents ) {
		var i, l, srcElements, destElements,
			clone = elem.cloneNode( true ),
			inPage = isAttached( elem );

		// Fix IE cloning issues
		if ( !support.noCloneChecked && ( elem.nodeType === 1 || elem.nodeType === 11 ) &&
				!jQuery.isXMLDoc( elem ) ) {

			// We eschew jQuery#find here for performance reasons:
			// https://jsperf.com/getall-vs-sizzle/2
			destElements = getAll( clone );
			srcElements = getAll( elem );

			for ( i = 0, l = srcElements.length; i < l; i++ ) {
				fixInput( srcElements[ i ], destElements[ i ] );
			}
		}

		// Copy the events from the original to the clone
		if ( dataAndEvents ) {
			if ( deepDataAndEvents ) {
				srcElements = srcElements || getAll( elem );
				destElements = destElements || getAll( clone );

				for ( i = 0, l = srcElements.length; i < l; i++ ) {
					cloneCopyEvent( srcElements[ i ], destElements[ i ] );
				}
			} else {
				cloneCopyEvent( elem, clone );
			}
		}

		// Preserve script evaluation history
		destElements = getAll( clone, "script" );
		if ( destElements.length > 0 ) {
			setGlobalEval( destElements, !inPage && getAll( elem, "script" ) );
		}

		// Return the cloned set
		return clone;
	},

	cleanData: function( elems ) {
		var data, elem, type,
			special = jQuery.event.special,
			i = 0;

		for ( ; ( elem = elems[ i ] ) !== undefined; i++ ) {
			if ( acceptData( elem ) ) {
				if ( ( data = elem[ dataPriv.expando ] ) ) {
					if ( data.events ) {
						for ( type in data.events ) {
							if ( special[ type ] ) {
								jQuery.event.remove( elem, type );

							// This is a shortcut to avoid jQuery.event.remove's overhead
							} else {
								jQuery.removeEvent( elem, type, data.handle );
							}
						}
					}

					// Support: Chrome <=35 - 45+
					// Assign undefined instead of using delete, see Data#remove
					elem[ dataPriv.expando ] = undefined;
				}
				if ( elem[ dataUser.expando ] ) {

					// Support: Chrome <=35 - 45+
					// Assign undefined instead of using delete, see Data#remove
					elem[ dataUser.expando ] = undefined;
				}
			}
		}
	}
} );

jQuery.fn.extend( {
	detach: function( selector ) {
		return remove( this, selector, true );
	},

	remove: function( selector ) {
		return remove( this, selector );
	},

	text: function( value ) {
		return access( this, function( value ) {
			return value === undefined ?
				jQuery.text( this ) :
				this.empty().each( function() {
					if ( this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9 ) {
						this.textContent = value;
					}
				} );
		}, null, value, arguments.length );
	},

	append: function() {
		return domManip( this, arguments, function( elem ) {
			if ( this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9 ) {
				var target = manipulationTarget( this, elem );
				target.appendChild( elem );
			}
		} );
	},

	prepend: function() {
		return domManip( this, arguments, function( elem ) {
			if ( this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9 ) {
				var target = manipulationTarget( this, elem );
				target.insertBefore( elem, target.firstChild );
			}
		} );
	},

	before: function() {
		return domManip( this, arguments, function( elem ) {
			if ( this.parentNode ) {
				this.parentNode.insertBefore( elem, this );
			}
		} );
	},

	after: function() {
		return domManip( this, arguments, function( elem ) {
			if ( this.parentNode ) {
				this.parentNode.insertBefore( elem, this.nextSibling );
			}
		} );
	},

	empty: function() {
		var elem,
			i = 0;

		for ( ; ( elem = this[ i ] ) != null; i++ ) {
			if ( elem.nodeType === 1 ) {

				// Prevent memory leaks
				jQuery.cleanData( getAll( elem, false ) );

				// Remove any remaining nodes
				elem.textContent = "";
			}
		}

		return this;
	},

	clone: function( dataAndEvents, deepDataAndEvents ) {
		dataAndEvents = dataAndEvents == null ? false : dataAndEvents;
		deepDataAndEvents = deepDataAndEvents == null ? dataAndEvents : deepDataAndEvents;

		return this.map( function() {
			return jQuery.clone( this, dataAndEvents, deepDataAndEvents );
		} );
	},

	html: function( value ) {
		return access( this, function( value ) {
			var elem = this[ 0 ] || {},
				i = 0,
				l = this.length;

			if ( value === undefined && elem.nodeType === 1 ) {
				return elem.innerHTML;
			}

			// See if we can take a shortcut and just use innerHTML
			if ( typeof value === "string" && !rnoInnerhtml.test( value ) &&
				!wrapMap[ ( rtagName.exec( value ) || [ "", "" ] )[ 1 ].toLowerCase() ] ) {

				value = jQuery.htmlPrefilter( value );

				try {
					for ( ; i < l; i++ ) {
						elem = this[ i ] || {};

						// Remove element nodes and prevent memory leaks
						if ( elem.nodeType === 1 ) {
							jQuery.cleanData( getAll( elem, false ) );
							elem.innerHTML = value;
						}
					}

					elem = 0;

				// If using innerHTML throws an exception, use the fallback method
				} catch ( e ) {}
			}

			if ( elem ) {
				this.empty().append( value );
			}
		}, null, value, arguments.length );
	},

	replaceWith: function() {
		var ignored = [];

		// Make the changes, replacing each non-ignored context element with the new content
		return domManip( this, arguments, function( elem ) {
			var parent = this.parentNode;

			if ( jQuery.inArray( this, ignored ) < 0 ) {
				jQuery.cleanData( getAll( this ) );
				if ( parent ) {
					parent.replaceChild( elem, this );
				}
			}

		// Force callback invocation
		}, ignored );
	}
} );

jQuery.each( {
	appendTo: "append",
	prependTo: "prepend",
	insertBefore: "before",
	insertAfter: "after",
	replaceAll: "replaceWith"
}, function( name, original ) {
	jQuery.fn[ name ] = function( selector ) {
		var elems,
			ret = [],
			insert = jQuery( selector ),
			last = insert.length - 1,
			i = 0;

		for ( ; i <= last; i++ ) {
			elems = i === last ? this : this.clone( true );
			jQuery( insert[ i ] )[ original ]( elems );

			// Support: Android <=4.0 only, PhantomJS 1 only
			// .get() because push.apply(_, arraylike) throws on ancient WebKit
			push.apply( ret, elems.get() );
		}

		return this.pushStack( ret );
	};
} );
var rnumnonpx = new RegExp( "^(" + pnum + ")(?!px)[a-z%]+$", "i" );

var rcustomProp = /^--/;


var getStyles = function( elem ) {

		// Support: IE <=11 only, Firefox <=30 (trac-15098, trac-14150)
		// IE throws on elements created in popups
		// FF meanwhile throws on frame elements through "defaultView.getComputedStyle"
		var view = elem.ownerDocument.defaultView;

		if ( !view || !view.opener ) {
			view = window;
		}

		return view.getComputedStyle( elem );
	};

var swap = function( elem, options, callback ) {
	var ret, name,
		old = {};

	// Remember the old values, and insert the new ones
	for ( name in options ) {
		old[ name ] = elem.style[ name ];
		elem.style[ name ] = options[ name ];
	}

	ret = callback.call( elem );

	// Revert the old values
	for ( name in options ) {
		elem.style[ name ] = old[ name ];
	}

	return ret;
};


var rboxStyle = new RegExp( cssExpand.join( "|" ), "i" );



( function() {

	// Executing both pixelPosition & boxSizingReliable tests require only one layout
	// so they're executed at the same time to save the second computation.
	function computeStyleTests() {

		// This is a singleton, we need to execute it only once
		if ( !div ) {
			return;
		}

		container.style.cssText = "position:absolute;left:-11111px;width:60px;" +
			"margin-top:1px;padding:0;border:0";
		div.style.cssText =
			"position:relative;display:block;box-sizing:border-box;overflow:scroll;" +
			"margin:auto;border:1px;padding:1px;" +
			"width:60%;top:1%";
		documentElement.appendChild( container ).appendChild( div );

		var divStyle = window.getComputedStyle( div );
		pixelPositionVal = divStyle.top !== "1%";

		// Support: Android 4.0 - 4.3 only, Firefox <=3 - 44
		reliableMarginLeftVal = roundPixelMeasures( divStyle.marginLeft ) === 12;

		// Support: Android 4.0 - 4.3 only, Safari <=9.1 - 10.1, iOS <=7.0 - 9.3
		// Some styles come back with percentage values, even though they shouldn't
		div.style.right = "60%";
		pixelBoxStylesVal = roundPixelMeasures( divStyle.right ) === 36;

		// Support: IE 9 - 11 only
		// Detect misreporting of content dimensions for box-sizing:border-box elements
		boxSizingReliableVal = roundPixelMeasures( divStyle.width ) === 36;

		// Support: IE 9 only
		// Detect overflow:scroll screwiness (gh-3699)
		// Support: Chrome <=64
		// Don't get tricked when zoom affects offsetWidth (gh-4029)
		div.style.position = "absolute";
		scrollboxSizeVal = roundPixelMeasures( div.offsetWidth / 3 ) === 12;

		documentElement.removeChild( container );

		// Nullify the div so it wouldn't be stored in the memory and
		// it will also be a sign that checks already performed
		div = null;
	}

	function roundPixelMeasures( measure ) {
		return Math.round( parseFloat( measure ) );
	}

	var pixelPositionVal, boxSizingReliableVal, scrollboxSizeVal, pixelBoxStylesVal,
		reliableTrDimensionsVal, reliableMarginLeftVal,
		container = document.createElement( "div" ),
		div = document.createElement( "div" );

	// Finish early in limited (non-browser) environments
	if ( !div.style ) {
		return;
	}

	// Support: IE <=9 - 11 only
	// Style of cloned element affects source element cloned (trac-8908)
	div.style.backgroundClip = "content-box";
	div.cloneNode( true ).style.backgroundClip = "";
	support.clearCloneStyle = div.style.backgroundClip === "content-box";

	jQuery.extend( support, {
		boxSizingReliable: function() {
			computeStyleTests();
			return boxSizingReliableVal;
		},
		pixelBoxStyles: function() {
			computeStyleTests();
			return pixelBoxStylesVal;
		},
		pixelPosition: function() {
			computeStyleTests();
			return pixelPositionVal;
		},
		reliableMarginLeft: function() {
			computeStyleTests();
			return reliableMarginLeftVal;
		},
		scrollboxSize: function() {
			computeStyleTests();
			return scrollboxSizeVal;
		},

		// Support: IE 9 - 11+, Edge 15 - 18+
		// IE/Edge misreport `getComputedStyle` of table rows with width/height
		// set in CSS while `offset*` properties report correct values.
		// Behavior in IE 9 is more subtle than in newer versions & it passes
		// some versions of this test; make sure not to make it pass there!
		//
		// Support: Firefox 70+
		// Only Firefox includes border widths
		// in computed dimensions. (gh-4529)
		reliableTrDimensions: function() {
			var table, tr, trChild, trStyle;
			if ( reliableTrDimensionsVal == null ) {
				table = document.createElement( "table" );
				tr = document.createElement( "tr" );
				trChild = document.createElement( "div" );

				table.style.cssText = "position:absolute;left:-11111px;border-collapse:separate";
				tr.style.cssText = "box-sizing:content-box;border:1px solid";

				// Support: Chrome 86+
				// Height set through cssText does not get applied.
				// Computed height then comes back as 0.
				tr.style.height = "1px";
				trChild.style.height = "9px";

				// Support: Android 8 Chrome 86+
				// In our bodyBackground.html iframe,
				// display for all div elements is set to "inline",
				// which causes a problem only in Android 8 Chrome 86.
				// Ensuring the div is `display: block`
				// gets around this issue.
				trChild.style.display = "block";

				documentElement
					.appendChild( table )
					.appendChild( tr )
					.appendChild( trChild );

				trStyle = window.getComputedStyle( tr );
				reliableTrDimensionsVal = ( parseInt( trStyle.height, 10 ) +
					parseInt( trStyle.borderTopWidth, 10 ) +
					parseInt( trStyle.borderBottomWidth, 10 ) ) === tr.offsetHeight;

				documentElement.removeChild( table );
			}
			return reliableTrDimensionsVal;
		}
	} );
} )();


function curCSS( elem, name, computed ) {
	var width, minWidth, maxWidth, ret,
		isCustomProp = rcustomProp.test( name ),

		// Support: Firefox 51+
		// Retrieving style before computed somehow
		// fixes an issue with getting wrong values
		// on detached elements
		style = elem.style;

	computed = computed || getStyles( elem );

	// getPropertyValue is needed for:
	//   .css('filter') (IE 9 only, trac-12537)
	//   .css('--customProperty) (gh-3144)
	if ( computed ) {

		// Support: IE <=9 - 11+
		// IE only supports `"float"` in `getPropertyValue`; in computed styles
		// it's only available as `"cssFloat"`. We no longer modify properties
		// sent to `.css()` apart from camelCasing, so we need to check both.
		// Normally, this would create difference in behavior: if
		// `getPropertyValue` returns an empty string, the value returned
		// by `.css()` would be `undefined`. This is usually the case for
		// disconnected elements. However, in IE even disconnected elements
		// with no styles return `"none"` for `getPropertyValue( "float" )`
		ret = computed.getPropertyValue( name ) || computed[ name ];

		if ( isCustomProp && ret ) {

			// Support: Firefox 105+, Chrome <=105+
			// Spec requires trimming whitespace for custom properties (gh-4926).
			// Firefox only trims leading whitespace. Chrome just collapses
			// both leading & trailing whitespace to a single space.
			//
			// Fall back to `undefined` if empty string returned.
			// This collapses a missing definition with property defined
			// and set to an empty string but there's no standard API
			// allowing us to differentiate them without a performance penalty
			// and returning `undefined` aligns with older jQuery.
			//
			// rtrimCSS treats U+000D CARRIAGE RETURN and U+000C FORM FEED
			// as whitespace while CSS does not, but this is not a problem
			// because CSS preprocessing replaces them with U+000A LINE FEED
			// (which *is* CSS whitespace)
			// https://www.w3.org/TR/css-syntax-3/#input-preprocessing
			ret = ret.replace( rtrimCSS, "$1" ) || undefined;
		}

		if ( ret === "" && !isAttached( elem ) ) {
			ret = jQuery.style( elem, name );
		}

		// A tribute to the "awesome hack by Dean Edwards"
		// Android Browser returns percentage for some values,
		// but width seems to be reliably pixels.
		// This is against the CSSOM draft spec:
		// https://drafts.csswg.org/cssom/#resolved-values
		if ( !support.pixelBoxStyles() && rnumnonpx.test( ret ) && rboxStyle.test( name ) ) {

			// Remember the original values
			width = style.width;
			minWidth = style.minWidth;
			maxWidth = style.maxWidth;

			// Put in the new values to get a computed value out
			style.minWidth = style.maxWidth = style.width = ret;
			ret = computed.width;

			// Revert the changed values
			style.width = width;
			style.minWidth = minWidth;
			style.maxWidth = maxWidth;
		}
	}

	return ret !== undefined ?

		// Support: IE <=9 - 11 only
		// IE returns zIndex value as an integer.
		ret + "" :
		ret;
}


function addGetHookIf( conditionFn, hookFn ) {

	// Define the hook, we'll check on the first run if it's really needed.
	return {
		get: function() {
			if ( conditionFn() ) {

				// Hook not needed (or it's not possible to use it due
				// to missing dependency), remove it.
				delete this.get;
				return;
			}

			// Hook needed; redefine it so that the support test is not executed again.
			return ( this.get = hookFn ).apply( this, arguments );
		}
	};
}


var cssPrefixes = [ "Webkit", "Moz", "ms" ],
	emptyStyle = document.createElement( "div" ).style,
	vendorProps = {};

// Return a vendor-prefixed property or undefined
function vendorPropName( name ) {

	// Check for vendor prefixed names
	var capName = name[ 0 ].toUpperCase() + name.slice( 1 ),
		i = cssPrefixes.length;

	while ( i-- ) {
		name = cssPrefixes[ i ] + capName;
		if ( name in emptyStyle ) {
			return name;
		}
	}
}

// Return a potentially-mapped jQuery.cssProps or vendor prefixed property
function finalPropName( name ) {
	var final = jQuery.cssProps[ name ] || vendorProps[ name ];

	if ( final ) {
		return final;
	}
	if ( name in emptyStyle ) {
		return name;
	}
	return vendorProps[ name ] = vendorPropName( name ) || name;
}


var

	// Swappable if display is none or starts with table
	// except "table", "table-cell", or "table-caption"
	// See here for display values: https://developer.mozilla.org/en-US/docs/CSS/display
	rdisplayswap = /^(none|table(?!-c[ea]).+)/,
	cssShow = { position: "absolute", visibility: "hidden", display: "block" },
	cssNormalTransform = {
		letterSpacing: "0",
		fontWeight: "400"
	};

function setPositiveNumber( _elem, value, subtract ) {

	// Any relative (+/-) values have already been
	// normalized at this point
	var matches = rcssNum.exec( value );
	return matches ?

		// Guard against undefined "subtract", e.g., when used as in cssHooks
		Math.max( 0, matches[ 2 ] - ( subtract || 0 ) ) + ( matches[ 3 ] || "px" ) :
		value;
}

function boxModelAdjustment( elem, dimension, box, isBorderBox, styles, computedVal ) {
	var i = dimension === "width" ? 1 : 0,
		extra = 0,
		delta = 0,
		marginDelta = 0;

	// Adjustment may not be necessary
	if ( box === ( isBorderBox ? "border" : "content" ) ) {
		return 0;
	}

	for ( ; i < 4; i += 2 ) {

		// Both box models exclude margin
		// Count margin delta separately to only add it after scroll gutter adjustment.
		// This is needed to make negative margins work with `outerHeight( true )` (gh-3982).
		if ( box === "margin" ) {
			marginDelta += jQuery.css( elem, box + cssExpand[ i ], true, styles );
		}

		// If we get here with a content-box, we're seeking "padding" or "border" or "margin"
		if ( !isBorderBox ) {

			// Add padding
			delta += jQuery.css( elem, "padding" + cssExpand[ i ], true, styles );

			// For "border" or "margin", add border
			if ( box !== "padding" ) {
				delta += jQuery.css( elem, "border" + cssExpand[ i ] + "Width", true, styles );

			// But still keep track of it otherwise
			} else {
				extra += jQuery.css( elem, "border" + cssExpand[ i ] + "Width", true, styles );
			}

		// If we get here with a border-box (content + padding + border), we're seeking "content" or
		// "padding" or "margin"
		} else {

			// For "content", subtract padding
			if ( box === "content" ) {
				delta -= jQuery.css( elem, "padding" + cssExpand[ i ], true, styles );
			}

			// For "content" or "padding", subtract border
			if ( box !== "margin" ) {
				delta -= jQuery.css( elem, "border" + cssExpand[ i ] + "Width", true, styles );
			}
		}
	}

	// Account for positive content-box scroll gutter when requested by providing computedVal
	if ( !isBorderBox && computedVal >= 0 ) {

		// offsetWidth/offsetHeight is a rounded sum of content, padding, scroll gutter, and border
		// Assuming integer scroll gutter, subtract the rest and round down
		delta += Math.max( 0, Math.ceil(
			elem[ "offset" + dimension[ 0 ].toUpperCase() + dimension.slice( 1 ) ] -
			computedVal -
			delta -
			extra -
			0.5

		// If offsetWidth/offsetHeight is unknown, then we can't determine content-box scroll gutter
		// Use an explicit zero to avoid NaN (gh-3964)
		) ) || 0;
	}

	return delta + marginDelta;
}

function getWidthOrHeight( elem, dimension, extra ) {

	// Start with computed style
	var styles = getStyles( elem ),

		// To avoid forcing a reflow, only fetch boxSizing if we need it (gh-4322).
		// Fake content-box until we know it's needed to know the true value.
		boxSizingNeeded = !support.boxSizingReliable() || extra,
		isBorderBox = boxSizingNeeded &&
			jQuery.css( elem, "boxSizing", false, styles ) === "border-box",
		valueIsBorderBox = isBorderBox,

		val = curCSS( elem, dimension, styles ),
		offsetProp = "offset" + dimension[ 0 ].toUpperCase() + dimension.slice( 1 );

	// Support: Firefox <=54
	// Return a confounding non-pixel value or feign ignorance, as appropriate.
	if ( rnumnonpx.test( val ) ) {
		if ( !extra ) {
			return val;
		}
		val = "auto";
	}


	// Support: IE 9 - 11 only
	// Use offsetWidth/offsetHeight for when box sizing is unreliable.
	// In those cases, the computed value can be trusted to be border-box.
	if ( ( !support.boxSizingReliable() && isBorderBox ||

		// Support: IE 10 - 11+, Edge 15 - 18+
		// IE/Edge misreport `getComputedStyle` of table rows with width/height
		// set in CSS while `offset*` properties report correct values.
		// Interestingly, in some cases IE 9 doesn't suffer from this issue.
		!support.reliableTrDimensions() && nodeName( elem, "tr" ) ||

		// Fall back to offsetWidth/offsetHeight when value is "auto"
		// This happens for inline elements with no explicit setting (gh-3571)
		val === "auto" ||

		// Support: Android <=4.1 - 4.3 only
		// Also use offsetWidth/offsetHeight for misreported inline dimensions (gh-3602)
		!parseFloat( val ) && jQuery.css( elem, "display", false, styles ) === "inline" ) &&

		// Make sure the element is visible & connected
		elem.getClientRects().length ) {

		isBorderBox = jQuery.css( elem, "boxSizing", false, styles ) === "border-box";

		// Where available, offsetWidth/offsetHeight approximate border box dimensions.
		// Where not available (e.g., SVG), assume unreliable box-sizing and interpret the
		// retrieved value as a content box dimension.
		valueIsBorderBox = offsetProp in elem;
		if ( valueIsBorderBox ) {
			val = elem[ offsetProp ];
		}
	}

	// Normalize "" and auto
	val = parseFloat( val ) || 0;

	// Adjust for the element's box model
	return ( val +
		boxModelAdjustment(
			elem,
			dimension,
			extra || ( isBorderBox ? "border" : "content" ),
			valueIsBorderBox,
			styles,

			// Provide the current computed size to request scroll gutter calculation (gh-3589)
			val
		)
	) + "px";
}

jQuery.extend( {

	// Add in style property hooks for overriding the default
	// behavior of getting and setting a style property
	cssHooks: {
		opacity: {
			get: function( elem, computed ) {
				if ( computed ) {

					// We should always get a number back from opacity
					var ret = curCSS( elem, "opacity" );
					return ret === "" ? "1" : ret;
				}
			}
		}
	},

	// Don't automatically add "px" to these possibly-unitless properties
	cssNumber: {
		animationIterationCount: true,
		aspectRatio: true,
		borderImageSlice: true,
		columnCount: true,
		flexGrow: true,
		flexShrink: true,
		fontWeight: true,
		gridArea: true,
		gridColumn: true,
		gridColumnEnd: true,
		gridColumnStart: true,
		gridRow: true,
		gridRowEnd: true,
		gridRowStart: true,
		lineHeight: true,
		opacity: true,
		order: true,
		orphans: true,
		scale: true,
		widows: true,
		zIndex: true,
		zoom: true,

		// SVG-related
		fillOpacity: true,
		floodOpacity: true,
		stopOpacity: true,
		strokeMiterlimit: true,
		strokeOpacity: true
	},

	// Add in properties whose names you wish to fix before
	// setting or getting the value
	cssProps: {},

	// Get and set the style property on a DOM Node
	style: function( elem, name, value, extra ) {

		// Don't set styles on text and comment nodes
		if ( !elem || elem.nodeType === 3 || elem.nodeType === 8 || !elem.style ) {
			return;
		}

		// Make sure that we're working with the right name
		var ret, type, hooks,
			origName = camelCase( name ),
			isCustomProp = rcustomProp.test( name ),
			style = elem.style;

		// Make sure that we're working with the right name. We don't
		// want to query the value if it is a CSS custom property
		// since they are user-defined.
		if ( !isCustomProp ) {
			name = finalPropName( origName );
		}

		// Gets hook for the prefixed version, then unprefixed version
		hooks = jQuery.cssHooks[ name ] || jQuery.cssHooks[ origName ];

		// Check if we're setting a value
		if ( value !== undefined ) {
			type = typeof value;

			// Convert "+=" or "-=" to relative numbers (trac-7345)
			if ( type === "string" && ( ret = rcssNum.exec( value ) ) && ret[ 1 ] ) {
				value = adjustCSS( elem, name, ret );

				// Fixes bug trac-9237
				type = "number";
			}

			// Make sure that null and NaN values aren't set (trac-7116)
			if ( value == null || value !== value ) {
				return;
			}

			// If a number was passed in, add the unit (except for certain CSS properties)
			// The isCustomProp check can be removed in jQuery 4.0 when we only auto-append
			// "px" to a few hardcoded values.
			if ( type === "number" && !isCustomProp ) {
				value += ret && ret[ 3 ] || ( jQuery.cssNumber[ origName ] ? "" : "px" );
			}

			// background-* props affect original clone's values
			if ( !support.clearCloneStyle && value === "" && name.indexOf( "background" ) === 0 ) {
				style[ name ] = "inherit";
			}

			// If a hook was provided, use that value, otherwise just set the specified value
			if ( !hooks || !( "set" in hooks ) ||
				( value = hooks.set( elem, value, extra ) ) !== undefined ) {

				if ( isCustomProp ) {
					style.setProperty( name, value );
				} else {
					style[ name ] = value;
				}
			}

		} else {

			// If a hook was provided get the non-computed value from there
			if ( hooks && "get" in hooks &&
				( ret = hooks.get( elem, false, extra ) ) !== undefined ) {

				return ret;
			}

			// Otherwise just get the value from the style object
			return style[ name ];
		}
	},

	css: function( elem, name, extra, styles ) {
		var val, num, hooks,
			origName = camelCase( name ),
			isCustomProp = rcustomProp.test( name );

		// Make sure that we're working with the right name. We don't
		// want to modify the value if it is a CSS custom property
		// since they are user-defined.
		if ( !isCustomProp ) {
			name = finalPropName( origName );
		}

		// Try prefixed name followed by the unprefixed name
		hooks = jQuery.cssHooks[ name ] || jQuery.cssHooks[ origName ];

		// If a hook was provided get the computed value from there
		if ( hooks && "get" in hooks ) {
			val = hooks.get( elem, true, extra );
		}

		// Otherwise, if a way to get the computed value exists, use that
		if ( val === undefined ) {
			val = curCSS( elem, name, styles );
		}

		// Convert "normal" to computed value
		if ( val === "normal" && name in cssNormalTransform ) {
			val = cssNormalTransform[ name ];
		}

		// Make numeric if forced or a qualifier was provided and val looks numeric
		if ( extra === "" || extra ) {
			num = parseFloat( val );
			return extra === true || isFinite( num ) ? num || 0 : val;
		}

		return val;
	}
} );

jQuery.each( [ "height", "width" ], function( _i, dimension ) {
	jQuery.cssHooks[ dimension ] = {
		get: function( elem, computed, extra ) {
			if ( computed ) {

				// Certain elements can have dimension info if we invisibly show them
				// but it must have a current display style that would benefit
				return rdisplayswap.test( jQuery.css( elem, "display" ) ) &&

					// Support: Safari 8+
					// Table columns in Safari have non-zero offsetWidth & zero
					// getBoundingClientRect().width unless display is changed.
					// Support: IE <=11 only
					// Running getBoundingClientRect on a disconnected node
					// in IE throws an error.
					( !elem.getClientRects().length || !elem.getBoundingClientRect().width ) ?
					swap( elem, cssShow, function() {
						return getWidthOrHeight( elem, dimension, extra );
					} ) :
					getWidthOrHeight( elem, dimension, extra );
			}
		},

		set: function( elem, value, extra ) {
			var matches,
				styles = getStyles( elem ),

				// Only read styles.position if the test has a chance to fail
				// to avoid forcing a reflow.
				scrollboxSizeBuggy = !support.scrollboxSize() &&
					styles.position === "absolute",

				// To avoid forcing a reflow, only fetch boxSizing if we need it (gh-3991)
				boxSizingNeeded = scrollboxSizeBuggy || extra,
				isBorderBox = boxSizingNeeded &&
					jQuery.css( elem, "boxSizing", false, styles ) === "border-box",
				subtract = extra ?
					boxModelAdjustment(
						elem,
						dimension,
						extra,
						isBorderBox,
						styles
					) :
					0;

			// Account for unreliable border-box dimensions by comparing offset* to computed and
			// faking a content-box to get border and padding (gh-3699)
			if ( isBorderBox && scrollboxSizeBuggy ) {
				subtract -= Math.ceil(
					elem[ "offset" + dimension[ 0 ].toUpperCase() + dimension.slice( 1 ) ] -
					parseFloat( styles[ dimension ] ) -
					boxModelAdjustment( elem, dimension, "border", false, styles ) -
					0.5
				);
			}

			// Convert to pixels if value adjustment is needed
			if ( subtract && ( matches = rcssNum.exec( value ) ) &&
				( matches[ 3 ] || "px" ) !== "px" ) {

				elem.style[ dimension ] = value;
				value = jQuery.css( elem, dimension );
			}

			return setPositiveNumber( elem, value, subtract );
		}
	};
} );

jQuery.cssHooks.marginLeft = addGetHookIf( support.reliableMarginLeft,
	function( elem, computed ) {
		if ( computed ) {
			return ( parseFloat( curCSS( elem, "marginLeft" ) ) ||
				elem.getBoundingClientRect().left -
					swap( elem, { marginLeft: 0 }, function() {
						return elem.getBoundingClientRect().left;
					} )
			) + "px";
		}
	}
);

// These hooks are used by animate to expand properties
jQuery.each( {
	margin: "",
	padding: "",
	border: "Width"
}, function( prefix, suffix ) {
	jQuery.cssHooks[ prefix + suffix ] = {
		expand: function( value ) {
			var i = 0,
				expanded = {},

				// Assumes a single number if not a string
				parts = typeof value === "string" ? value.split( " " ) : [ value ];

			for ( ; i < 4; i++ ) {
				expanded[ prefix + cssExpand[ i ] + suffix ] =
					parts[ i ] || parts[ i - 2 ] || parts[ 0 ];
			}

			return expanded;
		}
	};

	if ( prefix !== "margin" ) {
		jQuery.cssHooks[ prefix + suffix ].set = setPositiveNumber;
	}
} );

jQuery.fn.extend( {
	css: function( name, value ) {
		return access( this, function( elem, name, value ) {
			var styles, len,
				map = {},
				i = 0;

			if ( Array.isArray( name ) ) {
				styles = getStyles( elem );
				len = name.length;

				for ( ; i < len; i++ ) {
					map[ name[ i ] ] = jQuery.css( elem, name[ i ], false, styles );
				}

				return map;
			}

			return value !== undefined ?
				jQuery.style( elem, name, value ) :
				jQuery.css( elem, name );
		}, name, value, arguments.length > 1 );
	}
} );


function Tween( elem, options, prop, end, easing ) {
	return new Tween.prototype.init( elem, options, prop, end, easing );
}
jQuery.Tween = Tween;

Tween.prototype = {
	constructor: Tween,
	init: function( elem, options, prop, end, easing, unit ) {
		this.elem = elem;
		this.prop = prop;
		this.easing = easing || jQuery.easing._default;
		this.options = options;
		this.start = this.now = this.cur();
		this.end = end;
		this.unit = unit || ( jQuery.cssNumber[ prop ] ? "" : "px" );
	},
	cur: function() {
		var hooks = Tween.propHooks[ this.prop ];

		return hooks && hooks.get ?
			hooks.get( this ) :
			Tween.propHooks._default.get( this );
	},
	run: function( percent ) {
		var eased,
			hooks = Tween.propHooks[ this.prop ];

		if ( this.options.duration ) {
			this.pos = eased = jQuery.easing[ this.easing ](
				percent, this.options.duration * percent, 0, 1, this.options.duration
			);
		} else {
			this.pos = eased = percent;
		}
		this.now = ( this.end - this.start ) * eased + this.start;

		if ( this.options.step ) {
			this.options.step.call( this.elem, this.now, this );
		}

		if ( hooks && hooks.set ) {
			hooks.set( this );
		} else {
			Tween.propHooks._default.set( this );
		}
		return this;
	}
};

Tween.prototype.init.prototype = Tween.prototype;

Tween.propHooks = {
	_default: {
		get: function( tween ) {
			var result;

			// Use a property on the element directly when it is not a DOM element,
			// or when there is no matching style property that exists.
			if ( tween.elem.nodeType !== 1 ||
				tween.elem[ tween.prop ] != null && tween.elem.style[ tween.prop ] == null ) {
				return tween.elem[ tween.prop ];
			}

			// Passing an empty string as a 3rd parameter to .css will automatically
			// attempt a parseFloat and fallback to a string if the parse fails.
			// Simple values such as "10px" are parsed to Float;
			// complex values such as "rotate(1rad)" are returned as-is.
			result = jQuery.css( tween.elem, tween.prop, "" );

			// Empty strings, null, undefined and "auto" are converted to 0.
			return !result || result === "auto" ? 0 : result;
		},
		set: function( tween ) {

			// Use step hook for back compat.
			// Use cssHook if its there.
			// Use .style if available and use plain properties where available.
			if ( jQuery.fx.step[ tween.prop ] ) {
				jQuery.fx.step[ tween.prop ]( tween );
			} else if ( tween.elem.nodeType === 1 && (
				jQuery.cssHooks[ tween.prop ] ||
					tween.elem.style[ finalPropName( tween.prop ) ] != null ) ) {
				jQuery.style( tween.elem, tween.prop, tween.now + tween.unit );
			} else {
				tween.elem[ tween.prop ] = tween.now;
			}
		}
	}
};

// Support: IE <=9 only
// Panic based approach to setting things on disconnected nodes
Tween.propHooks.scrollTop = Tween.propHooks.scrollLeft = {
	set: function( tween ) {
		if ( tween.elem.nodeType && tween.elem.parentNode ) {
			tween.elem[ tween.prop ] = tween.now;
		}
	}
};

jQuery.easing = {
	linear: function( p ) {
		return p;
	},
	swing: function( p ) {
		return 0.5 - Math.cos( p * Math.PI ) / 2;
	},
	_default: "swing"
};

jQuery.fx = Tween.prototype.init;

// Back compat <1.8 extension point
jQuery.fx.step = {};




var
	fxNow, inProgress,
	rfxtypes = /^(?:toggle|show|hide)$/,
	rrun = /queueHooks$/;

function schedule() {
	if ( inProgress ) {
		if ( document.hidden === false && window.requestAnimationFrame ) {
			window.requestAnimationFrame( schedule );
		} else {
			window.setTimeout( schedule, jQuery.fx.interval );
		}

		jQuery.fx.tick();
	}
}

// Animations created synchronously will run synchronously
function createFxNow() {
	window.setTimeout( function() {
		fxNow = undefined;
	} );
	return ( fxNow = Date.now() );
}

// Generate parameters to create a standard animation
function genFx( type, includeWidth ) {
	var which,
		i = 0,
		attrs = { height: type };

	// If we include width, step value is 1 to do all cssExpand values,
	// otherwise step value is 2 to skip over Left and Right
	includeWidth = includeWidth ? 1 : 0;
	for ( ; i < 4; i += 2 - includeWidth ) {
		which = cssExpand[ i ];
		attrs[ "margin" + which ] = attrs[ "padding" + which ] = type;
	}

	if ( includeWidth ) {
		attrs.opacity = attrs.width = type;
	}

	return attrs;
}

function createTween( value, prop, animation ) {
	var tween,
		collection = ( Animation.tweeners[ prop ] || [] ).concat( Animation.tweeners[ "*" ] ),
		index = 0,
		length = collection.length;
	for ( ; index < length; index++ ) {
		if ( ( tween = collection[ index ].call( animation, prop, value ) ) ) {

			// We're done with this property
			return tween;
		}
	}
}

function defaultPrefilter( elem, props, opts ) {
	var prop, value, toggle, hooks, oldfire, propTween, restoreDisplay, display,
		isBox = "width" in props || "height" in props,
		anim = this,
		orig = {},
		style = elem.style,
		hidden = elem.nodeType && isHiddenWithinTree( elem ),
		dataShow = dataPriv.get( elem, "fxshow" );

	// Queue-skipping animations hijack the fx hooks
	if ( !opts.queue ) {
		hooks = jQuery._queueHooks( elem, "fx" );
		if ( hooks.unqueued == null ) {
			hooks.unqueued = 0;
			oldfire = hooks.empty.fire;
			hooks.empty.fire = function() {
				if ( !hooks.unqueued ) {
					oldfire();
				}
			};
		}
		hooks.unqueued++;

		anim.always( function() {

			// Ensure the complete handler is called before this completes
			anim.always( function() {
				hooks.unqueued--;
				if ( !jQuery.queue( elem, "fx" ).length ) {
					hooks.empty.fire();
				}
			} );
		} );
	}

	// Detect show/hide animations
	for ( prop in props ) {
		value = props[ prop ];
		if ( rfxtypes.test( value ) ) {
			delete props[ prop ];
			toggle = toggle || value === "toggle";
			if ( value === ( hidden ? "hide" : "show" ) ) {

				// Pretend to be hidden if this is a "show" and
				// there is still data from a stopped show/hide
				if ( value === "show" && dataShow && dataShow[ prop ] !== undefined ) {
					hidden = true;

				// Ignore all other no-op show/hide data
				} else {
					continue;
				}
			}
			orig[ prop ] = dataShow && dataShow[ prop ] || jQuery.style( elem, prop );
		}
	}

	// Bail out if this is a no-op like .hide().hide()
	propTween = !jQuery.isEmptyObject( props );
	if ( !propTween && jQuery.isEmptyObject( orig ) ) {
		return;
	}

	// Restrict "overflow" and "display" styles during box animations
	if ( isBox && elem.nodeType === 1 ) {

		// Support: IE <=9 - 11, Edge 12 - 15
		// Record all 3 overflow attributes because IE does not infer the shorthand
		// from identically-valued overflowX and overflowY and Edge just mirrors
		// the overflowX value there.
		opts.overflow = [ style.overflow, style.overflowX, style.overflowY ];

		// Identify a display type, preferring old show/hide data over the CSS cascade
		restoreDisplay = dataShow && dataShow.display;
		if ( restoreDisplay == null ) {
			restoreDisplay = dataPriv.get( elem, "display" );
		}
		display = jQuery.css( elem, "display" );
		if ( display === "none" ) {
			if ( restoreDisplay ) {
				display = restoreDisplay;
			} else {

				// Get nonempty value(s) by temporarily forcing visibility
				showHide( [ elem ], true );
				restoreDisplay = elem.style.display || restoreDisplay;
				display = jQuery.css( elem, "display" );
				showHide( [ elem ] );
			}
		}

		// Animate inline elements as inline-block
		if ( display === "inline" || display === "inline-block" && restoreDisplay != null ) {
			if ( jQuery.css( elem, "float" ) === "none" ) {

				// Restore the original display value at the end of pure show/hide animations
				if ( !propTween ) {
					anim.done( function() {
						style.display = restoreDisplay;
					} );
					if ( restoreDisplay == null ) {
						display = style.display;
						restoreDisplay = display === "none" ? "" : display;
					}
				}
				style.display = "inline-block";
			}
		}
	}

	if ( opts.overflow ) {
		style.overflow = "hidden";
		anim.always( function() {
			style.overflow = opts.overflow[ 0 ];
			style.overflowX = opts.overflow[ 1 ];
			style.overflowY = opts.overflow[ 2 ];
		} );
	}

	// Implement show/hide animations
	propTween = false;
	for ( prop in orig ) {

		// General show/hide setup for this element animation
		if ( !propTween ) {
			if ( dataShow ) {
				if ( "hidden" in dataShow ) {
					hidden = dataShow.hidden;
				}
			} else {
				dataShow = dataPriv.access( elem, "fxshow", { display: restoreDisplay } );
			}

			// Store hidden/visible for toggle so `.stop().toggle()` "reverses"
			if ( toggle ) {
				dataShow.hidden = !hidden;
			}

			// Show elements before animating them
			if ( hidden ) {
				showHide( [ elem ], true );
			}

			/* eslint-disable no-loop-func */

			anim.done( function() {

				/* eslint-enable no-loop-func */

				// The final step of a "hide" animation is actually hiding the element
				if ( !hidden ) {
					showHide( [ elem ] );
				}
				dataPriv.remove( elem, "fxshow" );
				for ( prop in orig ) {
					jQuery.style( elem, prop, orig[ prop ] );
				}
			} );
		}

		// Per-property setup
		propTween = createTween( hidden ? dataShow[ prop ] : 0, prop, anim );
		if ( !( prop in dataShow ) ) {
			dataShow[ prop ] = propTween.start;
			if ( hidden ) {
				propTween.end = propTween.start;
				propTween.start = 0;
			}
		}
	}
}

function propFilter( props, specialEasing ) {
	var index, name, easing, value, hooks;

	// camelCase, specialEasing and expand cssHook pass
	for ( index in props ) {
		name = camelCase( index );
		easing = specialEasing[ name ];
		value = props[ index ];
		if ( Array.isArray( value ) ) {
			easing = value[ 1 ];
			value = props[ index ] = value[ 0 ];
		}

		if ( index !== name ) {
			props[ name ] = value;
			delete props[ index ];
		}

		hooks = jQuery.cssHooks[ name ];
		if ( hooks && "expand" in hooks ) {
			value = hooks.expand( value );
			delete props[ name ];

			// Not quite $.extend, this won't overwrite existing keys.
			// Reusing 'index' because we have the correct "name"
			for ( index in value ) {
				if ( !( index in props ) ) {
					props[ index ] = value[ index ];
					specialEasing[ index ] = easing;
				}
			}
		} else {
			specialEasing[ name ] = easing;
		}
	}
}

function Animation( elem, properties, options ) {
	var result,
		stopped,
		index = 0,
		length = Animation.prefilters.length,
		deferred = jQuery.Deferred().always( function() {

			// Don't match elem in the :animated selector
			delete tick.elem;
		} ),
		tick = function() {
			if ( stopped ) {
				return false;
			}
			var currentTime = fxNow || createFxNow(),
				remaining = Math.max( 0, animation.startTime + animation.duration - currentTime ),

				// Support: Android 2.3 only
				// Archaic crash bug won't allow us to use `1 - ( 0.5 || 0 )` (trac-12497)
				temp = remaining / animation.duration || 0,
				percent = 1 - temp,
				index = 0,
				length = animation.tweens.length;

			for ( ; index < length; index++ ) {
				animation.tweens[ index ].run( percent );
			}

			deferred.notifyWith( elem, [ animation, percent, remaining ] );

			// If there's more to do, yield
			if ( percent < 1 && length ) {
				return remaining;
			}

			// If this was an empty animation, synthesize a final progress notification
			if ( !length ) {
				deferred.notifyWith( elem, [ animation, 1, 0 ] );
			}

			// Resolve the animation and report its conclusion
			deferred.resolveWith( elem, [ animation ] );
			return false;
		},
		animation = deferred.promise( {
			elem: elem,
			props: jQuery.extend( {}, properties ),
			opts: jQuery.extend( true, {
				specialEasing: {},
				easing: jQuery.easing._default
			}, options ),
			originalProperties: properties,
			originalOptions: options,
			startTime: fxNow || createFxNow(),
			duration: options.duration,
			tweens: [],
			createTween: function( prop, end ) {
				var tween = jQuery.Tween( elem, animation.opts, prop, end,
					animation.opts.specialEasing[ prop ] || animation.opts.easing );
				animation.tweens.push( tween );
				return tween;
			},
			stop: function( gotoEnd ) {
				var index = 0,

					// If we are going to the end, we want to run all the tweens
					// otherwise we skip this part
					length = gotoEnd ? animation.tweens.length : 0;
				if ( stopped ) {
					return this;
				}
				stopped = true;
				for ( ; index < length; index++ ) {
					animation.tweens[ index ].run( 1 );
				}

				// Resolve when we played the last frame; otherwise, reject
				if ( gotoEnd ) {
					deferred.notifyWith( elem, [ animation, 1, 0 ] );
					deferred.resolveWith( elem, [ animation, gotoEnd ] );
				} else {
					deferred.rejectWith( elem, [ animation, gotoEnd ] );
				}
				return this;
			}
		} ),
		props = animation.props;

	propFilter( props, animation.opts.specialEasing );

	for ( ; index < length; index++ ) {
		result = Animation.prefilters[ index ].call( animation, elem, props, animation.opts );
		if ( result ) {
			if ( isFunction( result.stop ) ) {
				jQuery._queueHooks( animation.elem, animation.opts.queue ).stop =
					result.stop.bind( result );
			}
			return result;
		}
	}

	jQuery.map( props, createTween, animation );

	if ( isFunction( animation.opts.start ) ) {
		animation.opts.start.call( elem, animation );
	}

	// Attach callbacks from options
	animation
		.progress( animation.opts.progress )
		.done( animation.opts.done, animation.opts.complete )
		.fail( animation.opts.fail )
		.always( animation.opts.always );

	jQuery.fx.timer(
		jQuery.extend( tick, {
			elem: elem,
			anim: animation,
			queue: animation.opts.queue
		} )
	);

	return animation;
}

jQuery.Animation = jQuery.extend( Animation, {

	tweeners: {
		"*": [ function( prop, value ) {
			var tween = this.createTween( prop, value );
			adjustCSS( tween.elem, prop, rcssNum.exec( value ), tween );
			return tween;
		} ]
	},

	tweener: function( props, callback ) {
		if ( isFunction( props ) ) {
			callback = props;
			props = [ "*" ];
		} else {
			props = props.match( rnothtmlwhite );
		}

		var prop,
			index = 0,
			length = props.length;

		for ( ; index < length; index++ ) {
			prop = props[ index ];
			Animation.tweeners[ prop ] = Animation.tweeners[ prop ] || [];
			Animation.tweeners[ prop ].unshift( callback );
		}
	},

	prefilters: [ defaultPrefilter ],

	prefilter: function( callback, prepend ) {
		if ( prepend ) {
			Animation.prefilters.unshift( callback );
		} else {
			Animation.prefilters.push( callback );
		}
	}
} );

jQuery.speed = function( speed, easing, fn ) {
	var opt = speed && typeof speed === "object" ? jQuery.extend( {}, speed ) : {
		complete: fn || !fn && easing ||
			isFunction( speed ) && speed,
		duration: speed,
		easing: fn && easing || easing && !isFunction( easing ) && easing
	};

	// Go to the end state if fx are off
	if ( jQuery.fx.off ) {
		opt.duration = 0;

	} else {
		if ( typeof opt.duration !== "number" ) {
			if ( opt.duration in jQuery.fx.speeds ) {
				opt.duration = jQuery.fx.speeds[ opt.duration ];

			} else {
				opt.duration = jQuery.fx.speeds._default;
			}
		}
	}

	// Normalize opt.queue - true/undefined/null -> "fx"
	if ( opt.queue == null || opt.queue === true ) {
		opt.queue = "fx";
	}

	// Queueing
	opt.old = opt.complete;

	opt.complete = function() {
		if ( isFunction( opt.old ) ) {
			opt.old.call( this );
		}

		if ( opt.queue ) {
			jQuery.dequeue( this, opt.queue );
		}
	};

	return opt;
};

jQuery.fn.extend( {
	fadeTo: function( speed, to, easing, callback ) {

		// Show any hidden elements after setting opacity to 0
		return this.filter( isHiddenWithinTree ).css( "opacity", 0 ).show()

			// Animate to the value specified
			.end().animate( { opacity: to }, speed, easing, callback );
	},
	animate: function( prop, speed, easing, callback ) {
		var empty = jQuery.isEmptyObject( prop ),
			optall = jQuery.speed( speed, easing, callback ),
			doAnimation = function() {

				// Operate on a copy of prop so per-property easing won't be lost
				var anim = Animation( this, jQuery.extend( {}, prop ), optall );

				// Empty animations, or finishing resolves immediately
				if ( empty || dataPriv.get( this, "finish" ) ) {
					anim.stop( true );
				}
			};

		doAnimation.finish = doAnimation;

		return empty || optall.queue === false ?
			this.each( doAnimation ) :
			this.queue( optall.queue, doAnimation );
	},
	stop: function( type, clearQueue, gotoEnd ) {
		var stopQueue = function( hooks ) {
			var stop = hooks.stop;
			delete hooks.stop;
			stop( gotoEnd );
		};

		if ( typeof type !== "string" ) {
			gotoEnd = clearQueue;
			clearQueue = type;
			type = undefined;
		}
		if ( clearQueue ) {
			this.queue( type || "fx", [] );
		}

		return this.each( function() {
			var dequeue = true,
				index = type != null && type + "queueHooks",
				timers = jQuery.timers,
				data = dataPriv.get( this );

			if ( index ) {
				if ( data[ index ] && data[ index ].stop ) {
					stopQueue( data[ index ] );
				}
			} else {
				for ( index in data ) {
					if ( data[ index ] && data[ index ].stop && rrun.test( index ) ) {
						stopQueue( data[ index ] );
					}
				}
			}

			for ( index = timers.length; index--; ) {
				if ( timers[ index ].elem === this &&
					( type == null || timers[ index ].queue === type ) ) {

					timers[ index ].anim.stop( gotoEnd );
					dequeue = false;
					timers.splice( index, 1 );
				}
			}

			// Start the next in the queue if the last step wasn't forced.
			// Timers currently will call their complete callbacks, which
			// will dequeue but only if they were gotoEnd.
			if ( dequeue || !gotoEnd ) {
				jQuery.dequeue( this, type );
			}
		} );
	},
	finish: function( type ) {
		if ( type !== false ) {
			type = type || "fx";
		}
		return this.each( function() {
			var index,
				data = dataPriv.get( this ),
				queue = data[ type + "queue" ],
				hooks = data[ type + "queueHooks" ],
				timers = jQuery.timers,
				length = queue ? queue.length : 0;

			// Enable finishing flag on private data
			data.finish = true;

			// Empty the queue first
			jQuery.queue( this, type, [] );

			if ( hooks && hooks.stop ) {
				hooks.stop.call( this, true );
			}

			// Look for any active animations, and finish them
			for ( index = timers.length; index--; ) {
				if ( timers[ index ].elem === this && timers[ index ].queue === type ) {
					timers[ index ].anim.stop( true );
					timers.splice( index, 1 );
				}
			}

			// Look for any animations in the old queue and finish them
			for ( index = 0; index < length; index++ ) {
				if ( queue[ index ] && queue[ index ].finish ) {
					queue[ index ].finish.call( this );
				}
			}

			// Turn off finishing flag
			delete data.finish;
		} );
	}
} );

jQuery.each( [ "toggle", "show", "hide" ], function( _i, name ) {
	var cssFn = jQuery.fn[ name ];
	jQuery.fn[ name ] = function( speed, easing, callback ) {
		return speed == null || typeof speed === "boolean" ?
			cssFn.apply( this, arguments ) :
			this.animate( genFx( name, true ), speed, easing, callback );
	};
} );

// Generate shortcuts for custom animations
jQuery.each( {
	slideDown: genFx( "show" ),
	slideUp: genFx( "hide" ),
	slideToggle: genFx( "toggle" ),
	fadeIn: { opacity: "show" },
	fadeOut: { opacity: "hide" },
	fadeToggle: { opacity: "toggle" }
}, function( name, props ) {
	jQuery.fn[ name ] = function( speed, easing, callback ) {
		return this.animate( props, speed, easing, callback );
	};
} );

jQuery.timers = [];
jQuery.fx.tick = function() {
	var timer,
		i = 0,
		timers = jQuery.timers;

	fxNow = Date.now();

	for ( ; i < timers.length; i++ ) {
		timer = timers[ i ];

		// Run the timer and safely remove it when done (allowing for external removal)
		if ( !timer() && timers[ i ] === timer ) {
			timers.splice( i--, 1 );
		}
	}

	if ( !timers.length ) {
		jQuery.fx.stop();
	}
	fxNow = undefined;
};

jQuery.fx.timer = function( timer ) {
	jQuery.timers.push( timer );
	jQuery.fx.start();
};

jQuery.fx.interval = 13;
jQuery.fx.start = function() {
	if ( inProgress ) {
		return;
	}

	inProgress = true;
	schedule();
};

jQuery.fx.stop = function() {
	inProgress = null;
};

jQuery.fx.speeds = {
	slow: 600,
	fast: 200,

	// Default speed
	_default: 400
};


// Based off of the plugin by Clint Helfers, with permission.
jQuery.fn.delay = function( time, type ) {
	time = jQuery.fx ? jQuery.fx.speeds[ time ] || time : time;
	type = type || "fx";

	return this.queue( type, function( next, hooks ) {
		var timeout = window.setTimeout( next, time );
		hooks.stop = function() {
			window.clearTimeout( timeout );
		};
	} );
};


( function() {
	var input = document.createElement( "input" ),
		select = document.createElement( "select" ),
		opt = select.appendChild( document.createElement( "option" ) );

	input.type = "checkbox";

	// Support: Android <=4.3 only
	// Default value for a checkbox should be "on"
	support.checkOn = input.value !== "";

	// Support: IE <=11 only
	// Must access selectedIndex to make default options select
	support.optSelected = opt.selected;

	// Support: IE <=11 only
	// An input loses its value after becoming a radio
	input = document.createElement( "input" );
	input.value = "t";
	input.type = "radio";
	support.radioValue = input.value === "t";
} )();


var boolHook,
	attrHandle = jQuery.expr.attrHandle;

jQuery.fn.extend( {
	attr: function( name, value ) {
		return access( this, jQuery.attr, name, value, arguments.length > 1 );
	},

	removeAttr: function( name ) {
		return this.each( function() {
			jQuery.removeAttr( this, name );
		} );
	}
} );

jQuery.extend( {
	attr: function( elem, name, value ) {
		var ret, hooks,
			nType = elem.nodeType;

		// Don't get/set attributes on text, comment and attribute nodes
		if ( nType === 3 || nType === 8 || nType === 2 ) {
			return;
		}

		// Fallback to prop when attributes are not supported
		if ( typeof elem.getAttribute === "undefined" ) {
			return jQuery.prop( elem, name, value );
		}

		// Attribute hooks are determined by the lowercase version
		// Grab necessary hook if one is defined
		if ( nType !== 1 || !jQuery.isXMLDoc( elem ) ) {
			hooks = jQuery.attrHooks[ name.toLowerCase() ] ||
				( jQuery.expr.match.bool.test( name ) ? boolHook : undefined );
		}

		if ( value !== undefined ) {
			if ( value === null ) {
				jQuery.removeAttr( elem, name );
				return;
			}

			if ( hooks && "set" in hooks &&
				( ret = hooks.set( elem, value, name ) ) !== undefined ) {
				return ret;
			}

			elem.setAttribute( name, value + "" );
			return value;
		}

		if ( hooks && "get" in hooks && ( ret = hooks.get( elem, name ) ) !== null ) {
			return ret;
		}

		ret = jQuery.find.attr( elem, name );

		// Non-existent attributes return null, we normalize to undefined
		return ret == null ? undefined : ret;
	},

	attrHooks: {
		type: {
			set: function( elem, value ) {
				if ( !support.radioValue && value === "radio" &&
					nodeName( elem, "input" ) ) {
					var val = elem.value;
					elem.setAttribute( "type", value );
					if ( val ) {
						elem.value = val;
					}
					return value;
				}
			}
		}
	},

	removeAttr: function( elem, value ) {
		var name,
			i = 0,

			// Attribute names can contain non-HTML whitespace characters
			// https://html.spec.whatwg.org/multipage/syntax.html#attributes-2
			attrNames = value && value.match( rnothtmlwhite );

		if ( attrNames && elem.nodeType === 1 ) {
			while ( ( name = attrNames[ i++ ] ) ) {
				elem.removeAttribute( name );
			}
		}
	}
} );

// Hooks for boolean attributes
boolHook = {
	set: function( elem, value, name ) {
		if ( value === false ) {

			// Remove boolean attributes when set to false
			jQuery.removeAttr( elem, name );
		} else {
			elem.setAttribute( name, name );
		}
		return name;
	}
};

jQuery.each( jQuery.expr.match.bool.source.match( /\w+/g ), function( _i, name ) {
	var getter = attrHandle[ name ] || jQuery.find.attr;

	attrHandle[ name ] = function( elem, name, isXML ) {
		var ret, handle,
			lowercaseName = name.toLowerCase();

		if ( !isXML ) {

			// Avoid an infinite loop by temporarily removing this function from the getter
			handle = attrHandle[ lowercaseName ];
			attrHandle[ lowercaseName ] = ret;
			ret = getter( elem, name, isXML ) != null ?
				lowercaseName :
				null;
			attrHandle[ lowercaseName ] = handle;
		}
		return ret;
	};
} );




var rfocusable = /^(?:input|select|textarea|button)$/i,
	rclickable = /^(?:a|area)$/i;

jQuery.fn.extend( {
	prop: function( name, value ) {
		return access( this, jQuery.prop, name, value, arguments.length > 1 );
	},

	removeProp: function( name ) {
		return this.each( function() {
			delete this[ jQuery.propFix[ name ] || name ];
		} );
	}
} );

jQuery.extend( {
	prop: function( elem, name, value ) {
		var ret, hooks,
			nType = elem.nodeType;

		// Don't get/set properties on text, comment and attribute nodes
		if ( nType === 3 || nType === 8 || nType === 2 ) {
			return;
		}

		if ( nType !== 1 || !jQuery.isXMLDoc( elem ) ) {

			// Fix name and attach hooks
			name = jQuery.propFix[ name ] || name;
			hooks = jQuery.propHooks[ name ];
		}

		if ( value !== undefined ) {
			if ( hooks && "set" in hooks &&
				( ret = hooks.set( elem, value, name ) ) !== undefined ) {
				return ret;
			}

			return ( elem[ name ] = value );
		}

		if ( hooks && "get" in hooks && ( ret = hooks.get( elem, name ) ) !== null ) {
			return ret;
		}

		return elem[ name ];
	},

	propHooks: {
		tabIndex: {
			get: function( elem ) {

				// Support: IE <=9 - 11 only
				// elem.tabIndex doesn't always return the
				// correct value when it hasn't been explicitly set
				// Use proper attribute retrieval (trac-12072)
				var tabindex = jQuery.find.attr( elem, "tabindex" );

				if ( tabindex ) {
					return parseInt( tabindex, 10 );
				}

				if (
					rfocusable.test( elem.nodeName ) ||
					rclickable.test( elem.nodeName ) &&
					elem.href
				) {
					return 0;
				}

				return -1;
			}
		}
	},

	propFix: {
		"for": "htmlFor",
		"class": "className"
	}
} );

// Support: IE <=11 only
// Accessing the selectedIndex property
// forces the browser to respect setting selected
// on the option
// The getter ensures a default option is selected
// when in an optgroup
// eslint rule "no-unused-expressions" is disabled for this code
// since it considers such accessions noop
if ( !support.optSelected ) {
	jQuery.propHooks.selected = {
		get: function( elem ) {

			/* eslint no-unused-expressions: "off" */

			var parent = elem.parentNode;
			if ( parent && parent.parentNode ) {
				parent.parentNode.selectedIndex;
			}
			return null;
		},
		set: function( elem ) {

			/* eslint no-unused-expressions: "off" */

			var parent = elem.parentNode;
			if ( parent ) {
				parent.selectedIndex;

				if ( parent.parentNode ) {
					parent.parentNode.selectedIndex;
				}
			}
		}
	};
}

jQuery.each( [
	"tabIndex",
	"readOnly",
	"maxLength",
	"cellSpacing",
	"cellPadding",
	"rowSpan",
	"colSpan",
	"useMap",
	"frameBorder",
	"contentEditable"
], function() {
	jQuery.propFix[ this.toLowerCase() ] = this;
} );




	// Strip and collapse whitespace according to HTML spec
	// https://infra.spec.whatwg.org/#strip-and-collapse-ascii-whitespace
	function stripAndCollapse( value ) {
		var tokens = value.match( rnothtmlwhite ) || [];
		return tokens.join( " " );
	}


function getClass( elem ) {
	return elem.getAttribute && elem.getAttribute( "class" ) || "";
}

function classesToArray( value ) {
	if ( Array.isArray( value ) ) {
		return value;
	}
	if ( typeof value === "string" ) {
		return value.match( rnothtmlwhite ) || [];
	}
	return [];
}

jQuery.fn.extend( {
	addClass: function( value ) {
		var classNames, cur, curValue, className, i, finalValue;

		if ( isFunction( value ) ) {
			return this.each( function( j ) {
				jQuery( this ).addClass( value.call( this, j, getClass( this ) ) );
			} );
		}

		classNames = classesToArray( value );

		if ( classNames.length ) {
			return this.each( function() {
				curValue = getClass( this );
				cur = this.nodeType === 1 && ( " " + stripAndCollapse( curValue ) + " " );

				if ( cur ) {
					for ( i = 0; i < classNames.length; i++ ) {
						className = classNames[ i ];
						if ( cur.indexOf( " " + className + " " ) < 0 ) {
							cur += className + " ";
						}
					}

					// Only assign if different to avoid unneeded rendering.
					finalValue = stripAndCollapse( cur );
					if ( curValue !== finalValue ) {
						this.setAttribute( "class", finalValue );
					}
				}
			} );
		}

		return this;
	},

	removeClass: function( value ) {
		var classNames, cur, curValue, className, i, finalValue;

		if ( isFunction( value ) ) {
			return this.each( function( j ) {
				jQuery( this ).removeClass( value.call( this, j, getClass( this ) ) );
			} );
		}

		if ( !arguments.length ) {
			return this.attr( "class", "" );
		}

		classNames = classesToArray( value );

		if ( classNames.length ) {
			return this.each( function() {
				curValue = getClass( this );

				// This expression is here for better compressibility (see addClass)
				cur = this.nodeType === 1 && ( " " + stripAndCollapse( curValue ) + " " );

				if ( cur ) {
					for ( i = 0; i < classNames.length; i++ ) {
						className = classNames[ i ];

						// Remove *all* instances
						while ( cur.indexOf( " " + className + " " ) > -1 ) {
							cur = cur.replace( " " + className + " ", " " );
						}
					}

					// Only assign if different to avoid unneeded rendering.
					finalValue = stripAndCollapse( cur );
					if ( curValue !== finalValue ) {
						this.setAttribute( "class", finalValue );
					}
				}
			} );
		}

		return this;
	},

	toggleClass: function( value, stateVal ) {
		var classNames, className, i, self,
			type = typeof value,
			isValidValue = type === "string" || Array.isArray( value );

		if ( isFunction( value ) ) {
			return this.each( function( i ) {
				jQuery( this ).toggleClass(
					value.call( this, i, getClass( this ), stateVal ),
					stateVal
				);
			} );
		}

		if ( typeof stateVal === "boolean" && isValidValue ) {
			return stateVal ? this.addClass( value ) : this.removeClass( value );
		}

		classNames = classesToArray( value );

		return this.each( function() {
			if ( isValidValue ) {

				// Toggle individual class names
				self = jQuery( this );

				for ( i = 0; i < classNames.length; i++ ) {
					className = classNames[ i ];

					// Check each className given, space separated list
					if ( self.hasClass( className ) ) {
						self.removeClass( className );
					} else {
						self.addClass( className );
					}
				}

			// Toggle whole class name
			} else if ( value === undefined || type === "boolean" ) {
				className = getClass( this );
				if ( className ) {

					// Store className if set
					dataPriv.set( this, "__className__", className );
				}

				// If the element has a class name or if we're passed `false`,
				// then remove the whole classname (if there was one, the above saved it).
				// Otherwise bring back whatever was previously saved (if anything),
				// falling back to the empty string if nothing was stored.
				if ( this.setAttribute ) {
					this.setAttribute( "class",
						className || value === false ?
							"" :
							dataPriv.get( this, "__className__" ) || ""
					);
				}
			}
		} );
	},

	hasClass: function( selector ) {
		var className, elem,
			i = 0;

		className = " " + selector + " ";
		while ( ( elem = this[ i++ ] ) ) {
			if ( elem.nodeType === 1 &&
				( " " + stripAndCollapse( getClass( elem ) ) + " " ).indexOf( className ) > -1 ) {
				return true;
			}
		}

		return false;
	}
} );




var rreturn = /\r/g;

jQuery.fn.extend( {
	val: function( value ) {
		var hooks, ret, valueIsFunction,
			elem = this[ 0 ];

		if ( !arguments.length ) {
			if ( elem ) {
				hooks = jQuery.valHooks[ elem.type ] ||
					jQuery.valHooks[ elem.nodeName.toLowerCase() ];

				if ( hooks &&
					"get" in hooks &&
					( ret = hooks.get( elem, "value" ) ) !== undefined
				) {
					return ret;
				}

				ret = elem.value;

				// Handle most common string cases
				if ( typeof ret === "string" ) {
					return ret.replace( rreturn, "" );
				}

				// Handle cases where value is null/undef or number
				return ret == null ? "" : ret;
			}

			return;
		}

		valueIsFunction = isFunction( value );

		return this.each( function( i ) {
			var val;

			if ( this.nodeType !== 1 ) {
				return;
			}

			if ( valueIsFunction ) {
				val = value.call( this, i, jQuery( this ).val() );
			} else {
				val = value;
			}

			// Treat null/undefined as ""; convert numbers to string
			if ( val == null ) {
				val = "";

			} else if ( typeof val === "number" ) {
				val += "";

			} else if ( Array.isArray( val ) ) {
				val = jQuery.map( val, function( value ) {
					return value == null ? "" : value + "";
				} );
			}

			hooks = jQuery.valHooks[ this.type ] || jQuery.valHooks[ this.nodeName.toLowerCase() ];

			// If set returns undefined, fall back to normal setting
			if ( !hooks || !( "set" in hooks ) || hooks.set( this, val, "value" ) === undefined ) {
				this.value = val;
			}
		} );
	}
} );

jQuery.extend( {
	valHooks: {
		option: {
			get: function( elem ) {

				var val = jQuery.find.attr( elem, "value" );
				return val != null ?
					val :

					// Support: IE <=10 - 11 only
					// option.text throws exceptions (trac-14686, trac-14858)
					// Strip and collapse whitespace
					// https://html.spec.whatwg.org/#strip-and-collapse-whitespace
					stripAndCollapse( jQuery.text( elem ) );
			}
		},
		select: {
			get: function( elem ) {
				var value, option, i,
					options = elem.options,
					index = elem.selectedIndex,
					one = elem.type === "select-one",
					values = one ? null : [],
					max = one ? index + 1 : options.length;

				if ( index < 0 ) {
					i = max;

				} else {
					i = one ? index : 0;
				}

				// Loop through all the selected options
				for ( ; i < max; i++ ) {
					option = options[ i ];

					// Support: IE <=9 only
					// IE8-9 doesn't update selected after form reset (trac-2551)
					if ( ( option.selected || i === index ) &&

							// Don't return options that are disabled or in a disabled optgroup
							!option.disabled &&
							( !option.parentNode.disabled ||
								!nodeName( option.parentNode, "optgroup" ) ) ) {

						// Get the specific value for the option
						value = jQuery( option ).val();

						// We don't need an array for one selects
						if ( one ) {
							return value;
						}

						// Multi-Selects return an array
						values.push( value );
					}
				}

				return values;
			},

			set: function( elem, value ) {
				var optionSet, option,
					options = elem.options,
					values = jQuery.makeArray( value ),
					i = options.length;

				while ( i-- ) {
					option = options[ i ];

					/* eslint-disable no-cond-assign */

					if ( option.selected =
						jQuery.inArray( jQuery.valHooks.option.get( option ), values ) > -1
					) {
						optionSet = true;
					}

					/* eslint-enable no-cond-assign */
				}

				// Force browsers to behave consistently when non-matching value is set
				if ( !optionSet ) {
					elem.selectedIndex = -1;
				}
				return values;
			}
		}
	}
} );

// Radios and checkboxes getter/setter
jQuery.each( [ "radio", "checkbox" ], function() {
	jQuery.valHooks[ this ] = {
		set: function( elem, value ) {
			if ( Array.isArray( value ) ) {
				return ( elem.checked = jQuery.inArray( jQuery( elem ).val(), value ) > -1 );
			}
		}
	};
	if ( !support.checkOn ) {
		jQuery.valHooks[ this ].get = function( elem ) {
			return elem.getAttribute( "value" ) === null ? "on" : elem.value;
		};
	}
} );




// Return jQuery for attributes-only inclusion
var location = window.location;

var nonce = { guid: Date.now() };

var rquery = ( /\?/ );



// Cross-browser xml parsing
jQuery.parseXML = function( data ) {
	var xml, parserErrorElem;
	if ( !data || typeof data !== "string" ) {
		return null;
	}

	// Support: IE 9 - 11 only
	// IE throws on parseFromString with invalid input.
	try {
		xml = ( new window.DOMParser() ).parseFromString( data, "text/xml" );
	} catch ( e ) {}

	parserErrorElem = xml && xml.getElementsByTagName( "parsererror" )[ 0 ];
	if ( !xml || parserErrorElem ) {
		jQuery.error( "Invalid XML: " + (
			parserErrorElem ?
				jQuery.map( parserErrorElem.childNodes, function( el ) {
					return el.textContent;
				} ).join( "\n" ) :
				data
		) );
	}
	return xml;
};


var rfocusMorph = /^(?:focusinfocus|focusoutblur)$/,
	stopPropagationCallback = function( e ) {
		e.stopPropagation();
	};

jQuery.extend( jQuery.event, {

	trigger: function( event, data, elem, onlyHandlers ) {

		var i, cur, tmp, bubbleType, ontype, handle, special, lastElement,
			eventPath = [ elem || document ],
			type = hasOwn.call( event, "type" ) ? event.type : event,
			namespaces = hasOwn.call( event, "namespace" ) ? event.namespace.split( "." ) : [];

		cur = lastElement = tmp = elem = elem || document;

		// Don't do events on text and comment nodes
		if ( elem.nodeType === 3 || elem.nodeType === 8 ) {
			return;
		}

		// focus/blur morphs to focusin/out; ensure we're not firing them right now
		if ( rfocusMorph.test( type + jQuery.event.triggered ) ) {
			return;
		}

		if ( type.indexOf( "." ) > -1 ) {

			// Namespaced trigger; create a regexp to match event type in handle()
			namespaces = type.split( "." );
			type = namespaces.shift();
			namespaces.sort();
		}
		ontype = type.indexOf( ":" ) < 0 && "on" + type;

		// Caller can pass in a jQuery.Event object, Object, or just an event type string
		event = event[ jQuery.expando ] ?
			event :
			new jQuery.Event( type, typeof event === "object" && event );

		// Trigger bitmask: & 1 for native handlers; & 2 for jQuery (always true)
		event.isTrigger = onlyHandlers ? 2 : 3;
		event.namespace = namespaces.join( "." );
		event.rnamespace = event.namespace ?
			new RegExp( "(^|\\.)" + namespaces.join( "\\.(?:.*\\.|)" ) + "(\\.|$)" ) :
			null;

		// Clean up the event in case it is being reused
		event.result = undefined;
		if ( !event.target ) {
			event.target = elem;
		}

		// Clone any incoming data and prepend the event, creating the handler arg list
		data = data == null ?
			[ event ] :
			jQuery.makeArray( data, [ event ] );

		// Allow special events to draw outside the lines
		special = jQuery.event.special[ type ] || {};
		if ( !onlyHandlers && special.trigger && special.trigger.apply( elem, data ) === false ) {
			return;
		}

		// Determine event propagation path in advance, per W3C events spec (trac-9951)
		// Bubble up to document, then to window; watch for a global ownerDocument var (trac-9724)
		if ( !onlyHandlers && !special.noBubble && !isWindow( elem ) ) {

			bubbleType = special.delegateType || type;
			if ( !rfocusMorph.test( bubbleType + type ) ) {
				cur = cur.parentNode;
			}
			for ( ; cur; cur = cur.parentNode ) {
				eventPath.push( cur );
				tmp = cur;
			}

			// Only add window if we got to document (e.g., not plain obj or detached DOM)
			if ( tmp === ( elem.ownerDocument || document ) ) {
				eventPath.push( tmp.defaultView || tmp.parentWindow || window );
			}
		}

		// Fire handlers on the event path
		i = 0;
		while ( ( cur = eventPath[ i++ ] ) && !event.isPropagationStopped() ) {
			lastElement = cur;
			event.type = i > 1 ?
				bubbleType :
				special.bindType || type;

			// jQuery handler
			handle = ( dataPriv.get( cur, "events" ) || Object.create( null ) )[ event.type ] &&
				dataPriv.get( cur, "handle" );
			if ( handle ) {
				handle.apply( cur, data );
			}

			// Native handler
			handle = ontype && cur[ ontype ];
			if ( handle && handle.apply && acceptData( cur ) ) {
				event.result = handle.apply( cur, data );
				if ( event.result === false ) {
					event.preventDefault();
				}
			}
		}
		event.type = type;

		// If nobody prevented the default action, do it now
		if ( !onlyHandlers && !event.isDefaultPrevented() ) {

			if ( ( !special._default ||
				special._default.apply( eventPath.pop(), data ) === false ) &&
				acceptData( elem ) ) {

				// Call a native DOM method on the target with the same name as the event.
				// Don't do default actions on window, that's where global variables be (trac-6170)
				if ( ontype && isFunction( elem[ type ] ) && !isWindow( elem ) ) {

					// Don't re-trigger an onFOO event when we call its FOO() method
					tmp = elem[ ontype ];

					if ( tmp ) {
						elem[ ontype ] = null;
					}

					// Prevent re-triggering of the same event, since we already bubbled it above
					jQuery.event.triggered = type;

					if ( event.isPropagationStopped() ) {
						lastElement.addEventListener( type, stopPropagationCallback );
					}

					elem[ type ]();

					if ( event.isPropagationStopped() ) {
						lastElement.removeEventListener( type, stopPropagationCallback );
					}

					jQuery.event.triggered = undefined;

					if ( tmp ) {
						elem[ ontype ] = tmp;
					}
				}
			}
		}

		return event.result;
	},

	// Piggyback on a donor event to simulate a different one
	// Used only for `focus(in | out)` events
	simulate: function( type, elem, event ) {
		var e = jQuery.extend(
			new jQuery.Event(),
			event,
			{
				type: type,
				isSimulated: true
			}
		);

		jQuery.event.trigger( e, null, elem );
	}

} );

jQuery.fn.extend( {

	trigger: function( type, data ) {
		return this.each( function() {
			jQuery.event.trigger( type, data, this );
		} );
	},
	triggerHandler: function( type, data ) {
		var elem = this[ 0 ];
		if ( elem ) {
			return jQuery.event.trigger( type, data, elem, true );
		}
	}
} );


var
	rbracket = /\[\]$/,
	rCRLF = /\r?\n/g,
	rsubmitterTypes = /^(?:submit|button|image|reset|file)$/i,
	rsubmittable = /^(?:input|select|textarea|keygen)/i;

function buildParams( prefix, obj, traditional, add ) {
	var name;

	if ( Array.isArray( obj ) ) {

		// Serialize array item.
		jQuery.each( obj, function( i, v ) {
			if ( traditional || rbracket.test( prefix ) ) {

				// Treat each array item as a scalar.
				add( prefix, v );

			} else {

				// Item is non-scalar (array or object), encode its numeric index.
				buildParams(
					prefix + "[" + ( typeof v === "object" && v != null ? i : "" ) + "]",
					v,
					traditional,
					add
				);
			}
		} );

	} else if ( !traditional && toType( obj ) === "object" ) {

		// Serialize object item.
		for ( name in obj ) {
			buildParams( prefix + "[" + name + "]", obj[ name ], traditional, add );
		}

	} else {

		// Serialize scalar item.
		add( prefix, obj );
	}
}

// Serialize an array of form elements or a set of
// key/values into a query string
jQuery.param = function( a, traditional ) {
	var prefix,
		s = [],
		add = function( key, valueOrFunction ) {

			// If value is a function, invoke it and use its return value
			var value = isFunction( valueOrFunction ) ?
				valueOrFunction() :
				valueOrFunction;

			s[ s.length ] = encodeURIComponent( key ) + "=" +
				encodeURIComponent( value == null ? "" : value );
		};

	if ( a == null ) {
		return "";
	}

	// If an array was passed in, assume that it is an array of form elements.
	if ( Array.isArray( a ) || ( a.jquery && !jQuery.isPlainObject( a ) ) ) {

		// Serialize the form elements
		jQuery.each( a, function() {
			add( this.name, this.value );
		} );

	} else {

		// If traditional, encode the "old" way (the way 1.3.2 or older
		// did it), otherwise encode params recursively.
		for ( prefix in a ) {
			buildParams( prefix, a[ prefix ], traditional, add );
		}
	}

	// Return the resulting serialization
	return s.join( "&" );
};

jQuery.fn.extend( {
	serialize: function() {
		return jQuery.param( this.serializeArray() );
	},
	serializeArray: function() {
		return this.map( function() {

			// Can add propHook for "elements" to filter or add form elements
			var elements = jQuery.prop( this, "elements" );
			return elements ? jQuery.makeArray( elements ) : this;
		} ).filter( function() {
			var type = this.type;

			// Use .is( ":disabled" ) so that fieldset[disabled] works
			return this.name && !jQuery( this ).is( ":disabled" ) &&
				rsubmittable.test( this.nodeName ) && !rsubmitterTypes.test( type ) &&
				( this.checked || !rcheckableType.test( type ) );
		} ).map( function( _i, elem ) {
			var val = jQuery( this ).val();

			if ( val == null ) {
				return null;
			}

			if ( Array.isArray( val ) ) {
				return jQuery.map( val, function( val ) {
					return { name: elem.name, value: val.replace( rCRLF, "\r\n" ) };
				} );
			}

			return { name: elem.name, value: val.replace( rCRLF, "\r\n" ) };
		} ).get();
	}
} );


var
	r20 = /%20/g,
	rhash = /#.*$/,
	rantiCache = /([?&])_=[^&]*/,
	rheaders = /^(.*?):[ \t]*([^\r\n]*)$/mg,

	// trac-7653, trac-8125, trac-8152: local protocol detection
	rlocalProtocol = /^(?:about|app|app-storage|.+-extension|file|res|widget):$/,
	rnoContent = /^(?:GET|HEAD)$/,
	rprotocol = /^\/\//,

	/* Prefilters
	 * 1) They are useful to introduce custom dataTypes (see ajax/jsonp.js for an example)
	 * 2) These are called:
	 *    - BEFORE asking for a transport
	 *    - AFTER param serialization (s.data is a string if s.processData is true)
	 * 3) key is the dataType
	 * 4) the catchall symbol "*" can be used
	 * 5) execution will start with transport dataType and THEN continue down to "*" if needed
	 */
	prefilters = {},

	/* Transports bindings
	 * 1) key is the dataType
	 * 2) the catchall symbol "*" can be used
	 * 3) selection will start with transport dataType and THEN go to "*" if needed
	 */
	transports = {},

	// Avoid comment-prolog char sequence (trac-10098); must appease lint and evade compression
	allTypes = "*/".concat( "*" ),

	// Anchor tag for parsing the document origin
	originAnchor = document.createElement( "a" );

originAnchor.href = location.href;

// Base "constructor" for jQuery.ajaxPrefilter and jQuery.ajaxTransport
function addToPrefiltersOrTransports( structure ) {

	// dataTypeExpression is optional and defaults to "*"
	return function( dataTypeExpression, func ) {

		if ( typeof dataTypeExpression !== "string" ) {
			func = dataTypeExpression;
			dataTypeExpression = "*";
		}

		var dataType,
			i = 0,
			dataTypes = dataTypeExpression.toLowerCase().match( rnothtmlwhite ) || [];

		if ( isFunction( func ) ) {

			// For each dataType in the dataTypeExpression
			while ( ( dataType = dataTypes[ i++ ] ) ) {

				// Prepend if requested
				if ( dataType[ 0 ] === "+" ) {
					dataType = dataType.slice( 1 ) || "*";
					( structure[ dataType ] = structure[ dataType ] || [] ).unshift( func );

				// Otherwise append
				} else {
					( structure[ dataType ] = structure[ dataType ] || [] ).push( func );
				}
			}
		}
	};
}

// Base inspection function for prefilters and transports
function inspectPrefiltersOrTransports( structure, options, originalOptions, jqXHR ) {

	var inspected = {},
		seekingTransport = ( structure === transports );

	function inspect( dataType ) {
		var selected;
		inspected[ dataType ] = true;
		jQuery.each( structure[ dataType ] || [], function( _, prefilterOrFactory ) {
			var dataTypeOrTransport = prefilterOrFactory( options, originalOptions, jqXHR );
			if ( typeof dataTypeOrTransport === "string" &&
				!seekingTransport && !inspected[ dataTypeOrTransport ] ) {

				options.dataTypes.unshift( dataTypeOrTransport );
				inspect( dataTypeOrTransport );
				return false;
			} else if ( seekingTransport ) {
				return !( selected = dataTypeOrTransport );
			}
		} );
		return selected;
	}

	return inspect( options.dataTypes[ 0 ] ) || !inspected[ "*" ] && inspect( "*" );
}

// A special extend for ajax options
// that takes "flat" options (not to be deep extended)
// Fixes trac-9887
function ajaxExtend( target, src ) {
	var key, deep,
		flatOptions = jQuery.ajaxSettings.flatOptions || {};

	for ( key in src ) {
		if ( src[ key ] !== undefined ) {
			( flatOptions[ key ] ? target : ( deep || ( deep = {} ) ) )[ key ] = src[ key ];
		}
	}
	if ( deep ) {
		jQuery.extend( true, target, deep );
	}

	return target;
}

/* Handles responses to an ajax request:
 * - finds the right dataType (mediates between content-type and expected dataType)
 * - returns the corresponding response
 */
function ajaxHandleResponses( s, jqXHR, responses ) {

	var ct, type, finalDataType, firstDataType,
		contents = s.contents,
		dataTypes = s.dataTypes;

	// Remove auto dataType and get content-type in the process
	while ( dataTypes[ 0 ] === "*" ) {
		dataTypes.shift();
		if ( ct === undefined ) {
			ct = s.mimeType || jqXHR.getResponseHeader( "Content-Type" );
		}
	}

	// Check if we're dealing with a known content-type
	if ( ct ) {
		for ( type in contents ) {
			if ( contents[ type ] && contents[ type ].test( ct ) ) {
				dataTypes.unshift( type );
				break;
			}
		}
	}

	// Check to see if we have a response for the expected dataType
	if ( dataTypes[ 0 ] in responses ) {
		finalDataType = dataTypes[ 0 ];
	} else {

		// Try convertible dataTypes
		for ( type in responses ) {
			if ( !dataTypes[ 0 ] || s.converters[ type + " " + dataTypes[ 0 ] ] ) {
				finalDataType = type;
				break;
			}
			if ( !firstDataType ) {
				firstDataType = type;
			}
		}

		// Or just use first one
		finalDataType = finalDataType || firstDataType;
	}

	// If we found a dataType
	// We add the dataType to the list if needed
	// and return the corresponding response
	if ( finalDataType ) {
		if ( finalDataType !== dataTypes[ 0 ] ) {
			dataTypes.unshift( finalDataType );
		}
		return responses[ finalDataType ];
	}
}

/* Chain conversions given the request and the original response
 * Also sets the responseXXX fields on the jqXHR instance
 */
function ajaxConvert( s, response, jqXHR, isSuccess ) {
	var conv2, current, conv, tmp, prev,
		converters = {},

		// Work with a copy of dataTypes in case we need to modify it for conversion
		dataTypes = s.dataTypes.slice();

	// Create converters map with lowercased keys
	if ( dataTypes[ 1 ] ) {
		for ( conv in s.converters ) {
			converters[ conv.toLowerCase() ] = s.converters[ conv ];
		}
	}

	current = dataTypes.shift();

	// Convert to each sequential dataType
	while ( current ) {

		if ( s.responseFields[ current ] ) {
			jqXHR[ s.responseFields[ current ] ] = response;
		}

		// Apply the dataFilter if provided
		if ( !prev && isSuccess && s.dataFilter ) {
			response = s.dataFilter( response, s.dataType );
		}

		prev = current;
		current = dataTypes.shift();

		if ( current ) {

			// There's only work to do if current dataType is non-auto
			if ( current === "*" ) {

				current = prev;

			// Convert response if prev dataType is non-auto and differs from current
			} else if ( prev !== "*" && prev !== current ) {

				// Seek a direct converter
				conv = converters[ prev + " " + current ] || converters[ "* " + current ];

				// If none found, seek a pair
				if ( !conv ) {
					for ( conv2 in converters ) {

						// If conv2 outputs current
						tmp = conv2.split( " " );
						if ( tmp[ 1 ] === current ) {

							// If prev can be converted to accepted input
							conv = converters[ prev + " " + tmp[ 0 ] ] ||
								converters[ "* " + tmp[ 0 ] ];
							if ( conv ) {

								// Condense equivalence converters
								if ( conv === true ) {
									conv = converters[ conv2 ];

								// Otherwise, insert the intermediate dataType
								} else if ( converters[ conv2 ] !== true ) {
									current = tmp[ 0 ];
									dataTypes.unshift( tmp[ 1 ] );
								}
								break;
							}
						}
					}
				}

				// Apply converter (if not an equivalence)
				if ( conv !== true ) {

					// Unless errors are allowed to bubble, catch and return them
					if ( conv && s.throws ) {
						response = conv( response );
					} else {
						try {
							response = conv( response );
						} catch ( e ) {
							return {
								state: "parsererror",
								error: conv ? e : "No conversion from " + prev + " to " + current
							};
						}
					}
				}
			}
		}
	}

	return { state: "success", data: response };
}

jQuery.extend( {

	// Counter for holding the number of active queries
	active: 0,

	// Last-Modified header cache for next request
	lastModified: {},
	etag: {},

	ajaxSettings: {
		url: location.href,
		type: "GET",
		isLocal: rlocalProtocol.test( location.protocol ),
		global: true,
		processData: true,
		async: true,
		contentType: "application/x-www-form-urlencoded; charset=UTF-8",

		/*
		timeout: 0,
		data: null,
		dataType: null,
		username: null,
		password: null,
		cache: null,
		throws: false,
		traditional: false,
		headers: {},
		*/

		accepts: {
			"*": allTypes,
			text: "text/plain",
			html: "text/html",
			xml: "application/xml, text/xml",
			json: "application/json, text/javascript"
		},

		contents: {
			xml: /\bxml\b/,
			html: /\bhtml/,
			json: /\bjson\b/
		},

		responseFields: {
			xml: "responseXML",
			text: "responseText",
			json: "responseJSON"
		},

		// Data converters
		// Keys separate source (or catchall "*") and destination types with a single space
		converters: {

			// Convert anything to text
			"* text": String,

			// Text to html (true = no transformation)
			"text html": true,

			// Evaluate text as a json expression
			"text json": JSON.parse,

			// Parse text as xml
			"text xml": jQuery.parseXML
		},

		// For options that shouldn't be deep extended:
		// you can add your own custom options here if
		// and when you create one that shouldn't be
		// deep extended (see ajaxExtend)
		flatOptions: {
			url: true,
			context: true
		}
	},

	// Creates a full fledged settings object into target
	// with both ajaxSettings and settings fields.
	// If target is omitted, writes into ajaxSettings.
	ajaxSetup: function( target, settings ) {
		return settings ?

			// Building a settings object
			ajaxExtend( ajaxExtend( target, jQuery.ajaxSettings ), settings ) :

			// Extending ajaxSettings
			ajaxExtend( jQuery.ajaxSettings, target );
	},

	ajaxPrefilter: addToPrefiltersOrTransports( prefilters ),
	ajaxTransport: addToPrefiltersOrTransports( transports ),

	// Main method
	ajax: function( url, options ) {

		// If url is an object, simulate pre-1.5 signature
		if ( typeof url === "object" ) {
			options = url;
			url = undefined;
		}

		// Force options to be an object
		options = options || {};

		var transport,

			// URL without anti-cache param
			cacheURL,

			// Response headers
			responseHeadersString,
			responseHeaders,

			// timeout handle
			timeoutTimer,

			// Url cleanup var
			urlAnchor,

			// Request state (becomes false upon send and true upon completion)
			completed,

			// To know if global events are to be dispatched
			fireGlobals,

			// Loop variable
			i,

			// uncached part of the url
			uncached,

			// Create the final options object
			s = jQuery.ajaxSetup( {}, options ),

			// Callbacks context
			callbackContext = s.context || s,

			// Context for global events is callbackContext if it is a DOM node or jQuery collection
			globalEventContext = s.context &&
				( callbackContext.nodeType || callbackContext.jquery ) ?
				jQuery( callbackContext ) :
				jQuery.event,

			// Deferreds
			deferred = jQuery.Deferred(),
			completeDeferred = jQuery.Callbacks( "once memory" ),

			// Status-dependent callbacks
			statusCode = s.statusCode || {},

			// Headers (they are sent all at once)
			requestHeaders = {},
			requestHeadersNames = {},

			// Default abort message
			strAbort = "canceled",

			// Fake xhr
			jqXHR = {
				readyState: 0,

				// Builds headers hashtable if needed
				getResponseHeader: function( key ) {
					var match;
					if ( completed ) {
						if ( !responseHeaders ) {
							responseHeaders = {};
							while ( ( match = rheaders.exec( responseHeadersString ) ) ) {
								responseHeaders[ match[ 1 ].toLowerCase() + " " ] =
									( responseHeaders[ match[ 1 ].toLowerCase() + " " ] || [] )
										.concat( match[ 2 ] );
							}
						}
						match = responseHeaders[ key.toLowerCase() + " " ];
					}
					return match == null ? null : match.join( ", " );
				},

				// Raw string
				getAllResponseHeaders: function() {
					return completed ? responseHeadersString : null;
				},

				// Caches the header
				setRequestHeader: function( name, value ) {
					if ( completed == null ) {
						name = requestHeadersNames[ name.toLowerCase() ] =
							requestHeadersNames[ name.toLowerCase() ] || name;
						requestHeaders[ name ] = value;
					}
					return this;
				},

				// Overrides response content-type header
				overrideMimeType: function( type ) {
					if ( completed == null ) {
						s.mimeType = type;
					}
					return this;
				},

				// Status-dependent callbacks
				statusCode: function( map ) {
					var code;
					if ( map ) {
						if ( completed ) {

							// Execute the appropriate callbacks
							jqXHR.always( map[ jqXHR.status ] );
						} else {

							// Lazy-add the new callbacks in a way that preserves old ones
							for ( code in map ) {
								statusCode[ code ] = [ statusCode[ code ], map[ code ] ];
							}
						}
					}
					return this;
				},

				// Cancel the request
				abort: function( statusText ) {
					var finalText = statusText || strAbort;
					if ( transport ) {
						transport.abort( finalText );
					}
					done( 0, finalText );
					return this;
				}
			};

		// Attach deferreds
		deferred.promise( jqXHR );

		// Add protocol if not provided (prefilters might expect it)
		// Handle falsy url in the settings object (trac-10093: consistency with old signature)
		// We also use the url parameter if available
		s.url = ( ( url || s.url || location.href ) + "" )
			.replace( rprotocol, location.protocol + "//" );

		// Alias method option to type as per ticket trac-12004
		s.type = options.method || options.type || s.method || s.type;

		// Extract dataTypes list
		s.dataTypes = ( s.dataType || "*" ).toLowerCase().match( rnothtmlwhite ) || [ "" ];

		// A cross-domain request is in order when the origin doesn't match the current origin.
		if ( s.crossDomain == null ) {
			urlAnchor = document.createElement( "a" );

			// Support: IE <=8 - 11, Edge 12 - 15
			// IE throws exception on accessing the href property if url is malformed,
			// e.g. http://example.com:80x/
			try {
				urlAnchor.href = s.url;

				// Support: IE <=8 - 11 only
				// Anchor's host property isn't correctly set when s.url is relative
				urlAnchor.href = urlAnchor.href;
				s.crossDomain = originAnchor.protocol + "//" + originAnchor.host !==
					urlAnchor.protocol + "//" + urlAnchor.host;
			} catch ( e ) {

				// If there is an error parsing the URL, assume it is crossDomain,
				// it can be rejected by the transport if it is invalid
				s.crossDomain = true;
			}
		}

		// Convert data if not already a string
		if ( s.data && s.processData && typeof s.data !== "string" ) {
			s.data = jQuery.param( s.data, s.traditional );
		}

		// Apply prefilters
		inspectPrefiltersOrTransports( prefilters, s, options, jqXHR );

		// If request was aborted inside a prefilter, stop there
		if ( completed ) {
			return jqXHR;
		}

		// We can fire global events as of now if asked to
		// Don't fire events if jQuery.event is undefined in an AMD-usage scenario (trac-15118)
		fireGlobals = jQuery.event && s.global;

		// Watch for a new set of requests
		if ( fireGlobals && jQuery.active++ === 0 ) {
			jQuery.event.trigger( "ajaxStart" );
		}

		// Uppercase the type
		s.type = s.type.toUpperCase();

		// Determine if request has content
		s.hasContent = !rnoContent.test( s.type );

		// Save the URL in case we're toying with the If-Modified-Since
		// and/or If-None-Match header later on
		// Remove hash to simplify url manipulation
		cacheURL = s.url.replace( rhash, "" );

		// More options handling for requests with no content
		if ( !s.hasContent ) {

			// Remember the hash so we can put it back
			uncached = s.url.slice( cacheURL.length );

			// If data is available and should be processed, append data to url
			if ( s.data && ( s.processData || typeof s.data === "string" ) ) {
				cacheURL += ( rquery.test( cacheURL ) ? "&" : "?" ) + s.data;

				// trac-9682: remove data so that it's not used in an eventual retry
				delete s.data;
			}

			// Add or update anti-cache param if needed
			if ( s.cache === false ) {
				cacheURL = cacheURL.replace( rantiCache, "$1" );
				uncached = ( rquery.test( cacheURL ) ? "&" : "?" ) + "_=" + ( nonce.guid++ ) +
					uncached;
			}

			// Put hash and anti-cache on the URL that will be requested (gh-1732)
			s.url = cacheURL + uncached;

		// Change '%20' to '+' if this is encoded form body content (gh-2658)
		} else if ( s.data && s.processData &&
			( s.contentType || "" ).indexOf( "application/x-www-form-urlencoded" ) === 0 ) {
			s.data = s.data.replace( r20, "+" );
		}

		// Set the If-Modified-Since and/or If-None-Match header, if in ifModified mode.
		if ( s.ifModified ) {
			if ( jQuery.lastModified[ cacheURL ] ) {
				jqXHR.setRequestHeader( "If-Modified-Since", jQuery.lastModified[ cacheURL ] );
			}
			if ( jQuery.etag[ cacheURL ] ) {
				jqXHR.setRequestHeader( "If-None-Match", jQuery.etag[ cacheURL ] );
			}
		}

		// Set the correct header, if data is being sent
		if ( s.data && s.hasContent && s.contentType !== false || options.contentType ) {
			jqXHR.setRequestHeader( "Content-Type", s.contentType );
		}

		// Set the Accepts header for the server, depending on the dataType
		jqXHR.setRequestHeader(
			"Accept",
			s.dataTypes[ 0 ] && s.accepts[ s.dataTypes[ 0 ] ] ?
				s.accepts[ s.dataTypes[ 0 ] ] +
					( s.dataTypes[ 0 ] !== "*" ? ", " + allTypes + "; q=0.01" : "" ) :
				s.accepts[ "*" ]
		);

		// Check for headers option
		for ( i in s.headers ) {
			jqXHR.setRequestHeader( i, s.headers[ i ] );
		}

		// Allow custom headers/mimetypes and early abort
		if ( s.beforeSend &&
			( s.beforeSend.call( callbackContext, jqXHR, s ) === false || completed ) ) {

			// Abort if not done already and return
			return jqXHR.abort();
		}

		// Aborting is no longer a cancellation
		strAbort = "abort";

		// Install callbacks on deferreds
		completeDeferred.add( s.complete );
		jqXHR.done( s.success );
		jqXHR.fail( s.error );

		// Get transport
		transport = inspectPrefiltersOrTransports( transports, s, options, jqXHR );

		// If no transport, we auto-abort
		if ( !transport ) {
			done( -1, "No Transport" );
		} else {
			jqXHR.readyState = 1;

			// Send global event
			if ( fireGlobals ) {
				globalEventContext.trigger( "ajaxSend", [ jqXHR, s ] );
			}

			// If request was aborted inside ajaxSend, stop there
			if ( completed ) {
				return jqXHR;
			}

			// Timeout
			if ( s.async && s.timeout > 0 ) {
				timeoutTimer = window.setTimeout( function() {
					jqXHR.abort( "timeout" );
				}, s.timeout );
			}

			try {
				completed = false;
				transport.send( requestHeaders, done );
			} catch ( e ) {

				// Rethrow post-completion exceptions
				if ( completed ) {
					throw e;
				}

				// Propagate others as results
				done( -1, e );
			}
		}

		// Callback for when everything is done
		function done( status, nativeStatusText, responses, headers ) {
			var isSuccess, success, error, response, modified,
				statusText = nativeStatusText;

			// Ignore repeat invocations
			if ( completed ) {
				return;
			}

			completed = true;

			// Clear timeout if it exists
			if ( timeoutTimer ) {
				window.clearTimeout( timeoutTimer );
			}

			// Dereference transport for early garbage collection
			// (no matter how long the jqXHR object will be used)
			transport = undefined;

			// Cache response headers
			responseHeadersString = headers || "";

			// Set readyState
			jqXHR.readyState = status > 0 ? 4 : 0;

			// Determine if successful
			isSuccess = status >= 200 && status < 300 || status === 304;

			// Get response data
			if ( responses ) {
				response = ajaxHandleResponses( s, jqXHR, responses );
			}

			// Use a noop converter for missing script but not if jsonp
			if ( !isSuccess &&
				jQuery.inArray( "script", s.dataTypes ) > -1 &&
				jQuery.inArray( "json", s.dataTypes ) < 0 ) {
				s.converters[ "text script" ] = function() {};
			}

			// Convert no matter what (that way responseXXX fields are always set)
			response = ajaxConvert( s, response, jqXHR, isSuccess );

			// If successful, handle type chaining
			if ( isSuccess ) {

				// Set the If-Modified-Since and/or If-None-Match header, if in ifModified mode.
				if ( s.ifModified ) {
					modified = jqXHR.getResponseHeader( "Last-Modified" );
					if ( modified ) {
						jQuery.lastModified[ cacheURL ] = modified;
					}
					modified = jqXHR.getResponseHeader( "etag" );
					if ( modified ) {
						jQuery.etag[ cacheURL ] = modified;
					}
				}

				// if no content
				if ( status === 204 || s.type === "HEAD" ) {
					statusText = "nocontent";

				// if not modified
				} else if ( status === 304 ) {
					statusText = "notmodified";

				// If we have data, let's convert it
				} else {
					statusText = response.state;
					success = response.data;
					error = response.error;
					isSuccess = !error;
				}
			} else {

				// Extract error from statusText and normalize for non-aborts
				error = statusText;
				if ( status || !statusText ) {
					statusText = "error";
					if ( status < 0 ) {
						status = 0;
					}
				}
			}

			// Set data for the fake xhr object
			jqXHR.status = status;
			jqXHR.statusText = ( nativeStatusText || statusText ) + "";

			// Success/Error
			if ( isSuccess ) {
				deferred.resolveWith( callbackContext, [ success, statusText, jqXHR ] );
			} else {
				deferred.rejectWith( callbackContext, [ jqXHR, statusText, error ] );
			}

			// Status-dependent callbacks
			jqXHR.statusCode( statusCode );
			statusCode = undefined;

			if ( fireGlobals ) {
				globalEventContext.trigger( isSuccess ? "ajaxSuccess" : "ajaxError",
					[ jqXHR, s, isSuccess ? success : error ] );
			}

			// Complete
			completeDeferred.fireWith( callbackContext, [ jqXHR, statusText ] );

			if ( fireGlobals ) {
				globalEventContext.trigger( "ajaxComplete", [ jqXHR, s ] );

				// Handle the global AJAX counter
				if ( !( --jQuery.active ) ) {
					jQuery.event.trigger( "ajaxStop" );
				}
			}
		}

		return jqXHR;
	},

	getJSON: function( url, data, callback ) {
		return jQuery.get( url, data, callback, "json" );
	},

	getScript: function( url, callback ) {
		return jQuery.get( url, undefined, callback, "script" );
	}
} );

jQuery.each( [ "get", "post" ], function( _i, method ) {
	jQuery[ method ] = function( url, data, callback, type ) {

		// Shift arguments if data argument was omitted
		if ( isFunction( data ) ) {
			type = type || callback;
			callback = data;
			data = undefined;
		}

		// The url can be an options object (which then must have .url)
		return jQuery.ajax( jQuery.extend( {
			url: url,
			type: method,
			dataType: type,
			data: data,
			success: callback
		}, jQuery.isPlainObject( url ) && url ) );
	};
} );

jQuery.ajaxPrefilter( function( s ) {
	var i;
	for ( i in s.headers ) {
		if ( i.toLowerCase() === "content-type" ) {
			s.contentType = s.headers[ i ] || "";
		}
	}
} );


jQuery._evalUrl = function( url, options, doc ) {
	return jQuery.ajax( {
		url: url,

		// Make this explicit, since user can override this through ajaxSetup (trac-11264)
		type: "GET",
		dataType: "script",
		cache: true,
		async: false,
		global: false,

		// Only evaluate the response if it is successful (gh-4126)
		// dataFilter is not invoked for failure responses, so using it instead
		// of the default converter is kludgy but it works.
		converters: {
			"text script": function() {}
		},
		dataFilter: function( response ) {
			jQuery.globalEval( response, options, doc );
		}
	} );
};


jQuery.fn.extend( {
	wrapAll: function( html ) {
		var wrap;

		if ( this[ 0 ] ) {
			if ( isFunction( html ) ) {
				html = html.call( this[ 0 ] );
			}

			// The elements to wrap the target around
			wrap = jQuery( html, this[ 0 ].ownerDocument ).eq( 0 ).clone( true );

			if ( this[ 0 ].parentNode ) {
				wrap.insertBefore( this[ 0 ] );
			}

			wrap.map( function() {
				var elem = this;

				while ( elem.firstElementChild ) {
					elem = elem.firstElementChild;
				}

				return elem;
			} ).append( this );
		}

		return this;
	},

	wrapInner: function( html ) {
		if ( isFunction( html ) ) {
			return this.each( function( i ) {
				jQuery( this ).wrapInner( html.call( this, i ) );
			} );
		}

		return this.each( function() {
			var self = jQuery( this ),
				contents = self.contents();

			if ( contents.length ) {
				contents.wrapAll( html );

			} else {
				self.append( html );
			}
		} );
	},

	wrap: function( html ) {
		var htmlIsFunction = isFunction( html );

		return this.each( function( i ) {
			jQuery( this ).wrapAll( htmlIsFunction ? html.call( this, i ) : html );
		} );
	},

	unwrap: function( selector ) {
		this.parent( selector ).not( "body" ).each( function() {
			jQuery( this ).replaceWith( this.childNodes );
		} );
		return this;
	}
} );


jQuery.expr.pseudos.hidden = function( elem ) {
	return !jQuery.expr.pseudos.visible( elem );
};
jQuery.expr.pseudos.visible = function( elem ) {
	return !!( elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length );
};




jQuery.ajaxSettings.xhr = function() {
	try {
		return new window.XMLHttpRequest();
	} catch ( e ) {}
};

var xhrSuccessStatus = {

		// File protocol always yields status code 0, assume 200
		0: 200,

		// Support: IE <=9 only
		// trac-1450: sometimes IE returns 1223 when it should be 204
		1223: 204
	},
	xhrSupported = jQuery.ajaxSettings.xhr();

support.cors = !!xhrSupported && ( "withCredentials" in xhrSupported );
support.ajax = xhrSupported = !!xhrSupported;

jQuery.ajaxTransport( function( options ) {
	var callback, errorCallback;

	// Cross domain only allowed if supported through XMLHttpRequest
	if ( support.cors || xhrSupported && !options.crossDomain ) {
		return {
			send: function( headers, complete ) {
				var i,
					xhr = options.xhr();

				xhr.open(
					options.type,
					options.url,
					options.async,
					options.username,
					options.password
				);

				// Apply custom fields if provided
				if ( options.xhrFields ) {
					for ( i in options.xhrFields ) {
						xhr[ i ] = options.xhrFields[ i ];
					}
				}

				// Override mime type if needed
				if ( options.mimeType && xhr.overrideMimeType ) {
					xhr.overrideMimeType( options.mimeType );
				}

				// X-Requested-With header
				// For cross-domain requests, seeing as conditions for a preflight are
				// akin to a jigsaw puzzle, we simply never set it to be sure.
				// (it can always be set on a per-request basis or even using ajaxSetup)
				// For same-domain requests, won't change header if already provided.
				if ( !options.crossDomain && !headers[ "X-Requested-With" ] ) {
					headers[ "X-Requested-With" ] = "XMLHttpRequest";
				}

				// Set headers
				for ( i in headers ) {
					xhr.setRequestHeader( i, headers[ i ] );
				}

				// Callback
				callback = function( type ) {
					return function() {
						if ( callback ) {
							callback = errorCallback = xhr.onload =
								xhr.onerror = xhr.onabort = xhr.ontimeout =
									xhr.onreadystatechange = null;

							if ( type === "abort" ) {
								xhr.abort();
							} else if ( type === "error" ) {

								// Support: IE <=9 only
								// On a manual native abort, IE9 throws
								// errors on any property access that is not readyState
								if ( typeof xhr.status !== "number" ) {
									complete( 0, "error" );
								} else {
									complete(

										// File: protocol always yields status 0; see trac-8605, trac-14207
										xhr.status,
										xhr.statusText
									);
								}
							} else {
								complete(
									xhrSuccessStatus[ xhr.status ] || xhr.status,
									xhr.statusText,

									// Support: IE <=9 only
									// IE9 has no XHR2 but throws on binary (trac-11426)
									// For XHR2 non-text, let the caller handle it (gh-2498)
									( xhr.responseType || "text" ) !== "text"  ||
									typeof xhr.responseText !== "string" ?
										{ binary: xhr.response } :
										{ text: xhr.responseText },
									xhr.getAllResponseHeaders()
								);
							}
						}
					};
				};

				// Listen to events
				xhr.onload = callback();
				errorCallback = xhr.onerror = xhr.ontimeout = callback( "error" );

				// Support: IE 9 only
				// Use onreadystatechange to replace onabort
				// to handle uncaught aborts
				if ( xhr.onabort !== undefined ) {
					xhr.onabort = errorCallback;
				} else {
					xhr.onreadystatechange = function() {

						// Check readyState before timeout as it changes
						if ( xhr.readyState === 4 ) {

							// Allow onerror to be called first,
							// but that will not handle a native abort
							// Also, save errorCallback to a variable
							// as xhr.onerror cannot be accessed
							window.setTimeout( function() {
								if ( callback ) {
									errorCallback();
								}
							} );
						}
					};
				}

				// Create the abort callback
				callback = callback( "abort" );

				try {

					// Do send the request (this may raise an exception)
					xhr.send( options.hasContent && options.data || null );
				} catch ( e ) {

					// trac-14683: Only rethrow if this hasn't been notified as an error yet
					if ( callback ) {
						throw e;
					}
				}
			},

			abort: function() {
				if ( callback ) {
					callback();
				}
			}
		};
	}
} );




// Prevent auto-execution of scripts when no explicit dataType was provided (See gh-2432)
jQuery.ajaxPrefilter( function( s ) {
	if ( s.crossDomain ) {
		s.contents.script = false;
	}
} );

// Install script dataType
jQuery.ajaxSetup( {
	accepts: {
		script: "text/javascript, application/javascript, " +
			"application/ecmascript, application/x-ecmascript"
	},
	contents: {
		script: /\b(?:java|ecma)script\b/
	},
	converters: {
		"text script": function( text ) {
			jQuery.globalEval( text );
			return text;
		}
	}
} );

// Handle cache's special case and crossDomain
jQuery.ajaxPrefilter( "script", function( s ) {
	if ( s.cache === undefined ) {
		s.cache = false;
	}
	if ( s.crossDomain ) {
		s.type = "GET";
	}
} );

// Bind script tag hack transport
jQuery.ajaxTransport( "script", function( s ) {

	// This transport only deals with cross domain or forced-by-attrs requests
	if ( s.crossDomain || s.scriptAttrs ) {
		var script, callback;
		return {
			send: function( _, complete ) {
				script = jQuery( "<script>" )
					.attr( s.scriptAttrs || {} )
					.prop( { charset: s.scriptCharset, src: s.url } )
					.on( "load error", callback = function( evt ) {
						script.remove();
						callback = null;
						if ( evt ) {
							complete( evt.type === "error" ? 404 : 200, evt.type );
						}
					} );

				// Use native DOM manipulation to avoid our domManip AJAX trickery
				document.head.appendChild( script[ 0 ] );
			},
			abort: function() {
				if ( callback ) {
					callback();
				}
			}
		};
	}
} );




var oldCallbacks = [],
	rjsonp = /(=)\?(?=&|$)|\?\?/;

// Default jsonp settings
jQuery.ajaxSetup( {
	jsonp: "callback",
	jsonpCallback: function() {
		var callback = oldCallbacks.pop() || ( jQuery.expando + "_" + ( nonce.guid++ ) );
		this[ callback ] = true;
		return callback;
	}
} );

// Detect, normalize options and install callbacks for jsonp requests
jQuery.ajaxPrefilter( "json jsonp", function( s, originalSettings, jqXHR ) {

	var callbackName, overwritten, responseContainer,
		jsonProp = s.jsonp !== false && ( rjsonp.test( s.url ) ?
			"url" :
			typeof s.data === "string" &&
				( s.contentType || "" )
					.indexOf( "application/x-www-form-urlencoded" ) === 0 &&
				rjsonp.test( s.data ) && "data"
		);

	// Handle iff the expected data type is "jsonp" or we have a parameter to set
	if ( jsonProp || s.dataTypes[ 0 ] === "jsonp" ) {

		// Get callback name, remembering preexisting value associated with it
		callbackName = s.jsonpCallback = isFunction( s.jsonpCallback ) ?
			s.jsonpCallback() :
			s.jsonpCallback;

		// Insert callback into url or form data
		if ( jsonProp ) {
			s[ jsonProp ] = s[ jsonProp ].replace( rjsonp, "$1" + callbackName );
		} else if ( s.jsonp !== false ) {
			s.url += ( rquery.test( s.url ) ? "&" : "?" ) + s.jsonp + "=" + callbackName;
		}

		// Use data converter to retrieve json after script execution
		s.converters[ "script json" ] = function() {
			if ( !responseContainer ) {
				jQuery.error( callbackName + " was not called" );
			}
			return responseContainer[ 0 ];
		};

		// Force json dataType
		s.dataTypes[ 0 ] = "json";

		// Install callback
		overwritten = window[ callbackName ];
		window[ callbackName ] = function() {
			responseContainer = arguments;
		};

		// Clean-up function (fires after converters)
		jqXHR.always( function() {

			// If previous value didn't exist - remove it
			if ( overwritten === undefined ) {
				jQuery( window ).removeProp( callbackName );

			// Otherwise restore preexisting value
			} else {
				window[ callbackName ] = overwritten;
			}

			// Save back as free
			if ( s[ callbackName ] ) {

				// Make sure that re-using the options doesn't screw things around
				s.jsonpCallback = originalSettings.jsonpCallback;

				// Save the callback name for future use
				oldCallbacks.push( callbackName );
			}

			// Call if it was a function and we have a response
			if ( responseContainer && isFunction( overwritten ) ) {
				overwritten( responseContainer[ 0 ] );
			}

			responseContainer = overwritten = undefined;
		} );

		// Delegate to script
		return "script";
	}
} );




// Support: Safari 8 only
// In Safari 8 documents created via document.implementation.createHTMLDocument
// collapse sibling forms: the second one becomes a child of the first one.
// Because of that, this security measure has to be disabled in Safari 8.
// https://bugs.webkit.org/show_bug.cgi?id=137337
support.createHTMLDocument = ( function() {
	var body = document.implementation.createHTMLDocument( "" ).body;
	body.innerHTML = "<form></form><form></form>";
	return body.childNodes.length === 2;
} )();


// Argument "data" should be string of html
// context (optional): If specified, the fragment will be created in this context,
// defaults to document
// keepScripts (optional): If true, will include scripts passed in the html string
jQuery.parseHTML = function( data, context, keepScripts ) {
	if ( typeof data !== "string" ) {
		return [];
	}
	if ( typeof context === "boolean" ) {
		keepScripts = context;
		context = false;
	}

	var base, parsed, scripts;

	if ( !context ) {

		// Stop scripts or inline event handlers from being executed immediately
		// by using document.implementation
		if ( support.createHTMLDocument ) {
			context = document.implementation.createHTMLDocument( "" );

			// Set the base href for the created document
			// so any parsed elements with URLs
			// are based on the document's URL (gh-2965)
			base = context.createElement( "base" );
			base.href = document.location.href;
			context.head.appendChild( base );
		} else {
			context = document;
		}
	}

	parsed = rsingleTag.exec( data );
	scripts = !keepScripts && [];

	// Single tag
	if ( parsed ) {
		return [ context.createElement( parsed[ 1 ] ) ];
	}

	parsed = buildFragment( [ data ], context, scripts );

	if ( scripts && scripts.length ) {
		jQuery( scripts ).remove();
	}

	return jQuery.merge( [], parsed.childNodes );
};


/**
 * Load a url into a page
 */
jQuery.fn.load = function( url, params, callback ) {
	var selector, type, response,
		self = this,
		off = url.indexOf( " " );

	if ( off > -1 ) {
		selector = stripAndCollapse( url.slice( off ) );
		url = url.slice( 0, off );
	}

	// If it's a function
	if ( isFunction( params ) ) {

		// We assume that it's the callback
		callback = params;
		params = undefined;

	// Otherwise, build a param string
	} else if ( params && typeof params === "object" ) {
		type = "POST";
	}

	// If we have elements to modify, make the request
	if ( self.length > 0 ) {
		jQuery.ajax( {
			url: url,

			// If "type" variable is undefined, then "GET" method will be used.
			// Make value of this field explicit since
			// user can override it through ajaxSetup method
			type: type || "GET",
			dataType: "html",
			data: params
		} ).done( function( responseText ) {

			// Save response for use in complete callback
			response = arguments;

			self.html( selector ?

				// If a selector was specified, locate the right elements in a dummy div
				// Exclude scripts to avoid IE 'Permission Denied' errors
				jQuery( "<div>" ).append( jQuery.parseHTML( responseText ) ).find( selector ) :

				// Otherwise use the full result
				responseText );

		// If the request succeeds, this function gets "data", "status", "jqXHR"
		// but they are ignored because response was set above.
		// If it fails, this function gets "jqXHR", "status", "error"
		} ).always( callback && function( jqXHR, status ) {
			self.each( function() {
				callback.apply( this, response || [ jqXHR.responseText, status, jqXHR ] );
			} );
		} );
	}

	return this;
};




jQuery.expr.pseudos.animated = function( elem ) {
	return jQuery.grep( jQuery.timers, function( fn ) {
		return elem === fn.elem;
	} ).length;
};




jQuery.offset = {
	setOffset: function( elem, options, i ) {
		var curPosition, curLeft, curCSSTop, curTop, curOffset, curCSSLeft, calculatePosition,
			position = jQuery.css( elem, "position" ),
			curElem = jQuery( elem ),
			props = {};

		// Set position first, in-case top/left are set even on static elem
		if ( position === "static" ) {
			elem.style.position = "relative";
		}

		curOffset = curElem.offset();
		curCSSTop = jQuery.css( elem, "top" );
		curCSSLeft = jQuery.css( elem, "left" );
		calculatePosition = ( position === "absolute" || position === "fixed" ) &&
			( curCSSTop + curCSSLeft ).indexOf( "auto" ) > -1;

		// Need to be able to calculate position if either
		// top or left is auto and position is either absolute or fixed
		if ( calculatePosition ) {
			curPosition = curElem.position();
			curTop = curPosition.top;
			curLeft = curPosition.left;

		} else {
			curTop = parseFloat( curCSSTop ) || 0;
			curLeft = parseFloat( curCSSLeft ) || 0;
		}

		if ( isFunction( options ) ) {

			// Use jQuery.extend here to allow modification of coordinates argument (gh-1848)
			options = options.call( elem, i, jQuery.extend( {}, curOffset ) );
		}

		if ( options.top != null ) {
			props.top = ( options.top - curOffset.top ) + curTop;
		}
		if ( options.left != null ) {
			props.left = ( options.left - curOffset.left ) + curLeft;
		}

		if ( "using" in options ) {
			options.using.call( elem, props );

		} else {
			curElem.css( props );
		}
	}
};

jQuery.fn.extend( {

	// offset() relates an element's border box to the document origin
	offset: function( options ) {

		// Preserve chaining for setter
		if ( arguments.length ) {
			return options === undefined ?
				this :
				this.each( function( i ) {
					jQuery.offset.setOffset( this, options, i );
				} );
		}

		var rect, win,
			elem = this[ 0 ];

		if ( !elem ) {
			return;
		}

		// Return zeros for disconnected and hidden (display: none) elements (gh-2310)
		// Support: IE <=11 only
		// Running getBoundingClientRect on a
		// disconnected node in IE throws an error
		if ( !elem.getClientRects().length ) {
			return { top: 0, left: 0 };
		}

		// Get document-relative position by adding viewport scroll to viewport-relative gBCR
		rect = elem.getBoundingClientRect();
		win = elem.ownerDocument.defaultView;
		return {
			top: rect.top + win.pageYOffset,
			left: rect.left + win.pageXOffset
		};
	},

	// position() relates an element's margin box to its offset parent's padding box
	// This corresponds to the behavior of CSS absolute positioning
	position: function() {
		if ( !this[ 0 ] ) {
			return;
		}

		var offsetParent, offset, doc,
			elem = this[ 0 ],
			parentOffset = { top: 0, left: 0 };

		// position:fixed elements are offset from the viewport, which itself always has zero offset
		if ( jQuery.css( elem, "position" ) === "fixed" ) {

			// Assume position:fixed implies availability of getBoundingClientRect
			offset = elem.getBoundingClientRect();

		} else {
			offset = this.offset();

			// Account for the *real* offset parent, which can be the document or its root element
			// when a statically positioned element is identified
			doc = elem.ownerDocument;
			offsetParent = elem.offsetParent || doc.documentElement;
			while ( offsetParent &&
				( offsetParent === doc.body || offsetParent === doc.documentElement ) &&
				jQuery.css( offsetParent, "position" ) === "static" ) {

				offsetParent = offsetParent.parentNode;
			}
			if ( offsetParent && offsetParent !== elem && offsetParent.nodeType === 1 ) {

				// Incorporate borders into its offset, since they are outside its content origin
				parentOffset = jQuery( offsetParent ).offset();
				parentOffset.top += jQuery.css( offsetParent, "borderTopWidth", true );
				parentOffset.left += jQuery.css( offsetParent, "borderLeftWidth", true );
			}
		}

		// Subtract parent offsets and element margins
		return {
			top: offset.top - parentOffset.top - jQuery.css( elem, "marginTop", true ),
			left: offset.left - parentOffset.left - jQuery.css( elem, "marginLeft", true )
		};
	},

	// This method will return documentElement in the following cases:
	// 1) For the element inside the iframe without offsetParent, this method will return
	//    documentElement of the parent window
	// 2) For the hidden or detached element
	// 3) For body or html element, i.e. in case of the html node - it will return itself
	//
	// but those exceptions were never presented as a real life use-cases
	// and might be considered as more preferable results.
	//
	// This logic, however, is not guaranteed and can change at any point in the future
	offsetParent: function() {
		return this.map( function() {
			var offsetParent = this.offsetParent;

			while ( offsetParent && jQuery.css( offsetParent, "position" ) === "static" ) {
				offsetParent = offsetParent.offsetParent;
			}

			return offsetParent || documentElement;
		} );
	}
} );

// Create scrollLeft and scrollTop methods
jQuery.each( { scrollLeft: "pageXOffset", scrollTop: "pageYOffset" }, function( method, prop ) {
	var top = "pageYOffset" === prop;

	jQuery.fn[ method ] = function( val ) {
		return access( this, function( elem, method, val ) {

			// Coalesce documents and windows
			var win;
			if ( isWindow( elem ) ) {
				win = elem;
			} else if ( elem.nodeType === 9 ) {
				win = elem.defaultView;
			}

			if ( val === undefined ) {
				return win ? win[ prop ] : elem[ method ];
			}

			if ( win ) {
				win.scrollTo(
					!top ? val : win.pageXOffset,
					top ? val : win.pageYOffset
				);

			} else {
				elem[ method ] = val;
			}
		}, method, val, arguments.length );
	};
} );

// Support: Safari <=7 - 9.1, Chrome <=37 - 49
// Add the top/left cssHooks using jQuery.fn.position
// Webkit bug: https://bugs.webkit.org/show_bug.cgi?id=29084
// Blink bug: https://bugs.chromium.org/p/chromium/issues/detail?id=589347
// getComputedStyle returns percent when specified for top/left/bottom/right;
// rather than make the css module depend on the offset module, just check for it here
jQuery.each( [ "top", "left" ], function( _i, prop ) {
	jQuery.cssHooks[ prop ] = addGetHookIf( support.pixelPosition,
		function( elem, computed ) {
			if ( computed ) {
				computed = curCSS( elem, prop );

				// If curCSS returns percentage, fallback to offset
				return rnumnonpx.test( computed ) ?
					jQuery( elem ).position()[ prop ] + "px" :
					computed;
			}
		}
	);
} );


// Create innerHeight, innerWidth, height, width, outerHeight and outerWidth methods
jQuery.each( { Height: "height", Width: "width" }, function( name, type ) {
	jQuery.each( {
		padding: "inner" + name,
		content: type,
		"": "outer" + name
	}, function( defaultExtra, funcName ) {

		// Margin is only for outerHeight, outerWidth
		jQuery.fn[ funcName ] = function( margin, value ) {
			var chainable = arguments.length && ( defaultExtra || typeof margin !== "boolean" ),
				extra = defaultExtra || ( margin === true || value === true ? "margin" : "border" );

			return access( this, function( elem, type, value ) {
				var doc;

				if ( isWindow( elem ) ) {

					// $( window ).outerWidth/Height return w/h including scrollbars (gh-1729)
					return funcName.indexOf( "outer" ) === 0 ?
						elem[ "inner" + name ] :
						elem.document.documentElement[ "client" + name ];
				}

				// Get document width or height
				if ( elem.nodeType === 9 ) {
					doc = elem.documentElement;

					// Either scroll[Width/Height] or offset[Width/Height] or client[Width/Height],
					// whichever is greatest
					return Math.max(
						elem.body[ "scroll" + name ], doc[ "scroll" + name ],
						elem.body[ "offset" + name ], doc[ "offset" + name ],
						doc[ "client" + name ]
					);
				}

				return value === undefined ?

					// Get width or height on the element, requesting but not forcing parseFloat
					jQuery.css( elem, type, extra ) :

					// Set width or height on the element
					jQuery.style( elem, type, value, extra );
			}, type, chainable ? margin : undefined, chainable );
		};
	} );
} );


jQuery.each( [
	"ajaxStart",
	"ajaxStop",
	"ajaxComplete",
	"ajaxError",
	"ajaxSuccess",
	"ajaxSend"
], function( _i, type ) {
	jQuery.fn[ type ] = function( fn ) {
		return this.on( type, fn );
	};
} );




jQuery.fn.extend( {

	bind: function( types, data, fn ) {
		return this.on( types, null, data, fn );
	},
	unbind: function( types, fn ) {
		return this.off( types, null, fn );
	},

	delegate: function( selector, types, data, fn ) {
		return this.on( types, selector, data, fn );
	},
	undelegate: function( selector, types, fn ) {

		// ( namespace ) or ( selector, types [, fn] )
		return arguments.length === 1 ?
			this.off( selector, "**" ) :
			this.off( types, selector || "**", fn );
	},

	hover: function( fnOver, fnOut ) {
		return this
			.on( "mouseenter", fnOver )
			.on( "mouseleave", fnOut || fnOver );
	}
} );

jQuery.each(
	( "blur focus focusin focusout resize scroll click dblclick " +
	"mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave " +
	"change select submit keydown keypress keyup contextmenu" ).split( " " ),
	function( _i, name ) {

		// Handle event binding
		jQuery.fn[ name ] = function( data, fn ) {
			return arguments.length > 0 ?
				this.on( name, null, data, fn ) :
				this.trigger( name );
		};
	}
);




// Support: Android <=4.0 only
// Make sure we trim BOM and NBSP
// Require that the "whitespace run" starts from a non-whitespace
// to avoid O(N^2) behavior when the engine would try matching "\s+$" at each space position.
var rtrim = /^[\s\uFEFF\xA0]+|([^\s\uFEFF\xA0])[\s\uFEFF\xA0]+$/g;

// Bind a function to a context, optionally partially applying any
// arguments.
// jQuery.proxy is deprecated to promote standards (specifically Function#bind)
// However, it is not slated for removal any time soon
jQuery.proxy = function( fn, context ) {
	var tmp, args, proxy;

	if ( typeof context === "string" ) {
		tmp = fn[ context ];
		context = fn;
		fn = tmp;
	}

	// Quick check to determine if target is callable, in the spec
	// this throws a TypeError, but we will just return undefined.
	if ( !isFunction( fn ) ) {
		return undefined;
	}

	// Simulated bind
	args = slice.call( arguments, 2 );
	proxy = function() {
		return fn.apply( context || this, args.concat( slice.call( arguments ) ) );
	};

	// Set the guid of unique handler to the same of original handler, so it can be removed
	proxy.guid = fn.guid = fn.guid || jQuery.guid++;

	return proxy;
};

jQuery.holdReady = function( hold ) {
	if ( hold ) {
		jQuery.readyWait++;
	} else {
		jQuery.ready( true );
	}
};
jQuery.isArray = Array.isArray;
jQuery.parseJSON = JSON.parse;
jQuery.nodeName = nodeName;
jQuery.isFunction = isFunction;
jQuery.isWindow = isWindow;
jQuery.camelCase = camelCase;
jQuery.type = toType;

jQuery.now = Date.now;

jQuery.isNumeric = function( obj ) {

	// As of jQuery 3.0, isNumeric is limited to
	// strings and numbers (primitives or objects)
	// that can be coerced to finite numbers (gh-2662)
	var type = jQuery.type( obj );
	return ( type === "number" || type === "string" ) &&

		// parseFloat NaNs numeric-cast false positives ("")
		// ...but misinterprets leading-number strings, particularly hex literals ("0x...")
		// subtraction forces infinities to NaN
		!isNaN( obj - parseFloat( obj ) );
};

jQuery.trim = function( text ) {
	return text == null ?
		"" :
		( text + "" ).replace( rtrim, "$1" );
};



// Register as a named AMD module, since jQuery can be concatenated with other
// files that may use define, but not via a proper concatenation script that
// understands anonymous AMD modules. A named AMD is safest and most robust
// way to register. Lowercase jquery is used because AMD module names are
// derived from file names, and jQuery is normally delivered in a lowercase
// file name. Do this after creating the global so that if an AMD module wants
// to call noConflict to hide this version of jQuery, it will work.

// Note that for maximum portability, libraries that are not jQuery should
// declare themselves as anonymous modules, and avoid setting a global if an
// AMD loader is present. jQuery is a special case. For more information, see
// https://github.com/jrburke/requirejs/wiki/Updating-existing-libraries#wiki-anon

if ( true ) {
	!(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_RESULT__ = (function() {
		return jQuery;
	}).apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
		__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
}




var

	// Map over jQuery in case of overwrite
	_jQuery = window.jQuery,

	// Map over the $ in case of overwrite
	_$ = window.$;

jQuery.noConflict = function( deep ) {
	if ( window.$ === jQuery ) {
		window.$ = _$;
	}

	if ( deep && window.jQuery === jQuery ) {
		window.jQuery = _jQuery;
	}

	return jQuery;
};

// Expose jQuery and $ identifiers, even in AMD
// (trac-7102#comment:10, https://github.com/jquery/jquery/pull/557)
// and CommonJS for browser emulators (trac-13566)
if ( typeof noGlobal === "undefined" ) {
	window.jQuery = window.$ = jQuery;
}




return jQuery;
} );


/***/ })

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
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
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
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
var __webpack_exports__ = {};
/*!********************************************************!*\
  !*** ./node_modules/jquery.filer/css/jquery.filer.css ***!
  \********************************************************/
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin

})();

// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
var __webpack_exports__ = {};
/*!*********************************************************************************!*\
  !*** ./node_modules/jquery.filer/css/themes/jquery.filer-dragdropbox-theme.css ***!
  \*********************************************************************************/
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin

})();

// This entry need to be wrapped in an IIFE because it need to be isolated against other entry modules.
(() => {
/*!******************************************************!*\
  !*** ./node_modules/jquery.filer/js/jquery.filer.js ***!
  \******************************************************/
/* provided dependency */ var jQuery = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/*!
 * jQuery.filer
 * Copyright (c) 2016 CreativeDream
 * Website: https://github.com/CreativeDream/jquery.filer
 * Version: 1.3 (14-Sep-2016)
 * Requires: jQuery v1.7.1 or later
 */
(function($) {
    "use strict";
    $.fn.filer = function(q) {
        return this.each(function(t, r) {
            var s = $(r),
                b = '.jFiler',
                p = $(),
                o = $(),
                l = $(),
                sl = [],
                n_f = $.isFunction(q) ? q(s, $.fn.filer.defaults) : q,
                n = n_f && $.isPlainObject(n_f) ? $.extend(true, {}, $.fn.filer.defaults, n_f) : $.fn.filer.defaults,
                f = {
                    init: function() {
                        s.wrap('<div class="jFiler"></div>');
                        f._set('props');
                        s.prop("jFiler").boxEl = p = s.closest(b);
                        f._changeInput();
                    },
                    _bindInput: function() {
                        if (n.changeInput && o.length > 0) {
                            o.on("click", f._clickHandler);
                        }
                        s.on({
                            "focus": function() {
                                o.addClass('focused');
                            },
                            "blur": function() {
                                o.removeClass('focused');
                            },
                            "change": f._onChange
                        });
                        if (n.dragDrop) {
                            n.dragDrop.dragContainer.on("drag dragstart dragend dragover dragenter dragleave drop", function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                            });
                            n.dragDrop.dragContainer.on("drop", f._dragDrop.drop);
                            n.dragDrop.dragContainer.on("dragover", f._dragDrop.dragEnter);
                            n.dragDrop.dragContainer.on("dragleave", f._dragDrop.dragLeave);
                        }
                        if (n.uploadFile && n.clipBoardPaste) {
                            $(window)
                                .on("paste", f._clipboardPaste);
                        }
                    },
                    _unbindInput: function(all) {
                        if (n.changeInput && o.length > 0) {
                            o.off("click", f._clickHandler);
                        }

                        if (all) {
                            s.off("change", f._onChange);
                            if (n.dragDrop) {
                                n.dragDrop.dragContainer.off("drop", f._dragDrop.drop);
                                n.dragDrop.dragContainer.off("dragover", f._dragDrop.dragEnter);
                                n.dragDrop.dragContainer.off("dragleave", f._dragDrop.dragLeave);
                            }
                            if (n.uploadFile && n.clipBoardPaste) {
                                $(window)
                                    .off("paste", f._clipboardPaste);
                            }
                        }
                    },
                    _clickHandler: function() {
                        if (!n.uploadFile && n.addMore && s.val().length != 0) {
                            f._unbindInput(true);
                            var elem = $('<input type="file" />');
                            var attributes = s.prop("attributes");
                            $.each(attributes, function() {
                                if (this.name == "required") return;
                                elem.attr(this.name, this.value);
                            });
                            s.after(elem);
                            sl.push(elem);
                            s = elem;
                            f._bindInput();
                            f._set('props');
                        }
                        s.click()
                    },
                    _applyAttrSettings: function() {
                        var d = ["name", "limit", "maxSize", "fileMaxSize", "extensions", "changeInput", "showThumbs", "appendTo", "theme", "addMore", "excludeName", "files", "uploadUrl", "uploadData", "options"];
                        for (var k in d) {
                            var j = "data-jfiler-" + d[k];
                            if (f._assets.hasAttr(j)) {
                                switch (d[k]) {
                                    case "changeInput":
                                    case "showThumbs":
                                    case "addMore":
                                        n[d[k]] = (["true", "false"].indexOf(s.attr(j)) > -1 ? s.attr(j) == "true" : s.attr(j));
                                        break;
                                    case "extensions":
                                        n[d[k]] = s.attr(j)
                                            .replace(/ /g, '')
                                            .split(",");
                                        break;
                                    case "uploadUrl":
                                        if (n.uploadFile) n.uploadFile.url = s.attr(j);
                                        break;
                                    case "uploadData":
                                        if (n.uploadFile) n.uploadFile.data = JSON.parse(s.attr(j));
                                        break;
                                    case "files":
                                    case "options":
                                        n[d[k]] = JSON.parse(s.attr(j));
                                        break;
                                    default:
                                        n[d[k]] = s.attr(j);
                                }
                                s.removeAttr(j);
                            }
                        }
                    },
                    _changeInput: function() {
                        f._applyAttrSettings();
                        n.beforeRender != null && typeof n.beforeRender == "function" ? n.beforeRender(p, s) : null;
                        if (n.theme) {
                            p.addClass('jFiler-theme-' + n.theme);
                        }
                        if (s.get(0)
                            .tagName.toLowerCase() != "input" && s.get(0)
                            .type != "file") {
                            o = s;
                            s = $("<input type=\"file\" name=\"" + n.name + "\" />");
                            s.css({
                                position: "absolute",
                                left: "-9999px",
                                top: "-9999px",
                                "z-index": "-9999"
                            });
                            p.prepend(s);
                            f._isGn = s;
                        } else {
                            if (n.changeInput) {
                                switch (typeof n.changeInput) {
                                    case "boolean":
                                        o = $('<div class="jFiler-input"><div class="jFiler-input-caption"><span>' + n.captions.feedback + '</span></div><div class="jFiler-input-button">' + n.captions.button + '</div></div>"');
                                        break;
                                    case "string":
                                    case "object":
                                        o = $(n.changeInput);
                                        break;
                                    case "function":
                                        o = $(n.changeInput(p, s, n));
                                        break;
                                }
                                s.after(o);
                                s.css({
                                    position: "absolute",
                                    left: "-9999px",
                                    top: "-9999px",
                                    "z-index": "-9999"
                                });
                            }
                        }
                        s.prop("jFiler").newInputEl = o;
                        if (n.dragDrop) {
                            n.dragDrop.dragContainer = n.dragDrop.dragContainer ? $(n.dragDrop.dragContainer) : o;
                        }
                        if (!n.limit || (n.limit && n.limit >= 2)) {
                            s.attr("multiple", "multiple");
                            s.attr("name")
                                .slice(-2) != "[]" ? s.attr("name", s.attr("name") + "[]") : null;
                        }
                        if (!s.attr("disabled") && !n.disabled) {
                            n.disabled = false;
                            f._bindInput();
                            p.removeClass("jFiler-disabled");
                        } else {
                            n.disabled = true;
                            f._unbindInput(true);
                            p.addClass("jFiler-disabled");
                        }
                        if (n.files) {
                            f._append(false, {
                                files: n.files
                            });
                        }
                        n.afterRender != null && typeof n.afterRender == "function" ? n.afterRender(l, p, o, s) : null;
                    },
                    _clear: function() {
                        f.files = null;
                        s.prop("jFiler")
                            .files = null;
                        if (!n.uploadFile && !n.addMore) {
                            f._reset();
                        }
                        f._set('feedback', (f._itFl && f._itFl.length > 0 ? f._itFl.length + ' ' + n.captions.feedback2 : n.captions.feedback));
                        n.onEmpty != null && typeof n.onEmpty == "function" ? n.onEmpty(p, o, s) : null
                    },
                    _reset: function(a) {
                        if (!a) {
                            if (!n.uploadFile && n.addMore) {
                                for (var i = 0; i < sl.length; i++) {
                                    sl[i].remove();
                                }
                                sl = [];
                                f._unbindInput(true);
                                if (f._isGn) {
                                    s = f._isGn;
                                } else {
                                    s = $(r);
                                }
                                f._bindInput();
                            }
                            f._set('input', '');
                        }
                        f._itFl = [];
                        f._itFc = null;
                        f._ajFc = 0;
                        f._set('props');
                        s.prop("jFiler")
                            .files_list = f._itFl;
                        s.prop("jFiler")
                            .current_file = f._itFc;
                        f._itFr = [];
                        p.find("input[name^='jfiler-items-exclude-']:hidden")
                            .remove();
                        l.fadeOut("fast", function() {
                            $(this)
                                .remove();
                        });
                        s.prop("jFiler").listEl = l = $();
                    },
                    _set: function(element, value) {
                        switch (element) {
                            case 'input':
                                s.val(value);
                                break;
                            case 'feedback':
                                if (o.length > 0) {
                                    o.find('.jFiler-input-caption span')
                                        .html(value);
                                }
                                break;
                            case 'props':
                                if (!s.prop("jFiler")) {
                                    s.prop("jFiler", {
                                        options: n,
                                        listEl: l,
                                        boxEl: p,
                                        newInputEl: o,
                                        inputEl: s,
                                        files: f.files,
                                        files_list: f._itFl,
                                        current_file: f._itFc,
                                        append: function(data) {
                                            return f._append(false, {
                                                files: [data]
                                            });
                                        },
                                        enable: function() {
                                            if (!n.disabled)
                                                return;
                                            n.disabled = false;
                                            s.removeAttr("disabled");
                                            p.removeClass("jFiler-disabled");
                                            f._bindInput();
                                        },
                                        disable: function() {
                                            if (n.disabled)
                                                return;
                                            n.disabled = true;
                                            p.addClass("jFiler-disabled");
                                            f._unbindInput(true);
                                        },
                                        remove: function(id) {
                                            f._remove(null, {
                                                binded: true,
                                                data: {
                                                    id: id
                                                }
                                            });
                                            return true;
                                        },
                                        reset: function() {
                                            f._reset();
                                            f._clear();
                                            return true;
                                        },
                                        retry: function(data) {
                                            return f._retryUpload(data);
                                        }
                                    })
                                }
                        }
                    },
                    _filesCheck: function() {
                        var s = 0;
                        if (n.limit && f.files.length + f._itFl.length > n.limit) {
                            n.dialogs.alert(f._assets.textParse(n.captions.errors.filesLimit));
                            return false
                        }
                        for (var t = 0; t < f.files.length; t++) {
                            var file = f.files[t],
                                x = file.name.split(".")
                                .pop()
                                .toLowerCase(),
                                m = {
                                    name: file.name,
                                    size: file.size,
                                    size2: f._assets.bytesToSize(file.size),
                                    type: file.type,
                                    ext: x
                                };
                            if (n.extensions != null && $.inArray(x, n.extensions) == -1 && $.inArray(m.type, n.extensions) == -1) {
                                n.dialogs.alert(f._assets.textParse(n.captions.errors.filesType, m));
                                return false;
                            }
                            if ((n.maxSize != null && f.files[t].size > n.maxSize * 1048576) || (n.fileMaxSize != null && f.files[t].size > n.fileMaxSize * 1048576)) {
                                n.dialogs.alert(f._assets.textParse(n.captions.errors.filesSize, m));
                                return false;
                            }
                            if (file.size == 4096 && file.type.length == 0) {
                                n.dialogs.alert(f._assets.textParse(n.captions.errors.folderUpload, m));
                                return false;
                            }
                            if (n.onFileCheck != null && typeof n.onFileCheck == "function" ? n.onFileCheck(m, n, f._assets.textParse) === false : null) {
                                return false;
                            }

                            if ((n.uploadFile || n.addMore) && !n.allowDuplicates) {
                                var m = f._itFl.filter(function(a, b) {
                                    if (a.file.name == file.name && a.file.size == file.size && a.file.type == file.type && (file.lastModified ? a.file.lastModified == file.lastModified : true)) {
                                        return true;
                                    }
                                });
                                if (m.length > 0) {
                                    if (f.files.length == 1) {
                                        return false;
                                    } else {
                                        file._pendRemove = true;
                                    }
                                }
                            }

                            s += f.files[t].size
                        }
                        if (n.maxSize != null && s >= Math.round(n.maxSize * 1048576)) {
                            n.dialogs.alert(f._assets.textParse(n.captions.errors.filesSizeAll));
                            return false
                        }
                        return true;
                    },
                    _thumbCreator: {
                        create: function(i) {
                            var file = f.files[i],
                                id = (f._itFc ? f._itFc.id : i),
                                name = file.name,
                                size = file.size,
                                url = file.file,
                                type = file.type ? file.type.split("/", 1) : ""
                                .toString()
                                .toLowerCase(),
                                ext = name.indexOf(".") != -1 ? name.split(".")
                                .pop()
                                .toLowerCase() : "",
                                progressBar = n.uploadFile ? '<div class="jFiler-jProgressBar">' + n.templates.progressBar + '</div>' : '',
                                opts = {
                                    id: id,
                                    name: name,
                                    size: size,
                                    size2: f._assets.bytesToSize(size),
                                    url: url,
                                    type: type,
                                    extension: ext,
                                    icon: f._assets.getIcon(ext, type),
                                    icon2: f._thumbCreator.generateIcon({
                                        type: type,
                                        extension: ext
                                    }),
                                    image: '<div class="jFiler-item-thumb-image fi-loading"></div>',
                                    progressBar: progressBar,
                                    _appended: file._appended
                                },
                                html = "";
                            if (file.opts) {
                                opts = $.extend({}, file.opts, opts);
                            }
                            html = $(f._thumbCreator.renderContent(opts))
                                .attr("data-jfiler-index", id);
                            html.get(0)
                                .jfiler_id = id;
                            f._thumbCreator.renderFile(file, html, opts);
                            if (file.forList) {
                                return html;
                            }
                            f._itFc.html = html;
                            html.hide()[n.templates.itemAppendToEnd ? "appendTo" : "prependTo"](l.find(n.templates._selectors.list))
                                .show();
                            if (!file._appended) {
                                f._onSelect(i);
                            }
                        },
                        renderContent: function(opts) {
                            return f._assets.textParse((opts._appended ? n.templates.itemAppend : n.templates.item), opts);
                        },
                        renderFile: function(file, html, opts) {
                            if (html.find('.jFiler-item-thumb-image')
                                .length == 0) {
                                return false;
                            }
                            if (file.file && opts.type == "image") {
                                var g = '<img src="' + file.file + '" draggable="false" />',
                                    m = html.find('.jFiler-item-thumb-image.fi-loading');
                                $(g)
                                    .error(function() {
                                        g = f._thumbCreator.generateIcon(opts);
                                        html.addClass('jFiler-no-thumbnail');
                                        m.removeClass('fi-loading')
                                            .html(g);
                                    })
                                    .load(function() {
                                        m.removeClass('fi-loading')
                                            .html(g);
                                    });
                                return true;
                            }
                            if (window.File && window.FileList && window.FileReader && opts.type == "image" && opts.size < 1e+7) {
                                var y = new FileReader;
                                y.onload = function(e) {
                                    var m = html.find('.jFiler-item-thumb-image.fi-loading');
                                    if (n.templates.canvasImage) {
                                        var canvas = document.createElement('canvas'),
                                            context = canvas.getContext('2d'),
                                            img = new Image();

                                        img.onload = function() {
                                            var height = m.height(),
                                                width = m.width(),
                                                heightRatio = img.height / height,
                                                widthRatio = img.width / width,
                                                optimalRatio = heightRatio < widthRatio ? heightRatio : widthRatio,
                                                optimalHeight = img.height / optimalRatio,
                                                optimalWidth = img.width / optimalRatio,
                                                steps = Math.ceil(Math.log(img.width / optimalWidth) / Math.log(2));

                                            canvas.height = height;
                                            canvas.width = width;

                                            if (img.width < canvas.width || img.height < canvas.height || steps <= 1) {
                                                var x = img.width < canvas.width ? canvas.width / 2 - img.width / 2 : img.width > canvas.width ? -(img.width - canvas.width) / 2 : 0,
                                                    y = img.height < canvas.height ? canvas.height / 2 - img.height / 2 : 0
                                                context.drawImage(img, x, y, img.width, img.height);
                                            } else {
                                                var oc = document.createElement('canvas'),
                                                    octx = oc.getContext('2d');
                                                oc.width = img.width * 0.5;
                                                oc.height = img.height * 0.5;
                                                octx.fillStyle = "#fff";
                                                octx.fillRect(0, 0, oc.width, oc.height);
                                                octx.drawImage(img, 0, 0, oc.width, oc.height);
                                                octx.drawImage(oc, 0, 0, oc.width * 0.5, oc.height * 0.5);

                                                context.drawImage(oc, optimalWidth > canvas.width ? optimalWidth - canvas.width : 0, 0, oc.width * 0.5, oc.height * 0.5, 0, 0, optimalWidth, optimalHeight);
                                            }
                                            m.removeClass('fi-loading').html('<img src="' + canvas.toDataURL("image/png") + '" draggable="false" />');
                                        }
                                        img.onerror = function() {
                                            html.addClass('jFiler-no-thumbnail');
                                            m.removeClass('fi-loading')
                                                .html(f._thumbCreator.generateIcon(opts));
                                        }
                                        img.src = e.target.result;
                                    } else {
                                        m.removeClass('fi-loading').html('<img src="' + e.target.result + '" draggable="false" />');
                                    }
                                }
                                y.readAsDataURL(file);
                            } else {
                                var g = f._thumbCreator.generateIcon(opts),
                                    m = html.find('.jFiler-item-thumb-image.fi-loading');
                                html.addClass('jFiler-no-thumbnail');
                                m.removeClass('fi-loading')
                                    .html(g);
                            }
                        },
                        generateIcon: function(obj) {
                            var m = new Array(3);
                            if (obj && obj.type && obj.type[0] && obj.extension) {
                                switch (obj.type[0]) {
                                    case "image":
                                        m[0] = "f-image";
                                        m[1] = "<i class=\"icon-jfi-file-image\"></i>"
                                        break;
                                    case "video":
                                        m[0] = "f-video";
                                        m[1] = "<i class=\"icon-jfi-file-video\"></i>"
                                        break;
                                    case "audio":
                                        m[0] = "f-audio";
                                        m[1] = "<i class=\"icon-jfi-file-audio\"></i>"
                                        break;
                                    default:
                                        m[0] = "f-file f-file-ext-" + obj.extension;
                                        m[1] = (obj.extension.length > 0 ? "." + obj.extension : "");
                                        m[2] = 1
                                }
                            } else {
                                m[0] = "f-file";
                                m[1] = (obj.extension && obj.extension.length > 0 ? "." + obj.extension : "");
                                m[2] = 1
                            }
                            var el = '<span class="jFiler-icon-file ' + m[0] + '">' + m[1] + '</span>';
                            if (m[2] == 1) {
                                var c = f._assets.text2Color(obj.extension);
                                if (c) {
                                    var j = $(el)
                                        .appendTo("body");

                                    j.css('background-color', f._assets.text2Color(obj.extension));
                                    el = j.prop('outerHTML');
                                    j.remove();
                                }
                            }
                            return el;
                        },
                        _box: function(params) {
                            if (n.beforeShow != null && typeof n.beforeShow == "function" ? !n.beforeShow(f.files, l, p, o, s) : false) {
                                return false
                            }
                            if (l.length < 1) {
                                if (n.appendTo) {
                                    var appendTo = $(n.appendTo);
                                } else {
                                    var appendTo = p;
                                }
                                appendTo.find('.jFiler-items')
                                    .remove();
                                l = $('<div class="jFiler-items jFiler-row"></div>');
                                s.prop("jFiler").listEl = l;
                                l.append(f._assets.textParse(n.templates.box))
                                    .appendTo(appendTo);
                                l.on('click', n.templates._selectors.remove, function(e) {
                                    e.preventDefault();
                                    var m = [params ? params.remove.event : e, params ? params.remove.el : $(this).closest(n.templates._selectors.item)],
                                        c = function(a) {
                                            f._remove(m[0], m[1]);
                                        };
                                    if (n.templates.removeConfirmation) {
                                        n.dialogs.confirm(n.captions.removeConfirmation, c);
                                    } else {
                                        c();
                                    }
                                });
                            }
                            for (var i = 0; i < f.files.length; i++) {
                                if (!f.files[i]._appended) f.files[i]._choosed = true;
                                f._addToMemory(i);
                                f._thumbCreator.create(i);
                            }
                        }
                    },
                    _upload: function(i) {
                        var c = f._itFl[i],
                            el = c.html,
                            formData = new FormData();
                        formData.append(s.attr('name'), c.file, (c.file.name ? c.file.name : false));
                        if (n.uploadFile.data != null && $.isPlainObject(typeof(n.uploadFile.data) == "function" ? n.uploadFile.data(c.file) : n.uploadFile.data)) {
                            for (var k in n.uploadFile.data) {
                                formData.append(k, n.uploadFile.data[k])
                            }
                        }

                        f._ajax.send(el, formData, c);
                    },
                    _ajax: {
                        send: function(el, formData, c) {
                            c.ajax = $.ajax({
                                url: n.uploadFile.url,
                                data: formData,
                                type: n.uploadFile.type,
                                enctype: n.uploadFile.enctype,
                                xhr: function() {
                                    var myXhr = $.ajaxSettings.xhr();
                                    if (myXhr.upload) {
                                        myXhr.upload.addEventListener("progress", function(e) {
                                            f._ajax.progressHandling(e, el)
                                        }, false)
                                    }
                                    return myXhr
                                },
                                complete: function(jqXHR, textStatus) {
                                    c.ajax = false;
                                    f._ajFc++;

                                    if (n.uploadFile.synchron && c.id + 1 < f._itFl.length) {
                                        f._upload(c.id + 1);
                                    }

                                    if (f._ajFc >= f.files.length) {
                                        f._ajFc = 0;
                                        s.get(0).value = "";
                                        n.uploadFile.onComplete != null && typeof n.uploadFile.onComplete == "function" ? n.uploadFile.onComplete(l, p, o, s, jqXHR, textStatus) : null;
                                    }
                                },
                                beforeSend: function(jqXHR, settings) {
                                    return n.uploadFile.beforeSend != null && typeof n.uploadFile.beforeSend == "function" ? n.uploadFile.beforeSend(el, l, p, o, s, c.id, jqXHR, settings) : true;
                                },
                                success: function(data, textStatus, jqXHR) {
                                    c.uploaded = true;
                                    n.uploadFile.success != null && typeof n.uploadFile.success == "function" ? n.uploadFile.success(data, el, l, p, o, s, c.id, textStatus, jqXHR) : null
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    c.uploaded = false;
                                    n.uploadFile.error != null && typeof n.uploadFile.error == "function" ? n.uploadFile.error(el, l, p, o, s, c.id, jqXHR, textStatus, errorThrown) : null
                                },
                                statusCode: n.uploadFile.statusCode,
                                cache: false,
                                contentType: false,
                                processData: false
                            });
                            return c.ajax;
                        },
                        progressHandling: function(e, el) {
                            if (e.lengthComputable) {
                                var t = Math.round(e.loaded * 100 / e.total)
                                    .toString();
                                n.uploadFile.onProgress != null && typeof n.uploadFile.onProgress == "function" ? n.uploadFile.onProgress(t, el, l, p, o, s) : null;
                                el.find('.jFiler-jProgressBar')
                                    .find(n.templates._selectors.progressBar)
                                    .css("width", t + "%")
                            }
                        }
                    },
                    _dragDrop: {
                        dragEnter: function(e) {
                            clearTimeout(f._dragDrop._drt);
                            n.dragDrop.dragContainer.addClass('dragged');
                            f._set('feedback', n.captions.drop);
                            n.dragDrop.dragEnter != null && typeof n.dragDrop.dragEnter == "function" ? n.dragDrop.dragEnter(e, o, s, p) : null;
                        },
                        dragLeave: function(e) {
                            clearTimeout(f._dragDrop._drt);
                            f._dragDrop._drt = setTimeout(function(e) {
                                if (!f._dragDrop._dragLeaveCheck(e)) {
                                    f._dragDrop.dragLeave(e);
                                    return false;
                                }
                                n.dragDrop.dragContainer.removeClass('dragged');
                                f._set('feedback', n.captions.feedback);
                                n.dragDrop.dragLeave != null && typeof n.dragDrop.dragLeave == "function" ? n.dragDrop.dragLeave(e, o, s, p) : null;
                            }, 100, e);
                        },
                        drop: function(e) {
                            clearTimeout(f._dragDrop._drt);
                            n.dragDrop.dragContainer.removeClass('dragged');
                            f._set('feedback', n.captions.feedback);
                            if (e && e.originalEvent && e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files && e.originalEvent.dataTransfer.files.length > 0) {
                                f._onChange(e, e.originalEvent.dataTransfer.files);
                            }
                            n.dragDrop.drop != null && typeof n.dragDrop.drop == "function" ? n.dragDrop.drop(e.originalEvent.dataTransfer.files, e, o, s, p) : null;
                        },
                        _dragLeaveCheck: function(e) {
                            var related = $(e.currentTarget),
                                insideEls = 0;
                            if (!related.is(o)) {
                                insideEls = o.find(related).length;

                                if (insideEls > 0) {
                                    debugger;
                                    return false;
                                }
                            }
                            return true;
                        }
                    },
                    _clipboardPaste: function(e, fromDrop) {
                        if (!fromDrop && (!e.originalEvent.clipboardData && !e.originalEvent.clipboardData.items)) {
                            return
                        }
                        if (fromDrop && (!e.originalEvent.dataTransfer && !e.originalEvent.dataTransfer.items)) {
                            return
                        }
                        if (f._clPsePre) {
                            return
                        }
                        var items = (fromDrop ? e.originalEvent.dataTransfer.items : e.originalEvent.clipboardData.items),
                            b64toBlob = function(b64Data, contentType, sliceSize) {
                                contentType = contentType || '';
                                sliceSize = sliceSize || 512;
                                var byteCharacters = atob(b64Data);
                                var byteArrays = [];
                                for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
                                    var slice = byteCharacters.slice(offset, offset + sliceSize);
                                    var byteNumbers = new Array(slice.length);
                                    for (var i = 0; i < slice.length; i++) {
                                        byteNumbers[i] = slice.charCodeAt(i);
                                    }
                                    var byteArray = new Uint8Array(byteNumbers);
                                    byteArrays.push(byteArray);
                                }
                                var blob = new Blob(byteArrays, {
                                    type: contentType
                                });
                                return blob;
                            };
                        if (items) {
                            for (var i = 0; i < items.length; i++) {
                                if (items[i].type.indexOf("image") !== -1 || items[i].type.indexOf("text/uri-list") !== -1) {
                                    if (fromDrop) {
                                        try {
                                            window.atob(e.originalEvent.dataTransfer.getData("text/uri-list")
                                                .toString()
                                                .split(',')[1]);
                                        } catch (e) {
                                            return;
                                        }
                                    }
                                    var blob = (fromDrop ? b64toBlob(e.originalEvent.dataTransfer.getData("text/uri-list")
                                        .toString()
                                        .split(',')[1], "image/png") : items[i].getAsFile());
                                    blob.name = Math.random()
                                        .toString(36)
                                        .substring(5);
                                    blob.name += blob.type.indexOf("/") != -1 ? "." + blob.type.split("/")[1].toString()
                                        .toLowerCase() : ".png";
                                    f._onChange(e, [blob]);
                                    f._clPsePre = setTimeout(function() {
                                        delete f._clPsePre
                                    }, 1000);
                                }
                            }
                        }
                    },
                    _onSelect: function(i) {
                        if (n.uploadFile && !$.isEmptyObject(n.uploadFile)) {
                            if (!n.uploadFile.synchron || (n.uploadFile.synchron && $.grep(f._itFl, function(a) {
                                    return a.ajax
                                }).length == 0)) {
                                f._upload(f._itFc.id)
                            }
                        }
                        if (f.files[i]._pendRemove) {
                            f._itFc.html.hide();
                            f._remove(null, {
                                binded: true,
                                data: {
                                    id: f._itFc.id
                                }
                            });
                        }
                        n.onSelect != null && typeof n.onSelect == "function" ? n.onSelect(f.files[i], f._itFc.html, l, p, o, s) : null;
                        if (i + 1 >= f.files.length) {
                            n.afterShow != null && typeof n.afterShow == "function" ? n.afterShow(l, p, o, s) : null
                        }
                    },
                    _onChange: function(e, d) {
                        if (!d) {
                            if (!s.get(0)
                                .files || typeof s.get(0)
                                .files == "undefined" || s.get(0)
                                .files.length == 0) {
                                if (!n.uploadFile && !n.addMore) {
                                    f._set('input', '');
                                    f._clear();
                                }
                                return false
                            }
                            f.files = s.get(0)
                                .files;
                        } else {
                            if (!d || d.length == 0) {
                                f._set('input', '');
                                f._clear();
                                return false
                            }
                            f.files = d;
                        }
                        if (!n.uploadFile && !n.addMore) {
                            f._reset(true);
                        }
                        s.prop("jFiler")
                            .files = f.files;
                        if (!f._filesCheck() || (n.beforeSelect != null && typeof n.beforeSelect == "function" ? !n.beforeSelect(f.files, l, p, o, s) : false)) {
                            f._set('input', '');
                            f._clear();
                            if (n.addMore && sl.length > 0) {
                                f._unbindInput(true);
                                sl[sl.length - 1].remove();
                                sl.splice(sl.length - 1, 1);
                                s = sl.length > 0 ? sl[sl.length - 1] : $(r);
                                f._bindInput();
                            }
                            return false
                        }
                        f._set('feedback', f.files.length + f._itFl.length + ' ' + n.captions.feedback2);
                        if (n.showThumbs) {
                            f._thumbCreator._box();
                        } else {
                            for (var i = 0; i < f.files.length; i++) {
                                f.files[i]._choosed = true;
                                f._addToMemory(i);
                                f._onSelect(i);
                            }
                        }
                    },
                    _append: function(e, data) {
                        var files = (!data ? false : data.files);
                        if (!files || files.length <= 0) {
                            return;
                        }
                        f.files = files;
                        s.prop("jFiler")
                            .files = f.files;
                        if (n.showThumbs) {
                            for (var i = 0; i < f.files.length; i++) {
                                f.files[i]._appended = true;
                            }
                            f._thumbCreator._box();
                        }
                    },
                    _getList: function(e, data) {
                        var files = (!data ? false : data.files);
                        if (!files || files.length <= 0) {
                            return;
                        }
                        f.files = files;
                        s.prop("jFiler")
                            .files = f.files;
                        if (n.showThumbs) {
                            var returnData = [];
                            for (var i = 0; i < f.files.length; i++) {
                                f.files[i].forList = true;
                                returnData.push(f._thumbCreator.create(i));
                            }
                            if (data.callback) {
                                data.callback(returnData, l, p, o, s);
                            }
                        }
                    },
                    _retryUpload: function(e, data) {
                        var id = parseInt(typeof data == "object" ? data.attr("data-jfiler-index") : data),
                            obj = f._itFl.filter(function(value, key) {
                                return value.id == id;
                            });
                        if (obj.length > 0) {
                            if (n.uploadFile && !$.isEmptyObject(n.uploadFile) && !obj[0].uploaded) {
                                f._itFc = obj[0];
                                s.prop("jFiler")
                                    .current_file = f._itFc;
                                f._upload(id);
                                return true;
                            }
                        } else {
                            return false;
                        }
                    },
                    _remove: function(e, el) {
                        if (el.binded) {
                            if (typeof(el.data.id) != "undefined") {
                                el = l.find(n.templates._selectors.item + "[data-jfiler-index='" + el.data.id + "']");
                                if (el.length == 0) {
                                    return false
                                }
                            }
                            if (el.data.el) {
                                el = el.data.el;
                            }
                        }
                        var excl_input = function(val) {
                                var input = p.find("input[name^='jfiler-items-exclude-']:hidden")
                                    .first();

                                if (input.length == 0) {
                                    input = $('<input type="hidden" name="jfiler-items-exclude-' + (n.excludeName ? n.excludeName : (s.attr("name")
                                        .slice(-2) != "[]" ? s.attr("name") : s.attr("name")
                                        .substring(0, s.attr("name")
                                            .length - 2)) + "-" + t) + '">');
                                    input.appendTo(p);
                                }

                                if (val && $.isArray(val)) {
                                    val = JSON.stringify(val);
                                    input.val(val);
                                }
                            },
                            callback = function(el, id) {
                                var item = f._itFl[id],
                                    val = [];

                                if (item.file._choosed || item.file._appended || item.uploaded) {
                                    f._itFr.push(item);

                                    var m = f._itFl.filter(function(a) {
                                        return a.file.name == item.file.name;
                                    });

                                    for (var i = 0; i < f._itFr.length; i++) {
                                        if (n.addMore && f._itFr[i] == item && m.length > 0) {
                                            f._itFr[i].remove_name = m.indexOf(item) + "://" + f._itFr[i].file.name;
                                        }
                                        val.push(f._itFr[i].remove_name ? f._itFr[i].remove_name : f._itFr[i].file.name);
                                    }
                                }
                                excl_input(val);
                                f._itFl.splice(id, 1);
                                if (f._itFl.length < 1) {
                                    f._reset();
                                    f._clear();
                                } else {
                                    f._set('feedback', f._itFl.length + ' ' + n.captions.feedback2);
                                }
                                el.fadeOut("fast", function() {
                                    $(this)
                                        .remove();
                                });
                            };

                        var attrId = el.get(0)
                            .jfiler_id || el.attr('data-jfiler-index'),
                            id = null;

                        for (var key in f._itFl) {
                            if (key === 'length' || !f._itFl.hasOwnProperty(key)) continue;
                            if (f._itFl[key].id == attrId) {
                                id = key;
                            }
                        }
                        if (!f._itFl.hasOwnProperty(id)) {
                            return false
                        }
                        if (f._itFl[id].ajax) {
                            f._itFl[id].ajax.abort();
                            callback(el, id);
                            return;
                        }
                        if (n.onRemove != null && typeof n.onRemove == "function" ? n.onRemove(el, f._itFl[id].file, id, l, p, o, s) !== false : true) {
                            callback(el, id);
                        }
                    },
                    _addToMemory: function(i) {
                        f._itFl.push({
                            id: f._itFl.length,
                            file: f.files[i],
                            html: $(),
                            ajax: false,
                            uploaded: false,
                        });
                        if (n.addMore || f.files[i]._appended) f._itFl[f._itFl.length - 1].input = s;
                        f._itFc = f._itFl[f._itFl.length - 1];
                        s.prop("jFiler")
                            .files_list = f._itFl;
                        s.prop("jFiler")
                            .current_file = f._itFc;
                    },
                    _assets: {
                        bytesToSize: function(bytes) {
                            if (bytes == 0) return '0 Byte';
                            var k = 1000;
                            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
                            var i = Math.floor(Math.log(bytes) / Math.log(k));
                            return (bytes / Math.pow(k, i))
                                .toPrecision(3) + ' ' + sizes[i];
                        },
                        hasAttr: function(attr, el) {
                            var el = (!el ? s : el),
                                a = el.attr(attr);
                            if (!a || typeof a == "undefined") {
                                return false;
                            } else {
                                return true;
                            }
                        },
                        getIcon: function(ext, type) {
                            var types = ["audio", "image", "text", "video"];
                            if ($.inArray(type, types) > -1) {
                                return '<i class="icon-jfi-file-' + type + ' jfi-file-ext-' + ext + '"></i>';
                            }
                            return '<i class="icon-jfi-file-o jfi-file-type-' + type + ' jfi-file-ext-' + ext + '"></i>';
                        },
                        textParse: function(text, opts) {
                            opts = $.extend({}, {
                                limit: n.limit,
                                maxSize: n.maxSize,
                                fileMaxSize: n.fileMaxSize,
                                extensions: n.extensions ? n.extensions.join(',') : null,
                            }, (opts && $.isPlainObject(opts) ? opts : {}), n.options);
                            switch (typeof(text)) {
                                case "string":
                                    return text.replace(/\{\{fi-(.*?)\}\}/g, function(match, a) {
                                        a = a.replace(/ /g, '');
                                        if (a.match(/(.*?)\|limitTo\:(\d+)/)) {
                                            return a.replace(/(.*?)\|limitTo\:(\d+)/, function(match, a, b) {
                                                var a = (opts[a] ? opts[a] : ""),
                                                    str = a.substring(0, b);
                                                str = (a.length > str.length ? str.substring(0, str.length - 3) + "..." : str);
                                                return str;
                                            });
                                        } else {
                                            return (opts[a] ? opts[a] : "");
                                        }
                                    });
                                    break;
                                case "function":
                                    return text(opts);
                                    break;
                                default:
                                    return text;
                            }
                        },
                        text2Color: function(str) {
                            if (!str || str.length == 0) {
                                return false
                            }
                            for (var i = 0, hash = 0; i < str.length; hash = str.charCodeAt(i++) + ((hash << 5) - hash));
                            for (var i = 0, colour = "#"; i < 3; colour += ("00" + ((hash >> i++ * 2) & 0xFF)
                                    .toString(16))
                                .slice(-2));
                            return colour;
                        }
                    },
                    files: null,
                    _itFl: [],
                    _itFc: null,
                    _itFr: [],
                    _itPl: [],
                    _ajFc: 0
                }

            s.on("filer.append", function(e, data) {
                f._append(e, data)
            }).on("filer.remove", function(e, data) {
                data.binded = true;
                f._remove(e, data);
            }).on("filer.reset", function(e) {
                f._reset();
                f._clear();
                return true;
            }).on("filer.generateList", function(e, data) {
                return f._getList(e, data)
            }).on("filer.retry", function(e, data) {
                return f._retryUpload(e, data)
            });

            f.init();

            return this;
        });
    };
    $.fn.filer.defaults = {
        limit: null,
        maxSize: null,
        fileMaxSize: null,
        extensions: null,
        changeInput: true,
        showThumbs: false,
        appendTo: null,
        theme: 'default',
        templates: {
            box: '<ul class="jFiler-items-list jFiler-items-default"></ul>',
            item: '<li class="jFiler-item"><div class="jFiler-item-container"><div class="jFiler-item-inner"><div class="jFiler-item-icon pull-left">{{fi-icon}}</div><div class="jFiler-item-info pull-left"><div class="jFiler-item-title" title="{{fi-name}}">{{fi-name | limitTo:30}}</div><div class="jFiler-item-others"><span>size: {{fi-size2}}</span><span>type: {{fi-extension}}</span><span class="jFiler-item-status">{{fi-progressBar}}</span></div><div class="jFiler-item-assets"><ul class="list-inline"><li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li></ul></div></div></div></div></li>',
            itemAppend: '<li class="jFiler-item"><div class="jFiler-item-container"><div class="jFiler-item-inner"><div class="jFiler-item-icon pull-left">{{fi-icon}}</div><div class="jFiler-item-info pull-left"><div class="jFiler-item-title">{{fi-name | limitTo:35}}</div><div class="jFiler-item-others"><span>size: {{fi-size2}}</span><span>type: {{fi-extension}}</span><span class="jFiler-item-status"></span></div><div class="jFiler-item-assets"><ul class="list-inline"><li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li></ul></div></div></div></div></li>',
            progressBar: '<div class="bar"></div>',
            itemAppendToEnd: false,
            removeConfirmation: true,
            canvasImage: true,
            _selectors: {
                list: '.jFiler-items-list',
                item: '.jFiler-item',
                progressBar: '.bar',
                remove: '.jFiler-item-trash-action'
            }
        },
        files: null,
        uploadFile: null,
        dragDrop: null,
        addMore: false,
        allowDuplicates: false,
        clipBoardPaste: true,
        excludeName: null,
        beforeRender: null,
        afterRender: null,
        beforeShow: null,
        beforeSelect: null,
        onSelect: null,
        onFileCheck: null,
        afterShow: null,
        onRemove: null,
        onEmpty: null,
        options: null,
        dialogs: {
            alert: function(text) {
                return alert(text);
            },
            confirm: function(text, callback) {
                confirm(text) ? callback() : null;
            }
        },
        captions: {
            button: "Choose Files",
            feedback: "Choose files To Upload",
            feedback2: "files were chosen",
            drop: "Drop file here to Upload",
            removeConfirmation: "Are you sure you want to remove this file?",
            errors: {
                filesLimit: "Only {{fi-limit}} files are allowed to be uploaded.",
                filesType: "Only Images are allowed to be uploaded.",
                filesSize: "{{fi-name}} is too large! Please upload file up to {{fi-fileMaxSize}} MB.",
                filesSizeAll: "Files you've choosed are too large! Please upload files up to {{fi-maxSize}} MB.",
                folderUpload: "You are not allowed to upload folders."
            }
        }
    }
})(jQuery);

})();

// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
var __webpack_exports__ = {};
/*!*********************************************************************!*\
  !*** ./src/Bundle/StorageBundle/Resources/assets/css/drag-drop.css ***!
  \*********************************************************************/
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin

})();

// This entry need to be wrapped in an IIFE because it need to be isolated against other entry modules.
(() => {
/*!*******************************************************************!*\
  !*** ./src/Bundle/StorageBundle/Resources/assets/js/drag-drop.js ***!
  \*******************************************************************/
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
$(document).ready(function () {
  // feature detection for drag&drop upload
  var isAdvancedUpload = function () {
    var div = document.createElement('div');
    return ('draggable' in div || 'ondragstart' in div && 'ondrop' in div) && 'FormData' in window && 'FileReader' in window;
  }();
  var $dropZones = $('.integrated-dropzone');
  $.each($dropZones, function () {
    var $dropZone = $(this);

    // drag&drop files if the feature is available
    if (isAdvancedUpload) {
      $dropZone.addClass('has-advanced-upload') // letting the CSS part to know drag&drop is supported by the browser
      .on('drag dragstart dragend dragover dragenter dragleave drop', function (e) {
        // preventing the unwanted behaviours
        e.preventDefault();
        e.stopPropagation();
      }).on('dragover dragenter', function ()
      //
      {
        $dropZone.addClass('is-dragover');
      }).on('dragleave dragend drop', function () {
        $dropZone.removeClass('is-dragover');
      }).on('drop', function (e) {
        $("input[type='file']", $dropZone).prop("files", e.originalEvent.dataTransfer.files); // the files that were dropped
        $("input[type='file']", $dropZone).trigger('change');
      });
    }
    var filer_default_opts = {
      showThumbs: true,
      limit: 1,
      appendTo: ".integrated-dropzone",
      afterRender: function afterRender(item, box) {
        if (this.files && this.files.length) {
          $(box).hide();
        }
      },
      afterShow: function afterShow(item, box, a) {
        $(box).hide();
        $dropZone.find('.remove-file').val(0);
      },
      onSelect: function onSelect(item) {
        if ($('#integrated_content_title').val() == '') {
          $('#integrated_content_title').val(item.name.split('.').slice(0, -1).join('.'));
        }
      },
      onEmpty: function onEmpty(box) {
        $(box).show();
        $dropZone.find('.remove-file').val(1);
      },
      theme: "dragdropbox",
      templates: {
        box: '<ul class="jFiler-items-list jFiler-items-grid"></ul>',
        item: '<li class="jFiler-item">\
						<div class="jFiler-item-container">\
							<div class="jFiler-item-inner">\
								<div class="jFiler-item-thumb">\
									<div class="jFiler-item-status"></div>\
									<div class="jFiler-item-thumb-overlay">\
										<div class="jFiler-item-info">\
											<div style="display:table-cell;vertical-align: middle;">\
												<span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name}}</b></span>\
												<span class="jFiler-item-others">{{fi-size2}}</span>\
											</div>\
										</div>\
									</div>\
									{{fi-image}}\
								</div>\
								<div class="jFiler-item-assets jFiler-row">\
									<ul class="list-inline pull-right">\
										<li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
									</ul>\
								</div>\
							</div>\
						</div>\
					</li>',
        itemAppend: '<li class="jFiler-item">\
                                <div class="jFiler-item-container">\
                                    <div class="jFiler-item-inner">\
                                        <div class="jFiler-item-thumb">\
                                            <div class="jFiler-item-status"></div>\
                                            <div class="jFiler-item-thumb-overlay">\
        										<div class="jFiler-item-info">\
        											<div style="display:table-cell;vertical-align: middle;">\
        												<span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name}}</b></span>\
        											</div>\
        										</div>\
        									</div>\
                                            {{fi-image}}\
                                        </div>\
                                        <div class="jFiler-item-assets jFiler-row">\
                                            <ul class="list-inline pull-right">\
                                                <li><a href="{{fi-name}}" title="Download" target="_blank"><span class="iconoir-download"></span></a></li>\
                                                <li><a class="jFiler-item-trash-action" title="Delete"><i class="iconoir-trash"></i></a></li>\
                                            </ul>\
                                        </div>\
                                    </div>\
                                </div>\
                            </li>',
        canvasImage: true,
        removeConfirmation: true,
        _selectors: {
          list: '.jFiler-items-list',
          item: '.jFiler-item',
          progressBar: '.bar',
          remove: '.jFiler-item-trash-action'
        }
      }
    };

    //merge default options with options passed in view
    $.extend(filer_default_opts, $dropZone.data('options'));
    $('input[type="file"]', $dropZone).filer(filer_default_opts);
  });
});
})();

/******/ })()
;
=======
/*! For license information please see drag-drop.js.LICENSE.txt */
(()=>{var e,t={4692:function(e,t){var n;!function(t,n){"use strict";"object"==typeof e.exports?e.exports=t.document?n(t,!0):function(e){if(!e.document)throw new Error("jQuery requires a window with a document");return n(e)}:n(t)}("undefined"!=typeof window?window:this,(function(i,r){"use strict";var o=[],a=Object.getPrototypeOf,s=o.slice,l=o.flat?function(e){return o.flat.call(e)}:function(e){return o.concat.apply([],e)},u=o.push,c=o.indexOf,d={},f=d.toString,p=d.hasOwnProperty,h=p.toString,g=h.call(Object),m={},v=function(e){return"function"==typeof e&&"number"!=typeof e.nodeType&&"function"!=typeof e.item},y=function(e){return null!=e&&e===e.window},x=i.document,b={type:!0,src:!0,nonce:!0,noModule:!0};function w(e,t,n){var i,r,o=(n=n||x).createElement("script");if(o.text=e,t)for(i in b)(r=t[i]||t.getAttribute&&t.getAttribute(i))&&o.setAttribute(i,r);n.head.appendChild(o).parentNode.removeChild(o)}function j(e){return null==e?e+"":"object"==typeof e||"function"==typeof e?d[f.call(e)]||"object":typeof e}var T="3.7.1",C=/HTML$/i,_=function(e,t){return new _.fn.init(e,t)};function F(e){var t=!!e&&"length"in e&&e.length,n=j(e);return!v(e)&&!y(e)&&("array"===n||0===t||"number"==typeof t&&t>0&&t-1 in e)}function S(e,t){return e.nodeName&&e.nodeName.toLowerCase()===t.toLowerCase()}_.fn=_.prototype={jquery:T,constructor:_,length:0,toArray:function(){return s.call(this)},get:function(e){return null==e?s.call(this):e<0?this[e+this.length]:this[e]},pushStack:function(e){var t=_.merge(this.constructor(),e);return t.prevObject=this,t},each:function(e){return _.each(this,e)},map:function(e){return this.pushStack(_.map(this,(function(t,n){return e.call(t,n,t)})))},slice:function(){return this.pushStack(s.apply(this,arguments))},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},even:function(){return this.pushStack(_.grep(this,(function(e,t){return(t+1)%2})))},odd:function(){return this.pushStack(_.grep(this,(function(e,t){return t%2})))},eq:function(e){var t=this.length,n=+e+(e<0?t:0);return this.pushStack(n>=0&&n<t?[this[n]]:[])},end:function(){return this.prevObject||this.constructor()},push:u,sort:o.sort,splice:o.splice},_.extend=_.fn.extend=function(){var e,t,n,i,r,o,a=arguments[0]||{},s=1,l=arguments.length,u=!1;for("boolean"==typeof a&&(u=a,a=arguments[s]||{},s++),"object"==typeof a||v(a)||(a={}),s===l&&(a=this,s--);s<l;s++)if(null!=(e=arguments[s]))for(t in e)i=e[t],"__proto__"!==t&&a!==i&&(u&&i&&(_.isPlainObject(i)||(r=Array.isArray(i)))?(n=a[t],o=r&&!Array.isArray(n)?[]:r||_.isPlainObject(n)?n:{},r=!1,a[t]=_.extend(u,o,i)):void 0!==i&&(a[t]=i));return a},_.extend({expando:"jQuery"+(T+Math.random()).replace(/\D/g,""),isReady:!0,error:function(e){throw new Error(e)},noop:function(){},isPlainObject:function(e){var t,n;return!(!e||"[object Object]"!==f.call(e))&&(!(t=a(e))||"function"==typeof(n=p.call(t,"constructor")&&t.constructor)&&h.call(n)===g)},isEmptyObject:function(e){var t;for(t in e)return!1;return!0},globalEval:function(e,t,n){w(e,{nonce:t&&t.nonce},n)},each:function(e,t){var n,i=0;if(F(e))for(n=e.length;i<n&&!1!==t.call(e[i],i,e[i]);i++);else for(i in e)if(!1===t.call(e[i],i,e[i]))break;return e},text:function(e){var t,n="",i=0,r=e.nodeType;if(!r)for(;t=e[i++];)n+=_.text(t);return 1===r||11===r?e.textContent:9===r?e.documentElement.textContent:3===r||4===r?e.nodeValue:n},makeArray:function(e,t){var n=t||[];return null!=e&&(F(Object(e))?_.merge(n,"string"==typeof e?[e]:e):u.call(n,e)),n},inArray:function(e,t,n){return null==t?-1:c.call(t,e,n)},isXMLDoc:function(e){var t=e&&e.namespaceURI,n=e&&(e.ownerDocument||e).documentElement;return!C.test(t||n&&n.nodeName||"HTML")},merge:function(e,t){for(var n=+t.length,i=0,r=e.length;i<n;i++)e[r++]=t[i];return e.length=r,e},grep:function(e,t,n){for(var i=[],r=0,o=e.length,a=!n;r<o;r++)!t(e[r],r)!==a&&i.push(e[r]);return i},map:function(e,t,n){var i,r,o=0,a=[];if(F(e))for(i=e.length;o<i;o++)null!=(r=t(e[o],o,n))&&a.push(r);else for(o in e)null!=(r=t(e[o],o,n))&&a.push(r);return l(a)},guid:1,support:m}),"function"==typeof Symbol&&(_.fn[Symbol.iterator]=o[Symbol.iterator]),_.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "),(function(e,t){d["[object "+t+"]"]=t.toLowerCase()}));var k=o.pop,E=o.sort,D=o.splice,A="[\\x20\\t\\r\\n\\f]",N=new RegExp("^"+A+"+|((?:^|[^\\\\])(?:\\\\.)*)"+A+"+$","g");_.contains=function(e,t){var n=t&&t.parentNode;return e===n||!(!n||1!==n.nodeType||!(e.contains?e.contains(n):e.compareDocumentPosition&&16&e.compareDocumentPosition(n)))};var L=/([\0-\x1f\x7f]|^-?\d)|^-$|[^\x80-\uFFFF\w-]/g;function P(e,t){return t?"\0"===e?"�":e.slice(0,-1)+"\\"+e.charCodeAt(e.length-1).toString(16)+" ":"\\"+e}_.escapeSelector=function(e){return(e+"").replace(L,P)};var M=x,O=u;!function(){var e,t,n,r,a,l,u,d,f,h,g=O,v=_.expando,y=0,x=0,b=ee(),w=ee(),j=ee(),T=ee(),C=function(e,t){return e===t&&(a=!0),0},F="checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",L="(?:\\\\[\\da-fA-F]{1,6}"+A+"?|\\\\[^\\r\\n\\f]|[\\w-]|[^\0-\\x7f])+",P="\\["+A+"*("+L+")(?:"+A+"*([*^$|!~]?=)"+A+"*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|("+L+"))|)"+A+"*\\]",q=":("+L+")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|"+P+")*)|.*)\\)|)",H=new RegExp(A+"+","g"),I=new RegExp("^"+A+"*,"+A+"*"),R=new RegExp("^"+A+"*([>+~]|"+A+")"+A+"*"),z=new RegExp(A+"|>"),B=new RegExp(q),W=new RegExp("^"+L+"$"),$={ID:new RegExp("^#("+L+")"),CLASS:new RegExp("^\\.("+L+")"),TAG:new RegExp("^("+L+"|[*])"),ATTR:new RegExp("^"+P),PSEUDO:new RegExp("^"+q),CHILD:new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\("+A+"*(even|odd|(([+-]|)(\\d*)n|)"+A+"*(?:([+-]|)"+A+"*(\\d+)|))"+A+"*\\)|)","i"),bool:new RegExp("^(?:"+F+")$","i"),needsContext:new RegExp("^"+A+"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\("+A+"*((?:-\\d)?\\d*)"+A+"*\\)|)(?=[^-]|$)","i")},U=/^(?:input|select|textarea|button)$/i,X=/^h\d$/i,G=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,V=/[+~]/,Y=new RegExp("\\\\[\\da-fA-F]{1,6}"+A+"?|\\\\([^\\r\\n\\f])","g"),J=function(e,t){var n="0x"+e.slice(1)-65536;return t||(n<0?String.fromCharCode(n+65536):String.fromCharCode(n>>10|55296,1023&n|56320))},Q=function(){le()},K=fe((function(e){return!0===e.disabled&&S(e,"fieldset")}),{dir:"parentNode",next:"legend"});try{g.apply(o=s.call(M.childNodes),M.childNodes),o[M.childNodes.length].nodeType}catch(e){g={apply:function(e,t){O.apply(e,s.call(t))},call:function(e){O.apply(e,s.call(arguments,1))}}}function Z(e,t,n,i){var r,o,a,s,u,c,p,h=t&&t.ownerDocument,y=t?t.nodeType:9;if(n=n||[],"string"!=typeof e||!e||1!==y&&9!==y&&11!==y)return n;if(!i&&(le(t),t=t||l,d)){if(11!==y&&(u=G.exec(e)))if(r=u[1]){if(9===y){if(!(a=t.getElementById(r)))return n;if(a.id===r)return g.call(n,a),n}else if(h&&(a=h.getElementById(r))&&Z.contains(t,a)&&a.id===r)return g.call(n,a),n}else{if(u[2])return g.apply(n,t.getElementsByTagName(e)),n;if((r=u[3])&&t.getElementsByClassName)return g.apply(n,t.getElementsByClassName(r)),n}if(!(T[e+" "]||f&&f.test(e))){if(p=e,h=t,1===y&&(z.test(e)||R.test(e))){for((h=V.test(e)&&se(t.parentNode)||t)==t&&m.scope||((s=t.getAttribute("id"))?s=_.escapeSelector(s):t.setAttribute("id",s=v)),o=(c=ce(e)).length;o--;)c[o]=(s?"#"+s:":scope")+" "+de(c[o]);p=c.join(",")}try{return g.apply(n,h.querySelectorAll(p)),n}catch(t){T(e,!0)}finally{s===v&&t.removeAttribute("id")}}}return ye(e.replace(N,"$1"),t,n,i)}function ee(){var e=[];return function n(i,r){return e.push(i+" ")>t.cacheLength&&delete n[e.shift()],n[i+" "]=r}}function te(e){return e[v]=!0,e}function ne(e){var t=l.createElement("fieldset");try{return!!e(t)}catch(e){return!1}finally{t.parentNode&&t.parentNode.removeChild(t),t=null}}function ie(e){return function(t){return S(t,"input")&&t.type===e}}function re(e){return function(t){return(S(t,"input")||S(t,"button"))&&t.type===e}}function oe(e){return function(t){return"form"in t?t.parentNode&&!1===t.disabled?"label"in t?"label"in t.parentNode?t.parentNode.disabled===e:t.disabled===e:t.isDisabled===e||t.isDisabled!==!e&&K(t)===e:t.disabled===e:"label"in t&&t.disabled===e}}function ae(e){return te((function(t){return t=+t,te((function(n,i){for(var r,o=e([],n.length,t),a=o.length;a--;)n[r=o[a]]&&(n[r]=!(i[r]=n[r]))}))}))}function se(e){return e&&void 0!==e.getElementsByTagName&&e}function le(e){var n,i=e?e.ownerDocument||e:M;return i!=l&&9===i.nodeType&&i.documentElement?(u=(l=i).documentElement,d=!_.isXMLDoc(l),h=u.matches||u.webkitMatchesSelector||u.msMatchesSelector,u.msMatchesSelector&&M!=l&&(n=l.defaultView)&&n.top!==n&&n.addEventListener("unload",Q),m.getById=ne((function(e){return u.appendChild(e).id=_.expando,!l.getElementsByName||!l.getElementsByName(_.expando).length})),m.disconnectedMatch=ne((function(e){return h.call(e,"*")})),m.scope=ne((function(){return l.querySelectorAll(":scope")})),m.cssHas=ne((function(){try{return l.querySelector(":has(*,:jqfake)"),!1}catch(e){return!0}})),m.getById?(t.filter.ID=function(e){var t=e.replace(Y,J);return function(e){return e.getAttribute("id")===t}},t.find.ID=function(e,t){if(void 0!==t.getElementById&&d){var n=t.getElementById(e);return n?[n]:[]}}):(t.filter.ID=function(e){var t=e.replace(Y,J);return function(e){var n=void 0!==e.getAttributeNode&&e.getAttributeNode("id");return n&&n.value===t}},t.find.ID=function(e,t){if(void 0!==t.getElementById&&d){var n,i,r,o=t.getElementById(e);if(o){if((n=o.getAttributeNode("id"))&&n.value===e)return[o];for(r=t.getElementsByName(e),i=0;o=r[i++];)if((n=o.getAttributeNode("id"))&&n.value===e)return[o]}return[]}}),t.find.TAG=function(e,t){return void 0!==t.getElementsByTagName?t.getElementsByTagName(e):t.querySelectorAll(e)},t.find.CLASS=function(e,t){if(void 0!==t.getElementsByClassName&&d)return t.getElementsByClassName(e)},f=[],ne((function(e){var t;u.appendChild(e).innerHTML="<a id='"+v+"' href='' disabled='disabled'></a><select id='"+v+"-\r\\' disabled='disabled'><option selected=''></option></select>",e.querySelectorAll("[selected]").length||f.push("\\["+A+"*(?:value|"+F+")"),e.querySelectorAll("[id~="+v+"-]").length||f.push("~="),e.querySelectorAll("a#"+v+"+*").length||f.push(".#.+[+~]"),e.querySelectorAll(":checked").length||f.push(":checked"),(t=l.createElement("input")).setAttribute("type","hidden"),e.appendChild(t).setAttribute("name","D"),u.appendChild(e).disabled=!0,2!==e.querySelectorAll(":disabled").length&&f.push(":enabled",":disabled"),(t=l.createElement("input")).setAttribute("name",""),e.appendChild(t),e.querySelectorAll("[name='']").length||f.push("\\["+A+"*name"+A+"*="+A+"*(?:''|\"\")")})),m.cssHas||f.push(":has"),f=f.length&&new RegExp(f.join("|")),C=function(e,t){if(e===t)return a=!0,0;var n=!e.compareDocumentPosition-!t.compareDocumentPosition;return n||(1&(n=(e.ownerDocument||e)==(t.ownerDocument||t)?e.compareDocumentPosition(t):1)||!m.sortDetached&&t.compareDocumentPosition(e)===n?e===l||e.ownerDocument==M&&Z.contains(M,e)?-1:t===l||t.ownerDocument==M&&Z.contains(M,t)?1:r?c.call(r,e)-c.call(r,t):0:4&n?-1:1)},l):l}for(e in Z.matches=function(e,t){return Z(e,null,null,t)},Z.matchesSelector=function(e,t){if(le(e),d&&!T[t+" "]&&(!f||!f.test(t)))try{var n=h.call(e,t);if(n||m.disconnectedMatch||e.document&&11!==e.document.nodeType)return n}catch(e){T(t,!0)}return Z(t,l,null,[e]).length>0},Z.contains=function(e,t){return(e.ownerDocument||e)!=l&&le(e),_.contains(e,t)},Z.attr=function(e,n){(e.ownerDocument||e)!=l&&le(e);var i=t.attrHandle[n.toLowerCase()],r=i&&p.call(t.attrHandle,n.toLowerCase())?i(e,n,!d):void 0;return void 0!==r?r:e.getAttribute(n)},Z.error=function(e){throw new Error("Syntax error, unrecognized expression: "+e)},_.uniqueSort=function(e){var t,n=[],i=0,o=0;if(a=!m.sortStable,r=!m.sortStable&&s.call(e,0),E.call(e,C),a){for(;t=e[o++];)t===e[o]&&(i=n.push(o));for(;i--;)D.call(e,n[i],1)}return r=null,e},_.fn.uniqueSort=function(){return this.pushStack(_.uniqueSort(s.apply(this)))},t=_.expr={cacheLength:50,createPseudo:te,match:$,attrHandle:{},find:{},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(e){return e[1]=e[1].replace(Y,J),e[3]=(e[3]||e[4]||e[5]||"").replace(Y,J),"~="===e[2]&&(e[3]=" "+e[3]+" "),e.slice(0,4)},CHILD:function(e){return e[1]=e[1].toLowerCase(),"nth"===e[1].slice(0,3)?(e[3]||Z.error(e[0]),e[4]=+(e[4]?e[5]+(e[6]||1):2*("even"===e[3]||"odd"===e[3])),e[5]=+(e[7]+e[8]||"odd"===e[3])):e[3]&&Z.error(e[0]),e},PSEUDO:function(e){var t,n=!e[6]&&e[2];return $.CHILD.test(e[0])?null:(e[3]?e[2]=e[4]||e[5]||"":n&&B.test(n)&&(t=ce(n,!0))&&(t=n.indexOf(")",n.length-t)-n.length)&&(e[0]=e[0].slice(0,t),e[2]=n.slice(0,t)),e.slice(0,3))}},filter:{TAG:function(e){var t=e.replace(Y,J).toLowerCase();return"*"===e?function(){return!0}:function(e){return S(e,t)}},CLASS:function(e){var t=b[e+" "];return t||(t=new RegExp("(^|"+A+")"+e+"("+A+"|$)"))&&b(e,(function(e){return t.test("string"==typeof e.className&&e.className||void 0!==e.getAttribute&&e.getAttribute("class")||"")}))},ATTR:function(e,t,n){return function(i){var r=Z.attr(i,e);return null==r?"!="===t:!t||(r+="","="===t?r===n:"!="===t?r!==n:"^="===t?n&&0===r.indexOf(n):"*="===t?n&&r.indexOf(n)>-1:"$="===t?n&&r.slice(-n.length)===n:"~="===t?(" "+r.replace(H," ")+" ").indexOf(n)>-1:"|="===t&&(r===n||r.slice(0,n.length+1)===n+"-"))}},CHILD:function(e,t,n,i,r){var o="nth"!==e.slice(0,3),a="last"!==e.slice(-4),s="of-type"===t;return 1===i&&0===r?function(e){return!!e.parentNode}:function(t,n,l){var u,c,d,f,p,h=o!==a?"nextSibling":"previousSibling",g=t.parentNode,m=s&&t.nodeName.toLowerCase(),x=!l&&!s,b=!1;if(g){if(o){for(;h;){for(d=t;d=d[h];)if(s?S(d,m):1===d.nodeType)return!1;p=h="only"===e&&!p&&"nextSibling"}return!0}if(p=[a?g.firstChild:g.lastChild],a&&x){for(b=(f=(u=(c=g[v]||(g[v]={}))[e]||[])[0]===y&&u[1])&&u[2],d=f&&g.childNodes[f];d=++f&&d&&d[h]||(b=f=0)||p.pop();)if(1===d.nodeType&&++b&&d===t){c[e]=[y,f,b];break}}else if(x&&(b=f=(u=(c=t[v]||(t[v]={}))[e]||[])[0]===y&&u[1]),!1===b)for(;(d=++f&&d&&d[h]||(b=f=0)||p.pop())&&(!(s?S(d,m):1===d.nodeType)||!++b||(x&&((c=d[v]||(d[v]={}))[e]=[y,b]),d!==t)););return(b-=r)===i||b%i==0&&b/i>=0}}},PSEUDO:function(e,n){var i,r=t.pseudos[e]||t.setFilters[e.toLowerCase()]||Z.error("unsupported pseudo: "+e);return r[v]?r(n):r.length>1?(i=[e,e,"",n],t.setFilters.hasOwnProperty(e.toLowerCase())?te((function(e,t){for(var i,o=r(e,n),a=o.length;a--;)e[i=c.call(e,o[a])]=!(t[i]=o[a])})):function(e){return r(e,0,i)}):r}},pseudos:{not:te((function(e){var t=[],n=[],i=ve(e.replace(N,"$1"));return i[v]?te((function(e,t,n,r){for(var o,a=i(e,null,r,[]),s=e.length;s--;)(o=a[s])&&(e[s]=!(t[s]=o))})):function(e,r,o){return t[0]=e,i(t,null,o,n),t[0]=null,!n.pop()}})),has:te((function(e){return function(t){return Z(e,t).length>0}})),contains:te((function(e){return e=e.replace(Y,J),function(t){return(t.textContent||_.text(t)).indexOf(e)>-1}})),lang:te((function(e){return W.test(e||"")||Z.error("unsupported lang: "+e),e=e.replace(Y,J).toLowerCase(),function(t){var n;do{if(n=d?t.lang:t.getAttribute("xml:lang")||t.getAttribute("lang"))return(n=n.toLowerCase())===e||0===n.indexOf(e+"-")}while((t=t.parentNode)&&1===t.nodeType);return!1}})),target:function(e){var t=i.location&&i.location.hash;return t&&t.slice(1)===e.id},root:function(e){return e===u},focus:function(e){return e===function(){try{return l.activeElement}catch(e){}}()&&l.hasFocus()&&!!(e.type||e.href||~e.tabIndex)},enabled:oe(!1),disabled:oe(!0),checked:function(e){return S(e,"input")&&!!e.checked||S(e,"option")&&!!e.selected},selected:function(e){return e.parentNode&&e.parentNode.selectedIndex,!0===e.selected},empty:function(e){for(e=e.firstChild;e;e=e.nextSibling)if(e.nodeType<6)return!1;return!0},parent:function(e){return!t.pseudos.empty(e)},header:function(e){return X.test(e.nodeName)},input:function(e){return U.test(e.nodeName)},button:function(e){return S(e,"input")&&"button"===e.type||S(e,"button")},text:function(e){var t;return S(e,"input")&&"text"===e.type&&(null==(t=e.getAttribute("type"))||"text"===t.toLowerCase())},first:ae((function(){return[0]})),last:ae((function(e,t){return[t-1]})),eq:ae((function(e,t,n){return[n<0?n+t:n]})),even:ae((function(e,t){for(var n=0;n<t;n+=2)e.push(n);return e})),odd:ae((function(e,t){for(var n=1;n<t;n+=2)e.push(n);return e})),lt:ae((function(e,t,n){var i;for(i=n<0?n+t:n>t?t:n;--i>=0;)e.push(i);return e})),gt:ae((function(e,t,n){for(var i=n<0?n+t:n;++i<t;)e.push(i);return e}))}},t.pseudos.nth=t.pseudos.eq,{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})t.pseudos[e]=ie(e);for(e in{submit:!0,reset:!0})t.pseudos[e]=re(e);function ue(){}function ce(e,n){var i,r,o,a,s,l,u,c=w[e+" "];if(c)return n?0:c.slice(0);for(s=e,l=[],u=t.preFilter;s;){for(a in i&&!(r=I.exec(s))||(r&&(s=s.slice(r[0].length)||s),l.push(o=[])),i=!1,(r=R.exec(s))&&(i=r.shift(),o.push({value:i,type:r[0].replace(N," ")}),s=s.slice(i.length)),t.filter)!(r=$[a].exec(s))||u[a]&&!(r=u[a](r))||(i=r.shift(),o.push({value:i,type:a,matches:r}),s=s.slice(i.length));if(!i)break}return n?s.length:s?Z.error(e):w(e,l).slice(0)}function de(e){for(var t=0,n=e.length,i="";t<n;t++)i+=e[t].value;return i}function fe(e,t,n){var i=t.dir,r=t.next,o=r||i,a=n&&"parentNode"===o,s=x++;return t.first?function(t,n,r){for(;t=t[i];)if(1===t.nodeType||a)return e(t,n,r);return!1}:function(t,n,l){var u,c,d=[y,s];if(l){for(;t=t[i];)if((1===t.nodeType||a)&&e(t,n,l))return!0}else for(;t=t[i];)if(1===t.nodeType||a)if(c=t[v]||(t[v]={}),r&&S(t,r))t=t[i]||t;else{if((u=c[o])&&u[0]===y&&u[1]===s)return d[2]=u[2];if(c[o]=d,d[2]=e(t,n,l))return!0}return!1}}function pe(e){return e.length>1?function(t,n,i){for(var r=e.length;r--;)if(!e[r](t,n,i))return!1;return!0}:e[0]}function he(e,t,n,i,r){for(var o,a=[],s=0,l=e.length,u=null!=t;s<l;s++)(o=e[s])&&(n&&!n(o,i,r)||(a.push(o),u&&t.push(s)));return a}function ge(e,t,n,i,r,o){return i&&!i[v]&&(i=ge(i)),r&&!r[v]&&(r=ge(r,o)),te((function(o,a,s,l){var u,d,f,p,h=[],m=[],v=a.length,y=o||function(e,t,n){for(var i=0,r=t.length;i<r;i++)Z(e,t[i],n);return n}(t||"*",s.nodeType?[s]:s,[]),x=!e||!o&&t?y:he(y,h,e,s,l);if(n?n(x,p=r||(o?e:v||i)?[]:a,s,l):p=x,i)for(u=he(p,m),i(u,[],s,l),d=u.length;d--;)(f=u[d])&&(p[m[d]]=!(x[m[d]]=f));if(o){if(r||e){if(r){for(u=[],d=p.length;d--;)(f=p[d])&&u.push(x[d]=f);r(null,p=[],u,l)}for(d=p.length;d--;)(f=p[d])&&(u=r?c.call(o,f):h[d])>-1&&(o[u]=!(a[u]=f))}}else p=he(p===a?p.splice(v,p.length):p),r?r(null,a,p,l):g.apply(a,p)}))}function me(e){for(var i,r,o,a=e.length,s=t.relative[e[0].type],l=s||t.relative[" "],u=s?1:0,d=fe((function(e){return e===i}),l,!0),f=fe((function(e){return c.call(i,e)>-1}),l,!0),p=[function(e,t,r){var o=!s&&(r||t!=n)||((i=t).nodeType?d(e,t,r):f(e,t,r));return i=null,o}];u<a;u++)if(r=t.relative[e[u].type])p=[fe(pe(p),r)];else{if((r=t.filter[e[u].type].apply(null,e[u].matches))[v]){for(o=++u;o<a&&!t.relative[e[o].type];o++);return ge(u>1&&pe(p),u>1&&de(e.slice(0,u-1).concat({value:" "===e[u-2].type?"*":""})).replace(N,"$1"),r,u<o&&me(e.slice(u,o)),o<a&&me(e=e.slice(o)),o<a&&de(e))}p.push(r)}return pe(p)}function ve(e,i){var r,o=[],a=[],s=j[e+" "];if(!s){for(i||(i=ce(e)),r=i.length;r--;)(s=me(i[r]))[v]?o.push(s):a.push(s);s=j(e,function(e,i){var r=i.length>0,o=e.length>0,a=function(a,s,u,c,f){var p,h,m,v=0,x="0",b=a&&[],w=[],j=n,T=a||o&&t.find.TAG("*",f),C=y+=null==j?1:Math.random()||.1,F=T.length;for(f&&(n=s==l||s||f);x!==F&&null!=(p=T[x]);x++){if(o&&p){for(h=0,s||p.ownerDocument==l||(le(p),u=!d);m=e[h++];)if(m(p,s||l,u)){g.call(c,p);break}f&&(y=C)}r&&((p=!m&&p)&&v--,a&&b.push(p))}if(v+=x,r&&x!==v){for(h=0;m=i[h++];)m(b,w,s,u);if(a){if(v>0)for(;x--;)b[x]||w[x]||(w[x]=k.call(c));w=he(w)}g.apply(c,w),f&&!a&&w.length>0&&v+i.length>1&&_.uniqueSort(c)}return f&&(y=C,n=j),b};return r?te(a):a}(a,o)),s.selector=e}return s}function ye(e,n,i,r){var o,a,s,l,u,c="function"==typeof e&&e,f=!r&&ce(e=c.selector||e);if(i=i||[],1===f.length){if((a=f[0]=f[0].slice(0)).length>2&&"ID"===(s=a[0]).type&&9===n.nodeType&&d&&t.relative[a[1].type]){if(!(n=(t.find.ID(s.matches[0].replace(Y,J),n)||[])[0]))return i;c&&(n=n.parentNode),e=e.slice(a.shift().value.length)}for(o=$.needsContext.test(e)?0:a.length;o--&&(s=a[o],!t.relative[l=s.type]);)if((u=t.find[l])&&(r=u(s.matches[0].replace(Y,J),V.test(a[0].type)&&se(n.parentNode)||n))){if(a.splice(o,1),!(e=r.length&&de(a)))return g.apply(i,r),i;break}}return(c||ve(e,f))(r,n,!d,i,!n||V.test(e)&&se(n.parentNode)||n),i}ue.prototype=t.filters=t.pseudos,t.setFilters=new ue,m.sortStable=v.split("").sort(C).join("")===v,le(),m.sortDetached=ne((function(e){return 1&e.compareDocumentPosition(l.createElement("fieldset"))})),_.find=Z,_.expr[":"]=_.expr.pseudos,_.unique=_.uniqueSort,Z.compile=ve,Z.select=ye,Z.setDocument=le,Z.tokenize=ce,Z.escape=_.escapeSelector,Z.getText=_.text,Z.isXML=_.isXMLDoc,Z.selectors=_.expr,Z.support=_.support,Z.uniqueSort=_.uniqueSort}();var q=function(e,t,n){for(var i=[],r=void 0!==n;(e=e[t])&&9!==e.nodeType;)if(1===e.nodeType){if(r&&_(e).is(n))break;i.push(e)}return i},H=function(e,t){for(var n=[];e;e=e.nextSibling)1===e.nodeType&&e!==t&&n.push(e);return n},I=_.expr.match.needsContext,R=/^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i;function z(e,t,n){return v(t)?_.grep(e,(function(e,i){return!!t.call(e,i,e)!==n})):t.nodeType?_.grep(e,(function(e){return e===t!==n})):"string"!=typeof t?_.grep(e,(function(e){return c.call(t,e)>-1!==n})):_.filter(t,e,n)}_.filter=function(e,t,n){var i=t[0];return n&&(e=":not("+e+")"),1===t.length&&1===i.nodeType?_.find.matchesSelector(i,e)?[i]:[]:_.find.matches(e,_.grep(t,(function(e){return 1===e.nodeType})))},_.fn.extend({find:function(e){var t,n,i=this.length,r=this;if("string"!=typeof e)return this.pushStack(_(e).filter((function(){for(t=0;t<i;t++)if(_.contains(r[t],this))return!0})));for(n=this.pushStack([]),t=0;t<i;t++)_.find(e,r[t],n);return i>1?_.uniqueSort(n):n},filter:function(e){return this.pushStack(z(this,e||[],!1))},not:function(e){return this.pushStack(z(this,e||[],!0))},is:function(e){return!!z(this,"string"==typeof e&&I.test(e)?_(e):e||[],!1).length}});var B,W=/^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/;(_.fn.init=function(e,t,n){var i,r;if(!e)return this;if(n=n||B,"string"==typeof e){if(!(i="<"===e[0]&&">"===e[e.length-1]&&e.length>=3?[null,e,null]:W.exec(e))||!i[1]&&t)return!t||t.jquery?(t||n).find(e):this.constructor(t).find(e);if(i[1]){if(t=t instanceof _?t[0]:t,_.merge(this,_.parseHTML(i[1],t&&t.nodeType?t.ownerDocument||t:x,!0)),R.test(i[1])&&_.isPlainObject(t))for(i in t)v(this[i])?this[i](t[i]):this.attr(i,t[i]);return this}return(r=x.getElementById(i[2]))&&(this[0]=r,this.length=1),this}return e.nodeType?(this[0]=e,this.length=1,this):v(e)?void 0!==n.ready?n.ready(e):e(_):_.makeArray(e,this)}).prototype=_.fn,B=_(x);var $=/^(?:parents|prev(?:Until|All))/,U={children:!0,contents:!0,next:!0,prev:!0};function X(e,t){for(;(e=e[t])&&1!==e.nodeType;);return e}_.fn.extend({has:function(e){var t=_(e,this),n=t.length;return this.filter((function(){for(var e=0;e<n;e++)if(_.contains(this,t[e]))return!0}))},closest:function(e,t){var n,i=0,r=this.length,o=[],a="string"!=typeof e&&_(e);if(!I.test(e))for(;i<r;i++)for(n=this[i];n&&n!==t;n=n.parentNode)if(n.nodeType<11&&(a?a.index(n)>-1:1===n.nodeType&&_.find.matchesSelector(n,e))){o.push(n);break}return this.pushStack(o.length>1?_.uniqueSort(o):o)},index:function(e){return e?"string"==typeof e?c.call(_(e),this[0]):c.call(this,e.jquery?e[0]:e):this[0]&&this[0].parentNode?this.first().prevAll().length:-1},add:function(e,t){return this.pushStack(_.uniqueSort(_.merge(this.get(),_(e,t))))},addBack:function(e){return this.add(null==e?this.prevObject:this.prevObject.filter(e))}}),_.each({parent:function(e){var t=e.parentNode;return t&&11!==t.nodeType?t:null},parents:function(e){return q(e,"parentNode")},parentsUntil:function(e,t,n){return q(e,"parentNode",n)},next:function(e){return X(e,"nextSibling")},prev:function(e){return X(e,"previousSibling")},nextAll:function(e){return q(e,"nextSibling")},prevAll:function(e){return q(e,"previousSibling")},nextUntil:function(e,t,n){return q(e,"nextSibling",n)},prevUntil:function(e,t,n){return q(e,"previousSibling",n)},siblings:function(e){return H((e.parentNode||{}).firstChild,e)},children:function(e){return H(e.firstChild)},contents:function(e){return null!=e.contentDocument&&a(e.contentDocument)?e.contentDocument:(S(e,"template")&&(e=e.content||e),_.merge([],e.childNodes))}},(function(e,t){_.fn[e]=function(n,i){var r=_.map(this,t,n);return"Until"!==e.slice(-5)&&(i=n),i&&"string"==typeof i&&(r=_.filter(i,r)),this.length>1&&(U[e]||_.uniqueSort(r),$.test(e)&&r.reverse()),this.pushStack(r)}}));var G=/[^\x20\t\r\n\f]+/g;function V(e){return e}function Y(e){throw e}function J(e,t,n,i){var r;try{e&&v(r=e.promise)?r.call(e).done(t).fail(n):e&&v(r=e.then)?r.call(e,t,n):t.apply(void 0,[e].slice(i))}catch(e){n.apply(void 0,[e])}}_.Callbacks=function(e){e="string"==typeof e?function(e){var t={};return _.each(e.match(G)||[],(function(e,n){t[n]=!0})),t}(e):_.extend({},e);var t,n,i,r,o=[],a=[],s=-1,l=function(){for(r=r||e.once,i=t=!0;a.length;s=-1)for(n=a.shift();++s<o.length;)!1===o[s].apply(n[0],n[1])&&e.stopOnFalse&&(s=o.length,n=!1);e.memory||(n=!1),t=!1,r&&(o=n?[]:"")},u={add:function(){return o&&(n&&!t&&(s=o.length-1,a.push(n)),function t(n){_.each(n,(function(n,i){v(i)?e.unique&&u.has(i)||o.push(i):i&&i.length&&"string"!==j(i)&&t(i)}))}(arguments),n&&!t&&l()),this},remove:function(){return _.each(arguments,(function(e,t){for(var n;(n=_.inArray(t,o,n))>-1;)o.splice(n,1),n<=s&&s--})),this},has:function(e){return e?_.inArray(e,o)>-1:o.length>0},empty:function(){return o&&(o=[]),this},disable:function(){return r=a=[],o=n="",this},disabled:function(){return!o},lock:function(){return r=a=[],n||t||(o=n=""),this},locked:function(){return!!r},fireWith:function(e,n){return r||(n=[e,(n=n||[]).slice?n.slice():n],a.push(n),t||l()),this},fire:function(){return u.fireWith(this,arguments),this},fired:function(){return!!i}};return u},_.extend({Deferred:function(e){var t=[["notify","progress",_.Callbacks("memory"),_.Callbacks("memory"),2],["resolve","done",_.Callbacks("once memory"),_.Callbacks("once memory"),0,"resolved"],["reject","fail",_.Callbacks("once memory"),_.Callbacks("once memory"),1,"rejected"]],n="pending",r={state:function(){return n},always:function(){return o.done(arguments).fail(arguments),this},catch:function(e){return r.then(null,e)},pipe:function(){var e=arguments;return _.Deferred((function(n){_.each(t,(function(t,i){var r=v(e[i[4]])&&e[i[4]];o[i[1]]((function(){var e=r&&r.apply(this,arguments);e&&v(e.promise)?e.promise().progress(n.notify).done(n.resolve).fail(n.reject):n[i[0]+"With"](this,r?[e]:arguments)}))})),e=null})).promise()},then:function(e,n,r){var o=0;function a(e,t,n,r){return function(){var s=this,l=arguments,u=function(){var i,u;if(!(e<o)){if((i=n.apply(s,l))===t.promise())throw new TypeError("Thenable self-resolution");u=i&&("object"==typeof i||"function"==typeof i)&&i.then,v(u)?r?u.call(i,a(o,t,V,r),a(o,t,Y,r)):(o++,u.call(i,a(o,t,V,r),a(o,t,Y,r),a(o,t,V,t.notifyWith))):(n!==V&&(s=void 0,l=[i]),(r||t.resolveWith)(s,l))}},c=r?u:function(){try{u()}catch(i){_.Deferred.exceptionHook&&_.Deferred.exceptionHook(i,c.error),e+1>=o&&(n!==Y&&(s=void 0,l=[i]),t.rejectWith(s,l))}};e?c():(_.Deferred.getErrorHook?c.error=_.Deferred.getErrorHook():_.Deferred.getStackHook&&(c.error=_.Deferred.getStackHook()),i.setTimeout(c))}}return _.Deferred((function(i){t[0][3].add(a(0,i,v(r)?r:V,i.notifyWith)),t[1][3].add(a(0,i,v(e)?e:V)),t[2][3].add(a(0,i,v(n)?n:Y))})).promise()},promise:function(e){return null!=e?_.extend(e,r):r}},o={};return _.each(t,(function(e,i){var a=i[2],s=i[5];r[i[1]]=a.add,s&&a.add((function(){n=s}),t[3-e][2].disable,t[3-e][3].disable,t[0][2].lock,t[0][3].lock),a.add(i[3].fire),o[i[0]]=function(){return o[i[0]+"With"](this===o?void 0:this,arguments),this},o[i[0]+"With"]=a.fireWith})),r.promise(o),e&&e.call(o,o),o},when:function(e){var t=arguments.length,n=t,i=Array(n),r=s.call(arguments),o=_.Deferred(),a=function(e){return function(n){i[e]=this,r[e]=arguments.length>1?s.call(arguments):n,--t||o.resolveWith(i,r)}};if(t<=1&&(J(e,o.done(a(n)).resolve,o.reject,!t),"pending"===o.state()||v(r[n]&&r[n].then)))return o.then();for(;n--;)J(r[n],a(n),o.reject);return o.promise()}});var Q=/^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;_.Deferred.exceptionHook=function(e,t){i.console&&i.console.warn&&e&&Q.test(e.name)&&i.console.warn("jQuery.Deferred exception: "+e.message,e.stack,t)},_.readyException=function(e){i.setTimeout((function(){throw e}))};var K=_.Deferred();function Z(){x.removeEventListener("DOMContentLoaded",Z),i.removeEventListener("load",Z),_.ready()}_.fn.ready=function(e){return K.then(e).catch((function(e){_.readyException(e)})),this},_.extend({isReady:!1,readyWait:1,ready:function(e){(!0===e?--_.readyWait:_.isReady)||(_.isReady=!0,!0!==e&&--_.readyWait>0||K.resolveWith(x,[_]))}}),_.ready.then=K.then,"complete"===x.readyState||"loading"!==x.readyState&&!x.documentElement.doScroll?i.setTimeout(_.ready):(x.addEventListener("DOMContentLoaded",Z),i.addEventListener("load",Z));var ee=function(e,t,n,i,r,o,a){var s=0,l=e.length,u=null==n;if("object"===j(n))for(s in r=!0,n)ee(e,t,s,n[s],!0,o,a);else if(void 0!==i&&(r=!0,v(i)||(a=!0),u&&(a?(t.call(e,i),t=null):(u=t,t=function(e,t,n){return u.call(_(e),n)})),t))for(;s<l;s++)t(e[s],n,a?i:i.call(e[s],s,t(e[s],n)));return r?e:u?t.call(e):l?t(e[0],n):o},te=/^-ms-/,ne=/-([a-z])/g;function ie(e,t){return t.toUpperCase()}function re(e){return e.replace(te,"ms-").replace(ne,ie)}var oe=function(e){return 1===e.nodeType||9===e.nodeType||!+e.nodeType};function ae(){this.expando=_.expando+ae.uid++}ae.uid=1,ae.prototype={cache:function(e){var t=e[this.expando];return t||(t={},oe(e)&&(e.nodeType?e[this.expando]=t:Object.defineProperty(e,this.expando,{value:t,configurable:!0}))),t},set:function(e,t,n){var i,r=this.cache(e);if("string"==typeof t)r[re(t)]=n;else for(i in t)r[re(i)]=t[i];return r},get:function(e,t){return void 0===t?this.cache(e):e[this.expando]&&e[this.expando][re(t)]},access:function(e,t,n){return void 0===t||t&&"string"==typeof t&&void 0===n?this.get(e,t):(this.set(e,t,n),void 0!==n?n:t)},remove:function(e,t){var n,i=e[this.expando];if(void 0!==i){if(void 0!==t){n=(t=Array.isArray(t)?t.map(re):(t=re(t))in i?[t]:t.match(G)||[]).length;for(;n--;)delete i[t[n]]}(void 0===t||_.isEmptyObject(i))&&(e.nodeType?e[this.expando]=void 0:delete e[this.expando])}},hasData:function(e){var t=e[this.expando];return void 0!==t&&!_.isEmptyObject(t)}};var se=new ae,le=new ae,ue=/^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,ce=/[A-Z]/g;function de(e,t,n){var i;if(void 0===n&&1===e.nodeType)if(i="data-"+t.replace(ce,"-$&").toLowerCase(),"string"==typeof(n=e.getAttribute(i))){try{n=function(e){return"true"===e||"false"!==e&&("null"===e?null:e===+e+""?+e:ue.test(e)?JSON.parse(e):e)}(n)}catch(e){}le.set(e,t,n)}else n=void 0;return n}_.extend({hasData:function(e){return le.hasData(e)||se.hasData(e)},data:function(e,t,n){return le.access(e,t,n)},removeData:function(e,t){le.remove(e,t)},_data:function(e,t,n){return se.access(e,t,n)},_removeData:function(e,t){se.remove(e,t)}}),_.fn.extend({data:function(e,t){var n,i,r,o=this[0],a=o&&o.attributes;if(void 0===e){if(this.length&&(r=le.get(o),1===o.nodeType&&!se.get(o,"hasDataAttrs"))){for(n=a.length;n--;)a[n]&&0===(i=a[n].name).indexOf("data-")&&(i=re(i.slice(5)),de(o,i,r[i]));se.set(o,"hasDataAttrs",!0)}return r}return"object"==typeof e?this.each((function(){le.set(this,e)})):ee(this,(function(t){var n;if(o&&void 0===t)return void 0!==(n=le.get(o,e))||void 0!==(n=de(o,e))?n:void 0;this.each((function(){le.set(this,e,t)}))}),null,t,arguments.length>1,null,!0)},removeData:function(e){return this.each((function(){le.remove(this,e)}))}}),_.extend({queue:function(e,t,n){var i;if(e)return t=(t||"fx")+"queue",i=se.get(e,t),n&&(!i||Array.isArray(n)?i=se.access(e,t,_.makeArray(n)):i.push(n)),i||[]},dequeue:function(e,t){t=t||"fx";var n=_.queue(e,t),i=n.length,r=n.shift(),o=_._queueHooks(e,t);"inprogress"===r&&(r=n.shift(),i--),r&&("fx"===t&&n.unshift("inprogress"),delete o.stop,r.call(e,(function(){_.dequeue(e,t)}),o)),!i&&o&&o.empty.fire()},_queueHooks:function(e,t){var n=t+"queueHooks";return se.get(e,n)||se.access(e,n,{empty:_.Callbacks("once memory").add((function(){se.remove(e,[t+"queue",n])}))})}}),_.fn.extend({queue:function(e,t){var n=2;return"string"!=typeof e&&(t=e,e="fx",n--),arguments.length<n?_.queue(this[0],e):void 0===t?this:this.each((function(){var n=_.queue(this,e,t);_._queueHooks(this,e),"fx"===e&&"inprogress"!==n[0]&&_.dequeue(this,e)}))},dequeue:function(e){return this.each((function(){_.dequeue(this,e)}))},clearQueue:function(e){return this.queue(e||"fx",[])},promise:function(e,t){var n,i=1,r=_.Deferred(),o=this,a=this.length,s=function(){--i||r.resolveWith(o,[o])};for("string"!=typeof e&&(t=e,e=void 0),e=e||"fx";a--;)(n=se.get(o[a],e+"queueHooks"))&&n.empty&&(i++,n.empty.add(s));return s(),r.promise(t)}});var fe=/[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,pe=new RegExp("^(?:([+-])=|)("+fe+")([a-z%]*)$","i"),he=["Top","Right","Bottom","Left"],ge=x.documentElement,me=function(e){return _.contains(e.ownerDocument,e)},ve={composed:!0};ge.getRootNode&&(me=function(e){return _.contains(e.ownerDocument,e)||e.getRootNode(ve)===e.ownerDocument});var ye=function(e,t){return"none"===(e=t||e).style.display||""===e.style.display&&me(e)&&"none"===_.css(e,"display")};function xe(e,t,n,i){var r,o,a=20,s=i?function(){return i.cur()}:function(){return _.css(e,t,"")},l=s(),u=n&&n[3]||(_.cssNumber[t]?"":"px"),c=e.nodeType&&(_.cssNumber[t]||"px"!==u&&+l)&&pe.exec(_.css(e,t));if(c&&c[3]!==u){for(l/=2,u=u||c[3],c=+l||1;a--;)_.style(e,t,c+u),(1-o)*(1-(o=s()/l||.5))<=0&&(a=0),c/=o;c*=2,_.style(e,t,c+u),n=n||[]}return n&&(c=+c||+l||0,r=n[1]?c+(n[1]+1)*n[2]:+n[2],i&&(i.unit=u,i.start=c,i.end=r)),r}var be={};function we(e){var t,n=e.ownerDocument,i=e.nodeName,r=be[i];return r||(t=n.body.appendChild(n.createElement(i)),r=_.css(t,"display"),t.parentNode.removeChild(t),"none"===r&&(r="block"),be[i]=r,r)}function je(e,t){for(var n,i,r=[],o=0,a=e.length;o<a;o++)(i=e[o]).style&&(n=i.style.display,t?("none"===n&&(r[o]=se.get(i,"display")||null,r[o]||(i.style.display="")),""===i.style.display&&ye(i)&&(r[o]=we(i))):"none"!==n&&(r[o]="none",se.set(i,"display",n)));for(o=0;o<a;o++)null!=r[o]&&(e[o].style.display=r[o]);return e}_.fn.extend({show:function(){return je(this,!0)},hide:function(){return je(this)},toggle:function(e){return"boolean"==typeof e?e?this.show():this.hide():this.each((function(){ye(this)?_(this).show():_(this).hide()}))}});var Te,Ce,_e=/^(?:checkbox|radio)$/i,Fe=/<([a-z][^\/\0>\x20\t\r\n\f]*)/i,Se=/^$|^module$|\/(?:java|ecma)script/i;Te=x.createDocumentFragment().appendChild(x.createElement("div")),(Ce=x.createElement("input")).setAttribute("type","radio"),Ce.setAttribute("checked","checked"),Ce.setAttribute("name","t"),Te.appendChild(Ce),m.checkClone=Te.cloneNode(!0).cloneNode(!0).lastChild.checked,Te.innerHTML="<textarea>x</textarea>",m.noCloneChecked=!!Te.cloneNode(!0).lastChild.defaultValue,Te.innerHTML="<option></option>",m.option=!!Te.lastChild;var ke={thead:[1,"<table>","</table>"],col:[2,"<table><colgroup>","</colgroup></table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],_default:[0,"",""]};function Ee(e,t){var n;return n=void 0!==e.getElementsByTagName?e.getElementsByTagName(t||"*"):void 0!==e.querySelectorAll?e.querySelectorAll(t||"*"):[],void 0===t||t&&S(e,t)?_.merge([e],n):n}function De(e,t){for(var n=0,i=e.length;n<i;n++)se.set(e[n],"globalEval",!t||se.get(t[n],"globalEval"))}ke.tbody=ke.tfoot=ke.colgroup=ke.caption=ke.thead,ke.th=ke.td,m.option||(ke.optgroup=ke.option=[1,"<select multiple='multiple'>","</select>"]);var Ae=/<|&#?\w+;/;function Ne(e,t,n,i,r){for(var o,a,s,l,u,c,d=t.createDocumentFragment(),f=[],p=0,h=e.length;p<h;p++)if((o=e[p])||0===o)if("object"===j(o))_.merge(f,o.nodeType?[o]:o);else if(Ae.test(o)){for(a=a||d.appendChild(t.createElement("div")),s=(Fe.exec(o)||["",""])[1].toLowerCase(),l=ke[s]||ke._default,a.innerHTML=l[1]+_.htmlPrefilter(o)+l[2],c=l[0];c--;)a=a.lastChild;_.merge(f,a.childNodes),(a=d.firstChild).textContent=""}else f.push(t.createTextNode(o));for(d.textContent="",p=0;o=f[p++];)if(i&&_.inArray(o,i)>-1)r&&r.push(o);else if(u=me(o),a=Ee(d.appendChild(o),"script"),u&&De(a),n)for(c=0;o=a[c++];)Se.test(o.type||"")&&n.push(o);return d}var Le=/^([^.]*)(?:\.(.+)|)/;function Pe(){return!0}function Me(){return!1}function Oe(e,t,n,i,r,o){var a,s;if("object"==typeof t){for(s in"string"!=typeof n&&(i=i||n,n=void 0),t)Oe(e,s,n,i,t[s],o);return e}if(null==i&&null==r?(r=n,i=n=void 0):null==r&&("string"==typeof n?(r=i,i=void 0):(r=i,i=n,n=void 0)),!1===r)r=Me;else if(!r)return e;return 1===o&&(a=r,r=function(e){return _().off(e),a.apply(this,arguments)},r.guid=a.guid||(a.guid=_.guid++)),e.each((function(){_.event.add(this,t,r,i,n)}))}function qe(e,t,n){n?(se.set(e,t,!1),_.event.add(e,t,{namespace:!1,handler:function(e){var n,i=se.get(this,t);if(1&e.isTrigger&&this[t]){if(i)(_.event.special[t]||{}).delegateType&&e.stopPropagation();else if(i=s.call(arguments),se.set(this,t,i),this[t](),n=se.get(this,t),se.set(this,t,!1),i!==n)return e.stopImmediatePropagation(),e.preventDefault(),n}else i&&(se.set(this,t,_.event.trigger(i[0],i.slice(1),this)),e.stopPropagation(),e.isImmediatePropagationStopped=Pe)}})):void 0===se.get(e,t)&&_.event.add(e,t,Pe)}_.event={global:{},add:function(e,t,n,i,r){var o,a,s,l,u,c,d,f,p,h,g,m=se.get(e);if(oe(e))for(n.handler&&(n=(o=n).handler,r=o.selector),r&&_.find.matchesSelector(ge,r),n.guid||(n.guid=_.guid++),(l=m.events)||(l=m.events=Object.create(null)),(a=m.handle)||(a=m.handle=function(t){return void 0!==_&&_.event.triggered!==t.type?_.event.dispatch.apply(e,arguments):void 0}),u=(t=(t||"").match(G)||[""]).length;u--;)p=g=(s=Le.exec(t[u])||[])[1],h=(s[2]||"").split(".").sort(),p&&(d=_.event.special[p]||{},p=(r?d.delegateType:d.bindType)||p,d=_.event.special[p]||{},c=_.extend({type:p,origType:g,data:i,handler:n,guid:n.guid,selector:r,needsContext:r&&_.expr.match.needsContext.test(r),namespace:h.join(".")},o),(f=l[p])||((f=l[p]=[]).delegateCount=0,d.setup&&!1!==d.setup.call(e,i,h,a)||e.addEventListener&&e.addEventListener(p,a)),d.add&&(d.add.call(e,c),c.handler.guid||(c.handler.guid=n.guid)),r?f.splice(f.delegateCount++,0,c):f.push(c),_.event.global[p]=!0)},remove:function(e,t,n,i,r){var o,a,s,l,u,c,d,f,p,h,g,m=se.hasData(e)&&se.get(e);if(m&&(l=m.events)){for(u=(t=(t||"").match(G)||[""]).length;u--;)if(p=g=(s=Le.exec(t[u])||[])[1],h=(s[2]||"").split(".").sort(),p){for(d=_.event.special[p]||{},f=l[p=(i?d.delegateType:d.bindType)||p]||[],s=s[2]&&new RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"),a=o=f.length;o--;)c=f[o],!r&&g!==c.origType||n&&n.guid!==c.guid||s&&!s.test(c.namespace)||i&&i!==c.selector&&("**"!==i||!c.selector)||(f.splice(o,1),c.selector&&f.delegateCount--,d.remove&&d.remove.call(e,c));a&&!f.length&&(d.teardown&&!1!==d.teardown.call(e,h,m.handle)||_.removeEvent(e,p,m.handle),delete l[p])}else for(p in l)_.event.remove(e,p+t[u],n,i,!0);_.isEmptyObject(l)&&se.remove(e,"handle events")}},dispatch:function(e){var t,n,i,r,o,a,s=new Array(arguments.length),l=_.event.fix(e),u=(se.get(this,"events")||Object.create(null))[l.type]||[],c=_.event.special[l.type]||{};for(s[0]=l,t=1;t<arguments.length;t++)s[t]=arguments[t];if(l.delegateTarget=this,!c.preDispatch||!1!==c.preDispatch.call(this,l)){for(a=_.event.handlers.call(this,l,u),t=0;(r=a[t++])&&!l.isPropagationStopped();)for(l.currentTarget=r.elem,n=0;(o=r.handlers[n++])&&!l.isImmediatePropagationStopped();)l.rnamespace&&!1!==o.namespace&&!l.rnamespace.test(o.namespace)||(l.handleObj=o,l.data=o.data,void 0!==(i=((_.event.special[o.origType]||{}).handle||o.handler).apply(r.elem,s))&&!1===(l.result=i)&&(l.preventDefault(),l.stopPropagation()));return c.postDispatch&&c.postDispatch.call(this,l),l.result}},handlers:function(e,t){var n,i,r,o,a,s=[],l=t.delegateCount,u=e.target;if(l&&u.nodeType&&!("click"===e.type&&e.button>=1))for(;u!==this;u=u.parentNode||this)if(1===u.nodeType&&("click"!==e.type||!0!==u.disabled)){for(o=[],a={},n=0;n<l;n++)void 0===a[r=(i=t[n]).selector+" "]&&(a[r]=i.needsContext?_(r,this).index(u)>-1:_.find(r,this,null,[u]).length),a[r]&&o.push(i);o.length&&s.push({elem:u,handlers:o})}return u=this,l<t.length&&s.push({elem:u,handlers:t.slice(l)}),s},addProp:function(e,t){Object.defineProperty(_.Event.prototype,e,{enumerable:!0,configurable:!0,get:v(t)?function(){if(this.originalEvent)return t(this.originalEvent)}:function(){if(this.originalEvent)return this.originalEvent[e]},set:function(t){Object.defineProperty(this,e,{enumerable:!0,configurable:!0,writable:!0,value:t})}})},fix:function(e){return e[_.expando]?e:new _.Event(e)},special:{load:{noBubble:!0},click:{setup:function(e){var t=this||e;return _e.test(t.type)&&t.click&&S(t,"input")&&qe(t,"click",!0),!1},trigger:function(e){var t=this||e;return _e.test(t.type)&&t.click&&S(t,"input")&&qe(t,"click"),!0},_default:function(e){var t=e.target;return _e.test(t.type)&&t.click&&S(t,"input")&&se.get(t,"click")||S(t,"a")}},beforeunload:{postDispatch:function(e){void 0!==e.result&&e.originalEvent&&(e.originalEvent.returnValue=e.result)}}}},_.removeEvent=function(e,t,n){e.removeEventListener&&e.removeEventListener(t,n)},_.Event=function(e,t){if(!(this instanceof _.Event))return new _.Event(e,t);e&&e.type?(this.originalEvent=e,this.type=e.type,this.isDefaultPrevented=e.defaultPrevented||void 0===e.defaultPrevented&&!1===e.returnValue?Pe:Me,this.target=e.target&&3===e.target.nodeType?e.target.parentNode:e.target,this.currentTarget=e.currentTarget,this.relatedTarget=e.relatedTarget):this.type=e,t&&_.extend(this,t),this.timeStamp=e&&e.timeStamp||Date.now(),this[_.expando]=!0},_.Event.prototype={constructor:_.Event,isDefaultPrevented:Me,isPropagationStopped:Me,isImmediatePropagationStopped:Me,isSimulated:!1,preventDefault:function(){var e=this.originalEvent;this.isDefaultPrevented=Pe,e&&!this.isSimulated&&e.preventDefault()},stopPropagation:function(){var e=this.originalEvent;this.isPropagationStopped=Pe,e&&!this.isSimulated&&e.stopPropagation()},stopImmediatePropagation:function(){var e=this.originalEvent;this.isImmediatePropagationStopped=Pe,e&&!this.isSimulated&&e.stopImmediatePropagation(),this.stopPropagation()}},_.each({altKey:!0,bubbles:!0,cancelable:!0,changedTouches:!0,ctrlKey:!0,detail:!0,eventPhase:!0,metaKey:!0,pageX:!0,pageY:!0,shiftKey:!0,view:!0,char:!0,code:!0,charCode:!0,key:!0,keyCode:!0,button:!0,buttons:!0,clientX:!0,clientY:!0,offsetX:!0,offsetY:!0,pointerId:!0,pointerType:!0,screenX:!0,screenY:!0,targetTouches:!0,toElement:!0,touches:!0,which:!0},_.event.addProp),_.each({focus:"focusin",blur:"focusout"},(function(e,t){function n(e){if(x.documentMode){var n=se.get(this,"handle"),i=_.event.fix(e);i.type="focusin"===e.type?"focus":"blur",i.isSimulated=!0,n(e),i.target===i.currentTarget&&n(i)}else _.event.simulate(t,e.target,_.event.fix(e))}_.event.special[e]={setup:function(){var i;if(qe(this,e,!0),!x.documentMode)return!1;(i=se.get(this,t))||this.addEventListener(t,n),se.set(this,t,(i||0)+1)},trigger:function(){return qe(this,e),!0},teardown:function(){var e;if(!x.documentMode)return!1;(e=se.get(this,t)-1)?se.set(this,t,e):(this.removeEventListener(t,n),se.remove(this,t))},_default:function(t){return se.get(t.target,e)},delegateType:t},_.event.special[t]={setup:function(){var i=this.ownerDocument||this.document||this,r=x.documentMode?this:i,o=se.get(r,t);o||(x.documentMode?this.addEventListener(t,n):i.addEventListener(e,n,!0)),se.set(r,t,(o||0)+1)},teardown:function(){var i=this.ownerDocument||this.document||this,r=x.documentMode?this:i,o=se.get(r,t)-1;o?se.set(r,t,o):(x.documentMode?this.removeEventListener(t,n):i.removeEventListener(e,n,!0),se.remove(r,t))}}})),_.each({mouseenter:"mouseover",mouseleave:"mouseout",pointerenter:"pointerover",pointerleave:"pointerout"},(function(e,t){_.event.special[e]={delegateType:t,bindType:t,handle:function(e){var n,i=e.relatedTarget,r=e.handleObj;return i&&(i===this||_.contains(this,i))||(e.type=r.origType,n=r.handler.apply(this,arguments),e.type=t),n}}})),_.fn.extend({on:function(e,t,n,i){return Oe(this,e,t,n,i)},one:function(e,t,n,i){return Oe(this,e,t,n,i,1)},off:function(e,t,n){var i,r;if(e&&e.preventDefault&&e.handleObj)return i=e.handleObj,_(e.delegateTarget).off(i.namespace?i.origType+"."+i.namespace:i.origType,i.selector,i.handler),this;if("object"==typeof e){for(r in e)this.off(r,t,e[r]);return this}return!1!==t&&"function"!=typeof t||(n=t,t=void 0),!1===n&&(n=Me),this.each((function(){_.event.remove(this,e,n,t)}))}});var He=/<script|<style|<link/i,Ie=/checked\s*(?:[^=]|=\s*.checked.)/i,Re=/^\s*<!\[CDATA\[|\]\]>\s*$/g;function ze(e,t){return S(e,"table")&&S(11!==t.nodeType?t:t.firstChild,"tr")&&_(e).children("tbody")[0]||e}function Be(e){return e.type=(null!==e.getAttribute("type"))+"/"+e.type,e}function We(e){return"true/"===(e.type||"").slice(0,5)?e.type=e.type.slice(5):e.removeAttribute("type"),e}function $e(e,t){var n,i,r,o,a,s;if(1===t.nodeType){if(se.hasData(e)&&(s=se.get(e).events))for(r in se.remove(t,"handle events"),s)for(n=0,i=s[r].length;n<i;n++)_.event.add(t,r,s[r][n]);le.hasData(e)&&(o=le.access(e),a=_.extend({},o),le.set(t,a))}}function Ue(e,t){var n=t.nodeName.toLowerCase();"input"===n&&_e.test(e.type)?t.checked=e.checked:"input"!==n&&"textarea"!==n||(t.defaultValue=e.defaultValue)}function Xe(e,t,n,i){t=l(t);var r,o,a,s,u,c,d=0,f=e.length,p=f-1,h=t[0],g=v(h);if(g||f>1&&"string"==typeof h&&!m.checkClone&&Ie.test(h))return e.each((function(r){var o=e.eq(r);g&&(t[0]=h.call(this,r,o.html())),Xe(o,t,n,i)}));if(f&&(o=(r=Ne(t,e[0].ownerDocument,!1,e,i)).firstChild,1===r.childNodes.length&&(r=o),o||i)){for(s=(a=_.map(Ee(r,"script"),Be)).length;d<f;d++)u=r,d!==p&&(u=_.clone(u,!0,!0),s&&_.merge(a,Ee(u,"script"))),n.call(e[d],u,d);if(s)for(c=a[a.length-1].ownerDocument,_.map(a,We),d=0;d<s;d++)u=a[d],Se.test(u.type||"")&&!se.access(u,"globalEval")&&_.contains(c,u)&&(u.src&&"module"!==(u.type||"").toLowerCase()?_._evalUrl&&!u.noModule&&_._evalUrl(u.src,{nonce:u.nonce||u.getAttribute("nonce")},c):w(u.textContent.replace(Re,""),u,c))}return e}function Ge(e,t,n){for(var i,r=t?_.filter(t,e):e,o=0;null!=(i=r[o]);o++)n||1!==i.nodeType||_.cleanData(Ee(i)),i.parentNode&&(n&&me(i)&&De(Ee(i,"script")),i.parentNode.removeChild(i));return e}_.extend({htmlPrefilter:function(e){return e},clone:function(e,t,n){var i,r,o,a,s=e.cloneNode(!0),l=me(e);if(!(m.noCloneChecked||1!==e.nodeType&&11!==e.nodeType||_.isXMLDoc(e)))for(a=Ee(s),i=0,r=(o=Ee(e)).length;i<r;i++)Ue(o[i],a[i]);if(t)if(n)for(o=o||Ee(e),a=a||Ee(s),i=0,r=o.length;i<r;i++)$e(o[i],a[i]);else $e(e,s);return(a=Ee(s,"script")).length>0&&De(a,!l&&Ee(e,"script")),s},cleanData:function(e){for(var t,n,i,r=_.event.special,o=0;void 0!==(n=e[o]);o++)if(oe(n)){if(t=n[se.expando]){if(t.events)for(i in t.events)r[i]?_.event.remove(n,i):_.removeEvent(n,i,t.handle);n[se.expando]=void 0}n[le.expando]&&(n[le.expando]=void 0)}}}),_.fn.extend({detach:function(e){return Ge(this,e,!0)},remove:function(e){return Ge(this,e)},text:function(e){return ee(this,(function(e){return void 0===e?_.text(this):this.empty().each((function(){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||(this.textContent=e)}))}),null,e,arguments.length)},append:function(){return Xe(this,arguments,(function(e){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||ze(this,e).appendChild(e)}))},prepend:function(){return Xe(this,arguments,(function(e){if(1===this.nodeType||11===this.nodeType||9===this.nodeType){var t=ze(this,e);t.insertBefore(e,t.firstChild)}}))},before:function(){return Xe(this,arguments,(function(e){this.parentNode&&this.parentNode.insertBefore(e,this)}))},after:function(){return Xe(this,arguments,(function(e){this.parentNode&&this.parentNode.insertBefore(e,this.nextSibling)}))},empty:function(){for(var e,t=0;null!=(e=this[t]);t++)1===e.nodeType&&(_.cleanData(Ee(e,!1)),e.textContent="");return this},clone:function(e,t){return e=null!=e&&e,t=null==t?e:t,this.map((function(){return _.clone(this,e,t)}))},html:function(e){return ee(this,(function(e){var t=this[0]||{},n=0,i=this.length;if(void 0===e&&1===t.nodeType)return t.innerHTML;if("string"==typeof e&&!He.test(e)&&!ke[(Fe.exec(e)||["",""])[1].toLowerCase()]){e=_.htmlPrefilter(e);try{for(;n<i;n++)1===(t=this[n]||{}).nodeType&&(_.cleanData(Ee(t,!1)),t.innerHTML=e);t=0}catch(e){}}t&&this.empty().append(e)}),null,e,arguments.length)},replaceWith:function(){var e=[];return Xe(this,arguments,(function(t){var n=this.parentNode;_.inArray(this,e)<0&&(_.cleanData(Ee(this)),n&&n.replaceChild(t,this))}),e)}}),_.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},(function(e,t){_.fn[e]=function(e){for(var n,i=[],r=_(e),o=r.length-1,a=0;a<=o;a++)n=a===o?this:this.clone(!0),_(r[a])[t](n),u.apply(i,n.get());return this.pushStack(i)}}));var Ve=new RegExp("^("+fe+")(?!px)[a-z%]+$","i"),Ye=/^--/,Je=function(e){var t=e.ownerDocument.defaultView;return t&&t.opener||(t=i),t.getComputedStyle(e)},Qe=function(e,t,n){var i,r,o={};for(r in t)o[r]=e.style[r],e.style[r]=t[r];for(r in i=n.call(e),t)e.style[r]=o[r];return i},Ke=new RegExp(he.join("|"),"i");function Ze(e,t,n){var i,r,o,a,s=Ye.test(t),l=e.style;return(n=n||Je(e))&&(a=n.getPropertyValue(t)||n[t],s&&a&&(a=a.replace(N,"$1")||void 0),""!==a||me(e)||(a=_.style(e,t)),!m.pixelBoxStyles()&&Ve.test(a)&&Ke.test(t)&&(i=l.width,r=l.minWidth,o=l.maxWidth,l.minWidth=l.maxWidth=l.width=a,a=n.width,l.width=i,l.minWidth=r,l.maxWidth=o)),void 0!==a?a+"":a}function et(e,t){return{get:function(){if(!e())return(this.get=t).apply(this,arguments);delete this.get}}}!function(){function e(){if(c){u.style.cssText="position:absolute;left:-11111px;width:60px;margin-top:1px;padding:0;border:0",c.style.cssText="position:relative;display:block;box-sizing:border-box;overflow:scroll;margin:auto;border:1px;padding:1px;width:60%;top:1%",ge.appendChild(u).appendChild(c);var e=i.getComputedStyle(c);n="1%"!==e.top,l=12===t(e.marginLeft),c.style.right="60%",a=36===t(e.right),r=36===t(e.width),c.style.position="absolute",o=12===t(c.offsetWidth/3),ge.removeChild(u),c=null}}function t(e){return Math.round(parseFloat(e))}var n,r,o,a,s,l,u=x.createElement("div"),c=x.createElement("div");c.style&&(c.style.backgroundClip="content-box",c.cloneNode(!0).style.backgroundClip="",m.clearCloneStyle="content-box"===c.style.backgroundClip,_.extend(m,{boxSizingReliable:function(){return e(),r},pixelBoxStyles:function(){return e(),a},pixelPosition:function(){return e(),n},reliableMarginLeft:function(){return e(),l},scrollboxSize:function(){return e(),o},reliableTrDimensions:function(){var e,t,n,r;return null==s&&(e=x.createElement("table"),t=x.createElement("tr"),n=x.createElement("div"),e.style.cssText="position:absolute;left:-11111px;border-collapse:separate",t.style.cssText="box-sizing:content-box;border:1px solid",t.style.height="1px",n.style.height="9px",n.style.display="block",ge.appendChild(e).appendChild(t).appendChild(n),r=i.getComputedStyle(t),s=parseInt(r.height,10)+parseInt(r.borderTopWidth,10)+parseInt(r.borderBottomWidth,10)===t.offsetHeight,ge.removeChild(e)),s}}))}();var tt=["Webkit","Moz","ms"],nt=x.createElement("div").style,it={};function rt(e){var t=_.cssProps[e]||it[e];return t||(e in nt?e:it[e]=function(e){for(var t=e[0].toUpperCase()+e.slice(1),n=tt.length;n--;)if((e=tt[n]+t)in nt)return e}(e)||e)}var ot=/^(none|table(?!-c[ea]).+)/,at={position:"absolute",visibility:"hidden",display:"block"},st={letterSpacing:"0",fontWeight:"400"};function lt(e,t,n){var i=pe.exec(t);return i?Math.max(0,i[2]-(n||0))+(i[3]||"px"):t}function ut(e,t,n,i,r,o){var a="width"===t?1:0,s=0,l=0,u=0;if(n===(i?"border":"content"))return 0;for(;a<4;a+=2)"margin"===n&&(u+=_.css(e,n+he[a],!0,r)),i?("content"===n&&(l-=_.css(e,"padding"+he[a],!0,r)),"margin"!==n&&(l-=_.css(e,"border"+he[a]+"Width",!0,r))):(l+=_.css(e,"padding"+he[a],!0,r),"padding"!==n?l+=_.css(e,"border"+he[a]+"Width",!0,r):s+=_.css(e,"border"+he[a]+"Width",!0,r));return!i&&o>=0&&(l+=Math.max(0,Math.ceil(e["offset"+t[0].toUpperCase()+t.slice(1)]-o-l-s-.5))||0),l+u}function ct(e,t,n){var i=Je(e),r=(!m.boxSizingReliable()||n)&&"border-box"===_.css(e,"boxSizing",!1,i),o=r,a=Ze(e,t,i),s="offset"+t[0].toUpperCase()+t.slice(1);if(Ve.test(a)){if(!n)return a;a="auto"}return(!m.boxSizingReliable()&&r||!m.reliableTrDimensions()&&S(e,"tr")||"auto"===a||!parseFloat(a)&&"inline"===_.css(e,"display",!1,i))&&e.getClientRects().length&&(r="border-box"===_.css(e,"boxSizing",!1,i),(o=s in e)&&(a=e[s])),(a=parseFloat(a)||0)+ut(e,t,n||(r?"border":"content"),o,i,a)+"px"}function dt(e,t,n,i,r){return new dt.prototype.init(e,t,n,i,r)}_.extend({cssHooks:{opacity:{get:function(e,t){if(t){var n=Ze(e,"opacity");return""===n?"1":n}}}},cssNumber:{animationIterationCount:!0,aspectRatio:!0,borderImageSlice:!0,columnCount:!0,flexGrow:!0,flexShrink:!0,fontWeight:!0,gridArea:!0,gridColumn:!0,gridColumnEnd:!0,gridColumnStart:!0,gridRow:!0,gridRowEnd:!0,gridRowStart:!0,lineHeight:!0,opacity:!0,order:!0,orphans:!0,scale:!0,widows:!0,zIndex:!0,zoom:!0,fillOpacity:!0,floodOpacity:!0,stopOpacity:!0,strokeMiterlimit:!0,strokeOpacity:!0},cssProps:{},style:function(e,t,n,i){if(e&&3!==e.nodeType&&8!==e.nodeType&&e.style){var r,o,a,s=re(t),l=Ye.test(t),u=e.style;if(l||(t=rt(s)),a=_.cssHooks[t]||_.cssHooks[s],void 0===n)return a&&"get"in a&&void 0!==(r=a.get(e,!1,i))?r:u[t];"string"===(o=typeof n)&&(r=pe.exec(n))&&r[1]&&(n=xe(e,t,r),o="number"),null!=n&&n==n&&("number"!==o||l||(n+=r&&r[3]||(_.cssNumber[s]?"":"px")),m.clearCloneStyle||""!==n||0!==t.indexOf("background")||(u[t]="inherit"),a&&"set"in a&&void 0===(n=a.set(e,n,i))||(l?u.setProperty(t,n):u[t]=n))}},css:function(e,t,n,i){var r,o,a,s=re(t);return Ye.test(t)||(t=rt(s)),(a=_.cssHooks[t]||_.cssHooks[s])&&"get"in a&&(r=a.get(e,!0,n)),void 0===r&&(r=Ze(e,t,i)),"normal"===r&&t in st&&(r=st[t]),""===n||n?(o=parseFloat(r),!0===n||isFinite(o)?o||0:r):r}}),_.each(["height","width"],(function(e,t){_.cssHooks[t]={get:function(e,n,i){if(n)return!ot.test(_.css(e,"display"))||e.getClientRects().length&&e.getBoundingClientRect().width?ct(e,t,i):Qe(e,at,(function(){return ct(e,t,i)}))},set:function(e,n,i){var r,o=Je(e),a=!m.scrollboxSize()&&"absolute"===o.position,s=(a||i)&&"border-box"===_.css(e,"boxSizing",!1,o),l=i?ut(e,t,i,s,o):0;return s&&a&&(l-=Math.ceil(e["offset"+t[0].toUpperCase()+t.slice(1)]-parseFloat(o[t])-ut(e,t,"border",!1,o)-.5)),l&&(r=pe.exec(n))&&"px"!==(r[3]||"px")&&(e.style[t]=n,n=_.css(e,t)),lt(0,n,l)}}})),_.cssHooks.marginLeft=et(m.reliableMarginLeft,(function(e,t){if(t)return(parseFloat(Ze(e,"marginLeft"))||e.getBoundingClientRect().left-Qe(e,{marginLeft:0},(function(){return e.getBoundingClientRect().left})))+"px"})),_.each({margin:"",padding:"",border:"Width"},(function(e,t){_.cssHooks[e+t]={expand:function(n){for(var i=0,r={},o="string"==typeof n?n.split(" "):[n];i<4;i++)r[e+he[i]+t]=o[i]||o[i-2]||o[0];return r}},"margin"!==e&&(_.cssHooks[e+t].set=lt)})),_.fn.extend({css:function(e,t){return ee(this,(function(e,t,n){var i,r,o={},a=0;if(Array.isArray(t)){for(i=Je(e),r=t.length;a<r;a++)o[t[a]]=_.css(e,t[a],!1,i);return o}return void 0!==n?_.style(e,t,n):_.css(e,t)}),e,t,arguments.length>1)}}),_.Tween=dt,dt.prototype={constructor:dt,init:function(e,t,n,i,r,o){this.elem=e,this.prop=n,this.easing=r||_.easing._default,this.options=t,this.start=this.now=this.cur(),this.end=i,this.unit=o||(_.cssNumber[n]?"":"px")},cur:function(){var e=dt.propHooks[this.prop];return e&&e.get?e.get(this):dt.propHooks._default.get(this)},run:function(e){var t,n=dt.propHooks[this.prop];return this.options.duration?this.pos=t=_.easing[this.easing](e,this.options.duration*e,0,1,this.options.duration):this.pos=t=e,this.now=(this.end-this.start)*t+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),n&&n.set?n.set(this):dt.propHooks._default.set(this),this}},dt.prototype.init.prototype=dt.prototype,dt.propHooks={_default:{get:function(e){var t;return 1!==e.elem.nodeType||null!=e.elem[e.prop]&&null==e.elem.style[e.prop]?e.elem[e.prop]:(t=_.css(e.elem,e.prop,""))&&"auto"!==t?t:0},set:function(e){_.fx.step[e.prop]?_.fx.step[e.prop](e):1!==e.elem.nodeType||!_.cssHooks[e.prop]&&null==e.elem.style[rt(e.prop)]?e.elem[e.prop]=e.now:_.style(e.elem,e.prop,e.now+e.unit)}}},dt.propHooks.scrollTop=dt.propHooks.scrollLeft={set:function(e){e.elem.nodeType&&e.elem.parentNode&&(e.elem[e.prop]=e.now)}},_.easing={linear:function(e){return e},swing:function(e){return.5-Math.cos(e*Math.PI)/2},_default:"swing"},_.fx=dt.prototype.init,_.fx.step={};var ft,pt,ht=/^(?:toggle|show|hide)$/,gt=/queueHooks$/;function mt(){pt&&(!1===x.hidden&&i.requestAnimationFrame?i.requestAnimationFrame(mt):i.setTimeout(mt,_.fx.interval),_.fx.tick())}function vt(){return i.setTimeout((function(){ft=void 0})),ft=Date.now()}function yt(e,t){var n,i=0,r={height:e};for(t=t?1:0;i<4;i+=2-t)r["margin"+(n=he[i])]=r["padding"+n]=e;return t&&(r.opacity=r.width=e),r}function xt(e,t,n){for(var i,r=(bt.tweeners[t]||[]).concat(bt.tweeners["*"]),o=0,a=r.length;o<a;o++)if(i=r[o].call(n,t,e))return i}function bt(e,t,n){var i,r,o=0,a=bt.prefilters.length,s=_.Deferred().always((function(){delete l.elem})),l=function(){if(r)return!1;for(var t=ft||vt(),n=Math.max(0,u.startTime+u.duration-t),i=1-(n/u.duration||0),o=0,a=u.tweens.length;o<a;o++)u.tweens[o].run(i);return s.notifyWith(e,[u,i,n]),i<1&&a?n:(a||s.notifyWith(e,[u,1,0]),s.resolveWith(e,[u]),!1)},u=s.promise({elem:e,props:_.extend({},t),opts:_.extend(!0,{specialEasing:{},easing:_.easing._default},n),originalProperties:t,originalOptions:n,startTime:ft||vt(),duration:n.duration,tweens:[],createTween:function(t,n){var i=_.Tween(e,u.opts,t,n,u.opts.specialEasing[t]||u.opts.easing);return u.tweens.push(i),i},stop:function(t){var n=0,i=t?u.tweens.length:0;if(r)return this;for(r=!0;n<i;n++)u.tweens[n].run(1);return t?(s.notifyWith(e,[u,1,0]),s.resolveWith(e,[u,t])):s.rejectWith(e,[u,t]),this}}),c=u.props;for(!function(e,t){var n,i,r,o,a;for(n in e)if(r=t[i=re(n)],o=e[n],Array.isArray(o)&&(r=o[1],o=e[n]=o[0]),n!==i&&(e[i]=o,delete e[n]),(a=_.cssHooks[i])&&"expand"in a)for(n in o=a.expand(o),delete e[i],o)n in e||(e[n]=o[n],t[n]=r);else t[i]=r}(c,u.opts.specialEasing);o<a;o++)if(i=bt.prefilters[o].call(u,e,c,u.opts))return v(i.stop)&&(_._queueHooks(u.elem,u.opts.queue).stop=i.stop.bind(i)),i;return _.map(c,xt,u),v(u.opts.start)&&u.opts.start.call(e,u),u.progress(u.opts.progress).done(u.opts.done,u.opts.complete).fail(u.opts.fail).always(u.opts.always),_.fx.timer(_.extend(l,{elem:e,anim:u,queue:u.opts.queue})),u}_.Animation=_.extend(bt,{tweeners:{"*":[function(e,t){var n=this.createTween(e,t);return xe(n.elem,e,pe.exec(t),n),n}]},tweener:function(e,t){v(e)?(t=e,e=["*"]):e=e.match(G);for(var n,i=0,r=e.length;i<r;i++)n=e[i],bt.tweeners[n]=bt.tweeners[n]||[],bt.tweeners[n].unshift(t)},prefilters:[function(e,t,n){var i,r,o,a,s,l,u,c,d="width"in t||"height"in t,f=this,p={},h=e.style,g=e.nodeType&&ye(e),m=se.get(e,"fxshow");for(i in n.queue||(null==(a=_._queueHooks(e,"fx")).unqueued&&(a.unqueued=0,s=a.empty.fire,a.empty.fire=function(){a.unqueued||s()}),a.unqueued++,f.always((function(){f.always((function(){a.unqueued--,_.queue(e,"fx").length||a.empty.fire()}))}))),t)if(r=t[i],ht.test(r)){if(delete t[i],o=o||"toggle"===r,r===(g?"hide":"show")){if("show"!==r||!m||void 0===m[i])continue;g=!0}p[i]=m&&m[i]||_.style(e,i)}if((l=!_.isEmptyObject(t))||!_.isEmptyObject(p))for(i in d&&1===e.nodeType&&(n.overflow=[h.overflow,h.overflowX,h.overflowY],null==(u=m&&m.display)&&(u=se.get(e,"display")),"none"===(c=_.css(e,"display"))&&(u?c=u:(je([e],!0),u=e.style.display||u,c=_.css(e,"display"),je([e]))),("inline"===c||"inline-block"===c&&null!=u)&&"none"===_.css(e,"float")&&(l||(f.done((function(){h.display=u})),null==u&&(c=h.display,u="none"===c?"":c)),h.display="inline-block")),n.overflow&&(h.overflow="hidden",f.always((function(){h.overflow=n.overflow[0],h.overflowX=n.overflow[1],h.overflowY=n.overflow[2]}))),l=!1,p)l||(m?"hidden"in m&&(g=m.hidden):m=se.access(e,"fxshow",{display:u}),o&&(m.hidden=!g),g&&je([e],!0),f.done((function(){for(i in g||je([e]),se.remove(e,"fxshow"),p)_.style(e,i,p[i])}))),l=xt(g?m[i]:0,i,f),i in m||(m[i]=l.start,g&&(l.end=l.start,l.start=0))}],prefilter:function(e,t){t?bt.prefilters.unshift(e):bt.prefilters.push(e)}}),_.speed=function(e,t,n){var i=e&&"object"==typeof e?_.extend({},e):{complete:n||!n&&t||v(e)&&e,duration:e,easing:n&&t||t&&!v(t)&&t};return _.fx.off?i.duration=0:"number"!=typeof i.duration&&(i.duration in _.fx.speeds?i.duration=_.fx.speeds[i.duration]:i.duration=_.fx.speeds._default),null!=i.queue&&!0!==i.queue||(i.queue="fx"),i.old=i.complete,i.complete=function(){v(i.old)&&i.old.call(this),i.queue&&_.dequeue(this,i.queue)},i},_.fn.extend({fadeTo:function(e,t,n,i){return this.filter(ye).css("opacity",0).show().end().animate({opacity:t},e,n,i)},animate:function(e,t,n,i){var r=_.isEmptyObject(e),o=_.speed(t,n,i),a=function(){var t=bt(this,_.extend({},e),o);(r||se.get(this,"finish"))&&t.stop(!0)};return a.finish=a,r||!1===o.queue?this.each(a):this.queue(o.queue,a)},stop:function(e,t,n){var i=function(e){var t=e.stop;delete e.stop,t(n)};return"string"!=typeof e&&(n=t,t=e,e=void 0),t&&this.queue(e||"fx",[]),this.each((function(){var t=!0,r=null!=e&&e+"queueHooks",o=_.timers,a=se.get(this);if(r)a[r]&&a[r].stop&&i(a[r]);else for(r in a)a[r]&&a[r].stop&&gt.test(r)&&i(a[r]);for(r=o.length;r--;)o[r].elem!==this||null!=e&&o[r].queue!==e||(o[r].anim.stop(n),t=!1,o.splice(r,1));!t&&n||_.dequeue(this,e)}))},finish:function(e){return!1!==e&&(e=e||"fx"),this.each((function(){var t,n=se.get(this),i=n[e+"queue"],r=n[e+"queueHooks"],o=_.timers,a=i?i.length:0;for(n.finish=!0,_.queue(this,e,[]),r&&r.stop&&r.stop.call(this,!0),t=o.length;t--;)o[t].elem===this&&o[t].queue===e&&(o[t].anim.stop(!0),o.splice(t,1));for(t=0;t<a;t++)i[t]&&i[t].finish&&i[t].finish.call(this);delete n.finish}))}}),_.each(["toggle","show","hide"],(function(e,t){var n=_.fn[t];_.fn[t]=function(e,i,r){return null==e||"boolean"==typeof e?n.apply(this,arguments):this.animate(yt(t,!0),e,i,r)}})),_.each({slideDown:yt("show"),slideUp:yt("hide"),slideToggle:yt("toggle"),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},(function(e,t){_.fn[e]=function(e,n,i){return this.animate(t,e,n,i)}})),_.timers=[],_.fx.tick=function(){var e,t=0,n=_.timers;for(ft=Date.now();t<n.length;t++)(e=n[t])()||n[t]!==e||n.splice(t--,1);n.length||_.fx.stop(),ft=void 0},_.fx.timer=function(e){_.timers.push(e),_.fx.start()},_.fx.interval=13,_.fx.start=function(){pt||(pt=!0,mt())},_.fx.stop=function(){pt=null},_.fx.speeds={slow:600,fast:200,_default:400},_.fn.delay=function(e,t){return e=_.fx&&_.fx.speeds[e]||e,t=t||"fx",this.queue(t,(function(t,n){var r=i.setTimeout(t,e);n.stop=function(){i.clearTimeout(r)}}))},function(){var e=x.createElement("input"),t=x.createElement("select").appendChild(x.createElement("option"));e.type="checkbox",m.checkOn=""!==e.value,m.optSelected=t.selected,(e=x.createElement("input")).value="t",e.type="radio",m.radioValue="t"===e.value}();var wt,jt=_.expr.attrHandle;_.fn.extend({attr:function(e,t){return ee(this,_.attr,e,t,arguments.length>1)},removeAttr:function(e){return this.each((function(){_.removeAttr(this,e)}))}}),_.extend({attr:function(e,t,n){var i,r,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return void 0===e.getAttribute?_.prop(e,t,n):(1===o&&_.isXMLDoc(e)||(r=_.attrHooks[t.toLowerCase()]||(_.expr.match.bool.test(t)?wt:void 0)),void 0!==n?null===n?void _.removeAttr(e,t):r&&"set"in r&&void 0!==(i=r.set(e,n,t))?i:(e.setAttribute(t,n+""),n):r&&"get"in r&&null!==(i=r.get(e,t))?i:null==(i=_.find.attr(e,t))?void 0:i)},attrHooks:{type:{set:function(e,t){if(!m.radioValue&&"radio"===t&&S(e,"input")){var n=e.value;return e.setAttribute("type",t),n&&(e.value=n),t}}}},removeAttr:function(e,t){var n,i=0,r=t&&t.match(G);if(r&&1===e.nodeType)for(;n=r[i++];)e.removeAttribute(n)}}),wt={set:function(e,t,n){return!1===t?_.removeAttr(e,n):e.setAttribute(n,n),n}},_.each(_.expr.match.bool.source.match(/\w+/g),(function(e,t){var n=jt[t]||_.find.attr;jt[t]=function(e,t,i){var r,o,a=t.toLowerCase();return i||(o=jt[a],jt[a]=r,r=null!=n(e,t,i)?a:null,jt[a]=o),r}}));var Tt=/^(?:input|select|textarea|button)$/i,Ct=/^(?:a|area)$/i;function _t(e){return(e.match(G)||[]).join(" ")}function Ft(e){return e.getAttribute&&e.getAttribute("class")||""}function St(e){return Array.isArray(e)?e:"string"==typeof e&&e.match(G)||[]}_.fn.extend({prop:function(e,t){return ee(this,_.prop,e,t,arguments.length>1)},removeProp:function(e){return this.each((function(){delete this[_.propFix[e]||e]}))}}),_.extend({prop:function(e,t,n){var i,r,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return 1===o&&_.isXMLDoc(e)||(t=_.propFix[t]||t,r=_.propHooks[t]),void 0!==n?r&&"set"in r&&void 0!==(i=r.set(e,n,t))?i:e[t]=n:r&&"get"in r&&null!==(i=r.get(e,t))?i:e[t]},propHooks:{tabIndex:{get:function(e){var t=_.find.attr(e,"tabindex");return t?parseInt(t,10):Tt.test(e.nodeName)||Ct.test(e.nodeName)&&e.href?0:-1}}},propFix:{for:"htmlFor",class:"className"}}),m.optSelected||(_.propHooks.selected={get:function(e){var t=e.parentNode;return t&&t.parentNode&&t.parentNode.selectedIndex,null},set:function(e){var t=e.parentNode;t&&(t.selectedIndex,t.parentNode&&t.parentNode.selectedIndex)}}),_.each(["tabIndex","readOnly","maxLength","cellSpacing","cellPadding","rowSpan","colSpan","useMap","frameBorder","contentEditable"],(function(){_.propFix[this.toLowerCase()]=this})),_.fn.extend({addClass:function(e){var t,n,i,r,o,a;return v(e)?this.each((function(t){_(this).addClass(e.call(this,t,Ft(this)))})):(t=St(e)).length?this.each((function(){if(i=Ft(this),n=1===this.nodeType&&" "+_t(i)+" "){for(o=0;o<t.length;o++)r=t[o],n.indexOf(" "+r+" ")<0&&(n+=r+" ");a=_t(n),i!==a&&this.setAttribute("class",a)}})):this},removeClass:function(e){var t,n,i,r,o,a;return v(e)?this.each((function(t){_(this).removeClass(e.call(this,t,Ft(this)))})):arguments.length?(t=St(e)).length?this.each((function(){if(i=Ft(this),n=1===this.nodeType&&" "+_t(i)+" "){for(o=0;o<t.length;o++)for(r=t[o];n.indexOf(" "+r+" ")>-1;)n=n.replace(" "+r+" "," ");a=_t(n),i!==a&&this.setAttribute("class",a)}})):this:this.attr("class","")},toggleClass:function(e,t){var n,i,r,o,a=typeof e,s="string"===a||Array.isArray(e);return v(e)?this.each((function(n){_(this).toggleClass(e.call(this,n,Ft(this),t),t)})):"boolean"==typeof t&&s?t?this.addClass(e):this.removeClass(e):(n=St(e),this.each((function(){if(s)for(o=_(this),r=0;r<n.length;r++)i=n[r],o.hasClass(i)?o.removeClass(i):o.addClass(i);else void 0!==e&&"boolean"!==a||((i=Ft(this))&&se.set(this,"__className__",i),this.setAttribute&&this.setAttribute("class",i||!1===e?"":se.get(this,"__className__")||""))})))},hasClass:function(e){var t,n,i=0;for(t=" "+e+" ";n=this[i++];)if(1===n.nodeType&&(" "+_t(Ft(n))+" ").indexOf(t)>-1)return!0;return!1}});var kt=/\r/g;_.fn.extend({val:function(e){var t,n,i,r=this[0];return arguments.length?(i=v(e),this.each((function(n){var r;1===this.nodeType&&(null==(r=i?e.call(this,n,_(this).val()):e)?r="":"number"==typeof r?r+="":Array.isArray(r)&&(r=_.map(r,(function(e){return null==e?"":e+""}))),(t=_.valHooks[this.type]||_.valHooks[this.nodeName.toLowerCase()])&&"set"in t&&void 0!==t.set(this,r,"value")||(this.value=r))}))):r?(t=_.valHooks[r.type]||_.valHooks[r.nodeName.toLowerCase()])&&"get"in t&&void 0!==(n=t.get(r,"value"))?n:"string"==typeof(n=r.value)?n.replace(kt,""):null==n?"":n:void 0}}),_.extend({valHooks:{option:{get:function(e){var t=_.find.attr(e,"value");return null!=t?t:_t(_.text(e))}},select:{get:function(e){var t,n,i,r=e.options,o=e.selectedIndex,a="select-one"===e.type,s=a?null:[],l=a?o+1:r.length;for(i=o<0?l:a?o:0;i<l;i++)if(((n=r[i]).selected||i===o)&&!n.disabled&&(!n.parentNode.disabled||!S(n.parentNode,"optgroup"))){if(t=_(n).val(),a)return t;s.push(t)}return s},set:function(e,t){for(var n,i,r=e.options,o=_.makeArray(t),a=r.length;a--;)((i=r[a]).selected=_.inArray(_.valHooks.option.get(i),o)>-1)&&(n=!0);return n||(e.selectedIndex=-1),o}}}}),_.each(["radio","checkbox"],(function(){_.valHooks[this]={set:function(e,t){if(Array.isArray(t))return e.checked=_.inArray(_(e).val(),t)>-1}},m.checkOn||(_.valHooks[this].get=function(e){return null===e.getAttribute("value")?"on":e.value})}));var Et=i.location,Dt={guid:Date.now()},At=/\?/;_.parseXML=function(e){var t,n;if(!e||"string"!=typeof e)return null;try{t=(new i.DOMParser).parseFromString(e,"text/xml")}catch(e){}return n=t&&t.getElementsByTagName("parsererror")[0],t&&!n||_.error("Invalid XML: "+(n?_.map(n.childNodes,(function(e){return e.textContent})).join("\n"):e)),t};var Nt=/^(?:focusinfocus|focusoutblur)$/,Lt=function(e){e.stopPropagation()};_.extend(_.event,{trigger:function(e,t,n,r){var o,a,s,l,u,c,d,f,h=[n||x],g=p.call(e,"type")?e.type:e,m=p.call(e,"namespace")?e.namespace.split("."):[];if(a=f=s=n=n||x,3!==n.nodeType&&8!==n.nodeType&&!Nt.test(g+_.event.triggered)&&(g.indexOf(".")>-1&&(m=g.split("."),g=m.shift(),m.sort()),u=g.indexOf(":")<0&&"on"+g,(e=e[_.expando]?e:new _.Event(g,"object"==typeof e&&e)).isTrigger=r?2:3,e.namespace=m.join("."),e.rnamespace=e.namespace?new RegExp("(^|\\.)"+m.join("\\.(?:.*\\.|)")+"(\\.|$)"):null,e.result=void 0,e.target||(e.target=n),t=null==t?[e]:_.makeArray(t,[e]),d=_.event.special[g]||{},r||!d.trigger||!1!==d.trigger.apply(n,t))){if(!r&&!d.noBubble&&!y(n)){for(l=d.delegateType||g,Nt.test(l+g)||(a=a.parentNode);a;a=a.parentNode)h.push(a),s=a;s===(n.ownerDocument||x)&&h.push(s.defaultView||s.parentWindow||i)}for(o=0;(a=h[o++])&&!e.isPropagationStopped();)f=a,e.type=o>1?l:d.bindType||g,(c=(se.get(a,"events")||Object.create(null))[e.type]&&se.get(a,"handle"))&&c.apply(a,t),(c=u&&a[u])&&c.apply&&oe(a)&&(e.result=c.apply(a,t),!1===e.result&&e.preventDefault());return e.type=g,r||e.isDefaultPrevented()||d._default&&!1!==d._default.apply(h.pop(),t)||!oe(n)||u&&v(n[g])&&!y(n)&&((s=n[u])&&(n[u]=null),_.event.triggered=g,e.isPropagationStopped()&&f.addEventListener(g,Lt),n[g](),e.isPropagationStopped()&&f.removeEventListener(g,Lt),_.event.triggered=void 0,s&&(n[u]=s)),e.result}},simulate:function(e,t,n){var i=_.extend(new _.Event,n,{type:e,isSimulated:!0});_.event.trigger(i,null,t)}}),_.fn.extend({trigger:function(e,t){return this.each((function(){_.event.trigger(e,t,this)}))},triggerHandler:function(e,t){var n=this[0];if(n)return _.event.trigger(e,t,n,!0)}});var Pt=/\[\]$/,Mt=/\r?\n/g,Ot=/^(?:submit|button|image|reset|file)$/i,qt=/^(?:input|select|textarea|keygen)/i;function Ht(e,t,n,i){var r;if(Array.isArray(t))_.each(t,(function(t,r){n||Pt.test(e)?i(e,r):Ht(e+"["+("object"==typeof r&&null!=r?t:"")+"]",r,n,i)}));else if(n||"object"!==j(t))i(e,t);else for(r in t)Ht(e+"["+r+"]",t[r],n,i)}_.param=function(e,t){var n,i=[],r=function(e,t){var n=v(t)?t():t;i[i.length]=encodeURIComponent(e)+"="+encodeURIComponent(null==n?"":n)};if(null==e)return"";if(Array.isArray(e)||e.jquery&&!_.isPlainObject(e))_.each(e,(function(){r(this.name,this.value)}));else for(n in e)Ht(n,e[n],t,r);return i.join("&")},_.fn.extend({serialize:function(){return _.param(this.serializeArray())},serializeArray:function(){return this.map((function(){var e=_.prop(this,"elements");return e?_.makeArray(e):this})).filter((function(){var e=this.type;return this.name&&!_(this).is(":disabled")&&qt.test(this.nodeName)&&!Ot.test(e)&&(this.checked||!_e.test(e))})).map((function(e,t){var n=_(this).val();return null==n?null:Array.isArray(n)?_.map(n,(function(e){return{name:t.name,value:e.replace(Mt,"\r\n")}})):{name:t.name,value:n.replace(Mt,"\r\n")}})).get()}});var It=/%20/g,Rt=/#.*$/,zt=/([?&])_=[^&]*/,Bt=/^(.*?):[ \t]*([^\r\n]*)$/gm,Wt=/^(?:GET|HEAD)$/,$t=/^\/\//,Ut={},Xt={},Gt="*/".concat("*"),Vt=x.createElement("a");function Yt(e){return function(t,n){"string"!=typeof t&&(n=t,t="*");var i,r=0,o=t.toLowerCase().match(G)||[];if(v(n))for(;i=o[r++];)"+"===i[0]?(i=i.slice(1)||"*",(e[i]=e[i]||[]).unshift(n)):(e[i]=e[i]||[]).push(n)}}function Jt(e,t,n,i){var r={},o=e===Xt;function a(s){var l;return r[s]=!0,_.each(e[s]||[],(function(e,s){var u=s(t,n,i);return"string"!=typeof u||o||r[u]?o?!(l=u):void 0:(t.dataTypes.unshift(u),a(u),!1)})),l}return a(t.dataTypes[0])||!r["*"]&&a("*")}function Qt(e,t){var n,i,r=_.ajaxSettings.flatOptions||{};for(n in t)void 0!==t[n]&&((r[n]?e:i||(i={}))[n]=t[n]);return i&&_.extend(!0,e,i),e}Vt.href=Et.href,_.extend({active:0,lastModified:{},etag:{},ajaxSettings:{url:Et.href,type:"GET",isLocal:/^(?:about|app|app-storage|.+-extension|file|res|widget):$/.test(Et.protocol),global:!0,processData:!0,async:!0,contentType:"application/x-www-form-urlencoded; charset=UTF-8",accepts:{"*":Gt,text:"text/plain",html:"text/html",xml:"application/xml, text/xml",json:"application/json, text/javascript"},contents:{xml:/\bxml\b/,html:/\bhtml/,json:/\bjson\b/},responseFields:{xml:"responseXML",text:"responseText",json:"responseJSON"},converters:{"* text":String,"text html":!0,"text json":JSON.parse,"text xml":_.parseXML},flatOptions:{url:!0,context:!0}},ajaxSetup:function(e,t){return t?Qt(Qt(e,_.ajaxSettings),t):Qt(_.ajaxSettings,e)},ajaxPrefilter:Yt(Ut),ajaxTransport:Yt(Xt),ajax:function(e,t){"object"==typeof e&&(t=e,e=void 0),t=t||{};var n,r,o,a,s,l,u,c,d,f,p=_.ajaxSetup({},t),h=p.context||p,g=p.context&&(h.nodeType||h.jquery)?_(h):_.event,m=_.Deferred(),v=_.Callbacks("once memory"),y=p.statusCode||{},b={},w={},j="canceled",T={readyState:0,getResponseHeader:function(e){var t;if(u){if(!a)for(a={};t=Bt.exec(o);)a[t[1].toLowerCase()+" "]=(a[t[1].toLowerCase()+" "]||[]).concat(t[2]);t=a[e.toLowerCase()+" "]}return null==t?null:t.join(", ")},getAllResponseHeaders:function(){return u?o:null},setRequestHeader:function(e,t){return null==u&&(e=w[e.toLowerCase()]=w[e.toLowerCase()]||e,b[e]=t),this},overrideMimeType:function(e){return null==u&&(p.mimeType=e),this},statusCode:function(e){var t;if(e)if(u)T.always(e[T.status]);else for(t in e)y[t]=[y[t],e[t]];return this},abort:function(e){var t=e||j;return n&&n.abort(t),C(0,t),this}};if(m.promise(T),p.url=((e||p.url||Et.href)+"").replace($t,Et.protocol+"//"),p.type=t.method||t.type||p.method||p.type,p.dataTypes=(p.dataType||"*").toLowerCase().match(G)||[""],null==p.crossDomain){l=x.createElement("a");try{l.href=p.url,l.href=l.href,p.crossDomain=Vt.protocol+"//"+Vt.host!=l.protocol+"//"+l.host}catch(e){p.crossDomain=!0}}if(p.data&&p.processData&&"string"!=typeof p.data&&(p.data=_.param(p.data,p.traditional)),Jt(Ut,p,t,T),u)return T;for(d in(c=_.event&&p.global)&&0==_.active++&&_.event.trigger("ajaxStart"),p.type=p.type.toUpperCase(),p.hasContent=!Wt.test(p.type),r=p.url.replace(Rt,""),p.hasContent?p.data&&p.processData&&0===(p.contentType||"").indexOf("application/x-www-form-urlencoded")&&(p.data=p.data.replace(It,"+")):(f=p.url.slice(r.length),p.data&&(p.processData||"string"==typeof p.data)&&(r+=(At.test(r)?"&":"?")+p.data,delete p.data),!1===p.cache&&(r=r.replace(zt,"$1"),f=(At.test(r)?"&":"?")+"_="+Dt.guid+++f),p.url=r+f),p.ifModified&&(_.lastModified[r]&&T.setRequestHeader("If-Modified-Since",_.lastModified[r]),_.etag[r]&&T.setRequestHeader("If-None-Match",_.etag[r])),(p.data&&p.hasContent&&!1!==p.contentType||t.contentType)&&T.setRequestHeader("Content-Type",p.contentType),T.setRequestHeader("Accept",p.dataTypes[0]&&p.accepts[p.dataTypes[0]]?p.accepts[p.dataTypes[0]]+("*"!==p.dataTypes[0]?", "+Gt+"; q=0.01":""):p.accepts["*"]),p.headers)T.setRequestHeader(d,p.headers[d]);if(p.beforeSend&&(!1===p.beforeSend.call(h,T,p)||u))return T.abort();if(j="abort",v.add(p.complete),T.done(p.success),T.fail(p.error),n=Jt(Xt,p,t,T)){if(T.readyState=1,c&&g.trigger("ajaxSend",[T,p]),u)return T;p.async&&p.timeout>0&&(s=i.setTimeout((function(){T.abort("timeout")}),p.timeout));try{u=!1,n.send(b,C)}catch(e){if(u)throw e;C(-1,e)}}else C(-1,"No Transport");function C(e,t,a,l){var d,f,x,b,w,j=t;u||(u=!0,s&&i.clearTimeout(s),n=void 0,o=l||"",T.readyState=e>0?4:0,d=e>=200&&e<300||304===e,a&&(b=function(e,t,n){for(var i,r,o,a,s=e.contents,l=e.dataTypes;"*"===l[0];)l.shift(),void 0===i&&(i=e.mimeType||t.getResponseHeader("Content-Type"));if(i)for(r in s)if(s[r]&&s[r].test(i)){l.unshift(r);break}if(l[0]in n)o=l[0];else{for(r in n){if(!l[0]||e.converters[r+" "+l[0]]){o=r;break}a||(a=r)}o=o||a}if(o)return o!==l[0]&&l.unshift(o),n[o]}(p,T,a)),!d&&_.inArray("script",p.dataTypes)>-1&&_.inArray("json",p.dataTypes)<0&&(p.converters["text script"]=function(){}),b=function(e,t,n,i){var r,o,a,s,l,u={},c=e.dataTypes.slice();if(c[1])for(a in e.converters)u[a.toLowerCase()]=e.converters[a];for(o=c.shift();o;)if(e.responseFields[o]&&(n[e.responseFields[o]]=t),!l&&i&&e.dataFilter&&(t=e.dataFilter(t,e.dataType)),l=o,o=c.shift())if("*"===o)o=l;else if("*"!==l&&l!==o){if(!(a=u[l+" "+o]||u["* "+o]))for(r in u)if((s=r.split(" "))[1]===o&&(a=u[l+" "+s[0]]||u["* "+s[0]])){!0===a?a=u[r]:!0!==u[r]&&(o=s[0],c.unshift(s[1]));break}if(!0!==a)if(a&&e.throws)t=a(t);else try{t=a(t)}catch(e){return{state:"parsererror",error:a?e:"No conversion from "+l+" to "+o}}}return{state:"success",data:t}}(p,b,T,d),d?(p.ifModified&&((w=T.getResponseHeader("Last-Modified"))&&(_.lastModified[r]=w),(w=T.getResponseHeader("etag"))&&(_.etag[r]=w)),204===e||"HEAD"===p.type?j="nocontent":304===e?j="notmodified":(j=b.state,f=b.data,d=!(x=b.error))):(x=j,!e&&j||(j="error",e<0&&(e=0))),T.status=e,T.statusText=(t||j)+"",d?m.resolveWith(h,[f,j,T]):m.rejectWith(h,[T,j,x]),T.statusCode(y),y=void 0,c&&g.trigger(d?"ajaxSuccess":"ajaxError",[T,p,d?f:x]),v.fireWith(h,[T,j]),c&&(g.trigger("ajaxComplete",[T,p]),--_.active||_.event.trigger("ajaxStop")))}return T},getJSON:function(e,t,n){return _.get(e,t,n,"json")},getScript:function(e,t){return _.get(e,void 0,t,"script")}}),_.each(["get","post"],(function(e,t){_[t]=function(e,n,i,r){return v(n)&&(r=r||i,i=n,n=void 0),_.ajax(_.extend({url:e,type:t,dataType:r,data:n,success:i},_.isPlainObject(e)&&e))}})),_.ajaxPrefilter((function(e){var t;for(t in e.headers)"content-type"===t.toLowerCase()&&(e.contentType=e.headers[t]||"")})),_._evalUrl=function(e,t,n){return _.ajax({url:e,type:"GET",dataType:"script",cache:!0,async:!1,global:!1,converters:{"text script":function(){}},dataFilter:function(e){_.globalEval(e,t,n)}})},_.fn.extend({wrapAll:function(e){var t;return this[0]&&(v(e)&&(e=e.call(this[0])),t=_(e,this[0].ownerDocument).eq(0).clone(!0),this[0].parentNode&&t.insertBefore(this[0]),t.map((function(){for(var e=this;e.firstElementChild;)e=e.firstElementChild;return e})).append(this)),this},wrapInner:function(e){return v(e)?this.each((function(t){_(this).wrapInner(e.call(this,t))})):this.each((function(){var t=_(this),n=t.contents();n.length?n.wrapAll(e):t.append(e)}))},wrap:function(e){var t=v(e);return this.each((function(n){_(this).wrapAll(t?e.call(this,n):e)}))},unwrap:function(e){return this.parent(e).not("body").each((function(){_(this).replaceWith(this.childNodes)})),this}}),_.expr.pseudos.hidden=function(e){return!_.expr.pseudos.visible(e)},_.expr.pseudos.visible=function(e){return!!(e.offsetWidth||e.offsetHeight||e.getClientRects().length)},_.ajaxSettings.xhr=function(){try{return new i.XMLHttpRequest}catch(e){}};var Kt={0:200,1223:204},Zt=_.ajaxSettings.xhr();m.cors=!!Zt&&"withCredentials"in Zt,m.ajax=Zt=!!Zt,_.ajaxTransport((function(e){var t,n;if(m.cors||Zt&&!e.crossDomain)return{send:function(r,o){var a,s=e.xhr();if(s.open(e.type,e.url,e.async,e.username,e.password),e.xhrFields)for(a in e.xhrFields)s[a]=e.xhrFields[a];for(a in e.mimeType&&s.overrideMimeType&&s.overrideMimeType(e.mimeType),e.crossDomain||r["X-Requested-With"]||(r["X-Requested-With"]="XMLHttpRequest"),r)s.setRequestHeader(a,r[a]);t=function(e){return function(){t&&(t=n=s.onload=s.onerror=s.onabort=s.ontimeout=s.onreadystatechange=null,"abort"===e?s.abort():"error"===e?"number"!=typeof s.status?o(0,"error"):o(s.status,s.statusText):o(Kt[s.status]||s.status,s.statusText,"text"!==(s.responseType||"text")||"string"!=typeof s.responseText?{binary:s.response}:{text:s.responseText},s.getAllResponseHeaders()))}},s.onload=t(),n=s.onerror=s.ontimeout=t("error"),void 0!==s.onabort?s.onabort=n:s.onreadystatechange=function(){4===s.readyState&&i.setTimeout((function(){t&&n()}))},t=t("abort");try{s.send(e.hasContent&&e.data||null)}catch(e){if(t)throw e}},abort:function(){t&&t()}}})),_.ajaxPrefilter((function(e){e.crossDomain&&(e.contents.script=!1)})),_.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/\b(?:java|ecma)script\b/},converters:{"text script":function(e){return _.globalEval(e),e}}}),_.ajaxPrefilter("script",(function(e){void 0===e.cache&&(e.cache=!1),e.crossDomain&&(e.type="GET")})),_.ajaxTransport("script",(function(e){var t,n;if(e.crossDomain||e.scriptAttrs)return{send:function(i,r){t=_("<script>").attr(e.scriptAttrs||{}).prop({charset:e.scriptCharset,src:e.url}).on("load error",n=function(e){t.remove(),n=null,e&&r("error"===e.type?404:200,e.type)}),x.head.appendChild(t[0])},abort:function(){n&&n()}}}));var en,tn=[],nn=/(=)\?(?=&|$)|\?\?/;_.ajaxSetup({jsonp:"callback",jsonpCallback:function(){var e=tn.pop()||_.expando+"_"+Dt.guid++;return this[e]=!0,e}}),_.ajaxPrefilter("json jsonp",(function(e,t,n){var r,o,a,s=!1!==e.jsonp&&(nn.test(e.url)?"url":"string"==typeof e.data&&0===(e.contentType||"").indexOf("application/x-www-form-urlencoded")&&nn.test(e.data)&&"data");if(s||"jsonp"===e.dataTypes[0])return r=e.jsonpCallback=v(e.jsonpCallback)?e.jsonpCallback():e.jsonpCallback,s?e[s]=e[s].replace(nn,"$1"+r):!1!==e.jsonp&&(e.url+=(At.test(e.url)?"&":"?")+e.jsonp+"="+r),e.converters["script json"]=function(){return a||_.error(r+" was not called"),a[0]},e.dataTypes[0]="json",o=i[r],i[r]=function(){a=arguments},n.always((function(){void 0===o?_(i).removeProp(r):i[r]=o,e[r]&&(e.jsonpCallback=t.jsonpCallback,tn.push(r)),a&&v(o)&&o(a[0]),a=o=void 0})),"script"})),m.createHTMLDocument=((en=x.implementation.createHTMLDocument("").body).innerHTML="<form></form><form></form>",2===en.childNodes.length),_.parseHTML=function(e,t,n){return"string"!=typeof e?[]:("boolean"==typeof t&&(n=t,t=!1),t||(m.createHTMLDocument?((i=(t=x.implementation.createHTMLDocument("")).createElement("base")).href=x.location.href,t.head.appendChild(i)):t=x),o=!n&&[],(r=R.exec(e))?[t.createElement(r[1])]:(r=Ne([e],t,o),o&&o.length&&_(o).remove(),_.merge([],r.childNodes)));var i,r,o},_.fn.load=function(e,t,n){var i,r,o,a=this,s=e.indexOf(" ");return s>-1&&(i=_t(e.slice(s)),e=e.slice(0,s)),v(t)?(n=t,t=void 0):t&&"object"==typeof t&&(r="POST"),a.length>0&&_.ajax({url:e,type:r||"GET",dataType:"html",data:t}).done((function(e){o=arguments,a.html(i?_("<div>").append(_.parseHTML(e)).find(i):e)})).always(n&&function(e,t){a.each((function(){n.apply(this,o||[e.responseText,t,e])}))}),this},_.expr.pseudos.animated=function(e){return _.grep(_.timers,(function(t){return e===t.elem})).length},_.offset={setOffset:function(e,t,n){var i,r,o,a,s,l,u=_.css(e,"position"),c=_(e),d={};"static"===u&&(e.style.position="relative"),s=c.offset(),o=_.css(e,"top"),l=_.css(e,"left"),("absolute"===u||"fixed"===u)&&(o+l).indexOf("auto")>-1?(a=(i=c.position()).top,r=i.left):(a=parseFloat(o)||0,r=parseFloat(l)||0),v(t)&&(t=t.call(e,n,_.extend({},s))),null!=t.top&&(d.top=t.top-s.top+a),null!=t.left&&(d.left=t.left-s.left+r),"using"in t?t.using.call(e,d):c.css(d)}},_.fn.extend({offset:function(e){if(arguments.length)return void 0===e?this:this.each((function(t){_.offset.setOffset(this,e,t)}));var t,n,i=this[0];return i?i.getClientRects().length?(t=i.getBoundingClientRect(),n=i.ownerDocument.defaultView,{top:t.top+n.pageYOffset,left:t.left+n.pageXOffset}):{top:0,left:0}:void 0},position:function(){if(this[0]){var e,t,n,i=this[0],r={top:0,left:0};if("fixed"===_.css(i,"position"))t=i.getBoundingClientRect();else{for(t=this.offset(),n=i.ownerDocument,e=i.offsetParent||n.documentElement;e&&(e===n.body||e===n.documentElement)&&"static"===_.css(e,"position");)e=e.parentNode;e&&e!==i&&1===e.nodeType&&((r=_(e).offset()).top+=_.css(e,"borderTopWidth",!0),r.left+=_.css(e,"borderLeftWidth",!0))}return{top:t.top-r.top-_.css(i,"marginTop",!0),left:t.left-r.left-_.css(i,"marginLeft",!0)}}},offsetParent:function(){return this.map((function(){for(var e=this.offsetParent;e&&"static"===_.css(e,"position");)e=e.offsetParent;return e||ge}))}}),_.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},(function(e,t){var n="pageYOffset"===t;_.fn[e]=function(i){return ee(this,(function(e,i,r){var o;if(y(e)?o=e:9===e.nodeType&&(o=e.defaultView),void 0===r)return o?o[t]:e[i];o?o.scrollTo(n?o.pageXOffset:r,n?r:o.pageYOffset):e[i]=r}),e,i,arguments.length)}})),_.each(["top","left"],(function(e,t){_.cssHooks[t]=et(m.pixelPosition,(function(e,n){if(n)return n=Ze(e,t),Ve.test(n)?_(e).position()[t]+"px":n}))})),_.each({Height:"height",Width:"width"},(function(e,t){_.each({padding:"inner"+e,content:t,"":"outer"+e},(function(n,i){_.fn[i]=function(r,o){var a=arguments.length&&(n||"boolean"!=typeof r),s=n||(!0===r||!0===o?"margin":"border");return ee(this,(function(t,n,r){var o;return y(t)?0===i.indexOf("outer")?t["inner"+e]:t.document.documentElement["client"+e]:9===t.nodeType?(o=t.documentElement,Math.max(t.body["scroll"+e],o["scroll"+e],t.body["offset"+e],o["offset"+e],o["client"+e])):void 0===r?_.css(t,n,s):_.style(t,n,r,s)}),t,a?r:void 0,a)}}))})),_.each(["ajaxStart","ajaxStop","ajaxComplete","ajaxError","ajaxSuccess","ajaxSend"],(function(e,t){_.fn[t]=function(e){return this.on(t,e)}})),_.fn.extend({bind:function(e,t,n){return this.on(e,null,t,n)},unbind:function(e,t){return this.off(e,null,t)},delegate:function(e,t,n,i){return this.on(t,e,n,i)},undelegate:function(e,t,n){return 1===arguments.length?this.off(e,"**"):this.off(t,e||"**",n)},hover:function(e,t){return this.on("mouseenter",e).on("mouseleave",t||e)}}),_.each("blur focus focusin focusout resize scroll click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup contextmenu".split(" "),(function(e,t){_.fn[t]=function(e,n){return arguments.length>0?this.on(t,null,e,n):this.trigger(t)}}));var rn=/^[\s\uFEFF\xA0]+|([^\s\uFEFF\xA0])[\s\uFEFF\xA0]+$/g;_.proxy=function(e,t){var n,i,r;if("string"==typeof t&&(n=e[t],t=e,e=n),v(e))return i=s.call(arguments,2),r=function(){return e.apply(t||this,i.concat(s.call(arguments)))},r.guid=e.guid=e.guid||_.guid++,r},_.holdReady=function(e){e?_.readyWait++:_.ready(!0)},_.isArray=Array.isArray,_.parseJSON=JSON.parse,_.nodeName=S,_.isFunction=v,_.isWindow=y,_.camelCase=re,_.type=j,_.now=Date.now,_.isNumeric=function(e){var t=_.type(e);return("number"===t||"string"===t)&&!isNaN(e-parseFloat(e))},_.trim=function(e){return null==e?"":(e+"").replace(rn,"$1")},void 0===(n=function(){return _}.apply(t,[]))||(e.exports=n);var on=i.jQuery,an=i.$;return _.noConflict=function(e){return i.$===_&&(i.$=an),e&&i.jQuery===_&&(i.jQuery=on),_},void 0===r&&(i.jQuery=i.$=_),_}))}},n={};function i(e){var r=n[e];if(void 0!==r)return r.exports;var o=n[e]={exports:{}};return t[e].call(o.exports,o,o.exports,i),o.exports}!function(e){"use strict";e.fn.filer=function(t){return this.each((function(n,i){var r=e(i),o=e(),a=e(),s=e(),l=[],u=e.isFunction(t)?t(r,e.fn.filer.defaults):t,c=u&&e.isPlainObject(u)?e.extend(!0,{},e.fn.filer.defaults,u):e.fn.filer.defaults,d={init:function(){r.wrap('<div class="jFiler"></div>'),d._set("props"),r.prop("jFiler").boxEl=o=r.closest(".jFiler"),d._changeInput()},_bindInput:function(){c.changeInput&&a.length>0&&a.on("click",d._clickHandler),r.on({focus:function(){a.addClass("focused")},blur:function(){a.removeClass("focused")},change:d._onChange}),c.dragDrop&&(c.dragDrop.dragContainer.on("drag dragstart dragend dragover dragenter dragleave drop",(function(e){e.preventDefault(),e.stopPropagation()})),c.dragDrop.dragContainer.on("drop",d._dragDrop.drop),c.dragDrop.dragContainer.on("dragover",d._dragDrop.dragEnter),c.dragDrop.dragContainer.on("dragleave",d._dragDrop.dragLeave)),c.uploadFile&&c.clipBoardPaste&&e(window).on("paste",d._clipboardPaste)},_unbindInput:function(t){c.changeInput&&a.length>0&&a.off("click",d._clickHandler),t&&(r.off("change",d._onChange),c.dragDrop&&(c.dragDrop.dragContainer.off("drop",d._dragDrop.drop),c.dragDrop.dragContainer.off("dragover",d._dragDrop.dragEnter),c.dragDrop.dragContainer.off("dragleave",d._dragDrop.dragLeave)),c.uploadFile&&c.clipBoardPaste&&e(window).off("paste",d._clipboardPaste))},_clickHandler:function(){if(!c.uploadFile&&c.addMore&&0!=r.val().length){d._unbindInput(!0);var t=e('<input type="file" />'),n=r.prop("attributes");e.each(n,(function(){"required"!=this.name&&t.attr(this.name,this.value)})),r.after(t),l.push(t),r=t,d._bindInput(),d._set("props")}r.click()},_applyAttrSettings:function(){var e=["name","limit","maxSize","fileMaxSize","extensions","changeInput","showThumbs","appendTo","theme","addMore","excludeName","files","uploadUrl","uploadData","options"];for(var t in e){var n="data-jfiler-"+e[t];if(d._assets.hasAttr(n)){switch(e[t]){case"changeInput":case"showThumbs":case"addMore":c[e[t]]=["true","false"].indexOf(r.attr(n))>-1?"true"==r.attr(n):r.attr(n);break;case"extensions":c[e[t]]=r.attr(n).replace(/ /g,"").split(",");break;case"uploadUrl":c.uploadFile&&(c.uploadFile.url=r.attr(n));break;case"uploadData":c.uploadFile&&(c.uploadFile.data=JSON.parse(r.attr(n)));break;case"files":case"options":c[e[t]]=JSON.parse(r.attr(n));break;default:c[e[t]]=r.attr(n)}r.removeAttr(n)}}},_changeInput:function(){if(d._applyAttrSettings(),null!=c.beforeRender&&"function"==typeof c.beforeRender&&c.beforeRender(o,r),c.theme&&o.addClass("jFiler-theme-"+c.theme),"input"!=r.get(0).tagName.toLowerCase()&&"file"!=r.get(0).type)a=r,(r=e('<input type="file" name="'+c.name+'" />')).css({position:"absolute",left:"-9999px",top:"-9999px","z-index":"-9999"}),o.prepend(r),d._isGn=r;else if(c.changeInput){switch(typeof c.changeInput){case"boolean":a=e('<div class="jFiler-input"><div class="jFiler-input-caption"><span>'+c.captions.feedback+'</span></div><div class="jFiler-input-button">'+c.captions.button+'</div></div>"');break;case"string":case"object":a=e(c.changeInput);break;case"function":a=e(c.changeInput(o,r,c))}r.after(a),r.css({position:"absolute",left:"-9999px",top:"-9999px","z-index":"-9999"})}r.prop("jFiler").newInputEl=a,c.dragDrop&&(c.dragDrop.dragContainer=c.dragDrop.dragContainer?e(c.dragDrop.dragContainer):a),(!c.limit||c.limit&&c.limit>=2)&&(r.attr("multiple","multiple"),"[]"!=r.attr("name").slice(-2)&&r.attr("name",r.attr("name")+"[]")),r.attr("disabled")||c.disabled?(c.disabled=!0,d._unbindInput(!0),o.addClass("jFiler-disabled")):(c.disabled=!1,d._bindInput(),o.removeClass("jFiler-disabled")),c.files&&d._append(!1,{files:c.files}),null!=c.afterRender&&"function"==typeof c.afterRender&&c.afterRender(s,o,a,r)},_clear:function(){d.files=null,r.prop("jFiler").files=null,c.uploadFile||c.addMore||d._reset(),d._set("feedback",d._itFl&&d._itFl.length>0?d._itFl.length+" "+c.captions.feedback2:c.captions.feedback),null!=c.onEmpty&&"function"==typeof c.onEmpty&&c.onEmpty(o,a,r)},_reset:function(t){if(!t){if(!c.uploadFile&&c.addMore){for(var n=0;n<l.length;n++)l[n].remove();l=[],d._unbindInput(!0),r=d._isGn?d._isGn:e(i),d._bindInput()}d._set("input","")}d._itFl=[],d._itFc=null,d._ajFc=0,d._set("props"),r.prop("jFiler").files_list=d._itFl,r.prop("jFiler").current_file=d._itFc,d._itFr=[],o.find("input[name^='jfiler-items-exclude-']:hidden").remove(),s.fadeOut("fast",(function(){e(this).remove()})),r.prop("jFiler").listEl=s=e()},_set:function(e,t){switch(e){case"input":r.val(t);break;case"feedback":a.length>0&&a.find(".jFiler-input-caption span").html(t);break;case"props":r.prop("jFiler")||r.prop("jFiler",{options:c,listEl:s,boxEl:o,newInputEl:a,inputEl:r,files:d.files,files_list:d._itFl,current_file:d._itFc,append:function(e){return d._append(!1,{files:[e]})},enable:function(){c.disabled&&(c.disabled=!1,r.removeAttr("disabled"),o.removeClass("jFiler-disabled"),d._bindInput())},disable:function(){c.disabled||(c.disabled=!0,o.addClass("jFiler-disabled"),d._unbindInput(!0))},remove:function(e){return d._remove(null,{binded:!0,data:{id:e}}),!0},reset:function(){return d._reset(),d._clear(),!0},retry:function(e){return d._retryUpload(e)}})}},_filesCheck:function(){var t=0;if(c.limit&&d.files.length+d._itFl.length>c.limit)return c.dialogs.alert(d._assets.textParse(c.captions.errors.filesLimit)),!1;for(var n=0;n<d.files.length;n++){var i=d.files[n],r=i.name.split(".").pop().toLowerCase(),o={name:i.name,size:i.size,size2:d._assets.bytesToSize(i.size),type:i.type,ext:r};if(null!=c.extensions&&-1==e.inArray(r,c.extensions)&&-1==e.inArray(o.type,c.extensions))return c.dialogs.alert(d._assets.textParse(c.captions.errors.filesType,o)),!1;if(null!=c.maxSize&&d.files[n].size>1048576*c.maxSize||null!=c.fileMaxSize&&d.files[n].size>1048576*c.fileMaxSize)return c.dialogs.alert(d._assets.textParse(c.captions.errors.filesSize,o)),!1;if(4096==i.size&&0==i.type.length)return c.dialogs.alert(d._assets.textParse(c.captions.errors.folderUpload,o)),!1;if(null!=c.onFileCheck&&"function"==typeof c.onFileCheck&&!1===c.onFileCheck(o,c,d._assets.textParse))return!1;if((c.uploadFile||c.addMore)&&!c.allowDuplicates&&(o=d._itFl.filter((function(e,t){if(e.file.name==i.name&&e.file.size==i.size&&e.file.type==i.type&&(!i.lastModified||e.file.lastModified==i.lastModified))return!0}))).length>0){if(1==d.files.length)return!1;i._pendRemove=!0}t+=d.files[n].size}return!(null!=c.maxSize&&t>=Math.round(1048576*c.maxSize)&&(c.dialogs.alert(d._assets.textParse(c.captions.errors.filesSizeAll)),1))},_thumbCreator:{create:function(t){var n=d.files[t],i=d._itFc?d._itFc.id:t,r=n.name,o=n.size,a=n.file,l=n.type?n.type.split("/",1):"".toString().toLowerCase(),u=-1!=r.indexOf(".")?r.split(".").pop().toLowerCase():"",f=c.uploadFile?'<div class="jFiler-jProgressBar">'+c.templates.progressBar+"</div>":"",p={id:i,name:r,size:o,size2:d._assets.bytesToSize(o),url:a,type:l,extension:u,icon:d._assets.getIcon(u,l),icon2:d._thumbCreator.generateIcon({type:l,extension:u}),image:'<div class="jFiler-item-thumb-image fi-loading"></div>',progressBar:f,_appended:n._appended},h="";if(n.opts&&(p=e.extend({},n.opts,p)),(h=e(d._thumbCreator.renderContent(p)).attr("data-jfiler-index",i)).get(0).jfiler_id=i,d._thumbCreator.renderFile(n,h,p),n.forList)return h;d._itFc.html=h,h.hide()[c.templates.itemAppendToEnd?"appendTo":"prependTo"](s.find(c.templates._selectors.list)).show(),n._appended||d._onSelect(t)},renderContent:function(e){return d._assets.textParse(e._appended?c.templates.itemAppend:c.templates.item,e)},renderFile:function(t,n,i){if(0==n.find(".jFiler-item-thumb-image").length)return!1;if(t.file&&"image"==i.type){var r='<img src="'+t.file+'" draggable="false" />',o=n.find(".jFiler-item-thumb-image.fi-loading");return e(r).error((function(){r=d._thumbCreator.generateIcon(i),n.addClass("jFiler-no-thumbnail"),o.removeClass("fi-loading").html(r)})).load((function(){o.removeClass("fi-loading").html(r)})),!0}if(window.File&&window.FileList&&window.FileReader&&"image"==i.type&&i.size<1e7){var a=new FileReader;a.onload=function(e){var t=n.find(".jFiler-item-thumb-image.fi-loading");if(c.templates.canvasImage){var r=document.createElement("canvas"),o=r.getContext("2d"),a=new Image;a.onload=function(){var e=t.height(),n=t.width(),i=a.height/e,s=a.width/n,l=i<s?i:s,u=a.height/l,c=a.width/l,d=Math.ceil(Math.log(a.width/c)/Math.log(2));if(r.height=e,r.width=n,a.width<r.width||a.height<r.height||d<=1){var f=a.width<r.width?r.width/2-a.width/2:a.width>r.width?-(a.width-r.width)/2:0,p=a.height<r.height?r.height/2-a.height/2:0;o.drawImage(a,f,p,a.width,a.height)}else{var h=document.createElement("canvas"),g=h.getContext("2d");h.width=.5*a.width,h.height=.5*a.height,g.fillStyle="#fff",g.fillRect(0,0,h.width,h.height),g.drawImage(a,0,0,h.width,h.height),g.drawImage(h,0,0,.5*h.width,.5*h.height),o.drawImage(h,c>r.width?c-r.width:0,0,.5*h.width,.5*h.height,0,0,c,u)}t.removeClass("fi-loading").html('<img src="'+r.toDataURL("image/png")+'" draggable="false" />')},a.onerror=function(){n.addClass("jFiler-no-thumbnail"),t.removeClass("fi-loading").html(d._thumbCreator.generateIcon(i))},a.src=e.target.result}else t.removeClass("fi-loading").html('<img src="'+e.target.result+'" draggable="false" />')},a.readAsDataURL(t)}else r=d._thumbCreator.generateIcon(i),o=n.find(".jFiler-item-thumb-image.fi-loading"),n.addClass("jFiler-no-thumbnail"),o.removeClass("fi-loading").html(r)},generateIcon:function(t){var n=new Array(3);if(t&&t.type&&t.type[0]&&t.extension)switch(t.type[0]){case"image":n[0]="f-image",n[1]='<i class="icon-jfi-file-image"></i>';break;case"video":n[0]="f-video",n[1]='<i class="icon-jfi-file-video"></i>';break;case"audio":n[0]="f-audio",n[1]='<i class="icon-jfi-file-audio"></i>';break;default:n[0]="f-file f-file-ext-"+t.extension,n[1]=t.extension.length>0?"."+t.extension:"",n[2]=1}else n[0]="f-file",n[1]=t.extension&&t.extension.length>0?"."+t.extension:"",n[2]=1;var i='<span class="jFiler-icon-file '+n[0]+'">'+n[1]+"</span>";if(1==n[2]&&d._assets.text2Color(t.extension)){var r=e(i).appendTo("body");r.css("background-color",d._assets.text2Color(t.extension)),i=r.prop("outerHTML"),r.remove()}return i},_box:function(t){if(null!=c.beforeShow&&"function"==typeof c.beforeShow&&!c.beforeShow(d.files,s,o,a,r))return!1;if(s.length<1){if(c.appendTo)var n=e(c.appendTo);else n=o;n.find(".jFiler-items").remove(),s=e('<div class="jFiler-items jFiler-row"></div>'),r.prop("jFiler").listEl=s,s.append(d._assets.textParse(c.templates.box)).appendTo(n),s.on("click",c.templates._selectors.remove,(function(n){n.preventDefault();var i=[t?t.remove.event:n,t?t.remove.el:e(this).closest(c.templates._selectors.item)],r=function(e){d._remove(i[0],i[1])};c.templates.removeConfirmation?c.dialogs.confirm(c.captions.removeConfirmation,r):r()}))}for(var i=0;i<d.files.length;i++)d.files[i]._appended||(d.files[i]._choosed=!0),d._addToMemory(i),d._thumbCreator.create(i)}},_upload:function(t){var n=d._itFl[t],i=n.html,o=new FormData;if(o.append(r.attr("name"),n.file,!!n.file.name&&n.file.name),null!=c.uploadFile.data&&e.isPlainObject("function"==typeof c.uploadFile.data?c.uploadFile.data(n.file):c.uploadFile.data))for(var a in c.uploadFile.data)o.append(a,c.uploadFile.data[a]);d._ajax.send(i,o,n)},_ajax:{send:function(t,n,i){return i.ajax=e.ajax({url:c.uploadFile.url,data:n,type:c.uploadFile.type,enctype:c.uploadFile.enctype,xhr:function(){var n=e.ajaxSettings.xhr();return n.upload&&n.upload.addEventListener("progress",(function(e){d._ajax.progressHandling(e,t)}),!1),n},complete:function(e,t){i.ajax=!1,d._ajFc++,c.uploadFile.synchron&&i.id+1<d._itFl.length&&d._upload(i.id+1),d._ajFc>=d.files.length&&(d._ajFc=0,r.get(0).value="",null!=c.uploadFile.onComplete&&"function"==typeof c.uploadFile.onComplete&&c.uploadFile.onComplete(s,o,a,r,e,t))},beforeSend:function(e,n){return null==c.uploadFile.beforeSend||"function"!=typeof c.uploadFile.beforeSend||c.uploadFile.beforeSend(t,s,o,a,r,i.id,e,n)},success:function(e,n,l){i.uploaded=!0,null!=c.uploadFile.success&&"function"==typeof c.uploadFile.success&&c.uploadFile.success(e,t,s,o,a,r,i.id,n,l)},error:function(e,n,l){i.uploaded=!1,null!=c.uploadFile.error&&"function"==typeof c.uploadFile.error&&c.uploadFile.error(t,s,o,a,r,i.id,e,n,l)},statusCode:c.uploadFile.statusCode,cache:!1,contentType:!1,processData:!1}),i.ajax},progressHandling:function(e,t){if(e.lengthComputable){var n=Math.round(100*e.loaded/e.total).toString();null!=c.uploadFile.onProgress&&"function"==typeof c.uploadFile.onProgress&&c.uploadFile.onProgress(n,t,s,o,a,r),t.find(".jFiler-jProgressBar").find(c.templates._selectors.progressBar).css("width",n+"%")}}},_dragDrop:{dragEnter:function(e){clearTimeout(d._dragDrop._drt),c.dragDrop.dragContainer.addClass("dragged"),d._set("feedback",c.captions.drop),null!=c.dragDrop.dragEnter&&"function"==typeof c.dragDrop.dragEnter&&c.dragDrop.dragEnter(e,a,r,o)},dragLeave:function(e){clearTimeout(d._dragDrop._drt),d._dragDrop._drt=setTimeout((function(e){if(!d._dragDrop._dragLeaveCheck(e))return d._dragDrop.dragLeave(e),!1;c.dragDrop.dragContainer.removeClass("dragged"),d._set("feedback",c.captions.feedback),null!=c.dragDrop.dragLeave&&"function"==typeof c.dragDrop.dragLeave&&c.dragDrop.dragLeave(e,a,r,o)}),100,e)},drop:function(e){clearTimeout(d._dragDrop._drt),c.dragDrop.dragContainer.removeClass("dragged"),d._set("feedback",c.captions.feedback),e&&e.originalEvent&&e.originalEvent.dataTransfer&&e.originalEvent.dataTransfer.files&&e.originalEvent.dataTransfer.files.length>0&&d._onChange(e,e.originalEvent.dataTransfer.files),null!=c.dragDrop.drop&&"function"==typeof c.dragDrop.drop&&c.dragDrop.drop(e.originalEvent.dataTransfer.files,e,a,r,o)},_dragLeaveCheck:function(t){var n=e(t.currentTarget);return!(!n.is(a)&&a.find(n).length>0)}},_clipboardPaste:function(e,t){if((t||e.originalEvent.clipboardData||e.originalEvent.clipboardData.items)&&(!t||e.originalEvent.dataTransfer||e.originalEvent.dataTransfer.items)&&!d._clPsePre){var n=t?e.originalEvent.dataTransfer.items:e.originalEvent.clipboardData.items,i=function(e,t,n){t=t||"",n=n||512;for(var i=atob(e),r=[],o=0;o<i.length;o+=n){for(var a=i.slice(o,o+n),s=new Array(a.length),l=0;l<a.length;l++)s[l]=a.charCodeAt(l);var u=new Uint8Array(s);r.push(u)}return new Blob(r,{type:t})};if(n)for(var r=0;r<n.length;r++)if(-1!==n[r].type.indexOf("image")||-1!==n[r].type.indexOf("text/uri-list")){if(t)try{window.atob(e.originalEvent.dataTransfer.getData("text/uri-list").toString().split(",")[1])}catch(e){return}var o=t?i(e.originalEvent.dataTransfer.getData("text/uri-list").toString().split(",")[1],"image/png"):n[r].getAsFile();o.name=Math.random().toString(36).substring(5),o.name+=-1!=o.type.indexOf("/")?"."+o.type.split("/")[1].toString().toLowerCase():".png",d._onChange(e,[o]),d._clPsePre=setTimeout((function(){delete d._clPsePre}),1e3)}}},_onSelect:function(t){c.uploadFile&&!e.isEmptyObject(c.uploadFile)&&(!c.uploadFile.synchron||c.uploadFile.synchron&&0==e.grep(d._itFl,(function(e){return e.ajax})).length)&&d._upload(d._itFc.id),d.files[t]._pendRemove&&(d._itFc.html.hide(),d._remove(null,{binded:!0,data:{id:d._itFc.id}})),null!=c.onSelect&&"function"==typeof c.onSelect&&c.onSelect(d.files[t],d._itFc.html,s,o,a,r),t+1>=d.files.length&&null!=c.afterShow&&"function"==typeof c.afterShow&&c.afterShow(s,o,a,r)},_onChange:function(t,n){if(n){if(!n||0==n.length)return d._set("input",""),d._clear(),!1;d.files=n}else{if(!r.get(0).files||void 0===r.get(0).files||0==r.get(0).files.length)return c.uploadFile||c.addMore||(d._set("input",""),d._clear()),!1;d.files=r.get(0).files}if(c.uploadFile||c.addMore||d._reset(!0),r.prop("jFiler").files=d.files,!d._filesCheck()||null!=c.beforeSelect&&"function"==typeof c.beforeSelect&&!c.beforeSelect(d.files,s,o,a,r))return d._set("input",""),d._clear(),c.addMore&&l.length>0&&(d._unbindInput(!0),l[l.length-1].remove(),l.splice(l.length-1,1),r=l.length>0?l[l.length-1]:e(i),d._bindInput()),!1;if(d._set("feedback",d.files.length+d._itFl.length+" "+c.captions.feedback2),c.showThumbs)d._thumbCreator._box();else for(var u=0;u<d.files.length;u++)d.files[u]._choosed=!0,d._addToMemory(u),d._onSelect(u)},_append:function(e,t){var n=!!t&&t.files;if(n&&!(n.length<=0)&&(d.files=n,r.prop("jFiler").files=d.files,c.showThumbs)){for(var i=0;i<d.files.length;i++)d.files[i]._appended=!0;d._thumbCreator._box()}},_getList:function(e,t){var n=!!t&&t.files;if(n&&!(n.length<=0)&&(d.files=n,r.prop("jFiler").files=d.files,c.showThumbs)){for(var i=[],l=0;l<d.files.length;l++)d.files[l].forList=!0,i.push(d._thumbCreator.create(l));t.callback&&t.callback(i,s,o,a,r)}},_retryUpload:function(t,n){var i=parseInt("object"==typeof n?n.attr("data-jfiler-index"):n),o=d._itFl.filter((function(e,t){return e.id==i}));return o.length>0&&(!c.uploadFile||e.isEmptyObject(c.uploadFile)||o[0].uploaded?void 0:(d._itFc=o[0],r.prop("jFiler").current_file=d._itFc,d._upload(i),!0))},_remove:function(t,i){if(i.binded){if(void 0!==i.data.id&&0==(i=s.find(c.templates._selectors.item+"[data-jfiler-index='"+i.data.id+"']")).length)return!1;i.data.el&&(i=i.data.el)}var l=function(t,i){var a=d._itFl[i],s=[];if(a.file._choosed||a.file._appended||a.uploaded){d._itFr.push(a);for(var l=d._itFl.filter((function(e){return e.file.name==a.file.name})),u=0;u<d._itFr.length;u++)c.addMore&&d._itFr[u]==a&&l.length>0&&(d._itFr[u].remove_name=l.indexOf(a)+"://"+d._itFr[u].file.name),s.push(d._itFr[u].remove_name?d._itFr[u].remove_name:d._itFr[u].file.name)}!function(t){var i=o.find("input[name^='jfiler-items-exclude-']:hidden").first();0==i.length&&(i=e('<input type="hidden" name="jfiler-items-exclude-'+(c.excludeName?c.excludeName:("[]"!=r.attr("name").slice(-2)?r.attr("name"):r.attr("name").substring(0,r.attr("name").length-2))+"-"+n)+'">')).appendTo(o),t&&e.isArray(t)&&(t=JSON.stringify(t),i.val(t))}(s),d._itFl.splice(i,1),d._itFl.length<1?(d._reset(),d._clear()):d._set("feedback",d._itFl.length+" "+c.captions.feedback2),t.fadeOut("fast",(function(){e(this).remove()}))},u=i.get(0).jfiler_id||i.attr("data-jfiler-index"),f=null;for(var p in d._itFl)"length"!==p&&d._itFl.hasOwnProperty(p)&&d._itFl[p].id==u&&(f=p);return!!d._itFl.hasOwnProperty(f)&&(d._itFl[f].ajax?(d._itFl[f].ajax.abort(),void l(i,f)):void(null!=c.onRemove&&"function"==typeof c.onRemove&&!1===c.onRemove(i,d._itFl[f].file,f,s,o,a,r)||l(i,f)))},_addToMemory:function(t){d._itFl.push({id:d._itFl.length,file:d.files[t],html:e(),ajax:!1,uploaded:!1}),(c.addMore||d.files[t]._appended)&&(d._itFl[d._itFl.length-1].input=r),d._itFc=d._itFl[d._itFl.length-1],r.prop("jFiler").files_list=d._itFl,r.prop("jFiler").current_file=d._itFc},_assets:{bytesToSize:function(e){if(0==e)return"0 Byte";var t=Math.floor(Math.log(e)/Math.log(1e3));return(e/Math.pow(1e3,t)).toPrecision(3)+" "+["Bytes","KB","MB","GB","TB","PB","EB","ZB","YB"][t]},hasAttr:function(e,t){var n=(t=t||r).attr(e);return!(!n||void 0===n)},getIcon:function(t,n){return e.inArray(n,["audio","image","text","video"])>-1?'<i class="icon-jfi-file-'+n+" jfi-file-ext-"+t+'"></i>':'<i class="icon-jfi-file-o jfi-file-type-'+n+" jfi-file-ext-"+t+'"></i>'},textParse:function(t,n){switch(n=e.extend({},{limit:c.limit,maxSize:c.maxSize,fileMaxSize:c.fileMaxSize,extensions:c.extensions?c.extensions.join(","):null},n&&e.isPlainObject(n)?n:{},c.options),typeof t){case"string":return t.replace(/\{\{fi-(.*?)\}\}/g,(function(e,t){return(t=t.replace(/ /g,"")).match(/(.*?)\|limitTo\:(\d+)/)?t.replace(/(.*?)\|limitTo\:(\d+)/,(function(e,t,i){var r=(t=n[t]?n[t]:"").substring(0,i);return r=t.length>r.length?r.substring(0,r.length-3)+"...":r})):n[t]?n[t]:""}));case"function":return t(n);default:return t}},text2Color:function(e){if(!e||0==e.length)return!1;for(var t=0,n=0;t<e.length;n=e.charCodeAt(t++)+((n<<5)-n));t=0;for(var i="#";t<3;i+=("00"+(n>>2*t++&255).toString(16)).slice(-2));return i}},files:null,_itFl:[],_itFc:null,_itFr:[],_itPl:[],_ajFc:0};return r.on("filer.append",(function(e,t){d._append(e,t)})).on("filer.remove",(function(e,t){t.binded=!0,d._remove(e,t)})).on("filer.reset",(function(e){return d._reset(),d._clear(),!0})).on("filer.generateList",(function(e,t){return d._getList(e,t)})).on("filer.retry",(function(e,t){return d._retryUpload(e,t)})),d.init(),this}))},e.fn.filer.defaults={limit:null,maxSize:null,fileMaxSize:null,extensions:null,changeInput:!0,showThumbs:!1,appendTo:null,theme:"default",templates:{box:'<ul class="jFiler-items-list jFiler-items-default"></ul>',item:'<li class="jFiler-item"><div class="jFiler-item-container"><div class="jFiler-item-inner"><div class="jFiler-item-icon pull-left">{{fi-icon}}</div><div class="jFiler-item-info pull-left"><div class="jFiler-item-title" title="{{fi-name}}">{{fi-name | limitTo:30}}</div><div class="jFiler-item-others"><span>size: {{fi-size2}}</span><span>type: {{fi-extension}}</span><span class="jFiler-item-status">{{fi-progressBar}}</span></div><div class="jFiler-item-assets"><ul class="list-inline"><li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li></ul></div></div></div></div></li>',itemAppend:'<li class="jFiler-item"><div class="jFiler-item-container"><div class="jFiler-item-inner"><div class="jFiler-item-icon pull-left">{{fi-icon}}</div><div class="jFiler-item-info pull-left"><div class="jFiler-item-title">{{fi-name | limitTo:35}}</div><div class="jFiler-item-others"><span>size: {{fi-size2}}</span><span>type: {{fi-extension}}</span><span class="jFiler-item-status"></span></div><div class="jFiler-item-assets"><ul class="list-inline"><li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li></ul></div></div></div></div></li>',progressBar:'<div class="bar"></div>',itemAppendToEnd:!1,removeConfirmation:!0,canvasImage:!0,_selectors:{list:".jFiler-items-list",item:".jFiler-item",progressBar:".bar",remove:".jFiler-item-trash-action"}},files:null,uploadFile:null,dragDrop:null,addMore:!1,allowDuplicates:!1,clipBoardPaste:!0,excludeName:null,beforeRender:null,afterRender:null,beforeShow:null,beforeSelect:null,onSelect:null,onFileCheck:null,afterShow:null,onRemove:null,onEmpty:null,options:null,dialogs:{alert:function(e){return alert(e)},confirm:function(e,t){confirm(e)&&t()}},captions:{button:"Choose Files",feedback:"Choose files To Upload",feedback2:"files were chosen",drop:"Drop file here to Upload",removeConfirmation:"Are you sure you want to remove this file?",errors:{filesLimit:"Only {{fi-limit}} files are allowed to be uploaded.",filesType:"Only Images are allowed to be uploaded.",filesSize:"{{fi-name}} is too large! Please upload file up to {{fi-fileMaxSize}} MB.",filesSizeAll:"Files you've choosed are too large! Please upload files up to {{fi-maxSize}} MB.",folderUpload:"You are not allowed to upload folders."}}}}(i(4692)),(e=i(4692))(document).ready((function(){var t,n=("draggable"in(t=document.createElement("div"))||"ondragstart"in t&&"ondrop"in t)&&"FormData"in window&&"FileReader"in window,i=e(".integrated-dropzone");e.each(i,(function(){var t=e(this);n&&t.addClass("has-advanced-upload").on("drag dragstart dragend dragover dragenter dragleave drop",(function(e){e.preventDefault(),e.stopPropagation()})).on("dragover dragenter",(function(){t.addClass("is-dragover")})).on("dragleave dragend drop",(function(){t.removeClass("is-dragover")})).on("drop",(function(n){e("input[type='file']",t).prop("files",n.originalEvent.dataTransfer.files),e("input[type='file']",t).trigger("change")}));var i={showThumbs:!0,limit:1,appendTo:".integrated-dropzone",afterRender:function(t,n){this.files&&this.files.length&&e(n).hide()},afterShow:function(n,i,r){e(i).hide(),t.find(".remove-file").val(0)},onSelect:function(t){""==e("#integrated_content_title").val()&&e("#integrated_content_title").val(t.name.split(".").slice(0,-1).join("."))},onEmpty:function(n){e(n).show(),t.find(".remove-file").val(1)},theme:"dragdropbox",templates:{box:'<ul class="jFiler-items-list jFiler-items-grid"></ul>',item:'<li class="jFiler-item">\t\t\t\t\t\t<div class="jFiler-item-container">\t\t\t\t\t\t\t<div class="jFiler-item-inner">\t\t\t\t\t\t\t\t<div class="jFiler-item-thumb">\t\t\t\t\t\t\t\t\t<div class="jFiler-item-status"></div>\t\t\t\t\t\t\t\t\t<div class="jFiler-item-thumb-overlay">\t\t\t\t\t\t\t\t\t\t<div class="jFiler-item-info">\t\t\t\t\t\t\t\t\t\t\t<div style="display:table-cell;vertical-align: middle;">\t\t\t\t\t\t\t\t\t\t\t\t<span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name}}</b></span>\t\t\t\t\t\t\t\t\t\t\t\t<span class="jFiler-item-others">{{fi-size2}}</span>\t\t\t\t\t\t\t\t\t\t\t</div>\t\t\t\t\t\t\t\t\t\t</div>\t\t\t\t\t\t\t\t\t</div>\t\t\t\t\t\t\t\t\t{{fi-image}}\t\t\t\t\t\t\t\t</div>\t\t\t\t\t\t\t\t<div class="jFiler-item-assets jFiler-row">\t\t\t\t\t\t\t\t\t<ul class="list-inline pull-right">\t\t\t\t\t\t\t\t\t\t<li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\t\t\t\t\t\t\t\t\t</ul>\t\t\t\t\t\t\t\t</div>\t\t\t\t\t\t\t</div>\t\t\t\t\t\t</div>\t\t\t\t\t</li>',itemAppend:'<li class="jFiler-item">                                <div class="jFiler-item-container">                                    <div class="jFiler-item-inner">                                        <div class="jFiler-item-thumb">                                            <div class="jFiler-item-status"></div>                                            <div class="jFiler-item-thumb-overlay">        \t\t\t\t\t\t\t\t\t\t<div class="jFiler-item-info">        \t\t\t\t\t\t\t\t\t\t\t<div style="display:table-cell;vertical-align: middle;">        \t\t\t\t\t\t\t\t\t\t\t\t<span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name}}</b></span>        \t\t\t\t\t\t\t\t\t\t\t</div>        \t\t\t\t\t\t\t\t\t\t</div>        \t\t\t\t\t\t\t\t\t</div>                                            {{fi-image}}                                        </div>                                        <div class="jFiler-item-assets jFiler-row">                                            <ul class="list-inline pull-right">                                                <li><a href="{{fi-name}}" title="Download" target="_blank"><span class="iconoir-download"></span></a></li>                                                <li><a class="jFiler-item-trash-action" title="Delete"><i class="iconoir-trash"></i></a></li>                                            </ul>                                        </div>                                    </div>                                </div>                            </li>',canvasImage:!0,removeConfirmation:!0,_selectors:{list:".jFiler-items-list",item:".jFiler-item",progressBar:".bar",remove:".jFiler-item-trash-action"}}};e.extend(i,t.data("options")),e('input[type="file"]',t).filer(i)}))}))})();
>>>>>>> release/0.80
