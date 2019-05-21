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
        $pdf    = $parser->parseFile('/app/1339247T COLDWINE B (19.2.19) Joint and several.pdf');
        $text = $pdf->getText();

        $appointment = $this->getAppointmentType($text);
        $subtype = $this->getSubtype($text);
        $bond = $this->getBondLevel($text);

        return $this->render('home.html.twig', [
            'text' => $appointment,
            'subtype' => $subtype,
            'bond' => $bond]);
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
