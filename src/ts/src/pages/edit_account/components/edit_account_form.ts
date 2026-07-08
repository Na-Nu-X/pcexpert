import { 
    passwordVerification,
    showPassword, 
    hidePassword
} from "../../../utils/password.js"

import { generateKey } from "../../../utils/generateKey.js"

"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Delete Profile Picture Warning

    // Variables

    const edit_account_form:HTMLFormElement = document.querySelector(".edit_account .edit_account_form") as HTMLFormElement // Gets The Edit Account Form
    const profile_picture_container:HTMLDivElement = edit_account_form.querySelector(".profile_picture_container") as HTMLDivElement // Gets The Profile Picture Container
    const delete_profile_picture_image:HTMLImageElement = profile_picture_container.querySelector("label .delete_profile_picture") as HTMLImageElement // Gets The Delete Profile Picture Image
    const delete_profile_picture_input:HTMLInputElement|null = profile_picture_container.querySelector("#delete_profile_picture") as HTMLInputElement || null // Gets The Delete Profile Picture Input If Is Available

    // Events
    
    // Delete Profile Picture Input Click Functionality
    if(delete_profile_picture_input) {
        delete_profile_picture_input.addEventListener("click", function():void {
            if(delete_profile_picture_input.checked) delete_profile_picture_image.style.opacity = "1"
            else delete_profile_picture_image.style.opacity = "0.8"
        })
    }
    
    // Delete Account Warning
    
    // Variables

    const delete_account_container:HTMLDivElement = edit_account_form.querySelector(".delete_account_container") as HTMLDivElement // Gets The Delete Account Container
    const delete_account_image:HTMLImageElement = delete_account_container.querySelector("label .delete_account") as HTMLImageElement // Gets The Delete Account Image
    const delete_account_input:HTMLInputElement = delete_account_container.querySelector("#delete_account") as HTMLInputElement // Gets The Delete Account Input
    const form_report:HTMLParagraphElement = edit_account_form.querySelector(".form_report") as HTMLParagraphElement // Gets The Form Report Paragraph

    // Events

    // Delete Account Input Click Functionality
    delete_account_input.addEventListener("click", function():void {
        if(delete_account_input.checked) {
            delete_account_image.style.opacity = "1"
            form_report.textContent = "Váš účet bude odstránený." // Shows The Form Report Message
            form_report.classList.add("invalid") // Adds Invalid Class To The Form Report Paragraph
        }

        else {
            delete_account_image.style.opacity = "0.8"
            form_report.textContent = "" // Removes The Form Report Message
        }
    })

    // Random Password Generator

    // Variables

    const current_password_container:HTMLDivElement = edit_account_form.querySelector(".current_password_container") as HTMLDivElement // Gets The Current Password Container
    const current_password:HTMLInputElement = current_password_container.querySelector(".current_password") as HTMLInputElement // Gets The Current Password Input
    const new_password_container:HTMLDivElement = edit_account_form.querySelector(".new_password_container") as HTMLDivElement // Gets The New Password Container
    const new_password:HTMLInputElement = new_password_container.querySelector(".new_password") as HTMLInputElement // Gets The New Password Input
    const generate_password:HTMLButtonElement = new_password_container.querySelector(".generate_password") as HTMLButtonElement // Gets The Generate Password Button

    // Events

    // Generate Password Click Functionality
    generate_password.addEventListener("click", function():void {
        new_password.value = generateKey(15) // Sets Generated Password To The Password Input
        passwordVerification(new_password, current_password, form_report) // Verifies The Password
    })

    // Toggle Show / Hide Password

    // Variables

    const show_hide_password:HTMLButtonElement = new_password_container.querySelector(".show_hide_password") as HTMLButtonElement // Gets The Show Hide Password Button

    // Events

    // Show Hide Password Click Functionality
    show_hide_password.addEventListener("click", function():void {
        const icon:HTMLElement = this.querySelector("i") as HTMLElement // Gets The Icon
        
        if(icon.matches(".fa-eye-slash")) showPassword(this, new_password) // Shows The Password
        else if(icon.matches(".fa-eye")) hidePassword(this, new_password) // Hides The Password
    })
})