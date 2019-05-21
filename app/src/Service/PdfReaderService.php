<?php


namespace App\Service;


use Smalot\PdfParser\Parser;

class PdfReaderService
{

    public function readPdf() {

        $pdfText = $this->getText();
        $appointment = $this->getAppointmentType($pdfText);
        $subtype = $this->getSubtype($pdfText);
        $bond = $this->getBondLevel($pdfText);

        return ['Appointment' => $appointment, 'Subtype' => $subtype, 'Bond' => $bond];
    }

    private function getText() {
        $parser = new Parser();
        // TODO - Pass the file in here
        $pdf    = $parser->parseFile('/app/1339247T COLDWINE B (19.2.19) Joint and several.pdf');
        return $pdf->getText();
    }

    private function getAppointmentType($orderText) {
        $expression = "/ORDER APPOINTING (?:A )?(?:NEW )?(JOINT AND SEVERAL |JOINT )?(?:DEPUTIES|DEPUTY)?/";
        preg_match($expression, $orderText, $matches, PREG_UNMATCHED_AS_NULL);

        switch ($matches) {
            case (sizeof($matches) == 1):
                return 'SOLE';
            case $matches[1] == 'JOINT AND SEVERAL ':
                return 'JOINT AND SEVERAL';
            case $matches[1] == 'JOINT ':
                return 'JOINT';
            default:
                return 'NO MATCHES FOUND';
        }

    }

    private function getSubtype($orderText) {
        $expression = "/ORDER APPOINTING (?:A |AN )?(NEW )?(INTERIM )?(?:JOINT AND SEVERAL |JOINT )?(?:DEPUTIES|DEPUTY)?/";
        preg_match($expression, $orderText, $matches, PREG_UNMATCHED_AS_NULL);

        switch ($matches) {
            case (sizeof($matches) == 1):
                return 'NEW ORDER';
            case $matches[1] == 'NEW ':
                return 'REPLACEMENT ORDER';
            case $matches[1] == 'INTERIM ':
                return 'INTERIM ORDER';
            default:
                return 'NO MATCHES FOUND';
        }
    }

    private function getBondLevel($orderText){
        $expression = "/sum of (.*) in /";
        preg_match($expression, $orderText, $matches, PREG_UNMATCHED_AS_NULL);

        $bond = intval(preg_replace("/[^a-zA-Z0-9]/", "", $matches[1]));
        switch ($bond) {
            case ($bond > 21000):
                return 'Bond is > 21,000';
            case ($bond < 21000):
                return 'Bond is < 21,000';
        }
    }
}
