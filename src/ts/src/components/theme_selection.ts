"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Theme Selection

    // Variables

    const theme_selection:HTMLDivElement = document.querySelector(".theme_selection") as HTMLDivElement // Gets The Theme Selection Container
    const light_theme:HTMLButtonElement = theme_selection.querySelector(".light_theme") as HTMLButtonElement // Gets The Light Theme Button
    const dark_theme:HTMLButtonElement = theme_selection.querySelector(".dark_theme") as HTMLButtonElement // Gets The Dark Theme Button

    // Functions

    // Function For Initialize The Theme
    function initializeTheme(button:HTMLButtonElement, theme:string):void {
        // Button Click Functionality
        button.addEventListener("click", function():void {
            document.cookie = `theme=${theme}; max-age=` + 60 * 60 * 24 * 30 * 3 + "; path=/"; // Creates The 90 Days Timed Cookie
            location.reload() // Reloads The Page
        })
    }

    // Initialization

    initializeTheme(light_theme, "light") // Initializes The Light Theme
    initializeTheme(dark_theme, "dark") // Initializes The Dark Theme
})