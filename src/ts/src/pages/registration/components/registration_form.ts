import { 
    passwordVerification,
    showPassword, 
    hidePassword,
    copy, 
    paste 
} from "../../../utils/password.js"

import { generateKey } from "../../../utils/generateKey.js"

"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Phone Number Country Flag

    // Variables

    const registration_form:HTMLFormElement = document.querySelector(".registration .registration_form") as HTMLFormElement // Gets The Registration Form
    const phone_number_container:HTMLDivElement = registration_form.querySelector(".phone_number_container") as HTMLDivElement // Gets The Phone Number Container
    const phone_number_icon_container:HTMLDivElement = phone_number_container.querySelector(".phone_number_icon") as HTMLDivElement // Gets The Phone Number Icon Container
    const slovakia_icon:HTMLImageElement = phone_number_icon_container.querySelector(".slovakia_icon") as HTMLImageElement // Gets The Slovakia Flag Icon
    const czech_republic_icon:HTMLImageElement = phone_number_icon_container.querySelector(".czech_republic_icon") as HTMLImageElement // Gets The Czech Republic Flag Icon
    const spain_icon:HTMLImageElement = phone_number_icon_container.querySelector(".spain_icon") as HTMLImageElement // Gets The Spain Flag Icon
    const united_states_icon:HTMLImageElement = phone_number_icon_container.querySelector(".united_states_icon") as HTMLImageElement // Gets The United States Flag Icon
    const phone_number_input:HTMLInputElement = phone_number_container.querySelector(".phone_number") as HTMLInputElement // Gets The Phone Number Input

    // Events

    // Phone Number Input Functionality
    phone_number_input.addEventListener("input", function():void {
        if(this.value.includes("+421") || this.value.includes("09")) slovakia_icon.style.visibility = "visible" // Shows The Slovakia Flag Icon
        else if(this.value.includes("+420")) czech_republic_icon.style.visibility = "visible" // Shows The Czech Republic Flag Icon
        else if(this.value.includes("+34")) spain_icon.style.visibility = "visible" // Shows The Spain Flag Icon
        else if(this.value.includes("+1")) united_states_icon.style.visibility = "visible" // Shows The United States Flag Icon

        else {
            slovakia_icon.style.visibility = "hidden" // Hides The Slovakia Flag Icon
            czech_republic_icon.style.visibility = "hidden" // Hides The Czech Republic Flag Icon
            spain_icon.style.visibility = "hidden" // Hides The Spain Flag Icon
            united_states_icon.style.visibility = "hidden" // Hides The United States Flag Icon
        }
    })

    // Password Verification

    // Variables

    const password_container:HTMLDivElement = registration_form.querySelector(".password_container") as HTMLDivElement // Gets The Password Container
    const password_input:HTMLInputElement = password_container.querySelector(".password") as HTMLInputElement // Gets The Password Input
    const password_check_container:HTMLDivElement = registration_form.querySelector(".password_check_container") as HTMLDivElement // Gets The Password Check Container
    const password_check_input:HTMLInputElement = password_check_container.querySelector(".password_check") as HTMLInputElement // Gets The Password Check Input

    const form_report:HTMLParagraphElement = registration_form.querySelector(".form_report") as HTMLParagraphElement // Gets The Form Report Paragraph

    // Events

    password_input.addEventListener("input", () => passwordVerification(password_input, password_check_input, form_report)) // Verifies The Password)
    password_check_input.addEventListener("input", () => passwordVerification(password_input, password_check_input, form_report)) // Verifies The Password
    
    // Random Password Generator

    // Variables

    const generate_password:HTMLButtonElement = registration_form.querySelector(".generate_password") as HTMLButtonElement // Gets The Generate Password Button

    // Events

    // Generate Password Click Functionality
    generate_password.addEventListener("click", function():void {
        password_input.value = generateKey(15) // Sets Generated Password To The Password Input
        passwordVerification(password_input, password_check_input, form_report) // Verifies The Password
    })

    // Toggle Show / Hide Password

    // Variables

    const show_hide_password:HTMLButtonElement = password_container.querySelector(".show_hide_password") as HTMLButtonElement // Gets The Show Hide Password Button

    // Events

    // Show Hide Password Click Functionality
    show_hide_password.addEventListener("click", function():void {
        const icon:HTMLElement = this.querySelector("i") as HTMLElement // Gets The Icon
        
        if(icon.matches(".fa-eye-slash")) showPassword(this, password_input) // Shows The Password
        else if(icon.matches(".fa-eye")) hidePassword(this, password_input) // Hides The Password
    })

    // Copy Password

    // Variables

    const copy_password:HTMLButtonElement = password_container.querySelector(".copy_password") as HTMLButtonElement // Gets The Copy Password Button
    let copied_password:string = "" // Stores The Copied Password

    // Events

    // Copy Password Click Functionality
    copy_password.addEventListener("click", function():void {
        copied_password = copy(password_input) // Copies Value In The Input And Stores It To The Variable
    })

    // Paste Password

    // Variables

    const paste_password:HTMLButtonElement = password_check_container.querySelector(".paste_password") as HTMLButtonElement // Gets The Paste Password Button

    // Events

    // Paste Password Click Functionality
    paste_password.addEventListener("click", function():void {
        paste(password_check_input, copied_password) // Paste Password To The Password Check Input
        passwordVerification(password_input, password_check_input, form_report) // Verifies The Password
    })

    // Terms Check

    // Variables

    const registration_form_submit:HTMLButtonElement = registration_form.querySelector(".registration_form_submit") as HTMLButtonElement // Gets The Registration Form Submit Button
    const agree:HTMLDivElement = registration_form.querySelector(".agree") as HTMLDivElement // Gets The Agree Container
    const terms:HTMLInputElement = agree.querySelector(".terms") as HTMLInputElement // Gets The Terms Input
    const terms_label:HTMLLabelElement = agree.querySelector(".terms_label") as HTMLLabelElement // Gets The Terms Label
    const news:HTMLInputElement = agree.querySelector(".news") as HTMLInputElement // Gets The News Input
    const news_label:HTMLLabelElement = agree.querySelector(".news_label") as HTMLLabelElement // Gets The News Label

    // Events

    // Registration Form Submit Button Click Functionality
    registration_form_submit.addEventListener("click", function():void {
        if(!terms.checked) terms_label.style.color = "rgb(250, 50, 50)"
    })

    // Terms Input Functionality
    terms.addEventListener("change", function():void {
        if(terms.checked) terms_label.style.color = "rgb(180, 180, 180)"
    })

    // Terms Label Keydown Functionality
    terms_label.addEventListener("keydown", function(event:KeyboardEvent):void {
        if(event.key === "Enter") terms.checked = !terms.checked // Inverts The Terms Input Check State
    })

    // News Label Keydown Functionality
    news_label.addEventListener("keydown", function(event:KeyboardEvent):void {
        if(event.key === "Enter") news.checked = !news.checked // Inverts The News Input Check State
    })
})