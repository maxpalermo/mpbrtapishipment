function showDeleteBrtLabel() {
    // Creo un form SWAL2 con due input, numericSenderReference e alphanumericSenderReference
    // e due bottoni, uno per conferma e uno per annullamento
    // URL a cui inviare i dati con fetch
    const targetUrl = controllerURL;

    Swal.fire({
        title: "Inserisci i riferimenti del segnacollo",
        // HTML per creare i due campi input all'interno del modal
        html: `
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
        `,
        icon: "warning", // Icona per indicare un'azione potenzialmente distruttiva
        showCancelButton: true,
        confirmButtonText: "ELIMINA",
        cancelButtonText: "ANNULLA",
        confirmButtonColor: "#d33", // Colore rosso per il pulsante ELIMINA
        cancelButtonColor: "#3085d6",
        focusConfirm: false, // Evita che il focus immediato sul pulsante causi invio con Invio

        // Opzionale: Metti il focus sul primo campo quando il modal si apre
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

            // --- Validazione (Esempio base) ---
            if (!numericValue || !alphaValue) {
                Swal.showValidationMessage("Entrambi i campi sono obbligatori");
                return false; // Interrompe la conferma
            }
            // Aggiungi qui altre validazioni se necessario (es. formato numerico, lunghezza)
            // --- Fine Validazione ---

            try {
                // Prepara i dati da inviare
                const dataToSend = {
                    numericSenderReference: numericValue,
                    alphanumericSenderReference: alphaValue
                };

                console.log("Invio dati:", dataToSend);

                // Esegui la chiamata fetch (POST come esempio)
                const response = await fetch(targetUrl + "&action=deleteLabel&ajax=1", {
                    method: "POST", // O 'DELETE', 'PUT' a seconda della tua API
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify(dataToSend)
                });

                // Controlla se la risposta del server è OK (status 2xx)
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

                // Ritorna i dati (o semplicemente true) per indicare successo a Swal2
                return resultData; // O return true;
            } catch (error) {
                // Gestisci errori di rete o altri errori durante fetch/elaborazione
                console.error("Errore in preConfirm:", error);
                Swal.showValidationMessage(`Richiesta fallita: ${error.message || "Errore sconosciuto"}`);
                return false; // Impedisce la chiusura
            }
        }
    }).then((result) => {
        // Questa parte viene eseguita DOPO che preConfirm ha risolto (o è stato premuto ANNULLA)

        if (result.isConfirmed) {
            // Eseguito solo se l'utente ha cliccato ELIMINA e preConfirm ha avuto successo
            // 'result.value' contiene ciò che è stato ritornato dalla promise di preConfirm
            console.log("Risultato preConfirm:", result.value);
            Swal.fire(
                "Inviato!", // O 'Eliminato!' a seconda dell'azione
                "I dati sono stati inviati con successo.", // Personalizza messaggio
                "success"
            );
        } else if (result.isDismissed) {
            // Eseguito se l'utente ha cliccato ANNULLA o ha chiuso il modal in altro modo
            console.log("Operazione annullata dall'utente.");
            // Non è necessario fare nulla qui, il form si chiude e si "pulisce" automaticamente
        }
    });
}

document.addEventListener("DOMContentLoaded", async (e) => {
    const bulkActions = document.querySelector(".btn-group.bulk-actions.dropup");
    const printPdfBtn = bulkActions.querySelector("i.icon-barcode").closest("a");
    const printAllPdfBtn = bulkActions.querySelector("i.icon-barcode.text-danger").closest("a");

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
            if (data.pdf) {
                // Decodifica la stringa base64 in un array di byte
                const byteCharacters = atob(data.pdf);
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
            if (data.pdf) {
                // Decodifica la stringa base64 in un array di byte
                const byteCharacters = atob(data.pdf);
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
        });
    }
});
