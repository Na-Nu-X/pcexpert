"use strict"

// Disappearing Navigation Bar

const navigation_bar = document.querySelector(".navigation_bar")

function handleScroll() {
    if (window.innerWidth <= 800) {
        navigation_bar.style.opacity = 1;
        return;
    }

    if (window.scrollY > window.innerHeight - 81) {
        navigation_bar.style.opacity = 0;
    } else {
        navigation_bar.style.opacity = 1;
        navigation_bar.style.transition = "0.5s";
    }
}

function handleMouseOver() {
    if (window.innerWidth > 800) {
        navigation_bar.style.opacity = 1;
        navigation_bar.style.transition = "1s";
    }
}

function handleMouseOut() {
    if (window.innerWidth > 800 && window.scrollY > window.innerHeight - 81) {
        navigation_bar.style.opacity = 0;
    }
}

navigation_bar.addEventListener("mouseover", handleMouseOver);
navigation_bar.addEventListener("mouseout", handleMouseOut);

window.addEventListener("scroll", handleScroll);

// Navigation Bar Icons

const navigation_icon = document.querySelector(".navigation_icon")
const navigation_bar_item = document.querySelectorAll(".navigation_bar_item")

navigation_icon.addEventListener("click", function(event) {
    if(document.querySelector(".fa-solid").classList[1] === "fa-bars") {
        document.querySelector(".fa-solid").classList.remove("fa-bars")
        document.querySelector(".fa-solid").classList.add("fa-xmark")

        navigation_bar_item.forEach(function(one_item) {
            one_item.style.display = "block"
        })
    }

    else if(document.querySelector(".fa-solid").classList[1] === "fa-xmark") {
        document.querySelector(".fa-solid").classList.remove("fa-xmark")
        document.querySelector(".fa-solid").classList.add("fa-bars")

        navigation_bar_item.forEach(function(one_item) {
            one_item.style.display = "none"
        })
    }
})

// Auto Scroll Buttons

function autoScrollButtons(button, scroll_to) {
    button.addEventListener("click", function(event){
        event.preventDefault()

        window.scrollTo({
            top: scroll_to.offsetTop - 90,
            behavior: "smooth"
        })
    })
}

const about_us = document.querySelector(".about_us")
const our_services = document.querySelector(".our_services")
const contact = document.querySelector(".contact")
const users = document.querySelector(".users")
const error_log = document.querySelector(".error_log")

const scroll_about_us = document.querySelector(".scroll_about_us")
const scroll_our_services = document.querySelector(".scroll_our_services")
const scroll_contact = document.querySelector(".scroll_contact")
const scroll_users = document.querySelector(".scroll_users")
const scroll_error_log = document.querySelector(".scroll_error_log")

if(scroll_about_us && about_us) {
    autoScrollButtons(scroll_about_us, about_us)
}

if(scroll_our_services && our_services) {
    autoScrollButtons(scroll_our_services, our_services)
}

if(scroll_contact && contact) {
    autoScrollButtons(scroll_contact, contact)
}

if(scroll_users && users) {
    autoScrollButtons(scroll_users, users)
}

if(scroll_error_log && error_log) {
    autoScrollButtons(scroll_error_log, error_log)
}

const user_rating = document.querySelector(".user_rating")
const reviews = document.querySelector(".reviews")

if(user_rating && reviews) {
    autoScrollButtons(user_rating, reviews)
}