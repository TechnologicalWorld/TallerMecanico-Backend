<?php

namespace App\Console\Commands;

use App\Services\ArchivoService;
use Illuminate\Console\Command;

class LimpiarArchivosExpirados extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archivos:limpiar-expirados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina archivos cuya fecha de expiración ha pasado';

    /**
     * Execute the console command.
     */
    public function handle(ArchivoService $archivoService)
    {
        $this->info('Limpiando archivos expirados');
        
        $count = $archivoService->limpiarExpirados();
        
        $this->info("Se eliminaron {$count} archivos expirados");
        
        return Command::SUCCESS;
    }
}
