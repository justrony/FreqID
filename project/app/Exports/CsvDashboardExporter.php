<?php

namespace App\Exports;

class CsvDashboardExporter extends AbstractDashboardExporter
{
    protected function generateHeader(): string
    {
        // Usa UTF-8 BOM para garantir que o Excel abra corretamente
        return "\xEF\xBB\xBF" . "Métrica,Valor\n";
    }

    protected function generateBody(array $data): string
    {
        $body = "";
        foreach ($data as $key => $value) {
            // Arrays não escalares viram JSON, valores simples continuam iguais
            if (is_array($value)) {
                $value = json_encode($value);
            }
            // Escapa aspas e junta os valores
            $body .= "\"{$key}\",\"" . str_replace('"', '""', $value) . "\"\n";
        }
        return $body;
    }
}
