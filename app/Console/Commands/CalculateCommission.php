<?php

namespace App\Console\Commands;

use App\Services\CommissionCalculatorService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateCommission extends Command
{
    protected $signature = 'app:calculate-commission';
    protected $description = 'Command description';

    public function handle()
    {
        $csvPath = storage_path('app/public/operations.csv');
        $csvFile = fopen($csvPath, 'r');
        while (($row = fgetcsv($csvFile)) !== false) {
            $rows[] = $row;
            }
        fclose($csvFile);
        $calculator = app(CommissionCalculatorService::class);
        foreach ($rows as $row) {

            $commission = $calculator->calculateCommission($row);
//            dump( $commission);
        }
    }
}
// $users[$row->userId] = $row
