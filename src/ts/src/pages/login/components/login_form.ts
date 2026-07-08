import { 
    showPassword, 
    hidePassword 
} from "../../../utils/password.js"

"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Toggle Show / Hide Password

    // Variables

    const login_form:HTMLFormElement = document.querySelector(".login .login_form") as HTMLFormElement // Gets The Login Form
    const password_container:HTMLDivElement = login_form.querySelector(".password_container") as HTMLDivElement // Gets The Password Container
    const show_hide_password:HTMLButtonElement = password_container.querySelector(".show_hide_password") as HTMLButtonElement // Gets The Show Hide Password Button
    const password_input:HTMLInputElement = password_container.querySelector(".password") as HTMLInputElement // Gets The Password Input

    // Events

    // Show Hide Password Click Functionality
    show_hide_password.addEventListener("click", function():void {
        const icon:HTMLElement = this.querySelector("i") as HTMLElement // Gets The Icon
        
        if(icon.matches(".fa-eye-slash")) showPassword(this, password_input) // Shows The Password
        else if(icon.matches(".fa-eye")) hidePassword(this, password_input) // Hides The Password
    })

    // Password Reset

    // Variables

    const email_address:HTMLInputElement = login_form.querySelector(".email_address") as HTMLInputElement // Gets The Email Address Input
    const form_report:HTMLParagraphElement = login_form.querySelector(".form_report") as HTMLParagraphElement // Gets The Form Report Paragraph
    const password_reset:HTMLAnchorElement = login_form.querySelector(".forgot_password p a") as HTMLAnchorElement // Gets The Password Reset Button

    // Events

    // Password Reset Click Functionality
    password_reset.addEventListener("click", function(event:PointerEvent):void {
        // If E-mail Was Entered
        if(email_address.value !== "" && email_address.value.includes("@") && email_address.value.includes(".")) {
            form_report.textContent = "" // Removes The Form Report Message
            document.cookie = `email_address=${email_address.value}; max-age=` + 60 * 10 + "; path=/"; // Creates 10 Minutes Timed Cookie With User's Email Address
        }

        // If E-mail Wasn't Entered
        else {
            event.preventDefault() // Prevents Default Behaviour
            form_report.textContent = "Zadajte váš e-mail." // Shows Attachment Report Message
            form_report.classList.add("invalid") // Adds The Invalid Class To The Form Report Paragraph
        }
    })

    // Remember Me

    // Variables

    const news:HTMLInputElement = login_form.querySelector(".agree .remember_me") as HTMLInputElement // Gets The Remember Me Input
    const news_label:HTMLLabelElement = login_form.querySelector(".agree .remember_me_label") as HTMLLabelElement // Gets The Remember Me Label

    // Events

    // News Label Keydown Functionality
    news_label.addEventListener("keydown", function(event:KeyboardEvent):void {
        if(event.key === "Enter") news.checked = !news.checked // Inverts The News Input Check State
    })
})