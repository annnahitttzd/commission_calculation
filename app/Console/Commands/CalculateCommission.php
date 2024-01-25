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
            $operations[] = $row;
            }
        fclose($csvFile);
//        $calculator = new CommissionCalculatorService();
//        $calculator->calculateCommissionForUser($operations);
        $calculator = app(CommissionCalculatorService::class);
        foreach ($operations as $row) {
            $commission = $calculator->calculateCommission($row);
//            dump( $commission);
        }
    }
}
// $users[$row->userId] = $row
