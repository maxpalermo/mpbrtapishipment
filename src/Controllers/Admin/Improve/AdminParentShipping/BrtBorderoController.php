<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace MpSoft\MpBrtApiShipment\Controllers\Admin\Improve\AdminParentShipping;

require_once _PS_MODULE_DIR_.'/mpbrtapishipment/vendor/autoload.php';

use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentBordero;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponse;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponseLabel;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller per la stampa etichetta BRT da action della grid ordini.
 */
class BrtBorderoController extends FrameworkBundleAdminController
{
    public function showLastPendingBorderoAction()
    {
        $bordero = ModelBrtShipmentBordero::compileBordero();

        return $this->render('@Modules/mpbrtapishipment/views/templates/Controllers/Admin/Bordero.html.twig', [
            'bordero' => $bordero,
        ]);
    }

    /**
     * Stampa etichetta BRT per ordine
     * Rotta: /mpbrtapishipment/printlabel/{id_bordero}.
     *
     * @return Response
     */
    public function printLabelAction(Request $request, $id_bordero = null)
    {
        if (null === $id_bordero) {
            $id_bordero = (int) \Tools::getValue('id');
        }

        if ($id_bordero > 0) {
            // Recupera i dati del borderò
            $bordero = new ModelBrtShipmentBordero($id_bordero);
            if (!\Validate::isLoadedObject($bordero)) {
                $this->addFlash('error', $this->trans('Riga borderò non trovata', 'Modules.MpBrtApiShipment.Admin', []));

                return $this->redirectToRoute('admin_brt_shipping_bordero');
            }

            $id_response = $bordero->id_brt_shipment_response;
            $modelResponse = new ModelBrtShipmentResponse($id_response);

            if (!\Validate::isLoadedObject($modelResponse)) {
                $this->addFlash('error', $this->trans('Response segnacollo non trovato!', 'Modules.MpBrtApiShipment.Admin', []));

                return $this->redirectToRoute('admin_brt_shipping_bordero');
            }

            $pdf = ModelBrtShipmentResponseLabel::createLabelPdf($id_response);

            if ($pdf) {
                // Apre il PDF in una nuova pagina
                header('Content-type: application/pdf');
                header('Content-Disposition: inline; filename="bordero_'.$bordero->bordero_number.'.pdf"');
                echo $pdf;
                exit;
            }
        }

        $this->addFlash('error', $this->trans('Impossibile stampare il borderò', 'Modules.MpBrtApiShipment.Admin', []));

        return $this->redirectToRoute('admin_brt_shipping_bordero');
    }

    public function getLabelLinkAction(Request $request, $numericSenderReference)
    {
        $order = new \Order($numericSenderReference);
        if (!\Validate::isLoadedObject($order)) {
            $this->addFlash('error', $this->trans('Ordine non trovato', 'Modules.MpBrtApiShipment.Admin', []));

            return $this->redirectToRoute('admin_orders');
        }

        $shipmentResponse = ModelBrtShipmentResponse::getByNumericSenderReference($numericSenderReference);
        if (!\Validate::isLoadedObject($shipmentResponse)) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->trans('Response segnacollo non trovato!', 'Modules.MpBrtApiShipment.Admin', []),
            ]);
        }

        return new JsonResponse([
            'success' => true,
        ]);
    }
}
