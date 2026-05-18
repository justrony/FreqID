<?php

namespace App\Exports;

abstract class AbstractDashboardExporter
{

    final public function export(array $data): string
    {
        $output = $this->generateHeader();
        $output .= $this->generateBody($data);
        $output .= $this->generateFooter();

        return $output;
    }

    //Obrigatorias para as subclasses implementarem a geração do cabeçalho e do corpo do relatório.
    abstract protected function generateHeader(): string;

    abstract protected function generateBody(array $data): string;

    // Hook para as subclasses implementarem, caso necessário, a geração do rodapé. Por padrão, retorna uma string vazia.
    protected function generateFooter(): string
    {
        return '';
    }
}
