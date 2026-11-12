import { initializeThemeToggle } from "./theme_selection.js"

"use strict"

const DESKTOP_BREAKPOINT:number = 1024 // Must Match The "lg" Breakpoint In partials/_variables.scss

document.addEventListener("DOMContentLoaded", function():void {
    // Navigation Bar

    // Variables

    const navigation_bar:HTMLElement|null = document.querySelector<HTMLElement>("[data-navigation]") // Gets The Navigation Bar
    if(!navigation_bar) return

    const toggle:HTMLButtonElement|null = navigation_bar.querySelector<HTMLButtonElement>("[data-navigation-toggle]") // Gets The Hamburger Button
    const toggle_icon:HTMLElement|null = toggle?.querySelector<HTMLElement>("i") ?? null // Gets The Hamburger Icon
    const menu:HTMLElement|null = navigation_bar.querySelector<HTMLElement>("[data-navigation-menu]") // Gets The Collapsible Menu
    const theme_toggle:HTMLButtonElement|null = navigation_bar.querySelector<HTMLButtonElement>("[data-theme-toggle]") // Gets The Theme Toggle Button

    // Functions

    // Function For Open Or Close The Mobile Menu
    function setMenuOpen(is_open:boolean):void {
        navigation_bar?.classList.toggle("open", is_open)
        toggle?.setAttribute("aria-expanded", String(is_open))
        toggle?.setAttribute("aria-label", is_open ? "Zavrieť menu" : "Otvoriť menu")
        toggle_icon?.classList.toggle("fa-bars", !is_open) // https://fontawesome.com/icons/bars
        toggle_icon?.classList.toggle("fa-xmark", is_open) // https://fontawesome.com/icons/xmark
    }

    // Function For Update The Scrolled State (Adds A Shadow Under The Bar)
    function updateScrolledState():void {
        navigation_bar?.classList.toggle("scrolled", window.scrollY > 8)
    }

    // Events

    // Hamburger Click Functionality
    toggle?.addEventListener("click", () => setMenuOpen(!navigation_bar.classList.contains("open")))

    // Menu Link Click Functionality (Closes The Mobile Menu)
    menu?.addEventListener("click", function(event:MouseEvent):void {
        if(event.target instanceof Element && event.target.closest("a")) setMenuOpen(false)
    })

    // Escape Key Functionality
    document.addEventListener("keydown", function(event:KeyboardEvent):void {
        if(event.key === "Escape" && navigation_bar.classList.contains("open")) {
            setMenuOpen(false)
            toggle?.focus()
        }
    })

    // Window Resize Functionality (Resets The Mobile Menu On Desktop)
    window.addEventListener("resize", function():void {
        if(window.innerWidth >= DESKTOP_BREAKPOINT) setMenuOpen(false)
    })

    // Window Scroll Functionality
    window.addEventListener("scroll", updateScrolledState, { passive: true })

    // Initialization

    if(theme_toggle) initializeThemeToggle(theme_toggle) // Initializes The Theme Toggle
    updateScrolledState()
})
