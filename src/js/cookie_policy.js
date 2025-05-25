"use strict"

const cookie_policy = document.querySelector(".cookie_policy")
const cookie_policy_button = document.querySelector(".cookie_policy_button");

cookie_policy_button.addEventListener("click", function() {
    document.cookie = "cookie_policy_accepted=yes; max-age=" + 60 * 60 * 24 * 30 + "; path=/"; // 30 Days
    
    cookie_policy.classList.add("hide");
});

function getCookieValue(name) {
    const r = document.cookie.match("(^|;)\\s*" + name + "\\s*=\\s*([^;]+)");
    return r ? r[2] : "";
}

const cookieValue = getCookieValue("cookie_policy_accepted");

if (cookieValue === "yes") {
    cookie_policy.classList.add("hide");
}