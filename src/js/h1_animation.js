"use strict"

const header_text_paragraph = document.querySelector(".h1_text_content")
const header_text_paragraph_text = document.querySelector(".h1_text_content").textContent
let letter = 1
let delay = 250

function printText(){
    header_text_paragraph.textContent = header_text_paragraph_text.slice(0, letter)

    letter++

    setTimeout(printText, delay)
}

printText()