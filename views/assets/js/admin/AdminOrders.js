let dialogBrtLabel = null;
let labelManagerInstance = null;

document.addEventListener("DOMContentLoaded", e => {
    console.log("DomContentLoaded - AdminOrder.js");
    dialogBrtLabel = document.getElementById("brt-label-dialog");
});

async function instanceLabelManager() {
    labelManagerInstance = new LabelManager(brtLabelUrls);
    await labelManagerInstance.init();
    labelManagerInstance.fillOrderDetails(orderId);
    document.getElementById("brt-label-toolbar").style.display = "none";
    labelManagerInstance.showModal();
}

async function showBrtLabelForm() {
    await instanceLabelManager();
}

async function createLabel() {
    labelManagerInstance.createLabel();
}

async function showLastBordero() {
    labelManagerInstance.closeModal();
    printLabel(orderId);
}

async function readParcels() {
    const numericSenderReferenceElement = document.getElementById("numeric_sender_reference");
    if (numericSenderReferenceElement) {
        const numericSenderReference = numericSenderReferenceElement.value;
        if (numericSenderReference) {
            await labelManagerInstance.readParcels(numericSenderReference);
        } else {
            alert("Inserisci un identificativo etichetta valido (numericSenderReference)");
        }
    } else {
        alert("Elemento NumericSenderReference non trovato");
    }
}

async function printLabel(orderId) {
    const ids = [orderId];
    const response = await fetch(borderoPrintLabelsUrl, {
        method: "POST",
        body: JSON.stringify({
            ids: ids
        })
    });
    const data = await response.json();
    const success = data.success || false;
    if (success) {
        const pdf64 = data.pdf || "";
        // Decodifica base64 in array di byte
        const byteCharacters = atob(pdf64);
        const byteNumbers = new Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);
        const blob = new Blob([byteArray], { type: "application/pdf" });
        const url = URL.createObjectURL(blob);
        window.open(url, "_blank");
    } else {
        alert(data.message || "Errore durante la stampa");
    }
}
