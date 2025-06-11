async function brtLabelFormLoaded() {
    console.log("BrtLabelFormLoaded labelFormScript.js");

    const form = document.getElementById("brt-label-form");
    if (!form) return;

    // Focus primo campo
    setTimeout(() => {
        const firstInput = form.querySelector("input,select,textarea");
        if (firstInput) firstInput.focus();
    }, 200);

    // Helper: scroll to first error
    function scrollToFirstError() {
        const error = form.querySelector(".brt-error");
        if (error) {
            error.scrollIntoView({ behavior: "smooth", block: "center" });
            error.focus();
        }
    }

    // Helper: mostra errore campo
    function showFieldError(field, msg) {
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

    function clearFieldError(field) {
        let err = field.parentNode.querySelector(".brt-error-msg");
        if (err) err.remove();
        field.classList.remove("brt-error");
        field.removeAttribute("aria-invalid");
    }

    // Uppercase for provincia/nazione
    ["consigneeProvinceAbbreviation", "consigneeCountryAbbreviationISOAlpha2"].forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener("input", (e) => {
                e.target.value = e.target.value.toUpperCase();
            });
        }
    });

    // Validazione custom
    function validateForm() {
        let valid = true;
        [...form.elements].forEach((el) => {
            clearFieldError(el);
            if (el.hasAttribute("required") && !el.value.trim()) {
                showFieldError(el, "Campo obbligatorio");
                valid = false;
            }
            // Validazione email
            if (el.type === "email" && el.value) {
                const re = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
                if (!re.test(el.value)) {
                    showFieldError(el, "Email non valida");
                    valid = false;
                }
            }
            // Validazione CAP (solo numeri, 5 cifre)
            if (el.name === "consigneeZIPCode" && el.value) {
                if (!/^\d{5,10}$/.test(el.value)) {
                    showFieldError(el, "CAP non valido");
                    valid = false;
                }
            }
            // Validazione provincia/nazione (2 lettere)
            if ((el.name === "consigneeProvinceAbbreviation" || el.name === "consigneeCountryAbbreviationISOAlpha2") && el.value) {
                if (!/^[A-Z]{2}$/.test(el.value)) {
                    showFieldError(el, "Inserire 2 lettere maiuscole");
                    valid = false;
                }
            }
        });
        return valid;
    }

    // Blocco doppio invio
    let submitting = false;
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        if (submitting) return;
        if (!validateForm()) {
            scrollToFirstError();
            return;
        }
        submitting = true;
        // Mostra loader su SWAL2
        if (window.Swal) {
            Swal.showLoading();
        }
        // Prepara dati
        const formData = new FormData(form);
        const data = {};
        formData.forEach((v, k) => {
            data[k] = v;
        });
        // ESEMPIO: endpoint da sostituire con quello reale
        fetch("BRT_LABEL_CREATE_ENDPOINT", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        })
            .then((res) => res.json())
            .then((json) => {
                submitting = false;
                if (window.Swal) Swal.hideLoading();
                // Gestione risposta
                if (json.success) {
                    if (window.Swal) {
                        Swal.fire({ icon: "success", title: "Etichetta creata!", text: "Operazione completata con successo." });
                    }
                    // Qui puoi chiudere modale e aggiornare lista, ecc.
                } else {
                    if (window.Swal) {
                        Swal.fire({ icon: "error", title: "Errore", text: json.message || "Si è verificato un errore." });
                    }
                }
            })
            .catch((err) => {
                submitting = false;
                if (window.Swal) {
                    Swal.hideLoading();
                    Swal.fire({ icon: "error", title: "Errore di rete", text: err.message });
                }
            });
    });
}
