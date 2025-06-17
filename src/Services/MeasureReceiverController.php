<?php

namespace Mpbrtapishipment\Services;

use Doctrine\ORM\EntityManagerInterface;
use MpSoft\MpBrtApiShipment\Entity\BrtShipmentResponseLabel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MeasureReceiverController extends AbstractController
{
    /**
     * @Route("/module/mpbrtapishipment/get-measures", name="mpbrtapishipment_get_measures", methods={"GET"})
     */
    public function getMeasures(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = [
            'numericSenderReference' => $request->query->get('PECOD'),
            'number' => 1, // oppure altro valore logico
            'weight' => (float) $request->query->get('PPESO'),
            'volume' => (float) $request->query->get('PVOLU'),
            'x' => (int) $request->query->get('X'),
            'y' => (int) $request->query->get('Y'),
            'z' => (int) $request->query->get('Z'),
            'fiscalId' => $request->query->get('ID_FISCALE'),
            'pFlag' => $request->query->get('PFLAG'),
            'envelope' => $request->query->get('ENVELOPE'),
            'measureDate' => $request->query->get('PTIMP', date('Y-m-d H:i:s')),
        ];

        try {
            $label = new BrtShipmentResponseLabel();
            $label
                ->setNumericSenderReference($data['numericSenderReference'])
                ->setNumber($data['number'])
                ->setWeight($data['weight'])
                ->setVolume($data['volume'])
                ->setX($data['x'])
                ->setY($data['y'])
                ->setZ($data['z'])
                ->setFiscalId($data['fiscalId'] ?? null)
                ->setpFlag($data['pFlag'] ?? null);
            $entityManager->persist($label);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Dati ricevuti e salvati',
                'data' => $data,
                'id' => $label->getId(),
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Errore salvataggio: '.$e->getMessage(),
            ], 500);
        }
    }
}
