document.addEventListener("DOMContentLoaded", async (e) => {
    async function fetchBrtLabel(orderId, btnGroup) {
        try {
            const response = await fetch(getLabelLinkURL + "&numericSenderReference=" + orderId);
            const json = await response.json();
            const success = json.success || false;
            if (success) {
                const icon = document.createElement("i");
                icon.className = "fa fa-barcode text-info";
                const a = document.createElement("a");
                a.className = "btn tooltip-link grid-print-brt-label inline-dropdown-item";
                a.href = printLabelLinkURL + "&numericSenderReference=" + orderId;
                a.target = "_blank";
                a.title = "Stampa segnacolli BRT";

                a.appendChild(icon);
                btnGroup.insertAdjacentElement("afterbegin", a);

                btnGroup.style.display = "flex";
                btnGroup.style.flexWrap = "wrap";
                btnGroup.style.flexDirection = "row";
                btnGroup.style.gap = "5px";
                btnGroup.style.width = "100px";
                btnGroup.style.maxWidth = "100px";
                btnGroup.style.padding = "5px";

                const btns = btnGroup.querySelectorAll(".btn");
                btns.forEach((btn) => {
                    btn.style.width = "24px";
                    btn.style.height = "24px";
                    btn.style.display = "flex";
                    btn.style.alignItems = "start";
                    btn.style.justifyContent = "flex-start";
                    btn.style.backgroundColor = "transparent";
                    btn.style.border = "none";
                    btn.style.padding = "0";
                    btn.style.margin = "0";
                });

                btnGroup.classList.remove("justify-content-between");
                btnGroup.classList.remove("d-flex");
            }
        } catch (error) {
            console.error("Errore durante la chiamata API:", error);
        }
    }

    const table = document.getElementById("order_grid_table");
    if (!table) return false;

    const rows = table.querySelectorAll("tbody tr");
    rows.forEach((row) => {
        const dataIdentifier = row.querySelector("td[data-identifier]");
        if (dataIdentifier) {
            const orderId = dataIdentifier.getAttribute("data-identifier");

            const columnActions = row.querySelector("td.action-type.column-actions");
            if (!columnActions) return false;

            const btnGroupContainer = columnActions.querySelector(".btn-group-action.text-right");
            if (!btnGroupContainer) return false;

            const btnGroup = btnGroupContainer.querySelector(".btn-group");
            if (!btnGroup) return false;

            fetchBrtLabel(orderId, btnGroup);
        }
    });
});
