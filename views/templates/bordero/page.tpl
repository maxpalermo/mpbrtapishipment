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
<table cellborder="0" cellspacing="0">
    <thead>
        <tr>
            {foreach $cellstyle as $key => $row}
                <th style="{$cellstyle[$key].style}">{$row.label1}</th>
            {/foreach}
        </tr>
        <tr>
            {foreach $cellstyle as $key => $row}
                <th style="{$cellstyle[$key].style}">{$row.label2}</th>
            {/foreach}
        </tr>
        <tr>
            <th colspan="9">
                <hr>
            </th>
        </tr>
    </thead>
    <tbody>
        {foreach $rows as $row}
            <tr>
                {foreach $row as $key => $cell}
                    <td style="{$cellstyle[$key].style}">
                        {$cell.row1|upper}
                    </td>
                {/foreach}
            </tr>
            <tr>
                {foreach $row as $key => $cell}
                    <td style="{$cellstyle[$key].style}">
                        <strong>{$cell.row2|upper}</strong>
                    </td>
                {/foreach}
            </tr>
        {/foreach}
    </tbody>
</table>