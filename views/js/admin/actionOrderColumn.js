document.addEventListener("DOMContentLoaded", async (e) => {
    const table = document.getElementById("order_grid_table");
    if (!table) return false;

    const rows = table.querySelectorAll("tbody tr");
    rows.forEach((row) => {
        const dataIdentifier = row.querySelector("td[data-identifier]");
        if (dataIdentifier) {
            const orderID = dataIdentifier.getAttribute("data-identifier");
            const columnActions = row.querySelector("td.action-type.column-actions");
            const btnGroup = columnActions.querySelector(".btn-group");
            fetch(getLabelLinkURL + "&numericSenderReference=" + orderID)
                .then((response) => response.json())
                .then((json) => {
                    const success = json.success || false;
                    if (success) {
                        const icon = document.createElement("i");
                        icon.className = "fa fa-barcode";
                        const a = document.createElement("a");
                        a.type = "a";
                        a.className = "btn tooltip-link js-link-row-action dropdown-item inline-dropdown-item grid-print-brt-label";
                        a.href = printLabelLinkURL + "&numericSenderReference=" + orderID;
                        a.target = "_blank";
                        a.title = "Stampa segnacolli BRT";
                        a.appendChild(icon);
                        btnGroup.appendChild(a);
                    }
                })
                .catch((error) => {
                    console.error("Errore durante la chiamata API:", error);
                });
        }
    });
});
