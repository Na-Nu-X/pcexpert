"use strict"

document.querySelector(".contact_form").addEventListener("submit", function(event){
    event.preventDefault()

    if(event.target.full_name.value === "" || event.target.email_address.value === "" || event.target.subject.value === "empty_option" || event.target.message_content.value === ""){
        window.alert("Všetky polia sú povinné.")
    }
    else{
        window.alert(`Správa od ${event.target.full_name.value} bola odoslaná.`)
    }
    
    /*
    console.log(event.target.full_name.value)
    console.log(event.target.email_address.value)
    console.log(event.target.subject.value)
    console.log(event.target.message_content.value)
    */
})