"use strict"

const image = document.querySelectorAll(".image");

image.forEach(function (one_image) {
    one_image.addEventListener("click", function () {
        deleteActiveClass();
        one_image.classList.add("active");
    })
})

function deleteActiveClass() {
    image.forEach(function (one_image) {
        one_image.classList.remove("active");
    })
}