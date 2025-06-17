class LabelManager {
    dialog = null;
    form = null;
    packageTable = null;
    parcels = null;
    urls = null;

    constructor(urls) {
        this.urls = urls;
        console.log("URLS", urls);
    }

    async init() {
        await this.newBrtLabel();
        const self = this;
        if (!this.dialog) {
            alert("Errore durante la creazione del form etichetta.");
            return;
        }
        this.form = this.dialog.querySelector("#form-brt-label");
        this.packageTable = this.dialog.querySelector("#table-label-packages");
        this.setDefaultLabelValues();
        this.bindLabelForm();
        this.dialog.addEventListener("close", () => {
            self.closeModal();
        });
    }

    showModal() {
        this.dialog.showModal();
    }

    async newBrtLabel() {
        const self = this;
        const response = await fetch(this.urls.borderoNewLabelUrl, {
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
                self.dialog = dialogElement; //document.getElementById("brt-label-dialog");
            } else {
                alert("Errore durante la creazione del form etichetta.");
            }
        } else {
            alert("Errore durante la creazione del form etichetta.");
        }
    }

    async fillOrderDetails(orderId) {
        if (!orderId) {
            alert("Inserisci un id ordine valido.");
            return;
        }
        const request = await fetch(this.urls.borderoFillOrderDetailsUrl, {
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
                    const el = document.getElementById(key);
                    if (el) {
                        el.value = String(value).toUpperCase();
                    }
                });

                const countryIsoValue = document.getElementById("consignee_country_abbreviation_iso_alpha_2");
                if (countryIsoValue && countryIsoValue.value.toUpperCase() != "IT") {
                    const networkValue = document.getElementById("network");
                    if (networkValue) {
                        networkValue.value = labelPreferences.network;
                    }
                }
                if (orderDetails.is_cod_mandatory == 1 || orderDetails.cash_on_delivery > 0) {
                    this.toggleCashOnDelivery(1);
                }
                this.readParcels(orderId);
                this.packagingCalculation();
            } else {
                alert("Ordine non trovato");
            }
        }
    }

    toggleCashOnDelivery(value) {
        const isCodMandatoryOn = this.form.querySelector("#is_cod_mandatory-on");
        const isCodMandatoryOff = this.form.querySelector("#is_cod_mandatory-off");
        const cashOnDelivery = this.form.querySelector("#cash_on_delivery");
        const codPaymentType = this.form.querySelector("#cod_payment_type");

        if (value == 1) {
            isCodMandatoryOn.checked = true;
            isCodMandatoryOff.checked = false;
            cashOnDelivery.removeAttribute("disabled");
            codPaymentType.removeAttribute("disabled");
            cashOnDelivery.focus();
        } else {
            isCodMandatoryOn.checked = false;
            isCodMandatoryOff.checked = true;
            cashOnDelivery.setAttribute("disabled", true);
            codPaymentType.setAttribute("disabled", true);
        }
    }

    async readParcels(numericSenderReference) {
        const request = await fetch(this.urls.borderoReadParcelsUrl, {
            method: "POST",
            body: JSON.stringify({
                numericSenderReference: numericSenderReference
            })
        });
        const response = await request.json();
        const success = response.success || false;
        const parcels = response.parcels || [];

        if (success) {
            this.parcels = this.parcelsCalculation(parcels);
            this.fillPackageTable();
            this.packagingCalculation();
        }
    }

    parcelsCalculation(parcels) {
        parcels.forEach(parcel => {
            parcel.volume = Number((parcel.x * parcel.y * parcel.z) / 1000000000).toFixed(3);
        });

        return parcels;
    }

    emptyRow() {
        const emptyRow = `
            <tr class="package-row" data-row-id="0">
                <td scope="row" style="text-align: right; padding-right: 4px;">
                    <span class="badge badge-success">1</span>
                </td>
                <td style="text-align: right; padding-right: 4px;">
                    <input type="text" name="package-weight" class="form-control text-right fixed-width-mini package-weight" min="0" value="0.0" data-default-value="0.0">
                </td>
                <td style="text-align: right; padding-right: 4px;">
                    <input type="text" name="package-x" class="form-control text-right fixed-width-mini package-measure package-x" min="0" value="0" data-default-value="0">
                </td>
                <td style="text-align: right; padding-right: 4px;">
                    <input type="text" name="package-y" class="form-control text-right fixed-width-mini package-measure package-y" min="0" value="0" data-default-value="0">
                </td>
                <td style="text-align: right; padding-right: 4px;">
                    <input type="text" name="package-z" class="form-control text-right fixed-width-mini package-measure package-z" min="0" value="0" data-default-value="0">
                </td>
                <td style="text-align: right; padding-right: 4px;">
                    <input type="text" name="package-volume" class="form-control text-right fixed-width-mini package-volume" min="0" value="0.000" data-default-value="0.000" tabindex="-1" readonly="">
                </td>
                <td style="text-align: center; width: auto;">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default btn-sm package-button" data-action="add">
                            <span class="material-icons" style="color: var(--info) !important;" title="Nuova riga">add</span>
                        </button>
                        <button type="button" class="btn btn-default btn-sm package-button" data-action="delete">
                            <span class="material-icons" style="color: var(--danger) !important;" title="Elimina riga">delete</span>
                        </button>
                    </div>
                </td>
            </tr>
        `;

        const template = document.createElement("template");
        template.innerHTML = emptyRow.trim();
        const clone = template.content.cloneNode(true);
        const element = clone.firstElementChild;
        console.log("EMPTY ROW:", element);

        return element;
    }

    fillPackageTable() {
        const parcels = this.parcels;
        console.log("PARCELS: ", parcels);

        this.packageTable.querySelector("tbody").innerHTML = ``;
        if (parcels.length > 0) {
            parcels.forEach(parcel => {
                const row = this.emptyRow();
                let size = this.packageTable.querySelector("tbody").children.length;
                row.dataset.rowId = parcel.numericSenderReference;
                row.querySelector(".badge").textContent = ++size;
                row.querySelector("input[name='package-weight']").value = parcel.weight;
                row.querySelector("input[name='package-x']").value = parcel.x;
                row.querySelector("input[name='package-y']").value = parcel.y;
                row.querySelector("input[name='package-z']").value = parcel.z;
                row.querySelector("input[name='package-volume']").value = parcel.volume;
                this.packageTable.querySelector("tbody").appendChild(row);
            });
        } else {
            const emptyRow = this.emptyRow();
            this.packageTable.querySelector("tbody").appendChild(emptyRow);
            alert("Non ci sono misure da leggere.");
        }
        this.packagingCalculation();
        this.bindLabelPackageButtons();
        this.bindLabelPackageInput();
    }

    _handlerInput(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        e.target.value = e.target.value.toUpperCase();
    }

    _handlerFocus(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        e.target.select();
    }

    _handlerBlur(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        e.target.value = e.target.value.toUpperCase();
    }

    _handlerInputCurrency(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        e.target.value = e.target.value.replace(/[^0-9.]/g, "");
    }

    _handlerBlurCurrency(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        let val = parseFloat(e.target.value);

        if (val < 0) {
            e.target.value = "0.00";
        } else {
            e.target.value = val.toFixed(2);
        }

        let maxVal = e.target.dataset.max || 0;
        if (isNaN(maxVal) || maxVal < 0) {
            maxVal = 0;
        }
        if (maxVal > 0 && val > maxVal) {
            e.target.value = maxVal.toFixed(2);
        }
    }

    bindLabelForm() {
        const labelForm = this.form;
        if (labelForm) {
            labelForm.querySelectorAll("input:not(.package-measure)").forEach(input => {
                input.addEventListener("input", this._handlerInput);
                input.addEventListener("focus", this._handlerFocus);
                input.addEventListener("blur", this._handlerBlur);
            });

            const cashOnDelivery = this.form.querySelector("#cash_on_delivery");
            if (cashOnDelivery) {
                //Rimuovi gli eventi globali
                cashOnDelivery.removeEventListener("input", this._handlerInput);
                cashOnDelivery.removeEventListener("blur", this._handlerBlur);

                //Aggiungi il nuovo evento
                cashOnDelivery.addEventListener("input", this._handlerInputCurrency);
                cashOnDelivery.addEventListener("blur", this._handlerBlurCurrency);
            }

            const switchCashOnDelivery = this.form.querySelectorAll("input[name='is_cod_mandatory']");
            if (switchCashOnDelivery) {
                switchCashOnDelivery.forEach(switchCashOnDelivery => {
                    switchCashOnDelivery.addEventListener("change", e => {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();

                        this.toggleCashOnDelivery(e.target.value);
                    });
                });
            }
        }

        const countryIso = this.form.querySelector("#consignee_country_abbreviation_iso_alpha_2");

        if (countryIso) {
            countryIso.addEventListener("blur", e => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                const countryIsoValue = countryIso.value.toUpperCase();
                const network = this.form.querySelector("#network");
                if (network && countryIsoValue != "IT") {
                    network.value = labelPreferences.network;
                } else {
                    network.value = "DEF";
                }
            });
        }

        this.bindLabelPackageButtons();
        this.bindLabelPackageInput();
    }

    bindLabelPackageButtons() {
        const self = this;
        const labelForm = self.form;
        if (!labelForm) {
            return false;
        }

        const buttons = labelForm.querySelectorAll(".package-button");
        buttons.forEach(button => {
            button.addEventListener("click", e => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                const button = e.target.closest("button");
                const row = button.closest("tr");
                const action = button.getAttribute("data-action");

                if (action) {
                    if (action === "add") {
                        self.addPackageRow();
                    } else if (action === "delete") {
                        self.deletePackageRow(row);
                    }
                }
            });
        });
    }

    bindLabelPackageInput() {
        const self = this;
        const labelForm = self.form;
        const tablePackages = self.packageTable;
        if (!labelForm || !tablePackages) {
            console.error("Form o tabella non trovati");
            return false;
        }

        const package_measure = tablePackages.querySelectorAll(".package-measure");
        const package_weight = tablePackages.querySelectorAll("input[name='package-weight']");
        const package_volume = tablePackages.querySelectorAll("input[name='package-volume']");

        package_measure.forEach(input => {
            input.addEventListener("blur", e => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                const row = e.target.closest("tr");
                self.rowCalculate(row);
                self.packagingCalculation();
            });

            input.addEventListener("focus", e => {
                e.target.select();
            });
        });

        package_weight.forEach(input => {
            input.addEventListener("input", () => {
                console.log("INPUT", input.value);

                self.packagingCalculation();
            });

            input.addEventListener("focus", e => {
                e.target.select();
            });
        });

        package_volume.forEach(input => {
            input.addEventListener("change", () => {
                console.log("CHANGE", input.value);

                self.packagingCalculation();
            });

            input.addEventListener("focus", e => {
                e.target.select();
            });
        });
    }

    packagingCalculation() {
        const table = this.packageTable;
        const tbody = table.querySelector("tbody");
        const rows = tbody.querySelectorAll("tr");
        const totalPackages = rows.length;
        let totalWeight = 0;
        let totalVolume = 0;
        rows.forEach(row => {
            const weight = row.querySelector("input[name='package-weight']").value;
            const volume = row.querySelector("input[name='package-volume']").value;
            totalWeight += parseFloat(weight);
            totalVolume += parseFloat(volume);
        });
        document.getElementById("weight_kg").value = totalWeight < 1 ? "1" : totalWeight.toFixed(1);
        document.getElementById("volume_m3").value = totalVolume.toFixed(3);
        document.getElementById("number_of_parcels").value = totalPackages;
    }

    rowCalculate(row) {
        const x = parseInt(row.querySelector("input[name='package-x']").value);
        const y = parseInt(row.querySelector("input[name='package-y']").value);
        const z = parseInt(row.querySelector("input[name='package-z']").value);

        let volume = 0;
        const volume_mm = x * y * z;
        if (volume_mm) {
            volume = Number(volume_mm / 1000000000).toFixed(3);
        } else {
            volume = "0.000";
        }

        const volumeElement = row.querySelector("input[name='package-volume']");
        if (volumeElement) {
            volumeElement.value = volume;
        }
    }

    addPackageRow() {
        const row = document.querySelector(`#table-label-packages tbody tr:first-child`);
        const tbody = row.closest("tbody");

        const newRow = row.cloneNode(true);
        const inputs = newRow.querySelectorAll("input");
        inputs.forEach(input => {
            input.value = input.getAttribute("data-default-value");
        });
        tbody.appendChild(newRow);

        //Inserisco il nuovo numero di riga
        const rowsNumber = tbody.querySelectorAll("tr").length;
        const badge = newRow.querySelector(".badge");
        badge.textContent = rowsNumber;

        this.bindLabelPackageButtons();
        this.bindLabelPackageInput();
        this.packagingCalculation();

        //do il focus al primo input della nuova riga
        tbody
            .querySelector("tr:last-child")
            .querySelector("input:first-child")
            .focus();
    }

    deletePackageRow(row) {
        const tbody = row.closest("tbody");
        if (tbody.querySelectorAll("tr").length == 1) {
            const row = tbody.querySelector("tr");
            row.querySelectorAll("input").forEach(input => {
                input.value = "0";
            });
        } else {
            row.remove();
        }

        //Rinumero tutti le righe
        let i = 1;
        const rows = tbody.querySelectorAll("tr");
        rows.forEach(row => {
            const badge = row.querySelector(".badge");
            badge.textContent = i;
            i++;
        });

        this.bindLabelPackageButtons();
        this.bindLabelPackageInput();
        this.packagingCalculation();
    }

    setDefaultLabelValues() {
        const service_type = this.form.querySelector("#service_type");
        const network = this.form.querySelector("#network");
        const delivery_freight_type_code = this.form.querySelector("#delivery_freight_type_code");
        const cod_payment_type = this.form.querySelector("#cod_payment_type");
        const change_order_state = this.form.querySelector("#change-order-state");

        service_type.value = labelPreferences.service_type;
        network.value = "DEF";
        delivery_freight_type_code.value = labelPreferences.delivery_freight_type_code;
        cod_payment_type.value = labelPreferences.cod_payment_type;
        change_order_state.value = labelPreferences.change_order_state;
    }

    async createLabel() {
        const form = document.getElementById("form-brt-label");
        if (!form) {
            alert("Form etichetta non disponibile");
            return false;
        }

        //Controllo che tutti i dati required siano stati compilati
        const inputs = form.querySelectorAll("input");
        for (let i = 0; i < inputs.length; i++) {
            if (inputs[i].required && inputs[i].value == "") {
                alert("Compilare tutti i campi obbligatori.");
                inputs[i].focus();
                return false;
            }
        }

        //Creo la richiesta per salvare i dati
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        Object.keys(data).forEach(key => {
            if (key.startsWith("package-")) {
                delete data[key];
            }
        });

        const packages = {};
        const table = this.packageTable;
        const tbody = table.querySelector("tbody");
        const rows = tbody.querySelectorAll("tr");
        rows.forEach(row => {
            const packageData = {};
            const inputs = row.querySelectorAll("input");
            inputs.forEach(input => {
                packageData[input.name] = input.value;
            });
            packages[row.querySelector(".badge").textContent] = packageData;
        });

        /***************************************************
         * SALVATAGGIO DATI SEGNACOLLO *********************
         ***************************************************/
        const callbackSaveBrtRequest = await this.callbackSaveBrtRequest(data, packages);

        if (!callbackSaveBrtRequest) {
            return false;
        }

        /***************************************************
         * CREAZIONE ETICHETTA *****************************
         ***************************************************/
        const numericSenderReference = callbackSaveBrtRequest;
        const callbackBrtResponse = await this.callbackGetBrtResponse(numericSenderReference);

        if (!callbackBrtResponse) {
            return false;
        }

        callbackBrtResponse.response.numericSenderReference = numericSenderReference;

        /***********************************************************
         * SALVATAGGIO RESPONSE BARTOLINI***************************
         ***********************************************************/
        const callbackBrtSaveResponse = await this.callbackBrtSaveResponse(callbackBrtResponse.executionMessage, callbackBrtResponse.response, callbackBrtResponse.labels);

        if (callbackBrtSaveResponse) {
            alert("Etichetta creata.");
            //fai il refresh della tabella
            showLastBordero();
        } else {
            return false;
        }
    }

    async callbackSaveBrtRequest(data, packages) {
        const response = await fetch(this.urls.borderoSaveBrtRequestUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ label: data, packages: packages })
        });
        const result = await response.json();
        const success = result.success || false;
        const numericSenderReference = result.id || false;

        if (!numericSenderReference) {
            alert(result.message || "Errore durante la creazione dell'etichetta.");
            return false;
        }

        if (!success) {
            alert(result.message || "Errore durante la creazione dell'etichetta.");
            return false;
        }

        return numericSenderReference;
    }

    async callbackGetBrtResponse(numericSenderReference) {
        const callback = await fetch(this.urls.borderoCreateBrtRequestUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                numericSenderReference: numericSenderReference
            })
        });

        if (!callback.ok) {
            alert("Errore nella chiamata callback createResponse");
            return false;
        }

        const result = await callback.json();
        const success = result.success || false;
        const executionMessage = result.executionMessage || false;
        const response = result.response || false;
        const labels = result.labels || false;
        const error = result.error || "";

        if (!success) {
            alert("Errore nella callback createResponse");
            return false;
        }

        if (executionMessage) {
            const code = executionMessage.code;
            const severity = executionMessage.severity;
            const title = executionMessage.codeDesc;
            const message = executionMessage.message;

            console.log(code, severity, title, message);

            if (code < 0) {
                alert(`${severity}: ${title}\n${message}`);
                return false;
            }
        }

        return {
            success: true,
            executionMessage: executionMessage,
            response: response,
            labels: labels,
            error: error
        };
    }

    async callbackBrtSaveResponse(executionMessage, response, labels) {
        const saveResponseRequest = await fetch(this.urls.borderoSaveBrtResponseUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                executionMessage: executionMessage,
                response: response,
                labels: labels
            })
        });
        const saveResponseResult = await saveResponseRequest.json();
        const saveResponseSuccess = saveResponseResult.success || false;
        if (!saveResponseSuccess) {
            alert(saveResponseResult.message || "Errore durante il salvataggio della response di Bartolini.");
            return false;
        }

        return true;
    }

    // Salva tutti i dati del form e della tabella pacchi in localStorage
    closeModal() {
        const form = this.form;
        const table = this.packageTable;
        if (!form || !table) {
            this.dialog.close();
            return;
        }
        // 1. Salva tutti i campi del form (esclusi i pacchi)
        const formData = {};
        form.querySelectorAll("input, select, textarea").forEach(input => {
            // Salva anche radio e checkbox
            if (input.type === "radio" || input.type === "checkbox") {
                formData[input.name] = input.checked ? input.value : formData[input.name] || "";
            } else {
                formData[input.name] = input.value;
            }
        });
        // 2. Salva tutte le righe della tabella pacchi
        const packages = [];
        table.querySelectorAll("tbody tr").forEach(row => {
            const pkg = {};
            row.querySelectorAll("input").forEach(input => {
                pkg[input.name] = input.value;
            });
            packages.push(pkg);
        });
        // 3. Salva tutto in localStorage
        const storageData = { form: formData, packages: packages };
        localStorage.setItem("brt_label_form_draft", JSON.stringify(storageData));
        this.dialog.close();
    }

    // Carica i dati da localStorage e compila form e tabella pacchi
    loadFromStorage() {
        const self = this;
        const dialog = document.getElementById("brt-label-dialog");
        if (!dialog) {
            console.error("Dialog etichetta non trovato");
            return;
        }
        const form = dialog.querySelector("#form-brt-label");
        const table = dialog.querySelector("#table-label-packages");
        const storage = localStorage.getItem("brt_label_form_draft");
        if (!form || !table || !storage) {
            console.error("Form etichetta o tabella pacchi non trovati");
            return;
        }
        try {
            const data = JSON.parse(storage);
            // 1. Ripristina i campi del form
            Object.entries(data.form || {}).forEach(([name, value]) => {
                const input = form.querySelector(`[name='${name}']`);
                if (input) {
                    if (input.type === "radio" || input.type === "checkbox") {
                        input.checked = input.value == value;
                    } else {
                        input.value = value;
                    }
                }
            });
            // 2. Ripristina le righe della tabella pacchi
            const tbody = table.querySelector("tbody");
            // Rimuovi tutte le righe tranne la prima
            while (tbody.rows.length > 1) {
                tbody.deleteRow(1);
            }
            // Prima riga
            if (data.packages && data.packages.length > 0) {
                Object.entries(data.packages[0]).forEach(([name, value]) => {
                    const input = tbody.rows[0].querySelector(`[name='${name}']`);
                    if (input) input.value = value;
                });
                // Eventuali altre righe
                for (let i = 1; i < data.packages.length; i++) {
                    // Usa il metodo addPackageRow per coerenza UI
                    self.addPackageRow();
                    const newRow = tbody.rows[i];
                    Object.entries(data.packages[i]).forEach(([name, value]) => {
                        const input = newRow.querySelector(`[name='${name}']`);
                        if (input) input.value = value;
                    });
                }
            }
        } catch (e) {
            console.error("Errore durante il caricamento dei dati da localStorage", e);
        }
    }
}
