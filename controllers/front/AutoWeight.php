<?php

/*
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

use Doctrine\ORM\EntityManagerInterface;
use MpSoft\MpBrtApiShipment\Entity\BrtShipmentResponseLabel;

class MpBrtApiShipmentAutoWeightModuleFrontController extends ModuleFrontController
{
    protected $name;

    public function __construct()
    {
        $this->auth = false;
        $this->guestAllowed = false;
        $this->maintenance = false;
        $this->ssl = (int) Configuration::get('PS_SSL_ENABLED');
        $this->ajax = Tools::getValue('ajax', 0);

        parent::__construct();

        $this->name = 'AutoWeight';
    }

    public function display()
    {
        $action = Tools::getValue('action');
        if ('insert' == $action) {
            $measure = $this->getMeasure();
            $insert = $this->insertMeasure($measure);
        }

        exit('NOT ALLOWED');
    }

    protected function getMeasure()
    {
        $id = Tools::getValue('PECOD');
        $parts = explode('-', $id);
        $numericSenderReference = (int) $parts[0];
        $number = (int) $parts[1];
        $params = [
            'numericSenderReference' => $numericSenderReference,
            'number' => $number,
            'weight' => (float) Tools::getValue('PPESO'),
            'volume' => (float) Tools::getValue('PVOLU'),
            'x' => (int) Tools::getValue('X'),
            'y' => (int) Tools::getValue('Y'),
            'z' => (int) Tools::getValue('Z'),
            'fiscalId' => Tools::getValue('ID_FISCALE'),
            'isRead' => Tools::getValue('PFLAG'),
            'isEnvelope' => Tools::getValue('ENVELOPE'),
            'measureDate' => Tools::getValue('PTIMP', date('Y-m-d H:i:s')),
        ];

        return $params;
    }

    protected function insertMeasure($measure)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        // Create product comment
        $label = new BrtShipmentResponseLabel();
        $label
            ->setNumericSenderReference($measure['numericSenderReference'])
            ->setNumber($measure['number'])
            ->setWeight($measure['weight'])
            ->setVolume($measure['volume'])
            ->setX($measure['x'])
            ->setY($measure['y'])
            ->setZ($measure['z'])
            ->setFiscalId($measure['fiscalId'])
            ->setpFlag($measure['pFlag'])
        ;
        // This call adds the entity to the EntityManager scope (now it knows the entity exists)
        $entityManager->persist($label);

        // This call validates all previous modification (modified/persisted entities)
        // This is when the database queries are performed
        $entityManager->flush();

        $this->ajaxRender(json_encode([
            'success' => true,
            'label' => $label->toArray(),
        ]));
    }
}
