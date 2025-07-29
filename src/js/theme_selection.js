"use strict"

const light_theme = document.querySelector(".light_theme")
const dark_theme = document.querySelector(".dark_theme")

function themeSelection(button, theme) {
    button.addEventListener("click", function(event) {
        document.cookie = `theme=${theme}; max-age=` + 60 * 60 * 24 * 30 + "; path=/"; // 30 Days
        
        location.reload()
    })
}

themeSelection(light_theme, "light")
themeSelection(dark_theme, "dark")