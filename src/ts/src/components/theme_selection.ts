export type Theme = "light" | "dark"

const THEME_COOKIE_MAX_AGE:number = 60 * 60 * 24 * 30 * 3 // 90 Days

// Function For Apply The Theme Without Reloading The Page
export function applyTheme(theme:Theme):void {
    const previous_theme:Theme = theme === "dark" ? "light" : "dark" // Gets The Previous Theme

    document.body.classList.replace(previous_theme, theme) || document.body.classList.add(theme) // Switches The Body Theme Class (Drives All CSS Custom Properties)

    document.cookie = `theme=${theme}; max-age=${THEME_COOKIE_MAX_AGE}; path=/; SameSite=Lax` // Creates The 90 Days Timed Cookie
}

// Function For Initialize The Theme Toggle Button
export function initializeThemeToggle(button:HTMLButtonElement):void {
    // Button Click Functionality
    button.addEventListener("click", function():void {
        const next_theme:Theme = button.dataset["theme"] === "dark" ? "light" : "dark" // Gets The Next Theme

        applyTheme(next_theme) // Applies The Theme

        button.dataset["theme"] = next_theme
        button.setAttribute("aria-label", `Prepnúť na ${next_theme === "dark" ? "svetlý" : "tmavý"} vzhľad`)
    })
}
