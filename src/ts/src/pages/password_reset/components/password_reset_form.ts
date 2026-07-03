import { 
    showPassword, 
    hidePassword
} from "../../../utils/password.js"

import { generateKey } from "../../../utils/generateKey.js"

"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Password Length Verification

    // Variables

    const password_reset_form:HTMLFormElement = document.querySelector(".password_reset .password_reset_form") as HTMLFormElement // Gets The Password Reset Form
    const new_password_container:HTMLDivElement = password_reset_form.querySelector(".new_password_container") as HTMLDivElement // Gets The New Password Container
    const new_password:HTMLInputElement = new_password_container.querySelector(".new_password") as HTMLInputElement // Gets The New Password Input
    const form_report:HTMLParagraphElement = password_reset_form.querySelector(".form_report") as HTMLParagraphElement // Gets The Form Report Paragraph

    // Events

    // New Password Input Functionality
    new_password.addEventListener("input", function():void {
        if(new_password.value.length < 8 && new_password.value.length > 0) {
            form_report.textContent = "Heslo je príliš krátke." // Shows The Form Report Message
            form_report.classList.add("invalid") // Adds Invalid Class To The Form Report Paragraph
            form_report.classList.remove("valid") // Removes Valid Class From The Form Report Paragraph
        }

        else form_report.textContent = "" // Removes The Form Report Message
    })

    // Random Password Generator

    // Variables
    
    const generate_password:HTMLButtonElement = new_password_container.querySelector(".generate_password") as HTMLButtonElement // Gets The Generate Password Button

    // Events

    // Generate Password Click Functionality
    generate_password.addEventListener("click", function():void {
        new_password.value = generateKey(15) // Sets Generated Password To The Password Input
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