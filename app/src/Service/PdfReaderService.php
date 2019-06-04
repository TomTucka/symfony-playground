<?php


namespace App\Service;

use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\ZipArchive as officeZip;
use Smalot\PdfParser\Parser;

class PdfReaderService
{
    /**
     * @var Parser
     */
    private $parser;


    /**
     * PdfReaderService constructor.
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    // TODO - Handle PDF coming into the function
    public function parseOrder($caseNumber) {
        $type_expression = "/ORDER\s*APPOINTING\s*(?:A|AN|)\s*(NEW|INTERIM|)\s*(?:JOINT\s*AND\s*|)(SEVERAL|JOINT|)\s*(?:DEPUTIES|DEPUTY)/m";

        //$file = $request->files->get('court-order');

        $this->readWordDocx();
        $text = $this->getText();
        if (!$this->checkCaseNumber($caseNumber, $text)) {
            return 'CASE_NUMBER_INVALID';
        }

        return ['text' => $this->getAppointmentType($text, $type_expression),
                'subtype' => $this->getOrderSubtype($text, $type_expression),
                'bond' => $this->getBondLevel($text)];
    }

    private function getText() {
        // TODO - Pass the file in here
        $pdf    = $this->parser->parseFile('/app/pdf/mockSoleReplacementOver21.pdf');
        return $pdf->getText();
    }

    /**
     * @throws Exception
     */
    private function readWordDocx()
    {
        $source = "/app/pdf/1339247T-COLDWINE.docx";
        // create your reader object
        $phpWordReader = IOFactory::createReader('Word2007');
        // read source
        if($phpWordReader->canRead($source)) {
            $phpWord = $phpWordReader->load($source);
           die($phpWord);
        }
    }

    private function checkCaseNumber($caseNumber, $text) {
        preg_match("/[^a-zA-Z0-9]/", $text, $matches);
        if (1234 == $caseNumber) {
            return true;
        } else {
            return false;
        }
    }

    private function getAppointmentType($text, $expression) {
        preg_match($expression, $text, $matches);

        switch ($matches[2]) {
            case null:
                return 'SOLE';
            case 'SEVERAL':
                return 'JOINT AND SEVERAL';
            case 'JOINT':
                return 'JOINT';
            default:
                return 'NO MATCHES FOUND';
        }
    }

    private function getOrderSubtype($text, $expression) {
        preg_match($expression, $text, $matches);

        switch ($matches[1]) {
            case null:
                return 'NEW ORDER';
            case 'NEW':
                return 'REPLACEMENT ORDER';
            case 'INTERIM':
                return 'INTERIM ORDER';
            default:
                return 'NO MATCHES FOUND';
        }
    }

    private function getBondLevel($text){
        preg_match("/sum of (.*) in/", $text, $matches);
        
        $bond = preg_replace("/[^a-zA-Z0-9]/", "", $matches[1]);
        
        switch ($bond) {
            case ($bond >= 21000):
                return 'Bond is > 21,000';
            case ($bond < 21000):
                return 'Bond is < 21,000';
        }
    }
}
