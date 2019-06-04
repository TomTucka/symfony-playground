<?php


namespace App\Controller;

use App\Service\PdfReaderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @var PdfReaderService
     */
    private $pdfReadService;

    public function __construct(PdfReaderService $pdfReaderService)
    {
        $this->pdfReadService = $pdfReaderService;
    }

    /**
     * @Route("/")
     * @throws \Exception
     */
    public function home()
    {
        return $this->render('home.html.twig');
    }

    /**
     * @Route("/pdf/text")
     * @throws \Exception
     */
    // TODO - Write something to convert word doc into pdf and slap it into a service
    public function pdfText()
    {
        return $this->render('home.html.twig', $this->pdfReadService->parseOrder(1234));
    }
}
