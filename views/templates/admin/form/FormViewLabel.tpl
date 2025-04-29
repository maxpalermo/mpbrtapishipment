{* modules/mpbrtapishipment/views/templates/admin/form/FormViewLabel.tpl *}
<style>
    .brt-label-view-container {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        background: #fff;
        border-radius: 12px;
        padding: 32px 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        margin-bottom: 24px;
    }

    .brt-label-group {
        flex: 1 1 320px;
        min-width: 300px;
        max-width: 480px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 12px;
    }

    .brt-label-field {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 0;
        border-bottom: 1px solid #f2f2f2;
    }

    .brt-label-label {
        font-weight: 600;
        color: #333;
        min-width: 170px;
        flex-shrink: 0;
    }

    .brt-label-value {
        color: #222;
        word-break: break-all;
        font-family: monospace;
    }

    @media (max-width: 900px) {
        .brt-label-view-container {
            flex-direction: column;
        }

        .brt-label-group {
            max-width: 100%;
        }
    }
</style>

<div class="brt-label-view-container">
    {* Gruppo 1: Identificativi e riferimenti *}
    <div class="brt-label-group">
        <div class="brt-label-field"><span class="brt-label-label">ID Response:</span> <span class="brt-label-value">{$response.id_brt_shipment_response}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Riferimento numerico:</span> <span class="brt-label-value">{$response.numeric_sender_reference}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Riferimento alfanumerico:</span> <span class="brt-label-value">{$response.alphanumeric_sender_reference}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Data/ora (UTC):</span> <span class="brt-label-value">{$response.current_time_utc}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Borderò:</span> <span class="brt-label-value">{$response.series_number}</span></div>
    </div>
    {* Gruppo 2: Destinatario *}
    <div class="brt-label-group">
        <div class="brt-label-field"><span class="brt-label-label">Azienda destinatario:</span> <span class="brt-label-value">{$response.consignee_company_name}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Indirizzo:</span> <span class="brt-label-value">{$response.consignee_address}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Città:</span> <span class="brt-label-value">{$response.consignee_city}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">CAP:</span> <span class="brt-label-value">{$response.consignee_zip_code}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Provincia:</span> <span class="brt-label-value">{$response.consignee_province_abbreviation}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Nazione:</span> <span class="brt-label-value">{$response.consignee_country_abbreviation_brt}</span></div>
    </div>
    {* Gruppo 3: Dettagli spedizione *}
    <div class="brt-label-group">
        <div class="brt-label-field"><span class="brt-label-label">Terminal arrivo:</span> <span class="brt-label-value">{$response.arrival_terminal}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Deposito arrivo:</span> <span class="brt-label-value">{$response.arrival_depot}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Deposito partenza:</span> <span class="brt-label-value">{$response.departure_depot}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Zona consegna:</span> <span class="brt-label-value">{$response.delivery_zone}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Tipo servizio:</span> <span class="brt-label-value">{$response.service_type}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Azienda mittente:</span> <span class="brt-label-value">{$response.sender_company_name}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Provincia mittente:</span> <span class="brt-label-value">{$response.sender_province_abbreviation}</span></div>
    </div>
    {* Gruppo 4: Dati colli e importi *}
    <div class="brt-label-group">
        <div class="brt-label-field"><span class="brt-label-label">Numero colli:</span> <span class="brt-label-value">{$response.number_of_parcels}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Peso (kg):</span> <span class="brt-label-value">{$response.weight_kg}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Volume (m³):</span> <span class="brt-label-value">{$response.volume_m3}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Contrassegno:</span> <span class="brt-label-value">{$response.cash_on_delivery}</span></div>
    </div>
    {* Gruppo 5: Altro *}
    <div class="brt-label-group" style="max-width: 800px;">
        <div class="brt-label-field"><span class="brt-label-label">Messaggio esecuzione:</span> <span class="brt-label-value">
                {if isset($response.execution_message) && !empty($response.execution_message)}
                    {if $response.execution_message.code == 0}
                        {assign var="alert_type" value="info"}
                    {elseif $response.execution_message.code > 0}
                        {assign var="alert_type" value="warning"}
                    {else}
                        {assign var="alert_type" value="danger"}
                    {/if}
                    <div class="alert alert-{$alert_type}">
                        <h4>Codice: {$response.execution_message.code}</h4>
                        <h4 class="title">{$response.execution_message.codeDesc}</h4>
                        <span>{$response.execution_message.message}</span>
                    </div>
                {/if}
            </span>
        </div>
    </div>
    {* Gruppo 6: Colli *}
    <div class="brt-label-group">
        <div class="brt-label-field"><span class="brt-label-label">Numeri colli da:</span> <span class="brt-label-value">{$response.parcel_number_from}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Numeri colli a:</span> <span class="brt-label-value">{$response.parcel_number_to}</span></div>
        <div class="brt-label-field"><span class="brt-label-label">Disclaimer:</span> <span class="brt-label-value">{$response.disclaimer}</span></div>
    </div>
</div>
<div class="button-container text-center">
    <a href="{Context::getContext()->link->getAdminLink('AdminBrtShippingBordero')}" class="btn btn-primary"><i class="material-icons">arrow_back</i>Indietro</a>
</div>