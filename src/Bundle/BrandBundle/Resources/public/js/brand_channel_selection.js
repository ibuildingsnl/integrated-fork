document.querySelectorAll('.brands input.brand-choice').forEach(
    (brandCheckbox) => brandCheckbox.addEventListener('change', () => brandCheckbox
        .closest('.brand-container')
        .querySelectorAll('.brand-channels')
        .forEach((brandChannels) => brandChannels.style.display = brandCheckbox.checked ? 'block' : 'none')
    ) || brandCheckbox.dispatchEvent(new Event('change'))
);
