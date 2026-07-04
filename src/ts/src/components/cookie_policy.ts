import { getCookieValue } from "../utils/getCookieValue.js"

"use strict"

document.addEventListener("DOMContentLoaded", function():void {
    // Cookie Policy

    // Variables

    const cookie_policy:HTMLDivElement = document.querySelector(".cookie_policy") as HTMLDivElement // Gets The Cookie Policy Container
    const accept_all:HTMLButtonElement = cookie_policy.querySelector(".accept_all") as HTMLButtonElement // Gets The Accept All Button
    const cookie_value:string|null = getCookieValue("cookie_policy_accepted")

    // Events

    // Accept All Button Click Functionality
    accept_all.addEventListener("click", function():void {
        document.cookie = "cookie_policy_accepted=yes; max-age=" + 60 * 60 * 24 * 30 * 3 + "; path=/" // Creates 90 Days Timed Cookie
        cookie_policy.classList.add("hide") // Hides The Cookie Policy Container
    })

    // Initialization

    if(cookie_value === "yes") cookie_policy.classList.add("hide") // Hides The Cookie Policy Container
})