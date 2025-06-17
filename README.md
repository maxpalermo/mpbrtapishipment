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

## Integrazione bilancia fiscale tramite endpoint pesatura (Symfony)

Il modulo espone un endpoint moderno e sicuro per ricevere i dati di pesatura direttamente da una bilancia fiscale o da dispositivi esterni tramite chiamata HTTP parametrizzata. Si consiglia di utilizzare questo endpoint Symfony rispetto al legacy `AutoWeight`.

**Nuovo endpoint consigliato:**
```
https://tuosito.it/module/mpbrtapishipment/get-measures
```

**Parametri accettati (GET):**
- `PECOD` (Codice prodotto)
- `PPESO` (Peso)
- `PVOLU` (Volume)
- `X` (Dimensione X in mm)
- `Y` (Dimensione Y in mm)
- `Z` (Dimensione Z in mm)
- `ID_FISCALE` (ID fiscale)
- `PFLAG` (Flag personalizzato)
- `ENVELOPE` (Busta, opzionale)
- `PTIMP` (Timestamp o parametro aggiuntivo, opzionale)

**Esempio di chiamata:**
```
https://tuosito.it/module/mpbrtapishipment/get-measures?PECOD=12345&PPESO=10.5&PVOLU=0.020&X=10&Y=20&Z=30&ID_FISCALE=IT12345678901&PFLAG=1&ENVELOPE=0&PTIMP=2025-06-17+11:25:00
```

**Risposta JSON:**
```json
{
  "success": true,
  "message": "Dati ricevuti e salvati",
  "data": {
    "numericSenderReference": "12345",
    "number": 1,
    "weight": 10.5,
    "volume": 0.02,
    "x": 10,
    "y": 20,
    "z": 30,
    "fiscalId": "IT12345678901",
    "pFlag": 1,
    "envelope": 0,
    "measureDate": "2025-06-17 11:25:00"
  },
  "id": 123
}
```

> **Nota:** L'endpoint legacy `/module/mpbrtapishipment/AutoWeight` è ancora disponibile per retrocompatibilità, ma si raccomanda di utilizzare il nuovo endpoint Symfony `/module/mpbrtapishipment/get-measures` per tutte le nuove integrazioni.

## Guida pratica: Doctrine ORM in PrestaShop 8

Questa guida raccoglie i passaggi fondamentali per integrare Doctrine ORM in un modulo PrestaShop 8: dalla creazione delle entity, ai service CRUD, fino alla configurazione dei file YAML e all'uso pratico per leggere/scrivere dati.

### 1. Panoramica: Leggere e scrivere dati con Doctrine ORM in PrestaShop 8

- **Entity**: rappresenta una tabella del database come classe PHP.
- **Service**: classe che incapsula la logica CRUD (Create, Read, Update, Delete) usando l'EntityManager Doctrine.
- **EntityManager**: oggetto fornito da Doctrine per gestire tutte le operazioni sulle entity.
- **Repository**: oggetto che permette query personalizzate sulle entity.

### Esempio di lettura e scrittura dati
```php
// Lettura
$label = $entityManager->getRepository(ProductLabel::class)->find($id);
$labels = $entityManager->getRepository(ProductLabel::class)->findAll();

// Scrittura
$label = new ProductLabel();
$label->setLabelName('Nuova Etichetta');
$entityManager->persist($label);
$entityManager->flush();

// Modifica
$label->setLabelName('Etichetta aggiornata');
$entityManager->flush();

// Cancellazione
$entityManager->remove($label);
$entityManager->flush();
```

### 2. Creazione di una Entity Doctrine

#### Nome della classe Entity
La classe Entity **deve chiamarsi ESATTAMENTE come la tabella che esiste nel database, senza il prefisso**.
- Esempio: se la tabella è `ps_product_comment`, la classe sarà `ProductComment` (o `ProductCommentEntity` se vuoi mantenere il suffisso `Entity`).
- Le tabelle e le colonne nel database devono essere in `snake_case`.
- I nomi delle classi Entity e degli attributi devono essere in `PascalCase` (UpperCamelCase) per le classi e `camelCase` per le proprietà.

#### Esempio
```php
// Tabella: ps_product_comment
namespace MyModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class ProductComment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id_product_comment")
     */
    private int $id;
    // ... altre proprietà ...
}
```

#### Annotazioni della tabella
L'annotazione della tabella deve essere:
```php
/**
 * @ORM\Table()
 * @ORM\Entity()
 */
```

> **NON** specificare il nome della tabella nell'annotazione! PrestaShop ricava il nome della tabella dalla classe e aggiunge automaticamente il prefisso corretto (`ps_`).

- Usa sempre le annotation Doctrine in docblock (/** @ORM\... */) e specifica il nome della colonna in snake_case con l'attributo `name`.

### 3. Creazione di un Repository CRUD

#### Repository: default e custom

#### Repository di default
Puoi usare il repository standard Doctrine:
```php
/** @var EntityManagerInterface $entityManager */
$entityManager = $this->container->get('doctrine.orm.entity_manager');
$productCommentRepository = $entityManager->getRepository(ProductComment::class);
```

#### Repository custom
Se vuoi aggiungere metodi personalizzati, crea una classe repository:
```php
namespace MyModule\Repository;

use Doctrine\ORM\EntityRepository;

class ProductCommentRepository extends EntityRepository
{
    // Metodi custom qui
}
```
E poi nel mapping dell'entity:
```php
/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyModule\\Repository\\ProductCommentRepository")
 */
```
Così puoi usare sia i metodi base che i tuoi custom:
```php
$productCommentRepository = $entityManager->getRepository(ProductComment::class);
$productCommentRepository->find($id);
$productCommentRepository->findByCustomCriteria(...);
```

### 4. Configurazione di doctrine.yaml

**File:** `config/doctrine.yaml`
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

### 5. Configurazione di services.yml

**File:** `config/services.yml`
```yaml
services:
  Mpbrtapishipment\Services\ModelBrtShipmentResponseService:
    arguments:
      - '@doctrine.orm.entity_manager'
```

### 6. Configurazione di routes.yml (solo per controller custom)

**File:** `config/routes.yml`
```yaml
yourmodule_productlabel:
  path: /admin/product-label
  methods: [GET]
  defaults:
    _controller: 'YourModuleNamespace\Controller\Admin\ProductLabelController::index'
```

### 7. Consigli pratici
- Usa sempre property camelCase in PHP, ma specifica `name="snake_case"` nelle annotation per compatibilità DB.
- Per query personalizzate, crea metodi nel service che usano il repository Doctrine.
- Per debug, puoi loggare `$sql` e i parametri bindati separatamente.
- In PrestaShop 8, l'autowiring funziona se registri i service in `services.yml` e usi il namespace corretto.
- Usa repository di default o custom a seconda delle esigenze
- PrestaShop aggiunge il prefisso alle tabelle in automatico

### Esempio di utilizzo nel controller
```php
public function indexAction(ProductCommentRepository $repo)
{
    $comments = $repo->findAll();
    // ...
}
```

## Utilizzo avanzato: Entity Doctrine e Service CRUD per risposte BRT

### 1. Configurazione Doctrine per il modulo
Assicurati che il file `config/doctrine.yaml` del modulo contenga la sezione sopra indicata.

### 2. Entity: BrtShipmentResponse
La classe `BrtShipmentResponse` rappresenta la tabella `ps_brt_shipment_response` e si trova in `src/Entity/BrtShipmentResponse.php`. Tutti i campi della tabella sono mappati come proprietà Doctrine con getter e setter.

### 3. Service: ModelBrtShipmentResponseService
Il service `ModelBrtShipmentResponseService` (in `src/Services/ModelBrtShipmentResponseService.php`) offre metodi CRUD per gestire le risposte BRT:
- `save(BrtShipmentResponse $entity)` — Salva o aggiorna una risposta
- `find(int $id)` — Trova una risposta per ID
- `findAll()` — Elenca tutte le risposte
- `remove(BrtShipmentResponse $entity)` — Elimina una risposta
- `findBy(array $criteria)` — Ricerca avanzata

### Esempio di utilizzo in un controller Symfony/PrestaShop
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

#### Esempio di utilizzo in un controller Symfony/PrestaShop:
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

## Supporto
Per assistenza tecnica o richieste di nuove funzionalità, contatta:
- **Autore:** Massimiliano Palermo <maxx.palermo@gmail.com>

- Il metodo `saveLabels` aggiorna la label se esiste già (stesso `numeric_sender_reference` e `number`), oppure la crea se non esiste.
- Puoi usare questi oggetti anche in Command, Controller, FormHandler o altri servizi custom.
- Tutti i metodi sono type-safe e pronti per l'uso in ambiente PrestaShop 8 con Symfony 4/5.
