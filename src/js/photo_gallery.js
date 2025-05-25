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

// function cycleImages() {
//     let i = 0

//     function showNextImage() {
//         deleteActiveClass()

//         image[i].style.transition = "min-width 3s linear";

//         image[i].classList.add("active")

//         i++

//         if (i >= 5) {
//             i = 0
//         }

//         setTimeout(showNextImage, 5000);
//     }

//     showNextImage()
// }

// cycleImages();