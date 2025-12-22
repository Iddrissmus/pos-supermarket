<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StockReorderService;

class CheckReorderLevels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check-reorder {--force : Run even if not scheduled}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan branch products and create reorder requests when stock is at or below reorder level.';

    protected StockReorderService $service;

    public function __construct(StockReorderService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $this->info('Starting reorder scan...');
        $result = $this->service->run();
        $this->info("Processed {$result['checked']} items. Created {$result['requests_created']} reorder requests.");
        return 0;
    }
}
