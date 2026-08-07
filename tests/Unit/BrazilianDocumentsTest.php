<?php

namespace Tests\Unit;

use App\Support\BrazilianDocuments;
use PHPUnit\Framework\TestCase;

class BrazilianDocumentsTest extends TestCase
{
    public function test_validates_known_cpf_and_cnpj(): void
    {
        $this->assertTrue(BrazilianDocuments::cpf('111.444.777-35'));
        $this->assertTrue(BrazilianDocuments::cnpj('11.222.333/0001-81'));
    }

    public function test_rejects_repeated_and_invalid_documents(): void
    {
        $this->assertFalse(BrazilianDocuments::cpf('111.111.111-11'));
        $this->assertFalse(BrazilianDocuments::cnpj('11.222.333/0001-80'));
    }
}
