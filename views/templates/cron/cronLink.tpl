<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-cogs"></i>
        <span>{l s='Misure automatiche tramite Bilancia'}</span>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="cron_link">{l s='Link Cron'}</label>
                    <div class="input-group">
                        <input type="text" id="cron_link" class="form-control" value="{$cron_link}" readonly>
                        <span class="input-group-addon">
                            <span onclick="copyToClipboard('cron_link')">
                                <i class="icon icon-copy"></i>
                            </span>
                        </span>
                    </div>
                    <div class="legend">
                        <p><strong><i>{l s='Imposta questo link nella tua bilancia elettronica e usalo per inviare le misure automatiche all\'ordine' mod='mpbrtapishipment'}</i></strong></p>
                        <h4>{l s='Parametri per la chiamata CRON' mod='mpbrtapishipment'}</h4>
                        <ul class="legend">
                            <li><strong>PECOD</strong>: Codice etichetta</li>
                            <li><strong>PPESO</strong>: Peso in kg</li>
                            <li><strong>PVOLU</strong>: Volume in m3 (opzionale)</li>
                            <li><strong>X</strong>: Misura X in mm</li>
                            <li><strong>Y</strong>: Misura Y in mm</li>
                            <li><strong>Z</strong>: Misura Z in mm</li>
                            <li><strong>ID_FISCALE</strong>: ID Fiscale peso bilancia (opzionale)</li>
                            <li><strong>PFLAG</strong>: PFLAG (opzionale)</li>
                            <li><strong>ENVELOPE</strong>: Il pacco è una busta (opzionale)</li>
                            <li><strong>PTIMP</strong>: Data della pesatura nel formato YYYY-MM-DD+HH:MM:SS (opzionale)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        function copyToClipboard(elementId) {
            var copyText = document.getElementById(elementId);
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");

            Swal.fire({
                icon: 'success',
                title: '{l s="Copiato"}',
                text: '{l s="Il link è stato copiato negli appunti"}',
                showConfirmButton: false,
                timer: 1500
            });
        }
    </script>
</div>