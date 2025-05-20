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
