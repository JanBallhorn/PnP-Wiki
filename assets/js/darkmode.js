$(function () {
    const toggle = document.getElementById('darkmode');

    function updateUI(theme) {
        toggle.checked = theme === 'dark';
    }

    toggle.addEventListener('click', () => {
        const theme = toggle.checked ? 'dark' : 'light';
        setTheme(theme, true);
        updateUI(theme);
        if(theme === 'light'){
            $("#darkmode + .slider .slide-before").animate({left: "0.25em"}, 400);
        }
        else{
            $("#darkmode + .slider .slide-before").animate({left: "1.75em"}, 400);
        }
    });

    const osPreference = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    const preferredTheme = localStorage.getItem('preferred-theme') || osPreference;

    setTheme(preferredTheme, false);
    updateUI(preferredTheme);

    if($("html").is(".light")){
        $("#darkmode + .slider .slide-before").css("left", "0.25em");
    }
    else{
        $("#darkmode + .slider .slide-before").css("left", "1.75em");
    }

    function setTheme(theme, persist = false) {
        const on = theme;
        const off = theme === 'light' ? 'dark' : 'light'

        const htmlEl = document.documentElement;
        htmlEl.classList.add(on);
        htmlEl.classList.remove(off);

        if (persist) {
            localStorage.setItem('preferred-theme', theme);
        }
    }
})