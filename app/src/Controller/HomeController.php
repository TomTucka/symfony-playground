<?php


namespace App\Controller;

use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    /**
     * @Route("/")
     * @throws \Exception
     */
    public function home()
    {
        return $this->render('home.html.twig');
    }

    /**
     * @Route("/enter-code")
     */
    public function enterCodes()
    {
        return $this->render('enter-code.html.twig', []);
    }


    /**
     * @Route("/confirmation")
     */
    public function confirmation()
    {
        return $this->render('base.html.twig', []);
    }

    /**
     * @Route("/pdf/text")
     * @throws \Exception
     */
    public function pdfText()
    {
        $parser = new Parser();
        
        //THIS WORKS 
        $pdf    = $parser->parseFile('/app/pdf/mockSoleNewUnder21.pdf');

        //$pdf = $parser->parseFile('/app/pdf/mockSoleReplacementOver21.pdf');
        //$pdf = $parser->parseFile('/app/pdf/mockSoleReplacementUnder21.pdf');
        
        $text = $pdf->getText();
        //die($text);
        $appointment = $this->getAppointmentType($text);
        $subtype = $this->getSubtype($text);
        $bond = $this->getBondLevel($text);

        return $this->render('home.html.twig', [
            'text' => $appointment,
            'subtype' => $subtype,
            'bond' => $bond]);
    }

    private function getAppointmentType($orderText) {
        $expression = "/ORDER\s*APPOINTING\s*(?:A|AN|)\s*(NEW|INTERIM|)\s*(?:JOINT\s*AND\s*SEVERAL|JOINT|)\s*(?:DEPUTIES|DEPUTY)/m";
        preg_match($expression, $orderText, $matches, PREG_UNMATCHED_AS_NULL);

        switch ($matches[2]) {
            case null:
                return 'SOLE';
            case 'JOINT AND SEVERAL':
                return 'JOINT AND SEVERAL';
            case 'JOINT':
                return 'JOINT';
            default:
                return 'NO MATCHES FOUND';
        }
    }

    private function getSubtype($orderText) {
        $expression = "/ORDER\s*APPOINTING\s*(?:A|AN|)\s*(NEW|INTERIM|)\s*(?:JOINT\s*AND\s*SEVERAL|JOINT|)\s*(?:DEPUTIES|DEPUTY)/m";
        preg_match($expression, $orderText, $matches, PREG_UNMATCHED_AS_NULL);

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

    private function getBondLevel($orderText){
        preg_match("/sum of (.*) in/", $orderText, $matches, PREG_UNMATCHED_AS_NULL);
        
        $bond = preg_replace("/[^a-zA-Z0-9]/", "", $matches[1]);
        
        switch ($bond) {
            case ($bond >= 21000):
                return 'Bond is > 21,000';
            case ($bond < 21000):
                return 'Bond is < 21,000';
        }
    }
}
