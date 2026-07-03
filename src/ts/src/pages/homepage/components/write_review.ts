"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Write Review

    // Variables

    const write_review:HTMLFormElement = document.querySelector(".reviews .user_review .write_review") as HTMLFormElement // Gets The Write Review Form
    const stars_container:HTMLDivElement = write_review.querySelector(".stars") as HTMLDivElement // Gets The Stars Container
    const all_stars:NodeListOf<HTMLImageElement> = stars_container.querySelectorAll<HTMLImageElement>(".star") // Gets All Star Icons
    const rating_input:HTMLInputElement = stars_container.querySelector(".rating") as HTMLInputElement // Gets The Rating Input

    const form_report:HTMLParagraphElement = write_review.querySelector(".form_report") as HTMLParagraphElement // Gets The Form Report Paragraph
    const write_review_submit:HTMLButtonElement = write_review.querySelector(".write_review_submit") as HTMLButtonElement // Gets The Write Review Submit Button

    let selected_rating:number = 0 // Stores The Selected Rating Value

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

    // Write Review Submit Button Click Functionality
    write_review_submit.addEventListener("click", function(event:PointerEvent):void {
        // Prevents The Form From Being Submitted If A Rating Has Not Been Set
        if(rating_input.value === "") {
            event.preventDefault() // Prevents Default Behaviour
            form_report.textContent = "Ukážte nám vašu spokojnosť." // Shows The Form Report Message
            form_report.classList.add("invalid") // Adds Invalid Class To The Form Report Paragraph
        }

        else {
            form_report.textContent = "" // Removes The Form Report Message
            form_report.classList.remove("invalid") // Removes The Invalid Class From The Form Report Paragraph
        }
    })
})