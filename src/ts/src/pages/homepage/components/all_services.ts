declare global {
    interface Window {
        Packeta:any,
        DPDPickupWidget:any
    }
}

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

    // Functions

    // Function For Initialize The Contact Section Validity (Enables Or Disables The Button)
    function initializeContactSectionValidity(contact_section:HTMLDivElement):void {
        const all_required_inputs:NodeListOf<HTMLInputElement> = contact_section.querySelectorAll<HTMLInputElement>("input[required]") // Gets The All Required Inputs
        const button:HTMLButtonElement = contact_section.querySelector(".next") as HTMLButtonElement || contact_section.querySelector(".pay") as HTMLButtonElement // Gets The Next Or Pay Button

        if([...all_required_inputs].every(one_required_input => one_required_input.checkValidity())) button.disabled = false // Enables The Button

        else {
            // Highlights The Invalid Input
            all_required_inputs.forEach(function(one_required_input:HTMLInputElement) {
                // Checks If The Input Is Filled, Isn't Valid And Isn't Focused
                if(one_required_input.value.trim() !== "" && !one_required_input.checkValidity() && document.activeElement !== one_required_input) {
                    one_required_input.style.borderColor = "#df3535"
                    one_required_input.style.boxShadow = "0 0 0 3px rgba(223, 53, 53, 0.2)"
                }

                else one_required_input.removeAttribute("style") // Removes The Styles
            })

            button.disabled = true // Disables The Button
        }
    }

    // Function For Initialize The Delivery Section Validity (Enables Or Disables The Button)
    function initializeDeliverySectionValidity(delivery_section:HTMLDivElement):void {
        const all_required_inputs:NodeListOf<HTMLInputElement> = delivery_section.querySelectorAll<HTMLInputElement>("input[required]") // Gets The All Required Inputs
        const all_shipping_method_buttons:NodeListOf<HTMLInputElement> = delivery_section.querySelectorAll<HTMLInputElement>(".shipping_method") // Gets All Shipping Method Buttons
        const button:HTMLButtonElement = delivery_section.querySelector(".next") as HTMLButtonElement || delivery_section.querySelector(".pay") as HTMLButtonElement // Gets The Next Or Pay Button

        // Checks If Are All Required Inputs Filled And If Is At Least One Shipping Method Selected
        if(
            [...all_required_inputs].every(one_required_input => one_required_input.checkValidity()) &&
            [...all_shipping_method_buttons].some(one_shipping_method_button => one_shipping_method_button.checked)
        ) {
            const selected_shipping_method_button:HTMLInputElement|null = [...all_shipping_method_buttons].find(one_shipping_method_button => one_shipping_method_button.checked) || null // Gets The Selected Shipping Method Button

            if(selected_shipping_method_button) {
                const shipping_method_container:HTMLDivElement = selected_shipping_method_button.closest(".shipping_method_container") as HTMLDivElement // Gets The Shipping Method Container
                const shipping_details:HTMLDivElement = shipping_method_container.querySelector(".shipping_details") as HTMLDivElement // Gets The Shipping Details Container

                if(shipping_method_container.classList.contains("packeta")) {
                    const packeta_branch_id:HTMLInputElement = shipping_details.querySelector<HTMLInputElement>(".packeta_branch_id") as HTMLInputElement // Gets The Packeta Branch ID Hidden Input
                    const packeta_branch_name:HTMLInputElement = shipping_details.querySelector<HTMLInputElement>(".packeta_branch_name") as HTMLInputElement // Gets The Packeta Branch Name Hidden Input

                    if(packeta_branch_id.value.trim() !== "" && packeta_branch_name.value.trim() !== "") {
                        button.disabled = false // Enables The Button
                    }
                }

                else if(shipping_method_container.classList.contains("dpd")) {
                    const dpd_branch_id:HTMLInputElement = shipping_details.querySelector<HTMLInputElement>(".dpd_branch_id") as HTMLInputElement // Gets The DPD Branch ID Hidden Input
                    const dpd_branch_name:HTMLInputElement = shipping_details.querySelector<HTMLInputElement>(".dpd_branch_name") as HTMLInputElement // Gets The DPD Branch Name Hidden Input

                    if(dpd_branch_id.value.trim() !== "" && dpd_branch_name.value.trim() !== "") {
                        button.disabled = false // Enables The Button
                    }
                }

                else if(shipping_method_container.classList.contains("gls")) {
                    button.disabled = false // Enables The Button
                }
            }
        }

        else button.disabled = true // Disables The Button
    }

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

        // Delivery Section

        if((event.target as HTMLElement).closest(".delivery_section")) {
            // Packeta

            const delivery_section:HTMLDivElement = (event.target as HTMLElement).closest(".delivery_section") as HTMLDivElement // Gets The Delivery Section
            const packeta_api_key:string = "1e86dc1a3e6601cb" // Stores The Packeta API Key
            const all_shipping_method_buttons:NodeListOf<HTMLInputElement> = delivery_section.querySelectorAll<HTMLInputElement>(".shipping_method") // Gets All Shipping Method Buttons
            const all_shipping_details:NodeListOf<HTMLDivElement> = delivery_section.querySelectorAll<HTMLDivElement>('.shipping_details') // Gets All Shipping Details Containers

            all_shipping_method_buttons.forEach(function(one_shipping_method_button:HTMLInputElement):void {
                // One Shipping Method Button Change Functionality
                one_shipping_method_button.addEventListener("change", function(event:Event):void {
                    const shipping_method_button:HTMLInputElement = event.target as HTMLInputElement // Gets The Shipping Method Button
                    const shipping_method_container:HTMLDivElement = shipping_method_button.closest(".shipping_method_container") as HTMLDivElement // Gets The Shipping Method Container
                    const shipping_details:HTMLDivElement = shipping_method_container.querySelector(".shipping_details") as HTMLDivElement // Gets The Shipping Details Container

                    all_shipping_details.forEach(one_shipping_detail => one_shipping_detail.style.display = "none") // Hides Every Shipping Details Container
                    if(shipping_details) shipping_details.style.display = "block" // Shows The Shipping Details Container
                })
            })

            const open_packeta_widget:HTMLButtonElement = delivery_section.querySelector<HTMLButtonElement>(".open_packeta_widget") as HTMLButtonElement // Gets The Open Packeta Widget Button
            const packeta_widget_container:HTMLDivElement = delivery_section.querySelector(".packeta_widget_container") as HTMLDivElement // Gets The Packeta Widget Container

            if(open_packeta_widget && packeta_widget_container) {
                // Open Packeta Widget Button Click Functionality
                open_packeta_widget.addEventListener("click", function():void {
                    packeta_widget_container.style.display = "block" // Shows The Packeta Widget Container

                    // Stores The Packeta Options
                    const options:{
                        country:string,
                        language:string
                    } = {
                        country: "sk",
                        language: "sk"
                    }

                    // Renders The Packeta Widget
                    window.Packeta.Widget.pick(
                        packeta_api_key,

                        (pickup_point:any) => {
                            if(pickup_point) {
                                const selected_packeta_branch_name:HTMLParagraphElement = delivery_section.querySelector(".selected_packeta_branch_name") as HTMLParagraphElement // Gets The Selected Packeta Branch Name
                                const packeta_branch_id:HTMLInputElement = delivery_section.querySelector<HTMLInputElement>(".packeta_branch_id") as HTMLInputElement // Gets The Packeta Branch ID Hidden Input
                                const packeta_branch_name:HTMLInputElement = delivery_section.querySelector<HTMLInputElement>(".packeta_branch_name") as HTMLInputElement // Gets The Packeta Branch Name Hidden Input

                                if(selected_packeta_branch_name) selected_packeta_branch_name.innerText = "Vybrané: " + pickup_point.name // Shows The Selected Packeta Branch Name
                                if(packeta_branch_id) packeta_branch_id.value = pickup_point.id // Stores The Packeta Branch ID
                                if(packeta_branch_name) packeta_branch_name.value = pickup_point.name // Stores The Packeta Branch Name

                                packeta_widget_container.style.display = "none" // Hides The Packeta Widget Container
                                initializeDeliverySectionValidity(delivery_section) // Initializes The Delivery Section Validity
                            }
                        },

                        options,
                        packeta_widget_container
                    )
                })
            }
        }
    })

    computer_building_dialog.addEventListener("input", function(event:InputEvent):void {
        // Contact Section

        if((event.target as HTMLElement).closest(".contact_section")) {
            const contact_section:HTMLDivElement = (event.target as HTMLElement).closest(".contact_section") as HTMLDivElement // Gets The contact Section
            initializeContactSectionValidity(contact_section) // Initializes The Contact Section Validity
        }

        // Delivery Section

        if((event.target as HTMLElement).closest(".delivery_section")) {
            const delivery_section:HTMLDivElement = (event.target as HTMLElement).closest(".delivery_section") as HTMLDivElement // Gets The Delivery Section
            initializeDeliverySectionValidity(delivery_section) // Initializes The Delivery Section Validity
        }

        // Computer Preferences Section

        // Max Price
        if((event.target as HTMLInputElement).classList.contains("max_price")) {
            const max_price_container:HTMLDivElement = (event.target as HTMLInputElement).closest(".max_price_container") as HTMLDivElement // Gets The Max Price Container
            const max_price:HTMLInputElement = max_price_container.querySelector(".max_price") as HTMLInputElement // Gets The Max Price Input
            const max_price_label:HTMLParagraphElement = max_price_container.querySelector(".max_price_label") as HTMLParagraphElement // Gets The Max Price Label

            max_price_label.innerHTML = `${max_price.value}<span>€</span>` // Shows The Current Max Price Value
        }

        // RAM
        if((event.target as HTMLInputElement).classList.contains("ram")) {
            const ram_container:HTMLDivElement = (event.target as HTMLInputElement).closest(".ram_container") as HTMLDivElement // Gets The Max Price Container
            const ram:HTMLInputElement = ram_container.querySelector(".ram") as HTMLInputElement // Gets The Max Price Input
            const ram_label:HTMLParagraphElement = ram_container.querySelector(".ram_label") as HTMLParagraphElement // Gets The Max Price Label

            // Maps The RAM Capicity Options
            const RAM_CAPACITY = {
                0: 8,
                1: 16,
                2: 32,
                3: 64,
                4: 128,
                5: 256
            }

            ram_label.innerHTML = `${RAM_CAPACITY[Number(ram.value) as keyof typeof RAM_CAPACITY]}<span>GB</span>` // Shows The Current Max Price Value
        }

        // Space
        if((event.target as HTMLInputElement).classList.contains("space")) {
            const space_container:HTMLDivElement = (event.target as HTMLInputElement).closest(".space_container") as HTMLDivElement // Gets The Max Price Container
            const space:HTMLInputElement = space_container.querySelector(".space") as HTMLInputElement // Gets The Max Price Input
            const space_label:HTMLParagraphElement = space_container.querySelector(".space_label") as HTMLParagraphElement // Gets The Max Price Label

            // Maps The Space Capicity Options
            const SPACE_CAPACITY = {
                0: 128,
                1: 256,
                2: 512,
                3: 1024,
                4: 2048,
                5: 4096
            }

            space_label.innerHTML = `${SPACE_CAPACITY[Number(space.value) as keyof typeof SPACE_CAPACITY]}<span>GB</span>` // Shows The Current Max Price Value
        }

        // Next Or Pay Button Activation
        if(
            (event.target as HTMLInputElement).closest(".cpu_manufacturer_container") ||
            (event.target as HTMLInputElement).closest(".gpu_manufacturer_container") ||
            (event.target as HTMLInputElement).closest(".disk_type_container")
        ) {
            const computer_preferences_section:HTMLDivElement = (event.target as HTMLInputElement).closest(".section") as HTMLDivElement // Gets The Computer Preferences Section
            const cpu_manufacturer_container:HTMLDivElement = computer_preferences_section.querySelector(".cpu_manufacturer_container") as HTMLDivElement // Gets The CPU Manufacturer Container
            const gpu_manufacturer_container:HTMLDivElement = computer_preferences_section.querySelector(".gpu_manufacturer_container") as HTMLDivElement // Gets The GPU Manufacturer Container
            const disk_type_container:HTMLDivElement = computer_preferences_section.querySelector(".disk_type_container") as HTMLDivElement // Gets The Disk Type Container
            const button:HTMLButtonElement = computer_preferences_section.querySelector(".next") as HTMLButtonElement || computer_preferences_section.querySelector(".pay") as HTMLButtonElement // Gets The Next Or Pay Button

            // Checks If Is At Least One Checkbox From Every Container Checked
            if(
                [...cpu_manufacturer_container.querySelectorAll<HTMLInputElement>("input[type='checkbox']")].some(one_checkbox => one_checkbox.checked) &&
                [...gpu_manufacturer_container.querySelectorAll<HTMLInputElement>("input[type='checkbox']")].some(one_checkbox => one_checkbox.checked) &&
                [...disk_type_container.querySelectorAll<HTMLInputElement>("input[type='checkbox']")].some(one_checkbox => one_checkbox.checked)
            ) {
                button.disabled = false // Enables The Button
            }

            else button.disabled = true // Disables The Button
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

        // Delivery Section

        if((event.target as HTMLElement).closest(".delivery_section")) {
            // Packeta

            const delivery_section:HTMLDivElement = (event.target as HTMLElement).closest(".delivery_section") as HTMLDivElement // Gets The Delivery Section
            const packeta_api_key:string = "1e86dc1a3e6601cb" // Stores The Packeta API Key
            const all_shipping_method_buttons:NodeListOf<HTMLInputElement> = delivery_section.querySelectorAll<HTMLInputElement>(".shipping_method") // Gets All Shipping Method Buttons
            const all_shipping_details:NodeListOf<HTMLDivElement> = delivery_section.querySelectorAll<HTMLDivElement>('.shipping_details') // Gets All Shipping Details Containers

            all_shipping_method_buttons.forEach(function(one_shipping_method_button:HTMLInputElement):void {
                // One Shipping Method Button Change Functionality
                one_shipping_method_button.addEventListener("change", function(event:Event):void {
                    const shipping_method_button:HTMLInputElement = event.target as HTMLInputElement // Gets The Shipping Method Button
                    const shipping_method_container:HTMLDivElement = shipping_method_button.closest(".shipping_method_container") as HTMLDivElement // Gets The Shipping Method Container
                    const shipping_details:HTMLDivElement = shipping_method_container.querySelector(".shipping_details") as HTMLDivElement // Gets The Shipping Details Container

                    all_shipping_details.forEach(one_shipping_detail => one_shipping_detail.style.display = "none") // Hides Every Shipping Details Container
                    if(shipping_details) shipping_details.style.display = "block" // Shows The Shipping Details Container
                })
            })

            const open_packeta_widget:HTMLButtonElement = delivery_section.querySelector<HTMLButtonElement>(".open_packeta_widget") as HTMLButtonElement // Gets The Open Packeta Widget Button
            const packeta_widget_container:HTMLDivElement = delivery_section.querySelector(".packeta_widget_container") as HTMLDivElement // Gets The Packeta Widget Container

            if(open_packeta_widget && packeta_widget_container) {
                // Open Packeta Widget Button Click Functionality
                open_packeta_widget.addEventListener("click", function():void {
                    packeta_widget_container.style.display = "block" // Shows The Packeta Widget Container

                    // Stores The Packeta Options
                    const options:{
                        country:string,
                        language:string
                    } = {
                        country: "sk",
                        language: "sk"
                    }

                    // Renders The Packeta Widget
                    window.Packeta.Widget.pick(
                        packeta_api_key,

                        (pickup_point:any) => {
                            if(pickup_point) {
                                const selected_packeta_branch_name:HTMLParagraphElement = delivery_section.querySelector(".selected_packeta_branch_name") as HTMLParagraphElement // Gets The Selected Packeta Branch Name
                                const packeta_branch_id:HTMLInputElement = delivery_section.querySelector<HTMLInputElement>(".packeta_branch_id") as HTMLInputElement // Gets The Packeta Branch ID Hidden Input
                                const packeta_branch_name:HTMLInputElement = delivery_section.querySelector<HTMLInputElement>(".packeta_branch_name") as HTMLInputElement // Gets The Packeta Branch Name Hidden Input

                                if(selected_packeta_branch_name) selected_packeta_branch_name.innerText = "Vybrané: " + pickup_point.name // Shows The Selected Packeta Branch Name
                                if(packeta_branch_id) packeta_branch_id.value = pickup_point.id // Stores The Packeta Branch ID
                                if(packeta_branch_name) packeta_branch_name.value = pickup_point.name // Stores The Packeta Branch Name

                                packeta_widget_container.style.display = "none" // Hides The Packeta Widget Container
                                initializeDeliverySectionValidity(delivery_section) // Initializes The Delivery Section Validity
                            }
                        },

                        options,
                        packeta_widget_container
                    )
                })
            }
        }
    })

    computer_maintenance_dialog.addEventListener("input", function(event:InputEvent):void {
        // Contact Section

        if((event.target as HTMLElement).closest(".contact_section")) {
            const contact_section:HTMLDivElement = (event.target as HTMLElement).closest(".contact_section") as HTMLDivElement // Gets The contact Section
            initializeContactSectionValidity(contact_section) // Initializes The Contact Section Validity
        }

        // Delivery Section

        if((event.target as HTMLElement).closest(".delivery_section")) {
            const delivery_section:HTMLDivElement = (event.target as HTMLElement).closest(".delivery_section") as HTMLDivElement // Gets The Delivery Section
            initializeDeliverySectionValidity(delivery_section) // Initializes The Delivery Section Validity
        }

        // Computer Info Section

        // // Max Price
        // if((event.target as HTMLInputElement).classList.contains("max_price")) {
        //     const max_price_container:HTMLDivElement = (event.target as HTMLInputElement).closest(".max_price_container") as HTMLDivElement // Gets The Max Price Container
        //     const max_price:HTMLInputElement = max_price_container.querySelector(".max_price") as HTMLInputElement // Gets The Max Price Input
        //     const max_price_label:HTMLParagraphElement = max_price_container.querySelector(".max_price_label") as HTMLParagraphElement // Gets The Max Price Label

        //     max_price_label.innerHTML = `${max_price.value}<span>€</span>` // Shows The Current Max Price Value
        // }

        // // RAM
        // if((event.target as HTMLInputElement).classList.contains("ram")) {
        //     const ram_container:HTMLDivElement = (event.target as HTMLInputElement).closest(".ram_container") as HTMLDivElement // Gets The Max Price Container
        //     const ram:HTMLInputElement = ram_container.querySelector(".ram") as HTMLInputElement // Gets The Max Price Input
        //     const ram_label:HTMLParagraphElement = ram_container.querySelector(".ram_label") as HTMLParagraphElement // Gets The Max Price Label

        //     // Maps The RAM Capicity Options
        //     const RAM_CAPACITY = {
        //         0: 8,
        //         1: 16,
        //         2: 32,
        //         3: 64,
        //         4: 128,
        //         5: 256
        //     }

        //     ram_label.innerHTML = `${RAM_CAPACITY[Number(ram.value) as keyof typeof RAM_CAPACITY]}<span>GB</span>` // Shows The Current Max Price Value
        // }

        // // Space
        // if((event.target as HTMLInputElement).classList.contains("space")) {
        //     const space_container:HTMLDivElement = (event.target as HTMLInputElement).closest(".space_container") as HTMLDivElement // Gets The Max Price Container
        //     const space:HTMLInputElement = space_container.querySelector(".space") as HTMLInputElement // Gets The Max Price Input
        //     const space_label:HTMLParagraphElement = space_container.querySelector(".space_label") as HTMLParagraphElement // Gets The Max Price Label

        //     // Maps The Space Capicity Options
        //     const SPACE_CAPACITY = {
        //         0: 128,
        //         1: 256,
        //         2: 512,
        //         3: 1024,
        //         4: 2048,
        //         5: 4096
        //     }

        //     space_label.innerHTML = `${SPACE_CAPACITY[Number(space.value) as keyof typeof SPACE_CAPACITY]}<span>GB</span>` // Shows The Current Max Price Value
        // }

        // // Next Or Pay Button Activation
        // if(
        //     (event.target as HTMLInputElement).closest(".cpu_manufacturer_container") ||
        //     (event.target as HTMLInputElement).closest(".gpu_manufacturer_container") ||
        //     (event.target as HTMLInputElement).closest(".disk_type_container")
        // ) {
        //     const computer_preferences_section:HTMLDivElement = (event.target as HTMLInputElement).closest(".section") as HTMLDivElement // Gets The Computer Preferences Section
        //     const cpu_manufacturer_container:HTMLDivElement = computer_preferences_section.querySelector(".cpu_manufacturer_container") as HTMLDivElement // Gets The CPU Manufacturer Container
        //     const gpu_manufacturer_container:HTMLDivElement = computer_preferences_section.querySelector(".gpu_manufacturer_container") as HTMLDivElement // Gets The GPU Manufacturer Container
        //     const disk_type_container:HTMLDivElement = computer_preferences_section.querySelector(".disk_type_container") as HTMLDivElement // Gets The Disk Type Container
        //     const button:HTMLButtonElement = computer_preferences_section.querySelector(".next") as HTMLButtonElement || computer_preferences_section.querySelector(".pay") as HTMLButtonElement // Gets The Next Or Pay Button

        //     // Checks If Is At Least One Checkbox From Every Container Checked
        //     if(
        //         [...cpu_manufacturer_container.querySelectorAll<HTMLInputElement>("input[type='checkbox']")].some(one_checkbox => one_checkbox.checked) &&
        //         [...gpu_manufacturer_container.querySelectorAll<HTMLInputElement>("input[type='checkbox']")].some(one_checkbox => one_checkbox.checked) &&
        //         [...disk_type_container.querySelectorAll<HTMLInputElement>("input[type='checkbox']")].some(one_checkbox => one_checkbox.checked)
        //     ) {
        //         button.disabled = false // Enables The Button
        //     }

        //     else button.disabled = true // Disables The Button
        // }
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

        // Delivery Section

        if((event.target as HTMLElement).closest(".delivery_section")) {
            // Packeta

            const delivery_section:HTMLDivElement = (event.target as HTMLElement).closest(".delivery_section") as HTMLDivElement // Gets The Delivery Section
            const packeta_api_key:string = "1e86dc1a3e6601cb" // Stores The Packeta API Key
            const all_shipping_method_buttons:NodeListOf<HTMLInputElement> = delivery_section.querySelectorAll<HTMLInputElement>(".shipping_method") // Gets All Shipping Method Buttons
            const all_shipping_details:NodeListOf<HTMLDivElement> = delivery_section.querySelectorAll<HTMLDivElement>('.shipping_details') // Gets All Shipping Details Containers

            all_shipping_method_buttons.forEach(function(one_shipping_method_button:HTMLInputElement):void {
                // One Shipping Method Button Change Functionality
                one_shipping_method_button.addEventListener("change", function(event:Event):void {
                    const shipping_method_button:HTMLInputElement = event.target as HTMLInputElement // Gets The Shipping Method Button
                    const shipping_method_container:HTMLDivElement = shipping_method_button.closest(".shipping_method_container") as HTMLDivElement // Gets The Shipping Method Container
                    const shipping_details:HTMLDivElement = shipping_method_container.querySelector(".shipping_details") as HTMLDivElement // Gets The Shipping Details Container

                    all_shipping_details.forEach(one_shipping_detail => one_shipping_detail.style.display = "none") // Hides Every Shipping Details Container
                    if(shipping_details) shipping_details.style.display = "block" // Shows The Shipping Details Container
                })
            })

            const open_packeta_widget:HTMLButtonElement = delivery_section.querySelector<HTMLButtonElement>(".open_packeta_widget") as HTMLButtonElement // Gets The Open Packeta Widget Button
            const packeta_widget_container:HTMLDivElement = delivery_section.querySelector(".packeta_widget_container") as HTMLDivElement // Gets The Packeta Widget Container

            if(open_packeta_widget && packeta_widget_container) {
                // Open Packeta Widget Button Click Functionality
                open_packeta_widget.addEventListener("click", function():void {
                    packeta_widget_container.style.display = "block" // Shows The Packeta Widget Container

                    // Stores The Packeta Options
                    const options:{
                        country:string,
                        language:string
                    } = {
                        country: "sk",
                        language: "sk"
                    }

                    // Renders The Packeta Widget
                    window.Packeta.Widget.pick(
                        packeta_api_key,

                        (pickup_point:any) => {
                            if(pickup_point) {
                                const selected_packeta_branch_name:HTMLParagraphElement = delivery_section.querySelector(".selected_packeta_branch_name") as HTMLParagraphElement // Gets The Selected Packeta Branch Name
                                const packeta_branch_id:HTMLInputElement = delivery_section.querySelector<HTMLInputElement>(".packeta_branch_id") as HTMLInputElement // Gets The Packeta Branch ID Hidden Input
                                const packeta_branch_name:HTMLInputElement = delivery_section.querySelector<HTMLInputElement>(".packeta_branch_name") as HTMLInputElement // Gets The Packeta Branch Name Hidden Input

                                if(selected_packeta_branch_name) selected_packeta_branch_name.innerText = "Vybrané: " + pickup_point.name // Shows The Selected Packeta Branch Name
                                if(packeta_branch_id) packeta_branch_id.value = pickup_point.id // Stores The Packeta Branch ID
                                if(packeta_branch_name) packeta_branch_name.value = pickup_point.name // Stores The Packeta Branch Name

                                packeta_widget_container.style.display = "none" // Hides The Packeta Widget Container
                                initializeDeliverySectionValidity(delivery_section) // Initializes The Delivery Section Validity
                            }
                        },

                        options,
                        packeta_widget_container
                    )
                })
            }
        }
    })

    computer_cleaning_dialog.addEventListener("input", function(event:InputEvent):void {
        // Contact Section

        if((event.target as HTMLElement).closest(".contact_section")) {
            const contact_section:HTMLDivElement = (event.target as HTMLElement).closest(".contact_section") as HTMLDivElement // Gets The contact Section
            initializeContactSectionValidity(contact_section) // Initializes The Contact Section Validity
        }

        // Delivery Section

        if((event.target as HTMLElement).closest(".delivery_section")) {
            const delivery_section:HTMLDivElement = (event.target as HTMLElement).closest(".delivery_section") as HTMLDivElement // Gets The Delivery Section
            initializeDeliverySectionValidity(delivery_section) // Initializes The Delivery Section Validity
        }
    })

    // Initialization

    initializeContactSectionValidity(computer_building_dialog.querySelector(".contact_section") as HTMLDivElement) // Initializes The Contact Section Validity
})