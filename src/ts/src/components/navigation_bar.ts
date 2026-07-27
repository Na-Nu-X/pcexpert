"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Navigation Bar

    // Variables

    const navigation_bar:HTMLElement = document.querySelector(".navigation_bar") as HTMLElement // Gets The Navigation Bar
    const hamburger:HTMLButtonElement = navigation_bar.querySelector(".hamburger") as HTMLButtonElement // Gets The Hamburger Button
    const hamburger_icon:HTMLElement = hamburger.querySelector("i") as HTMLElement // Gets The Hamburger Icon

    const SCREEN_HEIGHT:number = window.innerHeight - navigation_bar.offsetHeight // Gets The Screen Height (Without Navigation Bar)

    // Events

    // Window Scroll Functionality (Disappearing Navigation Bar)
    window.addEventListener("scroll", function():void {
        this.scrollY >= SCREEN_HEIGHT ? navigation_bar.style.opacity = "0" : navigation_bar.style.opacity = "1" // Hides Or Shows The Navigation Bar
    })

    // Navigation Bar Mouse Over Functionality
    navigation_bar.addEventListener("mouseover", () => navigation_bar.style.opacity = "1")

    // Navigation Bar Mouse Out Functionality
    navigation_bar.addEventListener("mouseout", function():void {
        if(window.scrollY >= SCREEN_HEIGHT) this.style.opacity = "0" // Hides The Navigation Bar
    })

    // Window Resize Functionalities
    window.addEventListener("resize", function():void {
        if(this.innerWidth > 600) {
            hamburger_icon.classList.remove("fa-xmark") // Hides The X Icon
            hamburger_icon.classList.add("fa-bars") // Shows The Bars Icon
        }
    })

    // Hamburger Icon Click Functionalities
    hamburger.addEventListener("click", function():void {
        // Shows The Navigation Bar
        if(hamburger_icon.classList.contains("fa-bars")) {
            hamburger_icon.classList.replace("fa-bars", "fa-xmark"); // Shows The X Icon
            (navigation_bar.querySelector("ul") as HTMLUListElement).classList.add("open")
        }

        // Hides The Navigation Bar
        else if(hamburger_icon.classList.contains("fa-xmark")) {
            hamburger_icon.classList.replace("fa-xmark", "fa-bars"); // Shows The Bars Icon
            (navigation_bar.querySelector("ul") as HTMLUListElement).classList.remove("open")
        }
    })

    // Auto Scroll Buttons

    // Variables
    
    const scroll_about_us:HTMLLIElement|null = document.querySelector(".scroll_about_us") as HTMLLIElement || null // Gets The Scroll About Us Button
    const about_us:HTMLElement|null = document.querySelector(".about_us") as HTMLElement || null // Gets The About Us Section
    
    const scroll_all_services:HTMLLIElement|null = document.querySelector(".scroll_all_services") as HTMLLIElement || null // Gets The Scroll Our Services Button
    const all_services:HTMLElement|null = document.querySelector(".all_services") as HTMLElement || null // Gets The Our Services Section
    
    const scroll_contact:HTMLLIElement|null = document.querySelector(".scroll_contact") as HTMLLIElement || null // Gets The Scroll Contact Button
    const contact:HTMLElement|null = document.querySelector(".contact") as HTMLElement || null // Gets The Contact Section
    
    const scroll_pending_reviews:HTMLLIElement|null = document.querySelector(".scroll_pending_reviews") as HTMLLIElement || null // Gets The Scroll Pending Reviews Button
    const pending_reviews:HTMLElement|null = document.querySelector(".pending_reviews") as HTMLElement || null // Gets The Pending Reviews Section
    
    const scroll_users:HTMLLIElement|null = document.querySelector(".scroll_users") as HTMLLIElement || null // Gets The Scroll Users Button
    const users:HTMLElement|null = document.querySelector(".users") as HTMLElement || null // Gets The Users Section

    const scroll_error_log:HTMLLIElement|null = document.querySelector(".scroll_error_log") as HTMLLIElement || null // Gets The Scroll Error Log Button
    const error_log:HTMLElement|null = document.querySelector(".error_log") as HTMLElement || null // Gets The Error Log Section

    // Functions

    // Function For Initialize The Scroll
    function initializeScroll(button:HTMLElement, scroll_to:HTMLElement):void {
        // Button Click Functionality
        button.addEventListener("click", function(event:PointerEvent):void {
            event.preventDefault() // Prevents The Default Behaviour

            console.log(scroll_to)

            let top:number = scroll_to.getBoundingClientRect().top + window.scrollY - navigation_bar.offsetHeight - 20 // Stores The Top Scroll

            if(scroll_to.classList.contains("all_services")) {
                top = scroll_to.getBoundingClientRect().top + window.scrollY - navigation_bar.offsetHeight // Sets The Top Scroll
            }

            window.scrollTo({
                top: top,
                behavior: "smooth"
            })
        })
    }

    // Initialization

    if(scroll_about_us && about_us) initializeScroll(scroll_about_us, about_us) // Initializes The Scroll
    if(scroll_all_services && all_services) initializeScroll(scroll_all_services, all_services) // Initializes The Scroll
    if(scroll_contact && contact) initializeScroll(scroll_contact, contact) // Initializes The Scroll
    if(scroll_pending_reviews && pending_reviews) initializeScroll(scroll_pending_reviews, pending_reviews) // Initializes The Scroll
    if(scroll_users && users) initializeScroll(scroll_users, users) // Initializes The Scroll
    if(scroll_error_log && error_log) initializeScroll(scroll_error_log, error_log) // Initializes The Scroll
})