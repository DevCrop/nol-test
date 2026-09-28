const app = () => {
    init();
    menuFunc();
    sidebarFunc();
    setDataset();
    resize();
};

const init = () => {
    console.log('[script.js]');
    const sidebar = document.querySelector('.no-sidebar');
    if (sidebar) {
        sidebar.dataset.drawerWidth = '225px';
    }
};

const setDataset = () => {
    document.body.dataset.sidebarHoverable = true;
};

const menuFunc = () => {
    const menu = $('.no-menu-list > li');
    const menuToggleBtn = $('.no-header-menu-btn');
    // const userMenu = $('.no-nav-list');
    const roleBtn = $('.no-role-btn');
    // const roleMenu = $('.no-table-action');

    const toggleSubMenu = (e) => {
        const target = e.target.closest('.no-menu-list > li');
        const menuIcon = target.querySelector('.no-menu-arrow');

        $(target)
            .siblings()
            .children('.no-menu-link')
            .stop()
            .removeClass('active');
        $(target).siblings().find('ul').stop().slideUp(200);
        $(target.querySelector('ul')).stop().slideToggle(200);

        $(target).children('.no-menu-link').toggleClass('active');
        $(target).siblings().find('.no-menu-arrow').removeClass('open');
        $(menuIcon).toggleClass('open');
    };

    function toggleModal() {
        console.log($(this).siblings());
        $(this).toggleClass('active');
        $(this).siblings().toggleClass('on');
    }

    const toggleUserMenu = () => {
        const userMenu = $('.no-nav-list');
        userMenu.toggleClass('on');
        menuToggleBtn.toggleClass('active');
    };

    const removeUserMenu = () => {
        const userMenu = $('.no-nav-list');
        userMenu.removeClass('on');
        menuToggleBtn.removeClass('active');
    };

    const removeModalWhenWindowClicked = (e) => {
        if (
            e.target.closest('.no-header-menu-btn') ||
            e.target.closest('.no-nav-list')
        ) {
            return;
        }
        removeUserMenu();
    };
    $(window).click(removeModalWhenWindowClicked);

    roleBtn.click(toggleModal);
    menuToggleBtn.click(toggleUserMenu);
    menu.click(toggleSubMenu);
};

const resize = () => {
    const sidebar = document.querySelector('.no-sidebar');
    const sidebarInner = sidebar.querySelector('.no-sidebar-menu__inner');
    const drawerOverlay = $('.no-drawer-overlay');

    const resizeInit = () => {
        const isBlow_1024 = window.innerWidth <= 1024;
        const drawerWitdh = sidebar.dataset.drawerWidth;

        if (isBlow_1024) {
            sidebar.style.width = drawerWitdh;
            sidebarInner.style.width = drawerWitdh;
            sidebar.dataset.drawerMobile = true;
            return;
        }
        sidebar.removeAttribute('style');
        sidebarInner.removeAttribute('style');
        sidebar.dataset.drawerMobile = false;
        sidebar.classList.remove('drawer-on');
        drawerOverlay.removeClass('drawer-on');
    };

    window.addEventListener('resize', resizeInit);
    resizeInit();
};

const sidebarFunc = () => {
    const body = document.body;
    const sidebarBtn = $('.no-sidebar-toggle');
    const drawerBtn = $('.no-drawer-btn');
    const sidebar = $('.no-sidebar');
    const drawerOverlay = $('.no-drawer-overlay');

    const toggleSidebar = () => {
        sidebarBtn.toggleClass('close');
        if (body.dataset.sidebarMinimize) {
            delete body.dataset.sidebarMinimize;
            return;
        }
        body.dataset.sidebarMinimize = 'on';
    };

    const toggleDrawer = () => {
        sidebar.toggleClass('drawer-on');
        drawerOverlay.toggleClass('drawer-on');
    };

    drawerOverlay.click(toggleDrawer);
    drawerBtn.click(toggleDrawer);
    sidebarBtn.click(toggleSidebar);
};

app();
