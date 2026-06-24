<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Command;

class MarkAbsencesCommand extends Command
{

    protected $signature = 'attendance:mark-absences
                            {--date= : Data alvo no formato YYYY-MM-DD (padrão: hoje)}';

    protected $description = 'Marca como ausente todos os alunos que não tiveram presença registrada no dia.';


    public function __construct(private readonly AttendanceService $service)
    {
        parent::__construct();
    }


    public function handle(): int
    {
        $date = $this->option('date')
            ? \Illuminate\Support\Carbon::parse($this->option('date'))
            : now();

        if ($date->isWeekend()) {
            $this->info(" {$date->toDateString()} é fim de semana — nenhuma falta registrada.");
            return self::SUCCESS;
        }

        $this->info("Verificando faltas para {$date->toDateString()}...");

        $count = $this->service->markAbsences($date);

        if ($count === 0) {
            $this->info(' Nenhuma falta a registrar — todos os alunos já possuem registro.');
        } else {
            $this->info(" {$count} falta(s) registrada(s) com sucesso.");
        }

        return self::SUCCESS;
    }
}
