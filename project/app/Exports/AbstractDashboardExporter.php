<?php

namespace App\Exports;

abstract class AbstractDashboardExporter
{
    /**
     * O Template Method que define o esqueleto do algoritmo de exportação.
     * Subclasses não devem sobrescrever este método, por isso ele é 'final'.
     */
    final public function export(array $data): string
    {
        $output = $this->generateHeader();
        $output .= $this->generateBody($data);
        $output .= $this->generateFooter();

        return $output;
    }

    /**
     * Passos a serem implementados obrigatoriamente pelas subclasses
     */
    abstract protected function generateHeader(): string;

    abstract protected function generateBody(array $data): string;

    /**
     * Hook (gancho) opcional. As subclasses podem sobrescrever se necessário,
     * mas não são obrigadas.
     */
    protected function generateFooter(): string
    {
        return '';
    }
}
