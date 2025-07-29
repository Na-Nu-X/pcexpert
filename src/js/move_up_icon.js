"use strict"

const move_up_icon = document.querySelector(".move_up_icon")

window.addEventListener("scroll", function(event) {
    if(window.scrollY >= 1000) {
        move_up_icon.style.opacity = 1
    }

    else {
        move_up_icon.style.opacity = 0
    }
})

move_up_icon.addEventListener("click", function(event) {
    event.preventDefault()
    
    window.scrollTo({
        top: 0,
        behavior: "smooth"
    })
})