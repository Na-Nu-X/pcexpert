"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Stars

    // Variables

    const edit_review_form:HTMLFormElement = document.querySelector(".edit_review .edit_review_form") as HTMLFormElement // Gets The Edit Review Form
    const stars_container:HTMLDivElement = edit_review_form.querySelector(".stars") as HTMLDivElement // Gets The Stars Container
    const all_stars:NodeListOf<HTMLImageElement> = stars_container.querySelectorAll<HTMLImageElement>(".star") // Gets All Star Icons
    const rating_input:HTMLInputElement = stars_container.querySelector(".rating") as HTMLInputElement // Gets The Rating Input
    let selected_rating:number = Number(rating_input.value) || 0 // Stores The Selected Rating Value

    // Functions

    // Function For Update Stars
    function updateStars(hover_value:number = 0):void {
        all_stars.forEach(function(one_star:HTMLImageElement, index:number):void {
            if(hover_value > 0) {
                if(index < hover_value) one_star.src = "/content/images/star.png" // Shows The Filled Star
                else one_star.src = "/content/images/empty_star.png" // Shows The Empty Star
            }

            else {
                if(index < selected_rating) one_star.src = "/content/images/star.png" // Shows The Filled Star
                else one_star.src = "/content/images/empty_star.png" // Shows The Empty Star
            }
        })
    }

    // Events

    all_stars.forEach(function(one_star:HTMLImageElement, index:number):void {
        const rating_number:number = index + 1 // Gets The Rating Number Of Each Star

        one_star.addEventListener("mouseover", () => updateStars(rating_number)) // Updates Stars
        one_star.addEventListener("mouseout", () => updateStars()) // Updates Stars

        one_star.addEventListener("click", function():void {
            selected_rating = rating_number // Sets The Selected Rating
            rating_input.value = String(rating_number) // Stores The Selected Rating
            updateStars() // Updates Stars
        })
    })

    // Initialization

    updateStars()

    // Update Review

    // Variables

    const form_report:HTMLParagraphElement = edit_review_form.querySelector(".form_report") as HTMLParagraphElement // Gets The Form Report Paragraph
    const edit_review_submit:HTMLButtonElement = edit_review_form.querySelector(".edit_review_submit") as HTMLButtonElement // Gets The Edit Review Submit Button

    // Events

    // Edit Review Submit Click Functionality
    edit_review_submit.addEventListener("click", function(event:PointerEvent):void {
        // Prevents The Form From Being Submitted If A Rating Has Not Been Set
        if(rating_input.value === "" || Number(rating_input.value) === 0) {
            event.preventDefault() // Prevents Default Behaviour
            form_report.textContent = "Ukážte nám vašu spokojnosť." // Shows The Form Report Message
            form_report.classList.add("invalid") // Adds Invalid Class To The Form Report Paragraph
        }

        else {
            form_report.textContent = "" // Removes The Form Report Message
            form_report.classList.remove("invalid") // Removes The Invalid Class From The Form Report Paragraph
        }
    })

    // Delete Review Warning

    // Variables

    const delete_review_container:HTMLDivElement = edit_review_form.querySelector(".delete_review_container") as HTMLDivElement // Gets The Delete Review Container
    const delete_review_image:HTMLImageElement = delete_review_container.querySelector("label .delete_review") as HTMLImageElement // Gets The Delete Review Image
    const delete_review_input:HTMLInputElement = delete_review_container.querySelector("#delete_review") as HTMLInputElement // Gets The Delete Review Input

    // Events

    // Delete Review Input Click Functionality
    delete_review_input.addEventListener("click", function():void {
        if(delete_review_input.checked) {
            delete_review_image.style.opacity = "1"
            form_report.textContent = "Vaša recenzia bude odstránená." // Shows The Form Report Message
            form_report.classList.add("invalid") // Adds Invalid Class To The Form Report Paragraph
        }

        else {
            delete_review_image.style.opacity = "0.8"
            form_report.textContent = "" // Removes The Form Report Message
        }
    })
})