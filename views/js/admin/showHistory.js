class ShowHistory {
    dialog = null;
    history = [];
    controllerURL = null;

    constructor(controllerURL) {
        this.controllerURL = controllerURL;
    }

    async getHistory() {
        const response = await fetch(this.controllerURL, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                ajax: 1,
                action: "getHistory"
            })
        });
        const data = await response.json();
        this.history = data.history;
        return this.history;
    }

    _formatDateToDDMMYYYY(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, "0");
        const month = String(date.getMonth() + 1).padStart(2, "0"); // I mesi partono da 0
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    _injectStyle() {
        const style = document.createElement("style");
        style.textContent = `
            dialog {
                padding: 2em;
                border: 1px solid #ccc;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            dialog table {
                width: 100%;
                border-collapse: collapse;
            }
            dialog table th, dialog table td {
                padding: 0.5em;
                text-align: left;
            }
            dialog table th {
                background-color: #f2f2f2;
            }
        `;
        document.head.appendChild(style);
    }

    async prepareDialog() {
        this._injectStyle();
        const history = await this.getHistory();
        const dialog = `
            <dialog id="history-dialog" class="bootstrap">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">Numero</th>
                                    <th class="text-center">Data</th>
                                    <th class="text-center">Numero spedizioni</th>
                                    <th class="text-center">Numero colli</th>
                                    <th class="text-center">Spedizioni COD</th>
                                    <th class="text-center">Importo COD</th>
                                    <th class="text-center">Peso</th>
                                    <th class="text-center">Volume</th>
                                    <th class="text-center">Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${history
                                    .map(
                                        (item) => `
                                    <tr onmouseover="this.style.backgroundColor = '#f5f5f5'; this.style.cursor = 'pointer'" onmouseout="this.style.backgroundColor = ''">
                                        <td>${item.bordero_number}</td>
                                        <td>${this._formatDateToDDMMYYYY(item.bordero_date)}</td>
                                        <td class="text-right">${item.total_deliveries}</td>
                                        <td class="text-right">${item.total_parcels}</td>
                                        <td class="text-right">${item.count_cash_on_delivery}</td>
                                        <td class="text-right">${Number(item.total_cash_on_delivery).toFixed(2)} EUR</td>
                                        <td class="text-right">${Number(item.total_weight_kg).toFixed(1)} kg</td>
                                        <td class="text-right">${Number(item.total_volume_m3).toFixed(3)} m3</td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary" onclick="showBordero(${item.bordero_number})">Visualizza</button>
                                                <button type="button" class="btn btn-info" onclick="printBordero(${item.bordero_number})">Stampa</button>
                                            </div>
                                        </td>
                                    </tr>
                                `
                                    )
                                    .join("")}
                            </tbody>
                        </table>
                    </div>
                </div>
            </dialog>
        `;
        const node = document.createElement("div");
        node.innerHTML = dialog;
        this.dialog = node.querySelector("dialog");
        document.body.insertAdjacentElement("beforeend", this.dialog);
    }

    showDialog() {
        this.dialog.showModal();
    }

    hideDialog() {
        this.dialog.close();
    }
}
