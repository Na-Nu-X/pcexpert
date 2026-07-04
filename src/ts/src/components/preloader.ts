"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Preloader

    // Variables

    const preloader:HTMLDivElement = document.querySelector(".preloader") as HTMLDivElement // Gets The Preloader

    // Events

    // Window Load Functionality
    window.addEventListener("load", () => preloader.classList.add("hidden")) // Hides The Preloader
})