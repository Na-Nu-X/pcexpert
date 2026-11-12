"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Move Up

    // Variables

    const move_up:HTMLButtonElement|null = document.querySelector<HTMLButtonElement>(".move_up") // Gets The Move Up Button
    if(!move_up) return

    // Events

    // Window Scroll Functionality
    window.addEventListener("scroll", () => move_up.classList.toggle("visible", window.scrollY >= 1000), { passive: true }) // Shows Or Hides The Move Up Button

    // Move Up Click Functionality
    move_up.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" })) // Scrolls Up
})
