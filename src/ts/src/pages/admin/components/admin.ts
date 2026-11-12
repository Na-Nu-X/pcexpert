"use strict"

const PAGE_SIZE:number = 5 // Must Match The Default Limit In Admin::getAllUsers() / Admin::getErrorLog()

// Function For Send A POST Request And Return The HTML Fragment
async function postFragment(endpoint:string, data:Record<string, string>):Promise<string> {
    const response:Response = await fetch(endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams(data).toString()
    })

    if(!response.ok) throw new Error(`Request to ${endpoint} failed with status ${response.status}.`)

    return response.text()
}

// Function For Initialize The Load More Button ([data-load-more] Next To A [data-list])
function initializeLoadMore(button:HTMLButtonElement, extra_data:Record<string, string>, on_load:() => void):void {
    const endpoint:string = button.dataset["loadMore"] ?? "" // Gets The Fragment Endpoint
    const total:number = Number(button.dataset["total"] ?? 0) // Gets The Total Count
    const list:HTMLElement|null = button.closest(".panel")?.querySelector<HTMLElement>("[data-list]") ?? null // Gets The List

    if(!endpoint || !list) return

    // Load More Button Click Functionality
    button.addEventListener("click", async function():Promise<void> {
        const offset:number = list.querySelectorAll(":scope > .data_row").length // Already Loaded Rows

        button.disabled = true
        button.classList.add("loading")

        try {
            const html:string = await postFragment(endpoint, { ...extra_data, offset: String(offset) })

            list.insertAdjacentHTML("beforeend", html) // Appends The Next Page
            on_load()

            const loaded:number = list.querySelectorAll(":scope > .data_row").length // Rows After Loading
            button.hidden = loaded >= total || loaded - offset < PAGE_SIZE // Hides The Button When Everything Is Loaded
        }

        catch(error) {
            console.error(error)
        }

        finally {
            button.disabled = false
            button.classList.remove("loading")
        }
    })
}

document.addEventListener("DOMContentLoaded", function():void {
    // Administration

    // Variables

    const admin_form:HTMLFormElement|null = document.querySelector<HTMLFormElement>("[data-admin-form]") // Gets The Admin Form
    if(!admin_form) return

    const save_button:HTMLButtonElement|null = admin_form.querySelector<HTMLButtonElement>("[data-admin-save]") // Gets The Save Button
    const pending_changes:HTMLElement|null = admin_form.querySelector<HTMLElement>("[data-pending-changes]") // Gets The Pending Changes Label
    const all_users:HTMLElement|null = admin_form.querySelector<HTMLElement>(".all_users") // Gets The Users List

    // Functions

    // Function For Count The Unsaved Changes And Update The Save Bar
    function updatePendingChanges():void {
        let changes:number = 0

        // Changed Roles
        admin_form?.querySelectorAll<HTMLSelectElement>(".role_select").forEach(function(one_select:HTMLSelectElement):void {
            const is_changed:boolean = one_select.value !== one_select.dataset["initial"]

            one_select.closest(".data_row")?.classList.toggle("changed", is_changed)
            if(is_changed) changes++
        })

        // Rows Marked For Deletion
        admin_form?.querySelectorAll<HTMLInputElement>(".delete_toggle input").forEach(function(one_checkbox:HTMLInputElement):void {
            one_checkbox.closest(".data_row")?.classList.toggle("marked_delete", one_checkbox.checked)
            if(one_checkbox.checked) changes++
        })

        if(save_button) save_button.disabled = changes === 0
        if(pending_changes) pending_changes.textContent = changes === 0 ? "Žiadne neuložené zmeny" : `Neuložené zmeny: ${changes}`

        admin_form?.classList.toggle("has_changes", changes > 0)
    }

    // Function For Show Or Hide The Error Details (Loaded Once On First Open)
    async function toggleErrorDetails(button:HTMLButtonElement):Promise<void> {
        const row:HTMLElement|null = button.closest<HTMLElement>(".error_row") // Gets The Error Row
        const details:HTMLElement|null = row?.querySelector<HTMLElement>("[data-error-details]") ?? null // Gets The Details Container
        const error_id:string|undefined = row?.dataset["error_id"] // Gets The Error ID

        if(!row || !details || !error_id) return

        const is_open:boolean = button.getAttribute("aria-expanded") === "true"

        if(!is_open && details.childElementCount === 0) {
            button.classList.add("loading")

            try {
                details.innerHTML = await postFragment("error_details.php", { error_id }) // Shows The Error Details
            }

            catch(error) {
                details.textContent = "Podrobnosti sa nepodarilo načítať."
                console.error(error)
            }

            finally {
                button.classList.remove("loading")
            }
        }

        details.hidden = is_open
        button.setAttribute("aria-expanded", String(!is_open))
        row.classList.toggle("expanded", !is_open)
    }

    // Events

    // Form Change Functionality (Roles And Delete Toggles, Also For Rows Loaded Later)
    admin_form.addEventListener("change", updatePendingChanges)

    // Error Details Click Functionality (Event Delegation)
    admin_form.addEventListener("click", function(event:MouseEvent):void {
        const button:HTMLButtonElement|null = event.target instanceof Element ? event.target.closest<HTMLButtonElement>("[data-error-details-toggle]") : null
        if(button) void toggleErrorDetails(button)
    })

    // Submit Functionality (Confirms Permanent Deletions)
    admin_form.addEventListener("submit", function(event:SubmitEvent):void {
        const deletions:number = admin_form.querySelectorAll(".delete_toggle input:checked").length // Counts The Marked Rows

        if(deletions > 0 && !window.confirm(`Naozaj chcete natrvalo odstrániť označené položky (${deletions})? Túto akciu nie je možné vrátiť.`)) {
            event.preventDefault()
        }
    })

    // Initialization

    const logged_in_user_id:string = all_users?.dataset["logged_in_user_id"] ?? "" // Gets The Logged In User ID (Excluded From The List)

    admin_form.querySelectorAll<HTMLButtonElement>("[data-load-more]").forEach(function(one_button:HTMLButtonElement):void {
        const extra_data:Record<string, string> = one_button.dataset["loadMore"] === "all_users.php" ? { user_id: logged_in_user_id } : {}
        initializeLoadMore(one_button, extra_data, updatePendingChanges) // Initializes The Load More Button
    })

    updatePendingChanges()
})
