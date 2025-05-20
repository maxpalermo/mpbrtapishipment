const labelFormSections = [
    // Sezione nascosta/hidden
    {
        section: "hidden",
        components: [
            {
                id: null,
                name: "id_order",
                value: "",
                default: "",
                required: false,
                type: "hidden"
            },
            {
                id: null,
                name: "senderCustomerCode",
                value: "",
                default: "",
                required: true,
                type: "input:number",
                maxLength: 7,
                decimal: 0
            },
            {
                id: null,
                name: "departureDepot",
                value: "",
                default: "",
                required: true,
                type: "input:number",
                maxLength: 3,
                decimal: 0
            }
        ]
    },
    // Sezione Destinatario
    {
        section: "destinatario",
        collapsed: false,
        components: [
            {
                id: "consigneeCompanyName",
                label: "Ragione sociale destinatario",
                name: "consigneeCompanyName",
                value: "",
                default: "",
                required: true,
                type: "input:text",
                maxLength: 70
            },
            {
                id: "consigneeAddress",
                label: "Indirizzo destinatario",
                name: "consigneeAddress",
                value: "",
                default: "",
                required: true,
                type: "input:text",
                maxLength: 35
            },
            {
                id: "consigneeZIPCode",
                label: "CAP destinatario",
                name: "consigneeZIPCode",
                value: "",
                default: "",
                required: true,
                type: "input:number",
                maxLength: 9
            },
            {
                id: "consigneeCity",
                label: "Città destinatario",
                name: "consigneeCity",
                value: "",
                default: "",
                required: true,
                type: "input:text",
                maxLength: 35
            },
            {
                id: "consigneeProvinceAbbreviation",
                label: "Provincia destinatario",
                name: "consigneeProvinceAbbreviation",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 2
            },
            {
                id: "consigneeCountryAbbreviationISOAlpha2",
                label: "Nazione destinatario (ISO)",
                name: "consigneeCountryAbbreviationISOAlpha2",
                value: "",
                default: "IT",
                required: false,
                type: "select",
                options: [
                    { value: "IT", label: "Italia" },
                    { value: "A", label: "Austria" },
                    { value: "AND", label: "Andorra" },
                    { value: "B", label: "Belgio" },
                    { value: "BG", label: "Bulgaria" },
                    { value: "CH", label: "Svizzera" },
                    { value: "CZ", label: "Repubblica Ceca" },
                    { value: "D", label: "Germania" },
                    { value: "DK", label: "Danimarca" },
                    { value: "E", label: "Spagna" },
                    { value: "F", label: "Francia" },
                    { value: "GB", label: "Gran Bretagna" },
                    { value: "GBZ", label: "Gibilterra" },
                    { value: "HU", label: "Ungheria" },
                    { value: "IRL", label: "Irlanda" },
                    { value: "L", label: "Lussemburgo" },
                    { value: "LI", label: "Liechtenstein" },
                    { value: "MC", label: "Principato Monaco" },
                    { value: "NL", label: "Olanda" },
                    { value: "P", label: "Portogallo" },
                    { value: "PL", label: "Polonia" },
                    { value: "RO", label: "Romania" },
                    { value: "S", label: "Svezia" },
                    { value: "SK", label: "Slovacchia" }
                ],
                maxLength: 2
            },
            {
                id: "consigneeContactName",
                label: "Nome contatto destinatario",
                name: "consigneeContactName",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 35
            },
            {
                id: "consigneeEMail",
                label: "Email destinatario",
                name: "consigneeEMail",
                value: "",
                default: "",
                required: false,
                type: "input:email",
                maxLength: 70
            },
            {
                id: "consigneeTelephone",
                label: "Telefono destinatario",
                name: "consigneeTelephone",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 16
            },
            {
                id: "consigneeMobilePhoneNumber",
                label: "Cellulare destinatario",
                name: "consigneeMobilePhoneNumber",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 16
            },
            {
                id: "network",
                label: "Network",
                name: "network",
                value: "",
                default: "",
                required: true,
                type: "select",
                options: [
                    { value: "", label: "Di default" },
                    { value: "D", label: "DPD" },
                    { value: "E", label: "Euro Express" },
                    { value: "S", label: "FED" }
                ],
                maxLength: 1
            },
            {
                id: "deliveryFreightTypeCode",
                label: "Tipo spedizione",
                name: "deliveryFreightTypeCode",
                value: "DAP",
                default: "DAP",
                required: true,
                type: "select",
                options: [
                    { value: "DAP", label: "FRANCO" },
                    { value: "EXW", label: "EXW" }
                ],
                maxLength: 3
            }
        ]
    },
    // Sezione Spedizione
    {
        section: "spedizione",
        collapsed: false,
        components: [
            {
                id: "numericSenderReference",
                label: "Riferimento mittente (numerico)",
                name: "numericSenderReference",
                value: "",
                default: "",
                required: true,
                type: "input:text",
                maxLength: 15,
                pattern: "^[0-9]*$"
            },
            {
                id: "alphanumericSenderReference",
                label: "Riferimento mittente (alfanumerico)",
                name: "alphanumericSenderReference",
                value: "",
                default: "",
                required: true,
                type: "input:text",
                maxLength: 15
            },
            {
                id: "declaredParcelValue",
                label: "Valore dichiarato",
                name: "declaredParcelValue",
                value: "",
                default: "",
                required: false,
                type: "input:number"
            },
            {
                id: "declaredParcelValueCurrency",
                label: "Valuta valore dichiarato",
                name: "declaredParcelValueCurrency",
                value: "EUR",
                default: "EUR",
                required: false,
                type: "input:text",
                maxLength: 3
            },
            {
                id: "insuranceAmount",
                label: "Assicurazione",
                name: "insuranceAmount",
                value: "",
                default: "",
                required: false,
                type: "input:number",
                maxLength: 7,
                decimal: 2
            },
            {
                id: "insuranceAmountCurrency",
                label: "Valuta assicurazione",
                name: "insuranceAmountCurrency",
                value: "EUR",
                default: "EUR",
                required: false,
                type: "input:number",
                maxLength: 3
            },
            {
                id: "serviceType",
                label: "Tipo servizio",
                name: "serviceType",
                value: "",
                default: "",
                required: true,
                type: "select",
                options: [
                    { value: "DEF", label: "Default" },
                    { value: "E", label: "Priority" },
                    { value: "H", label: "10:30" }
                ],
                maxLength: 1
            },
            {
                id: "notes",
                label: "Note",
                name: "notes",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 70
            },
            {
                id: "btnColli",
                label: "Colli",
                name: "btnColli",
                labelCell: "Mostra colli",
                type: "button",
                onClick: "showModalColli()",
                icon: "table"
            },
            {
                id: "numberOfParcels",
                label: "Numero colli",
                name: "numberOfParcels",
                value: "",
                default: "",
                required: true,
                type: "input:number",
                maxLength: 2,
                decimal: 0
            },
            {
                id: "volumeM3",
                label: "Volume (m3)",
                name: "volumeM3",
                value: "",
                default: "",
                required: true,
                type: "input:number",
                maxLength: 5,
                decimal: 3
            },
            {
                id: "weightKG",
                label: "Peso (kg)",
                name: "weightKG",
                value: "",
                default: "",
                required: true,
                type: "input:number",
                maxLength: 5,
                decimal: 1
            }
        ]
    },
    // Sezione Opzioni Avanzate
    {
        section: "opzioni",
        collapsed: false,
        components: [
            {
                id: "isCODMandatory",
                label: "Contrassegno",
                name: "isCODMandatory",
                value: "0",
                default: "0",
                required: false,
                type: "switch",
                maxLength: 1
            },
            {
                id: "cashOnDelivery",
                label: "Valore contrassegno",
                name: "cashOnDelivery",
                value: "",
                default: "",
                required: false,
                type: "input:number",
                maxLength: 7,
                decimal: 2
            },
            {
                id: "codPaymentType",
                label: "Tipo pagamento contrassegno",
                name: "codPaymentType",
                value: "",
                default: "",
                required: false,
                type: "select",
                options: [
                    { value: "", label: "ACCETTARE CONTANTE" },
                    { value: "BM", label: "ACCETTARE ASSEGNO BANCARIO INTESTATO ALLA MITTENTE" },
                    { value: "CM", label: "ACCETTARE ASSEGNO CIRCOLARE INTESTATO ALLA MITTENTE" },
                    { value: "BB", label: "ACCETTARE ASSEGNO BANCARIO INTESTATO CORRIERE CON MANLEVA" },
                    { value: "OM", label: "ACCETTARE ASSEGNO INTESTATO AL MITTENTE ORIGINALE" },
                    { value: "OC", label: "ACCETTARE ASSEGNO CIRCOLARE INTESTATO AL MITTENTE ORIGINALE" }
                ],
                maxLength: 2
            },
            {
                id: "codCurrency",
                label: "Valuta contrassegno",
                name: "codCurrency",
                value: "EUR",
                default: "EUR",
                required: false,
                type: "input:text",
                maxLength: 3
            },
            {
                id: "parcelsHandlingCode",
                label: "Gestione colli",
                name: "parcelsHandlingCode",
                value: "",
                default: "",
                required: false,
                type: "select",
                options: [
                    { value: "", label: "Default" },
                    { value: "PALLET", label: "Pallet" },
                    { value: "COLLO", label: "Collo" }
                ],
                maxLength: 2
            },
            {
                id: "particularitiesDeliveryManagementCode",
                label: "Particolarità consegna",
                name: "particularitiesDeliveryManagementCode",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 2
            },
            {
                id: "particularitiesHoldOnStockManagementCode",
                label: "Particolarità fermo deposito",
                name: "particularitiesHoldOnStockManagementCode",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxlength: 2
            },
            {
                id: "isAlertRequired",
                label: "Alert obbligatorio",
                name: "isAlertRequired",
                value: "0",
                default: "0",
                required: false,
                type: "switch",
                maxLength: 1
            }
        ]
    },
    // Sezione Avanzata (card a scomparsa)
    {
        section: "avanzate",
        collapsed: true,
        components: [
            {
                id: "consigneeVATNumber",
                label: "Partita IVA destinatario",
                name: "consigneeVATNumber",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 16
            },
            {
                id: "consigneeVATNumberCountryISOAlpha2",
                label: "Sigla Nazione PIVA",
                name: "consigneeVATNumberCountryISOAlpha2",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 2
            },
            {
                id: "consigneeItalianFiscalCode",
                label: "Codice fiscale destinatario",
                name: "consigneeItalianFiscalCode",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 16
            },
            {
                id: "pricingConditionCode",
                label: "Condizioni di prezzo",
                name: "pricingConditionCode",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 3
            },
            {
                id: "senderParcelType",
                label: "Tipo collo mittente",
                name: "senderParcelType",
                value: "",
                default: "",
                required: false,
                type: "input:text"
            },
            {
                id: "quantityToBeInvoiced",
                label: "Quantità da fatturare",
                name: "quantityToBeInvoiced",
                value: "",
                default: "",
                required: false,
                type: "input:number",
                maxLength: 7,
                decimal: 3
            },
            {
                id: "deliveryType",
                label: "Tipo consegna",
                name: "deliveryType",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 1
            },
            {
                id: "variousParticularitiesManagementCode",
                label: "Particolarità varie",
                name: "variousParticularitiesManagementCode",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 2
            },
            {
                id: "particularDelivery1",
                label: "Particolarità consegna 1",
                name: "particularDelivery1",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 1
            },
            {
                id: "particularDelivery2",
                label: "Particolarità consegna 2",
                name: "particularDelivery2",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 1
            },
            {
                id: "palletType1",
                label: "Tipo pallet 1",
                name: "palletType1",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 4
            },
            {
                id: "palletType1Number",
                label: "Numero pallet 1",
                name: "palletType1Number",
                value: "",
                default: "",
                required: false,
                type: "input:number",
                maxLength: 2,
                decimal: 0
            },
            {
                id: "palletType2",
                label: "Tipo pallet 2",
                name: "palletType2",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 4
            },
            {
                id: "palletType2Number",
                label: "Numero pallet 2",
                name: "palletType2Number",
                value: "",
                default: "",
                required: false,
                type: "input:number",
                maxLength: 2,
                decimal: 0
            },
            {
                id: "originalSenderCompanyName",
                label: "Nome azienda mittente originale",
                name: "originalSenderCompanyName",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 25
            },
            {
                id: "originalSenderZIPCode",
                label: "CAP mittente originale",
                name: "originalSenderZIPCode",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 9
            },
            {
                id: "originalSenderCountryAbbreviationISOAlpha2",
                label: "Paese mittente originale",
                name: "originalSenderCountryAbbreviationISOAlpha2",
                value: "",
                default: "IT",
                required: false,
                type: "input:text",
                maxLength: 2
            },
            {
                id: "cmrCode",
                label: "CMR",
                name: "cmrCode",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 35
            },
            {
                id: "neighborNameMandatoryAuthorization",
                label: "Nome vicino obbligatorio",
                name: "neighborNameMandatoryAuthorization",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 70
            },
            {
                id: "pinCodeMandatoryAuthorization",
                label: "Codice PIN obbligatorio",
                name: "pinCodeMandatoryAuthorization",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 15
            },
            {
                id: "packingListPDFFlagPrint",
                label: "Stampa lista colli PDF",
                name: "packingListPDFFlagPrint",
                value: "",
                default: "",
                required: false,
                type: "input:checkbox",
                maxLength: 1
            },
            {
                id: "packingListPDFFlagEmail",
                label: "Invia lista colli PDF",
                name: "packingListPDFFlagEmail",
                value: "",
                default: "",
                required: false,
                type: "input:checkbox",
                maxLength: 1
            },
            {
                id: "expiryDate",
                label: "Scadenza",
                name: "expiryDate",
                value: "",
                default: "",
                required: false,
                type: "input:date",
                maxLength: 10
            },
            {
                id: "holdForPickup",
                label: "Riserva per prelievo",
                name: "holdForPickup",
                value: "0",
                default: "0",
                required: false,
                type: "switch",
                maxLength: 1
            },
            {
                id: "genericReference",
                label: "Riferimento generico",
                name: "genericReference",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxlength: 35
            },
            {
                id: "pudoId",
                label: "PUDO ID",
                name: "pudoId",
                value: "",
                default: "",
                required: false,
                type: "input:text",
                maxLength: 20
            },
            {
                id: "brtServiceCode",
                label: "Codice servizio BRT",
                name: "brtServiceCode",
                value: "",
                default: "",
                required: false,
                type: "select",
                options: [
                    {
                        value: "",
                        label: "Nessuno",
                        description: "Dato facoltativo"
                    },
                    {
                        value: "B11",
                        label: "Direct to Shop",
                        description: "Viene controllato che sia presente il pudoId, che sia formalmente valido e che le caratteristiche della spedizione siano compatibili con il BRT Service Code"
                    },
                    {
                        value: "B13",
                        label: "Shop to Shop",
                        description: "Viene controllato che sia presente il pudoId, che sia formalmente valido e che le caratteristiche della spedizione siano compatibili con il BRT Service Code"
                    },
                    {
                        value: "B14",
                        label: "Shop to Home",
                        description: "Viene controllato che la spedizione non sia diretta a un PUDO BRTfermopoint e che le caratteristiche della spedizione siano compatibili con il BRT Service Code"
                    },
                    {
                        value: "B15",
                        label: "Return from Shop",
                        description: "Viene controllato che sia compilato correttamente il dato returnDepot. Non occorrono i dati del destinatario, se presenti verranno sostituiti con i dati del Deposito di reso."
                    },
                    {
                        value: "B20",
                        label: "FRESH",
                        description: "Servizio FRESH per spedizioni a temperatura controllata e garantita (0 °C -  4°C)"
                    }
                ],
                maxLength: 3
            },
            {
                id: "returnDepot",
                label: "Deposito di reso",
                name: "returnDepot",
                value: "",
                default: "",
                required: false,
                type: "input:number",
                maxLength: 10,
                decimal: 0
            },
            {
                id: "consigneeClosingShift1_DayOfTheWeek",
                label: "Giorno chiusura shift 1",
                name: "consigneeClosingShift1_DayOfTheWeek",
                value: "",
                default: "",
                required: false,
                type: "select",
                options: [
                    { value: "", label: "Seleziona" },
                    { value: "MON", label: "Lunedì" },
                    { value: "TUE", label: "Martedì" },
                    { value: "WED", label: "Mercoledì" },
                    { value: "THU", label: "Giovedì" },
                    { value: "FRI", label: "Venerdì" },
                    { value: "SAT", label: "Sabato" },
                    { value: "SUN", label: "Domenica" }
                ],
                maxLength: 3
            },
            {
                id: "consigneeClosingShift1_PeriodOfTheDay",
                label: "Periodo di chiusura shift 1",
                name: "consigneeClosingShift1_PeriodOfTheDay",
                value: "",
                default: "",
                required: false,
                type: "select",
                options: [
                    { value: "", label: "Seleziona" },
                    { value: "AM", label: "Mattino" },
                    { value: "PM", label: "Pomeriggio" }
                ],
                maxLength: 2
            },
            {
                id: "consigneeClosingShift2_DayOfTheWeek",
                label: "Giorno chiusura shift 2",
                name: "consigneeClosingShift2_DayOfTheWeek",
                value: "",
                default: "",
                required: false,
                type: "select",
                options: [
                    { value: "", label: "Seleziona" },
                    { value: "MON", label: "Lunedì" },
                    { value: "TUE", label: "Martedì" },
                    { value: "WED", label: "Mercoledì" },
                    { value: "THU", label: "Giovedì" },
                    { value: "FRI", label: "Venerdì" },
                    { value: "SAT", label: "Sabato" },
                    { value: "SUN", label: "Domenica" }
                ],
                maxLength: 3
            },
            {
                id: "consigneeClosingShift2_PeriodOfTheDay",
                label: "Periodo di chiusura shift 2",
                name: "consigneeClosingShift2_PeriodOfTheDay",
                value: "",
                default: "",
                required: false,
                type: "select",
                options: [
                    { value: "", label: "Seleziona" },
                    { value: "AM", label: "Mattino" },
                    { value: "PM", label: "Pomeriggio" }
                ],
                maxLength: 2
            },
            {
                id: "deliveryDateRequired",
                label: "Data consegna richiesta",
                name: "deliveryDateRequired",
                value: "",
                default: "",
                required: false,
                type: "input:date",
                maxLength: 10
            }
        ]
    }
];

// Esporta per utilizzo globale
window.labelFormSections = labelFormSections;

// Tipi di componenti utilizzati nel form:
// - input:text         // campo input di testo standard
// - input:number      // campo input numerico
// - select             // menu a tendina/select
// - switch             // interruttore booleano (on/off)
// - textarea           // area di testo multipla riga (se presente in altri componenti)
// - date               // selettore data (se presente in altri componenti)
// - hidden             // campo hidden (non visibile)
// - checkbox           // interruttore booleano (on/off)
// - radio              // interruttore booleano (on/off)
// - file               // campo file (non visibile)
