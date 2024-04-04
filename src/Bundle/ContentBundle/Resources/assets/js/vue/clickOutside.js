export default {
    beforeMount(el, {value}, {transition}) {
        el.clickOutsideEvent = function(event) {
            if(el !== event.target && !el.contains(event.target)) {
                value(event);
            }
        }

        document.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent);
    }
}
