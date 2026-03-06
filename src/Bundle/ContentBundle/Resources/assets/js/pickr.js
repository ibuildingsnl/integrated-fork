import Pickr from '@simonwep/pickr';
import '@simonwep/pickr/dist/themes/classic.min.css';

function applyInputColorPreview(input, color) {
    if (!color) {
        input.style.removeProperty('background-color');
        input.style.removeProperty('color');
        input.style.removeProperty('border-color');
        return;
    }

    const rgba = color.toRGBA();
    const red = rgba[0];
    const green = rgba[1];
    const blue = rgba[2];
    const hexColor = formatHexColorValue(color);
    const luminance = (0.299 * red) + (0.587 * green) + (0.114 * blue);

    input.style.backgroundColor = hexColor;
    input.style.color = (luminance > 186) ? '#111827' : '#ffffff';
    input.style.borderColor = hexColor;
}

function formatHexColorValue(color) {
    const hexParts = color.toHEXA();
    const red = String(hexParts[0] || '00').toUpperCase();
    const green = String(hexParts[1] || '00').toUpperCase();
    const blue = String(hexParts[2] || '00').toUpperCase();

    return `#${red}${green}${blue}`;
}

function emitColorInputEvents(input) {
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
}

function updateColorInput(input, color) {
    if (!color) {
        input.value = '';
        applyInputColorPreview(input, null);
        return;
    }

    input.value = formatHexColorValue(color);
    applyInputColorPreview(input, color);
}

function syncPickrFromInputValue(input, pickr) {
    const rawValue = (input.value || '').trim();
    if (rawValue === '') {
        applyInputColorPreview(input, null);
        return null;
    }

    try {
        if (!pickr.setColor(rawValue, true)) {
            return null;
        }

        const color = pickr.getColor();
        if (!color) {
            return null;
        }

        updateColorInput(input, color);
        return color;
    } catch (error) {
        return null;
    }
}

function initializePickr(root = document) {
    if (!root || typeof root.querySelectorAll !== 'function') {
        return;
    }

    const inputs = root.querySelectorAll('.coloris input');
    inputs.forEach((input) => {
        if (input.dataset.pickrInitialized === 'true') {
            return;
        }

        const pickr = Pickr.create({
            el: input,
            theme: 'classic',
            useAsButton: true,
            default: input.value || '#335767',
            defaultRepresentation: 'HEXA',
            comparison: false,
            components: {
                preview: true,
                opacity: false,
                hue: true,
                interaction: {
                    hex: true,
                    rgba: false,
                    input: true,
                    clear: true,
                    save: true,
                },
            },
        });

        pickr.on('change', (color) => {
            if (!color) {
                return;
            }

            updateColorInput(input, color);
            emitColorInputEvents(input);
        });

        pickr.on('save', (color, instance) => {
            const selectedColor = color || instance.getColor();

            if (selectedColor) {
                updateColorInput(input, selectedColor);
            } else {
                const syncedColor = syncPickrFromInputValue(input, instance);
                updateColorInput(input, syncedColor);
            }

            emitColorInputEvents(input);
            instance.hide();
        });

        pickr.on('clear', (instance) => {
            updateColorInput(input, null);
            emitColorInputEvents(input);
            instance.hide();
        });

        input.addEventListener('input', () => {
            syncPickrFromInputValue(input, pickr);
        });

        input.addEventListener('blur', () => {
            syncPickrFromInputValue(input, pickr);
        });

        syncPickrFromInputValue(input, pickr);
        input.dataset.pickrInitialized = 'true';
    });
}

initializePickr(document);

if (window.__pickrTurboLoadBound !== true) {
    document.addEventListener('turbo:load', (event) => {
        initializePickr(event.target || document);
    });
    window.__pickrTurboLoadBound = true;
}
