// Function For Get The Cookie Value
export function getCookieValue(name:string):string|null {
    const cookie:RegExpMatchArray|null = document.cookie.match("(^|;)\\s*" + name + "\\s*=\\s*([^;]+)") // Gets The Cookie
    if(cookie) return cookie[2] as string // Returns The Cookie Value
    else return null
}