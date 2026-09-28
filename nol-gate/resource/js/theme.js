const theme = () => {
    const THEME_LIGHT = 'light';
    const THEME_DARK = 'dark';

    const htmlEl = document.documentElement;
    const themeBtn = document.getElementById('toggle');
    const sunIcon = document.querySelector('.toggle .bxs-sun');
    const moonIcon = document.querySelector('.toggle .bx-moon');
    const logo = {
        element: document.querySelector('.no-logo--default'),
        url_light: '/resource/images/admin/logo.png',
        url_dark: '/resource/images/admin/logo-white.png',
    };
    const favicon = {
        element: document.querySelector('.no-logo--sm'),
        url_light: '/resource/images/admin/logo-sm.png',
        url_dark: '/resource/images/admin/logo-sm-white.png',
    };

    const theme = {
        set localStorage(val) {
            localStorage.setItem('theme', val);
            htmlEl.dataset.theme = val;
        },
        get localStorage() {
            return localStorage.getItem('theme');
        },
    };

    if (theme.localStorage === null) {
        theme.localStorage = THEME_LIGHT;
        logo.element.setAttribute('src', logo.url_light);
        favicon.element.setAttribute('src', favicon.url_light);
    } else {
        theme.localStorage = theme.localStorage;
    }
    if (theme.localStorage === THEME_DARK) {
        logo.element.setAttribute('src', logo.url_dark);
        favicon.element.setAttribute('src', favicon.url_dark);
    }

    const changeTheme = () => {
        const logoUrl =
            theme.localStorage === THEME_LIGHT ? logo.url_dark : logo.url_light;
        const faviconUrl =
            theme.localStorage === THEME_LIGHT
                ? favicon.url_dark
                : favicon.url_light;

        const themeMode =
            theme.localStorage === THEME_LIGHT ? THEME_DARK : THEME_LIGHT;
        theme.localStorage = themeMode;

        logo.element.setAttribute('src', logoUrl);
        favicon.element.setAttribute('src', faviconUrl);

        sunIcon.className =
            sunIcon.className == 'bx bxs-sun' ? 'bx bx-sun' : 'bx bxs-sun';
        moonIcon.className =
            moonIcon.className == 'bx bxs-moon' ? 'bx bx-moon' : 'bx bxs-moon';
    };

    if (themeBtn) {
        themeBtn.addEventListener('click', changeTheme);
    }
};

theme();
