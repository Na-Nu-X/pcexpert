"use strict"

const light_theme = document.querySelector(".light_theme")
const dark_theme = document.querySelector(".dark_theme")

light_theme.addEventListener("click", function(event) {
    document.cookie = "theme=light; max-age=" + 60 * 60 * 24 * 30 + "; path=/"; // 30 Days
    
    location.reload()
})

dark_theme.addEventListener("click", function(event) {
    document.cookie = "theme=dark; max-age=" + 60 * 60 * 24 * 30 + "; path=/"; // 30 Days

    location.reload()
})