<?php

namespace App\Console\Commands;

use App\Services\CommissionCalculatorService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CalculateCommission extends Command
{
    protected $signature = 'app:calculate-commission';
    protected $description = 'Command description';
    public function handle()
    {
        $csvPath = storage_path('app/public/operations.csv');
        $csvFile = fopen($csvPath, 'r');
        while (($row = fgetcsv($csvFile)) !== false) {
            $operations[] = [
                'date' => $row[0],
                'user_id' => intval($row[1]),
                'client_type' => $row[2],
                'operation_type' => $row[3],
                'amount' => floatval($row[4]),
                'currency' => $row[5]
            ];
        }
        usort($operations, function ($a, $b) {
           return Carbon::parse($a['date'])->gt(Carbon::parse($b['date']));
        });
        $operation_index = 0;
        $request = Http::get('https://developers.paysera.com/tasks/api/currency-exchange-rates');
        $response = $request->json();
        $rates = $response['rates'];
        foreach ($operations as &$operation) {
            $operation['operation_index'] = $operation_index;
            $operation_index++;
            $operation['amount_eur'] = $operation['currency'] == "EUR" ? $operation['amount'] : $operation['amount'] / $rates[$operation['currency']];
        }
        $operations = collect($operations);
        /** @var CommissionCalculatorService $calculator */
        $calculator = app(CommissionCalculatorService::class);
        $commissions = $calculator->calculateCommissions($operations, $rates);
        $this->info(implode("\n", $commissions));
    }
}
