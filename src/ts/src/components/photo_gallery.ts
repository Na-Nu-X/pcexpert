"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Photo Gallery

    // Variables

    const photo_gallery:HTMLElement = document.querySelector(".photo_gallery") as HTMLElement // Gets The Photo Gallery
    const image:NodeListOf<HTMLDivElement> = photo_gallery.querySelectorAll<HTMLDivElement>(".image") // Gets The Image Container

    // Events

    image.forEach(function(one_image:HTMLDivElement):void {
        one_image.addEventListener("click", function () {
            image.forEach(one_image => one_image.classList.remove("active")) // Deletes The Active Class From All Images
            one_image.classList.add("active") // Adds The Active Class For The Image
        })
    })
})