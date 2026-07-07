"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Our Services

    // Variables

    const navigation_bar:HTMLElement = document.querySelector(".navigation_bar") as HTMLElement // Gets The Navigation Bar
    const our_services:HTMLElement = document.querySelector(".our_services") as HTMLElement // Gets The Our Services Section
    const all_contact_us_buttons:NodeListOf<HTMLButtonElement> = our_services.querySelectorAll<HTMLButtonElement>(".contact_us_button") // Gets All Contact Us Buttons
    const contact:HTMLElement = document.querySelector(".contact") as HTMLElement // Gets The Contact Section
    const contact_form:HTMLFormElement = contact.querySelector(".contact_form") as HTMLFormElement // Gets The Contact Form
    const select_menu:HTMLSelectElement = contact_form.querySelector(".select_menu") as HTMLSelectElement // Gets The Select Menu

    // Events

    // All Contact Us Buttons Click Functionalities
    all_contact_us_buttons.forEach(function(one_button:HTMLButtonElement):void {
        one_button.addEventListener("click", function():void {
            // Scrolls To The Contact
            window.scrollTo({
                top: contact.getBoundingClientRect().top + window.scrollY - navigation_bar.offsetHeight,
                behavior: "smooth"
            })

            if(this.classList.contains("build_computer_button")) select_menu.value = "build_computer" // Selects The Build Computer Option
            else if(this.classList.contains("computer_maintenance_button")) select_menu.value = "computer_maintenance" // Selects The Build Maintenance Option
            else if(this.classList.contains("computer_cleaning_button")) select_menu.value = "computer_cleaning" // Selects The Build Cleaning Option
        })
    })
})