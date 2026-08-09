import { initializeAttachmentUpload } from "../../../components/attachment.js"

"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Contact Form

    // Variables

    const contact_form:HTMLFormElement = document.querySelector(".contact .contact_form") as HTMLFormElement // Gets The Contact Form
    const attachment_container:HTMLDivElement = contact_form.querySelector(".attachment_container") as HTMLDivElement // Gets The Attachment Container

    // Initialization

    initializeAttachmentUpload(attachment_container) // Initializes The Attachment Upload
})