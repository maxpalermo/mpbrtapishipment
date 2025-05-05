async function showDeleteBrtLabel() {
    // Creo un form SWAL2 con due input, numericSenderReference e alphanumericSenderReference
    // e due bottoni, uno per conferma e uno per annullamento
    // URL a cui inviare i dati con fetch
    const targetUrl = controllerURL;
    // Form Input dati
    const html = `
            <div class="row" style="width:90%; margin: 0 auto;">
                <div class="col-md-10">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="swal-input-sender-numeric" style="display: block; text-align: left; margin-bottom: .25rem;">Riferimento numerico:</label>
                        <input id="swal-input-sender-numeric" class="swal2-input" placeholder="Es. 12345" style="width: 90%;">
                    </div>
                    <div class="form-group">
                        <label for="swal-input-sender-alpha" style="display: block; text-align: left; margin-bottom: .25rem;">Riferimento alfanumerico:</label>
                        <input id="swal-input-sender-alpha" class="swal2-input" placeholder="Es. ABCD001" style="width: 90%;">
                    </div>
                </div>
            </div>
        `;

    const result = await Swal.fire({
        title: "Inserisci i riferimenti del segnacollo",
        html: html,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "ELIMINA",
        cancelButtonText: "ANNULLA",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        focusConfirm: false,
        didOpen: () => {
            const numericInput = document.getElementById("swal-input-sender-numeric");
            if (numericInput) {
                numericInput.focus();
            }
        },
        // Funzione eseguita PRIMA di confermare (click su ELIMINA)
        // Deve restituire una Promise se fa operazioni asincrone (come fetch)
        // Swal2 mostrerà automaticamente l'icona di caricamento
        preConfirm: async () => {
            // Leggi i valori dai campi input
            const numericValue = document.getElementById("swal-input-sender-numeric").value;
            const alphaValue = document.getElementById("swal-input-sender-alpha").value;

            if (!numericValue || !alphaValue) {
                Swal.showValidationMessage("Entrambi i campi sono obbligatori");
                return false;
            }
            if (!/^\d+$/.test(numericValue)) {
                Swal.showValidationMessage("Il campo Riferimento numerico deve essere un numero");
                return false;
            }
            if (numericValue.length > 15) {
                Swal.showValidationMessage("Il campo Riferimento numerico deve contenere massimo 15 caratteri");
                return false;
            }
            if (alphaValue.length > 15) {
                Swal.showValidationMessage("Il campo Riferimento alfanumerico deve contenere massimo 15 caratteri");
                return false;
            }
            const dataToSend = {
                ajax: 1,
                action: "deleteLabel",
                numericSenderReference: numericValue,
                alphanumericSenderReference: alphaValue
            };

            return dataToSend;
        }
    });

    console.log("DELETELABEL:", result);

    if (result.isConfirmed) {
        const dataToSend = result.value;

        const response = await fetch(targetUrl, {
            method: "POST", // O 'DELETE', 'GET', 'PUT' a seconda della tua API
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify(dataToSend)
        });

        if (!response.ok) {
            // Se la risposta non è OK, leggi l'errore (se disponibile) e lancialo
            // Swal.showValidationMessage mostrerà l'errore nel modal attivo
            const errorData = await response.json().catch(() => ({})); // Legge JSON o restituisce {}
            const errorMessage = errorData.message || `Errore: ${response.statusText} (${response.status})`;
            console.error("Errore fetch:", errorMessage);
            // Mostra l'errore nel popup Swal prima di rigettare la promise
            Swal.showValidationMessage(errorMessage);
            // Lanciare l'errore qui non è strettamente necessario se si usa showValidationMessage
            // throw new Error(errorMessage);
            return false; // Impedisce la chiusura e mostra il messaggio
        }

        // Se la risposta è OK, leggi i dati di risposta (se ce ne sono)
        const resultData = await response.json(); // Assumendo che il server risponda con JSON
        console.log("Risposta dal server:", resultData);

        //Visualizza il messaggio di risposta
        const executionMessage = resultData.response.response.deleteResponse.executionMessage || {
            code: "-999",
            severity: "ERROR",
            description: "Errore sconosciuto",
            message: "Nessun messaggio di esecuzione disponibile"
        };
        let alertGravity = "";

        console.log("EXECUTION MESSAGE", executionMessage);

        switch (executionMessage.severity) {
            case "ERROR":
                alertGravity = "danger";
                break;
            case "INFO":
                alertGravity = "info";
                break;
            case "WARNING":
                alertGravity = "warning";
                break;
            case "SUCCESS":
                alertGravity = "success";
                break;
        }

        if (resultData.response.httpCode == 200) {
            Swal.fire({
                icon: alertGravity,
                title: "Operazione completata",
                html: `<div class="alert alert-${alertGravity}" style="padding: 1rem;">
                    Codice: <strong>${executionMessage.code}</strong>
                    <br>
                    Gravità: <strong>${executionMessage.severity}</strong>
                    <br>
                    Descrizione: <strong>${executionMessage.codeDesc}</strong>
                    <br>
                    Dettagli: <strong>${executionMessage.message}</strong>
                    </div>`,
                confirmButtonText: "Chiudi"
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Errore",
                text: "Errore sconosciuto",
                confirmButtonText: "Chiudi"
            });
        }
    }
}

function showPdfPage(pdfData) {
    if (pdfData.pdf) {
        const byteCharacters = atob(pdfData.pdf);
        const byteNumbers = new Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);

        // Crea un Blob dal PDF
        const blob = new Blob([byteArray], { type: "application/pdf" });

        // Crea un URL temporaneo per il Blob
        const blobUrl = URL.createObjectURL(blob);

        // Apri il PDF in una nuova finestra/scheda
        window.open(blobUrl, "_blank");

        // (opzionale) Rilascia l'URL dopo un po' di tempo
        // setTimeout(() => URL.revokeObjectURL(blobUrl), 10000);
    } else {
        Swal.fire({
            icon: "error",
            title: "Errore",
            text: "Si è verificato un errore durante la stampa. Riprova più tardi.",
            confirmButtonText: "Chiudi"
        });
    }
}

document.addEventListener("DOMContentLoaded", async (e) => {
    const bulkActions = document.querySelector(".btn-group.bulk-actions.dropup");
    if (!bulkActions) return;

    const printPdfBtn = bulkActions.querySelector("i.icon-barcode").closest("a");
    const printAllPdfBtn = bulkActions.querySelector("i.icon-barcode.text-danger").closest("a");
    const printBorderoBtn = bulkActions.querySelector("i.icon-file.text-info").closest("a");

    if (printPdfBtn) {
        printPdfBtn.setAttribute("href", "javascript:void(0);");
        printPdfBtn.removeAttribute("onclick");

        printPdfBtn.addEventListener("click", async () => {
            const table = document.getElementById("table-brt_shipment_bordero");
            const rows = table.querySelectorAll("tbody tr");
            const selectedRows = Array.from(rows).filter((row) => row.querySelector("input[type='checkbox']:checked") !== null);
            const selectedIds = Array.from(selectedRows).map((row) => row.querySelector("input[type='checkbox']").value);

            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Attenzione",
                    text: "Nessuna riga selezionata. Seleziona almeno una riga per stampare i segnacolli.",
                    confirmButtonText: "Chiudi"
                });
                return;
            }

            const request = await swalConfirm("Stampare i segnacolli selezionati?", "Stampa in attesa");
            if (!request) {
                return;
            }

            const response = await fetch(controllerURL, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({
                    ajax: 1,
                    action: "printLabels",
                    ids: selectedIds
                })
            });
            if (!response.ok) {
                Swal.fire({
                    icon: "error",
                    title: "Errore",
                    text: "Si è verificato un errore durante la stampa. Riprova più tardi.",
                    confirmButtonText: "Chiudi"
                });
                return;
            }
            const data = await response.json();
            showPdfPage(data);
        });
    }

    if (printAllPdfBtn) {
        printAllPdfBtn.setAttribute("href", "javascript:void(0);");
        printAllPdfBtn.removeAttribute("onclick");

        printAllPdfBtn.addEventListener("click", async () => {
            const request = await swalConfirm("Stampare tutti i segnacolli di questo borderò?", "Stampa in attesa");
            if (!request) {
                return;
            }

            const response = await fetch(controllerURL, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({
                    ajax: 1,
                    action: "printAllLabels"
                })
            });
            if (!response.ok) {
                Swal.fire({
                    icon: "error",
                    title: "Errore",
                    text: "Si è verificato un errore durante la stampa. Riprova più tardi.",
                    confirmButtonText: "Chiudi"
                });
                return;
            }
            const data = await response.json();
            showPdfPage(data);
        });
    }

    if (printBorderoBtn) {
        printBorderoBtn.setAttribute("href", "javascript:void(0);");
        printBorderoBtn.removeAttribute("onclick");

        printBorderoBtn.addEventListener("click", async () => {
            const request = await swalConfirm("Stampare il borderò?", "Stampa in attesa");
            if (!request) {
                return;
            }

            const response = await fetch(controllerURL, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({
                    ajax: 1,
                    action: "printBordero"
                })
            });
            if (!response.ok) {
                Swal.fire({
                    icon: "error",
                    title: "Errore",
                    text: "Si è verificato un errore durante la stampa. Riprova più tardi.",
                    confirmButtonText: "Chiudi"
                });
                return;
            }
            const data = await response.json();
            showPdfPage(data);
        });
    }
});
