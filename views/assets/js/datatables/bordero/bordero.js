/**
 * Aggiorna i dati di una DataTable.
 * @param {Array} dataRows - L'array dei dati da visualizzare nella tabella.
 * @param {string} tableId - L'id della tabella (senza #).
 */
function refreshDataTable(dataRows, tableId) {
    var table = $("#" + tableId).DataTable();
    table.clear();
    table.rows.add(dataRows);
    table.draw();
}

async function showLastBordero() {
    const response = await fetch(borderoUrl, {
        method: "POST",
        body: JSON.stringify({
            searchTerm: $("#toolbar-search-input").val()
        })
    });
    const data = await response.json();
    const title = data.title || "BORDERO";
    const icon = data.icon || "list_alt";
    document.getElementById("datatable-title").textContent = title;
    document.getElementById("datatable-title-icon").textContent = icon;
    refreshDataTable(data.rows, "bordero-datatable");
}

async function showHistory() {
    const response = await fetch(borderoHistoryUrl, {
        method: "POST",
        body: JSON.stringify({
            searchTerm: $("#toolbar-search-input").val()
        })
    });
    const data = await response.json();
    const title = data.title || "BORDERO";
    const icon = data.icon || "history";
    document.getElementById("datatable-title").textContent = title;
    document.getElementById("datatable-title-icon").textContent = icon;
    refreshDataTable(data.rows, "bordero-datatable");
}

async function printLabels(button) {
    const ids = [];
    const rowId = button.getAttribute("data-row-id");
    ids.push(rowId);
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

// Funzione per collegare la barra di ricerca della toolbar a DataTables
function setupToolbarDatatableSearch(datatableInstance) {
    if (searchBtn && searchInput && datatableInstance) {
        searchBtn.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            datatableInstance.search(searchInput.value).draw();
        });
        // Permetti anche la ricerca premendo INVIO nell'input
        searchInput.addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                datatableInstance.search(searchInput.value).draw();
            }
        });
    }
}

async function newBrtLabel() {
    const response = await fetch(borderoNewLabelUrl, {
        method: "POST",
        body: JSON.stringify({
            showOrderId: true
        })
    });
    const data = await response.json();
    const dialog = data.dialog || null;
    if (dialog) {
        const template = document.createElement("template");
        template.innerHTML = dialog.trim();
        const dialogElement = template.content.querySelector("dialog");
        if (dialogElement) {
            // Rimuovi eventuale dialog precedente con stesso id
            const oldDialog = document.getElementById(dialogElement.id);
            if (oldDialog) oldDialog.remove();
            document.body.appendChild(dialogElement);
            dialogElement.showModal();
        } else {
            alert("Errore durante la creazione del form etichetta.");
        }
    } else {
        alert("Errore durante la creazione del form etichetta.");
    }
}

function toggleCashOnDelivery(value) {
    console.log("toggleCashOnDelivery", value);
    const cashOnDelivery = document.getElementById("cash_on_delivery");
    const codPaymentType = document.getElementById("cod_payment_type");
    if (value === 1) {
        cashOnDelivery.disabled = false;
        codPaymentType.disabled = false;
    } else {
        cashOnDelivery.disabled = true;
        codPaymentType.disabled = true;
    }
}

async function fillOrderDetails(button) {
    const orderIdInputId = button.getAttribute("data-input-id");
    const orderId = document.getElementById(orderIdInputId).value;
    if (!orderId) {
        alert("Inserisci un id ordine valido.");
        return;
    }
    const request = await fetch(borderoFillOrderDetailsUrl, {
        method: "POST",
        body: JSON.stringify({
            orderId: orderId
        })
    });
    const response = await request.json();
    const success = response.success || false;
    if (success) {
        const orderDetails = response.data.details || null;
        if (orderDetails) {
            Object.entries(orderDetails).forEach(([key, value]) => {
                console.log("element", key, value);

                const el = document.getElementById(key);
                if (el) {
                    el.value = value;
                }
            });
        } else {
            alert("Ordine non trovato");
        }
    }
}

async function showPreferences() {
    const response = await fetch(borderoPreferencesUrl, { method: "GET" });
    const data = await response.json();
    const dialog = data.dialog || null;
    if (dialog) {
        const template = document.createElement("template");
        template.innerHTML = dialog.trim();
        const dialogElement = template.content.querySelector("dialog");
        if (dialogElement) {
            // Rimuovi eventuale dialog precedente con stesso id
            const oldDialog = document.getElementById(dialogElement.id);
            if (oldDialog) oldDialog.remove();
            document.body.appendChild(dialogElement);
            dialogElement.showModal();
        } else {
            alert("Errore durante la creazione del form impostazioni.");
        }
    } else {
        alert("Errore durante la creazione del form impostazioni.");
    }
}

async function saveSettings() {
    const dialog = document.getElementById("brt-label-settings-dialog");
    const form = document.getElementById("form-brt-label-settings");
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    const response = await fetch(borderoSaveSettingsUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({ preferences: data })
    });
    const result = await response.json();
    const success = result.success || false;
    if (success) {
        dialog.close();
        alert("Impostazioni salvate.");
    } else {
        alert(
            result.message ||
                "Errore durante il salvataggio delle impostazioni."
        );
    }
}

document.addEventListener("DOMContentLoaded", function() {
    searchBtn = document.getElementById("toolbar-search-btn");
    searchInput = document.getElementById("toolbar-search-input");

    if (searchBtn) {
        setupToolbarDatatableSearch(dt);
        // Applico questo style alla pagina
        var filterDiv = document.querySelector("div.dataTables_filter");
        if (filterDiv) {
            filterDiv.style.display = "none";
        }
    }

    dt = $("#bordero-datatable").DataTable({
        ajax: {
            url: borderoUrl,
            type: "POST",
            dataSrc: "rows",
            // Se vuoi inviare parametri custom lato server (es: searchTerm), puoi aggiungerli qui
            data: function(d) {
                d.searchTerm = searchInput ? searchInput.value : "";
            }
        },
        paging: true,
        searching: true,
        responsive: false,
        scrollX: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/it-IT.json"
        },
        order: [[1, "desc"]],
        columns: [
            {
                data: null,
                name: "select",
                orderable: false,
                render: function(data, type, row) {
                    return `<input type="checkbox" class="row-checkbox" value="${row.id}" onclick="event.stopPropagation();"/>`;
                }
            },
            { data: "id", name: "id" },
            { data: "bordero", name: "bordero" },
            {
                data: "numeric_sender_reference",
                name: "numeric_sender_reference"
            },
            {
                data: "alphanumeric_sender_reference",
                name: "alphanumeric_sender_reference"
            },
            { data: "consignee_company_name", name: "consignee_company_name" },
            { data: "consignee_address", name: "consignee_address" },
            { data: "consignee_zip_code", name: "consignee_zip_code" },
            { data: "consignee_city", name: "consignee_city" },
            {
                data: "consignee_province_abbreviation",
                name: "consignee_province_abbreviation"
            },
            {
                data: "consignee_country_abbreviation_iso_alpha_2",
                name: "consignee_country_abbreviation_iso_alpha_2"
            },
            { data: "consignee_contact_name", name: "consignee_contact_name" },
            { data: "consignee_telephone", name: "consignee_telephone" },
            {
                data: "consignee_mobile_phone_number",
                name: "consignee_mobile_phone_number"
            },
            { data: "consignee_email", name: "consignee_email" },
            { data: "cash_on_delivery", name: "cash_on_delivery" },
            { data: "number_of_parcels", name: "number_of_parcels" },
            { data: "weight_kg", name: "weight_kg" },
            { data: "volume_m3", name: "volume_m3" },
            {
                data: "print_date",
                name: "print_date",
                render: function(data, type, row, meta) {
                    console.log("data received:", data);
                    if (!data) return "--";
                    // controlla se la data ha questo formato 0000-00-00 00:00:00
                    if (data === "0000-00-00 00:00:00") return "--";

                    var parts = data.split(" ")[0].split("-");
                    if (parts.length !== 3) return data;

                    return parts[2] + "/" + parts[1] + "/" + parts[0];
                }
            },
            {
                data: null,
                name: "actions",
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    console.log("data action received:", data);
                    if (type === "display") {
                        return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-info btn-sm" data-tooltip-text="Vedi etichetta" data-row-id="${row.id}" onclick="viewLabel(this)" title="Vedi etichetta">
                                    <span class="material-icons">preview</span>
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" data-tooltip-text="Stampa etichetta" data-row-id="${row.id}" onclick="printLabels(this)" title="Stampa etichetta">
                                    <span class="material-icons">print</span>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" data-tooltip-text="Elimina etichetta" data-row-id="${row.id}" onclick="deleteLabel(this)" title="Elimina etichetta">
                                    <span class="material-icons">delete</span>
                                </button>
                            </div>
                            `;
                    }
                    return "--dd--";
                }
            }
        ]
    });

    $("#bordero-datatable").on("preDraw.dt", function() {
        $(this)
            .find("tbody")
            .fadeOut(150);
    });
    $("#bordero-datatable").on("draw.dt", function() {
        $(this)
            .find("tbody")
            .fadeIn(150);
    });
});
