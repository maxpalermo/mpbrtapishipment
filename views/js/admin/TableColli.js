class TableColli {
    _brtForm = null;
    _numberOfParcels = null;
    _volumeM3 = null;
    _weightKG = null;
    _measures = [];
    _parcels = [];

    constructor() {
        const self = this;
        self.modalId = "TableColliModal";
        self.formId = "table-colli-form";
        self.overlayId = "TableColliOverlay";
        self._escListener = null;
        self._focusableEls = null;
        self._lastFocused = null;
        self._brtForm = document.getElementById("BrtLabelForm");
        if (self._brtForm) {
            self._numberOfParcels = self._brtForm.querySelector("#numberOfParcels");
            self._volumeM3 = self._brtForm.querySelector("#volumeM3");
            self._weightKG = self._brtForm.querySelector("#weightKG");
        } else {
            console.error("BrtLabelForm non trovato");
        }

        self._loadMeasures();
    }

    _saveMeasures() {
        const self = this;
        localStorage.setItem("brt_measures", JSON.stringify(self._measures));
    }

    _loadMeasures() {
        const self = this;
        self._measures = JSON.parse(localStorage.getItem("brt_measures"));
        return self._measures;
    }

    getParcels(numericSenderReference) {
        const self = this;
        const measures = self._loadMeasures();
        if (measures) {
            measures.forEach((row, idx) => {
                const index = idx;
                const barcode = numericSenderReference + "-" + (index + 1);
                const length = row.measures[0];
                const width = row.measures[1];
                const height = row.measures[2];
                const weight = parseFloat(row.weight).toFixed(1);
                const volume = parseFloat(row.volume).toFixed(3);
                if (barcode && length && width && height && weight && volume) {
                    self._parcels.push({
                        barcode: barcode,
                        length_mm: length,
                        width_mm: width,
                        height_mm: height,
                        weight_kg: weight,
                        volume_m3: volume
                    });
                }
            });
        }

        return self._parcels;
    }

    _updateDomRefs() {
        const self = this;
        self._brtForm = document.getElementById("BrtLabelForm");
        if (self._brtForm) {
            self._numberOfParcels = self._brtForm.querySelector("#numberOfParcels");
            self._volumeM3 = self._brtForm.querySelector("#volumeM3");
            self._weightKG = self._brtForm.querySelector("#weightKG");
        }
    }

    /**
     * Converte una stringa HTML in un nodo DOM
     * @param {string} htmlString - Stringa HTML da convertire
     * @returns {Node} Primo nodo DOM risultante dalla conversione
     * @throws {Error} Se l'HTML non è valido
     */
    htmlToNode(htmlString) {
        const template = document.createElement("template");
        template.innerHTML = htmlString.trim();
        try {
            const fragment = template.content.cloneNode(true);
            return fragment.firstChild;
        } catch (error) {
            throw new Error("Errore nella conversione HTML in nodo DOM: " + error.message);
        }
    }

    createTable(rows = []) {
        const self = this;
        let measures = self._loadMeasures();
        if (!measures) {
            measures = [];
        }
        if (rows.length === 0) {
            if (measures.length === 0) {
                rows.push({
                    x: 0,
                    y: 0,
                    z: 0,
                    volume: 0,
                    weight: 0
                });
            } else {
                measures.forEach((measure) => {
                    rows.push({
                        x: measure.measures[0],
                        y: measure.measures[1],
                        z: measure.measures[2],
                        volume: measure.volume,
                        weight: measure.weight
                    });
                });
            }
        }

        const thead = self.getTableHeader();
        const tableHTML = `
            <table id="tblColli" class="table table-striped table-bordered table-hover">
                <thead>
                    ${thead.outerHTML}
                </thead>
                <tbody>
                    <!-- dynamic content -->
                </tbody>
            </table>
        `;
        const table = self.htmlToNode(tableHTML);
        rows.forEach((row) => {
            table.querySelector("tbody").appendChild(self.getTableRow(row.x, row.y, row.z, row.volume, row.weight));
        });

        return table;
    }

    getTableHeader() {
        const self = this;
        const header = `
            <tr>
                <th>Lunghezza</th>
                <th>Altezza</th>
                <th>Profondità</th>
                <th>Volume</th>
                <th>Peso</th>
                <th>Riga</th>
            </tr>
        `;

        return self.htmlToNode(header);
    }

    getTableRow(x = 0, y = 0, z = 0, volume = 0, weight = 0) {
        const self = this;

        x = parseFloat(x).toFixed(0);
        y = parseFloat(y).toFixed(0);
        z = parseFloat(z).toFixed(0);
        volume = parseFloat(volume).toFixed(3);
        weight = parseFloat(weight).toFixed(1);

        const tr = `
            <tr>
                <td><input type="text" name="length" value="${x}" default="${x}" required="true" type="input:numeric" class="form-control text-right measure"></td>
                <td><input type="text" name="width" value="${y}" default="${y}" required="true" type="input:numeric" class="form-control text-right measure"></td>
                <td><input type="text" name="height" value="${z}" default="${z}" required="true" type="input:numeric" class="form-control text-right measure"></td>
                <td><input type="text" name="volume" value="${volume}" default="${volume}" required="true" type="input:numeric" class="form-control text-right volume" readonly tabindex="-1"></td>
                <td><input type="text" name="weight" value="${weight}" default="${weight}" required="true" type="input:numeric" class="form-control text-right weight"></td>
                <td>
                    <div class="btn-group">
                        <button type="button" name="btnAddRow" class="btn btn-info"><i class="icon icon-plus text-info"></i></button>
                        <button type="button" name="btnDelRow" class="btn btn-danger"><i class="icon icon-minus text-danger"></i></button>
                    </div>
                </td>
            </tr>
        `;

        const node = self.htmlToNode(tr);
        node.querySelectorAll("input").forEach((el) => {
            el.addEventListener("focus", (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                e.target.select();
            });
            el.addEventListener("blur", (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                if (e.target.classList.contains("measure")) {
                    e.target.value = parseFloat(e.target.value).toFixed(0);
                }
                self.getTableSum();
            });
        });
        node.querySelector("button[name='btnAddRow']").addEventListener("click", () => {
            self.addRow();
        });
        node.querySelector("button[name='btnDelRow']").addEventListener("click", () => {
            const idx = node.querySelector("button[name='btnDelRow']").closest("tr").rowIndex;
            self.delRow(idx);
        });
        return node;
    }

    addRow() {
        const self = this;
        const table = document.getElementById("tblColli");
        const row = self.getTableRow();
        table.querySelector("tbody").appendChild(row);
        // imposto il focus sul primo input della nuova riga
        row.querySelector("input").focus();
        row.querySelector("input").select();
    }

    delRow(idx) {
        console.log("DELROW " + idx);
        const self = this;
        const table = document.getElementById("tblColli");
        table.querySelector("tbody").removeChild(table.querySelector("tbody").children[idx - 1]);
        self._measures.splice(idx - 1, 1);

        if (table.querySelector("tbody").children.length === 0) {
            self._measures = [];
            self.addRow();
        }

        self.getTableSum();
    }

    async showFormColli() {
        const self = this;
        self.styleModal();
        const table = self.createTable();

        try {
            document.getElementById("TableColliModal").remove();
        } catch (error) {
            console.log("catch error: ", error);
        }

        const modalHTML = `
            <dialog id="TableColliModal">
                <form method="dialog" style="margin:0;padding:0;">
                    <header style="display:flex;justify-content:space-between;align-items:center;padding:1rem 1rem 0 1rem;">
                        <h5 class="modal-title" id="TableColliModalLabel">Colli</h5>
                        <button type="button" class="btn-close" aria-label="Close" onclick="document.getElementById('TableColliModal').close()"></button>
                    </header>
                    <div id="table-modal-body" class="modal-body" style="padding:1rem;">
                        <!-- dynamic content -->
                    </div>
                    <footer style="display:flex;justify-content:flex-end;gap:0.5rem;padding:0 1rem 1rem 1rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="icon icon-close"></i>
                            <span>Chiudi</span>
                        </button>
                    </footer>
                </form>
            </dialog>
        `;
        const modal = self.htmlToNode(modalHTML);
        modal.querySelector("#table-modal-body").appendChild(table);
        modal.addEventListener("close", (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            self._measures = [];
            self.getTableSum();
            self._saveMeasures();
            modal.close();
        });

        document.body.appendChild(modal);
        modal.showModal();
    }

    getTableSum() {
        const self = this;
        self._updateDomRefs();
        self._measures = [];

        const table = document.getElementById("tblColli");
        const rows = table.querySelectorAll("tbody tr");
        let sumVolume = 0;
        let sumWeight = 0;
        rows.forEach((row) => {
            const measures = row.querySelectorAll(".measure");
            let rowVolume = 1;
            let rowWeight = 0;
            const m = [];
            measures.forEach((measure) => {
                m.push(parseFloat(measure.value));
                rowVolume *= parseFloat(measure.value);
            });
            rowVolume = rowVolume / 1000000000;
            rowWeight = parseFloat(row.querySelector(".weight").value);
            sumVolume += rowVolume;
            sumWeight += rowWeight;
            const mRow = {
                measures: m,
                volume: rowVolume,
                weight: rowWeight
            };
            self._measures.push(mRow);
            self._saveMeasures();

            row.querySelector(".volume").value = rowVolume.toFixed(3);
            row.querySelector(".weight").value = rowWeight.toFixed(1);
        });

        if (self._numberOfParcels && self._volumeM3 && self._weightKG) {
            self._numberOfParcels.value = rows.length;
            self._volumeM3.value = sumVolume.toFixed(3);
            self._weightKG.value = sumWeight.toFixed(1);
        } else {
            console.error("Campi del form non trovati");
        }
    }

    styleModal() {
        if (document.getElementById("TableColliModalCss")) return;

        const style = document.createElement("style");
        style.id = "TableColliModalCss";
        style.textContent = `
            #TableColliModal {
                border: none;
                border-radius: 14px;
                box-shadow: 0 8px 40px 0 rgba(0,0,0,0.25);
                min-width: 420px;
                max-width: 98vw;
                padding: 0;
                background: #fff;
                color: #222;
                font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
                overflow: visible;
                animation: fadeInModal 0.25s cubic-bezier(.4,0,.2,1);
            }
            #TableColliModal form {
                padding: 0 1.5rem 1.5rem 1.5rem;
            }
            #TableColliModal header {
                border-bottom: 1px solid #e5e5e5;
                background: #f7f7fa;
                border-radius: 14px 14px 0 0;
            }
            #TableColliModal .btn-close {
                background: transparent;
                border: none;
                font-size: 1.5rem;
                color: #888;
                cursor: pointer;
                transition: color 0.2s;
            }
            #TableColliModal .btn-close:hover {
                color: #333;
            }
            #TableColliModal button, #TableColliModal input, #TableColliModal select {
                border-radius: 6px;
                border: 1px solid #d3d3d6;
                padding: 0.5em 0.75em;
                font-size: 1em;
                margin: 0.25em 0;
            }
            #TableColliModal table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                margin: 1.2rem 0;
                background: #f9fafd;
                border-radius: 8px;
                overflow: hidden;
            }
            #TableColliModal th, #TableColliModal td {
                padding: 0.75em 0.5em;
                text-align: left;
                border-bottom: 1px solid #ececec;
            }
            #TableColliModal tr:last-child td {
                border-bottom: none;
            }
            #TableColliModal tr:hover td {
                background: #f0f6ff;
            }
            #TableColliModal button[name='btnAddRow'],
            #TableColliModal button[name='btnDelRow'] {
                background: #e8f0fe;
                border: 1px solid #bcdcff;
                color: #1976d2;
                padding: 0.3em 0.7em;
                font-size: 1.05em;
                cursor: pointer;
                margin-left: 0.2em;
                margin-right: 0.2em;
                transition: background 0.15s, color 0.15s, border 0.15s;
            }
            #TableColliModal button[name='btnAddRow']:hover,
            #TableColliModal button[name='btnDelRow']:hover {
                background: #1976d2;
                color: #fff;
                border: 1px solid #1976d2;
            }
            #TableColliModal input[type='text'] {
                text-align: right;
                padding-right: 0.5em;
            }
            @media (max-width: 600px) {
                #TableColliModal {
                    min-width: 98vw;
                    max-width: 99vw;
                    padding: 0;
                }
                #TableColliModal table, #TableColliModal form {
                    padding: 0.5rem;
                }
            }
            @keyframes fadeInModal {
                from { opacity: 0; transform: translateY(20px);}
                to   { opacity: 1; transform: translateY(0);}
            }
        `;

        document.head.appendChild(style);
    }
}

window.TableColli = TableColli;
