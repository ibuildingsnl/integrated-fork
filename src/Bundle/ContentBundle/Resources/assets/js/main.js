import $ from 'jquery';

jQuery = $;
global.$ = global.jQuery = $;
window.$ = window.jQuery = $;

import Handlebars from 'handlebars';
global.Handlebars = Handlebars;

import 'typeahead.js/dist/typeahead.jquery';

import Bloodhound from 'typeahead.js/dist/bloodhound';
global.Bloodhound = Bloodhound;

import 'jquery-placeholder';

import moment from 'moment';
global.moment = moment;

import 'select2/dist/js/select2.full';

import Coloris from "@melloware/coloris";
Coloris.init();

Coloris({
    el: '.coloris input',
    themeMode: 'light',
    clearButton: true,
    clearLabel: 'Clear',
    format: 'mixed',
});

import './global'

import './scripts'

import './tooltip'
