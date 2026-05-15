<?php

namespace App\Exports;

class JsonDashboardExporter extends AbstractDashboardExporter
{
    private array $dataStorage = [];

    protected function generateHeader(): string
    {
        // No JSON, não vamos montar strings puras linha por linha para evitar erros de formatação
        // Apenas iniciamos a captura.
        $this->dataStorage = [
            'export_date' => now()->toIso8601String(),
            'data' => []
        ];
        return "";
    }

    protected function generateBody(array $data): string
    {
        $this->dataStorage['data'] = $data;
        return "";
    }

    protected function generateFooter(): string
    {
        return json_encode($this->dataStorage, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
