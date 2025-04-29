document.addEventListener("DOMContentLoaded", (e) => {
    console.log("DOMCONTENT loaded: showPrintLabelButton.js");

    showPrintLabelButton(e);
});

async function showPrintLabelButton(e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    const orderActions = document.querySelector(".order-actions");
    const orderActionsPrint = orderActions.querySelector(".order-actions-print").querySelector(".input-group");
    const response = await fetch(ajaxLabelFormController, {
        method: "POST",
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({
            ajax: 1,
            action: "showPrintLabelButton",
            id_order: orderId
        })
    });
    const json = await response.json();
    const success = json.success || false;
    const canShow = json.labelShown || false;
    if (success && canShow) {
        const icon = document.createElement("i");
        icon.className = "material-icons";
        icon.textContent = "print";
        const label = document.createElement("span");
        label.textContent = "Stampa Etichetta";
        const button = document.createElement("button");
        button.type = "button";
        button.className = "btn btn-info";
        button.appendChild(icon);
        button.appendChild(label);
        button.addEventListener("click", () => {
            printLabel(orderId);
        });
        orderActionsPrint.appendChild(button);
    }
}

async function printLabel(orderId) {
    const response = await fetch(ajaxLabelFormController, {
        method: "POST",
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({
            ajax: 1,
            action: "printLabel",
            numericSenderReference: orderId
        })
    });
    const json = await response.json();
    const success = json.success || false;
    if (success) {
        const pdf = json.stream || null;
        if (pdf) {
            // Decodifica base64 in array di byte
            const byteCharacters = atob(pdf);
            const byteNumbers = new Array(byteCharacters.length);
            for (let i = 0; i < byteCharacters.length; i++) {
                byteNumbers[i] = byteCharacters.charCodeAt(i);
            }
            const byteArray = new Uint8Array(byteNumbers);

            // Crea Blob e URL temporaneo
            const blob = new Blob([byteArray], { type: "application/pdf" });
            const blobUrl = URL.createObjectURL(blob);

            // Apri il PDF in una nuova finestra
            window.open(blobUrl, "_blank");
        }
    }
}
