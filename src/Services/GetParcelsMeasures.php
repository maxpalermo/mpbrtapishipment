<?php

namespace MpSoft\MpBrtApiShipment\Services;

use Doctrine\DBAL\Connection;

final class GetParcelsMeasures
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function run($numericSenderReference): array
    {
        $prefix = _DB_PREFIX_;
        $query = "SELECT * FROM {$prefix}brt_shipment_response_label WHERE numeric_sender_reference = :numericSenderReference";

        $stmt = $this->connection->prepare($query);
        $stmt->bindValue('numericSenderReference', $numericSenderReference);
        $result = $stmt->executeQuery();
        $rows = $result->fetchAllAssociative();

        return [
            'success' => true,
            'parcels' => $rows,
        ];
    }
}
