{*
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
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
 *}
<table cellborder="0" cellspacing="0" style="width: 12cm; border: 1px solid #404040;">
    <thead>
        <tr>
            <th colspan="3" style="border-bottom: 1px solid #404040;"><strong>RIEPILOGO</strong><br></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="width: 8cm;">TOTALE SPEDIZIONI:</td>
            <td style="width: 2cm; text-align: right;"><strong>{$totali.spedizioni}</strong></td>
            <td style="width: 2cm;">SPED</td>
        </tr>
        <tr>
            <td style="width: 8cm;">TOTALE COLLI:</td>
            <td style="width: 2cm; text-align: right;"><strong>{$totali.colli}</strong></td>
            <td style="width: 2cm;">COLLI</td>
        </tr>
        <tr>
            <td style="width: 8cm;">TOTALE CONTRASSEGNI:</td>
            <td style="width: 2cm; text-align: right;"><strong>{$totali.numcass}</strong></td>
            <td style="width: 2cm;">ORDINI</td>
        </tr>
        <tr>
            <td style="width: 8cm;">IMPORTO CONTRASSEGNI:</td>
            <td style="width: 2cm; text-align: right;"><strong>{$totali.cashOnDelivery|number_format:2:",":" "}</strong></td>
            <td style="width: 2cm;">EUR</td>
        </tr>
        <tr>
            <td style="width: 8cm;">TOTALE PESO:</td>
            <td style="width: 2cm; text-align: right;"><strong>{$totali.weightKg|number_format:2:",":" "}</strong></td>
            <td style="width: 2cm;">Kg</td>
        </tr>
        <tr>
            <td style="width: 8cm;">TOTALE VOLUME:</td>
            <td style="width: 2cm; text-align: right;"><strong>{$totali.volumeM3|number_format:3:",":" "}</strong></td>
            <td style="width: 2cm;">M<sup>3</sup></td>
        </tr>
    </tbody>
</table>

<br><br><br><br><br><br><br><br><br><br><br><br><br>

<table style="width: 100%;">
    <tbody>
        <tr>
            <td style="width: 15cm;"></td>
            <td style="text-align: center;">
                <label>FIRMA</label>
                <br><br><br><br>
                <label>-----------------------------------------------</label>
            </td>
        </tr>
    </tbody>
</table>