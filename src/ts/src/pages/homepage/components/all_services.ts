import { CheckoutWizard } from "../../../components/checkout_wizard.js"

"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // All Services

    // Initialization

    // Initializes One Checkout Wizard For Every Service Form That Contains A Checkout Dialog
    document.querySelectorAll<HTMLFormElement>(".all_services form").forEach(function(one_form:HTMLFormElement):void {
        if(one_form.querySelector("[data-checkout]")) new CheckoutWizard(one_form)
    })
})
