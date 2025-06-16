<?php

namespace MpSoft\MpBrtApiShipment\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use MpSoft\MpBrtApiShipment\Entity\BrtShipmentResponseLabel;

class BrtShipmentResponseLabelRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Inserisce o aggiorna le label associate a una spedizione.
     */
    public function save(?int $numericSenderReference, array $labels): array
    {
        /** @var \Doctrine\ORM\EntityRepository */
        $repository = $this->entityManager->getRepository(BrtShipmentResponseLabel::class);
        $i = 1;
        foreach ($labels as $labelData) {
            $persist = false;
            $entity = $repository->findOneBy(
                [
                    'numericSenderReference' => $numericSenderReference,
                    'number' => $i,
                ],
            );
            if (!$entity) {
                $entity = new BrtShipmentResponseLabel();
                $persist = true;
            }

            $label = $this->entityManager->getRepository(BrtShipmentResponseLabel::class)->findOneBy([
                'numericSenderReference' => $numericSenderReference,
                'number' => $i,
            ]);
            if (!$label) {
                $label = $entity;
                $label->setNumericSenderReference($numericSenderReference);
                $label->setNumber($labelData['number']);
            }
            $label->setAlphanumericSenderReference($labelData['alphanumericSenderReference'] ?? null);
            $label->setIdBrtShipmentResponse($numericSenderReference);
            $label->setNumber($i);
            $label->setDataLength((int) ($labelData['dataLength'] ?? 0));
            $label->setParcelId($labelData['parcelID'] ?? null);
            $label->setStream($labelData['stream'] ?? null);
            $label->setStreamDigitalLabel($labelData['streamDigitalLabel'] ?? null);
            $label->setParcelNumberGeoPost($labelData['parcelNumberGeoPost'] ?? null);
            $label->setTrackingByParcelId($labelData['trackingByParcelID'] ?? null);
            $label->setFormat($labelData['format'] ?? null);
            if ($persist) {
                $this->entityManager->persist($label);
            }
            ++$i;
        }
        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => 'Label salvata con successo',
        ];
    }

    public function saveMeasures(array $measureData): array
    {
        $persist = false;
        /** @var \Doctrine\ORM\EntityRepository */
        $repository = $this->entityManager->getRepository(BrtShipmentResponseLabel::class);
        $measure = $repository->findOneBy([
            'numericSenderReference' => $measureData['numeric_sender_reference'],
            'number' => $measureData['number'],
        ]);
        if (!$measure) {
            $measure = new BrtShipmentResponseLabel();
            $measure->setNumericSenderReference($measureData['numeric_sender_reference']);
            $measure->setNumber($measureData['number']);
            $persist = true;
        }
        $measure->setX($measureData['x'] ?? 0);
        $measure->setY($measureData['y'] ?? 0);
        $measure->setZ($measureData['z'] ?? 0);
        $measure->setWeight(isset($measureData['weight']) ? (float) $measureData['weight'] : 1.0);
        $measure->setVolume(isset($measureData['volume']) ? (float) $measureData['volume'] : 0.000);
        $measure->setFiscalId($measureData['fiscalId'] ?? null);
        $measure->setPFlag(isset($measureData['pFlag']) ? (bool) $measureData['pFlag'] : null);
        if ($persist) {
            $this->entityManager->persist($measure);
        }

        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => 'Misurazione salvata con successo',
        ];
    }

    /** CRUD base: find, findAll, remove **/
    public function find(int $id): ?BrtShipmentResponseLabel
    {
        return $this->entityManager->getRepository(BrtShipmentResponseLabel::class)->find($id);
    }

    public function findAll(): array
    {
        return $this->entityManager->getRepository(BrtShipmentResponseLabel::class)->findAll();
    }

    public function remove(BrtShipmentResponseLabel $label): void
    {
        $this->entityManager->remove($label);
        $this->entityManager->flush();
    }
}
