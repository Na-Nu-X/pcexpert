// Function For Initialize The Range Field (Updates The Output Label And The Optional Hidden Value Input)
//
// Markup:
// <div data-range>
//     <output data-range-output></output>
//     <input type="range" data-range-input data-unit="GB" data-values="8,16,32">
//     <input type="hidden" name="ram" data-range-value> (Optional - Used When data-values Maps The Slider Steps)
// </div>
export function initializeRangeField(range_field:HTMLElement):void {
    const input:HTMLInputElement|null = range_field.querySelector<HTMLInputElement>("[data-range-input]") // Gets The Range Input
    const output:HTMLOutputElement|null = range_field.querySelector<HTMLOutputElement>("[data-range-output]") // Gets The Output Label
    const hidden_value:HTMLInputElement|null = range_field.querySelector<HTMLInputElement>("[data-range-value]") // Gets The Hidden Value Input

    if(!input || !output) return

    const unit:string = input.dataset["unit"] ?? "" // Gets The Unit
    const mapped_values:string[]|null = input.dataset["values"]?.split(",") ?? null // Gets The Mapped Values (Non Linear Sliders)

    // Function For Update The Field
    function update():void {
        if(!input || !output) return

        const value:string = mapped_values?.[Number(input.value)] ?? input.value // Gets The Real Value
        const percentage:number = (Number(input.value) - Number(input.min)) / (Number(input.max) - Number(input.min)) * 100 // Gets The Filled Track Percentage

        output.textContent = `${value} ${unit}`.trim() // Shows The Current Value
        input.style.setProperty("--range-progress", `${percentage}%`) // Updates The Filled Track
        if(hidden_value) hidden_value.value = value // Stores The Real Value
    }

    input.addEventListener("input", update)

    update()
}
