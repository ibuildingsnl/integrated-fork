let inputChanged = false

document.addEventListener('DOMContentLoaded', function() {
    addEventListeners()

    function showSubmitButton() {
        if (inputChanged == true) {
            return
        }

        showSubmitButton()
        removeEventListeners()
        inputChanged = true
    }

    function showSubmitButton() {
        document.querySelector('#integrated_content_actions_save').style.display = 'block'
    }

    function addEventListeners() {
        let inputs = document.querySelectorAll('input')
        for (input of inputs) {
            input.addEventListener("click", showSubmitButton);
            input.addEventListener("focus", showSubmitButton);
        }
    }

    function removeEventListeners() {
        let inputs = document.querySelectorAll('input')
        for (input of inputs) {
            input.removeEventListener("click", showSubmitButton, false);
            input.removeEventListener("focus", showSubmitButton, false);
        }
    }
}, false);
