<?php

/**
 * MessageStatisticsPlugin for phplist.
 *
 * This file is a part of MessageStatisticsPlugin.
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category  phplist
 *
 * @author    Duncan Cameron
 * @copyright 2011-2021 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */
class MessageStatisticsPlugin_CampaignReport
{
    private $cellHeight = 6;
    private $i18n;
    private $pdf;

    public function __construct($i18n)
    {
        $this->i18n = $i18n;
        $this->pdf = new phpList\plugin\Common\FPDF();
    }

    public function create($fields)
    {
        $this->pdf->AddPage();
        $lines = [
            [$this->i18n->get('From'), $fields['from']],
        ];

        if ($fields['campaigntitle'] != $fields['subject']) {
            $lines[] = [$this->i18n->get('Title'), $fields['campaigntitle']];
        }
        $lines[] = [$this->i18n->get('Subject'), $fields['subject']];
        $lines[] = [$this->i18n->get('Start date'), $fields['datestart']];
        $lines[] = [$this->i18n->get('End date'), $fields['datesent']];

        foreach ($lines as $line) {
            $this->pdf->SetFont('', 'B');
            $this->pdf->cell(20, $this->cellHeight, $line[0], 0, 0);
            $this->pdf->SetFont('');
            $this->pdf->MultiCell(0, $this->cellHeight, $line[1], 0, 'L');
        }
        $this->pdf->ln($this->cellHeight * 0.5);

        // Sent
        $lines = [
            [$this->i18n->get('Sent'), number_format($fields['sent']), ''],
            [$this->i18n->get('Delivered'), number_format($fields['delivered']), $fields['deliveredrate'] . '%'],
            [$this->i18n->get('Bounced'), number_format($fields['bouncecount']), $fields['bouncerate'] . '%'],
        ];
        $this->printSection($this->i18n->get('Of the total sent'), $lines);

        // Delivered
        $lines = [
            [$this->i18n->get('Opened'), number_format($fields['opens']), $fields['openrate'] . '%'],
            [$this->i18n->get('Not opened'), number_format($fields['unopens']), $fields['unopenrate'] . '%'],
        ];
        $this->printSection($this->i18n->get('Of the total delivered'), $lines);

        // Clicked
        $lines = [
            [$this->i18n->get('Users who clicked'), number_format($fields['clickUsers']), $fields['clickopenrate'] . '%'],
            [$this->i18n->get('Total number of clicks'), number_format($fields['totalClicks']), ''],
            [$this->i18n->get('Users who forwarded'), number_format($fields['forwardcount']), $fields['forwardrate'] . '%'],
        ];
        $this->printSection($this->i18n->get('Of the total who opened'), $lines);

        $fileName = $fields['subject'] . '.pdf';
        $content = ob_get_clean();
        $this->pdf->Output('D', $fileName, true);
    }

    private function printSection($heading, $lines)
    {
        $this->pdf->SetFont('', 'B');
        $this->pdf->cell(50, $this->cellHeight, $heading, 0, 0);
        $this->pdf->ln();
        $y = $this->pdf->GetY();
        $this->pdf->Line(20, $y, 95, $y);

        foreach ($lines as $line) {
            $this->pdf->SetFont('', 'B');
            $this->pdf->cell(30, $this->cellHeight, $line[0], 0, 0);
            $this->pdf->SetFont('');
            $this->pdf->cell(30, $this->cellHeight, $line[1], 0, 0, 'R');
            $this->pdf->cell(15, $this->cellHeight, $line[2], 0, 0, 'R');
            $this->pdf->ln();
        }
        $this->pdf->ln($this->cellHeight * 0.5);
    }
}
