# Guida pratica: Doctrine ORM in PrestaShop 8

Questa guida raccoglie i passaggi fondamentali per integrare Doctrine ORM in un modulo PrestaShop 8: dalla creazione delle entity, ai service CRUD, fino alla configurazione dei file YAML e all'uso pratico per leggere/scrivere dati.

---

## 1. Panoramica: Leggere e scrivere dati con Doctrine ORM in PrestaShop 8

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

---

## 2. Creazione di una Entity Doctrine

### Nome della classe Entity

La classe Entity **deve chiamarsi ESATTAMENTE come la tabella che esiste nel database, senza il prefisso**.
- Esempio: se la tabella è `ps_product_comment`, la classe sarà `ProductComment` (o `ProductCommentEntity` se vuoi mantenere il suffisso `Entity`).
- Le tabelle e le colonne nel database devono essere in `snake_case`.
- I nomi delle classi Entity e degli attributi devono essere in `PascalCase` (UpperCamelCase) per le classi e `camelCase` per le proprietà.

### Esempio

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

---

### Annotazioni della tabella

L'annotazione della tabella deve essere:

```php
/**
 * @ORM\Table()
 * @ORM\Entity()
 */
```

> **NON** specificare il nome della tabella nell'annotazione! PrestaShop ricava il nome della tabella dalla classe e aggiunge automaticamente il prefisso corretto (`ps_`).

- Usa sempre le annotation Doctrine in docblock (/** @ORM\... */) e specifica il nome della colonna in snake_case con l'attributo `name`.

---

## 3. Creazione di un Repository CRUD

### Repository: default e custom

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

---

## 4. Configurazione di doctrine.yml

**File:** `config/doctrine.yml`
```yaml
doctrine:
  orm:
    mappings:
      YourModule:
        is_bundle: false
        type: annotation
        dir: "%kernel.project_dir%/modules/yourmodule/src/Entity"
        prefix: 'YourModuleNamespace\Entity'
        alias: YourModule
```

## 5. Configurazione di services.yml

**File:** `config/services.yml`
```yaml
services:
  YourModuleNamespace\Services\ProductLabelService:
    arguments:
      - '@doctrine.orm.entity_manager'
```

 

## 6. Configurazione di routes.yml (solo per controller custom)

**File:** `config/routes.yml`

```yaml
yourmodule_productlabel:
  path: /admin/product-label
  methods: [GET]
  defaults:
    _controller: 'YourModuleNamespace\Controller\Admin\ProductLabelController::index'
```

 

## 7. Consigli pratici

- Usa sempre property camelCase in PHP, ma specifica `name="snake_case"` nelle annotation per compatibilità DB.
- Per query personalizzate, crea metodi nel service che usano il repository Doctrine.
- Per debug, puoi loggare `$sql` e i parametri bindati separatamente.
- In PrestaShop 8, l'autowiring funziona se registri i service in `services.yml` e usi il namespace corretto.

 

### Esempio di utilizzo nel controller

```php
public function indexAction(ProductLabelService $productLabelService)
{
    $labels = $productLabelService->findAll();
    // ...
}
```

## Riepilogo

- Nome classe = nome tabella senza prefisso, PascalCase
- Table vuota nelle annotazioni
- Usa repository di default o custom a seconda delle esigenze
- PrestaShop aggiunge il prefisso alle tabelle in automatico
- Per debug, puoi loggare `$sql` e i parametri bindati separatamente.
- In PrestaShop 8, l'autowiring funziona se registri i service in `services.yml` e usi il namespace corretto.

### Esempio di utilizzo nel controller

```php
public function indexAction(ProductCommentRepository $repo)
{
    $comments = $repo->findAll();
    // ...
}
```

### Fine guida
