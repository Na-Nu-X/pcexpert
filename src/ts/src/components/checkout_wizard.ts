import { initializeAttachmentUpload } from "./attachment.js"
import { initializeRangeField } from "./range_field.js"
import { pickPacketaPoint, type PacketaPickupPoint } from "../utils/packeta.js"

"use strict"

// Checkout Wizard
//
// Drives One Multi Step Checkout Dialog Rendered By src/assets/partials/checkout_dialog.php.
// Validation Is Declarative:
//   - Native Constraints (required, type="email", Required Radio Groups...) Of Every Visible Field
//   - [data-required-group] -> At Least One Checkbox Inside Must Be Checked
//   - input[type="hidden"][data-required] Inside The Selected Shipping Method -> Must Have A Value
export class CheckoutWizard {
    private readonly form:HTMLFormElement
    private readonly dialog:HTMLDialogElement
    private readonly body:HTMLElement
    private readonly all_steps:HTMLFieldSetElement[]
    private readonly all_indicators:HTMLLIElement[]
    private readonly previous_button:HTMLButtonElement
    private readonly next_button:HTMLButtonElement
    private readonly pay_button:HTMLButtonElement
    private readonly step_current:HTMLElement

    private current_step:number = 0
    private is_submitting:boolean = false

    constructor(form:HTMLFormElement) {
        this.form = form
        this.dialog = this.query<HTMLDialogElement>(form, "[data-checkout]")
        this.body = this.query<HTMLElement>(this.dialog, "[data-checkout-body]")
        this.all_steps = [...this.dialog.querySelectorAll<HTMLFieldSetElement>("[data-step]")]
        this.all_indicators = [...this.dialog.querySelectorAll<HTMLLIElement>("[data-step-indicator]")]
        this.previous_button = this.query<HTMLButtonElement>(this.dialog, "[data-checkout-previous]")
        this.next_button = this.query<HTMLButtonElement>(this.dialog, "[data-checkout-next]")
        this.pay_button = this.query<HTMLButtonElement>(this.dialog, "[data-checkout-pay]")
        this.step_current = this.query<HTMLElement>(this.dialog, "[data-step-current]")

        this.dialog.querySelectorAll<HTMLElement>("[data-range]").forEach(initializeRangeField) // Initializes The Range Fields

        const attachment_container:HTMLDivElement|null = this.dialog.querySelector<HTMLDivElement>(".attachment_container") // Gets The Attachment Container
        if(attachment_container) initializeAttachmentUpload(attachment_container) // Initializes The Attachment Upload

        this.bindEvents()
        this.showStep(0, false)
    }

    // Public Methods

    // Function For Open The Dialog
    public open():void {
        this.dialog.showModal()
        document.body.classList.add("modal_open") // Locks The Page Scroll
        this.updateButtons()
    }

    // Function For Close The Dialog (The Filled Data Are Kept)
    public close():void {
        this.dialog.close()
    }

    // Private Methods

    // Function For Get The Required Element (Throws If The Markup Is Broken)
    private query<T extends Element>(parent:ParentNode, selector:string):T {
        const element:T|null = parent.querySelector<T>(selector)
        if(!element) throw new Error(`CheckoutWizard: Missing element "${selector}".`)

        return element
    }

    // Function For Bind All Events
    private bindEvents():void {
        // Open Buttons (Anywhere In The Form, E.g. The Service Card)
        this.form.querySelectorAll<HTMLButtonElement>("[data-checkout-open]").forEach(button => button.addEventListener("click", () => this.open()))

        // Close Button
        this.query<HTMLButtonElement>(this.dialog, "[data-checkout-close]").addEventListener("click", () => this.close())

        // Backdrop Click (The Click Target Is The Dialog Itself Only Outside Of The Content)
        this.dialog.addEventListener("click", (event:MouseEvent):void => {
            if(event.target === this.dialog) this.close()
        })

        // Dialog Close (Escape Key Or Programmatic)
        this.dialog.addEventListener("close", () => document.body.classList.remove("modal_open"))

        // Navigation
        this.previous_button.addEventListener("click", () => this.showStep(this.current_step - 1))
        this.next_button.addEventListener("click", () => {
            if(this.isStepValid(this.currentStep())) this.showStep(this.current_step + 1)
        })

        // Live Validation
        this.dialog.addEventListener("input", () => this.updateButtons())
        this.dialog.addEventListener("change", (event:Event):void => {
            const target:EventTarget|null = event.target

            if(target instanceof HTMLInputElement && target.name === "shipping_method") this.selectShippingMethod(target) // Shows The Selected Shipping Method Details
            this.updateButtons()
        })

        // Invalid Field Highlighting After The User Leaves The Field
        this.dialog.addEventListener("focusout", (event:FocusEvent):void => {
            const target:EventTarget|null = event.target
            if(target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement) this.highlightField(target)
        })

        // Packeta Widget
        this.dialog.querySelectorAll<HTMLButtonElement>("[data-packeta-open]").forEach(button => {
            button.addEventListener("click", () => this.openPacketaWidget(button))
        })

        // Enter Key Goes To The Next Step Instead Of Submitting The Form
        this.form.addEventListener("keydown", (event:KeyboardEvent):void => {
            if(event.key !== "Enter" || !(event.target instanceof HTMLInputElement)) return

            event.preventDefault()

            if(this.isLastStep() && !this.pay_button.disabled) this.form.requestSubmit(this.pay_button)
            else if(!this.isLastStep() && !this.next_button.disabled) this.next_button.click()
        })

        // Double Submit Protection (Prevents Creating Two Stripe Sessions)
        this.form.addEventListener("submit", (event:SubmitEvent):void => {
            if(this.is_submitting) {
                event.preventDefault()
                return
            }

            this.is_submitting = true
            this.pay_button.classList.add("loading")
            this.pay_button.setAttribute("aria-busy", "true")
        })

        // Reset The Submit State When The Page Is Restored From The Back / Forward Cache (Returning From Stripe)
        window.addEventListener("pageshow", () => {
            this.is_submitting = false
            this.pay_button.classList.remove("loading")
            this.pay_button.removeAttribute("aria-busy")
        })
    }

    // Function For Get The Current Step
    private currentStep():HTMLFieldSetElement {
        return this.all_steps[this.current_step] as HTMLFieldSetElement
    }

    // Function For Check If The Current Step Is The Last One
    private isLastStep():boolean {
        return this.current_step === this.all_steps.length - 1
    }

    // Function For Show The Step
    private showStep(index:number, move_focus:boolean = true):void {
        if(index < 0 || index >= this.all_steps.length) return

        this.current_step = index

        this.all_steps.forEach((one_step, one_index) => one_step.classList.toggle("active", one_index === index)) // Shows Only The Current Step

        // Updates The Progress Indicator
        this.all_indicators.forEach((one_indicator, one_index) => {
            one_indicator.classList.toggle("active", one_index === index)
            one_indicator.classList.toggle("done", one_index < index)

            if(one_index === index) one_indicator.setAttribute("aria-current", "step")
            else one_indicator.removeAttribute("aria-current")
        })

        this.step_current.textContent = String(index + 1) // Updates The Step Counter
        this.previous_button.hidden = index === 0
        this.next_button.hidden = this.isLastStep()
        this.pay_button.hidden = !this.isLastStep()

        this.body.scrollTop = 0 // Scrolls The New Step To The Top
        this.updateButtons()

        if(move_focus) {
            // Focuses The First Empty Field Of The New Step
            const first_empty_field:HTMLInputElement|HTMLTextAreaElement|null = [...this.currentStep().querySelectorAll<HTMLInputElement|HTMLTextAreaElement>("input:not([type='hidden']):not([type='range']), textarea")]
                .find(one_field => one_field.value.trim() === "" || (one_field instanceof HTMLInputElement && (one_field.type === "radio" || one_field.type === "checkbox") && !one_field.checked)) ?? null

            first_empty_field?.focus({ preventScroll: true })
        }
    }

    // Function For Validate The Step
    private isStepValid(step:HTMLFieldSetElement):boolean {
        // Native Constraints Of Visible Fields
        const all_fields:(HTMLInputElement|HTMLTextAreaElement|HTMLSelectElement)[] = [...step.querySelectorAll<HTMLInputElement|HTMLTextAreaElement|HTMLSelectElement>("input:not([type='hidden']), textarea, select")]
        if(!all_fields.every(one_field => one_field.checkValidity())) return false

        // At Least One Checked Checkbox In Every Required Group
        const all_required_groups:HTMLElement[] = [...step.querySelectorAll<HTMLElement>("[data-required-group]")]
        if(!all_required_groups.every(one_group => one_group.querySelector("input:checked") !== null)) return false

        // Required Hidden Values Of The Visible Shipping Details (E.g. Selected Packeta Point)
        const all_required_hidden:HTMLInputElement[] = [...step.querySelectorAll<HTMLInputElement>("[data-shipping-details]:not([hidden]) input[type='hidden'][data-required]")]
        if(!all_required_hidden.every(one_input => one_input.value.trim() !== "")) return false

        return true
    }

    // Function For Enable Or Disable The Navigation Buttons
    private updateButtons():void {
        const is_valid:boolean = this.isStepValid(this.currentStep()) // Checks The Current Step Validity

        this.next_button.disabled = !is_valid
        this.pay_button.disabled = !is_valid || !this.all_steps.every(one_step => this.isStepValid(one_step))
    }

    // Function For Highlight The Filled But Invalid Field
    private highlightField(field:HTMLInputElement|HTMLTextAreaElement):void {
        const is_invalid:boolean = field.value.trim() !== "" && !field.checkValidity()

        field.classList.toggle("invalid", is_invalid)
        field.setAttribute("aria-invalid", String(is_invalid))
    }

    // Function For Show The Details Of The Selected Shipping Method
    private selectShippingMethod(input:HTMLInputElement):void {
        this.dialog.querySelectorAll<HTMLElement>("[data-shipping-method]").forEach(one_method => {
            const details:HTMLElement|null = one_method.querySelector<HTMLElement>("[data-shipping-details]")

            one_method.classList.toggle("selected", one_method.contains(input))
            if(details) details.hidden = !one_method.contains(input)
        })
    }

    // Function For Open The Packeta Widget
    private openPacketaWidget(button:HTMLButtonElement):void {
        const details:HTMLElement = button.closest<HTMLElement>("[data-shipping-details]") as HTMLElement // Gets The Shipping Details Container
        const widget_container:HTMLElement = this.query<HTMLElement>(details, "[data-packeta-widget]")
        const label:HTMLElement = this.query<HTMLElement>(details, "[data-packeta-label]")
        const branch_id:HTMLInputElement = this.query<HTMLInputElement>(details, "[data-packeta-id]")
        const branch_name:HTMLInputElement = this.query<HTMLInputElement>(details, "[data-packeta-name]")

        widget_container.hidden = false // Shows The Packeta Widget Container

        pickPacketaPoint(widget_container, (pickup_point:PacketaPickupPoint):void => {
            branch_id.value = String(pickup_point.id) // Stores The Packeta Branch ID
            branch_name.value = pickup_point.name // Stores The Packeta Branch Name

            label.textContent = pickup_point.name // Shows The Selected Packeta Branch Name
            label.classList.add("selected")
            button.innerHTML = `<i class="fa-solid fa-pen"></i> Zmeniť` // https://fontawesome.com/icons/pen

            widget_container.hidden = true // Hides The Packeta Widget Container
            this.updateButtons()
        })
    }
}
