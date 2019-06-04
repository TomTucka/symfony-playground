<?php

namespace App\Service;


use Prophecy\Argument;
use Smalot\PdfParser\Parser;
use PHPUnit\Framework\TestCase;
use Smalot\PdfParser\Document;

class PdfReaderServiceTest extends TestCase
{

    public function testParseOrder() {
        $parser = $this->prophesize(Parser::class);
        $document = $this->prophesize(Document::class);
        $document->getText()->shouldBeCalled()->willReturn("TEXT");
        $parser->parseFile(Argument::any())->willReturn($document->reveal());
        $service = new PdfReaderService($parser->reveal());

        $expectedResponse = ['text' => 'SOLE', 'subtype' => 'NEW ORDER', 'bond' => 'Bond is > 21,000'];
        $actualResponse = $service->parseOrder(1234);

        self::assertEquals($expectedResponse, $actualResponse);
    }
}
