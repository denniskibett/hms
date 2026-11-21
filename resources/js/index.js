import "@fortawesome/fontawesome-free/css/all.min.css";

import Alpine from "alpinejs";
import persist from "@alpinejs/persist";

// Calendar page (only runs where #calendar exists)
import "./components/calendar-init.js";

Alpine.plugin(persist);
window.Alpine = Alpine;
Alpine.start();
