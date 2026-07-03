"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Show More / Show Less Reviews

    // Variables

    const reviews:HTMLElement = document.querySelector(".reviews") as HTMLElement // Gets The Reviews Section
    const all_reviews:HTMLDivElement = reviews.querySelector(".all_reviews") as HTMLDivElement // Gets The All Reviews Container
    const show_more:HTMLButtonElement = reviews.querySelector(".show_more") as HTMLButtonElement // Gets The Show More Button
    const show_less:HTMLButtonElement = reviews.querySelector(".show_less") as HTMLButtonElement // Gets The Show Less Button

    const LIMIT:number = 5 // Defines The Limit Of Loaded Reviews
    const logged_in_user_id:number|null = Number(reviews.dataset["logged_in_user_id"]) || null // Gets The Logged In User ID If Is Available
    let offset:number = 0 // Stores The Offset

    // Events

    // Show More Button Click Functionality
    show_more.addEventListener("click", function():void {
        if(logged_in_user_id) {
            offset += LIMIT // Increases The Offset
    
            // Sends The POST Request To Load Reviews
            fetch("all_reviews.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `offset=${offset}&user_id=${encodeURIComponent(logged_in_user_id)}`
            })
            .then(response => response.text())
            .then(data => {
                all_reviews.insertAdjacentHTML("beforeend", data)
                show_more.style.display = "none"
                show_less.style.display = "inline-block"
            })
        }
    })

    // Show Less Button Click Functionality
    show_less.addEventListener("click", function():void {
        if(logged_in_user_id) {
            offset = 0 // Sets The Offset To 0
    
            // Sends The POST Request To Load Reviews
            fetch("all_reviews.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `offset=${offset}&user_id=${encodeURIComponent(logged_in_user_id)}`
            })
            .then(response => response.text())
            .then(data => {
                all_reviews.innerHTML = data
                show_less.style.display = "none"
                show_more.style.display = "inline-block"
            })
        }
    })
})