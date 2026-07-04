"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Move Up

    // Variables

    const move_up:HTMLButtonElement = document.querySelector(".move_up") as HTMLButtonElement // Gets The Move Up Icon

    // Events

    // Window Scroll Functionality
    window.addEventListener("scroll", function():void {
        if(window.scrollY >= 1000) move_up.style.opacity = "1" // Shows The Move Up Icon
        else move_up.style.opacity = "0" // Hides The Move Up Icon
    })

    // Move Up Click Functionality
    move_up.addEventListener("click", function(event:PointerEvent):void {
        event.preventDefault() // Prevents Default Behaviour
        
        // Scrolls Up
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        })
    })
})