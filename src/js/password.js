// JavaScript Code

"use strict"

// Random Password Generator

const password = document.querySelector(".password")
const generate_password = document.querySelector(".fa-key")

const alphabet = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"]
const numbers = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
const special_chars = ['!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '=', '+', '[', ']', '{', '}', ';', ':', "'", '"', ',', '.', '<', '>', '/', '?', '\\', '|', '`', '~']

generate_password.addEventListener("click", function(event) {
    function shuffleString(string) {
        let array = string.split("")

        for(let i = array.length - 1; i > 0; i--) {
            let j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]]
        }

        return array.join("");
    }

    let generated_password = ""

    for(let x = 0; x < 5; x++) {
        generated_password = generated_password + alphabet[Math.floor(Math.random() * alphabet.length)] + numbers[Math.floor(Math.random() * numbers.length)] + special_chars[Math.floor(Math.random() * special_chars.length)]
    }

    password.value = shuffleString(generated_password)
})

// Toggle Show / Hide Password

const password_icon = document.querySelector(".fa-regular")

password_icon.addEventListener("click", function(event) {
    if(password_icon.classList[1] === "fa-eye-slash") {
        password_icon.classList.remove("fa-eye-slash")
        password_icon.classList.add("fa-eye")

        password.style.webkitTextSecurity = "none"
    }

    else if(password_icon.classList[1] === "fa-eye") {
        password_icon.classList.remove("fa-eye")
        password_icon.classList.add("fa-eye-slash")

        password.style.webkitTextSecurity = "disc"
    }
})

// Password Match

const password_check = document.querySelector(".password_check")
const form_report_paragraph = document.querySelector(".form_report_paragraph")
const dialogue_paragraph = document.querySelector(".dialogue_paragraph")

password.addEventListener("input", function(event){
    if(password.value === password_check.value) {
        form_report_paragraph.textContent = "Heslá sa zhodujú."
        form_report_paragraph.classList.add("valid")
        form_report_paragraph.classList.remove("invalid")
        dialogue_paragraph.textContent = ""
    }

    else {
        form_report_paragraph.textContent = "Heslá sa nezhodujú."
        form_report_paragraph.classList.add("invalid")
        form_report_paragraph.classList.remove("valid")
        dialogue_paragraph.textContent = ""
    }


    if (password.value === "" && password_check.value === "") {
        form_report_paragraph.textContent = ""
    }
})

password_check.addEventListener("input", function(event){
    if(password.value === password_check.value) {
        form_report_paragraph.textContent = "Heslá sa zhodujú."
        form_report_paragraph.classList.add("valid")
        form_report_paragraph.classList.remove("invalid")
        dialogue_paragraph.textContent = ""
    }

    else {
        form_report_paragraph.textContent = "Heslá sa nezhodujú."
        form_report_paragraph.classList.add("invalid")
        form_report_paragraph.classList.remove("valid")
        dialogue_paragraph.textContent = ""
    }


    if (password.value === "" && password_check.value === "") {
        form_report_paragraph.textContent = ""
    }
})

// Copy Password To Clipboard

const copy_password = document.querySelector(".copy_password_icon")

copy_password.addEventListener("click", function(event) {
    password.select()
    password.setSelectionRange(0, password.value.length)
    document.execCommand("copy")
    // navigator.clipboard.writeText(password.value) // Only HTTPS
})

// Paste From Clipboard

const paste_password = document.querySelector(".paste_password_icon")

paste_password.addEventListener("click", function(event) {
    // password_check.value = navigator.clipboard.readText() // Only HTTPS
    password_check.value = password.value
})