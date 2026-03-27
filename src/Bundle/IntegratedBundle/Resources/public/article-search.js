/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/@hotwired/stimulus/dist/stimulus.js"
/*!**********************************************************!*\
  !*** ./node_modules/@hotwired/stimulus/dist/stimulus.js ***!
  \**********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Application: () => (/* binding */ Application),
/* harmony export */   AttributeObserver: () => (/* binding */ AttributeObserver),
/* harmony export */   Context: () => (/* binding */ Context),
/* harmony export */   Controller: () => (/* binding */ Controller),
/* harmony export */   ElementObserver: () => (/* binding */ ElementObserver),
/* harmony export */   IndexedMultimap: () => (/* binding */ IndexedMultimap),
/* harmony export */   Multimap: () => (/* binding */ Multimap),
/* harmony export */   SelectorObserver: () => (/* binding */ SelectorObserver),
/* harmony export */   StringMapObserver: () => (/* binding */ StringMapObserver),
/* harmony export */   TokenListObserver: () => (/* binding */ TokenListObserver),
/* harmony export */   ValueListObserver: () => (/* binding */ ValueListObserver),
/* harmony export */   add: () => (/* binding */ add),
/* harmony export */   defaultSchema: () => (/* binding */ defaultSchema),
/* harmony export */   del: () => (/* binding */ del),
/* harmony export */   fetch: () => (/* binding */ fetch),
/* harmony export */   prune: () => (/* binding */ prune)
/* harmony export */ });
/*
Stimulus 3.2.1
Copyright © 2023 Basecamp, LLC
 */
class EventListener {
    constructor(eventTarget, eventName, eventOptions) {
        this.eventTarget = eventTarget;
        this.eventName = eventName;
        this.eventOptions = eventOptions;
        this.unorderedBindings = new Set();
    }
    connect() {
        this.eventTarget.addEventListener(this.eventName, this, this.eventOptions);
    }
    disconnect() {
        this.eventTarget.removeEventListener(this.eventName, this, this.eventOptions);
    }
    bindingConnected(binding) {
        this.unorderedBindings.add(binding);
    }
    bindingDisconnected(binding) {
        this.unorderedBindings.delete(binding);
    }
    handleEvent(event) {
        const extendedEvent = extendEvent(event);
        for (const binding of this.bindings) {
            if (extendedEvent.immediatePropagationStopped) {
                break;
            }
            else {
                binding.handleEvent(extendedEvent);
            }
        }
    }
    hasBindings() {
        return this.unorderedBindings.size > 0;
    }
    get bindings() {
        return Array.from(this.unorderedBindings).sort((left, right) => {
            const leftIndex = left.index, rightIndex = right.index;
            return leftIndex < rightIndex ? -1 : leftIndex > rightIndex ? 1 : 0;
        });
    }
}
function extendEvent(event) {
    if ("immediatePropagationStopped" in event) {
        return event;
    }
    else {
        const { stopImmediatePropagation } = event;
        return Object.assign(event, {
            immediatePropagationStopped: false,
            stopImmediatePropagation() {
                this.immediatePropagationStopped = true;
                stopImmediatePropagation.call(this);
            },
        });
    }
}

class Dispatcher {
    constructor(application) {
        this.application = application;
        this.eventListenerMaps = new Map();
        this.started = false;
    }
    start() {
        if (!this.started) {
            this.started = true;
            this.eventListeners.forEach((eventListener) => eventListener.connect());
        }
    }
    stop() {
        if (this.started) {
            this.started = false;
            this.eventListeners.forEach((eventListener) => eventListener.disconnect());
        }
    }
    get eventListeners() {
        return Array.from(this.eventListenerMaps.values()).reduce((listeners, map) => listeners.concat(Array.from(map.values())), []);
    }
    bindingConnected(binding) {
        this.fetchEventListenerForBinding(binding).bindingConnected(binding);
    }
    bindingDisconnected(binding, clearEventListeners = false) {
        this.fetchEventListenerForBinding(binding).bindingDisconnected(binding);
        if (clearEventListeners)
            this.clearEventListenersForBinding(binding);
    }
    handleError(error, message, detail = {}) {
        this.application.handleError(error, `Error ${message}`, detail);
    }
    clearEventListenersForBinding(binding) {
        const eventListener = this.fetchEventListenerForBinding(binding);
        if (!eventListener.hasBindings()) {
            eventListener.disconnect();
            this.removeMappedEventListenerFor(binding);
        }
    }
    removeMappedEventListenerFor(binding) {
        const { eventTarget, eventName, eventOptions } = binding;
        const eventListenerMap = this.fetchEventListenerMapForEventTarget(eventTarget);
        const cacheKey = this.cacheKey(eventName, eventOptions);
        eventListenerMap.delete(cacheKey);
        if (eventListenerMap.size == 0)
            this.eventListenerMaps.delete(eventTarget);
    }
    fetchEventListenerForBinding(binding) {
        const { eventTarget, eventName, eventOptions } = binding;
        return this.fetchEventListener(eventTarget, eventName, eventOptions);
    }
    fetchEventListener(eventTarget, eventName, eventOptions) {
        const eventListenerMap = this.fetchEventListenerMapForEventTarget(eventTarget);
        const cacheKey = this.cacheKey(eventName, eventOptions);
        let eventListener = eventListenerMap.get(cacheKey);
        if (!eventListener) {
            eventListener = this.createEventListener(eventTarget, eventName, eventOptions);
            eventListenerMap.set(cacheKey, eventListener);
        }
        return eventListener;
    }
    createEventListener(eventTarget, eventName, eventOptions) {
        const eventListener = new EventListener(eventTarget, eventName, eventOptions);
        if (this.started) {
            eventListener.connect();
        }
        return eventListener;
    }
    fetchEventListenerMapForEventTarget(eventTarget) {
        let eventListenerMap = this.eventListenerMaps.get(eventTarget);
        if (!eventListenerMap) {
            eventListenerMap = new Map();
            this.eventListenerMaps.set(eventTarget, eventListenerMap);
        }
        return eventListenerMap;
    }
    cacheKey(eventName, eventOptions) {
        const parts = [eventName];
        Object.keys(eventOptions)
            .sort()
            .forEach((key) => {
            parts.push(`${eventOptions[key] ? "" : "!"}${key}`);
        });
        return parts.join(":");
    }
}

const defaultActionDescriptorFilters = {
    stop({ event, value }) {
        if (value)
            event.stopPropagation();
        return true;
    },
    prevent({ event, value }) {
        if (value)
            event.preventDefault();
        return true;
    },
    self({ event, value, element }) {
        if (value) {
            return element === event.target;
        }
        else {
            return true;
        }
    },
};
const descriptorPattern = /^(?:(?:([^.]+?)\+)?(.+?)(?:\.(.+?))?(?:@(window|document))?->)?(.+?)(?:#([^:]+?))(?::(.+))?$/;
function parseActionDescriptorString(descriptorString) {
    const source = descriptorString.trim();
    const matches = source.match(descriptorPattern) || [];
    let eventName = matches[2];
    let keyFilter = matches[3];
    if (keyFilter && !["keydown", "keyup", "keypress"].includes(eventName)) {
        eventName += `.${keyFilter}`;
        keyFilter = "";
    }
    return {
        eventTarget: parseEventTarget(matches[4]),
        eventName,
        eventOptions: matches[7] ? parseEventOptions(matches[7]) : {},
        identifier: matches[5],
        methodName: matches[6],
        keyFilter: matches[1] || keyFilter,
    };
}
function parseEventTarget(eventTargetName) {
    if (eventTargetName == "window") {
        return window;
    }
    else if (eventTargetName == "document") {
        return document;
    }
}
function parseEventOptions(eventOptions) {
    return eventOptions
        .split(":")
        .reduce((options, token) => Object.assign(options, { [token.replace(/^!/, "")]: !/^!/.test(token) }), {});
}
function stringifyEventTarget(eventTarget) {
    if (eventTarget == window) {
        return "window";
    }
    else if (eventTarget == document) {
        return "document";
    }
}

function camelize(value) {
    return value.replace(/(?:[_-])([a-z0-9])/g, (_, char) => char.toUpperCase());
}
function namespaceCamelize(value) {
    return camelize(value.replace(/--/g, "-").replace(/__/g, "_"));
}
function capitalize(value) {
    return value.charAt(0).toUpperCase() + value.slice(1);
}
function dasherize(value) {
    return value.replace(/([A-Z])/g, (_, char) => `-${char.toLowerCase()}`);
}
function tokenize(value) {
    return value.match(/[^\s]+/g) || [];
}

function isSomething(object) {
    return object !== null && object !== undefined;
}
function hasProperty(object, property) {
    return Object.prototype.hasOwnProperty.call(object, property);
}

const allModifiers = ["meta", "ctrl", "alt", "shift"];
class Action {
    constructor(element, index, descriptor, schema) {
        this.element = element;
        this.index = index;
        this.eventTarget = descriptor.eventTarget || element;
        this.eventName = descriptor.eventName || getDefaultEventNameForElement(element) || error("missing event name");
        this.eventOptions = descriptor.eventOptions || {};
        this.identifier = descriptor.identifier || error("missing identifier");
        this.methodName = descriptor.methodName || error("missing method name");
        this.keyFilter = descriptor.keyFilter || "";
        this.schema = schema;
    }
    static forToken(token, schema) {
        return new this(token.element, token.index, parseActionDescriptorString(token.content), schema);
    }
    toString() {
        const eventFilter = this.keyFilter ? `.${this.keyFilter}` : "";
        const eventTarget = this.eventTargetName ? `@${this.eventTargetName}` : "";
        return `${this.eventName}${eventFilter}${eventTarget}->${this.identifier}#${this.methodName}`;
    }
    shouldIgnoreKeyboardEvent(event) {
        if (!this.keyFilter) {
            return false;
        }
        const filters = this.keyFilter.split("+");
        if (this.keyFilterDissatisfied(event, filters)) {
            return true;
        }
        const standardFilter = filters.filter((key) => !allModifiers.includes(key))[0];
        if (!standardFilter) {
            return false;
        }
        if (!hasProperty(this.keyMappings, standardFilter)) {
            error(`contains unknown key filter: ${this.keyFilter}`);
        }
        return this.keyMappings[standardFilter].toLowerCase() !== event.key.toLowerCase();
    }
    shouldIgnoreMouseEvent(event) {
        if (!this.keyFilter) {
            return false;
        }
        const filters = [this.keyFilter];
        if (this.keyFilterDissatisfied(event, filters)) {
            return true;
        }
        return false;
    }
    get params() {
        const params = {};
        const pattern = new RegExp(`^data-${this.identifier}-(.+)-param$`, "i");
        for (const { name, value } of Array.from(this.element.attributes)) {
            const match = name.match(pattern);
            const key = match && match[1];
            if (key) {
                params[camelize(key)] = typecast(value);
            }
        }
        return params;
    }
    get eventTargetName() {
        return stringifyEventTarget(this.eventTarget);
    }
    get keyMappings() {
        return this.schema.keyMappings;
    }
    keyFilterDissatisfied(event, filters) {
        const [meta, ctrl, alt, shift] = allModifiers.map((modifier) => filters.includes(modifier));
        return event.metaKey !== meta || event.ctrlKey !== ctrl || event.altKey !== alt || event.shiftKey !== shift;
    }
}
const defaultEventNames = {
    a: () => "click",
    button: () => "click",
    form: () => "submit",
    details: () => "toggle",
    input: (e) => (e.getAttribute("type") == "submit" ? "click" : "input"),
    select: () => "change",
    textarea: () => "input",
};
function getDefaultEventNameForElement(element) {
    const tagName = element.tagName.toLowerCase();
    if (tagName in defaultEventNames) {
        return defaultEventNames[tagName](element);
    }
}
function error(message) {
    throw new Error(message);
}
function typecast(value) {
    try {
        return JSON.parse(value);
    }
    catch (o_O) {
        return value;
    }
}

class Binding {
    constructor(context, action) {
        this.context = context;
        this.action = action;
    }
    get index() {
        return this.action.index;
    }
    get eventTarget() {
        return this.action.eventTarget;
    }
    get eventOptions() {
        return this.action.eventOptions;
    }
    get identifier() {
        return this.context.identifier;
    }
    handleEvent(event) {
        const actionEvent = this.prepareActionEvent(event);
        if (this.willBeInvokedByEvent(event) && this.applyEventModifiers(actionEvent)) {
            this.invokeWithEvent(actionEvent);
        }
    }
    get eventName() {
        return this.action.eventName;
    }
    get method() {
        const method = this.controller[this.methodName];
        if (typeof method == "function") {
            return method;
        }
        throw new Error(`Action "${this.action}" references undefined method "${this.methodName}"`);
    }
    applyEventModifiers(event) {
        const { element } = this.action;
        const { actionDescriptorFilters } = this.context.application;
        const { controller } = this.context;
        let passes = true;
        for (const [name, value] of Object.entries(this.eventOptions)) {
            if (name in actionDescriptorFilters) {
                const filter = actionDescriptorFilters[name];
                passes = passes && filter({ name, value, event, element, controller });
            }
            else {
                continue;
            }
        }
        return passes;
    }
    prepareActionEvent(event) {
        return Object.assign(event, { params: this.action.params });
    }
    invokeWithEvent(event) {
        const { target, currentTarget } = event;
        try {
            this.method.call(this.controller, event);
            this.context.logDebugActivity(this.methodName, { event, target, currentTarget, action: this.methodName });
        }
        catch (error) {
            const { identifier, controller, element, index } = this;
            const detail = { identifier, controller, element, index, event };
            this.context.handleError(error, `invoking action "${this.action}"`, detail);
        }
    }
    willBeInvokedByEvent(event) {
        const eventTarget = event.target;
        if (event instanceof KeyboardEvent && this.action.shouldIgnoreKeyboardEvent(event)) {
            return false;
        }
        if (event instanceof MouseEvent && this.action.shouldIgnoreMouseEvent(event)) {
            return false;
        }
        if (this.element === eventTarget) {
            return true;
        }
        else if (eventTarget instanceof Element && this.element.contains(eventTarget)) {
            return this.scope.containsElement(eventTarget);
        }
        else {
            return this.scope.containsElement(this.action.element);
        }
    }
    get controller() {
        return this.context.controller;
    }
    get methodName() {
        return this.action.methodName;
    }
    get element() {
        return this.scope.element;
    }
    get scope() {
        return this.context.scope;
    }
}

class ElementObserver {
    constructor(element, delegate) {
        this.mutationObserverInit = { attributes: true, childList: true, subtree: true };
        this.element = element;
        this.started = false;
        this.delegate = delegate;
        this.elements = new Set();
        this.mutationObserver = new MutationObserver((mutations) => this.processMutations(mutations));
    }
    start() {
        if (!this.started) {
            this.started = true;
            this.mutationObserver.observe(this.element, this.mutationObserverInit);
            this.refresh();
        }
    }
    pause(callback) {
        if (this.started) {
            this.mutationObserver.disconnect();
            this.started = false;
        }
        callback();
        if (!this.started) {
            this.mutationObserver.observe(this.element, this.mutationObserverInit);
            this.started = true;
        }
    }
    stop() {
        if (this.started) {
            this.mutationObserver.takeRecords();
            this.mutationObserver.disconnect();
            this.started = false;
        }
    }
    refresh() {
        if (this.started) {
            const matches = new Set(this.matchElementsInTree());
            for (const element of Array.from(this.elements)) {
                if (!matches.has(element)) {
                    this.removeElement(element);
                }
            }
            for (const element of Array.from(matches)) {
                this.addElement(element);
            }
        }
    }
    processMutations(mutations) {
        if (this.started) {
            for (const mutation of mutations) {
                this.processMutation(mutation);
            }
        }
    }
    processMutation(mutation) {
        if (mutation.type == "attributes") {
            this.processAttributeChange(mutation.target, mutation.attributeName);
        }
        else if (mutation.type == "childList") {
            this.processRemovedNodes(mutation.removedNodes);
            this.processAddedNodes(mutation.addedNodes);
        }
    }
    processAttributeChange(element, attributeName) {
        if (this.elements.has(element)) {
            if (this.delegate.elementAttributeChanged && this.matchElement(element)) {
                this.delegate.elementAttributeChanged(element, attributeName);
            }
            else {
                this.removeElement(element);
            }
        }
        else if (this.matchElement(element)) {
            this.addElement(element);
        }
    }
    processRemovedNodes(nodes) {
        for (const node of Array.from(nodes)) {
            const element = this.elementFromNode(node);
            if (element) {
                this.processTree(element, this.removeElement);
            }
        }
    }
    processAddedNodes(nodes) {
        for (const node of Array.from(nodes)) {
            const element = this.elementFromNode(node);
            if (element && this.elementIsActive(element)) {
                this.processTree(element, this.addElement);
            }
        }
    }
    matchElement(element) {
        return this.delegate.matchElement(element);
    }
    matchElementsInTree(tree = this.element) {
        return this.delegate.matchElementsInTree(tree);
    }
    processTree(tree, processor) {
        for (const element of this.matchElementsInTree(tree)) {
            processor.call(this, element);
        }
    }
    elementFromNode(node) {
        if (node.nodeType == Node.ELEMENT_NODE) {
            return node;
        }
    }
    elementIsActive(element) {
        if (element.isConnected != this.element.isConnected) {
            return false;
        }
        else {
            return this.element.contains(element);
        }
    }
    addElement(element) {
        if (!this.elements.has(element)) {
            if (this.elementIsActive(element)) {
                this.elements.add(element);
                if (this.delegate.elementMatched) {
                    this.delegate.elementMatched(element);
                }
            }
        }
    }
    removeElement(element) {
        if (this.elements.has(element)) {
            this.elements.delete(element);
            if (this.delegate.elementUnmatched) {
                this.delegate.elementUnmatched(element);
            }
        }
    }
}

class AttributeObserver {
    constructor(element, attributeName, delegate) {
        this.attributeName = attributeName;
        this.delegate = delegate;
        this.elementObserver = new ElementObserver(element, this);
    }
    get element() {
        return this.elementObserver.element;
    }
    get selector() {
        return `[${this.attributeName}]`;
    }
    start() {
        this.elementObserver.start();
    }
    pause(callback) {
        this.elementObserver.pause(callback);
    }
    stop() {
        this.elementObserver.stop();
    }
    refresh() {
        this.elementObserver.refresh();
    }
    get started() {
        return this.elementObserver.started;
    }
    matchElement(element) {
        return element.hasAttribute(this.attributeName);
    }
    matchElementsInTree(tree) {
        const match = this.matchElement(tree) ? [tree] : [];
        const matches = Array.from(tree.querySelectorAll(this.selector));
        return match.concat(matches);
    }
    elementMatched(element) {
        if (this.delegate.elementMatchedAttribute) {
            this.delegate.elementMatchedAttribute(element, this.attributeName);
        }
    }
    elementUnmatched(element) {
        if (this.delegate.elementUnmatchedAttribute) {
            this.delegate.elementUnmatchedAttribute(element, this.attributeName);
        }
    }
    elementAttributeChanged(element, attributeName) {
        if (this.delegate.elementAttributeValueChanged && this.attributeName == attributeName) {
            this.delegate.elementAttributeValueChanged(element, attributeName);
        }
    }
}

function add(map, key, value) {
    fetch(map, key).add(value);
}
function del(map, key, value) {
    fetch(map, key).delete(value);
    prune(map, key);
}
function fetch(map, key) {
    let values = map.get(key);
    if (!values) {
        values = new Set();
        map.set(key, values);
    }
    return values;
}
function prune(map, key) {
    const values = map.get(key);
    if (values != null && values.size == 0) {
        map.delete(key);
    }
}

class Multimap {
    constructor() {
        this.valuesByKey = new Map();
    }
    get keys() {
        return Array.from(this.valuesByKey.keys());
    }
    get values() {
        const sets = Array.from(this.valuesByKey.values());
        return sets.reduce((values, set) => values.concat(Array.from(set)), []);
    }
    get size() {
        const sets = Array.from(this.valuesByKey.values());
        return sets.reduce((size, set) => size + set.size, 0);
    }
    add(key, value) {
        add(this.valuesByKey, key, value);
    }
    delete(key, value) {
        del(this.valuesByKey, key, value);
    }
    has(key, value) {
        const values = this.valuesByKey.get(key);
        return values != null && values.has(value);
    }
    hasKey(key) {
        return this.valuesByKey.has(key);
    }
    hasValue(value) {
        const sets = Array.from(this.valuesByKey.values());
        return sets.some((set) => set.has(value));
    }
    getValuesForKey(key) {
        const values = this.valuesByKey.get(key);
        return values ? Array.from(values) : [];
    }
    getKeysForValue(value) {
        return Array.from(this.valuesByKey)
            .filter(([_key, values]) => values.has(value))
            .map(([key, _values]) => key);
    }
}

class IndexedMultimap extends Multimap {
    constructor() {
        super();
        this.keysByValue = new Map();
    }
    get values() {
        return Array.from(this.keysByValue.keys());
    }
    add(key, value) {
        super.add(key, value);
        add(this.keysByValue, value, key);
    }
    delete(key, value) {
        super.delete(key, value);
        del(this.keysByValue, value, key);
    }
    hasValue(value) {
        return this.keysByValue.has(value);
    }
    getKeysForValue(value) {
        const set = this.keysByValue.get(value);
        return set ? Array.from(set) : [];
    }
}

class SelectorObserver {
    constructor(element, selector, delegate, details) {
        this._selector = selector;
        this.details = details;
        this.elementObserver = new ElementObserver(element, this);
        this.delegate = delegate;
        this.matchesByElement = new Multimap();
    }
    get started() {
        return this.elementObserver.started;
    }
    get selector() {
        return this._selector;
    }
    set selector(selector) {
        this._selector = selector;
        this.refresh();
    }
    start() {
        this.elementObserver.start();
    }
    pause(callback) {
        this.elementObserver.pause(callback);
    }
    stop() {
        this.elementObserver.stop();
    }
    refresh() {
        this.elementObserver.refresh();
    }
    get element() {
        return this.elementObserver.element;
    }
    matchElement(element) {
        const { selector } = this;
        if (selector) {
            const matches = element.matches(selector);
            if (this.delegate.selectorMatchElement) {
                return matches && this.delegate.selectorMatchElement(element, this.details);
            }
            return matches;
        }
        else {
            return false;
        }
    }
    matchElementsInTree(tree) {
        const { selector } = this;
        if (selector) {
            const match = this.matchElement(tree) ? [tree] : [];
            const matches = Array.from(tree.querySelectorAll(selector)).filter((match) => this.matchElement(match));
            return match.concat(matches);
        }
        else {
            return [];
        }
    }
    elementMatched(element) {
        const { selector } = this;
        if (selector) {
            this.selectorMatched(element, selector);
        }
    }
    elementUnmatched(element) {
        const selectors = this.matchesByElement.getKeysForValue(element);
        for (const selector of selectors) {
            this.selectorUnmatched(element, selector);
        }
    }
    elementAttributeChanged(element, _attributeName) {
        const { selector } = this;
        if (selector) {
            const matches = this.matchElement(element);
            const matchedBefore = this.matchesByElement.has(selector, element);
            if (matches && !matchedBefore) {
                this.selectorMatched(element, selector);
            }
            else if (!matches && matchedBefore) {
                this.selectorUnmatched(element, selector);
            }
        }
    }
    selectorMatched(element, selector) {
        this.delegate.selectorMatched(element, selector, this.details);
        this.matchesByElement.add(selector, element);
    }
    selectorUnmatched(element, selector) {
        this.delegate.selectorUnmatched(element, selector, this.details);
        this.matchesByElement.delete(selector, element);
    }
}

class StringMapObserver {
    constructor(element, delegate) {
        this.element = element;
        this.delegate = delegate;
        this.started = false;
        this.stringMap = new Map();
        this.mutationObserver = new MutationObserver((mutations) => this.processMutations(mutations));
    }
    start() {
        if (!this.started) {
            this.started = true;
            this.mutationObserver.observe(this.element, { attributes: true, attributeOldValue: true });
            this.refresh();
        }
    }
    stop() {
        if (this.started) {
            this.mutationObserver.takeRecords();
            this.mutationObserver.disconnect();
            this.started = false;
        }
    }
    refresh() {
        if (this.started) {
            for (const attributeName of this.knownAttributeNames) {
                this.refreshAttribute(attributeName, null);
            }
        }
    }
    processMutations(mutations) {
        if (this.started) {
            for (const mutation of mutations) {
                this.processMutation(mutation);
            }
        }
    }
    processMutation(mutation) {
        const attributeName = mutation.attributeName;
        if (attributeName) {
            this.refreshAttribute(attributeName, mutation.oldValue);
        }
    }
    refreshAttribute(attributeName, oldValue) {
        const key = this.delegate.getStringMapKeyForAttribute(attributeName);
        if (key != null) {
            if (!this.stringMap.has(attributeName)) {
                this.stringMapKeyAdded(key, attributeName);
            }
            const value = this.element.getAttribute(attributeName);
            if (this.stringMap.get(attributeName) != value) {
                this.stringMapValueChanged(value, key, oldValue);
            }
            if (value == null) {
                const oldValue = this.stringMap.get(attributeName);
                this.stringMap.delete(attributeName);
                if (oldValue)
                    this.stringMapKeyRemoved(key, attributeName, oldValue);
            }
            else {
                this.stringMap.set(attributeName, value);
            }
        }
    }
    stringMapKeyAdded(key, attributeName) {
        if (this.delegate.stringMapKeyAdded) {
            this.delegate.stringMapKeyAdded(key, attributeName);
        }
    }
    stringMapValueChanged(value, key, oldValue) {
        if (this.delegate.stringMapValueChanged) {
            this.delegate.stringMapValueChanged(value, key, oldValue);
        }
    }
    stringMapKeyRemoved(key, attributeName, oldValue) {
        if (this.delegate.stringMapKeyRemoved) {
            this.delegate.stringMapKeyRemoved(key, attributeName, oldValue);
        }
    }
    get knownAttributeNames() {
        return Array.from(new Set(this.currentAttributeNames.concat(this.recordedAttributeNames)));
    }
    get currentAttributeNames() {
        return Array.from(this.element.attributes).map((attribute) => attribute.name);
    }
    get recordedAttributeNames() {
        return Array.from(this.stringMap.keys());
    }
}

class TokenListObserver {
    constructor(element, attributeName, delegate) {
        this.attributeObserver = new AttributeObserver(element, attributeName, this);
        this.delegate = delegate;
        this.tokensByElement = new Multimap();
    }
    get started() {
        return this.attributeObserver.started;
    }
    start() {
        this.attributeObserver.start();
    }
    pause(callback) {
        this.attributeObserver.pause(callback);
    }
    stop() {
        this.attributeObserver.stop();
    }
    refresh() {
        this.attributeObserver.refresh();
    }
    get element() {
        return this.attributeObserver.element;
    }
    get attributeName() {
        return this.attributeObserver.attributeName;
    }
    elementMatchedAttribute(element) {
        this.tokensMatched(this.readTokensForElement(element));
    }
    elementAttributeValueChanged(element) {
        const [unmatchedTokens, matchedTokens] = this.refreshTokensForElement(element);
        this.tokensUnmatched(unmatchedTokens);
        this.tokensMatched(matchedTokens);
    }
    elementUnmatchedAttribute(element) {
        this.tokensUnmatched(this.tokensByElement.getValuesForKey(element));
    }
    tokensMatched(tokens) {
        tokens.forEach((token) => this.tokenMatched(token));
    }
    tokensUnmatched(tokens) {
        tokens.forEach((token) => this.tokenUnmatched(token));
    }
    tokenMatched(token) {
        this.delegate.tokenMatched(token);
        this.tokensByElement.add(token.element, token);
    }
    tokenUnmatched(token) {
        this.delegate.tokenUnmatched(token);
        this.tokensByElement.delete(token.element, token);
    }
    refreshTokensForElement(element) {
        const previousTokens = this.tokensByElement.getValuesForKey(element);
        const currentTokens = this.readTokensForElement(element);
        const firstDifferingIndex = zip(previousTokens, currentTokens).findIndex(([previousToken, currentToken]) => !tokensAreEqual(previousToken, currentToken));
        if (firstDifferingIndex == -1) {
            return [[], []];
        }
        else {
            return [previousTokens.slice(firstDifferingIndex), currentTokens.slice(firstDifferingIndex)];
        }
    }
    readTokensForElement(element) {
        const attributeName = this.attributeName;
        const tokenString = element.getAttribute(attributeName) || "";
        return parseTokenString(tokenString, element, attributeName);
    }
}
function parseTokenString(tokenString, element, attributeName) {
    return tokenString
        .trim()
        .split(/\s+/)
        .filter((content) => content.length)
        .map((content, index) => ({ element, attributeName, content, index }));
}
function zip(left, right) {
    const length = Math.max(left.length, right.length);
    return Array.from({ length }, (_, index) => [left[index], right[index]]);
}
function tokensAreEqual(left, right) {
    return left && right && left.index == right.index && left.content == right.content;
}

class ValueListObserver {
    constructor(element, attributeName, delegate) {
        this.tokenListObserver = new TokenListObserver(element, attributeName, this);
        this.delegate = delegate;
        this.parseResultsByToken = new WeakMap();
        this.valuesByTokenByElement = new WeakMap();
    }
    get started() {
        return this.tokenListObserver.started;
    }
    start() {
        this.tokenListObserver.start();
    }
    stop() {
        this.tokenListObserver.stop();
    }
    refresh() {
        this.tokenListObserver.refresh();
    }
    get element() {
        return this.tokenListObserver.element;
    }
    get attributeName() {
        return this.tokenListObserver.attributeName;
    }
    tokenMatched(token) {
        const { element } = token;
        const { value } = this.fetchParseResultForToken(token);
        if (value) {
            this.fetchValuesByTokenForElement(element).set(token, value);
            this.delegate.elementMatchedValue(element, value);
        }
    }
    tokenUnmatched(token) {
        const { element } = token;
        const { value } = this.fetchParseResultForToken(token);
        if (value) {
            this.fetchValuesByTokenForElement(element).delete(token);
            this.delegate.elementUnmatchedValue(element, value);
        }
    }
    fetchParseResultForToken(token) {
        let parseResult = this.parseResultsByToken.get(token);
        if (!parseResult) {
            parseResult = this.parseToken(token);
            this.parseResultsByToken.set(token, parseResult);
        }
        return parseResult;
    }
    fetchValuesByTokenForElement(element) {
        let valuesByToken = this.valuesByTokenByElement.get(element);
        if (!valuesByToken) {
            valuesByToken = new Map();
            this.valuesByTokenByElement.set(element, valuesByToken);
        }
        return valuesByToken;
    }
    parseToken(token) {
        try {
            const value = this.delegate.parseValueForToken(token);
            return { value };
        }
        catch (error) {
            return { error };
        }
    }
}

class BindingObserver {
    constructor(context, delegate) {
        this.context = context;
        this.delegate = delegate;
        this.bindingsByAction = new Map();
    }
    start() {
        if (!this.valueListObserver) {
            this.valueListObserver = new ValueListObserver(this.element, this.actionAttribute, this);
            this.valueListObserver.start();
        }
    }
    stop() {
        if (this.valueListObserver) {
            this.valueListObserver.stop();
            delete this.valueListObserver;
            this.disconnectAllActions();
        }
    }
    get element() {
        return this.context.element;
    }
    get identifier() {
        return this.context.identifier;
    }
    get actionAttribute() {
        return this.schema.actionAttribute;
    }
    get schema() {
        return this.context.schema;
    }
    get bindings() {
        return Array.from(this.bindingsByAction.values());
    }
    connectAction(action) {
        const binding = new Binding(this.context, action);
        this.bindingsByAction.set(action, binding);
        this.delegate.bindingConnected(binding);
    }
    disconnectAction(action) {
        const binding = this.bindingsByAction.get(action);
        if (binding) {
            this.bindingsByAction.delete(action);
            this.delegate.bindingDisconnected(binding);
        }
    }
    disconnectAllActions() {
        this.bindings.forEach((binding) => this.delegate.bindingDisconnected(binding, true));
        this.bindingsByAction.clear();
    }
    parseValueForToken(token) {
        const action = Action.forToken(token, this.schema);
        if (action.identifier == this.identifier) {
            return action;
        }
    }
    elementMatchedValue(element, action) {
        this.connectAction(action);
    }
    elementUnmatchedValue(element, action) {
        this.disconnectAction(action);
    }
}

class ValueObserver {
    constructor(context, receiver) {
        this.context = context;
        this.receiver = receiver;
        this.stringMapObserver = new StringMapObserver(this.element, this);
        this.valueDescriptorMap = this.controller.valueDescriptorMap;
    }
    start() {
        this.stringMapObserver.start();
        this.invokeChangedCallbacksForDefaultValues();
    }
    stop() {
        this.stringMapObserver.stop();
    }
    get element() {
        return this.context.element;
    }
    get controller() {
        return this.context.controller;
    }
    getStringMapKeyForAttribute(attributeName) {
        if (attributeName in this.valueDescriptorMap) {
            return this.valueDescriptorMap[attributeName].name;
        }
    }
    stringMapKeyAdded(key, attributeName) {
        const descriptor = this.valueDescriptorMap[attributeName];
        if (!this.hasValue(key)) {
            this.invokeChangedCallback(key, descriptor.writer(this.receiver[key]), descriptor.writer(descriptor.defaultValue));
        }
    }
    stringMapValueChanged(value, name, oldValue) {
        const descriptor = this.valueDescriptorNameMap[name];
        if (value === null)
            return;
        if (oldValue === null) {
            oldValue = descriptor.writer(descriptor.defaultValue);
        }
        this.invokeChangedCallback(name, value, oldValue);
    }
    stringMapKeyRemoved(key, attributeName, oldValue) {
        const descriptor = this.valueDescriptorNameMap[key];
        if (this.hasValue(key)) {
            this.invokeChangedCallback(key, descriptor.writer(this.receiver[key]), oldValue);
        }
        else {
            this.invokeChangedCallback(key, descriptor.writer(descriptor.defaultValue), oldValue);
        }
    }
    invokeChangedCallbacksForDefaultValues() {
        for (const { key, name, defaultValue, writer } of this.valueDescriptors) {
            if (defaultValue != undefined && !this.controller.data.has(key)) {
                this.invokeChangedCallback(name, writer(defaultValue), undefined);
            }
        }
    }
    invokeChangedCallback(name, rawValue, rawOldValue) {
        const changedMethodName = `${name}Changed`;
        const changedMethod = this.receiver[changedMethodName];
        if (typeof changedMethod == "function") {
            const descriptor = this.valueDescriptorNameMap[name];
            try {
                const value = descriptor.reader(rawValue);
                let oldValue = rawOldValue;
                if (rawOldValue) {
                    oldValue = descriptor.reader(rawOldValue);
                }
                changedMethod.call(this.receiver, value, oldValue);
            }
            catch (error) {
                if (error instanceof TypeError) {
                    error.message = `Stimulus Value "${this.context.identifier}.${descriptor.name}" - ${error.message}`;
                }
                throw error;
            }
        }
    }
    get valueDescriptors() {
        const { valueDescriptorMap } = this;
        return Object.keys(valueDescriptorMap).map((key) => valueDescriptorMap[key]);
    }
    get valueDescriptorNameMap() {
        const descriptors = {};
        Object.keys(this.valueDescriptorMap).forEach((key) => {
            const descriptor = this.valueDescriptorMap[key];
            descriptors[descriptor.name] = descriptor;
        });
        return descriptors;
    }
    hasValue(attributeName) {
        const descriptor = this.valueDescriptorNameMap[attributeName];
        const hasMethodName = `has${capitalize(descriptor.name)}`;
        return this.receiver[hasMethodName];
    }
}

class TargetObserver {
    constructor(context, delegate) {
        this.context = context;
        this.delegate = delegate;
        this.targetsByName = new Multimap();
    }
    start() {
        if (!this.tokenListObserver) {
            this.tokenListObserver = new TokenListObserver(this.element, this.attributeName, this);
            this.tokenListObserver.start();
        }
    }
    stop() {
        if (this.tokenListObserver) {
            this.disconnectAllTargets();
            this.tokenListObserver.stop();
            delete this.tokenListObserver;
        }
    }
    tokenMatched({ element, content: name }) {
        if (this.scope.containsElement(element)) {
            this.connectTarget(element, name);
        }
    }
    tokenUnmatched({ element, content: name }) {
        this.disconnectTarget(element, name);
    }
    connectTarget(element, name) {
        var _a;
        if (!this.targetsByName.has(name, element)) {
            this.targetsByName.add(name, element);
            (_a = this.tokenListObserver) === null || _a === void 0 ? void 0 : _a.pause(() => this.delegate.targetConnected(element, name));
        }
    }
    disconnectTarget(element, name) {
        var _a;
        if (this.targetsByName.has(name, element)) {
            this.targetsByName.delete(name, element);
            (_a = this.tokenListObserver) === null || _a === void 0 ? void 0 : _a.pause(() => this.delegate.targetDisconnected(element, name));
        }
    }
    disconnectAllTargets() {
        for (const name of this.targetsByName.keys) {
            for (const element of this.targetsByName.getValuesForKey(name)) {
                this.disconnectTarget(element, name);
            }
        }
    }
    get attributeName() {
        return `data-${this.context.identifier}-target`;
    }
    get element() {
        return this.context.element;
    }
    get scope() {
        return this.context.scope;
    }
}

function readInheritableStaticArrayValues(constructor, propertyName) {
    const ancestors = getAncestorsForConstructor(constructor);
    return Array.from(ancestors.reduce((values, constructor) => {
        getOwnStaticArrayValues(constructor, propertyName).forEach((name) => values.add(name));
        return values;
    }, new Set()));
}
function readInheritableStaticObjectPairs(constructor, propertyName) {
    const ancestors = getAncestorsForConstructor(constructor);
    return ancestors.reduce((pairs, constructor) => {
        pairs.push(...getOwnStaticObjectPairs(constructor, propertyName));
        return pairs;
    }, []);
}
function getAncestorsForConstructor(constructor) {
    const ancestors = [];
    while (constructor) {
        ancestors.push(constructor);
        constructor = Object.getPrototypeOf(constructor);
    }
    return ancestors.reverse();
}
function getOwnStaticArrayValues(constructor, propertyName) {
    const definition = constructor[propertyName];
    return Array.isArray(definition) ? definition : [];
}
function getOwnStaticObjectPairs(constructor, propertyName) {
    const definition = constructor[propertyName];
    return definition ? Object.keys(definition).map((key) => [key, definition[key]]) : [];
}

class OutletObserver {
    constructor(context, delegate) {
        this.started = false;
        this.context = context;
        this.delegate = delegate;
        this.outletsByName = new Multimap();
        this.outletElementsByName = new Multimap();
        this.selectorObserverMap = new Map();
        this.attributeObserverMap = new Map();
    }
    start() {
        if (!this.started) {
            this.outletDefinitions.forEach((outletName) => {
                this.setupSelectorObserverForOutlet(outletName);
                this.setupAttributeObserverForOutlet(outletName);
            });
            this.started = true;
            this.dependentContexts.forEach((context) => context.refresh());
        }
    }
    refresh() {
        this.selectorObserverMap.forEach((observer) => observer.refresh());
        this.attributeObserverMap.forEach((observer) => observer.refresh());
    }
    stop() {
        if (this.started) {
            this.started = false;
            this.disconnectAllOutlets();
            this.stopSelectorObservers();
            this.stopAttributeObservers();
        }
    }
    stopSelectorObservers() {
        if (this.selectorObserverMap.size > 0) {
            this.selectorObserverMap.forEach((observer) => observer.stop());
            this.selectorObserverMap.clear();
        }
    }
    stopAttributeObservers() {
        if (this.attributeObserverMap.size > 0) {
            this.attributeObserverMap.forEach((observer) => observer.stop());
            this.attributeObserverMap.clear();
        }
    }
    selectorMatched(element, _selector, { outletName }) {
        const outlet = this.getOutlet(element, outletName);
        if (outlet) {
            this.connectOutlet(outlet, element, outletName);
        }
    }
    selectorUnmatched(element, _selector, { outletName }) {
        const outlet = this.getOutletFromMap(element, outletName);
        if (outlet) {
            this.disconnectOutlet(outlet, element, outletName);
        }
    }
    selectorMatchElement(element, { outletName }) {
        const selector = this.selector(outletName);
        const hasOutlet = this.hasOutlet(element, outletName);
        const hasOutletController = element.matches(`[${this.schema.controllerAttribute}~=${outletName}]`);
        if (selector) {
            return hasOutlet && hasOutletController && element.matches(selector);
        }
        else {
            return false;
        }
    }
    elementMatchedAttribute(_element, attributeName) {
        const outletName = this.getOutletNameFromOutletAttributeName(attributeName);
        if (outletName) {
            this.updateSelectorObserverForOutlet(outletName);
        }
    }
    elementAttributeValueChanged(_element, attributeName) {
        const outletName = this.getOutletNameFromOutletAttributeName(attributeName);
        if (outletName) {
            this.updateSelectorObserverForOutlet(outletName);
        }
    }
    elementUnmatchedAttribute(_element, attributeName) {
        const outletName = this.getOutletNameFromOutletAttributeName(attributeName);
        if (outletName) {
            this.updateSelectorObserverForOutlet(outletName);
        }
    }
    connectOutlet(outlet, element, outletName) {
        var _a;
        if (!this.outletElementsByName.has(outletName, element)) {
            this.outletsByName.add(outletName, outlet);
            this.outletElementsByName.add(outletName, element);
            (_a = this.selectorObserverMap.get(outletName)) === null || _a === void 0 ? void 0 : _a.pause(() => this.delegate.outletConnected(outlet, element, outletName));
        }
    }
    disconnectOutlet(outlet, element, outletName) {
        var _a;
        if (this.outletElementsByName.has(outletName, element)) {
            this.outletsByName.delete(outletName, outlet);
            this.outletElementsByName.delete(outletName, element);
            (_a = this.selectorObserverMap
                .get(outletName)) === null || _a === void 0 ? void 0 : _a.pause(() => this.delegate.outletDisconnected(outlet, element, outletName));
        }
    }
    disconnectAllOutlets() {
        for (const outletName of this.outletElementsByName.keys) {
            for (const element of this.outletElementsByName.getValuesForKey(outletName)) {
                for (const outlet of this.outletsByName.getValuesForKey(outletName)) {
                    this.disconnectOutlet(outlet, element, outletName);
                }
            }
        }
    }
    updateSelectorObserverForOutlet(outletName) {
        const observer = this.selectorObserverMap.get(outletName);
        if (observer) {
            observer.selector = this.selector(outletName);
        }
    }
    setupSelectorObserverForOutlet(outletName) {
        const selector = this.selector(outletName);
        const selectorObserver = new SelectorObserver(document.body, selector, this, { outletName });
        this.selectorObserverMap.set(outletName, selectorObserver);
        selectorObserver.start();
    }
    setupAttributeObserverForOutlet(outletName) {
        const attributeName = this.attributeNameForOutletName(outletName);
        const attributeObserver = new AttributeObserver(this.scope.element, attributeName, this);
        this.attributeObserverMap.set(outletName, attributeObserver);
        attributeObserver.start();
    }
    selector(outletName) {
        return this.scope.outlets.getSelectorForOutletName(outletName);
    }
    attributeNameForOutletName(outletName) {
        return this.scope.schema.outletAttributeForScope(this.identifier, outletName);
    }
    getOutletNameFromOutletAttributeName(attributeName) {
        return this.outletDefinitions.find((outletName) => this.attributeNameForOutletName(outletName) === attributeName);
    }
    get outletDependencies() {
        const dependencies = new Multimap();
        this.router.modules.forEach((module) => {
            const constructor = module.definition.controllerConstructor;
            const outlets = readInheritableStaticArrayValues(constructor, "outlets");
            outlets.forEach((outlet) => dependencies.add(outlet, module.identifier));
        });
        return dependencies;
    }
    get outletDefinitions() {
        return this.outletDependencies.getKeysForValue(this.identifier);
    }
    get dependentControllerIdentifiers() {
        return this.outletDependencies.getValuesForKey(this.identifier);
    }
    get dependentContexts() {
        const identifiers = this.dependentControllerIdentifiers;
        return this.router.contexts.filter((context) => identifiers.includes(context.identifier));
    }
    hasOutlet(element, outletName) {
        return !!this.getOutlet(element, outletName) || !!this.getOutletFromMap(element, outletName);
    }
    getOutlet(element, outletName) {
        return this.application.getControllerForElementAndIdentifier(element, outletName);
    }
    getOutletFromMap(element, outletName) {
        return this.outletsByName.getValuesForKey(outletName).find((outlet) => outlet.element === element);
    }
    get scope() {
        return this.context.scope;
    }
    get schema() {
        return this.context.schema;
    }
    get identifier() {
        return this.context.identifier;
    }
    get application() {
        return this.context.application;
    }
    get router() {
        return this.application.router;
    }
}

class Context {
    constructor(module, scope) {
        this.logDebugActivity = (functionName, detail = {}) => {
            const { identifier, controller, element } = this;
            detail = Object.assign({ identifier, controller, element }, detail);
            this.application.logDebugActivity(this.identifier, functionName, detail);
        };
        this.module = module;
        this.scope = scope;
        this.controller = new module.controllerConstructor(this);
        this.bindingObserver = new BindingObserver(this, this.dispatcher);
        this.valueObserver = new ValueObserver(this, this.controller);
        this.targetObserver = new TargetObserver(this, this);
        this.outletObserver = new OutletObserver(this, this);
        try {
            this.controller.initialize();
            this.logDebugActivity("initialize");
        }
        catch (error) {
            this.handleError(error, "initializing controller");
        }
    }
    connect() {
        this.bindingObserver.start();
        this.valueObserver.start();
        this.targetObserver.start();
        this.outletObserver.start();
        try {
            this.controller.connect();
            this.logDebugActivity("connect");
        }
        catch (error) {
            this.handleError(error, "connecting controller");
        }
    }
    refresh() {
        this.outletObserver.refresh();
    }
    disconnect() {
        try {
            this.controller.disconnect();
            this.logDebugActivity("disconnect");
        }
        catch (error) {
            this.handleError(error, "disconnecting controller");
        }
        this.outletObserver.stop();
        this.targetObserver.stop();
        this.valueObserver.stop();
        this.bindingObserver.stop();
    }
    get application() {
        return this.module.application;
    }
    get identifier() {
        return this.module.identifier;
    }
    get schema() {
        return this.application.schema;
    }
    get dispatcher() {
        return this.application.dispatcher;
    }
    get element() {
        return this.scope.element;
    }
    get parentElement() {
        return this.element.parentElement;
    }
    handleError(error, message, detail = {}) {
        const { identifier, controller, element } = this;
        detail = Object.assign({ identifier, controller, element }, detail);
        this.application.handleError(error, `Error ${message}`, detail);
    }
    targetConnected(element, name) {
        this.invokeControllerMethod(`${name}TargetConnected`, element);
    }
    targetDisconnected(element, name) {
        this.invokeControllerMethod(`${name}TargetDisconnected`, element);
    }
    outletConnected(outlet, element, name) {
        this.invokeControllerMethod(`${namespaceCamelize(name)}OutletConnected`, outlet, element);
    }
    outletDisconnected(outlet, element, name) {
        this.invokeControllerMethod(`${namespaceCamelize(name)}OutletDisconnected`, outlet, element);
    }
    invokeControllerMethod(methodName, ...args) {
        const controller = this.controller;
        if (typeof controller[methodName] == "function") {
            controller[methodName](...args);
        }
    }
}

function bless(constructor) {
    return shadow(constructor, getBlessedProperties(constructor));
}
function shadow(constructor, properties) {
    const shadowConstructor = extend(constructor);
    const shadowProperties = getShadowProperties(constructor.prototype, properties);
    Object.defineProperties(shadowConstructor.prototype, shadowProperties);
    return shadowConstructor;
}
function getBlessedProperties(constructor) {
    const blessings = readInheritableStaticArrayValues(constructor, "blessings");
    return blessings.reduce((blessedProperties, blessing) => {
        const properties = blessing(constructor);
        for (const key in properties) {
            const descriptor = blessedProperties[key] || {};
            blessedProperties[key] = Object.assign(descriptor, properties[key]);
        }
        return blessedProperties;
    }, {});
}
function getShadowProperties(prototype, properties) {
    return getOwnKeys(properties).reduce((shadowProperties, key) => {
        const descriptor = getShadowedDescriptor(prototype, properties, key);
        if (descriptor) {
            Object.assign(shadowProperties, { [key]: descriptor });
        }
        return shadowProperties;
    }, {});
}
function getShadowedDescriptor(prototype, properties, key) {
    const shadowingDescriptor = Object.getOwnPropertyDescriptor(prototype, key);
    const shadowedByValue = shadowingDescriptor && "value" in shadowingDescriptor;
    if (!shadowedByValue) {
        const descriptor = Object.getOwnPropertyDescriptor(properties, key).value;
        if (shadowingDescriptor) {
            descriptor.get = shadowingDescriptor.get || descriptor.get;
            descriptor.set = shadowingDescriptor.set || descriptor.set;
        }
        return descriptor;
    }
}
const getOwnKeys = (() => {
    if (typeof Object.getOwnPropertySymbols == "function") {
        return (object) => [...Object.getOwnPropertyNames(object), ...Object.getOwnPropertySymbols(object)];
    }
    else {
        return Object.getOwnPropertyNames;
    }
})();
const extend = (() => {
    function extendWithReflect(constructor) {
        function extended() {
            return Reflect.construct(constructor, arguments, new.target);
        }
        extended.prototype = Object.create(constructor.prototype, {
            constructor: { value: extended },
        });
        Reflect.setPrototypeOf(extended, constructor);
        return extended;
    }
    function testReflectExtension() {
        const a = function () {
            this.a.call(this);
        };
        const b = extendWithReflect(a);
        b.prototype.a = function () { };
        return new b();
    }
    try {
        testReflectExtension();
        return extendWithReflect;
    }
    catch (error) {
        return (constructor) => class extended extends constructor {
        };
    }
})();

function blessDefinition(definition) {
    return {
        identifier: definition.identifier,
        controllerConstructor: bless(definition.controllerConstructor),
    };
}

class Module {
    constructor(application, definition) {
        this.application = application;
        this.definition = blessDefinition(definition);
        this.contextsByScope = new WeakMap();
        this.connectedContexts = new Set();
    }
    get identifier() {
        return this.definition.identifier;
    }
    get controllerConstructor() {
        return this.definition.controllerConstructor;
    }
    get contexts() {
        return Array.from(this.connectedContexts);
    }
    connectContextForScope(scope) {
        const context = this.fetchContextForScope(scope);
        this.connectedContexts.add(context);
        context.connect();
    }
    disconnectContextForScope(scope) {
        const context = this.contextsByScope.get(scope);
        if (context) {
            this.connectedContexts.delete(context);
            context.disconnect();
        }
    }
    fetchContextForScope(scope) {
        let context = this.contextsByScope.get(scope);
        if (!context) {
            context = new Context(this, scope);
            this.contextsByScope.set(scope, context);
        }
        return context;
    }
}

class ClassMap {
    constructor(scope) {
        this.scope = scope;
    }
    has(name) {
        return this.data.has(this.getDataKey(name));
    }
    get(name) {
        return this.getAll(name)[0];
    }
    getAll(name) {
        const tokenString = this.data.get(this.getDataKey(name)) || "";
        return tokenize(tokenString);
    }
    getAttributeName(name) {
        return this.data.getAttributeNameForKey(this.getDataKey(name));
    }
    getDataKey(name) {
        return `${name}-class`;
    }
    get data() {
        return this.scope.data;
    }
}

class DataMap {
    constructor(scope) {
        this.scope = scope;
    }
    get element() {
        return this.scope.element;
    }
    get identifier() {
        return this.scope.identifier;
    }
    get(key) {
        const name = this.getAttributeNameForKey(key);
        return this.element.getAttribute(name);
    }
    set(key, value) {
        const name = this.getAttributeNameForKey(key);
        this.element.setAttribute(name, value);
        return this.get(key);
    }
    has(key) {
        const name = this.getAttributeNameForKey(key);
        return this.element.hasAttribute(name);
    }
    delete(key) {
        if (this.has(key)) {
            const name = this.getAttributeNameForKey(key);
            this.element.removeAttribute(name);
            return true;
        }
        else {
            return false;
        }
    }
    getAttributeNameForKey(key) {
        return `data-${this.identifier}-${dasherize(key)}`;
    }
}

class Guide {
    constructor(logger) {
        this.warnedKeysByObject = new WeakMap();
        this.logger = logger;
    }
    warn(object, key, message) {
        let warnedKeys = this.warnedKeysByObject.get(object);
        if (!warnedKeys) {
            warnedKeys = new Set();
            this.warnedKeysByObject.set(object, warnedKeys);
        }
        if (!warnedKeys.has(key)) {
            warnedKeys.add(key);
            this.logger.warn(message, object);
        }
    }
}

function attributeValueContainsToken(attributeName, token) {
    return `[${attributeName}~="${token}"]`;
}

class TargetSet {
    constructor(scope) {
        this.scope = scope;
    }
    get element() {
        return this.scope.element;
    }
    get identifier() {
        return this.scope.identifier;
    }
    get schema() {
        return this.scope.schema;
    }
    has(targetName) {
        return this.find(targetName) != null;
    }
    find(...targetNames) {
        return targetNames.reduce((target, targetName) => target || this.findTarget(targetName) || this.findLegacyTarget(targetName), undefined);
    }
    findAll(...targetNames) {
        return targetNames.reduce((targets, targetName) => [
            ...targets,
            ...this.findAllTargets(targetName),
            ...this.findAllLegacyTargets(targetName),
        ], []);
    }
    findTarget(targetName) {
        const selector = this.getSelectorForTargetName(targetName);
        return this.scope.findElement(selector);
    }
    findAllTargets(targetName) {
        const selector = this.getSelectorForTargetName(targetName);
        return this.scope.findAllElements(selector);
    }
    getSelectorForTargetName(targetName) {
        const attributeName = this.schema.targetAttributeForScope(this.identifier);
        return attributeValueContainsToken(attributeName, targetName);
    }
    findLegacyTarget(targetName) {
        const selector = this.getLegacySelectorForTargetName(targetName);
        return this.deprecate(this.scope.findElement(selector), targetName);
    }
    findAllLegacyTargets(targetName) {
        const selector = this.getLegacySelectorForTargetName(targetName);
        return this.scope.findAllElements(selector).map((element) => this.deprecate(element, targetName));
    }
    getLegacySelectorForTargetName(targetName) {
        const targetDescriptor = `${this.identifier}.${targetName}`;
        return attributeValueContainsToken(this.schema.targetAttribute, targetDescriptor);
    }
    deprecate(element, targetName) {
        if (element) {
            const { identifier } = this;
            const attributeName = this.schema.targetAttribute;
            const revisedAttributeName = this.schema.targetAttributeForScope(identifier);
            this.guide.warn(element, `target:${targetName}`, `Please replace ${attributeName}="${identifier}.${targetName}" with ${revisedAttributeName}="${targetName}". ` +
                `The ${attributeName} attribute is deprecated and will be removed in a future version of Stimulus.`);
        }
        return element;
    }
    get guide() {
        return this.scope.guide;
    }
}

class OutletSet {
    constructor(scope, controllerElement) {
        this.scope = scope;
        this.controllerElement = controllerElement;
    }
    get element() {
        return this.scope.element;
    }
    get identifier() {
        return this.scope.identifier;
    }
    get schema() {
        return this.scope.schema;
    }
    has(outletName) {
        return this.find(outletName) != null;
    }
    find(...outletNames) {
        return outletNames.reduce((outlet, outletName) => outlet || this.findOutlet(outletName), undefined);
    }
    findAll(...outletNames) {
        return outletNames.reduce((outlets, outletName) => [...outlets, ...this.findAllOutlets(outletName)], []);
    }
    getSelectorForOutletName(outletName) {
        const attributeName = this.schema.outletAttributeForScope(this.identifier, outletName);
        return this.controllerElement.getAttribute(attributeName);
    }
    findOutlet(outletName) {
        const selector = this.getSelectorForOutletName(outletName);
        if (selector)
            return this.findElement(selector, outletName);
    }
    findAllOutlets(outletName) {
        const selector = this.getSelectorForOutletName(outletName);
        return selector ? this.findAllElements(selector, outletName) : [];
    }
    findElement(selector, outletName) {
        const elements = this.scope.queryElements(selector);
        return elements.filter((element) => this.matchesElement(element, selector, outletName))[0];
    }
    findAllElements(selector, outletName) {
        const elements = this.scope.queryElements(selector);
        return elements.filter((element) => this.matchesElement(element, selector, outletName));
    }
    matchesElement(element, selector, outletName) {
        const controllerAttribute = element.getAttribute(this.scope.schema.controllerAttribute) || "";
        return element.matches(selector) && controllerAttribute.split(" ").includes(outletName);
    }
}

class Scope {
    constructor(schema, element, identifier, logger) {
        this.targets = new TargetSet(this);
        this.classes = new ClassMap(this);
        this.data = new DataMap(this);
        this.containsElement = (element) => {
            return element.closest(this.controllerSelector) === this.element;
        };
        this.schema = schema;
        this.element = element;
        this.identifier = identifier;
        this.guide = new Guide(logger);
        this.outlets = new OutletSet(this.documentScope, element);
    }
    findElement(selector) {
        return this.element.matches(selector) ? this.element : this.queryElements(selector).find(this.containsElement);
    }
    findAllElements(selector) {
        return [
            ...(this.element.matches(selector) ? [this.element] : []),
            ...this.queryElements(selector).filter(this.containsElement),
        ];
    }
    queryElements(selector) {
        return Array.from(this.element.querySelectorAll(selector));
    }
    get controllerSelector() {
        return attributeValueContainsToken(this.schema.controllerAttribute, this.identifier);
    }
    get isDocumentScope() {
        return this.element === document.documentElement;
    }
    get documentScope() {
        return this.isDocumentScope
            ? this
            : new Scope(this.schema, document.documentElement, this.identifier, this.guide.logger);
    }
}

class ScopeObserver {
    constructor(element, schema, delegate) {
        this.element = element;
        this.schema = schema;
        this.delegate = delegate;
        this.valueListObserver = new ValueListObserver(this.element, this.controllerAttribute, this);
        this.scopesByIdentifierByElement = new WeakMap();
        this.scopeReferenceCounts = new WeakMap();
    }
    start() {
        this.valueListObserver.start();
    }
    stop() {
        this.valueListObserver.stop();
    }
    get controllerAttribute() {
        return this.schema.controllerAttribute;
    }
    parseValueForToken(token) {
        const { element, content: identifier } = token;
        return this.parseValueForElementAndIdentifier(element, identifier);
    }
    parseValueForElementAndIdentifier(element, identifier) {
        const scopesByIdentifier = this.fetchScopesByIdentifierForElement(element);
        let scope = scopesByIdentifier.get(identifier);
        if (!scope) {
            scope = this.delegate.createScopeForElementAndIdentifier(element, identifier);
            scopesByIdentifier.set(identifier, scope);
        }
        return scope;
    }
    elementMatchedValue(element, value) {
        const referenceCount = (this.scopeReferenceCounts.get(value) || 0) + 1;
        this.scopeReferenceCounts.set(value, referenceCount);
        if (referenceCount == 1) {
            this.delegate.scopeConnected(value);
        }
    }
    elementUnmatchedValue(element, value) {
        const referenceCount = this.scopeReferenceCounts.get(value);
        if (referenceCount) {
            this.scopeReferenceCounts.set(value, referenceCount - 1);
            if (referenceCount == 1) {
                this.delegate.scopeDisconnected(value);
            }
        }
    }
    fetchScopesByIdentifierForElement(element) {
        let scopesByIdentifier = this.scopesByIdentifierByElement.get(element);
        if (!scopesByIdentifier) {
            scopesByIdentifier = new Map();
            this.scopesByIdentifierByElement.set(element, scopesByIdentifier);
        }
        return scopesByIdentifier;
    }
}

class Router {
    constructor(application) {
        this.application = application;
        this.scopeObserver = new ScopeObserver(this.element, this.schema, this);
        this.scopesByIdentifier = new Multimap();
        this.modulesByIdentifier = new Map();
    }
    get element() {
        return this.application.element;
    }
    get schema() {
        return this.application.schema;
    }
    get logger() {
        return this.application.logger;
    }
    get controllerAttribute() {
        return this.schema.controllerAttribute;
    }
    get modules() {
        return Array.from(this.modulesByIdentifier.values());
    }
    get contexts() {
        return this.modules.reduce((contexts, module) => contexts.concat(module.contexts), []);
    }
    start() {
        this.scopeObserver.start();
    }
    stop() {
        this.scopeObserver.stop();
    }
    loadDefinition(definition) {
        this.unloadIdentifier(definition.identifier);
        const module = new Module(this.application, definition);
        this.connectModule(module);
        const afterLoad = definition.controllerConstructor.afterLoad;
        if (afterLoad) {
            afterLoad.call(definition.controllerConstructor, definition.identifier, this.application);
        }
    }
    unloadIdentifier(identifier) {
        const module = this.modulesByIdentifier.get(identifier);
        if (module) {
            this.disconnectModule(module);
        }
    }
    getContextForElementAndIdentifier(element, identifier) {
        const module = this.modulesByIdentifier.get(identifier);
        if (module) {
            return module.contexts.find((context) => context.element == element);
        }
    }
    proposeToConnectScopeForElementAndIdentifier(element, identifier) {
        const scope = this.scopeObserver.parseValueForElementAndIdentifier(element, identifier);
        if (scope) {
            this.scopeObserver.elementMatchedValue(scope.element, scope);
        }
        else {
            console.error(`Couldn't find or create scope for identifier: "${identifier}" and element:`, element);
        }
    }
    handleError(error, message, detail) {
        this.application.handleError(error, message, detail);
    }
    createScopeForElementAndIdentifier(element, identifier) {
        return new Scope(this.schema, element, identifier, this.logger);
    }
    scopeConnected(scope) {
        this.scopesByIdentifier.add(scope.identifier, scope);
        const module = this.modulesByIdentifier.get(scope.identifier);
        if (module) {
            module.connectContextForScope(scope);
        }
    }
    scopeDisconnected(scope) {
        this.scopesByIdentifier.delete(scope.identifier, scope);
        const module = this.modulesByIdentifier.get(scope.identifier);
        if (module) {
            module.disconnectContextForScope(scope);
        }
    }
    connectModule(module) {
        this.modulesByIdentifier.set(module.identifier, module);
        const scopes = this.scopesByIdentifier.getValuesForKey(module.identifier);
        scopes.forEach((scope) => module.connectContextForScope(scope));
    }
    disconnectModule(module) {
        this.modulesByIdentifier.delete(module.identifier);
        const scopes = this.scopesByIdentifier.getValuesForKey(module.identifier);
        scopes.forEach((scope) => module.disconnectContextForScope(scope));
    }
}

const defaultSchema = {
    controllerAttribute: "data-controller",
    actionAttribute: "data-action",
    targetAttribute: "data-target",
    targetAttributeForScope: (identifier) => `data-${identifier}-target`,
    outletAttributeForScope: (identifier, outlet) => `data-${identifier}-${outlet}-outlet`,
    keyMappings: Object.assign(Object.assign({ enter: "Enter", tab: "Tab", esc: "Escape", space: " ", up: "ArrowUp", down: "ArrowDown", left: "ArrowLeft", right: "ArrowRight", home: "Home", end: "End", page_up: "PageUp", page_down: "PageDown" }, objectFromEntries("abcdefghijklmnopqrstuvwxyz".split("").map((c) => [c, c]))), objectFromEntries("0123456789".split("").map((n) => [n, n]))),
};
function objectFromEntries(array) {
    return array.reduce((memo, [k, v]) => (Object.assign(Object.assign({}, memo), { [k]: v })), {});
}

class Application {
    constructor(element = document.documentElement, schema = defaultSchema) {
        this.logger = console;
        this.debug = false;
        this.logDebugActivity = (identifier, functionName, detail = {}) => {
            if (this.debug) {
                this.logFormattedMessage(identifier, functionName, detail);
            }
        };
        this.element = element;
        this.schema = schema;
        this.dispatcher = new Dispatcher(this);
        this.router = new Router(this);
        this.actionDescriptorFilters = Object.assign({}, defaultActionDescriptorFilters);
    }
    static start(element, schema) {
        const application = new this(element, schema);
        application.start();
        return application;
    }
    async start() {
        await domReady();
        this.logDebugActivity("application", "starting");
        this.dispatcher.start();
        this.router.start();
        this.logDebugActivity("application", "start");
    }
    stop() {
        this.logDebugActivity("application", "stopping");
        this.dispatcher.stop();
        this.router.stop();
        this.logDebugActivity("application", "stop");
    }
    register(identifier, controllerConstructor) {
        this.load({ identifier, controllerConstructor });
    }
    registerActionOption(name, filter) {
        this.actionDescriptorFilters[name] = filter;
    }
    load(head, ...rest) {
        const definitions = Array.isArray(head) ? head : [head, ...rest];
        definitions.forEach((definition) => {
            if (definition.controllerConstructor.shouldLoad) {
                this.router.loadDefinition(definition);
            }
        });
    }
    unload(head, ...rest) {
        const identifiers = Array.isArray(head) ? head : [head, ...rest];
        identifiers.forEach((identifier) => this.router.unloadIdentifier(identifier));
    }
    get controllers() {
        return this.router.contexts.map((context) => context.controller);
    }
    getControllerForElementAndIdentifier(element, identifier) {
        const context = this.router.getContextForElementAndIdentifier(element, identifier);
        return context ? context.controller : null;
    }
    handleError(error, message, detail) {
        var _a;
        this.logger.error(`%s\n\n%o\n\n%o`, message, error, detail);
        (_a = window.onerror) === null || _a === void 0 ? void 0 : _a.call(window, message, "", 0, 0, error);
    }
    logFormattedMessage(identifier, functionName, detail = {}) {
        detail = Object.assign({ application: this }, detail);
        this.logger.groupCollapsed(`${identifier} #${functionName}`);
        this.logger.log("details:", Object.assign({}, detail));
        this.logger.groupEnd();
    }
}
function domReady() {
    return new Promise((resolve) => {
        if (document.readyState == "loading") {
            document.addEventListener("DOMContentLoaded", () => resolve());
        }
        else {
            resolve();
        }
    });
}

function ClassPropertiesBlessing(constructor) {
    const classes = readInheritableStaticArrayValues(constructor, "classes");
    return classes.reduce((properties, classDefinition) => {
        return Object.assign(properties, propertiesForClassDefinition(classDefinition));
    }, {});
}
function propertiesForClassDefinition(key) {
    return {
        [`${key}Class`]: {
            get() {
                const { classes } = this;
                if (classes.has(key)) {
                    return classes.get(key);
                }
                else {
                    const attribute = classes.getAttributeName(key);
                    throw new Error(`Missing attribute "${attribute}"`);
                }
            },
        },
        [`${key}Classes`]: {
            get() {
                return this.classes.getAll(key);
            },
        },
        [`has${capitalize(key)}Class`]: {
            get() {
                return this.classes.has(key);
            },
        },
    };
}

function OutletPropertiesBlessing(constructor) {
    const outlets = readInheritableStaticArrayValues(constructor, "outlets");
    return outlets.reduce((properties, outletDefinition) => {
        return Object.assign(properties, propertiesForOutletDefinition(outletDefinition));
    }, {});
}
function getOutletController(controller, element, identifier) {
    return controller.application.getControllerForElementAndIdentifier(element, identifier);
}
function getControllerAndEnsureConnectedScope(controller, element, outletName) {
    let outletController = getOutletController(controller, element, outletName);
    if (outletController)
        return outletController;
    controller.application.router.proposeToConnectScopeForElementAndIdentifier(element, outletName);
    outletController = getOutletController(controller, element, outletName);
    if (outletController)
        return outletController;
}
function propertiesForOutletDefinition(name) {
    const camelizedName = namespaceCamelize(name);
    return {
        [`${camelizedName}Outlet`]: {
            get() {
                const outletElement = this.outlets.find(name);
                const selector = this.outlets.getSelectorForOutletName(name);
                if (outletElement) {
                    const outletController = getControllerAndEnsureConnectedScope(this, outletElement, name);
                    if (outletController)
                        return outletController;
                    throw new Error(`The provided outlet element is missing an outlet controller "${name}" instance for host controller "${this.identifier}"`);
                }
                throw new Error(`Missing outlet element "${name}" for host controller "${this.identifier}". Stimulus couldn't find a matching outlet element using selector "${selector}".`);
            },
        },
        [`${camelizedName}Outlets`]: {
            get() {
                const outlets = this.outlets.findAll(name);
                if (outlets.length > 0) {
                    return outlets
                        .map((outletElement) => {
                        const outletController = getControllerAndEnsureConnectedScope(this, outletElement, name);
                        if (outletController)
                            return outletController;
                        console.warn(`The provided outlet element is missing an outlet controller "${name}" instance for host controller "${this.identifier}"`, outletElement);
                    })
                        .filter((controller) => controller);
                }
                return [];
            },
        },
        [`${camelizedName}OutletElement`]: {
            get() {
                const outletElement = this.outlets.find(name);
                const selector = this.outlets.getSelectorForOutletName(name);
                if (outletElement) {
                    return outletElement;
                }
                else {
                    throw new Error(`Missing outlet element "${name}" for host controller "${this.identifier}". Stimulus couldn't find a matching outlet element using selector "${selector}".`);
                }
            },
        },
        [`${camelizedName}OutletElements`]: {
            get() {
                return this.outlets.findAll(name);
            },
        },
        [`has${capitalize(camelizedName)}Outlet`]: {
            get() {
                return this.outlets.has(name);
            },
        },
    };
}

function TargetPropertiesBlessing(constructor) {
    const targets = readInheritableStaticArrayValues(constructor, "targets");
    return targets.reduce((properties, targetDefinition) => {
        return Object.assign(properties, propertiesForTargetDefinition(targetDefinition));
    }, {});
}
function propertiesForTargetDefinition(name) {
    return {
        [`${name}Target`]: {
            get() {
                const target = this.targets.find(name);
                if (target) {
                    return target;
                }
                else {
                    throw new Error(`Missing target element "${name}" for "${this.identifier}" controller`);
                }
            },
        },
        [`${name}Targets`]: {
            get() {
                return this.targets.findAll(name);
            },
        },
        [`has${capitalize(name)}Target`]: {
            get() {
                return this.targets.has(name);
            },
        },
    };
}

function ValuePropertiesBlessing(constructor) {
    const valueDefinitionPairs = readInheritableStaticObjectPairs(constructor, "values");
    const propertyDescriptorMap = {
        valueDescriptorMap: {
            get() {
                return valueDefinitionPairs.reduce((result, valueDefinitionPair) => {
                    const valueDescriptor = parseValueDefinitionPair(valueDefinitionPair, this.identifier);
                    const attributeName = this.data.getAttributeNameForKey(valueDescriptor.key);
                    return Object.assign(result, { [attributeName]: valueDescriptor });
                }, {});
            },
        },
    };
    return valueDefinitionPairs.reduce((properties, valueDefinitionPair) => {
        return Object.assign(properties, propertiesForValueDefinitionPair(valueDefinitionPair));
    }, propertyDescriptorMap);
}
function propertiesForValueDefinitionPair(valueDefinitionPair, controller) {
    const definition = parseValueDefinitionPair(valueDefinitionPair, controller);
    const { key, name, reader: read, writer: write } = definition;
    return {
        [name]: {
            get() {
                const value = this.data.get(key);
                if (value !== null) {
                    return read(value);
                }
                else {
                    return definition.defaultValue;
                }
            },
            set(value) {
                if (value === undefined) {
                    this.data.delete(key);
                }
                else {
                    this.data.set(key, write(value));
                }
            },
        },
        [`has${capitalize(name)}`]: {
            get() {
                return this.data.has(key) || definition.hasCustomDefaultValue;
            },
        },
    };
}
function parseValueDefinitionPair([token, typeDefinition], controller) {
    return valueDescriptorForTokenAndTypeDefinition({
        controller,
        token,
        typeDefinition,
    });
}
function parseValueTypeConstant(constant) {
    switch (constant) {
        case Array:
            return "array";
        case Boolean:
            return "boolean";
        case Number:
            return "number";
        case Object:
            return "object";
        case String:
            return "string";
    }
}
function parseValueTypeDefault(defaultValue) {
    switch (typeof defaultValue) {
        case "boolean":
            return "boolean";
        case "number":
            return "number";
        case "string":
            return "string";
    }
    if (Array.isArray(defaultValue))
        return "array";
    if (Object.prototype.toString.call(defaultValue) === "[object Object]")
        return "object";
}
function parseValueTypeObject(payload) {
    const { controller, token, typeObject } = payload;
    const hasType = isSomething(typeObject.type);
    const hasDefault = isSomething(typeObject.default);
    const fullObject = hasType && hasDefault;
    const onlyType = hasType && !hasDefault;
    const onlyDefault = !hasType && hasDefault;
    const typeFromObject = parseValueTypeConstant(typeObject.type);
    const typeFromDefaultValue = parseValueTypeDefault(payload.typeObject.default);
    if (onlyType)
        return typeFromObject;
    if (onlyDefault)
        return typeFromDefaultValue;
    if (typeFromObject !== typeFromDefaultValue) {
        const propertyPath = controller ? `${controller}.${token}` : token;
        throw new Error(`The specified default value for the Stimulus Value "${propertyPath}" must match the defined type "${typeFromObject}". The provided default value of "${typeObject.default}" is of type "${typeFromDefaultValue}".`);
    }
    if (fullObject)
        return typeFromObject;
}
function parseValueTypeDefinition(payload) {
    const { controller, token, typeDefinition } = payload;
    const typeObject = { controller, token, typeObject: typeDefinition };
    const typeFromObject = parseValueTypeObject(typeObject);
    const typeFromDefaultValue = parseValueTypeDefault(typeDefinition);
    const typeFromConstant = parseValueTypeConstant(typeDefinition);
    const type = typeFromObject || typeFromDefaultValue || typeFromConstant;
    if (type)
        return type;
    const propertyPath = controller ? `${controller}.${typeDefinition}` : token;
    throw new Error(`Unknown value type "${propertyPath}" for "${token}" value`);
}
function defaultValueForDefinition(typeDefinition) {
    const constant = parseValueTypeConstant(typeDefinition);
    if (constant)
        return defaultValuesByType[constant];
    const hasDefault = hasProperty(typeDefinition, "default");
    const hasType = hasProperty(typeDefinition, "type");
    const typeObject = typeDefinition;
    if (hasDefault)
        return typeObject.default;
    if (hasType) {
        const { type } = typeObject;
        const constantFromType = parseValueTypeConstant(type);
        if (constantFromType)
            return defaultValuesByType[constantFromType];
    }
    return typeDefinition;
}
function valueDescriptorForTokenAndTypeDefinition(payload) {
    const { token, typeDefinition } = payload;
    const key = `${dasherize(token)}-value`;
    const type = parseValueTypeDefinition(payload);
    return {
        type,
        key,
        name: camelize(key),
        get defaultValue() {
            return defaultValueForDefinition(typeDefinition);
        },
        get hasCustomDefaultValue() {
            return parseValueTypeDefault(typeDefinition) !== undefined;
        },
        reader: readers[type],
        writer: writers[type] || writers.default,
    };
}
const defaultValuesByType = {
    get array() {
        return [];
    },
    boolean: false,
    number: 0,
    get object() {
        return {};
    },
    string: "",
};
const readers = {
    array(value) {
        const array = JSON.parse(value);
        if (!Array.isArray(array)) {
            throw new TypeError(`expected value of type "array" but instead got value "${value}" of type "${parseValueTypeDefault(array)}"`);
        }
        return array;
    },
    boolean(value) {
        return !(value == "0" || String(value).toLowerCase() == "false");
    },
    number(value) {
        return Number(value.replace(/_/g, ""));
    },
    object(value) {
        const object = JSON.parse(value);
        if (object === null || typeof object != "object" || Array.isArray(object)) {
            throw new TypeError(`expected value of type "object" but instead got value "${value}" of type "${parseValueTypeDefault(object)}"`);
        }
        return object;
    },
    string(value) {
        return value;
    },
};
const writers = {
    default: writeString,
    array: writeJSON,
    object: writeJSON,
};
function writeJSON(value) {
    return JSON.stringify(value);
}
function writeString(value) {
    return `${value}`;
}

class Controller {
    constructor(context) {
        this.context = context;
    }
    static get shouldLoad() {
        return true;
    }
    static afterLoad(_identifier, _application) {
        return;
    }
    get application() {
        return this.context.application;
    }
    get scope() {
        return this.context.scope;
    }
    get element() {
        return this.scope.element;
    }
    get identifier() {
        return this.scope.identifier;
    }
    get targets() {
        return this.scope.targets;
    }
    get outlets() {
        return this.scope.outlets;
    }
    get classes() {
        return this.scope.classes;
    }
    get data() {
        return this.scope.data;
    }
    initialize() {
    }
    connect() {
    }
    disconnect() {
    }
    dispatch(eventName, { target = this.element, detail = {}, prefix = this.identifier, bubbles = true, cancelable = true, } = {}) {
        const type = prefix ? `${prefix}:${eventName}` : eventName;
        const event = new CustomEvent(type, { detail, bubbles, cancelable });
        target.dispatchEvent(event);
        return event;
    }
}
Controller.blessings = [
    ClassPropertiesBlessing,
    TargetPropertiesBlessing,
    ValuePropertiesBlessing,
    OutletPropertiesBlessing,
];
Controller.targets = [];
Controller.outlets = [];
Controller.values = {};




/***/ },

/***/ "./node_modules/@symfony/ux-live-component/dist/live_controller.js"
/*!*************************************************************************!*\
  !*** ./node_modules/@symfony/ux-live-component/dist/live_controller.js ***!
  \*************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Component: () => (/* binding */ Component),
/* harmony export */   "default": () => (/* binding */ LiveControllerDefault),
/* harmony export */   getComponent: () => (/* binding */ getComponent)
/* harmony export */ });
/* harmony import */ var _hotwired_stimulus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @hotwired/stimulus */ "./node_modules/@hotwired/stimulus/dist/stimulus.js");

var BackendRequest_default = class {
	constructor(promise, actions, updateModels) {
		this.isResolved = false;
		this.promise = promise;
		this.promise.then((response) => {
			this.isResolved = true;
			return response;
		});
		this.actions = actions;
		this.updatedModels = updateModels;
	}
	containsOneOfActions(targetedActions) {
		return this.actions.filter((action) => targetedActions.includes(action)).length > 0;
	}
	areAnyModelsUpdated(targetedModels) {
		return this.updatedModels.filter((model) => targetedModels.includes(model)).length > 0;
	}
};
var RequestBuilder_default = class {
	constructor(url, method = "post", credentials = "same-origin") {
		this.url = url;
		this.method = method;
		this.credentials = credentials;
	}
	buildRequest(props, actions, updated, children, updatedPropsFromParent, files) {
		const splitUrl = this.url.split("?");
		let [url] = splitUrl;
		const [, queryString] = splitUrl;
		const params = new URLSearchParams(queryString || "");
		const fetchOptions = {};
		fetchOptions.credentials = this.credentials;
		fetchOptions.headers = {
			Accept: "application/vnd.live-component+html",
			"X-Requested-With": "XMLHttpRequest",
			"X-Live-Url": window.location.pathname + window.location.search
		};
		const totalFiles = Object.entries(files).reduce((total, current) => total + current.length, 0);
		const hasFingerprints = Object.keys(children).length > 0;
		if (actions.length === 0 && totalFiles === 0 && this.method === "get" && this.willDataFitInUrl(JSON.stringify(props), JSON.stringify(updated), params, JSON.stringify(children), JSON.stringify(updatedPropsFromParent))) {
			params.set("props", JSON.stringify(props));
			params.set("updated", JSON.stringify(updated));
			if (Object.keys(updatedPropsFromParent).length > 0) params.set("propsFromParent", JSON.stringify(updatedPropsFromParent));
			if (hasFingerprints) params.set("children", JSON.stringify(children));
			fetchOptions.method = "GET";
		} else {
			fetchOptions.method = "POST";
			const requestData = {
				props,
				updated
			};
			if (Object.keys(updatedPropsFromParent).length > 0) requestData.propsFromParent = updatedPropsFromParent;
			if (hasFingerprints) requestData.children = children;
			if (actions.length > 0) if (actions.length === 1) {
				requestData.args = actions[0].args;
				url += `/${encodeURIComponent(actions[0].name)}`;
			} else {
				url += "/_batch";
				requestData.actions = actions;
			}
			const formData = new FormData();
			formData.append("data", JSON.stringify(requestData));
			for (const [key, value] of Object.entries(files)) {
				const length = value.length;
				for (let i = 0; i < length; ++i) formData.append(key, value[i]);
			}
			fetchOptions.body = formData;
		}
		const paramsString = params.toString();
		return {
			url: `${url}${paramsString.length > 0 ? `?${paramsString}` : ""}`,
			fetchOptions
		};
	}
	willDataFitInUrl(propsJson, updatedJson, params, childrenJson, propsFromParentJson) {
		return (new URLSearchParams(propsJson + updatedJson + childrenJson + propsFromParentJson).toString() + params.toString()).length < 1500;
	}
};
var Backend_default = class {
	constructor(url, method = "post", credentials = "same-origin") {
		this.requestBuilder = new RequestBuilder_default(url, method, credentials);
	}
	makeRequest(props, actions, updated, children, updatedPropsFromParent, files) {
		const { url, fetchOptions } = this.requestBuilder.buildRequest(props, actions, updated, children, updatedPropsFromParent, files);
		return new BackendRequest_default(fetch(url, fetchOptions), actions.map((backendAction) => backendAction.name), Object.keys(updated));
	}
};
var BackendResponse_default = class {
	constructor(response) {
		this.response = response;
	}
	async getBody() {
		if (!this.body) this.body = await this.response.text();
		return this.body;
	}
	getLiveUrl() {
		if (void 0 === this.liveUrl) this.liveUrl = this.response.headers.get("X-Live-Url");
		return this.liveUrl;
	}
};
function getElementAsTagText(element) {
	return element.innerHTML ? element.outerHTML.slice(0, element.outerHTML.indexOf(element.innerHTML)) : element.outerHTML;
}
let componentMapByElement = /* @__PURE__ */ new WeakMap();
let componentMapByComponent = /* @__PURE__ */ new Map();
const registerComponent = (component) => {
	componentMapByElement.set(component.element, component);
	componentMapByComponent.set(component, component.name);
};
const unregisterComponent = (component) => {
	componentMapByElement.delete(component.element);
	componentMapByComponent.delete(component);
};
const getComponent = (element) => new Promise((resolve, reject) => {
	let count = 0;
	const maxCount = 10;
	const interval = setInterval(() => {
		const component = componentMapByElement.get(element);
		if (component) {
			clearInterval(interval);
			resolve(component);
		}
		count++;
		if (count > maxCount) {
			clearInterval(interval);
			reject(/* @__PURE__ */ new Error(`Component not found for element ${getElementAsTagText(element)}`));
		}
	}, 5);
});
const findComponents = (currentComponent, onlyParents, onlyMatchName) => {
	const components = [];
	componentMapByComponent.forEach((componentName, component) => {
		if (onlyParents && (currentComponent === component || !component.element.contains(currentComponent.element))) return;
		if (onlyMatchName && componentName !== onlyMatchName) return;
		components.push(component);
	});
	return components;
};
const findChildren = (currentComponent) => {
	const children = [];
	componentMapByComponent.forEach((componentName, component) => {
		if (currentComponent === component) return;
		if (!currentComponent.element.contains(component.element)) return;
		let foundChildComponent = false;
		componentMapByComponent.forEach((childComponentName, childComponent) => {
			if (foundChildComponent) return;
			if (childComponent === component) return;
			if (childComponent.element.contains(component.element)) foundChildComponent = true;
		});
		children.push(component);
	});
	return children;
};
const findParent = (currentComponent) => {
	let parentElement = currentComponent.element.parentElement;
	while (parentElement) {
		const component = componentMapByElement.get(parentElement);
		if (component) return component;
		parentElement = parentElement.parentElement;
	}
	return null;
};
function parseDirectives(content) {
	const directives = [];
	if (!content) return directives;
	let currentActionName = "";
	let currentArgumentValue = "";
	let currentArguments = [];
	let currentModifiers = [];
	let state = "action";
	const getLastActionName = () => {
		if (currentActionName) return currentActionName;
		if (directives.length === 0) throw new Error("Could not find any directives");
		return directives[directives.length - 1].action;
	};
	const pushInstruction = () => {
		directives.push({
			action: currentActionName,
			args: currentArguments,
			modifiers: currentModifiers,
			getString: () => {
				return content;
			}
		});
		currentActionName = "";
		currentArgumentValue = "";
		currentArguments = [];
		currentModifiers = [];
		state = "action";
	};
	const pushArgument = () => {
		currentArguments.push(currentArgumentValue.trim());
		currentArgumentValue = "";
	};
	const pushModifier = () => {
		if (currentArguments.length > 1) throw new Error(`The modifier "${currentActionName}()" does not support multiple arguments.`);
		currentModifiers.push({
			name: currentActionName,
			value: currentArguments.length > 0 ? currentArguments[0] : null
		});
		currentActionName = "";
		currentArguments = [];
		state = "action";
	};
	for (let i = 0; i < content.length; i++) {
		const char = content[i];
		switch (state) {
			case "action":
				if (char === "(") {
					state = "arguments";
					break;
				}
				if (char === " ") {
					if (currentActionName) pushInstruction();
					break;
				}
				if (char === "|") {
					pushModifier();
					break;
				}
				currentActionName += char;
				break;
			case "arguments":
				if (char === ")") {
					pushArgument();
					state = "after_arguments";
					break;
				}
				if (char === ",") {
					pushArgument();
					break;
				}
				currentArgumentValue += char;
				break;
			case "after_arguments":
				if (char === "|") {
					pushModifier();
					break;
				}
				if (char !== " ") throw new Error(`Missing space after ${getLastActionName()}()`);
				pushInstruction();
				break;
		}
	}
	switch (state) {
		case "action":
		case "after_arguments":
			if (currentActionName) pushInstruction();
			break;
		default: throw new Error(`Did you forget to add a closing ")" after "${currentActionName}"?`);
	}
	return directives;
}
function combineSpacedArray(parts) {
	const finalParts = [];
	parts.forEach((part) => {
		finalParts.push(...trimAll(part).split(" "));
	});
	return finalParts;
}
function trimAll(str) {
	return str.replace(/[\s]+/g, " ").trim();
}
function normalizeModelName(model) {
	return model.replace(/\[]$/, "").split("[").map((s) => s.replace("]", "")).join(".");
}
function getValueFromElement(element, valueStore) {
	if (element instanceof HTMLInputElement) {
		if (element.type === "checkbox") {
			const modelNameData = getModelDirectiveFromElement(element, false);
			if (modelNameData !== null) {
				const modelValue = valueStore.get(modelNameData.action);
				if (Array.isArray(modelValue)) return getMultipleCheckboxValue(element, modelValue);
				if (Object(modelValue) === modelValue) return getMultipleCheckboxValue(element, Object.values(modelValue));
			}
			if (element.hasAttribute("value")) return element.checked ? element.getAttribute("value") : null;
			return element.checked;
		}
		return inputValue(element);
	}
	if (element instanceof HTMLSelectElement) {
		if (element.multiple) return Array.from(element.selectedOptions).map((el) => el.value);
		return element.value;
	}
	if (element.hasAttribute("data-value")) return element.dataset.value;
	if ("value" in element) return element.value;
	if (element.hasAttribute("value")) return element.getAttribute("value");
	return null;
}
function setValueOnElement(element, value) {
	if (element instanceof HTMLInputElement) {
		if (element.type === "file") return;
		if (element.type === "radio") {
			element.checked = element.value == value;
			return;
		}
		if (element.type === "checkbox") {
			if (Array.isArray(value)) element.checked = value.some((val) => val == element.value);
			else if (element.hasAttribute("value")) element.checked = element.value == value;
			else element.checked = value;
			return;
		}
	}
	if (element instanceof HTMLSelectElement) {
		const arrayWrappedValue = [].concat(value).map((value) => {
			return `${value}`;
		});
		Array.from(element.options).forEach((option) => {
			option.selected = arrayWrappedValue.includes(option.value);
		});
		return;
	}
	value = value === void 0 ? "" : value;
	element.value = value;
}
function getAllModelDirectiveFromElements(element) {
	if (!element.dataset.model) return [];
	const directives = parseDirectives(element.dataset.model);
	directives.forEach((directive) => {
		if (directive.args.length > 0) throw new Error(`The data-model="${element.dataset.model}" format is invalid: it does not support passing arguments to the model.`);
		directive.action = normalizeModelName(directive.action);
	});
	return directives;
}
function getModelDirectiveFromElement(element, throwOnMissing = true) {
	const dataModelDirectives = getAllModelDirectiveFromElements(element);
	if (dataModelDirectives.length > 0) return dataModelDirectives[0];
	if (element.getAttribute("name")) {
		const formElement = element.closest("form");
		if (formElement && "model" in formElement.dataset) {
			const directive = parseDirectives(formElement.dataset.model || "*")[0];
			if (directive.args.length > 0) throw new Error(`The data-model="${formElement.dataset.model}" format is invalid: it does not support passing arguments to the model.`);
			directive.action = normalizeModelName(element.getAttribute("name"));
			return directive;
		}
	}
	if (!throwOnMissing) return null;
	throw new Error(`Cannot determine the model name for "${getElementAsTagText(element)}": the element must either have a "data-model" (or "name" attribute living inside a <form data-model="*">).`);
}
function elementBelongsToThisComponent(element, component) {
	if (component.element === element) return true;
	if (!component.element.contains(element)) return false;
	return element.closest("[data-controller~=\"live\"]") === component.element;
}
function cloneHTMLElement(element) {
	const newElement = element.cloneNode(true);
	if (!(newElement instanceof HTMLElement)) throw new Error("Could not clone element");
	return newElement;
}
function htmlToElement(html) {
	const template = document.createElement("template");
	html = html.trim();
	template.innerHTML = html;
	if (template.content.childElementCount > 1) throw new Error(`Component HTML contains ${template.content.childElementCount} elements, but only 1 root element is allowed.`);
	const child = template.content.firstElementChild;
	if (!child) throw new Error("Child not found");
	if (!(child instanceof HTMLElement)) throw new Error(`Created element is not an HTMLElement: ${html.trim()}`);
	return child;
}
const getMultipleCheckboxValue = (element, currentValues) => {
	const finalValues = [...currentValues];
	const value = inputValue(element);
	const index = currentValues.indexOf(value);
	if (element.checked) {
		if (index === -1) finalValues.push(value);
		return finalValues;
	}
	if (index > -1) finalValues.splice(index, 1);
	return finalValues;
};
const inputValue = (element) => element.dataset.value ? element.dataset.value : element.value;
function isTextualInputElement(el) {
	return el instanceof HTMLInputElement && [
		"text",
		"email",
		"password",
		"search",
		"tel",
		"url"
	].includes(el.type);
}
function isTextareaElement(el) {
	return el instanceof HTMLTextAreaElement;
}
function isNumericalInputElement(element) {
	return element instanceof HTMLInputElement && ["number", "range"].includes(element.type);
}
var HookManager_default = class {
	constructor() {
		this.hooks = /* @__PURE__ */ new Map();
	}
	register(hookName, callback) {
		const hooks = this.hooks.get(hookName) || [];
		hooks.push(callback);
		this.hooks.set(hookName, hooks);
	}
	unregister(hookName, callback) {
		const hooks = this.hooks.get(hookName) || [];
		const index = hooks.indexOf(callback);
		if (index === -1) return;
		hooks.splice(index, 1);
		this.hooks.set(hookName, hooks);
	}
	triggerHook(hookName, ...args) {
		(this.hooks.get(hookName) || []).forEach((callback) => {
			callback(...args);
		});
	}
};
var Idiomorph = (function() {
	"use strict";
	let EMPTY_SET = /* @__PURE__ */ new Set();
	let defaults = {
		morphStyle: "outerHTML",
		callbacks: {
			beforeNodeAdded: noOp,
			afterNodeAdded: noOp,
			beforeNodeMorphed: noOp,
			afterNodeMorphed: noOp,
			beforeNodeRemoved: noOp,
			afterNodeRemoved: noOp,
			beforeAttributeUpdated: noOp
		},
		head: {
			style: "merge",
			shouldPreserve: function(elt) {
				return elt.getAttribute("im-preserve") === "true";
			},
			shouldReAppend: function(elt) {
				return elt.getAttribute("im-re-append") === "true";
			},
			shouldRemove: noOp,
			afterHeadMorphed: noOp
		}
	};
	function morph(oldNode, newContent, config = {}) {
		if (oldNode instanceof Document) oldNode = oldNode.documentElement;
		if (typeof newContent === "string") newContent = parseContent(newContent);
		let normalizedContent = normalizeContent(newContent);
		let ctx = createMorphContext(oldNode, normalizedContent, config);
		return morphNormalizedContent(oldNode, normalizedContent, ctx);
	}
	function morphNormalizedContent(oldNode, normalizedNewContent, ctx) {
		if (ctx.head.block) {
			let oldHead = oldNode.querySelector("head");
			let newHead = normalizedNewContent.querySelector("head");
			if (oldHead && newHead) {
				let promises = handleHeadElement(newHead, oldHead, ctx);
				Promise.all(promises).then(function() {
					morphNormalizedContent(oldNode, normalizedNewContent, Object.assign(ctx, { head: {
						block: false,
						ignore: true
					} }));
				});
				return;
			}
		}
		if (ctx.morphStyle === "innerHTML") {
			morphChildren(normalizedNewContent, oldNode, ctx);
			return oldNode.children;
		} else if (ctx.morphStyle === "outerHTML" || ctx.morphStyle == null) {
			let bestMatch = findBestNodeMatch(normalizedNewContent, oldNode, ctx);
			let previousSibling = bestMatch?.previousSibling;
			let nextSibling = bestMatch?.nextSibling;
			let morphedNode = morphOldNodeTo(oldNode, bestMatch, ctx);
			if (bestMatch) return insertSiblings(previousSibling, morphedNode, nextSibling);
			else return [];
		} else throw "Do not understand how to morph style " + ctx.morphStyle;
	}
	function ignoreValueOfActiveElement(possibleActiveElement, ctx) {
		return ctx.ignoreActiveValue && possibleActiveElement === document.activeElement;
	}
	function morphOldNodeTo(oldNode, newContent, ctx) {
		if (ctx.ignoreActive && oldNode === document.activeElement) {} else if (newContent == null) {
			if (ctx.callbacks.beforeNodeRemoved(oldNode) === false) return oldNode;
			oldNode.remove();
			ctx.callbacks.afterNodeRemoved(oldNode);
			return null;
		} else if (!isSoftMatch(oldNode, newContent)) {
			if (ctx.callbacks.beforeNodeRemoved(oldNode) === false) return oldNode;
			if (ctx.callbacks.beforeNodeAdded(newContent) === false) return oldNode;
			oldNode.parentElement.replaceChild(newContent, oldNode);
			ctx.callbacks.afterNodeAdded(newContent);
			ctx.callbacks.afterNodeRemoved(oldNode);
			return newContent;
		} else {
			if (ctx.callbacks.beforeNodeMorphed(oldNode, newContent) === false) return oldNode;
			if (oldNode instanceof HTMLHeadElement && ctx.head.ignore) {} else if (oldNode instanceof HTMLHeadElement && ctx.head.style !== "morph") handleHeadElement(newContent, oldNode, ctx);
			else {
				syncNodeFrom(newContent, oldNode, ctx);
				if (!ignoreValueOfActiveElement(oldNode, ctx)) morphChildren(newContent, oldNode, ctx);
			}
			ctx.callbacks.afterNodeMorphed(oldNode, newContent);
			return oldNode;
		}
	}
	function morphChildren(newParent, oldParent, ctx) {
		let nextNewChild = newParent.firstChild;
		let insertionPoint = oldParent.firstChild;
		let newChild;
		while (nextNewChild) {
			newChild = nextNewChild;
			nextNewChild = newChild.nextSibling;
			if (insertionPoint == null) {
				if (ctx.callbacks.beforeNodeAdded(newChild) === false) return;
				oldParent.appendChild(newChild);
				ctx.callbacks.afterNodeAdded(newChild);
				removeIdsFromConsideration(ctx, newChild);
				continue;
			}
			if (isIdSetMatch(newChild, insertionPoint, ctx)) {
				morphOldNodeTo(insertionPoint, newChild, ctx);
				insertionPoint = insertionPoint.nextSibling;
				removeIdsFromConsideration(ctx, newChild);
				continue;
			}
			let idSetMatch = findIdSetMatch(newParent, oldParent, newChild, insertionPoint, ctx);
			if (idSetMatch) {
				insertionPoint = removeNodesBetween(insertionPoint, idSetMatch, ctx);
				morphOldNodeTo(idSetMatch, newChild, ctx);
				removeIdsFromConsideration(ctx, newChild);
				continue;
			}
			let softMatch = findSoftMatch(newParent, oldParent, newChild, insertionPoint, ctx);
			if (softMatch) {
				insertionPoint = removeNodesBetween(insertionPoint, softMatch, ctx);
				morphOldNodeTo(softMatch, newChild, ctx);
				removeIdsFromConsideration(ctx, newChild);
				continue;
			}
			if (ctx.callbacks.beforeNodeAdded(newChild) === false) return;
			oldParent.insertBefore(newChild, insertionPoint);
			ctx.callbacks.afterNodeAdded(newChild);
			removeIdsFromConsideration(ctx, newChild);
		}
		while (insertionPoint !== null) {
			let tempNode = insertionPoint;
			insertionPoint = insertionPoint.nextSibling;
			removeNode(tempNode, ctx);
		}
	}
	function ignoreAttribute(attr, to, updateType, ctx) {
		if (attr === "value" && ctx.ignoreActiveValue && to === document.activeElement) return true;
		return ctx.callbacks.beforeAttributeUpdated(attr, to, updateType) === false;
	}
	function syncNodeFrom(from, to, ctx) {
		let type = from.nodeType;
		if (type === 1) {
			const fromAttributes = from.attributes;
			const toAttributes = to.attributes;
			for (const fromAttribute of fromAttributes) {
				if (ignoreAttribute(fromAttribute.name, to, "update", ctx)) continue;
				if (to.getAttribute(fromAttribute.name) !== fromAttribute.value) to.setAttribute(fromAttribute.name, fromAttribute.value);
			}
			for (let i = toAttributes.length - 1; 0 <= i; i--) {
				const toAttribute = toAttributes[i];
				if (ignoreAttribute(toAttribute.name, to, "remove", ctx)) continue;
				if (!from.hasAttribute(toAttribute.name)) to.removeAttribute(toAttribute.name);
			}
		}
		if (type === 8 || type === 3) {
			if (to.nodeValue !== from.nodeValue) to.nodeValue = from.nodeValue;
		}
		if (!ignoreValueOfActiveElement(to, ctx)) syncInputValue(from, to, ctx);
	}
	function syncBooleanAttribute(from, to, attributeName, ctx) {
		if (from[attributeName] !== to[attributeName]) {
			let ignoreUpdate = ignoreAttribute(attributeName, to, "update", ctx);
			if (!ignoreUpdate) to[attributeName] = from[attributeName];
			if (from[attributeName]) {
				if (!ignoreUpdate) to.setAttribute(attributeName, from[attributeName]);
			} else if (!ignoreAttribute(attributeName, to, "remove", ctx)) to.removeAttribute(attributeName);
		}
	}
	function syncInputValue(from, to, ctx) {
		if (from instanceof HTMLInputElement && to instanceof HTMLInputElement && from.type !== "file") {
			let fromValue = from.value;
			let toValue = to.value;
			syncBooleanAttribute(from, to, "checked", ctx);
			syncBooleanAttribute(from, to, "disabled", ctx);
			if (!from.hasAttribute("value")) {
				if (!ignoreAttribute("value", to, "remove", ctx)) {
					to.value = "";
					to.removeAttribute("value");
				}
			} else if (fromValue !== toValue) {
				if (!ignoreAttribute("value", to, "update", ctx)) {
					to.setAttribute("value", fromValue);
					to.value = fromValue;
				}
			}
		} else if (from instanceof HTMLOptionElement) syncBooleanAttribute(from, to, "selected", ctx);
		else if (from instanceof HTMLTextAreaElement && to instanceof HTMLTextAreaElement) {
			let fromValue = from.value;
			let toValue = to.value;
			if (ignoreAttribute("value", to, "update", ctx)) return;
			if (fromValue !== toValue) to.value = fromValue;
			if (to.firstChild && to.firstChild.nodeValue !== fromValue) to.firstChild.nodeValue = fromValue;
		}
	}
	function handleHeadElement(newHeadTag, currentHead, ctx) {
		let added = [];
		let removed = [];
		let preserved = [];
		let nodesToAppend = [];
		let headMergeStyle = ctx.head.style;
		let srcToNewHeadNodes = /* @__PURE__ */ new Map();
		for (const newHeadChild of newHeadTag.children) srcToNewHeadNodes.set(newHeadChild.outerHTML, newHeadChild);
		for (const currentHeadElt of currentHead.children) {
			let inNewContent = srcToNewHeadNodes.has(currentHeadElt.outerHTML);
			let isReAppended = ctx.head.shouldReAppend(currentHeadElt);
			let isPreserved = ctx.head.shouldPreserve(currentHeadElt);
			if (inNewContent || isPreserved) if (isReAppended) removed.push(currentHeadElt);
			else {
				srcToNewHeadNodes.delete(currentHeadElt.outerHTML);
				preserved.push(currentHeadElt);
			}
			else if (headMergeStyle === "append") {
				if (isReAppended) {
					removed.push(currentHeadElt);
					nodesToAppend.push(currentHeadElt);
				}
			} else if (ctx.head.shouldRemove(currentHeadElt) !== false) removed.push(currentHeadElt);
		}
		nodesToAppend.push(...srcToNewHeadNodes.values());
		log("to append: ", nodesToAppend);
		let promises = [];
		for (const newNode of nodesToAppend) {
			log("adding: ", newNode);
			let newElt = document.createRange().createContextualFragment(newNode.outerHTML).firstChild;
			log(newElt);
			if (ctx.callbacks.beforeNodeAdded(newElt) !== false) {
				if (newElt.href || newElt.src) {
					let resolve = null;
					let promise = new Promise(function(_resolve) {
						resolve = _resolve;
					});
					newElt.addEventListener("load", function() {
						resolve();
					});
					promises.push(promise);
				}
				currentHead.appendChild(newElt);
				ctx.callbacks.afterNodeAdded(newElt);
				added.push(newElt);
			}
		}
		for (const removedElement of removed) if (ctx.callbacks.beforeNodeRemoved(removedElement) !== false) {
			currentHead.removeChild(removedElement);
			ctx.callbacks.afterNodeRemoved(removedElement);
		}
		ctx.head.afterHeadMorphed(currentHead, {
			added,
			kept: preserved,
			removed
		});
		return promises;
	}
	function log() {}
	function noOp() {}
	function mergeDefaults(config) {
		let finalConfig = {};
		Object.assign(finalConfig, defaults);
		Object.assign(finalConfig, config);
		finalConfig.callbacks = {};
		Object.assign(finalConfig.callbacks, defaults.callbacks);
		Object.assign(finalConfig.callbacks, config.callbacks);
		finalConfig.head = {};
		Object.assign(finalConfig.head, defaults.head);
		Object.assign(finalConfig.head, config.head);
		return finalConfig;
	}
	function createMorphContext(oldNode, newContent, config) {
		config = mergeDefaults(config);
		return {
			target: oldNode,
			newContent,
			config,
			morphStyle: config.morphStyle,
			ignoreActive: config.ignoreActive,
			ignoreActiveValue: config.ignoreActiveValue,
			idMap: createIdMap(oldNode, newContent),
			deadIds: /* @__PURE__ */ new Set(),
			callbacks: config.callbacks,
			head: config.head
		};
	}
	function isIdSetMatch(node1, node2, ctx) {
		if (node1 == null || node2 == null) return false;
		if (node1.nodeType === node2.nodeType && node1.tagName === node2.tagName) if (node1.id !== "" && node1.id === node2.id) return true;
		else return getIdIntersectionCount(ctx, node1, node2) > 0;
		return false;
	}
	function isSoftMatch(node1, node2) {
		if (node1 == null || node2 == null) return false;
		return node1.nodeType === node2.nodeType && node1.tagName === node2.tagName;
	}
	function removeNodesBetween(startInclusive, endExclusive, ctx) {
		while (startInclusive !== endExclusive) {
			let tempNode = startInclusive;
			startInclusive = startInclusive.nextSibling;
			removeNode(tempNode, ctx);
		}
		removeIdsFromConsideration(ctx, endExclusive);
		return endExclusive.nextSibling;
	}
	function findIdSetMatch(newContent, oldParent, newChild, insertionPoint, ctx) {
		let newChildPotentialIdCount = getIdIntersectionCount(ctx, newChild, oldParent);
		let potentialMatch = null;
		if (newChildPotentialIdCount > 0) {
			let potentialMatch = insertionPoint;
			let otherMatchCount = 0;
			while (potentialMatch != null) {
				if (isIdSetMatch(newChild, potentialMatch, ctx)) return potentialMatch;
				otherMatchCount += getIdIntersectionCount(ctx, potentialMatch, newContent);
				if (otherMatchCount > newChildPotentialIdCount) return null;
				potentialMatch = potentialMatch.nextSibling;
			}
		}
		return potentialMatch;
	}
	function findSoftMatch(newContent, oldParent, newChild, insertionPoint, ctx) {
		let potentialSoftMatch = insertionPoint;
		let nextSibling = newChild.nextSibling;
		let siblingSoftMatchCount = 0;
		while (potentialSoftMatch != null) {
			if (getIdIntersectionCount(ctx, potentialSoftMatch, newContent) > 0) return null;
			if (isSoftMatch(newChild, potentialSoftMatch)) return potentialSoftMatch;
			if (isSoftMatch(nextSibling, potentialSoftMatch)) {
				siblingSoftMatchCount++;
				nextSibling = nextSibling.nextSibling;
				if (siblingSoftMatchCount >= 2) return null;
			}
			potentialSoftMatch = potentialSoftMatch.nextSibling;
		}
		return potentialSoftMatch;
	}
	function parseContent(newContent) {
		let parser = new DOMParser();
		let contentWithSvgsRemoved = newContent.replace(/<svg(\s[^>]*>|>)([\s\S]*?)<\/svg>/gim, "");
		if (contentWithSvgsRemoved.match(/<\/html>/) || contentWithSvgsRemoved.match(/<\/head>/) || contentWithSvgsRemoved.match(/<\/body>/)) {
			let content = parser.parseFromString(newContent, "text/html");
			if (contentWithSvgsRemoved.match(/<\/html>/)) {
				content.generatedByIdiomorph = true;
				return content;
			} else {
				let htmlElement = content.firstChild;
				if (htmlElement) {
					htmlElement.generatedByIdiomorph = true;
					return htmlElement;
				} else return null;
			}
		} else {
			let content = parser.parseFromString("<body><template>" + newContent + "</template></body>", "text/html").body.querySelector("template").content;
			content.generatedByIdiomorph = true;
			return content;
		}
	}
	function normalizeContent(newContent) {
		if (newContent == null) return document.createElement("div");
		else if (newContent.generatedByIdiomorph) return newContent;
		else if (newContent instanceof Node) {
			const dummyParent = document.createElement("div");
			dummyParent.append(newContent);
			return dummyParent;
		} else {
			const dummyParent = document.createElement("div");
			for (const elt of [...newContent]) dummyParent.append(elt);
			return dummyParent;
		}
	}
	function insertSiblings(previousSibling, morphedNode, nextSibling) {
		let stack = [];
		let added = [];
		while (previousSibling != null) {
			stack.push(previousSibling);
			previousSibling = previousSibling.previousSibling;
		}
		while (stack.length > 0) {
			let node = stack.pop();
			added.push(node);
			morphedNode.parentElement.insertBefore(node, morphedNode);
		}
		added.push(morphedNode);
		while (nextSibling != null) {
			stack.push(nextSibling);
			added.push(nextSibling);
			nextSibling = nextSibling.nextSibling;
		}
		while (stack.length > 0) morphedNode.parentElement.insertBefore(stack.pop(), morphedNode.nextSibling);
		return added;
	}
	function findBestNodeMatch(newContent, oldNode, ctx) {
		let currentElement;
		currentElement = newContent.firstChild;
		let bestElement = currentElement;
		let score = 0;
		while (currentElement) {
			let newScore = scoreElement(currentElement, oldNode, ctx);
			if (newScore > score) {
				bestElement = currentElement;
				score = newScore;
			}
			currentElement = currentElement.nextSibling;
		}
		return bestElement;
	}
	function scoreElement(node1, node2, ctx) {
		if (isSoftMatch(node1, node2)) return .5 + getIdIntersectionCount(ctx, node1, node2);
		return 0;
	}
	function removeNode(tempNode, ctx) {
		removeIdsFromConsideration(ctx, tempNode);
		if (ctx.callbacks.beforeNodeRemoved(tempNode) === false) return;
		tempNode.remove();
		ctx.callbacks.afterNodeRemoved(tempNode);
	}
	function isIdInConsideration(ctx, id) {
		return !ctx.deadIds.has(id);
	}
	function idIsWithinNode(ctx, id, targetNode) {
		return (ctx.idMap.get(targetNode) || EMPTY_SET).has(id);
	}
	function removeIdsFromConsideration(ctx, node) {
		let idSet = ctx.idMap.get(node) || EMPTY_SET;
		for (const id of idSet) ctx.deadIds.add(id);
	}
	function getIdIntersectionCount(ctx, node1, node2) {
		let sourceSet = ctx.idMap.get(node1) || EMPTY_SET;
		let matchCount = 0;
		for (const id of sourceSet) if (isIdInConsideration(ctx, id) && idIsWithinNode(ctx, id, node2)) ++matchCount;
		return matchCount;
	}
	function populateIdMapForNode(node, idMap) {
		let nodeParent = node.parentElement;
		let idElements = node.querySelectorAll("[id]");
		for (const elt of idElements) {
			let current = elt;
			while (current !== nodeParent && current != null) {
				let idSet = idMap.get(current);
				if (idSet == null) {
					idSet = /* @__PURE__ */ new Set();
					idMap.set(current, idSet);
				}
				idSet.add(elt.id);
				current = current.parentElement;
			}
		}
	}
	function createIdMap(oldContent, newContent) {
		let idMap = /* @__PURE__ */ new Map();
		populateIdMapForNode(oldContent, idMap);
		populateIdMapForNode(newContent, idMap);
		return idMap;
	}
	return {
		morph,
		defaults
	};
})();
function normalizeAttributesForComparison(element) {
	if (!(element instanceof HTMLInputElement && element.type === "file")) {
		if ("value" in element) element.setAttribute("value", element.value);
		else if (element.hasAttribute("value")) element.setAttribute("value", "");
	}
	Array.from(element.children).forEach((child) => {
		normalizeAttributesForComparison(child);
	});
}
const syncAttributes = (fromEl, toEl) => {
	for (let i = 0; i < fromEl.attributes.length; i++) {
		const attr = fromEl.attributes[i];
		toEl.setAttribute(attr.name, attr.value);
	}
};
function executeMorphdom(rootFromElement, rootToElement, modifiedFieldElements, getElementValue, externalMutationTracker) {
	const originalElementIdsToSwapAfter = [];
	const originalElementsToPreserve = /* @__PURE__ */ new Map();
	const markElementAsNeedingPostMorphSwap = (id, replaceWithClone) => {
		const oldElement = originalElementsToPreserve.get(id);
		if (!(oldElement instanceof HTMLElement)) throw new Error(`Original element with id ${id} not found`);
		originalElementIdsToSwapAfter.push(id);
		if (!replaceWithClone) return null;
		const clonedOldElement = cloneHTMLElement(oldElement);
		oldElement.replaceWith(clonedOldElement);
		return clonedOldElement;
	};
	rootToElement.querySelectorAll("[data-live-preserve]").forEach((newElement) => {
		const id = newElement.id;
		if (!id) throw new Error("The data-live-preserve attribute requires an id attribute to be set on the element");
		const oldElement = rootFromElement.querySelector(`#${id}`);
		if (!(oldElement instanceof HTMLElement)) throw new Error(`The element with id "${id}" was not found in the original HTML`);
		newElement.removeAttribute("data-live-preserve");
		originalElementsToPreserve.set(id, oldElement);
		syncAttributes(newElement, oldElement);
	});
	Idiomorph.morph(rootFromElement, rootToElement, { callbacks: {
		beforeNodeMorphed: (fromEl, toEl) => {
			if (!(fromEl instanceof Element) || !(toEl instanceof Element)) return true;
			if (fromEl === rootFromElement) return true;
			if (fromEl.id && originalElementsToPreserve.has(fromEl.id)) {
				if (fromEl.id === toEl.id) return false;
				const clonedFromEl = markElementAsNeedingPostMorphSwap(fromEl.id, true);
				if (!clonedFromEl) throw new Error("missing clone");
				Idiomorph.morph(clonedFromEl, toEl);
				return false;
			}
			if (fromEl instanceof HTMLElement && toEl instanceof HTMLElement) {
				if (typeof fromEl.__x !== "undefined") {
					if (!window.Alpine) throw new Error("Unable to access Alpine.js though the global window.Alpine variable. Please make sure Alpine.js is loaded before Symfony UX LiveComponent.");
					if (typeof window.Alpine.morph !== "function") throw new Error("Unable to access Alpine.js morph function. Please make sure the Alpine.js Morph plugin is installed and loaded, see https://alpinejs.dev/plugins/morph for more information.");
					window.Alpine.morph(fromEl.__x, toEl);
				}
				if (externalMutationTracker.wasElementAdded(fromEl)) {
					fromEl.insertAdjacentElement("afterend", toEl);
					return false;
				}
				if (modifiedFieldElements.includes(fromEl)) setValueOnElement(toEl, getElementValue(fromEl));
				if (fromEl === document.activeElement && fromEl !== document.body && null !== getModelDirectiveFromElement(fromEl, false)) setValueOnElement(toEl, getElementValue(fromEl));
				const elementChanges = externalMutationTracker.getChangedElement(fromEl);
				if (elementChanges) elementChanges.applyToElement(toEl);
				if (fromEl.nodeName.toUpperCase() !== "OPTION" && fromEl.isEqualNode(toEl)) {
					const normalizedFromEl = cloneHTMLElement(fromEl);
					normalizeAttributesForComparison(normalizedFromEl);
					const normalizedToEl = cloneHTMLElement(toEl);
					normalizeAttributesForComparison(normalizedToEl);
					if (normalizedFromEl.isEqualNode(normalizedToEl)) return false;
				}
			}
			if (fromEl.hasAttribute("data-skip-morph") || fromEl.id && fromEl.id !== toEl.id) {
				fromEl.innerHTML = toEl.innerHTML;
				return true;
			}
			if (fromEl.parentElement?.hasAttribute("data-skip-morph")) return false;
			return !fromEl.hasAttribute("data-live-ignore");
		},
		beforeNodeRemoved(node) {
			if (!(node instanceof HTMLElement)) return true;
			if (node.id && originalElementsToPreserve.has(node.id)) {
				markElementAsNeedingPostMorphSwap(node.id, false);
				return true;
			}
			if (externalMutationTracker.wasElementAdded(node)) return false;
			return !node.hasAttribute("data-live-ignore");
		}
	} });
	originalElementIdsToSwapAfter.forEach((id) => {
		const newElement = rootFromElement.querySelector(`#${id}`);
		const originalElement = originalElementsToPreserve.get(id);
		if (!(newElement instanceof HTMLElement) || !(originalElement instanceof HTMLElement)) throw new Error("Missing elements.");
		newElement.replaceWith(originalElement);
	});
}
var ChangingItemsTracker_default = class {
	constructor() {
		this.changedItems = /* @__PURE__ */ new Map();
		this.removedItems = /* @__PURE__ */ new Map();
	}
	setItem(itemName, newValue, previousValue) {
		if (this.removedItems.has(itemName)) {
			const removedRecord = this.removedItems.get(itemName);
			this.removedItems.delete(itemName);
			if (removedRecord.original === newValue) return;
		}
		if (this.changedItems.has(itemName)) {
			const originalRecord = this.changedItems.get(itemName);
			if (originalRecord.original === newValue) {
				this.changedItems.delete(itemName);
				return;
			}
			this.changedItems.set(itemName, {
				original: originalRecord.original,
				new: newValue
			});
			return;
		}
		this.changedItems.set(itemName, {
			original: previousValue,
			new: newValue
		});
	}
	removeItem(itemName, currentValue) {
		let trueOriginalValue = currentValue;
		if (this.changedItems.has(itemName)) {
			trueOriginalValue = this.changedItems.get(itemName).original;
			this.changedItems.delete(itemName);
			if (trueOriginalValue === null) return;
		}
		if (!this.removedItems.has(itemName)) this.removedItems.set(itemName, { original: trueOriginalValue });
	}
	getChangedItems() {
		return Array.from(this.changedItems, ([name, { new: value }]) => ({
			name,
			value
		}));
	}
	getRemovedItems() {
		return Array.from(this.removedItems.keys());
	}
	isEmpty() {
		return this.changedItems.size === 0 && this.removedItems.size === 0;
	}
};
var ElementChanges = class {
	constructor() {
		this.addedClasses = /* @__PURE__ */ new Set();
		this.removedClasses = /* @__PURE__ */ new Set();
		this.styleChanges = new ChangingItemsTracker_default();
		this.attributeChanges = new ChangingItemsTracker_default();
	}
	addClass(className) {
		if (!this.removedClasses.delete(className)) this.addedClasses.add(className);
	}
	removeClass(className) {
		if (!this.addedClasses.delete(className)) this.removedClasses.add(className);
	}
	addStyle(styleName, newValue, originalValue) {
		this.styleChanges.setItem(styleName, newValue, originalValue);
	}
	removeStyle(styleName, originalValue) {
		this.styleChanges.removeItem(styleName, originalValue);
	}
	addAttribute(attributeName, newValue, originalValue) {
		this.attributeChanges.setItem(attributeName, newValue, originalValue);
	}
	removeAttribute(attributeName, originalValue) {
		this.attributeChanges.removeItem(attributeName, originalValue);
	}
	getAddedClasses() {
		return [...this.addedClasses];
	}
	getRemovedClasses() {
		return [...this.removedClasses];
	}
	getChangedStyles() {
		return this.styleChanges.getChangedItems();
	}
	getRemovedStyles() {
		return this.styleChanges.getRemovedItems();
	}
	getChangedAttributes() {
		return this.attributeChanges.getChangedItems();
	}
	getRemovedAttributes() {
		return this.attributeChanges.getRemovedItems();
	}
	applyToElement(element) {
		element.classList.add(...this.addedClasses);
		element.classList.remove(...this.removedClasses);
		this.styleChanges.getChangedItems().forEach((change) => {
			if (/!\s*important/i.test(change.value)) element.style.setProperty(change.name, change.value.replace(/!\s*important/i, "").trim(), "important");
			else element.style.setProperty(change.name, change.value);
		});
		this.styleChanges.getRemovedItems().forEach((styleName) => {
			element.style.removeProperty(styleName);
		});
		this.attributeChanges.getChangedItems().forEach((change) => {
			element.setAttribute(change.name, change.value);
		});
		this.attributeChanges.getRemovedItems().forEach((attributeName) => {
			element.removeAttribute(attributeName);
		});
	}
	isEmpty() {
		return this.addedClasses.size === 0 && this.removedClasses.size === 0 && this.styleChanges.isEmpty() && this.attributeChanges.isEmpty();
	}
};
var ExternalMutationTracker_default = class {
	constructor(element, shouldTrackChangeCallback) {
		this.changedElements = /* @__PURE__ */ new WeakMap();
		this.changedElementsCount = 0;
		this.addedElements = [];
		this.removedElements = [];
		this.isStarted = false;
		this.element = element;
		this.shouldTrackChangeCallback = shouldTrackChangeCallback;
		this.mutationObserver = new MutationObserver(this.onMutations.bind(this));
	}
	start() {
		if (this.isStarted) return;
		this.mutationObserver.observe(this.element, {
			childList: true,
			subtree: true,
			attributes: true,
			attributeOldValue: true
		});
		this.isStarted = true;
	}
	stop() {
		if (this.isStarted) {
			this.mutationObserver.disconnect();
			this.isStarted = false;
		}
	}
	getChangedElement(element) {
		return this.changedElements.has(element) ? this.changedElements.get(element) : null;
	}
	getAddedElements() {
		return this.addedElements;
	}
	wasElementAdded(element) {
		return this.addedElements.includes(element);
	}
	handlePendingChanges() {
		this.onMutations(this.mutationObserver.takeRecords());
	}
	onMutations(mutations) {
		const handledAttributeMutations = /* @__PURE__ */ new WeakMap();
		for (const mutation of mutations) {
			const element = mutation.target;
			if (!this.shouldTrackChangeCallback(element)) continue;
			if (this.isElementAddedByTranslation(element)) continue;
			let isChangeInAddedElement = false;
			for (const addedElement of this.addedElements) if (addedElement.contains(element)) {
				isChangeInAddedElement = true;
				break;
			}
			if (isChangeInAddedElement) continue;
			switch (mutation.type) {
				case "childList":
					this.handleChildListMutation(mutation);
					break;
				case "attributes":
					if (!handledAttributeMutations.has(element)) handledAttributeMutations.set(element, []);
					if (!handledAttributeMutations.get(element).includes(mutation.attributeName)) {
						this.handleAttributeMutation(mutation);
						handledAttributeMutations.set(element, [...handledAttributeMutations.get(element), mutation.attributeName]);
					}
					break;
			}
		}
	}
	handleChildListMutation(mutation) {
		mutation.addedNodes.forEach((node) => {
			if (!(node instanceof Element)) return;
			if (this.removedElements.includes(node)) {
				this.removedElements.splice(this.removedElements.indexOf(node), 1);
				return;
			}
			if (this.isElementAddedByTranslation(node)) return;
			this.addedElements.push(node);
		});
		mutation.removedNodes.forEach((node) => {
			if (!(node instanceof Element)) return;
			if (this.addedElements.includes(node)) {
				this.addedElements.splice(this.addedElements.indexOf(node), 1);
				return;
			}
			this.removedElements.push(node);
		});
	}
	handleAttributeMutation(mutation) {
		const element = mutation.target;
		if (!this.changedElements.has(element)) {
			this.changedElements.set(element, new ElementChanges());
			this.changedElementsCount++;
		}
		const changedElement = this.changedElements.get(element);
		switch (mutation.attributeName) {
			case "class":
				this.handleClassAttributeMutation(mutation, changedElement);
				break;
			case "style":
				this.handleStyleAttributeMutation(mutation, changedElement);
				break;
			default: this.handleGenericAttributeMutation(mutation, changedElement);
		}
		if (changedElement.isEmpty()) {
			this.changedElements.delete(element);
			this.changedElementsCount--;
		}
	}
	handleClassAttributeMutation(mutation, elementChanges) {
		const element = mutation.target;
		const previousValues = (mutation.oldValue || "").match(/(\S+)/gu) || [];
		const newValues = [].slice.call(element.classList);
		const addedValues = newValues.filter((value) => !previousValues.includes(value));
		const removedValues = previousValues.filter((value) => !newValues.includes(value));
		addedValues.forEach((value) => {
			elementChanges.addClass(value);
		});
		removedValues.forEach((value) => {
			elementChanges.removeClass(value);
		});
	}
	handleStyleAttributeMutation(mutation, elementChanges) {
		const element = mutation.target;
		const previousValue = mutation.oldValue || "";
		const previousStyles = this.extractStyles(previousValue);
		const newValue = element.getAttribute("style") || "";
		const newStyles = this.extractStyles(newValue);
		const addedOrChangedStyles = Object.keys(newStyles).filter((key) => previousStyles[key] === void 0 || previousStyles[key] !== newStyles[key]);
		const removedStyles = Object.keys(previousStyles).filter((key) => !newStyles[key]);
		addedOrChangedStyles.forEach((style) => {
			elementChanges.addStyle(style, newStyles[style], previousStyles[style] === void 0 ? null : previousStyles[style]);
		});
		removedStyles.forEach((style) => {
			elementChanges.removeStyle(style, previousStyles[style]);
		});
	}
	handleGenericAttributeMutation(mutation, elementChanges) {
		const attributeName = mutation.attributeName;
		const element = mutation.target;
		let oldValue = mutation.oldValue;
		let newValue = element.getAttribute(attributeName);
		if (oldValue === attributeName) oldValue = "";
		if (newValue === attributeName) newValue = "";
		if (!element.hasAttribute(attributeName)) {
			if (oldValue === null) return;
			elementChanges.removeAttribute(attributeName, mutation.oldValue);
			return;
		}
		if (newValue === oldValue) return;
		elementChanges.addAttribute(attributeName, element.getAttribute(attributeName), mutation.oldValue);
	}
	extractStyles(styles) {
		const styleObject = {};
		styles.split(";").forEach((style) => {
			const parts = style.split(":");
			if (parts.length === 1) return;
			const property = parts[0].trim();
			styleObject[property] = parts.slice(1).join(":").trim();
		});
		return styleObject;
	}
	isElementAddedByTranslation(element) {
		return element.tagName === "FONT" && element.getAttribute("style") === "vertical-align: inherit;";
	}
};
var UnsyncedInputsTracker_default = class {
	constructor(component, modelElementResolver) {
		this.elementEventListeners = [{
			event: "input",
			callback: (event) => this.handleInputEvent(event)
		}];
		this.component = component;
		this.modelElementResolver = modelElementResolver;
		this.unsyncedInputs = new UnsyncedInputContainer();
	}
	activate() {
		this.elementEventListeners.forEach(({ event, callback }) => {
			this.component.element.addEventListener(event, callback);
		});
	}
	deactivate() {
		this.elementEventListeners.forEach(({ event, callback }) => {
			this.component.element.removeEventListener(event, callback);
		});
	}
	markModelAsSynced(modelName) {
		this.unsyncedInputs.markModelAsSynced(modelName);
	}
	handleInputEvent(event) {
		const target = event.target;
		if (!target) return;
		this.updateModelFromElement(target);
	}
	updateModelFromElement(element) {
		if (!elementBelongsToThisComponent(element, this.component)) return;
		if (!(element instanceof HTMLElement)) throw new Error("Could not update model for non HTMLElement");
		const modelName = this.modelElementResolver.getModelName(element);
		this.unsyncedInputs.add(element, modelName);
	}
	getUnsyncedInputs() {
		return this.unsyncedInputs.allUnsyncedInputs();
	}
	getUnsyncedModels() {
		return Array.from(this.unsyncedInputs.getUnsyncedModelNames());
	}
	resetUnsyncedFields() {
		this.unsyncedInputs.resetUnsyncedFields();
	}
};
var UnsyncedInputContainer = class {
	constructor() {
		this.unsyncedNonModelFields = [];
		this.unsyncedModelNames = [];
		this.unsyncedModelFields = /* @__PURE__ */ new Map();
	}
	add(element, modelName = null) {
		if (modelName) {
			this.unsyncedModelFields.set(modelName, element);
			if (!this.unsyncedModelNames.includes(modelName)) this.unsyncedModelNames.push(modelName);
			return;
		}
		this.unsyncedNonModelFields.push(element);
	}
	resetUnsyncedFields() {
		this.unsyncedModelFields.forEach((value, key) => {
			if (!this.unsyncedModelNames.includes(key)) this.unsyncedModelFields.delete(key);
		});
	}
	allUnsyncedInputs() {
		return [...this.unsyncedNonModelFields, ...this.unsyncedModelFields.values()];
	}
	markModelAsSynced(modelName) {
		const index = this.unsyncedModelNames.indexOf(modelName);
		if (index !== -1) this.unsyncedModelNames.splice(index, 1);
	}
	getUnsyncedModelNames() {
		return this.unsyncedModelNames;
	}
};
function getDeepData(data, propertyPath) {
	const { currentLevelData, finalKey } = parseDeepData(data, propertyPath);
	if (currentLevelData === void 0) return;
	return currentLevelData[finalKey];
}
const parseDeepData = (data, propertyPath) => {
	const finalData = JSON.parse(JSON.stringify(data));
	let currentLevelData = finalData;
	const parts = propertyPath.split(".");
	for (let i = 0; i < parts.length - 1; i++) currentLevelData = currentLevelData[parts[i]];
	const finalKey = parts[parts.length - 1];
	return {
		currentLevelData,
		finalData,
		finalKey,
		parts
	};
};
var ValueStore_default = class {
	constructor(props) {
		this.props = {};
		this.dirtyProps = {};
		this.pendingProps = {};
		this.updatedPropsFromParent = {};
		this.props = props;
	}
	get(name) {
		const normalizedName = normalizeModelName(name);
		if (this.dirtyProps[normalizedName] !== void 0) return this.dirtyProps[normalizedName];
		if (this.pendingProps[normalizedName] !== void 0) return this.pendingProps[normalizedName];
		if (this.props[normalizedName] !== void 0) return this.props[normalizedName];
		return getDeepData(this.props, normalizedName);
	}
	has(name) {
		return this.get(name) !== void 0;
	}
	set(name, value) {
		const normalizedName = normalizeModelName(name);
		if (this.get(normalizedName) === value) return false;
		this.dirtyProps[normalizedName] = value;
		return true;
	}
	getOriginalProps() {
		return { ...this.props };
	}
	getDirtyProps() {
		return { ...this.dirtyProps };
	}
	getUpdatedPropsFromParent() {
		return { ...this.updatedPropsFromParent };
	}
	flushDirtyPropsToPending() {
		this.pendingProps = { ...this.dirtyProps };
		this.dirtyProps = {};
	}
	reinitializeAllProps(props) {
		this.props = props;
		this.updatedPropsFromParent = {};
		this.pendingProps = {};
	}
	pushPendingPropsBackToDirty() {
		this.dirtyProps = {
			...this.pendingProps,
			...this.dirtyProps
		};
		this.pendingProps = {};
	}
	storeNewPropsFromParent(props) {
		let changed = false;
		for (const [key, value] of Object.entries(props)) if (this.get(key) !== value) changed = true;
		if (changed) this.updatedPropsFromParent = props;
		return changed;
	}
};
var Component = class {
	constructor(element, name, props, listeners, id, backend, elementDriver) {
		this.fingerprint = "";
		this.defaultDebounce = 150;
		this.backendRequest = null;
		this.pendingActions = [];
		this.pendingFiles = {};
		this.isRequestPending = false;
		this.requestDebounceTimeout = null;
		this.element = element;
		this.name = name;
		this.backend = backend;
		this.elementDriver = elementDriver;
		this.id = id;
		this.listeners = /* @__PURE__ */ new Map();
		listeners.forEach((listener) => {
			if (!this.listeners.has(listener.event)) this.listeners.set(listener.event, []);
			this.listeners.get(listener.event)?.push(listener.action);
		});
		this.valueStore = new ValueStore_default(props);
		this.unsyncedInputsTracker = new UnsyncedInputsTracker_default(this, elementDriver);
		this.hooks = new HookManager_default();
		this.resetPromise();
		this.externalMutationTracker = new ExternalMutationTracker_default(this.element, (element) => elementBelongsToThisComponent(element, this));
		this.externalMutationTracker.start();
	}
	addPlugin(plugin) {
		plugin.attachToComponent(this);
	}
	connect() {
		registerComponent(this);
		this.hooks.triggerHook("connect", this);
		this.unsyncedInputsTracker.activate();
		this.externalMutationTracker.start();
	}
	disconnect() {
		unregisterComponent(this);
		this.hooks.triggerHook("disconnect", this);
		this.clearRequestDebounceTimeout();
		this.unsyncedInputsTracker.deactivate();
		this.externalMutationTracker.stop();
	}
	on(hookName, callback) {
		this.hooks.register(hookName, callback);
	}
	off(hookName, callback) {
		this.hooks.unregister(hookName, callback);
	}
	set(model, value, reRender = false, debounce = false) {
		const promise = this.nextRequestPromise;
		const modelName = normalizeModelName(model);
		if (!this.valueStore.has(modelName)) throw new Error(`Invalid model name "${model}".`);
		const isChanged = this.valueStore.set(modelName, value);
		this.hooks.triggerHook("model:set", model, value, this);
		this.unsyncedInputsTracker.markModelAsSynced(modelName);
		if (reRender && isChanged) this.debouncedStartRequest(debounce);
		return promise;
	}
	getData(model) {
		const modelName = normalizeModelName(model);
		if (!this.valueStore.has(modelName)) throw new Error(`Invalid model "${model}".`);
		return this.valueStore.get(modelName);
	}
	action(name, args = {}, debounce = false) {
		const promise = this.nextRequestPromise;
		this.pendingActions.push({
			name,
			args
		});
		this.debouncedStartRequest(debounce);
		return promise;
	}
	files(key, input) {
		this.pendingFiles[key] = input;
	}
	render() {
		const promise = this.nextRequestPromise;
		this.tryStartingRequest();
		return promise;
	}
	getUnsyncedModels() {
		return this.unsyncedInputsTracker.getUnsyncedModels();
	}
	emit(name, data, onlyMatchingComponentsNamed = null) {
		this.performEmit(name, data, false, onlyMatchingComponentsNamed);
	}
	emitUp(name, data, onlyMatchingComponentsNamed = null) {
		this.performEmit(name, data, true, onlyMatchingComponentsNamed);
	}
	emitSelf(name, data) {
		this.doEmit(name, data);
	}
	performEmit(name, data, emitUp, matchingName) {
		findComponents(this, emitUp, matchingName).forEach((component) => {
			component.doEmit(name, data);
		});
	}
	doEmit(name, data) {
		if (!this.listeners.has(name)) return;
		(this.listeners.get(name) || []).forEach((action) => {
			this.action(action, data, 1);
		});
	}
	isTurboEnabled() {
		return typeof Turbo !== "undefined" && !this.element.closest("[data-turbo=\"false\"]");
	}
	tryStartingRequest() {
		if (!this.backendRequest) {
			this.performRequest();
			return;
		}
		this.isRequestPending = true;
	}
	performRequest() {
		const thisPromiseResolve = this.nextRequestPromiseResolve;
		this.resetPromise();
		this.unsyncedInputsTracker.resetUnsyncedFields();
		const filesToSend = {};
		for (const [key, value] of Object.entries(this.pendingFiles)) if (value.files) filesToSend[key] = value.files;
		const requestConfig = {
			props: this.valueStore.getOriginalProps(),
			actions: this.pendingActions,
			updated: this.valueStore.getDirtyProps(),
			children: {},
			updatedPropsFromParent: this.valueStore.getUpdatedPropsFromParent(),
			files: filesToSend
		};
		this.hooks.triggerHook("request:started", requestConfig);
		this.backendRequest = this.backend.makeRequest(requestConfig.props, requestConfig.actions, requestConfig.updated, requestConfig.children, requestConfig.updatedPropsFromParent, requestConfig.files);
		this.hooks.triggerHook("loading.state:started", this.element, this.backendRequest);
		this.pendingActions = [];
		this.valueStore.flushDirtyPropsToPending();
		this.isRequestPending = false;
		this.backendRequest.promise.then(async (response) => {
			const backendResponse = new BackendResponse_default(response);
			const html = await backendResponse.getBody();
			for (const input of Object.values(this.pendingFiles)) input.value = "";
			const headers = backendResponse.response.headers;
			if (!headers.get("Content-Type")?.includes("application/vnd.live-component+html") && !headers.get("X-Live-Redirect")) {
				const controls = { displayError: true };
				this.valueStore.pushPendingPropsBackToDirty();
				this.hooks.triggerHook("response:error", backendResponse, controls);
				if (controls.displayError) this.renderError(html);
				this.backendRequest = null;
				thisPromiseResolve(backendResponse);
				return response;
			}
			const liveUrl = backendResponse.getLiveUrl();
			if (liveUrl) history.replaceState(history.state, "", new URL(liveUrl + window.location.hash, window.location.origin));
			this.processRerender(html, backendResponse);
			this.backendRequest = null;
			thisPromiseResolve(backendResponse);
			if (this.isRequestPending) {
				this.isRequestPending = false;
				this.performRequest();
			}
			return response;
		});
	}
	processRerender(html, backendResponse) {
		const controls = { shouldRender: true };
		this.hooks.triggerHook("render:started", html, backendResponse, controls);
		if (!controls.shouldRender) return;
		if (backendResponse.response.headers.get("Location")) {
			if (this.isTurboEnabled()) Turbo.visit(backendResponse.response.headers.get("Location"));
			else window.location.href = backendResponse.response.headers.get("Location") || "";
			return;
		}
		this.hooks.triggerHook("loading.state:finished", this.element);
		const modifiedModelValues = {};
		Object.keys(this.valueStore.getDirtyProps()).forEach((modelName) => {
			modifiedModelValues[modelName] = this.valueStore.get(modelName);
		});
		let newElement;
		try {
			newElement = htmlToElement(html);
			if (!newElement.matches("[data-controller~=live]")) throw new Error("A live component template must contain a single root controller element.");
		} catch (error) {
			console.error(`There was a problem with the '${this.name}' component HTML returned:`, { id: this.id });
			throw error;
		}
		this.externalMutationTracker.handlePendingChanges();
		this.externalMutationTracker.stop();
		executeMorphdom(this.element, newElement, this.unsyncedInputsTracker.getUnsyncedInputs(), (element) => getValueFromElement(element, this.valueStore), this.externalMutationTracker);
		this.externalMutationTracker.start();
		const newProps = this.elementDriver.getComponentProps();
		this.valueStore.reinitializeAllProps(newProps);
		const eventsToEmit = this.elementDriver.getEventsToEmit();
		const browserEventsToDispatch = this.elementDriver.getBrowserEventsToDispatch();
		Object.keys(modifiedModelValues).forEach((modelName) => {
			this.valueStore.set(modelName, modifiedModelValues[modelName]);
		});
		eventsToEmit.forEach(({ event, data, target, componentName }) => {
			if (target === "up") {
				this.emitUp(event, data, componentName);
				return;
			}
			if (target === "self") {
				this.emitSelf(event, data);
				return;
			}
			this.emit(event, data, componentName);
		});
		browserEventsToDispatch.forEach(({ event, payload }) => {
			this.element.dispatchEvent(new CustomEvent(event, {
				detail: payload,
				bubbles: true
			}));
		});
		this.hooks.triggerHook("render:finished", this);
	}
	calculateDebounce(debounce) {
		if (debounce === true) return this.defaultDebounce;
		if (debounce === false) return 0;
		return debounce;
	}
	clearRequestDebounceTimeout() {
		if (this.requestDebounceTimeout) {
			clearTimeout(this.requestDebounceTimeout);
			this.requestDebounceTimeout = null;
		}
	}
	debouncedStartRequest(debounce) {
		this.clearRequestDebounceTimeout();
		this.requestDebounceTimeout = window.setTimeout(() => {
			this.render();
		}, this.calculateDebounce(debounce));
	}
	renderError(html) {
		let modal = document.getElementById("live-component-error");
		if (modal) modal.innerHTML = "";
		else {
			modal = document.createElement("div");
			modal.id = "live-component-error";
			modal.style.padding = "50px";
			modal.style.backgroundColor = "rgba(0, 0, 0, .5)";
			modal.style.zIndex = "100000";
			modal.style.position = "fixed";
			modal.style.top = "0px";
			modal.style.bottom = "0px";
			modal.style.left = "0px";
			modal.style.right = "0px";
			modal.style.display = "flex";
			modal.style.flexDirection = "column";
		}
		const iframe = document.createElement("iframe");
		iframe.style.borderRadius = "5px";
		iframe.style.flexGrow = "1";
		modal.appendChild(iframe);
		document.body.prepend(modal);
		document.body.style.overflow = "hidden";
		if (iframe.contentWindow) {
			iframe.contentWindow.document.open();
			iframe.contentWindow.document.write(html);
			iframe.contentWindow.document.close();
		}
		const closeModal = (modal) => {
			if (modal) modal.outerHTML = "";
			document.body.style.overflow = "visible";
		};
		modal.addEventListener("click", () => closeModal(modal));
		modal.setAttribute("tabindex", "0");
		modal.addEventListener("keydown", (e) => {
			if (e.key === "Escape") closeModal(modal);
		});
		modal.focus();
	}
	resetPromise() {
		this.nextRequestPromise = new Promise((resolve) => {
			this.nextRequestPromiseResolve = resolve;
		});
	}
	_updateFromParentProps(props) {
		if (this.valueStore.storeNewPropsFromParent(props)) this.render();
	}
};
function proxifyComponent(component) {
	return new Proxy(component, {
		get(component, prop) {
			if (prop in component || typeof prop !== "string") {
				if (typeof component[prop] === "function") {
					const callable = component[prop];
					return (...args) => {
						return callable.apply(component, args);
					};
				}
				return Reflect.get(component, prop);
			}
			if (component.valueStore.has(prop)) return component.getData(prop);
			return (args) => {
				return component.action.apply(component, [prop, args]);
			};
		},
		set(target, property, value) {
			if (property in target) {
				target[property] = value;
				return true;
			}
			target.set(property, value);
			return true;
		}
	});
}
var StimulusElementDriver = class {
	constructor(controller) {
		this.controller = controller;
	}
	getModelName(element) {
		const modelDirective = getModelDirectiveFromElement(element, false);
		if (!modelDirective) return null;
		return modelDirective.action;
	}
	getComponentProps() {
		return this.controller.propsValue;
	}
	getEventsToEmit() {
		return this.controller.eventsToEmitValue;
	}
	getBrowserEventsToDispatch() {
		return this.controller.eventsToDispatchValue;
	}
};
function get_model_binding_default(modelDirective) {
	let shouldRender = true;
	let targetEventName = null;
	let debounce = false;
	let minLength = null;
	let maxLength = null;
	let minValue = null;
	let maxValue = null;
	modelDirective.modifiers.forEach((modifier) => {
		switch (modifier.name) {
			case "on":
				if (!modifier.value) throw new Error(`The "on" modifier in ${modelDirective.getString()} requires a value - e.g. on(change).`);
				if (!["input", "change"].includes(modifier.value)) throw new Error(`The "on" modifier in ${modelDirective.getString()} only accepts the arguments "input" or "change".`);
				targetEventName = modifier.value;
				break;
			case "norender":
				shouldRender = false;
				break;
			case "debounce":
				debounce = modifier.value ? Number.parseInt(modifier.value) : true;
				break;
			case "min_length":
				minLength = modifier.value ? Number.parseInt(modifier.value) : null;
				break;
			case "max_length":
				maxLength = modifier.value ? Number.parseInt(modifier.value) : null;
				break;
			case "min_value":
				minValue = modifier.value ? Number.parseFloat(modifier.value) : null;
				break;
			case "max_value":
				maxValue = modifier.value ? Number.parseFloat(modifier.value) : null;
				break;
			default: throw new Error(`Unknown modifier "${modifier.name}" in data-model="${modelDirective.getString()}".`);
		}
	});
	const [modelName, innerModelName] = modelDirective.action.split(":");
	return {
		modelName,
		innerModelName: innerModelName || null,
		shouldRender,
		debounce,
		targetEventName,
		minLength,
		maxLength,
		minValue,
		maxValue
	};
}
var ChildComponentPlugin_default = class {
	constructor(component) {
		this.parentModelBindings = [];
		this.component = component;
		this.parentModelBindings = getAllModelDirectiveFromElements(this.component.element).map(get_model_binding_default);
	}
	attachToComponent(component) {
		component.on("request:started", (requestData) => {
			requestData.children = this.getChildrenFingerprints();
		});
		component.on("model:set", (model, value) => {
			this.notifyParentModelChange(model, value);
		});
	}
	getChildrenFingerprints() {
		const fingerprints = {};
		this.getChildren().forEach((child) => {
			if (!child.id) throw new Error("missing id");
			fingerprints[child.id] = {
				fingerprint: child.fingerprint,
				tag: child.element.tagName.toLowerCase()
			};
		});
		return fingerprints;
	}
	notifyParentModelChange(modelName, value) {
		const parentComponent = findParent(this.component);
		if (!parentComponent) return;
		this.parentModelBindings.forEach((modelBinding) => {
			if ((modelBinding.innerModelName || "value") !== modelName) return;
			parentComponent.set(modelBinding.modelName, value, modelBinding.shouldRender, modelBinding.debounce);
		});
	}
	getChildren() {
		return findChildren(this.component);
	}
};
var LazyPlugin_default = class {
	constructor() {
		this.intersectionObserver = null;
	}
	attachToComponent(component) {
		if ("lazy" !== component.element.attributes.getNamedItem("loading")?.value) return;
		component.on("connect", () => {
			this.getObserver().observe(component.element);
		});
		component.on("disconnect", () => {
			this.intersectionObserver?.unobserve(component.element);
		});
	}
	getObserver() {
		if (!this.intersectionObserver) this.intersectionObserver = new IntersectionObserver((entries, observer) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					entry.target.dispatchEvent(new CustomEvent("live:appear"));
					observer.unobserve(entry.target);
				}
			});
		});
		return this.intersectionObserver;
	}
};
var LoadingPlugin_default = class {
	attachToComponent(component) {
		component.on("loading.state:started", (element, request) => {
			this.startLoading(component, element, request);
		});
		component.on("loading.state:finished", (element) => {
			this.finishLoading(component, element);
		});
		this.finishLoading(component, component.element);
	}
	startLoading(component, targetElement, backendRequest) {
		this.handleLoadingToggle(component, true, targetElement, backendRequest);
	}
	finishLoading(component, targetElement) {
		this.handleLoadingToggle(component, false, targetElement, null);
	}
	handleLoadingToggle(component, isLoading, targetElement, backendRequest) {
		if (isLoading) this.addAttributes(targetElement, ["busy"]);
		else this.removeAttributes(targetElement, ["busy"]);
		this.getLoadingDirectives(component, targetElement).forEach(({ element, directives }) => {
			if (isLoading) this.addAttributes(element, ["data-live-is-loading"]);
			else this.removeAttributes(element, ["data-live-is-loading"]);
			directives.forEach((directive) => {
				this.handleLoadingDirective(element, isLoading, directive, backendRequest);
			});
		});
	}
	handleLoadingDirective(element, isLoading, directive, backendRequest) {
		const finalAction = parseLoadingAction(directive.action, isLoading);
		const targetedActions = [];
		const targetedModels = [];
		let delay = 0;
		const validModifiers = /* @__PURE__ */ new Map();
		validModifiers.set("delay", (modifier) => {
			if (!isLoading) return;
			delay = modifier.value ? Number.parseInt(modifier.value) : 200;
		});
		validModifiers.set("action", (modifier) => {
			if (!modifier.value) throw new Error(`The "action" in data-loading must have an action name - e.g. action(foo). It's missing for "${directive.getString()}"`);
			targetedActions.push(modifier.value);
		});
		validModifiers.set("model", (modifier) => {
			if (!modifier.value) throw new Error(`The "model" in data-loading must have an action name - e.g. model(foo). It's missing for "${directive.getString()}"`);
			targetedModels.push(modifier.value);
		});
		directive.modifiers.forEach((modifier) => {
			if (validModifiers.has(modifier.name)) {
				(validModifiers.get(modifier.name) ?? (() => {}))(modifier);
				return;
			}
			throw new Error(`Unknown modifier "${modifier.name}" used in data-loading="${directive.getString()}". Available modifiers are: ${Array.from(validModifiers.keys()).join(", ")}.`);
		});
		if (isLoading && targetedActions.length > 0 && backendRequest && !backendRequest.containsOneOfActions(targetedActions)) return;
		if (isLoading && targetedModels.length > 0 && backendRequest && !backendRequest.areAnyModelsUpdated(targetedModels)) return;
		let loadingDirective;
		switch (finalAction) {
			case "show":
				loadingDirective = () => this.showElement(element);
				break;
			case "hide":
				loadingDirective = () => this.hideElement(element);
				break;
			case "addClass":
				loadingDirective = () => this.addClass(element, directive.args);
				break;
			case "removeClass":
				loadingDirective = () => this.removeClass(element, directive.args);
				break;
			case "addAttribute":
				loadingDirective = () => this.addAttributes(element, directive.args);
				break;
			case "removeAttribute":
				loadingDirective = () => this.removeAttributes(element, directive.args);
				break;
			default: throw new Error(`Unknown data-loading action "${finalAction}"`);
		}
		if (delay) {
			window.setTimeout(() => {
				if (backendRequest && !backendRequest.isResolved) loadingDirective();
			}, delay);
			return;
		}
		loadingDirective();
	}
	getLoadingDirectives(component, element) {
		const loadingDirectives = [];
		let matchingElements = Array.from(element.querySelectorAll("[data-loading]"));
		matchingElements = matchingElements.filter((elt) => elementBelongsToThisComponent(elt, component));
		if (element.hasAttribute("data-loading")) matchingElements = [element, ...matchingElements];
		matchingElements.forEach((element) => {
			if (!(element instanceof HTMLElement) && !(element instanceof SVGElement)) throw new Error("Invalid Element Type");
			const directives = parseDirectives(element.dataset.loading || "show");
			loadingDirectives.push({
				element,
				directives
			});
		});
		return loadingDirectives;
	}
	showElement(element) {
		element.style.display = "revert";
	}
	hideElement(element) {
		element.style.display = "none";
	}
	addClass(element, classes) {
		element.classList.add(...combineSpacedArray(classes));
	}
	removeClass(element, classes) {
		element.classList.remove(...combineSpacedArray(classes));
		if (element.classList.length === 0) element.removeAttribute("class");
	}
	addAttributes(element, attributes) {
		attributes.forEach((attribute) => {
			element.setAttribute(attribute, "");
		});
	}
	removeAttributes(element, attributes) {
		attributes.forEach((attribute) => {
			element.removeAttribute(attribute);
		});
	}
};
const parseLoadingAction = (action, isLoading) => {
	switch (action) {
		case "show": return isLoading ? "show" : "hide";
		case "hide": return isLoading ? "hide" : "show";
		case "addClass": return isLoading ? "addClass" : "removeClass";
		case "removeClass": return isLoading ? "removeClass" : "addClass";
		case "addAttribute": return isLoading ? "addAttribute" : "removeAttribute";
		case "removeAttribute": return isLoading ? "removeAttribute" : "addAttribute";
	}
	throw new Error(`Unknown data-loading action "${action}"`);
};
var PageUnloadingPlugin_default = class {
	constructor() {
		this.isConnected = false;
	}
	attachToComponent(component) {
		component.on("render:started", (html, response, controls) => {
			if (!this.isConnected) controls.shouldRender = false;
		});
		component.on("connect", () => {
			this.isConnected = true;
		});
		component.on("disconnect", () => {
			this.isConnected = false;
		});
	}
};
var PollingDirector_default = class {
	constructor(component) {
		this.isPollingActive = true;
		this.pollingIntervals = [];
		this.component = component;
	}
	addPoll(actionName, duration) {
		this.polls.push({
			actionName,
			duration
		});
		if (this.isPollingActive) this.initiatePoll(actionName, duration);
	}
	startAllPolling() {
		if (this.isPollingActive) return;
		this.isPollingActive = true;
		this.polls.forEach(({ actionName, duration }) => {
			this.initiatePoll(actionName, duration);
		});
	}
	stopAllPolling() {
		this.isPollingActive = false;
		this.pollingIntervals.forEach((interval) => {
			clearInterval(interval);
		});
	}
	clearPolling() {
		this.stopAllPolling();
		this.polls = [];
		this.startAllPolling();
	}
	initiatePoll(actionName, duration) {
		let callback;
		if (actionName === "$render") callback = () => {
			this.component.render();
		};
		else callback = () => {
			this.component.action(actionName, {}, 0);
		};
		const timer = window.setInterval(() => {
			callback();
		}, duration);
		this.pollingIntervals.push(timer);
	}
};
var PollingPlugin_default = class {
	attachToComponent(component) {
		this.element = component.element;
		this.pollingDirector = new PollingDirector_default(component);
		this.initializePolling();
		component.on("connect", () => {
			this.pollingDirector.startAllPolling();
		});
		component.on("disconnect", () => {
			this.pollingDirector.stopAllPolling();
		});
		component.on("render:finished", () => {
			this.initializePolling();
		});
	}
	addPoll(actionName, duration) {
		this.pollingDirector.addPoll(actionName, duration);
	}
	clearPolling() {
		this.pollingDirector.clearPolling();
	}
	initializePolling() {
		this.clearPolling();
		if (this.element.dataset.poll === void 0) return;
		const rawPollConfig = this.element.dataset.poll;
		parseDirectives(rawPollConfig || "$render").forEach((directive) => {
			let duration = 2e3;
			directive.modifiers.forEach((modifier) => {
				switch (modifier.name) {
					case "delay":
						if (modifier.value) duration = Number.parseInt(modifier.value);
						break;
					default: console.warn(`Unknown modifier "${modifier.name}" in data-poll "${rawPollConfig}".`);
				}
			});
			this.addPoll(directive.action, duration);
		});
	}
};
var SetValueOntoModelFieldsPlugin_default = class {
	attachToComponent(component) {
		this.synchronizeValueOfModelFields(component);
		component.on("render:finished", () => {
			this.synchronizeValueOfModelFields(component);
		});
	}
	synchronizeValueOfModelFields(component) {
		component.element.querySelectorAll("[data-model]").forEach((element) => {
			if (!(element instanceof HTMLElement)) throw new Error("Invalid element using data-model.");
			if (element instanceof HTMLFormElement) return;
			if (!elementBelongsToThisComponent(element, component)) return;
			const modelDirective = getModelDirectiveFromElement(element);
			if (!modelDirective) return;
			const modelName = modelDirective.action;
			if (component.getUnsyncedModels().includes(modelName)) return;
			if (component.valueStore.has(modelName)) setValueOnElement(element, component.valueStore.get(modelName));
			if (element instanceof HTMLSelectElement && !element.multiple) component.valueStore.set(modelName, getValueFromElement(element, component.valueStore));
		});
	}
};
var ValidatedFieldsPlugin_default = class {
	attachToComponent(component) {
		component.on("model:set", (modelName) => {
			this.handleModelSet(modelName, component.valueStore);
		});
	}
	handleModelSet(modelName, valueStore) {
		if (valueStore.has("validatedFields")) {
			const validatedFields = [...valueStore.get("validatedFields")];
			if (!validatedFields.includes(modelName)) validatedFields.push(modelName);
			valueStore.set("validatedFields", validatedFields);
		}
	}
};
var LiveControllerDefault = class LiveControllerDefault extends _hotwired_stimulus__WEBPACK_IMPORTED_MODULE_0__.Controller {
	constructor(..._args) {
		super(..._args);
		this.pendingActionTriggerModelElement = null;
		this.elementEventListeners = [{
			event: "input",
			callback: (event) => this.handleInputEvent(event)
		}, {
			event: "change",
			callback: (event) => this.handleChangeEvent(event)
		}];
		this.pendingFiles = {};
	}
	initialize() {
		this.mutationObserver = new MutationObserver(this.onMutations.bind(this));
		this.createComponent();
	}
	connect() {
		this.connectComponent();
		this.mutationObserver.observe(this.element, { attributes: true });
	}
	disconnect() {
		this.disconnectComponent();
		this.mutationObserver.disconnect();
	}
	update(event) {
		if (event.type === "input" || event.type === "change") throw new Error(`Since LiveComponents 2.3, you no longer need data-action="live#update" on form elements. Found on element: ${getElementAsTagText(event.currentTarget)}`);
		this.updateModelFromElementEvent(event.currentTarget, null);
	}
	action(event) {
		const params = event.params;
		if (!params.action) throw new Error(`No action name provided on element: ${getElementAsTagText(event.currentTarget)}. Did you forget to add the "data-live-action-param" attribute?`);
		const rawAction = params.action;
		const actionArgs = { ...params };
		delete actionArgs.action;
		const directives = parseDirectives(rawAction);
		let debounce = false;
		directives.forEach((directive) => {
			let pendingFiles = {};
			const validModifiers = /* @__PURE__ */ new Map();
			validModifiers.set("stop", () => {
				event.stopPropagation();
			});
			validModifiers.set("self", () => {
				if (event.target !== event.currentTarget) return;
			});
			validModifiers.set("debounce", (modifier) => {
				debounce = modifier.value ? Number.parseInt(modifier.value) : true;
			});
			validModifiers.set("files", (modifier) => {
				if (!modifier.value) pendingFiles = this.pendingFiles;
				else if (this.pendingFiles[modifier.value]) pendingFiles[modifier.value] = this.pendingFiles[modifier.value];
			});
			directive.modifiers.forEach((modifier) => {
				if (validModifiers.has(modifier.name)) {
					(validModifiers.get(modifier.name) ?? (() => {}))(modifier);
					return;
				}
				console.warn(`Unknown modifier ${modifier.name} in action "${rawAction}". Available modifiers are: ${Array.from(validModifiers.keys()).join(", ")}.`);
			});
			for (const [key, input] of Object.entries(pendingFiles)) {
				if (input.files) this.component.files(key, input);
				delete this.pendingFiles[key];
			}
			this.component.action(directive.action, actionArgs, debounce);
			if (getModelDirectiveFromElement(event.currentTarget, false)) this.pendingActionTriggerModelElement = event.currentTarget;
		});
	}
	$render() {
		return this.component.render();
	}
	emit(event) {
		this.getEmitDirectives(event).forEach(({ name, data, nameMatch }) => {
			this.component.emit(name, data, nameMatch);
		});
	}
	emitUp(event) {
		this.getEmitDirectives(event).forEach(({ name, data, nameMatch }) => {
			this.component.emitUp(name, data, nameMatch);
		});
	}
	emitSelf(event) {
		this.getEmitDirectives(event).forEach(({ name, data }) => {
			this.component.emitSelf(name, data);
		});
	}
	$updateModel(model, value, shouldRender = true, debounce = true) {
		return this.component.set(model, value, shouldRender, debounce);
	}
	propsUpdatedFromParentValueChanged() {
		this.component._updateFromParentProps(this.propsUpdatedFromParentValue);
	}
	fingerprintValueChanged() {
		this.component.fingerprint = this.fingerprintValue;
	}
	getEmitDirectives(event) {
		const params = event.params;
		if (!params.event) throw new Error(`No event name provided on element: ${getElementAsTagText(event.currentTarget)}. Did you forget to add the "data-live-event-param" attribute?`);
		const eventInfo = params.event;
		const eventArgs = { ...params };
		delete eventArgs.event;
		const directives = parseDirectives(eventInfo);
		const emits = [];
		directives.forEach((directive) => {
			let nameMatch = null;
			directive.modifiers.forEach((modifier) => {
				switch (modifier.name) {
					case "name":
						nameMatch = modifier.value;
						break;
					default: throw new Error(`Unknown modifier ${modifier.name} in event "${eventInfo}".`);
				}
			});
			emits.push({
				name: directive.action,
				data: eventArgs,
				nameMatch
			});
		});
		return emits;
	}
	createComponent() {
		const id = this.element.id || null;
		this.component = new Component(this.element, this.nameValue, this.propsValue, this.listenersValue, id, LiveControllerDefault.backendFactory(this), new StimulusElementDriver(this));
		this.proxiedComponent = proxifyComponent(this.component);
		Object.defineProperty(this.element, "__component", {
			value: this.proxiedComponent,
			writable: true
		});
		if (this.hasDebounceValue) this.component.defaultDebounce = this.debounceValue;
		[
			new LoadingPlugin_default(),
			new LazyPlugin_default(),
			new ValidatedFieldsPlugin_default(),
			new PageUnloadingPlugin_default(),
			new PollingPlugin_default(),
			new SetValueOntoModelFieldsPlugin_default(),
			new ChildComponentPlugin_default(this.component)
		].forEach((plugin) => {
			this.component.addPlugin(plugin);
		});
	}
	connectComponent() {
		this.component.connect();
		this.mutationObserver.observe(this.element, { attributes: true });
		this.elementEventListeners.forEach(({ event, callback }) => {
			this.component.element.addEventListener(event, callback);
		});
		this.dispatchEvent("connect");
	}
	disconnectComponent() {
		this.component.disconnect();
		this.elementEventListeners.forEach(({ event, callback }) => {
			this.component.element.removeEventListener(event, callback);
		});
		this.dispatchEvent("disconnect");
	}
	handleInputEvent(event) {
		const target = event.target;
		if (!target) return;
		this.updateModelFromElementEvent(target, "input");
	}
	handleChangeEvent(event) {
		const target = event.target;
		if (!target) return;
		this.updateModelFromElementEvent(target, "change");
	}
	updateModelFromElementEvent(element, eventName) {
		if (!elementBelongsToThisComponent(element, this.component)) return;
		if (!(element instanceof HTMLElement)) throw new Error("Could not update model for non HTMLElement");
		if (element instanceof HTMLInputElement && element.type === "file") {
			const key = element.name;
			if (element.files?.length) this.pendingFiles[key] = element;
			else if (this.pendingFiles[key]) delete this.pendingFiles[key];
		}
		const modelDirective = getModelDirectiveFromElement(element, false);
		if (!modelDirective) return;
		const modelBinding = get_model_binding_default(modelDirective);
		if (!modelBinding.targetEventName) modelBinding.targetEventName = "input";
		if (this.pendingActionTriggerModelElement === element) modelBinding.shouldRender = false;
		if (eventName === "change" && modelBinding.targetEventName === "input") modelBinding.targetEventName = "change";
		if (eventName && modelBinding.targetEventName !== eventName) return;
		if (false === modelBinding.debounce) if (modelBinding.targetEventName === "input") modelBinding.debounce = true;
		else modelBinding.debounce = 0;
		const finalValue = getValueFromElement(element, this.component.valueStore);
		const finalValueIsEmpty = finalValue === "" || finalValue === null || finalValue === void 0;
		if (isTextualInputElement(element) || isTextareaElement(element)) {
			if (!finalValueIsEmpty && modelBinding.minLength !== null && typeof finalValue === "string" && finalValue.length < modelBinding.minLength) return;
			if (!finalValueIsEmpty && modelBinding.maxLength !== null && typeof finalValue === "string" && finalValue.length > modelBinding.maxLength) return;
		}
		if (isNumericalInputElement(element)) {
			if (!finalValueIsEmpty) {
				const numericValue = Number(finalValue);
				if (modelBinding.minValue !== null && numericValue < modelBinding.minValue) return;
				if (modelBinding.maxValue !== null && numericValue > modelBinding.maxValue) return;
			}
		}
		this.component.set(modelBinding.modelName, finalValue, modelBinding.shouldRender, modelBinding.debounce);
	}
	dispatchEvent(name, detail = {}, canBubble = true, cancelable = false) {
		detail.controller = this;
		detail.component = this.proxiedComponent;
		this.dispatch(name, {
			detail,
			prefix: "live",
			cancelable,
			bubbles: canBubble
		});
	}
	onMutations(mutations) {
		mutations.forEach((mutation) => {
			if (mutation.type === "attributes" && mutation.attributeName === "id" && this.element.id !== this.component.id) {
				this.disconnectComponent();
				this.createComponent();
				this.connectComponent();
			}
		});
	}
};
LiveControllerDefault.values = {
	name: String,
	url: String,
	props: {
		type: Object,
		default: {}
	},
	propsUpdatedFromParent: {
		type: Object,
		default: {}
	},
	listeners: {
		type: Array,
		default: []
	},
	eventsToEmit: {
		type: Array,
		default: []
	},
	eventsToDispatch: {
		type: Array,
		default: []
	},
	debounce: {
		type: Number,
		default: 150
	},
	fingerprint: {
		type: String,
		default: ""
	},
	requestMethod: {
		type: String,
		default: "post"
	},
	fetchCredentials: {
		type: String,
		default: "same-origin"
	}
};
LiveControllerDefault.backendFactory = (controller) => new Backend_default(controller.urlValue, controller.requestMethodValue, controller.fetchCredentialsValue);



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
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
var __webpack_exports__ = {};
/*!***************************************************************************************!*\
  !*** ./src/Bundle/ContentBundle/Resources/assets/js/article_search_live_bootstrap.js ***!
  \***************************************************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _hotwired_stimulus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @hotwired/stimulus */ "./node_modules/@hotwired/stimulus/dist/stimulus.js");
/* harmony import */ var _symfony_ux_live_component__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @symfony/ux-live-component */ "./node_modules/@symfony/ux-live-component/dist/live_controller.js");


if (!window.integratedArticleSearchStimulus) {
  var application = _hotwired_stimulus__WEBPACK_IMPORTED_MODULE_0__.Application.start();
  application.register('live', _symfony_ux_live_component__WEBPACK_IMPORTED_MODULE_1__["default"]);
  window.integratedArticleSearchStimulus = application;
}
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!*****************************************************************************!*\
  !*** ./src/Bundle/ContentBundle/Resources/assets/js/article_search_live.js ***!
  \*****************************************************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
var ROOT_SELECTOR = '[data-article-search-root]';
var APPLY_SELECTOR = '[data-article-search-apply]';
var CANCEL_SELECTOR = '[data-article-search-cancel]';
var LINK_TEXT_SELECTOR = '[data-article-search-link-text]';
var LINK_TITLE_SELECTOR = '[data-article-search-link-title]';
var TERM_SELECTOR = '[data-article-search-term]';
var NEW_TAB_SELECTOR = '[data-article-search-new-tab]';
var SELECTED_RESULT_SELECTOR = '[data-article-search-selection]:checked';
var RESULT_CONTAINER_SELECTOR = '[data-article-search-result]';
function getOriginFromUrl(url) {
  if (typeof url !== 'string' || url.length === 0) {
    return null;
  }
  try {
    return new URL(url, window.location.origin).origin;
  } catch (error) {
    return null;
  }
}
function getPreferredParentOrigin() {
  var _getOriginFromUrl;
  return (_getOriginFromUrl = getOriginFromUrl(document.referrer)) !== null && _getOriginFromUrl !== void 0 ? _getOriginFromUrl : window.location.origin;
}
function isTrustedParentMessage(event) {
  if (!event || event.source !== window.parent) {
    return false;
  }
  var trustedOrigins = new Set([window.location.origin]);
  var referrerOrigin = getOriginFromUrl(document.referrer);
  if (referrerOrigin) {
    trustedOrigins.add(referrerOrigin);
  }
  return trustedOrigins.has(event.origin);
}
function postToParent(payload) {
  window.parent.postMessage(payload, getPreferredParentOrigin());
}
function escapeHtml(value) {
  return String(value).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#39;');
}
function getRoot() {
  return document.querySelector(ROOT_SELECTOR);
}
function startsWithAny(value, prefixes) {
  return prefixes.some(function (prefix) {
    return value.startsWith(prefix);
  });
}
function ensureHttps(url) {
  if (startsWithAny(url, ['/', '#', 'mailto:', 'tel:', 'https://', 'http://'])) {
    return url;
  }
  return "https://".concat(url);
}
function isSearchTermUrl(searchTerm) {
  if (searchTerm.length === 0) {
    return false;
  }
  if (startsWithAny(searchTerm, ['/', '#', 'mailto:', 'tel:', 'www'])) {
    return true;
  }
  try {
    // eslint-disable-next-line no-new
    new URL(searchTerm);
    return true;
  } catch (error) {
    return false;
  }
}
function shouldOpenInNewTab(url, openInNewTab) {
  return openInNewTab && !startsWithAny(url, ['#', 'mailto:', 'tel:']);
}
function normalizeOptionalTitle(title) {
  var normalizedTitle = title.trim();
  return normalizedTitle.length > 0 ? normalizedTitle : null;
}
function getState(root) {
  var _root$querySelector$v, _root$querySelector, _root$querySelector$v2, _root$querySelector2, _root$querySelector$v3, _root$querySelector3, _root$querySelector$c, _root$querySelector4, _root$querySelector$d, _root$querySelector5;
  var linkText = (_root$querySelector$v = (_root$querySelector = root.querySelector(LINK_TEXT_SELECTOR)) === null || _root$querySelector === void 0 ? void 0 : _root$querySelector.value) !== null && _root$querySelector$v !== void 0 ? _root$querySelector$v : '';
  var linkTitle = (_root$querySelector$v2 = (_root$querySelector2 = root.querySelector(LINK_TITLE_SELECTOR)) === null || _root$querySelector2 === void 0 ? void 0 : _root$querySelector2.value) !== null && _root$querySelector$v2 !== void 0 ? _root$querySelector$v2 : '';
  var searchTerm = (_root$querySelector$v3 = (_root$querySelector3 = root.querySelector(TERM_SELECTOR)) === null || _root$querySelector3 === void 0 ? void 0 : _root$querySelector3.value) !== null && _root$querySelector$v3 !== void 0 ? _root$querySelector$v3 : '';
  var openInNewTab = (_root$querySelector$c = (_root$querySelector4 = root.querySelector(NEW_TAB_SELECTOR)) === null || _root$querySelector4 === void 0 ? void 0 : _root$querySelector4.checked) !== null && _root$querySelector$c !== void 0 ? _root$querySelector$c : false;
  var existing = ((_root$querySelector$d = (_root$querySelector5 = root.querySelector(APPLY_SELECTOR)) === null || _root$querySelector5 === void 0 ? void 0 : _root$querySelector5.dataset.existing) !== null && _root$querySelector$d !== void 0 ? _root$querySelector$d : '0') === '1';
  return {
    linkText: linkText,
    linkTitle: linkTitle,
    searchTerm: searchTerm,
    openInNewTab: openInNewTab,
    existing: existing
  };
}
function getSelectedResultUrl(root) {
  var _selection$closest$da, _selection$closest;
  var selection = root.querySelector(SELECTED_RESULT_SELECTOR);
  if (!selection) {
    return '';
  }
  return (_selection$closest$da = (_selection$closest = selection.closest(RESULT_CONTAINER_SELECTOR)) === null || _selection$closest === void 0 ? void 0 : _selection$closest.dataset.url) !== null && _selection$closest$da !== void 0 ? _selection$closest$da : '';
}
function isApplyEnabled(root) {
  var state = getState(root);
  if (state.linkText.length === 0) {
    return false;
  }
  if (isSearchTermUrl(state.searchTerm)) {
    return true;
  }
  return getSelectedResultUrl(root).length > 0;
}
function syncApplyState() {
  var root = getRoot();
  if (!root) {
    return;
  }
  var button = root.querySelector(APPLY_SELECTOR);
  if (!button) {
    return;
  }
  var enabled = isApplyEnabled(root);
  button.disabled = !enabled;
  button.classList.toggle('bg-[#007b67]', !enabled);
  button.classList.toggle('disabled:hover:bg-[#007b67]', !enabled);
  button.classList.toggle('text-zinc-300', !enabled);
  button.classList.toggle('cursor-not-allowed', !enabled);
}
function closeIframe() {
  postToParent({
    mceAction: 'close'
  });
}
function insertOrReplaceLink() {
  var root = getRoot();
  if (!root) {
    return;
  }
  var state = getState(root);
  if (state.linkText.length === 0) {
    return;
  }
  var url = '';
  var searchTermIsUrl = isSearchTermUrl(state.searchTerm);
  if (searchTermIsUrl) {
    url = ensureHttps(state.searchTerm);
  } else {
    var selectedResultUrl = getSelectedResultUrl(root);
    if (selectedResultUrl.length === 0) {
      return;
    }
    url = ensureHttps(selectedResultUrl);
  }
  var title = normalizeOptionalTitle(state.linkTitle);
  if (state.existing) {
    postToParent({
      mceAction: 'linkMakerReplace',
      title: title,
      href: url,
      newTab: shouldOpenInNewTab(url, state.openInNewTab),
      linkText: state.linkText
    });
  } else {
    var target = shouldOpenInNewTab(url, state.openInNewTab) ? ' target="_blank"' : '';
    var safeLinkText = escapeHtml(state.linkText);
    var safeUrl = escapeHtml(url);
    var titleAttribute = title === null ? '' : " title=\"".concat(escapeHtml(title), "\"");
    postToParent({
      mceAction: 'insertContent',
      content: "<a href=\"".concat(safeUrl, "\"").concat(target).concat(titleAttribute, ">").concat(safeLinkText, "</a>")
    });
  }
  closeIframe();
}
function setSelectionTextFromMessage(event) {
  if (!isTrustedParentMessage(event)) {
    return;
  }
  if (!event || _typeof(event.data) !== 'object' || event.data === null) {
    return;
  }
  if (typeof event.data.selectionText !== 'string') {
    return;
  }
  var root = getRoot();
  if (!root) {
    return;
  }
  var input = root.querySelector(LINK_TEXT_SELECTOR);
  if (!input) {
    return;
  }
  input.value = event.data.selectionText;
  input.dispatchEvent(new Event('input', {
    bubbles: true
  }));
  syncApplyState();
}
function bindEvents() {
  document.addEventListener('click', function (event) {
    var cancelButton = event.target.closest(CANCEL_SELECTOR);
    if (cancelButton) {
      event.preventDefault();
      closeIframe();
      return;
    }
    var applyButton = event.target.closest(APPLY_SELECTOR);
    if (applyButton) {
      event.preventDefault();
      insertOrReplaceLink();
    }
  });
  document.addEventListener('input', syncApplyState);
  document.addEventListener('change', syncApplyState);
  window.addEventListener('message', setSelectionTextFromMessage);
  var observer = new MutationObserver(function () {
    syncApplyState();
  });
  observer.observe(document.body, {
    childList: true,
    subtree: true
  });
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function () {
    bindEvents();
    syncApplyState();
  });
} else {
  bindEvents();
  syncApplyState();
}
})();

// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!********************************************************************************!*\
  !*** ./src/Bundle/ContentBundle/Resources/assets/sass/components/vue/vue.scss ***!
  \********************************************************************************/
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin

})();

/******/ })()
;