<?php

namespace MpSoft\MpBrtApiShipment\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use MpSoft\MpBrtApiShipment\Entity\BrtShipmentRequest;

class BrtShipmentRequestRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function save(BrtShipmentRequest $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?BrtShipmentRequest
    {
        return $this->entityManager->getRepository(BrtShipmentRequest::class)->findOneBy($criteria, $orderBy);
    }

    public function find(int $id): ?BrtShipmentRequest
    {
        return $this->entityManager->getRepository(BrtShipmentRequest::class)->find($id);
    }

    public function findAll(): array
    {
        return $this->entityManager->getRepository(BrtShipmentRequest::class)->findAll();
    }

    public function remove(BrtShipmentRequest $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    public function findBy(array $criteria): array
    {
        return $this->entityManager->getRepository(BrtShipmentRequest::class)->findBy($criteria);
    }
}
