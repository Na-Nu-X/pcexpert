"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Contact Form

    // Variables

    const contact_form:HTMLFormElement = document.querySelector(".contact .contact_form") as HTMLFormElement // Gets The Contact Form
    const attachment_container:HTMLDivElement = contact_form.querySelector(".attachment_container") as HTMLDivElement // Gets The Attachment Container
    const attachment_input:HTMLInputElement = attachment_container.querySelector("#attachment") as HTMLInputElement // Gets The Attachment Input
    const attachment_report:HTMLParagraphElement = attachment_container.querySelector(".attachment_report") as HTMLParagraphElement // Gets The Attachment Report

    // Events

    // Attachment Input Change Functionality
    attachment_input.addEventListener("change", function():void {
        const all_attachments:FileList|null = attachment_input.files // Gets All Attachments

        if(all_attachments) {
            const all_attachments_sizes:number[] = Array.from(all_attachments).map(one_file => one_file.size) // Gets All Attachments Sizes
            let total_size:number = 0 // Stores The Total Size
    
            for(let i:number = 0; i < all_attachments_sizes.length; i++) {
                const one_attachment_size:number|null = all_attachments_sizes[i] || null // Gets One Attachment Size
                if(one_attachment_size) total_size = total_size + one_attachment_size // Increases The Total Size
            }
    
            if(all_attachments.length > 0 && all_attachments.length <= 5 && total_size <= 25000000) {
                const all_attachments_names = Array.from(all_attachments).map(file => file.name) // Gets All Attachments Names
                attachment_report.textContent = `súbory (${all_attachments_names.length}): ${all_attachments_names.join(", ")}` // Shows Attachment Report Message
            }
    
            else if(all_attachments.length > 5) attachment_report.textContent = "Vybrali ste príliš mnoho súborov." // Shows Attachment Report Message
            else if(total_size > 25000000) attachment_report.textContent = "Vybrané súbory sú príliš veľké." // Shows Attachment Report Message
            else attachment_report.textContent = "Nie je vybraný žiaden súbor." // Shows Attachment Report Message
        }
    })
})