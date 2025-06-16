# mpbrtapishipment

Modulo PrestaShop per l'integrazione delle spedizioni tramite API BRT (Bartolini).

## Descrizione
Questo modulo consente di generare etichette di spedizione, gestire borderò, stampare etichette e integrare tutte le funzionalità principali offerte dalle API BRT direttamente dal back office di PrestaShop.

## Funzionalità principali
- Generazione e stampa etichette BRT
- Gestione borderò e storico spedizioni
- Supporto per ambiente Sandbox e Produzione
- Configurazione avanzata parametri BRT
- Gestione permessi utenti

## Installazione
1. Copia la cartella `mpbrtapishipment` nella directory `modules` di PrestaShop.
2. Installa il modulo dal back office di PrestaShop.
3. Accedi alla pagina di configurazione per impostare i parametri BRT.

## Configurazione
Nella sezione "Preferenze" del modulo puoi configurare i seguenti parametri:

| Variabile                                 | Descrizione                                                        |
|---------------------------------------------|--------------------------------------------------------------------|
| `BRT_ENVIRONMENT`                          | Ambiente di lavoro: `SANDBOX` o `PRODUCTION`                       |
| `BRT_SANDBOX_USER_ID`                      | User ID ambiente Sandbox                                           |
| `BRT_SANDBOX_PASSWORD`                     | Password ambiente Sandbox                                          |
| `BRT_SANDBOX_DEPARTURE_DEPOT`              | Deposito di partenza Sandbox                                      |
| `BRT_PRODUCTION_USER_ID`                   | User ID ambiente Produzione                                        |
| `BRT_PRODUCTION_PASSWORD`                  | Password ambiente Produzione                                       |
| `BRT_PRODUCTION_DEPARTURE_DEPOT`           | Deposito di partenza Produzione                                   |
| `BRT_SERVICE_TYPE`                         | Tipo di servizio BRT (es. `DEF`, `E`, `H`)                         |
| `BRT_NETWORK`                              | Network BRT (es. `D`, `E`, `F`)                                    |
| `BRT_DELIVERY_FREIGHT_TYPE_CODE`           | Porto: `1` FRANCO, `2` ASSEGNATO, ecc.                             |
| `BRT_COD_PAYMENT_TYPE`                     | Tipo pagamento contrassegno (es. `DEF`, `BM`, `CM`, `BB`, `OM`, `OC`) |
| `BRT_IS_ALERT_REQUIRED`                    | Invio avvisi: `0` NO, `1` SI                                       |
| `BRT_IS_LABEL_REQUIRED`                    | Stampa etichetta: `0` NO, `1` SI                                   |
| `BRT_LABEL_OUTPUT_TYPE`                    | Tipo stampa etichetta: `PDF` o `ZPL`                               |
| `BRT_LABEL_IS_BORDER_REQUIRED`             | Stampa bordi: `0` NO, `1` SI                                       |
| `BRT_LABEL_IS_BARCODE_CONTROL_ROW_REQUIRED`| Stampa riga controllo barcode: `0` NO, `1` SI                      |
| `BRT_LABEL_IS_LOGO_REQUIRED`               | Stampa logo: `0` NO, `1` SI                                        |
| `BRT_LABEL_FORMAT`                         | Formato etichetta: `DEF` (95x65), `DP5` (100x150 ZPL), `DPH` (100x150 Ibrido) |
| `BRT_LABEL_OFFSET_X`                       | Offset X etichetta in mm                                           |
| `BRT_LABEL_OFFSET_Y`                       | Offset Y etichetta in mm                                           |
| `BRT_ORDER_STATE_CHANGE`                   | ID stato ordine da impostare dopo la creazione dell'etichetta       |
| `BRT_SENDER_CUSTOMER_CODE`                 | Codice mittente                                                    |
| `BRT_PRICING_CONDITION_CODE`               | Codice condizioni tariffarie                                       |


> **Nota:** Alcuni parametri potrebbero essere richiesti solo per specifiche funzioni o servizi BRT.

## Utilizzo
- Accedi alla pagina "Borderò" dal menu amministrazione.
- Utilizza le azioni rapide per generare nuove etichette, stampare, caricare dati o modificare le preferenze.
- Per modificare le impostazioni, clicca su "Preferenze" e salva i parametri desiderati.

### Nuova funzionalità: Integrazione bilancia fiscale tramite endpoint AutoWeight

Il modulo espone un endpoint dedicato per ricevere i dati di pesatura direttamente da una bilancia fiscale o da dispositivi esterni tramite chiamata HTTP parametrizzata.

**Endpoint:**
```
https://tuosito.it/module/mpbrtapishipment/AutoWeight
```

**Parametri accettati:**
- `PECOD` (Codice prodotto)
- `PPESO` (Peso)
- `PVOLU` (Volume)
- `X` (Dimensione X in mm)
- `Y` (Dimensione Y in mm)
- `Z` (Dimensione Z in mm)
- `ID_FISCALE` (ID fiscale)
- `PFLAG` (Flag personalizzato)
- `PTIMP` (Parametro aggiuntivo)

**Esempio di chiamata:**
```
https://tuosito.it/module/mpbrtapishipment/AutoWeight?PECOD=12345&PPESO=10.5&PVOLU=0.020&X=10&Y=20&Z=30&ID_FISCALE=IT12345678901&PFLAG=1&PTIMP=0
```

L'endpoint restituisce una risposta JSON con conferma di ricezione dei dati. Puoi personalizzare la logica di gestione dei dati nel controller `AutoWeightController.php` del modulo.

## Supporto
Per assistenza tecnica o richieste di nuove funzionalità, contatta:
- **Autore:** Massimiliano Palermo <maxx.palermo@gmail.com>

## Licenza
Questo modulo è distribuito sotto licenza [AFL-3.0](https://opensource.org/licenses/AFL-3.0).

---

## Utilizzo avanzato: Entity Doctrine e Service CRUD per risposte BRT

### 1. Configurazione Doctrine per il modulo

Assicurati che il file `config/doctrine.yaml` del modulo contenga:

```yaml
doctrine:
  orm:
    mappings:
      Mpbrtapishipment:
        is_bundle: false
        type: attribute
        dir: '%kernel.project_dir%/modules/mpbrtapishipment/src/Entity'
        prefix: 'Mpbrtapishipment\\Entity'
        alias: Mpbrtapishipment
```

### 2. Entity: BrtShipmentResponse

La classe `BrtShipmentResponse` rappresenta la tabella `ps_brt_shipment_response` e si trova in `src/Entity/BrtShipmentResponse.php`. Tutti i campi della tabella sono mappati come proprietà Doctrine con getter e setter.

### 3. Service: ModelBrtShipmentResponseService

Il service `ModelBrtShipmentResponseService` (in `src/Services/ModelBrtShipmentResponseService.php`) offre metodi CRUD per gestire le risposte BRT:
- `save(BrtShipmentResponse $entity)` — Salva o aggiorna una risposta
- `find(int $id)` — Trova una risposta per ID
- `findAll()` — Elenca tutte le risposte
- `remove(BrtShipmentResponse $entity)` — Elimina una risposta
- `findBy(array $criteria)` — Ricerca avanzata

#### Esempio di utilizzo in un controller Symfony/PrestaShop

```php
use Mpbrtapishipment\Entity\BrtShipmentResponse;
use Mpbrtapishipment\Services\ModelBrtShipmentResponseService;

public function someAction(ModelBrtShipmentResponseService $service)
{
    // Creazione
    $entity = new BrtShipmentResponse();
    $entity->setConsigneeCompanyName('Azienda Srl')
           ->setConsigneeEmail('info@azienda.it')
           ->setDateAdd(new \DateTime())
           ->setDateUpd(new \DateTime());
    $service->save($entity);

    // Lettura
    $result = $service->find($entity->getId());

    // Modifica
    $result->setPrinted(true);
    $service->save($result);

    // Ricerca
    $list = $service->findBy(['consigneeCompanyName' => 'Azienda Srl']);

    // Eliminazione
    $service->remove($result);
}
```

### 4. Creazione automatica della tabella
Se hai accesso alla console Symfony, puoi generare la tabella con:

```
php bin/console doctrine:schema:update --force
```

Oppure, crea la tabella manualmente con la query SQL fornita all'inizio di questa documentazione.

### 5. Gestione etichette (Label) con Doctrine

#### Entity: ModelBrtShipmentResponseLabel
La classe `ModelBrtShipmentResponseLabel` rappresenta la tabella delle label di spedizione e si trova in `src/Entity/ModelBrtShipmentResponseLabel.php`. **Nota che non è necessario inserire il prefisso (es. `ps_`) nel nome della tabella nell'entity**, poiché il TablePrefixSubscriber Doctrine si occupa di aggiungerlo dinamicamente.

```php
#[ORM\Table(name: 'brt_shipment_response_label')]
```

Il subscriber aggiunge il prefisso corretto a runtime, quindi non è necessario specificarlo manualmente. Tutti i campi della tabella sono mappati come proprietà Doctrine con getter e setter. La relazione con la spedizione (`id_brt_shipment_response`) è gestita tramite ManyToOne.

#### Service: BrtShipmentResponseService
Il service `BrtShipmentResponseService` (in `src/Services/BrtShipmentResponseService.php`) offre:
- Metodo `saveLabels($idBrtShipmentResponse, $labels)` per inserire/aggiornare in batch un array di etichette (ad esempio dalla risposta Bartolini).
- CRUD base: `find`, `findAll`, `remove`.

**Esempio di utilizzo in un controller Symfony/PrestaShop:**

```php
use MpSoft\MpBrtApiShipment\Services\BrtShipmentResponseService;

public function saveLabelsAction(BrtShipmentResponseService $labelService)
{
    $idBrtShipmentResponse = 123; // ID della spedizione principale
    $labels = [
        [
            'numericSenderReference' => 'ABC123',
            'alphanumericSenderReference' => 'ORD001',
            'number' => 1,
            'x' => 10,
            'y' => 20,
            'z' => 30,
            'weight' => 2.5,
            'volume' => 0.012,
            'fiscalId' => 'IT12345678901',
            'pFlag' => true,
            'dataLength' => 1024,
            'parcelID' => 'PCK001',
            'stream' => '...',
            'streamDigitalLabel' => null,
            'parcelNumberGeoPost' => 'GEO123',
            'trackingByParcelID' => 'TRK123',
            'format' => 'PDF',
        ],
        // ... altre label
    ];
    $labelService->saveLabels($idBrtShipmentResponse, $labels);
}
```

**CRUD di base sulle label:**
```php
// Lettura di una label
$label = $labelService->find($id);
// Tutte le label
$allLabels = $labelService->findAll();
// Eliminazione
$labelService->remove($label);
```

- Il metodo `saveLabels` aggiorna la label se esiste già (stesso `numeric_sender_reference` e `number`), oppure la crea se non esiste.
- Puoi usare questi oggetti anche in Command, Controller, FormHandler o altri servizi custom.
- Tutti i metodi sono type-safe e pronti per l'uso in ambiente PrestaShop 8 con Symfony 4/5.
