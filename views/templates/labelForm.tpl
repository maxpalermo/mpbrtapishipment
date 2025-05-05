<form id="brt-label-form" autocomplete="off" style="width:100%;max-width:1200px;margin:auto;">
    <style>
        .brt-form-sections {
            display: flex;
            flex-direction: column;
            gap: 28px;
            margin-bottom: 32px;
        }

        .brt-form-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 24px 20px 16px 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            min-width: 0;
            margin-bottom: 0;
        }

        .brt-form-section h3 {
            margin-top: 0;
            font-size: 1.15rem;
            color: #1a2a3a;
            margin-bottom: 12px;
        }

        .brt-form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 18px 32px;
            width: 100%;
            margin-bottom: 0;
        }

        .brt-form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1 1 230px;
            margin-bottom: 14px;
        }

        .brt-form-group label {
            font-weight: 500;
            font-size: 0.98rem;
            color: #2b2b2b;
        }

        .brt-form-group input,
        .brt-form-group select,
        .brt-form-group textarea {
            padding: 7px 10px;
            border: 1px solid #bdbdbd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border 0.2s;
        }

        .brt-form-group input:focus,
        .brt-form-group select:focus,
        .brt-form-group textarea:focus {
            border-color: #007bff;
            outline: none;
        }

        .brt-form-actions {
            text-align: right;
            margin-top: 16px;
        }

        @media (max-width: 900px) {
            .brt-form-row {
                gap: 12px 12px;
            }

            .brt-form-group {
                min-width: 120px;
                max-width: 100%;
            }
        }

        @media (max-width: 700px) {
            .brt-form-row {
                flex-direction: column;
                gap: 10px 0;
            }

            .brt-form-group {
                min-width: 0;
                max-width: 100%;
            }
        }
    </style>
    <div class="brt-form-sections">
        <!-- Sezione Destinatario -->
        <div class="card">
            <div class="card-header">
                <h3>Destinatario</h3>
            </div>
            <div class="card-body">
                <input type="hidden" name="id_order" value="{$formData.id_order}">
                <input type="hidden" name="senderCustomerCode" value="{$formData.senderCustomerCode}">
                <input type="hidden" name="departureDepot" value="{$formData.departureDepot}">
                <div class="brt-form-row">
                    <div class="brt-form-group" style="min-width: 20rem; max-width: 100%">
                        <label for="consigneeCompanyName">Ragione Sociale / Nome *</label>
                        <input type="text" id="consigneeCompanyName" name="consigneeCompanyName" required value="{if isset($formData.consigneeCompanyName)}{$formData.consigneeCompanyName|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group" style="min-width: 20rem; max-width: 100%">
                        <label for="consigneeAddress">Indirizzo *</label>
                        <input type="text" id="consigneeAddress" name="consigneeAddress" required value="{if isset($formData.consigneeAddress)}{$formData.consigneeAddress|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group" style="min-width: 92px; max-width: 92px;">
                        <label for="consigneeZIPCode">CAP *</label>
                        <input type="text" id="consigneeZIPCode" name="consigneeZIPCode" required maxlength="10" value="{if isset($formData.consigneeZIPCode)}{$formData.consigneeZIPCode|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group" style="min-width: 120px; max-width: 100%;">
                        <label for="consigneeCity">Città *</label>
                        <input type="text" id="consigneeCity" name="consigneeCity" required value="{if isset($formData.consigneeCity)}{$formData.consigneeCity|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group" style="min-width: 92px; max-width: 92px;">
                        <label for="consigneeProvinceAbbreviation">Provincia *</label>
                        <input type="text" id="consigneeProvinceAbbreviation" name="consigneeProvinceAbbreviation" maxlength="2" required value="{if isset($formData.consigneeProvinceAbbreviation)}{$formData.consigneeProvinceAbbreviation|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group" style="min-width: 92px; max-width: 92px;">
                        <label for="consigneeCountryAbbreviationISOAlpha2">Nazione *</label>
                        <input type="text" id="consigneeCountryAbbreviationISOAlpha2" name="consigneeCountryAbbreviationISOAlpha2" maxlength="2" required value="{if isset($formData.consigneeCountryAbbreviationISOAlpha2)}{$formData.consigneeCountryAbbreviationISOAlpha2|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group">
                        <label for="consigneeContactName">Referente</label>
                        <input type="text" id="consigneeContactName" name="consigneeContactName" value="{if isset($formData.consigneeContactName)}{$formData.consigneeContactName|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group" style="min-width: 250px; max-width: 100%">
                        <label for="consigneeEMail">Email *</label>
                        <input type="email" id="consigneeEMail" name="consigneeEMail" required value="{if isset($formData.consigneeEMail)}{$formData.consigneeEMail|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group">
                        <label for="consigneeTelephone">Telefono *</label>
                        <input type="text" id="consigneeTelephone" name="consigneeTelephone" required value="{if isset($formData.consigneeTelephone)}{$formData.consigneeTelephone|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group">
                        <label for="consigneeMobilePhoneNumber">Cellulare</label>
                        <input type="text" id="consigneeMobilePhoneNumber" name="consigneeMobilePhoneNumber" value="{if isset($formData.consigneeMobilePhoneNumber)}{$formData.consigneeMobilePhoneNumber|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group">
                        <label for="consigneeVATNumber">Partita IVA</label>
                        <input type="text" id="consigneeVATNumber" name="consigneeVATNumber" value="{if isset($formData.consigneeVATNumber)}{$formData.consigneeVATNumber|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group" style="max-width: 16rem;">
                        <label for="network">Network</label>
                        <select id="network" name="network">
                            <option value="" selected>Di default</option>
                            <option value="D">DPD</option>
                            <option value="E">Euro Express</option>
                            <option value="S">FED</option>
                        </select>
                    </div>
                    <div class="brt-form-group" style="max-width: 8rem;">
                        <label for="deliveryFreightTypeCode">Porto</label>
                        <select id="deliveryFreightTypeCode" name="deliveryFreightTypeCode">
                            <option value="DAP" selected>FRANCO</option>
                            <option value="EXW">EXW</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sezione Spedizione -->
        <div class="card">
            <div class="card-header">
                <h3>Dati Spedizione</h3>
            </div>
            <div class="card-body">
                <div class="brt-form-row">
                    <div class="brt-form-group" style="min-width: 15rem; max-width: 15rem">
                        <label for="numericSenderReference">ID numerico *</label>
                        <input type="number" step="0.01" min="0.01" id="numericSenderReference" name="numericSenderReference" required value="{if isset($formData.numericSenderReference)}{$formData.numericSenderReference|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group" style="min-width: 15rem; max-width: 15rem">
                        <label for="alphanumericSenderReference">ID alfanumerico</label>
                        <input type="text" id="alphanumericSenderReference" name="alphanumericSenderReference" value="{if isset($formData.alphanumericSenderReference)}{$formData.alphanumericSenderReference|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group" style="min-width: 8rem; max-width: auto;">
                        <label for="declaredParcelValue">Valore dichiarato (€)</label>
                        <input type="number" step="0.01" min="0" id="declaredParcelValue" name="declaredParcelValue" value="{if isset($formData.declaredParcelValue)}{$formData.declaredParcelValue|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group" style="min-width: 8rem; max-width: auto;">
                        <label for="insuranceAmount">Assicurazione (€)</label>
                        <input type="number" step="0.01" min="0" id="insuranceAmount" name="insuranceAmount" value="{if isset($formData.insuranceAmount)}{$formData.insuranceAmount|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group">
                        <label for="serviceType">Tipo Servizio *</label>
                        <select id="serviceType" name="serviceType" required>
                            <option value="">-- Seleziona --</option>
                            <option value="EXP" {if isset($formData.serviceType) && $formData.serviceType == 'EXP'}selected{/if}>Express</option>
                            <option value="PRI" {if isset($formData.serviceType) && $formData.serviceType == 'PRI'}selected{/if}>Priority</option>
                            <option value="10" {if isset($formData.serviceType) && $formData.serviceType == '10'}selected{/if}>10:00</option>
                            <option value="12" {if isset($formData.serviceType) && $formData.serviceType == '12'}selected{/if}>12:00</option>
                            <option value="SAT" {if isset($formData.serviceType) && $formData.serviceType == 'SAT'}selected{/if}>Sabato</option>
                        </select>
                    </div>
                    <div class="brt-form-group" style="flex-grow: 1; max-width: auto;">
                        <label for="notes">Note di Spedizione</label>
                        <input type="text" id="notes" name="notes">
                    </div>
                    <div class="section-measurements">
                        <div class="brt-form-row">
                            <h3>Colli</h3>
                            <table class="table table-light table-bordered" id="table-brt-measure">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Lunghezza (mm)</th>
                                        <th>Larghezza (mm)</th>
                                        <th>Altezza (mm)</th>
                                        <th>Volume (m3)</th>
                                        <th>Peso (Kg)</th>
                                        <th>Collo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- contenuto dinamico -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th>Colli</th>
                                        <th>Volume (m3)</th>
                                        <th>Peso (Kg)</th>
                                    </tr>
                                    <tr id="total-row">
                                        <th></th>
                                        <th></th>
                                        <th><strong>TOTALE</strong></th>
                                        <th><input type="text" class="form-control text-right" readonly name="numberOfParcels" id="numberOfParcels"></th>
                                        <th><input type="text" class="form-control text-right" readonly name="volumeM3" id="volumeM3"></th>
                                        <th><input type="text" class="form-control text-right" readonly name="weightKG" id="weightKG"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Sezione Opzioni -->
        <div class="card">
            <div class="card-header">
                <h3>Opzioni Avanzate</h3>
            </div>
            <div class="card-body">
                <div class="brt-form-row">
                    <div class="brt-form-group">
                        <label for="isCODMandatory">Contrassegno?</label>
                        <div class="text-center">
                            <div class="ps-switch ps-switch-lg ps-switch-nolabel ps-switch-center ps-togglable-row">
                                <input type="radio" name="isCODMandatory" id="input-false-isCODMandatory" value="0" {if $formData.isCODMandatory == 0}checked{/if}>
                                <label for="input-false-isCODMandatory">Off</label>
                                <input type="radio" name="isCODMandatory" id="input-true-isCODMandatory" value="1" {if $formData.isCODMandatory == 1}checked{/if}>
                                <label for="input-true-isCODMandatory">On</label>
                                <span class="slide-button"></span>
                            </div>
                        </div>
                    </div>
                    <div class="brt-form-group">
                        <label for="cashOnDelivery">Importo Contrassegno (€)</label>
                        <input type="number" step="0.01" min="0" id="cashOnDelivery" name="cashOnDelivery" value="{if isset($formData.cashOnDelivery)}{$formData.cashOnDelivery|escape:'html':'UTF-8'}{/if}">
                    </div>
                    <div class="brt-form-group">
                        <label for="codPaymentType">Tipo Pagamento Contrassegno</label>
                        <select id="codPaymentType" name="codPaymentType" value="{if isset($formData.codPaymentType)}{$formData.codPaymentType|escape:'html':'UTF-8'}{/if}">
                            <option value="">ACCETTARE CONTANTE</option>
                            <option value="BM">ACCETTARE ASSEGNO BANCARIO INTESTATO ALLA MITTENTE</option>
                            <option value="CM">ACCETTARE ASSEGNO CIRCOLARE INTESTATO ALLA MITTENTE</option>
                            <option value="BB">ACCETTARE ASSEGNO BANCARIO INTESTATO CORRIERE CON MANLEVA</option>
                            <option value="OM">ACCETTARE ASSEGNO INTESTATO AL MITTENTE ORIGINALE</option>
                            <option value="OC">ACCETTARE ASSEGNO CIRCOLARE INTESTATO AL MITTENTE ORIGINALE</option>
                        </select>
                    </div>
                    <div class="brt-form-group">
                        <label for="parcelsHandlingCode">Gestione colli</label>
                        <input type="text" id="parcelsHandlingCode" name="parcelsHandlingCode">
                    </div>
                    <div class="brt-form-group">
                        <label for="particularitiesDeliveryManagementCode">Particolarità consegna</label>
                        <input type="text" id="particularitiesDeliveryManagementCode" name="particularitiesDeliveryManagementCode">
                    </div>
                    <div class="brt-form-group">
                        <label for="particularitiesHoldOnStockManagementCode">Particolarità giacenza</label>
                        <input type="text" id="particularitiesHoldOnStockManagementCode" name="particularitiesHoldOnStockManagementCode">
                    </div>
                    <div class="brt-form-row" style="max-width: auto; border: 1px solid #ccc; padding: 10px;">
                        <input type="hidden" id="isAlertRequired" name="isAlertRequired" value="0">
                        <div class="brt-form-group">
                            <label>Notifica via EMAIL</label>
                            <div class="text-center">
                                <div class="ps-switch ps-switch-lg ps-switch-nolabel ps-switch-center ps-togglable-row">
                                    <input type="radio" name="notifyByEmail" id="input-false-notifyByEmail" value="0" {if $formData.notifyByEmail == 0}checked{/if}>
                                    <label for="input-false-notifyByEmail">Off</label>
                                    <input type="radio" name="notifyByEmail" id="input-true-notifyByEmail" value="1" {if $formData.notifyByEmail == 1}checked{/if}>
                                    <label for="input-true-notifyByEmail">On</label>
                                    <span class="slide-button"></span>
                                </div>
                            </div>
                        </div>
                        <div class="brt-form-group">
                            <label>Notifica via SMS</label>
                            <div class="text-center">
                                <div class="ps-switch ps-switch-lg ps-switch-nolabel ps-switch-center ps-togglable-row">
                                    <input type="radio" name="notifyBySms" id="input-false-notifyBySms" value="0" {if $formData.notifyBySms == 0}checked{/if}>
                                    <label for="input-false-notifyBySms">Off</label>
                                    <input type="radio" name="notifyBySms" id="input-true-notifyBySms" value="1" {if $formData.notifyBySms == 1}checked{/if}>
                                    <label for="input-true-notifyBySms">On</label>
                                    <span class="slide-button"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Card a scomparsa: Altri dati avanzati -->
    <div class="card" id="advancedFieldsCard" style="margin-top: 18px; display: none;">
        <div class="card-header">
            <h3>Altri dati avanzati</h3>
        </div>
        <div class="card-body">
            <div class="brt-form-row">
                <div class="brt-form-group">
                    <label for="consigneeCountryISOAlpha2">Nazione destinatario (ISO2)</label>
                    <input type="text" id="consigneeCountryISOAlpha2" name="consigneeCountryISOAlpha2" maxlength="2">
                </div>
                <div class="brt-form-group">
                    <label for="consigneeVATNumber">P.IVA destinatario</label>
                    <input type="text" id="consigneeVATNumber" name="consigneeVATNumber" maxlength="16">
                </div>
                <div class="brt-form-group">
                    <label for="consigneeItalianFiscalCode">Codice Fiscale destinatario</label>
                    <input type="text" id="consigneeItalianFiscalCode" name="consigneeItalianFiscalCode" maxlength="16">
                </div>
                <div class="brt-form-group">
                    <label for="pricingConditionCode">Codice condizione prezzo</label>
                    <input type="text" id="pricingConditionCode" name="pricingConditionCode">
                </div>
                <div class="brt-form-group">
                    <label for="insuranceAmountCurrency">Valuta assicurazione</label>
                    <input type="text" id="insuranceAmountCurrency" name="insuranceAmountCurrency" maxlength="3" value="EUR">
                </div>
                <div class="brt-form-group">
                    <label for="senderParcelType">Tipo collo mittente</label>
                    <input type="text" id="senderParcelType" name="senderParcelType">
                </div>
                <div class="brt-form-group">
                    <label for="quantityToBeInvoiced">Quantità da fatturare</label>
                    <input type="number" step="0.01" id="quantityToBeInvoiced" name="quantityToBeInvoiced">
                </div>
                <div class="brt-form-group">
                    <label for="codCurrency">Valuta contrassegno</label>
                    <input type="text" id="codCurrency" name="codCurrency" maxlength="3" value="EUR">
                </div>
                <div class="brt-form-group">
                    <label for="deliveryType">Tipo consegna</label>
                    <input type="text" id="deliveryType" name="deliveryType">
                </div>
                <div class="brt-form-group">
                    <label for="declaredParcelValueCurrency">Valuta valore dichiarato</label>
                    <input type="text" id="declaredParcelValueCurrency" name="declaredParcelValueCurrency" maxlength="3" value="EUR">
                </div>
                <div class="brt-form-group">
                    <label for="variousParticularitiesManagementCode">Codice varie particolarità</label>
                    <input type="text" id="variousParticularitiesManagementCode" name="variousParticularitiesManagementCode">
                </div>
                <div class="brt-form-group">
                    <label for="particularDelivery1">Particolarità consegna 1</label>
                    <input type="text" id="particularDelivery1" name="particularDelivery1">
                </div>
                <div class="brt-form-group">
                    <label for="particularDelivery2">Particolarità consegna 2</label>
                    <input type="text" id="particularDelivery2" name="particularDelivery2">
                </div>
                <div class="brt-form-group">
                    <label for="palletType1">Tipo pallet 1</label>
                    <input type="text" id="palletType1" name="palletType1">
                </div>
                <div class="brt-form-group">
                    <label for="palletType1Number">Numero pallet 1</label>
                    <input type="number" id="palletType1Number" name="palletType1Number">
                </div>
                <div class="brt-form-group">
                    <label for="palletType2">Tipo pallet 2</label>
                    <input type="text" id="palletType2" name="palletType2">
                </div>
                <div class="brt-form-group">
                    <label for="palletType2Number">Numero pallet 2</label>
                    <input type="number" id="palletType2Number" name="palletType2Number">
                </div>
                <div class="brt-form-group">
                    <label for="originalSenderCompanyName">Mittente originale</label>
                    <input type="text" id="originalSenderCompanyName" name="originalSenderCompanyName">
                </div>
                <div class="brt-form-group">
                    <label for="originalSenderZIPCode">CAP mittente originale</label>
                    <input type="text" id="originalSenderZIPCode" name="originalSenderZIPCode">
                </div>
                <div class="brt-form-group">
                    <label for="originalSenderCountryAbbreviationISOAlpha2">Nazione mittente originale</label>
                    <input type="text" id="originalSenderCountryAbbreviationISOAlpha2" name="originalSenderCountryAbbreviationISOAlpha2" maxlength="2">
                </div>
                <div class="brt-form-group">
                    <label for="cmrCode">Codice CMR</label>
                    <input type="text" id="cmrCode" name="cmrCode">
                </div>
                <div class="brt-form-group">
                    <label for="neighborNameMandatoryAuthorization">Autorizzazione nome vicino</label>
                    <input type="text" id="neighborNameMandatoryAuthorization" name="neighborNameMandatoryAuthorization">
                </div>
                <div class="brt-form-group">
                    <label for="pinCodeMandatoryAuthorization">Autorizzazione PIN</label>
                    <input type="text" id="pinCodeMandatoryAuthorization" name="pinCodeMandatoryAuthorization">
                </div>
                <div class="brt-form-group">
                    <label for="packingListPDFName">Nome PDF packing list</label>
                    <input type="text" id="packingListPDFName" name="packingListPDFName">
                </div>
                <div class="brt-form-group">
                    <label for="packingListPDFFlagPrint">Packing list: stampa</label>
                    <input type="checkbox" id="packingListPDFFlagPrint" name="packingListPDFFlagPrint" value="1">
                </div>
                <div class="brt-form-group">
                    <label for="packingListPDFFlagEmail">Packing list: invia via email</label>
                    <input type="checkbox" id="packingListPDFFlagEmail" name="packingListPDFFlagEmail" value="1">
                </div>
                <div class="brt-form-group">
                    <label for="consigneeClosingShift1_DayOfTheWeek">Chiusura 1 - Giorno</label>
                    <input type="text" id="consigneeClosingShift1_DayOfTheWeek" name="consigneeClosingShift1_DayOfTheWeek">
                </div>
                <div class="brt-form-group">
                    <label for="consigneeClosingShift1_PeriodOfTheDay">Chiusura 1 - Periodo</label>
                    <input type="text" id="consigneeClosingShift1_PeriodOfTheDay" name="consigneeClosingShift1_PeriodOfTheDay">
                </div>
                <div class="brt-form-group">
                    <label for="consigneeClosingShift2_DayOfTheWeek">Chiusura 2 - Giorno</label>
                    <input type="text" id="consigneeClosingShift2_DayOfTheWeek" name="consigneeClosingShift2_DayOfTheWeek">
                </div>
                <div class="brt-form-group">
                    <label for="consigneeClosingShift2_PeriodOfTheDay">Chiusura 2 - Periodo</label>
                    <input type="text" id="consigneeClosingShift2_PeriodOfTheDay" name="consigneeClosingShift2_PeriodOfTheDay">
                </div>
                <div class="brt-form-group">
                    <label for="returnDepot">Depot di ritorno</label>
                    <input type="text" id="returnDepot" name="returnDepot">
                </div>
                <div class="brt-form-group">
                    <label for="expiryDate">Data scadenza</label>
                    <input type="date" id="expiryDate" name="expiryDate">
                </div>
                <div class="brt-form-group">
                    <label for="holdForPickup">Giacenza per ritiro</label>
                    <input type="text" id="holdForPickup" name="holdForPickup">
                </div>
                <div class="brt-form-group">
                    <label for="genericReference">Riferimento generico</label>
                    <input type="text" id="genericReference" name="genericReference">
                </div>
                <div class="brt-form-group">
                    <label for="pudoId">PUDO ID</label>
                    <input type="text" id="pudoId" name="pudoId">
                </div>
                <div class="brt-form-group">
                    <label for="brtServiceCode">Codice servizio BRT</label>
                    <input type="text" id="brtServiceCode" name="brtServiceCode">
                </div>
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-outline-secondary" id="toggleAdvancedFields" style="margin: 10px 0 20px 0;">
        Mostra/Nascondi altri dati avanzati
    </button>
    <div class="brt-form-actions">
        <button type="button" class="swal2-confirm swal2-styled" style="background:#007bff;" onclick="createLabelRequest(event);">Crea Etichetta</button>
        <button type="button" class="swal2-cancel swal2-styled" style="background:#aaa;" onclick="Swal.close()">Annulla</button>
    </div>
</form>

<script type="text/javascript">
    const id_order = {$formData.id_order};
    const initialParcels = {$formData.parcels|@json_encode nofilter};
    document.addEventListener("DOMContentLoaded", function() {
        // Dispatch custom event when BRT label form is loaded
        console.log("BRT label form loaded");
        bindBrtLabelEvents();
    });
</script>



<template id="table-row">
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
</template>