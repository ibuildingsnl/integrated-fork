//Goal:
//This code is to organise taxonomies
//The input is a list of taxonomies with a tree structure. But we receive them as flat list with levels
//There can be multiple of these in 1 page, for each we save a relation in the relations array
//The channels are involved: categories will be shown based on active channels

//Keywords:
//pills = The selected items that are shown to the user
//popup = When a user clicks on the fullscreen icon, a popup is shown with all the options
//tabs = The tabs in the popup screen, these are tabs. These refer to the level0 items of the input
//selected_tab = The selected item of the tabs.

//Key events:
//User clicks channel
//User clicks radio button
//User enabled fullscreen
//User disables fullscreen
//User clicks tab

//Flows:
// - One where we setup everything
// - One where we handle a specific category event

const channels_selector = '#integrated_content_channels'
const pills_selector = '.enabled_categories_pills'
const popup_selector = '.category_wrapper'
const input_field_prefix = 'integrated_content_relations_'

let relations = []
let enabled_categories = []
let enabled_channels = []
let current_relation = {}
let selected_tab = ''

document.addEventListener("DOMContentLoaded", function(event) {
    setupRelations()
    channel_checkboxes = setupChannels()
    addEventListeners(channel_checkboxes)
    updateDOMForAllRelations()
    filterBasedOnChannels()
});

function setupRelations() {
    const relevant_relations = Array.from(document.querySelectorAll('.taxonomy_category')).map(item => item.id)
    for (relation_id of relevant_relations) {
        let new_relation = createNewRelation(relation_id)
        relations = [...relations, new_relation]
    }
}

function createNewRelation(relation_id) {
    return {
        relation_id: relation_id,
        input_selector: input_field_prefix + relation_id.replace("taxonomy_category_", ''),
        pills_selector: '#' + relation_id + ' ' + pills_selector,
        popup_selector: '#' + relation_id + ' ' + popup_selector,
        category_checkboxes: document.querySelectorAll('#' + relation_id + ' .categories_checkboxes_basic input[type=checkbox]'),
        all_category_checkboxes: document.querySelectorAll('#' + relation_id + ' .categories_checkboxes input[type=checkbox]'),
        enabled_categories: document.querySelector('#' + relation_id + ' .enabled_categories').dataset.ids.split(",").filter(n => n),
        popup_tabs: document.querySelectorAll('#' + relation_id + ' .category_tab')
    }
}

function setupChannels() {
    const channel_checkboxes = document.querySelectorAll(channels_selector + ' input[type=checkbox]')
    enabled_channels = getEnabledChannels(channel_checkboxes)
    return channel_checkboxes
}

function getEnabledChannels(checkboxes) {
    return Array.from(checkboxes).reduce((prev, current, arr) => {
        return current.checked === true ? [...prev, current.value] : prev
    }, [])
}

function addEventListeners(channel_checkboxes) {
    for (relation of relations) {
        relation.all_category_checkboxes.forEach(item => {
            item.addEventListener('change', handleCategoryClick)
        })
    }
    document.querySelectorAll('.categories_tabs .category_tab').forEach(item => {
        item.addEventListener('click', handleTabClick)
    })
    channel_checkboxes.forEach(item => {
        item.addEventListener('change', handleChannelClick)
    })
    document.querySelectorAll('.togglefullscreen').forEach(item => {
        item.addEventListener('click', toggleFullscreen)
    })
    //listen to the closing of the popup. The event will be sent from global.js
    document.addEventListener("cancelPopupEvent", function(e) {
        handleClosePopup()
    });
}

//this function will run, when:
//a user clicks outside of the popup
//a user presses escape
function handleClosePopup() {
    if (selected_tab !== '') {
        filterBasedOnChannels()
        showEnabledCategoryPills()
    }
    selected_tab = ''
}

function activateHeader() {
    for (popup_tab of current_relation.popup_tabs) {
        popup_tab.classList.remove("active");
        if (selected_tab !== undefined && popup_tab.dataset.level0 === selected_tab.dataset.level0) {
            popup_tab.classList.add('active')
        }
    }
}

function handleTabClick() {
    selected_tab = event.target
    setCurrentRelation(event.target.closest('.taxonomy_category').id)
    activateHeader(selected_tab)
    filterCheckboxesInPopup()
}

function setCurrentRelation(relation_id) {
    current_relation = relations.filter(relation => relation.relation_id === relation_id)[0]
}

function setActiveTab() {
    selected_tab = Array.from(current_relation.popup_tabs).find(item => item.classList.contains('hidden') === false)
}

function togglePopup() {
    document.querySelector(current_relation.popup_selector).classList.toggle("show");
    document.querySelector('#dropdown_overlay').classList.toggle("hide");
}

function toggleFullscreen() {
    if (selected_tab != '') {
        selected_tab = ''
    } else {
        setCurrentRelation(event.target.closest('.taxonomy_category').id)
        setActiveTab()
        activateHeader()
    }
    togglePopup()
    filterCheckboxesInPopup()
    showEnabledCategoryPills()
}

function handleChannelClick(event) {
    if (enabled_channels.includes(event.target.value)) {
        enabled_channels = enabled_channels.filter(item => item != event.target.value)
    } else {
        enabled_channels.push(event.target.value)
    }

    filterBasedOnChannels()
    updateDOMForAllRelations()
}

function handleCategoryClick(event) {
    setCurrentRelation(event.target.closest('.taxonomy_category').id)
    updateFormInputField(event)
    updateDOMForCurrentRelation()
}

function updateDOMForCurrentRelation() {
    showEnabledCategoryPills()
    setCheckboxState()
}

function updateDOMForAllRelations() {
    for (relation of relations) {
        current_relation = relation
        updateDOMForCurrentRelation()
    }
}

function updateFormInputField(event) {
    const input_id = '#' + current_relation.input_selector
    const current_value = document.querySelector(input_id).value.split(',').filter(n => n)
    if (current_value.includes(event.target.dataset.id)) {
        //here we remove
        document.querySelector(input_id).value = current_value.filter(n => n !== event.target.dataset.id).toString()
    } else {
        //here we add
        document.querySelector(input_id).value = document.querySelector(input_id).value + "," + event.target.dataset.id
    }
}

function emptyCurrentPills() {
    document.querySelector(current_relation.pills_selector).innerHTML = "";
}

function showEnabledCategoryPills() {
    const current_value = document.querySelector('#' + current_relation.input_selector).value.split(',').filter(n => n)
    const categories_checkboxes = current_relation.category_checkboxes

    emptyCurrentPills()
    for (checkbox of categories_checkboxes) {
        //Hidden because of selected channels? Dont show
        if (checkbox.closest('li').classList.contains('hidden')) {
            continue
        }

        if (current_value.includes(checkbox.dataset.id)) {
            appendPill(checkbox.closest('li').dataset.fulltitle)
        }
    }
}

function appendPill(pill_text) {
    const node = document.createElement("div");
    node.classList.add('active_category');
    node.appendChild(document.createTextNode(pill_text));
    document.querySelector(current_relation.pills_selector).appendChild(node);
}

function setCheckboxState(enabled_categories) {
    const current_value = document.querySelector('#' + current_relation.input_selector).value.split(',').filter(n => n)
    for (checkbox of current_relation.all_category_checkboxes) {
        let state = checkbox.value

        if (current_value.includes( checkbox.dataset.id)) {
            checkbox.checked = true
        } else {
            checkbox.checked = false
        }
    }
}

function filterCheckboxesInPopup() {
    const categories_checkboxes = document.querySelectorAll('#' + current_relation.relation_id + ' .categories_checkboxes li')

    for (category_item of categories_checkboxes) {
        //if no channels are selected, the default is true
        let show = showCategoryBasedOnChannels(category_item)
        //should a category be shown based on tab selection
        show = showCategoryBasedOnTab(category_item, show)
        //apply outcome
        toggleItem(category_item, show)
    }
}

function showCategoryBasedOnTab(category_item, show) {
    if (selected_tab !== '' && show === true) {
        if (category_item.dataset.level0 === selected_tab.dataset.level0) {
            show = true
        } else {
            show = false
        }
    }
    return show
}

function filterBasedOnChannels() {
    const categories_checkboxes = document.querySelectorAll('.categories_checkboxes li')
    const popup_tabs = document.querySelectorAll('.categories_tabs .category_tab')

    for (category_item of [...categories_checkboxes, ...popup_tabs]) {
        let show = showCategoryBasedOnChannels(category_item)

        toggleItem(category_item, show)
    }
}

function toggleItem(item, show) {
    show === true ? item.classList.remove('hidden') : item.classList.add('hidden')
}

function showCategoryBasedOnChannels(category_item) {
    if (enabled_channels.length === 0) {
        return true
    }
    return category_item.dataset.parentchannels?.split(",").filter(n => n).some(channel => enabled_channels.includes(channel))
}
