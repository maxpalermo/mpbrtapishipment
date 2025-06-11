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

| Variabile                        | Descrizione                                                        |
|----------------------------------|--------------------------------------------------------------------|
| `BRT_ENVIRONMENT`                | Ambiente di lavoro: `SANDBOX` o `PRODUCTION`                       |
| `BRT_SANDBOX_USER_ID`            | User ID ambiente Sandbox                                           |
| `BRT_SANDBOX_PASSWORD`           | Password ambiente Sandbox                                          |
| `BRT_SANDBOX_DEPARTURE_DEPOT`    | Deposito di partenza Sandbox                                      |
| `BRT_PRODUCTION_USER_ID`         | User ID ambiente Produzione                                        |
| `BRT_PRODUCTION_PASSWORD`        | Password ambiente Produzione                                       |
| `BRT_PRODUCTION_DEPARTURE_DEPOT` | Deposito di partenza Produzione                                   |
| `BRT_SERVICE_TYPE`               | Tipo di servizio BRT (es. `DEF`, `E`, `H`)                         |
| `BRT_NETWORK`                    | Network BRT (es. `D`, `E`, `F`)                                    |
| `BRT_DELIVERY_FREIGHT_TYPE_CODE` | Porto: `1` FRANCO, `2` ASSEGNATO, ecc.                             |
| `BRT_COD_PAYMENT_TYPE`           | Tipo pagamento contrassegno (es. `DEF`, `BM`, `CM`, `BB`, `OM`, `OC`) |
| `BRT_IS_ALERT_REQUIRED`          | Invio avvisi: `0` NO, `1` SI                                       |
| `BRT_IS_LABEL_PRINTED`           | Stampa etichetta: `0` NO, `1` SI                                   |
| `BRT_LABEL_TYPE`                 | Tipo stampa etichetta: `PDF` o `ZPL`                               |
| `BRT_IS_BORDER_PRINTED`          | Stampa bordi: `0` NO, `1` SI                                       |
| `BRT_IS_BARCODE_PRINTED`         | Stampa barcode: `0` NO, `1` SI                                     |
| `BRT_IS_LOGO_PRINTED`            | Stampa logo: `0` NO, `1` SI                                        |
| `BRT_LABEL_OFFSET_X`             | Offset X etichetta in mm                                           |
| `BRT_LABEL_OFFSET_Y`             | Offset Y etichetta in mm                                           |

> **Nota:** Alcuni parametri potrebbero essere richiesti solo per specifiche funzioni o servizi BRT.

## Utilizzo
- Accedi alla pagina "Borderò" dal menu amministrazione.
- Utilizza le azioni rapide per generare nuove etichette, stampare, caricare dati o modificare le preferenze.
- Per modificare le impostazioni, clicca su "Preferenze" e salva i parametri desiderati.

## Supporto
Per assistenza tecnica o richieste di nuove funzionalità, contatta:
- **Autore:** Massimiliano Palermo <maxx.palermo@gmail.com>

## Licenza
Questo modulo è distribuito sotto licenza [AFL-3.0](https://opensource.org/licenses/AFL-3.0).
