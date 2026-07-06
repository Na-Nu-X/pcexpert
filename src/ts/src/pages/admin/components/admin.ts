"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // User Role

    // Variables

    const admin_form:HTMLFormElement = document.querySelector(".admin_form") as HTMLFormElement // Gets The Admin Form
    const users:HTMLElement = admin_form.querySelector(".users") as HTMLElement // Gets The Users Section
    const all_users:HTMLDivElement = users.querySelector(".all_users") as HTMLDivElement // Gets The All Users Container

    // Global Event Delegations

    // All Users Container Click Functionalities
    all_users.addEventListener("click", function(event:PointerEvent):void {
        // Change User Role
        if((event.target as HTMLInputElement).classList.contains("user_role_checkbox")) {
            const user_role_checkbox:HTMLInputElement = event.target as HTMLInputElement // Gets The User Role Checkbox
            const user:HTMLDivElement = user_role_checkbox.closest(".user") as HTMLDivElement // Gets The User Container
            const user_role_value:HTMLSpanElement = user.querySelector(".user_role_value") as HTMLSpanElement // Gets The User Role Value
            const role:string|null = user.dataset["role"] || null // Gets The Role

            if(role === "user") {
                if(user_role_checkbox.checked) {
                    user.dataset["role"] = "admin"
                    user_role_checkbox.value = "admin"
                    user_role_value.textContent = "- admin"
                    user_role_value.style.color = "rgb(250, 50, 50)"
                }

                else {
                    user.dataset["role"] = "user"
                    user_role_checkbox.value = "user"
                    user_role_value.textContent = "- user"
                    user_role_value.style.color = "rgb(150, 150, 150)"
                }
            }

            else if(role === "admin") {
                if(user_role_checkbox.checked) {
                    user.dataset["role"] = "user"
                    user_role_checkbox.value = "user"
                    user_role_value.textContent = "- user"
                    user_role_value.style.color = "rgb(150, 150, 150)"
                }

                else {
                    user.dataset["role"] = "admin"
                    user_role_checkbox.value = "admin"
                    user_role_value.textContent = "- admin"
                    user_role_value.style.color = "rgb(250, 50, 50)"
                }
            }
        }

        // Delete User
        if((event.target as HTMLInputElement).classList.contains("delete_user_checkbox")) {
            const delete_user_checkbox:HTMLInputElement = event.target as HTMLInputElement // Gets The User Role Checkbox
            const user:HTMLDivElement = delete_user_checkbox.closest(".user") as HTMLDivElement // Gets The User Container
            const delete_user:HTMLImageElement = user.querySelector(".delete_user_container .delete_user") as HTMLImageElement // Gets The Delete User Image
            const form_report:HTMLParagraphElement = user.querySelector(".form_report") as HTMLParagraphElement // Gets The Form Report Paragraph

            if(!delete_user_checkbox.checked) {
                delete_user.style.opacity = "1"
                form_report.textContent = "Účet bude odstránený." // Shows The Form Report Message
                form_report.classList.add("invalid") // Adds Invalid Class To The Form Report Paragraph
            }

            else {
                delete_user.style.opacity = "0.8"
                form_report.textContent = "" // Removes The Form Report Message
            }
        }
    })

    // Show More / Show Less Users

    // Variables

    const show_more_users:HTMLButtonElement = users.querySelector(".show_more_users") as HTMLButtonElement // Gets The Show More Users Button
    const show_less_users:HTMLButtonElement = users.querySelector(".show_less_users") as HTMLButtonElement // Gets The Show Less Users Button

    const LIMIT:number = 5 // Defines The Limit Of Loaded Reviews
    const logged_in_user_id:number|null = Number(all_users.dataset["logged_in_user_id"]) || null // Gets The Logged In User ID If Is Available
    let offset:number = 0 // Stores The Offset

    // Events

    // Show More Users Button Click Functionality
    show_more_users.addEventListener("click", async function():Promise<void> {
        if(logged_in_user_id) {
            offset += LIMIT // Increases The Offset

            // Sends The POST Request To Load Users
            await fetch("all_users.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `offset=${offset}&user_id=${encodeURIComponent(logged_in_user_id)}`
            })
            .then(response => response.text())
            .then(data => {
                all_users.insertAdjacentHTML("beforeend", data)
                show_less_users.classList.remove("hidden") // Shows The Show Less Button
                show_more_users.classList.add("hidden") // Hides The Show More Button
            })
        }
    })

    // Show Less Users Button Click Functionality
    show_less_users.addEventListener("click", async function():Promise<void> {
        if(logged_in_user_id) {
            offset = 0 // Sets The Offset To 0

            // Sends The POST Request To Load Users
            await fetch("all_users.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `offset=${offset}&user_id=${encodeURIComponent(logged_in_user_id)}`
            })
            .then(response => response.text())
            .then(data => {
                all_users.innerHTML = data
                show_more_users.classList.remove("hidden") // Shows The Show More Button
                show_less_users.classList.add("hidden") // Hides The Show Less Button
            })
        }
    })

    // Show / Hide Error Details

    // Variables

    const error_log:HTMLElement = admin_form.querySelector(".error_log") as HTMLElement // Gets The Error Log Section
    const all_errors:HTMLDivElement = error_log.querySelector(".all_errors") as HTMLDivElement // Gets The All Errors Container

    // Global Event Delegations

    // All Errors Container Click Functionalities
    all_errors.addEventListener("click", async function(event:PointerEvent):Promise<void> {
        // Show Error Details
        if((event.target as HTMLButtonElement).classList.contains("show_error_details")) {
            const show_error_details:HTMLButtonElement = event.target as HTMLButtonElement // Gets The Show Error Details Button
            const error:HTMLDivElement = show_error_details.closest(".error") as HTMLDivElement // Gets The Error Container
            const hide_error_details:HTMLButtonElement = error.querySelector(".hide_error_details") as HTMLButtonElement // Gets The Hide Error Details Button
            const error_details:HTMLDivElement = error.querySelector(".error_details") as HTMLDivElement // Gets The Error Details Container
            const error_id:number|null = Number(error.dataset["error_id"]) || null // Gets The Error ID

            if(error_id) {
                const body:string = new URLSearchParams({ error_id: String(error_id) }).toString() // Stores The Error ID

                // Sends The POST Request To Load The Error Details
                const response:Response = await fetch("error_details.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body
                })
    
                const response_content:string = await response.text() // Gets The Response Content

                error_details.innerHTML = response_content // Shows The Error Details
                hide_error_details.classList.remove("hidden") // Shows The Hide Error Details Button
                show_error_details.classList.add("hidden") // Hides The Show Error Details Button
            }
        }

        // Hide Error Details
        if((event.target as HTMLButtonElement).classList.contains("hide_error_details")) {
            const hide_error_details:HTMLButtonElement = event.target as HTMLButtonElement // Gets The Show Error Details Button
            const error:HTMLDivElement = hide_error_details.closest(".error") as HTMLDivElement // Gets The Error Container
            const show_error_details:HTMLButtonElement = error.querySelector(".show_error_details") as HTMLButtonElement // Gets The Hide Error Details Button
            const error_details:HTMLDivElement = error.querySelector(".error_details") as HTMLDivElement // Gets The Error Details Container

            error_details.innerHTML = "" // Hides The Error Details
            show_error_details.classList.remove("hidden") // Shows The Show Error Details Button
            hide_error_details.classList.add("hidden") // Hides The Hide Error Details Button
        }

        // Delete Error
        if((event.target as HTMLButtonElement).classList.contains("delete_error_log_checkbox")) {
            const delete_error_log_checkbox:HTMLInputElement = event.target as HTMLInputElement // Gets The Delete Error Log Checkbox
            const error:HTMLDivElement = delete_error_log_checkbox.closest(".error") as HTMLDivElement // Gets The Error Container
            const delete_error_log:HTMLLabelElement = error.querySelector(".delete_error_log") as HTMLLabelElement // Gets The Delete Error Log Label
            const cancel_delete_error_log:HTMLLabelElement = error.querySelector(".cancel_delete_error_log") as HTMLLabelElement // Gets The Cancel Delete Error Log Label

            // Delete
            if(delete_error_log_checkbox.checked) {
                delete_error_log.style.display = "none" // Hides The Delete Error Log Label
                cancel_delete_error_log.style.display = "inline-block" // Shows The Cancel Delete Error Log Label
            }

            // Cancel Delete
            else {
                cancel_delete_error_log.style.display = "none" // Hides The Cancel Delete Error Log Label
                delete_error_log.style.display = "inline-block" // Shows The Delete Error Log Label
            }
        }

        // Show More / Show Less Errors

        // Variables

        const show_more_errors:HTMLButtonElement|null = error_log.querySelector(".show_more_errors") as HTMLButtonElement || null // Gets The Show More Errors Button If Is Available
        const show_less_errors:HTMLButtonElement|null = error_log.querySelector(".show_less_errors") as HTMLButtonElement || null // Gets The Show Less Errors Button If Is Available

        const LIMIT:number = 5 // Defines The Limit Of Loaded Errors
        let offset:number = 0 // Stores The Offset

        // Events

        // Show More Errors Button Click Functionality
        if(show_more_errors) {
            show_more_errors.addEventListener("click", async function():Promise<void> {
                offset += LIMIT // Increases The Offset
    
                // Sends The POST Request To Load Errors
                await fetch("all_errors.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `offset=${offset}`
                })
                .then(response => response.text())
                .then(data => {
                    all_errors.insertAdjacentHTML("beforeend", data)
                    show_less_errors.classList.remove("hidden") // Shows The Show Less Button
                    show_more_errors.classList.add("hidden") // Hides The Show More Button
                })
            })
        }
    
        // Show Less Errors Button Click Functionality
        if(show_less_errors) {
            show_less_errors.addEventListener("click", async function():Promise<void> {
                offset = 0 // Sets The Offset To 0
    
                // Sends The POST Request To Load Errors
                await fetch("all_errors.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `offset=${offset}`
                })
                .then(response => response.text())
                .then(data => {
                    all_errors.innerHTML = data
                    show_more_errors.classList.remove("hidden") // Shows The Show More Button
                    show_less_errors.classList.add("hidden") // Hides The Show Less Button
                })
            })
        }
    })
})