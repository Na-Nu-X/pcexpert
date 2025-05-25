"use strict"

let computers = [
    {
        processor: "AMD Ryzen 5 4500",
        processor_page: "",
        graphics_card: "MSI GeForce RTX 4060 Ti VENTUS 2X BLACK 16G OC",
        graphics_card_page: "https://www.msi.com/Graphics-Card/GeForce-RTX-4060-Ti-VENTUS-2X-BLACK-16G-OC",
        ram: "Kingston FURY 16 GB KIT DDR4 3200 MHz CL16 Beast Black",
        ram_page: "",
        disk: "",
        disk_page: "",
        power_supply: "",
        power_supply_page: "",
        motherboard: "",
        motherboard_page: "",
        description: "Tento počítač je...",
        price: 1000
    },

    {
        processor: "AMD Ryzen 5 5500",
        processor_page: "",
        graphics_card: "MSI GeForce GTX 1660 Ti VENTUS XS 6G OC",
        graphics_card_page: "",
        ram: "Kingston FURY 16 GB KIT DDR4 3200 MHz CL16 Beast Black",
        ram_page: "",
        disk: "",
        disk_page: "",
        power_supply: "",
        power_supply_page: "",
        motherboard: "",
        motherboard_page: "",
        description: "Tento počítač je...",
        price: 500
    },

    {
        processor: "Intel Core i7-14700K",
        processor_page: "",
        graphics_card: "MSI GeForce GTX 1660 Ti VENTUS XS 6G OC",
        graphics_card_page: "",
        ram: "Kingston FURY 16 GB KIT DDR4 3200 MHz CL16 Beast Black",
        ram_page: "",
        disk: "",
        disk_page: "",
        power_supply: "",
        power_supply_page: "",
        motherboard: "",
        motherboard_page: "",
        description: "Tento počítač je...",
        price: 800
    },

    {
        processor: "Intel Core i9-12900KF",
        processor_page: "",
        graphics_card: "MSI GeForce GTX 1660 Ti VENTUS XS 6G OC",
        graphics_card_page: "",
        ram: "Kingston FURY 16 GB KIT DDR4 3200 MHz CL16 Beast Black",
        ram_page: "",
        disk: "",
        disk_page: "",
        power_supply: "",
        power_supply_page: "",
        motherboard: "",
        motherboard_page: "",
        description: "Tento počítač je...",
        price: 900
    },
]

computers.forEach(function(one_computer){
    let all_computers_paragraph = document.createElement("p")
    all_computers_paragraph.innerHTML = `<strong>Procesor:</strong> ${one_computer.processor}<br><strong>Grafická karta:</strong> ${one_computer.graphics_card}`
    document.querySelector(".relevant_computers").appendChild(all_computers_paragraph)
})

document.querySelector(".computer_filter_select").addEventListener("change", function(event){
    document.querySelector(".search_input").innerHTML = ""
    document.querySelector(".relevant_computers").innerHTML = ""

    if(event.target.value === "empty_option"){
        computers.forEach(function(one_computer){
            let all_computers_paragraph = document.createElement("p")
            all_computers_paragraph.innerHTML = `<strong>Procesor:</strong> ${one_computer.processor}<br><strong>Grafická karta:</strong> ${one_computer.graphics_card}`
            document.querySelector(".relevant_computers").appendChild(all_computers_paragraph)
        })
    }

    if(event.target.value === "processor"){
        let search_input = document.createElement("input")
        search_input.setAttribute("type", "text")
        search_input.setAttribute("placeholder", "Vyhľadajte podľa procesora")
        document.querySelector(".search_input").appendChild(search_input)

        search_input.addEventListener("input", function(event){
            let computer_filter = computers.filter(function(one_computer){
                return one_computer.processor.toLowerCase().includes(event.target.value.toLowerCase())
            })

            document.querySelector(".relevant_computers").innerHTML = ""

            computer_filter.forEach(function(one_computer){
                let relevant_computers_paragraph = document.createElement("p")
                relevant_computers_paragraph.innerHTML = `<strong>Procesor:</strong> ${one_computer.processor}<br><strong>Grafická karta:</strong> ${one_computer.graphics_card}`
                document.querySelector(".relevant_computers").appendChild(relevant_computers_paragraph)
            })

            let number_of_relevant_computers = document.createElement("p")
            number_of_relevant_computers.innerHTML = `Počet nájdených výsledkov: <strong>${computer_filter.length}</strong>`
            document.querySelector(".relevant_computers").appendChild(number_of_relevant_computers)

            if(event.target.value === ""){
                document.querySelector(".relevant_computers").innerHTML = ""
            }
        })
    }

    if(event.target.value === "graphics_card"){
        let search_input = document.createElement("input")
        search_input.setAttribute("type", "text")
        search_input.setAttribute("placeholder", "Vyhľadajte podľa grafickej karty")
        document.querySelector(".search_input").appendChild(search_input)

        search_input.addEventListener("input", function(event){
            let computer_filter = computers.filter(function(one_computer){
                return one_computer.graphics_card.toLowerCase().includes(event.target.value.toLowerCase())
            })

            document.querySelector(".relevant_computers").innerHTML = ""

            computer_filter.forEach(function(one_computer){
                let relevant_computers_paragraph = document.createElement("p")
                relevant_computers_paragraph.innerHTML = `<strong>Procesor:</strong> ${one_computer.processor}<br><strong>Grafická karta:</strong> ${one_computer.graphics_card}`
                document.querySelector(".relevant_computers").appendChild(relevant_computers_paragraph)
            })

            let number_of_relevant_computers = document.createElement("p")
            number_of_relevant_computers.innerHTML = `Počet nájdených výsledkov: <strong>${computer_filter.length}</strong>`
            document.querySelector(".relevant_computers").appendChild(number_of_relevant_computers)

            if(event.target.value === ""){
                document.querySelector(".relevant_computers").innerHTML = ""
            }
        })
    }

    if(event.target.value === "price"){
        let search_input = document.createElement("input")
        search_input.setAttribute("type", "range")
        search_input.setAttribute("min", "500")
        search_input.setAttribute("max", "3000")
        search_input.setAttribute("step", "100")
        document.querySelector(".search_input").appendChild(search_input)

        search_input.addEventListener("input", function(event){
            console.log(event.target.value)
        })
    }
})