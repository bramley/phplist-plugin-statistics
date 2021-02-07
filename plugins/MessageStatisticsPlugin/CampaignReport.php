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
    private $pdf;

    public function __construct()
    {
        $this->pdf = new phpList\plugin\Common\FPDF();
    }

    public function create($fields, $listNames)
    {
        $this->pdf->AddPage();
        $lines = [
            ['Mailing List', $listNames],
            ['From', $fields['from']],
            ['Subject', $fields['subject']],
            ['Start date', $fields['datestart']],
            ['End date',  $fields['datesent']],
        ];

        foreach ($lines as $line) {
            $this->pdf->SetFont('', 'B');
            $this->pdf->cell(20, $this->cellHeight, $line[0], 0, 0);
            $this->pdf->SetFont('');
            $this->pdf->cell(70, $this->cellHeight, $line[1], 0, 0, 'L');
            $this->pdf->ln();
        }
        $this->pdf->ln($this->cellHeight * 0.5);

        // Sent
        $lines = [
            ['Sent', number_format($fields['sent']), ''],
            ['Delivered', number_format($fields['delivered']), $fields['deliveredrate'] . '%'],
            ['Bounced', number_format($fields['bouncecount']), $fields['bouncerate'] . '%'],
        ];
        $this->printSection('Of the total sent', $lines);

        // Delivered
        $lines = [
            ['Opened', number_format($fields['opens']), $fields['openrate'] . '%'],
            ['Not opened', number_format($fields['unopens']), $fields['unopenrate'] . '%'],
        ];
        $this->printSection('Of the total delivered', $lines);

        // Clicked
        $lines = [
            ['Users who clicked', number_format($fields['clickUsers']), $fields['clickopenrate'] . '%'],
            ['Total number of clicks', number_format($fields['totalClicks']), ''],
            ['Users who forwarded', number_format($fields['forwardcount']), $fields['forwardrate'] . '%'],
        ];
        $this->printSection('Of the total who opened', $lines);

        $fileName = preg_replace('/[^\w]+/', '_', $fields['subject']) . '.pdf';
        $content = ob_get_clean();
        $this->pdf->Output('D', $fileName);
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
