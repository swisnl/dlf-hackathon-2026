import { reactive } from 'vue'

// Wijsje's state, shared by every page so the conversation survives navigating. It is never stored on the server (#31).
export const wijsje = reactive({ open: false, messages: [], nudgeSnoozedUntil: 0 })

// Lets any page (for example the dashboard tip) open the chat panel in the corner.
export const openWijsje = () => {
    wijsje.open = true
}
