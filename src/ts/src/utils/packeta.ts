// Packeta Widget Types (https://docs.packeta.com/docs/pudo-delivery/widget)

export interface PacketaPickupPoint {
    id:string|number,
    name:string
}

interface PacketaWidget {
    pick(
        api_key:string,
        callback:(pickup_point:PacketaPickupPoint|null) => void,
        options?:Record<string, string>,
        container?:HTMLElement
    ):void
}

declare global {
    interface Window {
        Packeta?:{
            Widget:PacketaWidget
        }
    }
}

const PACKETA_API_KEY:string = "1e86dc1a3e6601cb" // Stores The Packeta API Key

// Function For Open The Packeta Widget Inside The Given Container
export function pickPacketaPoint(container:HTMLElement, callback:(pickup_point:PacketaPickupPoint) => void):void {
    if(!window.Packeta) {
        console.error("Packeta widget library is not loaded.")
        return
    }

    window.Packeta.Widget.pick(
        PACKETA_API_KEY,

        (pickup_point:PacketaPickupPoint|null):void => {
            if(pickup_point) callback(pickup_point) // Returns The Selected Pickup Point
        },

        {
            country: "sk",
            language: "sk"
        },

        container
    )
}
