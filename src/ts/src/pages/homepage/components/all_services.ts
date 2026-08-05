"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // All Services

    // Variables

    const all_services:HTMLElement = document.querySelector(".all_services") as HTMLElement // Gets The All Services Section

    const computer_building:HTMLFormElement = all_services.querySelector(".computer_building") as HTMLFormElement // Gets The Computer Building Form
    const computer_maintenance:HTMLFormElement = all_services.querySelector(".computer_maintenance") as HTMLFormElement // Gets The Computer Maintenance Form
    const computer_cleaning:HTMLFormElement = all_services.querySelector(".computer_cleaning") as HTMLFormElement // Gets The Computer Cleaning Form

    const continue_computer_building:HTMLButtonElement = computer_building.querySelector(".computer_building_container .bottom .continue") as HTMLButtonElement // Gets The Computer Building Continue To Payment Button
    const continue_computer_maintenance:HTMLButtonElement = computer_maintenance.querySelector(".computer_maintenance_container .bottom .continue") as HTMLButtonElement // Gets The Computer Maintenance Continue To Payment Button
    const continue_computer_cleaning:HTMLButtonElement = computer_cleaning.querySelector(".computer_cleaning_container .bottom .continue") as HTMLButtonElement // Gets The Computer Cleaning Continue To Payment Button

    const computer_building_dialog:HTMLDialogElement = computer_building.querySelector(".computer_building_container .bottom .reserve_service_modal") as HTMLDialogElement // Gets The Computer Building Dialog
    const computer_maintenance_dialog:HTMLDialogElement = computer_maintenance.querySelector(".computer_maintenance_container .bottom .reserve_service_modal") as HTMLDialogElement // Gets The Computer Maintenance Dialog
    const computer_cleaning_dialog:HTMLDialogElement = computer_cleaning.querySelector(".computer_cleaning_container .bottom .reserve_service_modal") as HTMLDialogElement // Gets The Computer Cleaning Dialog

    // Gloval Event Delegations

    continue_computer_building.addEventListener("click", () => computer_building_dialog.showModal()) // Shows The Computer Building Dialog

    // Computer Building Dialog Click Functionality
    computer_building_dialog.addEventListener("click", function(event:PointerEvent):void {
        const reserve_service_dimensions:DOMRect = (this.querySelector(".reserve_service") as HTMLDivElement).getBoundingClientRect() // Gets The Reserve Service Container Dimensions

        if (
            event.clientX < reserve_service_dimensions.left ||
            event.clientX > reserve_service_dimensions.right ||
            event.clientY < reserve_service_dimensions.top ||
            event.clientY > reserve_service_dimensions.bottom ||
            (event.target as HTMLAnchorElement).classList.contains("back") ||
            ((event.target as HTMLElement).parentNode as HTMLAnchorElement).classList.contains("back")
        ) {
            this.close() // Hides The Computer Building Dialog
        }

        // Next Button
        if((event.target as HTMLButtonElement).classList.contains("next")) {
            const next:HTMLButtonElement = event.target as HTMLButtonElement // Gets The Next Button
            const current_section:HTMLDivElement = next.closest(".section") as HTMLDivElement // Gets The Current Section
            const all_sections:NodeListOf<HTMLDivElement> = this.querySelectorAll<HTMLDivElement>(".section") // Gets The All Sections
            const current_section_index:number = [...all_sections].findIndex(one_section => one_section === current_section) // Gets The Current Section Index

            all_sections.forEach(one_section => one_section.classList.remove("active")); // Hides All Sections
            if(current_section_index + 1 < all_sections.length) (all_sections[current_section_index + 1] as HTMLDivElement).classList.add("active") // Shows The Next Section
        }

        // Previous Button
        if((event.target as HTMLButtonElement).classList.contains("previous")) {
            const previous:HTMLButtonElement = event.target as HTMLButtonElement // Gets The Previous Button
            const current_section:HTMLDivElement = previous.closest(".section") as HTMLDivElement // Gets The Current Section
            const all_sections:NodeListOf<HTMLDivElement> = this.querySelectorAll<HTMLDivElement>(".section") // Gets The All Sections
            const current_section_index:number = [...all_sections].findIndex(one_section => one_section === current_section) // Gets The Current Section Index

            all_sections.forEach(one_section => one_section.classList.remove("active")); // Hides All Sections
            if(current_section_index - 1 >= 0) (all_sections[current_section_index - 1] as HTMLDivElement).classList.add("active") // Shows The Previous Section
        }
    })

    continue_computer_maintenance.addEventListener("click", () => computer_maintenance_dialog.showModal()) // Shows The Computer Maintenance Dialog

    // Computer Maintenance Dialog Click Functionality
    computer_maintenance_dialog.addEventListener("click", function(event:PointerEvent):void {
        const reserve_service_dimensions:DOMRect = (this.querySelector(".reserve_service") as HTMLDivElement).getBoundingClientRect() // Gets The Reserve Service Container Dimensions

        if (
            event.clientX < reserve_service_dimensions.left ||
            event.clientX > reserve_service_dimensions.right ||
            event.clientY < reserve_service_dimensions.top ||
            event.clientY > reserve_service_dimensions.bottom ||
            (event.target as HTMLAnchorElement).classList.contains("back") ||
            ((event.target as HTMLElement).parentNode as HTMLAnchorElement).classList.contains("back")
        ) {
            this.close() // Hides The Computer Maintenance Dialog
        }
    })

    continue_computer_cleaning.addEventListener("click", () => computer_cleaning_dialog.showModal()) // Shows The Computer Cleaning Dialog

    // Computer Cleaning Dialog Click Functionality
    computer_cleaning_dialog.addEventListener("click", function(event:PointerEvent):void {
        const reserve_service_dimensions:DOMRect = (this.querySelector(".reserve_service") as HTMLDivElement).getBoundingClientRect() // Gets The Reserve Service Container Dimensions

        if (
            event.clientX < reserve_service_dimensions.left ||
            event.clientX > reserve_service_dimensions.right ||
            event.clientY < reserve_service_dimensions.top ||
            event.clientY > reserve_service_dimensions.bottom ||
            (event.target as HTMLAnchorElement).classList.contains("back") ||
            ((event.target as HTMLElement).parentNode as HTMLAnchorElement).classList.contains("back")
        ) {
            this.close() // Hides The Computer Cleaning Dialog
        }
    })
})