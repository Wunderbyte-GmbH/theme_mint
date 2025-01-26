// Variables currentMode and targetedMode are defined in head of the page
// See standard_head_html() in \theme_mint\output\core_renderer
document.addEventListener('DOMContentLoaded', () => {
    const body = document.querySelector('body');
    body.classList.add(currentMode);

    const btn = document.querySelector('#usernavigation .switch-wrapper .mode-switch');

    if (!btn) return;

    const clickHandler = function(event) {
        if (event instanceof KeyboardEvent
            && event.key.toLocaleLowerCase() !== 'enter') {
            return;
        }

        const params = {
            targetedmode: targetedMode
        };
        const winloc = window.location;
        const path = '/theme/mint/ajax/site_mode.php';
        const urlBase = winloc.protocol + '//' + winloc.host + path + winloc.search;
        const url = new URL(urlBase);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        fetch(url)
            .then(response => response.json())
            .then(data => {
                body.classList.remove(currentMode);
                const newTarget = currentMode;
                currentMode = targetedMode;
                targetedMode = newTarget;
                body.classList.add(currentMode);
                btn.setAttribute('data-mode', currentMode);

                // Adjust logo according to theme mode
                // const logo = $('.navbar-brand img');
                // if (currentMode === 'dark') {
                //     logo.attr('src', '/theme/mint/pix/logo_light.png');
                // }
                // if (currentMode === 'light') {
                //     logo.attr('src', '/theme/mint/pix/logo.png');
                // }

                return data;
            })
            .then(data => console.log(data))
            .catch(error => console.error(error));
    };

    btn.addEventListener('click', clickHandler);
    btn.addEventListener('keyup', clickHandler);
});

function isKeyboardEvent(event) {
    // Check if the event has a property that indicates it's a keyboard event
    return typeof event.keyCode === 'number' || typeof event.key === 'string' || typeof event.which === 'number';
}