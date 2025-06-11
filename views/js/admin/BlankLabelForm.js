class BlankLabelForm {
    constructor() {
        this.modalId = "BlankLabelFormModal";
        this.formId = "fromBrtLabel";
        this._escListener = null;
        this._focusableEls = null;
        this._lastFocused = null;
        this.injectStyles();
    }

    injectStyles() {
        if (!document.getElementById("blank-label-form-css")) {
            const style = document.createElement("style");
            style.id = "blank-label-form-css";
            style.innerHTML = `
                #${this.modalId} {
                    background: #fff;
                    border-radius: 12px;
                    border-color: #e4e6f1;
                    box-shadow: 0 4px 32px rgba(60, 60, 60, 0.6);
                    padding: 2.5rem 2rem 2rem 2rem;
                    max-width: 65vw;
                    width: 65vw;
                    margin: 5vh auto;
                    position: relative;
                    animation: blankLabelFadeIn 0.18s cubic-bezier(.4,0,.2,1);
                    font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
                    color: #303030;
                }
                #${this.modalId} .modal-header {
                    border-bottom: 1px solid #e4e6f1;
                    padding-bottom: 1rem;
                    margin-bottom: 1.3rem;
                }
                #${this.modalId} .modal-footer {
                    display: flex;
                    justify-content: center;
                    border-top: 1px solid #e4e6f1;
                    padding-top: 1rem;
                    margin-top: 1.3rem;
                }
                #${this.modalId} .modal-title {
                    font-size: 1.35rem;
                    font-weight: 600;
                    color: #21243d;
                }
                #${this.modalId} .modal-close {
                    position: absolute;
                    top: 1.25rem;
                    right: 1.25rem;
                    background: none;
                    border: none;
                    font-size: 1.4rem;
                    cursor: pointer;
                    color: #72778e;
                    transition: color 0.18s;
                }
                #${this.modalId} .modal-close:hover {
                    color: #e64848;
                }
                #${this.modalId} .form-group {
                    margin-bottom: 1.3rem;
                }
                #${this.modalId} input, #${this.modalId} select, #${this.modalId} textarea {
                    border-radius: 8px;
                    border: 1px solid #d1d7e6;
                    padding: 0.55rem 0.9rem;
                    font-size: 1rem;
                    background: #f9fafb;
                    transition: border 0.18s, background 0.18s;
                }
                #${this.modalId} input:focus, #${this.modalId} select:focus, #${this.modalId} textarea:focus {
                    outline: none;
                    border-color: #3955e4;
                    background: #fff;
                }
                #${this.modalId} .btn {
                    border-radius: 7px;
                    padding: 0.5rem 1.15rem;
                    font-size: 1rem;
                    font-weight: 500;
                    transition: background 0.16s, box-shadow 0.16s;
                    box-shadow: 0 1px 4px rgba(57,85,228,0.04);
                }
                #${this.modalId} .btn-info {
                    background: #3955e4;
                    color: #fff;
                    border: none;
                }
                #${this.modalId} .btn-info:hover {
                    background: #2038ad;
                }
                @keyframes blankLabelFadeIn {
                    from { opacity: 0; transform: translateY(30px);}
                    to { opacity: 1; transform: translateY(0);}
                }
                #${this.modalId} .form-check-input[type="checkbox"] {
                    width: 2.4em;
                    height: 1.2em;
                    background-color: #d1d7e6;
                    border-radius: 1.2em;
                    border: 1px solid #bfc4d4;
                    position: relative;
                    appearance: none;
                    outline: none;
                    cursor: pointer;
                    transition: background 0.18s, border 0.18s;
                    vertical-align: middle;
                    margin-right: 0.5em;
                }
                #${this.modalId} .form-check-input[type="checkbox"]::before {
                    content: "";
                    position: absolute;
                    left: 0.13em;
                    top: 0.12em;
                    width: 0.95em;
                    height: 0.95em;
                    background: #fff;
                    border-radius: 50%;
                    box-shadow: 0 1px 4px rgba(57,85,228,0.10);
                    transition: transform 0.18s cubic-bezier(.4,0,.2,1);
                }
                #${this.modalId} .form-check-input[type="checkbox"]:checked {
                    background-color: #3955e4;
                    border-color: #3955e4;
                }
                #${this.modalId} .form-check-input[type="checkbox"]:checked::before {
                    transform: translateX(1.15em);
                }
                #${this.modalId} .form-check-input-md[type="checkbox"] {
                    width: 3.2em;
                    height: 1.6em;
                    width: 3.2em;
                    height: 1.6em;
                    background-color: #d1d7e6;
                    border-radius: 1.6em;
                    border: 1px solid #bfc4d4;
                    position: relative;
                    appearance: none;
                    outline: none;
                    cursor: pointer;
                    transition: background 0.18s, border 0.18s;
                    vertical-align: middle;
                    margin-right: 0.5em;
                }
                #${this.modalId} .form-check-input-md[type="checkbox"]::before {
                    content: "";
                    position: absolute;
                    left: 0.13em;
                    top: 0.8em;
                    width: 0.95em;
                    height: 0.95em;
                    background: #fff;
                    border-radius: 50%;
                    box-shadow: 0 1px 4px rgba(57,85,228,0.10);
                    transition: transform 0.18s cubic-bezier(.4,0,.2,1);
                }
                #${this.modalId} .form-check-input-md[type="checkbox"]::before {
                    width: 1.3em;
                    height: 1.3em;
                    left: 0.16em;
                    top: 0.15em;
                }
                #${this.modalId} .form-check-input-md[type="checkbox"]:checked {
                    background-color: #3955e4;
                    border-color: #3955e4;
                }
                #${this.modalId} .form-check-input-md[type="checkbox"]:checked::before {
                    transform: translateX(1.5em);
                }
                #${this.modalId} .form-check-input-lg[type="checkbox"] {
                    width: 4.1em;
                    height: 2.1em;
                    background-color: #d1d7e6;
                    border-radius: 2.1em;
                    border: 1px solid #bfc4d4;
                    position: relative;
                    appearance: none;
                    outline: none;
                    cursor: pointer;
                    transition: background 0.18s, border 0.18s;
                    vertical-align: middle;
                    margin-right: 0.5em;

                }
                #${this.modalId} .form-check-input-lg[type="checkbox"]::before {
                    width: 1.7em;
                    height: 1.7em;
                    left: 0.2em;
                    top: 0.18em;
                }
                #${this.modalId} .form-check-input-lg[type="checkbox"]:checked::before {
                    transform: translateX(2.2em);
                }
                #${this.modalId} .form-check-input-lg[type="checkbox"]:checked {
                    background-color: #3955e4;
                    border-color: #3955e4;
                }
                #${this.modalId} .accordion-title:hover {
                    cursor: pointer;
                }
            `;
            document.head.appendChild(style);
        }
    }

    /**
     * Converte una stringa HTML in un nodo DOM
     * @param {string} htmlString - Stringa HTML da convertire
     * @returns {Node} Primo nodo DOM risultante dalla conversione
     * @throws {Error} Se l'HTML non è valido
     */
    static htmlToNode(htmlString) {
        const template = document.createElement("template");
        template.innerHTML = htmlString.trim();
        try {
            return template.content.cloneNode(true);
        } catch (error) {
            throw new Error("Errore nella conversione HTML in nodo DOM: " + error.message);
        }
    }

    // --- COMPONENTI RIUSABILI ---
    input(field) {
        const id = field.id || "";
        const name = field.name || "";
        const type = field.type.split(":")[1] || "text";
        const label = field.label || "";
        const value = field.value || field.default || "";
        const required = field.required ? "required" : "";
        const maxLength = field.maxLength ? `maxlength="${field.maxLength}"` : "";
        const decimal = 1 / (field.decimal || 1);
        const placeholder = field.placeholder || "";
        const width = field.width ? `style="width: ${field.width}"` : "";
        const col = field.col || 4;

        if (!id && !name) {
            return "";
        }

        if (id || name) {
            if (!id && name) {
                id = name;
            } else if (id && !name) {
                name = id;
            }

            return `
            <div class="form-group mb-3 col-md-${col}">
                <label for="${id}" class="form-label">${label}${required ? ' <span style="color:red">*</span>' : ""}</label>
                <input type="${type}" id="${id}" name="${name}" class="form-control" ${type === "number" ? `step="${decimal}"` : "1"} value="${value}" ${required} ${maxLength ? `maxlength="${maxLength}"` : ""} ${field.width ? `style="width: ${field.width}"` : ""} placeholder="${placeholder}"/>
                <div class="invalid-feedback"></div>
                <div class="characters-digit"></div>
            </div>
            `;
        } else if (!id) {
            return `
                <div class="form-group mb-3 col-md-${field.col ?? 4}">
                    <label class="form-label">${label}${required ? ' <span style="color:red">*</span>' : ""}</label>
                    <input type="${type}" class="form-control" placeholder="${placeholder}" value="${value}" ${required} ${maxLength ? `maxlength="${maxLength}"` : ""} ${field.width ? `style="width: ${field.width}"` : ""}/>
                    <div class="invalid-feedback"></div>
                </div>
            `;
        }
    }

    hiddenInput(field) {
        const id = field.id || "";
        const name = field.name || "";
        const value = field.value || field.default || "";
        return `<input type="hidden" id="${id}" name="${name}" value="${value}">`;
    }

    button(field) {
        return `
        <div class="form-group mb-3 col-md-${field.col ?? 4}">
            ${field.labelCell ? `<label for="${field.name}">${field.labelCell}</label>` : ""}
            <div class="d-flex justify-content-center">
                <button type="button" id="${field.id}" name="${field.name}" class="btn ${field.class ?? "btn-info"}" ${field.dataDismiss ? 'data-bs-dismiss="modal"' : ""} ${field.onClick ? 'onclick="' + field.onClick + '"' : ""}>${field.icon ? `<i class="icon icon-${field.icon}"></i>` : ""}${field.label ? `<span>${field.label}</span>` : ""}</button>
            </div>
        </div>
        `;
    }

    select(field) {
        return `
        <div class="form-group mb-3 col-md-${field.col ?? 4}">
            <label for="${field.name}" class="form-label">${field.label}${field.required ? ' <span style="color:red">*</span>' : ""}</label>
            <select class="form-control select2" id="${field.name}" name="${field.name}" ${field.required ? "required" : ""}>
                ${field.options.map((opt) => `<option value="${opt.value}" ${field.value === opt.value ? "selected" : ""}>${opt.label}</option>`).join("")}
            </select>
            <div class="invalid-feedback"></div>
        </div>
        `;
    }

    switch(field) {
        return `
        <div class="form-group mb-3 col-md-${field.col ?? 4}">
            <label class="form-check-label" for="${field.name}">${field.label}</label>
            <div class="form-group form-switch" style="margin-top: 0.5rem;">
                <input class="form-check-input-md" type="checkbox" id="${field.name}" name="${field.name}" ${field.checked ? "checked" : ""}>
            </div>
        </div>
        `;
    }

    textArea(field) {
        return `
        <div class="form-group mb-3 col-md-${field.col ?? 4}">
            <label for="${field.name}" class="form-label">${field.label}${field.required ? ' <span style="color:red">*</span>' : ""}</label>
            <textarea class="form-control" id="${field.name}" name="${field.name}" ${field.required ? "required" : ""}>${field.value || field.default || ""}</textarea>
            <div class="invalid-feedback"></div>
        </div>
        `;
    }

    dialog({ title, body, footer }) {
        return `
        <dialog id="${this.modalId}" aria-labelledby="${this.modalId}Label">
            <div class="bootstrap">
                <form id="${this.formId}" method="dialog" role="dialog" aria-modal="true" aria-labelledby="${this.modalId}Label">
                    <div class="modal-header">
                        <h3 id="${this.modalId}Label">${title}</h3>
                        <button type="button" class="close" aria-label="Chiudi">&times;</button>
                    </div>
                    <div class="modal-body" style="overflow-y: auto; max-height: 500px;">
                        ${body}
                    </div>
                    <div class="modal-footer">
                        ${footer}
                    </div>
                </form>
            </div>
        </dialog>
        `;
    }

    accordion({ id, title, body, collapsed = true }) {
        return `
        <div class="card">
            <div class="card-header" id="heading${id}">
                <h2 class="mb-0 accordion-title" data-toggle="collapse" data-target="#accordion-${id}" aria-expanded="${collapsed ? "false" : "true"}" aria-controls="accordion-${id}">
                    ${title}
                </h2>
            </div>
            <div id="accordion-${id}" class="collapse ${collapsed ? "" : "in"}" aria-labelledby="heading${id}" data-parent="#accordion">
                <div class="card-body">
                    <div class="row">
                        ${body}
                    </div>
                </div>
            </div>
        </div>
        `;
    }

    ucFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // --- MODALE E FORM ---
    getFormHtml() {
        // Usa labelFormSections per generare un accordion collassabile per ogni sezione
        if (!window.labelFormSections) {
            return '<div class="alert alert-danger">Errore: labelFormSections non trovata</div>';
        }
        const self = this;
        const renderField = (field) => {
            let cloneField = { ...field };
            let fieldHtml = "";
            switch (cloneField.type) {
                case "input":
                case "input:text":
                case "input:number":
                case "input:date":
                    fieldHtml = this.input(cloneField);
                    break;
                case "select":
                    fieldHtml = this.select(cloneField);
                    break;
                case "switch":
                case "checkbox":
                    fieldHtml = this.switch({
                        label: cloneField.label || cloneField.name,
                        name: cloneField.name,
                        checked: cloneField.value == 1 || cloneField.value === true || cloneField.default == 1 || cloneField.default === true
                    });
                    break;
                case "button":
                    fieldHtml = this.button(cloneField);
                    break;
                case "textarea":
                    fieldHtml = this.textArea(cloneField);
                    break;
                case "hidden":
                    fieldHtml = this.hiddenInput(cloneField);
                    break;
                case "file":
                    fieldHtml = `<div class="form-group mb-3">
                                <label for="${cloneField.name}">${cloneField.label || cloneField.name}</label>
                                <input type="file" class="form-control" id="${cloneField.name}" name="${cloneField.name}" ${cloneField.required ? "required" : ""}>
                            </div>`;
                    break;
                case "radio":
                    // Se field.options è presente, crea radio group
                    if (cloneField.options && Array.isArray(cloneField.options)) {
                        fieldHtml = `<div class="form-group mb-3">
                            <label>${cloneField.label || cloneField.name}</label><br>
                            ${cloneField.options
                                .map(
                                    (opt) => `
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input-md" type="radio" name="${cloneField.name}" id="${cloneField.name}_${opt.value}" value="${opt.value}" ${(cloneField.value || cloneField.default) == opt.value ? "checked" : ""}>
                                    <label class="form-check-label" for="${cloneField.name}_${opt.value}">${opt.label}</label>
                                </div>
                            `
                                )
                                .join("")}
                        </div>`;
                    }
                    break;
                default:
                    break;
            }

            return fieldHtml;
        };

        // Accordion HTML
        const accordionId = "blankLabelAccordion";
        const accordionHtml = window.labelFormSections
            .map((section, idx) => {
                if (section.section === "hidden") return "";
                const collapsed = section.collapsed ?? true;
                const accordion = self.accordion({
                    id: idx,
                    title: section.section,
                    body: section.components.map((field) => `${renderField(field)}`).join(""),
                    collapsed: collapsed
                });

                return accordion;
            })
            .join("");

        return accordionHtml;
    }

    getModalHtml() {
        // Modale custom HTML/CSS
        return this.dialog({
            title: "Nuova Etichetta Bartolini",
            body: this.getFormHtml(),
            footer: `
                <button type="button" class="btn btn-primary btn-lg blank-label-main-btn" id="btnSubmitLabelForm">Crea Etichetta</button>
                <button type="button" class="btn btn-secondary btn-lg ms-2 blank-label-main-btn" onclick="document.getElementById('${this.modalId}').close()">Annulla</button>
            `
        });
    }

    getModal() {
        const fragment = BlankLabelForm.htmlToNode(this.getModalHtml());
        const dialog = fragment.querySelector("dialog");
        return dialog;
    }

    // --- APERTURA MODALE CON EFFETTO ---
    show() {
        // ... codice esistente ...
        this.destroy();
        this._lastFocused = document.activeElement;
        const fragment = BlankLabelForm.htmlToNode(this.getModalHtml());
        document.body.appendChild(fragment);
        // Dopo che il form è nel DOM, ripristina i valori
        setTimeout(() => {
            const saved = localStorage.getItem("blankLabelFormValues");
            if (saved) {
                const values = JSON.parse(saved);
                values.forEach(({ name, value }) => {
                    const el = document.querySelector(`[name="${name}"]`);
                    if (el) {
                        if (el.type === "checkbox") {
                            el.checked = value == 1 || value === true;
                        } else {
                            el.value = value;
                        }
                    }
                });
            }
        }, 0);
        this._trapFocus();
        this._bindModalEvents();
        this._bindFormEvents();
        // Rimuovi eventuale modale esistente
        this.destroy();
        // Ricorda l'ultimo focus
        this._lastFocused = document.activeElement;
        // Inietta HTML
        const modal = this.getModal();
        document.body.appendChild(modal);
        modal.showModal();

        // Focus trap
        this._trapFocus();
        // Bind chiusura e validazione
        this._bindModalEvents();
        this._bindFormEvents();
    }

    destroy() {
        const modalID = this.modalId;
        const modal = document.getElementById(modalID);
        if (modal) modal.remove();
    }

    // --- VALIDAZIONE E SUBMIT ---
    _bindFormEvents() {
        const form = document.getElementById(this.formId);
        if (!form) return;
        const btnSubmit = form.querySelector("#btnSubmitLabelForm");
        btnSubmit.addEventListener("click", (e) => {
            e.preventDefault();
            const validate = this.validateForm(form);
            if (validate.valid) {
                this.onSubmit(this.getFormData(form));
            } else {
                //Chiudo il form modale
                document.getElementById(this.modalId).close();
                //mostro l'elenco degli errori in un alert SWAL
                Swal.fire({
                    icon: "error",
                    title: "Errore",
                    html: validate.errors.map((err) => err.message).join("<br>"),
                    confirmButtonText: "Chiudi"
                });
            }
        });
        // Rimuovi errori on input
        form.querySelectorAll("input,select").forEach((el) => {
            el.addEventListener("input", () => {
                el.classList.remove("is-invalid");
                el.nextElementSibling && (el.nextElementSibling.textContent = "");

                const char = el.closest(".character-digit");
                if (char) {
                    char.querySelector(".char-limit").textContent = el.value.length;
                }
            });
        });
    }

    validateForm(form) {
        // Salva tutti i valori dei campi input/select/textarea con id
        const formValues = [];
        form.querySelectorAll("input, select, textarea").forEach((el) => {
            if (el.name) {
                if (el.type === "checkbox") {
                    formValues.push({ name: el.name, value: el.checked ? 1 : 0 });
                } else {
                    formValues.push({ name: el.name, value: el.value });
                }
            }
        });
        localStorage.setItem("blankLabelFormValues", JSON.stringify(formValues));
        let valid = true;
        const errors = [];
        form.querySelectorAll("[required]").forEach((el) => {
            if (!el.value.trim() && el.name !== "network") {
                valid = false;
                errors.push({
                    field: el.name,
                    message: el.name + ": Campo obbligatorio"
                });
            }
        });
        // Email
        const email = form.querySelector('input[type="email"]');
        if (email && email.value && !/^\S+@\S+\.\S+$/.test(email.value)) {
            valid = false;
            errors.push({
                field: email.name,
                message: "Email non valida"
            });
        }
        // CAP
        const zip = form.querySelector('input[name="zip"]');
        if (zip && zip.value && !/^\d{5,10}$/.test(zip.value)) {
            valid = false;
            errors.push({
                field: zip.name,
                message: "CAP non valido"
            });
        }
        // Provincia
        const prov = form.querySelector('input[name="province"]');
        if (prov && prov.value && !/^[A-Z]{2}$/.test(prov.value)) {
            valid = false;
            errors.push({
                field: prov.name,
                message: "Inserire 2 lettere maiuscole"
            });
        }
        return { valid, errors };
    }

    getFormData(form) {
        const data = {};
        form.querySelectorAll("input,select").forEach((el) => {
            if (el.type === "checkbox") {
                data[el.name] = el.checked ? 1 : 0;
            } else {
                data[el.name] = el.value;
            }
        });
        return data;
    }

    // Callback per submit (sovrascrivibile)
    onSubmit(formData) {
        // Da sovrascrivere: invio dati AJAX, ecc.
        console.log("Invio dati etichetta Bartolini:", formData);
        // Chiudi la modale custom
        this.close();
    }

    close() {
        const modal = document.getElementById(this.modalId);
        if (modal) {
            modal.classList.remove("show");
            setTimeout(() => {
                modal.remove();
            }, 250);
        }
        if (this._escListener) {
            document.removeEventListener("keydown", this._escListener);
            this._escListener = null;
        }
        if (this._lastFocused && typeof this._lastFocused.focus === "function") {
            this._lastFocused.focus();
        }
    }

    _bindModalEvents() {
        const modal = document.getElementById(this.modalId);
        if (!modal) return;
        // Chiudi su click esterno
        modal.addEventListener("mousedown", (e) => {
            if (e.target === modal) this.close();
        });
        // Chiudi su click X
        const closeBtn = modal.querySelector(".blank-label-close");
        if (closeBtn) closeBtn.onclick = () => this.close();
        // Chiudi con ESC
        this._escListener = (e) => {
            if (e.key === "Escape") this.close();
        };
        document.addEventListener("keydown", this._escListener);
    }

    _trapFocus() {
        const modal = document.getElementById(this.modalId);
        if (!modal) return;
        const focusable = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        this._focusableEls = Array.prototype.slice.call(focusable);
        if (this._focusableEls.length) this._focusableEls[0].focus();
        modal.addEventListener("keydown", (e) => {
            if (e.key !== "Tab") return;
            const first = this._focusableEls[0];
            const last = this._focusableEls[this._focusableEls.length - 1];
            if (e.shiftKey) {
                if (document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                }
            } else {
                if (document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        });
    }

    _bindSwitch(elem) {
        elem.addEventListener("click", (e) => {
            elem.classList.toggle("active");
            e.target.querySelector("input").checked = elem.classList.contains("active");
        });
    }
}

// Esportazione globale se serve
window.BlankLabelForm = BlankLabelForm;

// Esempio di utilizzo:
// const blankLabelForm = new BlankLabelForm();
// blankLabelForm.show();
