function toggleIsAlertRequired() {
    const isAlertRequiredInput = document.getElementById("isAlertRequired");
    const notifyByEmailValue = document.querySelector('input[name="notifyByEmail"]:checked').value;
    const notifyBySmsValue = document.querySelector('input[name="notifyBySms"]:checked').value;

    if (notifyByEmailValue == 1 || notifyBySmsValue == 1) {
        isAlertRequiredInput.value = 1;
    } else {
        isAlertRequiredInput.value = 0;
    }
}

async function bindIsAlertRequired() {
    const notifyByEmail = document.getElementsByName("notifyByEmail");
    const notifyBySms = document.getElementsByName("notifyBySms");

    notifyByEmail.forEach((radio) => {
        radio.addEventListener("click", () => {
            toggleIsAlertRequired();
        });
    });
    notifyBySms.forEach((radio) => {
        radio.addEventListener("click", () => {
            toggleIsAlertRequired();
        });
    });

    toggleIsAlertRequired();
}

async function bindBrtLabelEvents() {
    const toggleAdvancedFields = document.getElementById("toggleAdvancedFields");
    if (toggleAdvancedFields) {
        toggleAdvancedFields.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            $("#advancedFieldsCard").slideToggle(200);
        });
    }

    // Precompila la tabella colli e i campi riepilogo se disponibili
    if (window.initialParcels && Array.isArray(window.initialParcels)) {
        const tbody = document.getElementById("table-brt-measure").querySelector("tbody");
        tbody.innerHTML = "";
        let weight = 0,
            volume = 0;
        window.initialParcels.forEach((parcel) => {
            const row = document.getElementById("table-row").content.cloneNode(true);
            row.querySelector('input[name="length"]').value = parcel.length || "";
            row.querySelector('input[name="width"]').value = parcel.width || "";
            row.querySelector('input[name="height"]').value = parcel.height || "";
            row.querySelector('input[name="weight"]').value = parcel.weight || "";
            row.querySelector('input[name="volume"]').value = parcel.volume || "";
            tbody.appendChild(row);
            weight += parseFloat(parcel.weight || 0);
            volume += parseFloat(parcel.volume || 0);
        });
        bindTableEvents();
        getTableSum();
        // Valorizza i campi di riepilogo se presenti
        if (document.getElementById("weightKG")) document.getElementById("weightKG").value = weight.toFixed(2);
        if (document.getElementById("volumeM3")) document.getElementById("volumeM3").value = volume.toFixed(2);
        if (document.getElementById("numberOfParcels")) document.getElementById("numberOfParcels").value = window.initialParcels.length;
    }
}

function getTableSum() {
    const rows = document.getElementById("table-brt-measure").querySelector("tbody").querySelectorAll("tr");
    const trFoot = document.getElementById("table-brt-measure").querySelector("#total-row");
    let weight = 0;
    let volume = 0;
    const colli = rows.length;

    for (let i = 0; i < rows.length; i++) {
        weight += parseFloat(rows[i].querySelector('input[name="weight"]').value);
        volume += parseFloat(rows[i].querySelector('input[name="volume"]').value);
    }
    trFoot.querySelector('input[name="weightKG"]').value = weight.toFixed(2);
    trFoot.querySelector('input[name="volumeM3"]').value = volume.toFixed(2);
    trFoot.querySelector('input[name="numberOfParcels"]').value = colli;
}

function addTableRows(parcels) {
    if (parcels.length > 0) {
        console.log("PARCELS", parcels);

        parcels.forEach((p) => {
            const row = document.getElementById("table-row").content.cloneNode(true);
            row.querySelector('input[name="length"]').value = Number(p.x || 0).toFixed(0);
            row.querySelector('input[name="width"]').value = Number(p.y || 0).toFixed(0);
            row.querySelector('input[name="height"]').value = Number(p.z || 0).toFixed(0);
            row.querySelector('input[name="weight"]').value = Number(p.weight || 0).toFixed(3);
            row.querySelector('input[name="volume"]').value = Number(p.volume || 0).toFixed(3);
            document.getElementById("table-brt-measure").querySelector("tbody").appendChild(row);
        });

        bindTableEvents();
        getTableSum();
        setFocusOnRow();
    } else {
        addTableRow();
    }
}

function addTableRow() {
    const row = document.getElementById("table-row").content.cloneNode(true);
    document.getElementById("table-brt-measure").querySelector("tbody").appendChild(row);
    bindTableEvents();
    getTableSum();
    setFocusOnRow();
}

function setFocusOnRow() {
    const rows = document.getElementById("table-brt-measure").querySelector("tbody").querySelectorAll("tr");
    const lastRow = rows[rows.length - 1];
    lastRow.querySelector('input[name="length"]').focus();
}

function bindTableEvents() {
    const addButtons = document.querySelectorAll('button[name="addParcels"]');
    const delButtons = document.querySelectorAll('button[name="deleteParcels"]');

    addButtons.forEach((button) => {
        button.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            addTableRow();
        });
    });

    delButtons.forEach((button) => {
        button.addEventListener("click", (e) => {
            const tr = button.closest("tr");
            const rows = tr.closest("tbody").querySelectorAll("tr").length;
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            tr.remove();
            if (rows <= 1) {
                addTableRow();
            }
            getTableSum();
        });
    });

    const inputs = document.getElementById("table-brt-measure").querySelector("tbody").querySelectorAll('input[name="length"], input[name="width"], input[name="height"], input[name="weight"]');

    inputs.forEach((input) => {
        input.addEventListener("blur", (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            calcMeasure(input.closest("tr"));
            getTableSum();
        });

        input.addEventListener("focus", (e) => {
            //seleziono tutto il contenuto
            const input = e.target;
            if (input) {
                input.select();
            }
        });
    });
}

function calcMeasure(tr) {
    const length = parseFloat(tr.querySelector('input[name="length"]').value);
    const width = parseFloat(tr.querySelector('input[name="width"]').value);
    const height = parseFloat(tr.querySelector('input[name="height"]').value);
    const volume = Number((length * width * height) / 1000000000).toFixed(3);
    const weight = Number(parseFloat(tr.querySelector('input[name="weight"]').value)).toFixed(3);
    tr.querySelector('input[name="volume"]').value = volume;
    tr.querySelector('input[name="weight"]').value = weight;
}

async function showBrtLabelForm(id_order) {
    const confirm = await swalConfirm("Creare la richiesta di segnacollo?");
    if (!confirm) return;
    Swal.fire({
        title: "Caricamento modulo etichetta...",
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        // Sostituisci l'URL con quello del tuo endpoint che restituisce il form renderizzato
        const response = await fetch(ajaxLabelFormController, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                ajax: true,
                action: "labelForm",
                id_order: id_order
            })
        });

        if (!response.ok) throw new Error("Errore nel caricamento del form");
        const json = await response.json();
        const html = json.html || "<div class='alert alert-danger text-center'>Errore nel caricamento del form</div>";
        const parcels = json.parcels || [];
        // Mostra il form nella modale
        Swal.fire({
            html: html,
            width: "90%",
            customClass: {
                popup: "brt-label-modal"
            },
            showConfirmButton: false,
            showCancelButton: false,
            didOpen: () => {
                // Applica le classi CSS per fullscreen se non già presenti
                const popup = document.querySelector(".swal2-popup.brt-label-modal");
                if (popup && !popup.classList.contains("swal2-fullscreen")) {
                    popup.classList.add("swal2-fullscreen");
                }
                addTableRows(parcels);
                bindTableEvents();
                brtLabelFormLoaded();
                bindBrtLabelEvents();
                bindIsAlertRequired();
            }
        });
    } catch (e) {
        Swal.fire({
            icon: "error",
            title: "Errore",
            text: e.message || "Errore sconosciuto nel caricamento del form."
        });
    }
}

async function deleteBrtLabelForm(id_order) {
    const confirm = await swalConfirm("Eliminare il segnacollo?");
    if (!confirm) return;

    try {
        Swal.fire({ title: "Invio richiesta eliminazione...", allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });
        const response = await fetch(ajaxLabelFormController, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                ajax: true,
                action: "deleteLabel",
                id_order: id_order
            })
        });
        const json = await response.json();
        if (json.success) {
            Swal.fire({ icon: "success", title: "Etichetta eliminata!", html: json.message || "Richiesta inviata con successo." });
            //aspetta 2 secondi e ricarica la pagina
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            Swal.fire({ icon: "error", title: "Errore", html: json.message || "Errore nella creazione dell'etichetta." });
        }
    } catch (e) {
        Swal.fire({ icon: "error", title: "Errore", html: e.message || "Errore nella richiesta." });
    }
}

async function createLabelRequest(e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    const form = document.getElementById("brt-label-form");
    const formData = new FormData(form);
    const request = {};
    formData.forEach((value, key) => {
        request[key] = value;
    });
    // Colleziona i dati dei colli
    const parcels = [];
    const rows = document.getElementById("table-brt-measure").querySelector("tbody").querySelectorAll("tr");
    rows.forEach((row) => {
        const index = row.rowIndex - 1;
        const barcode = document.querySelector('input[name="numericSenderReference"]').value + "-" + (index + 1);
        const length = row.querySelector('input[name="length"]').value;
        const width = row.querySelector('input[name="width"]').value;
        const height = row.querySelector('input[name="height"]').value;
        const weight = Number(row.querySelector('input[name="weight"]').value).toFixed(3);
        const volume = Number((length * width * height) / 1000000000).toFixed(3);
        if (barcode || length || width || height || weight || volume) {
            parcels.push({
                barcode: barcode,
                length_mm: length,
                width_mm: width,
                height_mm: height,
                weight_kg: weight,
                volume_m3: volume
            });
        }
    });

    request.parcels = parcels;
    try {
        Swal.fire({ title: "Invio richiesta segnacollo...", allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });
        const response = await fetch(ajaxLabelFormController, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                ajax: true,
                action: "createLabelRequest",
                data: request
            })
        });
        const json = await response.json();
        if (json.success) {
            showPrintLabelButton(e);
            Swal.fire({ icon: "success", title: "Segnacollo creato!", html: json.message || "Richiesta inviata con successo." });
        } else {
            Swal.fire({ icon: "error", title: "Errore", html: json.message || "Errore nella creazione del segnacollo." });
        }
    } catch (e) {
        Swal.fire({ icon: "error", title: "Errore", html: e.message || "Errore nella richiesta." });
    }
}
