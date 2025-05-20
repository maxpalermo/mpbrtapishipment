// Classe centralizzata per tutte le operazioni BRT API Shipment
class MpBrtApiShipment {
    static parcels = [];
    static formControllerURL = window.ajaxLabelFormController;
    static orderID = null;

    // -- Bind Events ---
    static bindEvents() {
        this.bindToggleAdvancedFields();
        this.bindIsAlertRequired();
        this.bindTableEvents();
        this.bindRowMeasureEvents();
    }

    static bindToggleAdvancedFields() {
        const toggleAdvancedFields = document.getElementById("toggleAdvancedFields");

        if (toggleAdvancedFields) {
            toggleAdvancedFields.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                $("#advancedFieldsCard").slideToggle(200);
            });
        }
    }

    static bindIsAlertRequired() {
        const notifyByEmail = document.getElementsByName("notifyByEmail");
        const notifyBySms = document.getElementsByName("notifyBySms");
        notifyByEmail.forEach((radio) => {
            radio.addEventListener("click", () => {
                this.toggleIsAlertRequired();
            });
        });
        notifyBySms.forEach((radio) => {
            radio.addEventListener("click", () => {
                this.toggleIsAlertRequired();
            });
        });
        this.toggleIsAlertRequired();
    }

    static bindTableEvents() {
        const self = this;
        const addButtons = document.querySelectorAll('button[name="addParcels"]');
        const delButtons = document.querySelectorAll('button[name="deleteParcels"]');

        addButtons.forEach((button) => {
            button.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                self.addTableRow();
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
                    self.addTableRow();
                }
                self.getTableSum();
            });
        });
    }

    static bindRowButtonsEvents(row = null) {
        const self = this;
        let buttons = null;
        if (row) {
            buttons = row.querySelectorAll('button[name="addParcels"], button[name="deleteParcels"]');
        } else {
            buttons = document.getElementById("table-brt-measure").querySelector("tbody").querySelectorAll('button[name="addParcels"], button[name="deleteParcels"]');
        }

        buttons.forEach((button) => {
            button.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                if (button.name == "addParcels") {
                    self.addTableRow();
                } else {
                    const tr = button.closest("tr");
                    const rows = tr.closest("tbody").querySelectorAll("tr").length;
                    if (rows <= 1) {
                        self.addTableRow();
                    } else {
                        tr.remove();
                        self.getTableSum();
                    }
                }
            });
        });
    }

    static bindRowMeasureEvents(row = null) {
        const self = this;
        let inputs = null;
        if (row) {
            inputs = row.querySelectorAll('input[name="length"], input[name="width"], input[name="height"], input[name="weight"]');
        } else {
            inputs = document.getElementById("table-brt-measure").querySelector("tbody").querySelectorAll('input[name="length"], input[name="width"], input[name="height"], input[name="weight"]');
        }

        inputs.forEach((input) => {
            input.addEventListener("blur", (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                self.calcMeasure(input.closest("tr"));
                self.getTableSum();
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

    static bindMeasureEvents() {
        const self = this;
        if (self.parcels && Array.isArray(self.parcels)) {
            const tbody = document.getElementById("table-brt-measure").querySelector("tbody");
            tbody.innerHTML = "";
            let weight = 0,
                volume = 0;
            self.parcels.forEach((parcel) => {
                const row = document.getElementById("table-row").content.cloneNode(true);
                row.querySelector('input[name="length"]').value = parcel.x || "";
                row.querySelector('input[name="width"]').value = parcel.y || "";
                row.querySelector('input[name="height"]').value = parcel.z || "";
                row.querySelector('input[name="weight"]').value = parcel.weight || "";
                row.querySelector('input[name="volume"]').value = parcel.volume || "";
                tbody.appendChild(row);
                weight += parseFloat(parcel.weight || 0);
                volume += parseFloat(parcel.volume || 0);
            });
            self.getTableSum();
            if (document.getElementById("weightKG")) document.getElementById("weightKG").value = weight.toFixed(2);
            if (document.getElementById("volumeM3")) document.getElementById("volumeM3").value = volume.toFixed(2);
            if (document.getElementById("numberOfParcels")) {
                document.getElementById("numberOfParcels").value = self.parcels.length;
            }
        }
    }

    // --- Mostra le variabili statiche impostate ---
    static showStaticVariables() {
        console.log("*** SHOW STATIC VARIABLES (MpBrtApiShipment.js) ***");
        console.log("formControllerURL: " + this.formControllerURL);
        console.log("orderID: " + this.orderID);
    }

    // --- Invoca la chiamata API e restituisce il JSON ---
    static async fetch(url, data) {
        try {
            const response = await fetch(url, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify(data)
            });
            const json = await response.json();
            return json;
        } catch (error) {
            return [
                {
                    success: false,
                    action: data.action,
                    code: error.code || "-999",
                    message: error.message || "Errore nella richiesta API " + data.action
                }
            ];
        }
    }

    // --- Mostra il form di creazione richiesta di segnacollo ---
    static async showBrtLabelForm() {
        const self = this;

        const confirm = await swalConfirm("Creare la richiesta di segnacollo?");
        if (!confirm) return;

        Swal.fire({
            title: "Caricamento modulo etichetta...",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: function () {
                Swal.showLoading();
            }
        });

        try {
            const json = await self.fetch(self.formControllerURL, {
                ajax: true,
                action: "showBrtLabelForm",
                orderID: self.orderID
            });
            const html = json.html || "<div class='alert alert-danger text-center'>Errore nel caricamento del form</div>";
            self.parcels = json.parcels || [];
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

                    self.addTableRows();
                    self.disableOrderData();
                    self.bindEvents();
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

    // --- showPrintLabelButton.js ---
    static async showPrintLabelButton(orderId) {
        const orderActions = document.querySelector(".order-actions");
        const orderActionsPrint = orderActions.querySelector(".order-actions-print").querySelector(".input-group");
        const json = await this.fetch(this.formControllerURL, {
            ajax: 1,
            action: "showPrintLabelButton",
            numericSenderReference: orderId
        });

        const success = json.success || false;
        const canShow = json.labelShown || false;
        if (success && canShow) {
            const numericSenderReference = json.numericSenderReference || "";
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
                MpBrtApiShipment.printLabel(numericSenderReference);
            });
            orderActionsPrint.appendChild(button);
        }
    }

    static async printLabel(numericSenderReference) {
        const json = await this.fetch(this.formControllerURL, {
            ajax: 1,
            action: "printLabel",
            numericSenderReference: numericSenderReference
        });
        const success = json.success || false;
        if (success) {
            const pdf = json.stream || null;
            if (pdf) {
                const byteCharacters = atob(pdf);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], { type: "application/pdf" });
                const blobUrl = URL.createObjectURL(blob);
                window.open(blobUrl, "_blank");
            }
        }
    }

    // --- showBrtLabelForm.js ---
    static toggleIsAlertRequired() {
        const isAlertRequiredInput = document.getElementById("isAlertRequired");
        const notifyByEmailValue = document.querySelector('input[name="notifyByEmail"]:checked').value;
        const notifyBySmsValue = document.querySelector('input[name="notifyBySms"]:checked').value;
        if (notifyByEmailValue == 1 || notifyBySmsValue == 1) {
            isAlertRequiredInput.value = 1;
        } else {
            isAlertRequiredInput.value = 0;
        }
    }

    static getTableSum() {
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

    static addTableRows() {
        const parcels = this.parcels;
        const self = this;

        if (parcels.length > 0) {
            parcels.forEach((p) => {
                const cloneNode = document.getElementById("table-row").content.cloneNode(true);
                const row = cloneNode.querySelector("tr");

                row.querySelector('input[name="length"]').value = Number(p.x || 0).toFixed(0);
                row.querySelector('input[name="width"]').value = Number(p.y || 0).toFixed(0);
                row.querySelector('input[name="height"]').value = Number(p.z || 0).toFixed(0);
                row.querySelector('input[name="weight"]').value = Number(p.weight || 0).toFixed(3);
                row.querySelector('input[name="volume"]').value = Number(p.volume || 0).toFixed(3);
                document.getElementById("table-brt-measure").querySelector("tbody").appendChild(row);
            });

            self.bindEvents();
            self.getTableSum();
            self.setFocusOnRow();
        } else {
            self.addTableRow();
        }
    }

    /**
     * Converte una stringa HTML in un elemento DOM.
     * @param {string} html - La stringa HTML da convertire.
     * @param {boolean} [fragment=false] - Se `true`, restituisce un DocumentFragment invece del primo nodo.
     * @returns {Node|DocumentFragment} - L'elemento DOM o un fragment contenente i nodi.
     */
    static htmlToElement(html, fragment = false) {
        // 1. Elimina spazi bianchi non necessari (opzionale ma utile)
        const trimmedHtml = html.trim();

        // 2. Usa DOMParser per convertire la stringa in un Document HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(trimmedHtml, "text/html");

        // Controllo errore
        if (doc.body.firstChild.nodeName === "PARSERERROR") {
            throw new Error("HTML non valido");
        }

        // 3. Se richiesto un fragment, restituisci TUTTI i nodi
        if (fragment) {
            const fragment = document.createDocumentFragment();
            Array.from(doc.body.childNodes).forEach((node) => {
                fragment.appendChild(node.cloneNode(true));
            });
            return fragment;
        }

        // 4. Altrimenti restituisci solo il PRIMO elemento
        return doc.body.firstChild;
    }

    /**
     *
     * @param {string} html
     * @returns DOM Node
     */
    static cloneNode(html) {
        const clonedElement = this.htmlToElement(html).cloneNode(true);
        return clonedElement;
    }

    static addTableRow() {
        const tableRow = `
        <tr>
            <th><input type="text" class="form-control text-right td-length" name="length" id="length" value="0" min="0" required></th>
            <th><input type="text" class="form-control text-right td-width" name="width" id="width" value="0" min="0" required></th>
            <th><input type="text" class="form-control text-right td-height" name="height" id="height" value="0" min="0" required></th>
            <th><input type="text" class="form-control text-right td-volume" name="volume" id="volume" value="0" min="0" required readonly></th>
            <th><input type="text" class="form-control text-right td-weight" name="weight" id="weight" value="0" min="0" required></th>
            <th>
                <div class="btn-group text-center" role="group" aria-label="Button group">
                    <button type="button" class="btn btn-info" name="addParcels" title="Aggiungi collo"><i class="material-icons">add</i></button>
                    <button type="button" class="btn btn-danger" name="deleteParcels" title="Rimuovi collo"><i class="material-icons">delete</i></button>
                </div>
            </th>
        </tr>
    `;

        // Create a temporary container
        const temp = document.createElement("tbody");
        temp.innerHTML = tableRow.trim();
        const row = temp.firstElementChild;

        document.getElementById("table-brt-measure").querySelector("tbody").appendChild(row);

        this.getTableSum();
        this.setFocusOnRow();
        this.bindRowMeasureEvents(row);
        this.bindRowButtonsEvents(row);
    }

    static setFocusOnRow() {
        const rows = document.getElementById("table-brt-measure").querySelector("tbody").querySelectorAll("tr");
        const lastRow = rows[rows.length - 1];
        lastRow.querySelector('input[name="length"]').focus();
    }

    static calcMeasure(tr) {
        const length = parseFloat(tr.querySelector('input[name="length"]').value);
        const width = parseFloat(tr.querySelector('input[name="width"]').value);
        const height = parseFloat(tr.querySelector('input[name="height"]').value);
        const volume = Number((length * width * height) / 1000000000).toFixed(3);
        const weight = Number(parseFloat(tr.querySelector('input[name="weight"]').value)).toFixed(3);
        tr.querySelector('input[name="volume"]').value = volume;
        tr.querySelector('input[name="weight"]').value = weight;
    }

    static disableOrderData() {
        const orderDataContainer = document.querySelectorAll(".order-data-container");

        orderDataContainer.forEach((container) => {
            if (container.classList.contains("no-edit")) {
                container.querySelectorAll("input, select, textarea, button").forEach((element) => {
                    element.setAttribute("disabled", true);
                });
            }
        });

        const advancedFieldsCard = document.getElementById("toggleAdvancedFields");
        if (advancedFieldsCard) {
            advancedFieldsCard.style.display = "none";
        }
    }

    static async deleteBrtOrderLabel() {
        const self = this;

        const confirm = await swalConfirm("Sei sicuro di voler eliminare l'etichetta?");
        if (!confirm) {
            return;
        }

        try {
            const json = await self.fetch(self.formControllerURL, {
                ajax: true,
                action: "deleteBrtOrderLabel",
                orderID: self.orderID
            });
            if (json.success) {
                Swal.fire({ icon: "success", title: "Etichetta eliminata!", html: json.message ? json.message : "Etichetta eliminata con successo." });
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                Swal.fire({ icon: "error", title: "Errore", html: json.message ? json.message : "Errore nell'eliminazione dell'etichetta." });
            }
        } catch (e) {
            Swal.fire({ icon: "error", title: "Errore", html: e.message ? e.message : "Errore nella richiesta." });
        }
    }

    static async showNewBrtLabel() {
        const self = this;

        const confirm = await swalConfirm("Creare la richiesta di segnacollo?");
        if (!confirm) return;

        Swal.fire({
            title: "Caricamento modulo etichetta...",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: function () {
                Swal.showLoading();
            }
        });

        try {
            const json = await self.fetch(self.formControllerURL, {
                ajax: true,
                action: "showBrtLabelForm",
                orderID: 0
            });
            const html = json.html || "<div class='alert alert-danger text-center'>Errore nel caricamento del form</div>";
            self.parcels = json.parcels || [];
            // Mostra il form nella modale
            Swal.fire({
                html: html,
                width: "90%",
                customClass: {
                    popup: "brt-label-modal"
                },
                showConfirmButton: false,
                showCancelButton: false,
                willOpen: () => {
                    const swalEl = document.querySelector(".swal2-container");
                    if (swalEl) swalEl.style.zIndex = 99999;
                },
                didOpen: () => {
                    // Applica le classi CSS per fullscreen se non già presenti
                    const popup = document.querySelector(".swal2-popup.brt-label-modal");
                    if (popup && !popup.classList.contains("swal2-fullscreen")) {
                        popup.classList.add("swal2-fullscreen");
                    }

                    self.addTableRows();
                    self.bindEvents();
                    TableColli.measures = [];
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

    static async createLabelRequest() {
        const self = this;
        const form = document.getElementById("brt-label-form");
        if (!self.validateForm(form)) return;

        const formElements = form.querySelectorAll("input, select, textarea");
        const isCodMandatory = document.querySelector('input[name="isCODMandatory"]:checked').value;
        const notifyByEmail = document.querySelector('input[name="notifyByEmail"]:checked').value;
        const notifyBySms = document.querySelector('input[name="notifyBySms"]:checked').value;
        const request = {};
        formElements.forEach((element) => {
            if (element.name) {
                request[element.name] = element.value;
            }
        });
        request.isCODMandatory = isCodMandatory;
        request.notifyByEmail = notifyByEmail;
        request.notifyBySms = notifyBySms;
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
            const json = await self.fetch(self.formControllerURL, {
                ajax: true,
                action: "createLabelRequest",
                data: request
            });

            if (json.success) {
                self.showPrintLabelButton(request.numericSenderReference);
                Swal.fire({ icon: "success", title: "Segnacollo creato!", html: json.message || "Richiesta inviata con successo." });
                self.printLabel(request.numericSenderReference);
            } else {
                Swal.fire({ icon: "error", title: "Errore", html: json.message || "Errore nella creazione del segnacollo." });
            }
        } catch (e) {
            Swal.fire({ icon: "error", title: "Errore", html: e.message || "Errore nella richiesta." });
        }
    }

    // --- labelFormScript.js ---
    static async brtLabelFormLoaded() {
        const self = this;
        const form = document.getElementById("brt-label-form");
        if (!form) return;

        setTimeout(() => {
            const firstInput = form.querySelector("input,select,textarea");
            if (firstInput) firstInput.focus();
        }, 200);

        form.addEventListener("submit", async function (e) {
            e.preventDefault();
            if (MpBrtApiShipment._submitting) return;

            if (!MpBrtApiShipment.validateForm(form)) {
                MpBrtApiShipment.scrollToFirstError(form);
                return;
            }

            MpBrtApiShipment._submitting = true;
            if (window.Swal) {
                Swal.showLoading();
            }
            const formData = new FormData(form);
            const data = {};
            formData.forEach((v, k) => {
                data[k] = v;
            });

            const json = await self.fetch(self.formControllerURL, data);

            MpBrtApiShipment._submitting = false;

            if (window.Swal) Swal.hideLoading();

            if (json.success) {
                if (window.Swal) {
                    Swal.fire({ icon: "success", title: "Etichetta creata!", text: "Operazione completata con successo." });
                }
            } else {
                if (window.Swal) {
                    Swal.fire({ icon: "error", title: "Errore", text: json.message || "Si è verificato un errore." });
                }
            }
        });
    }

    static scrollToFirstError(form) {
        const error = form.querySelector(".brt-error");
        if (error) {
            error.scrollIntoView({ behavior: "smooth", block: "center" });
            error.focus();
        }
    }

    static showFieldError(field, msg) {
        let err = field.parentNode.querySelector(".brt-error-msg");
        if (!err) {
            err = document.createElement("div");
            err.className = "brt-error-msg";
            err.style.color = "#dc3545";
            err.style.fontSize = "0.93em";
            err.style.marginTop = "2px";
            field.parentNode.appendChild(err);
        }
        err.textContent = msg;
        field.classList.add("brt-error");
        field.setAttribute("aria-invalid", "true");
    }

    static clearFieldError(field) {
        let err = field.parentNode.querySelector(".brt-error-msg");
        if (err) err.remove();
        field.classList.remove("brt-error");
        field.removeAttribute("aria-invalid");
    }

    static validateForm(form) {
        const self = this;
        let valid = true;
        [...form.elements].forEach((el) => {
            self.clearFieldError(el);
            if (el.hasAttribute("required") && !el.value.trim()) {
                self.showFieldError(el, "Campo obbligatorio");
                valid = false;
            }
            if (el.type === "email" && el.value) {
                const re = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
                if (!re.test(el.value)) {
                    self.showFieldError(el, "Email non valida");
                    valid = false;
                }
            }
            if (el.name === "consigneeZIPCode" && el.value) {
                if (!/^\d{5,10}$/.test(el.value)) {
                    self.showFieldError(el, "CAP non valido");
                    valid = false;
                }
            }
            if ((el.name === "consigneeProvinceAbbreviation" || el.name === "consigneeCountryAbbreviationISOAlpha2") && el.value) {
                if (!/^[A-Z]{2}$/.test(el.value)) {
                    self.showFieldError(el, "Inserire 2 lettere maiuscole");
                    valid = false;
                }
            }
        });
        return valid;
    }

    // --- AdminScripts.js ---
    static async showDeleteBrtLabel() {
        const self = this;
        const html = `<div class="row" style="width:90%; margin: 0 auto;"><div class="col-md-10"><div class="form-group" style="margin-bottom: 1rem;"><label for="swal-input-sender-numeric" style="display: block; text-align: left; margin-bottom: .25rem;">Riferimento numerico:</label><input id="swal-input-sender-numeric" class="swal2-input" placeholder="Es. 12345" style="width: 90%;"></div><div class="form-group"><label for="swal-input-sender-alpha" style="display: block; text-align: left; margin-bottom: .25rem;">Riferimento alfanumerico:</label><input id="swal-input-sender-alpha" class="swal2-input" placeholder="Es. ABCD001" style="width: 90%;"></div></div></div>`;
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
            preConfirm: async () => {
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
        if (result.isConfirmed) {
            const dataToSend = result.value;
            const json = await self.fetch(self.controllerURL, dataToSend);
            if (json.success) {
                Swal.fire({ icon: "success", title: "Etichetta eliminata!", html: json.message || "Eliminazione completata." });
            } else {
                Swal.fire({ icon: "error", title: "Errore", html: json.message || "Errore durante l'eliminazione." });
            }
        }
    }

    static showPdfPage(pdfData) {
        if (!pdfData || !pdfData.stream) {
            Swal.fire({ icon: "error", title: "Errore", text: "PDF non disponibile." });
            return;
        }
        const byteCharacters = atob(pdfData.stream);
        const byteNumbers = new Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);
        const blob = new Blob([byteArray], { type: "application/pdf" });
        const blobUrl = URL.createObjectURL(blob);
        window.open(blobUrl, "_blank");
    }
}

// Per evitare doppio invio form
MpBrtApiShipment._submitting = false;

// Esportazione globale se serve
window.MpBrtApiShipment = MpBrtApiShipment;
