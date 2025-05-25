"use strict"

const header_text_paragraph = document.querySelector(".header_text_paragraph")
const header_text_paragraph_text = document.querySelector(".header_text_paragraph").textContent
let letter = 1
let delay = 10

function printText(){
    header_text_paragraph.textContent = header_text_paragraph_text.slice(0, letter)

    letter++

    setTimeout(printText, delay)
}

printText()