<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

    class  CommissionCalculatorService
    {
    public function calculateCommissions(Collection $operations, $rates)
    {
        $commissions = [];
        foreach ($operations as $operation) {
            if ($operation['operation_type'] == 'withdraw') {
                $commissions[] = $this->calculateWithdrawCommission($operation, $operations, $rates);
            } elseif ($operation['operation_type'] == 'deposit') {
                $commissions[] = $this->calculateDepositCommission($operation['amount_eur']);
            }
        }
        return $commissions;
    }

        private function calculateWithdrawCommission($operation, $operations, $rates)
        {
            $feePercentage = $operation['client_type'] == 'private' ? 0.003 : 0.005;
            $freeChargeAmount = 0;

            if ($operation['client_type'] == 'private') {
                $operationsBeforeInCurrentWeek = $operations->where('date', '>=', Carbon::parse($operation['date'])->startOfWeek()->toDateString())
                    ->where('user_id', $operation['user_id'])->where('operation_index', '<', $operation['operation_index'])
                    ->where('operation_type', 'withdraw')
                    ->where('client_type', 'private');
                $amountSum = $operationsBeforeInCurrentWeek->sum('amount_eur');
                if ($operationsBeforeInCurrentWeek->count() < 3 && $amountSum < 1000) {
                    $freeChargeAmount = 1000 - $amountSum;
                }
            }

            $freeChargeAmount = $operation['currency'] == 'EUR' ? $freeChargeAmount : $freeChargeAmount * $rates[$operation['currency']];

            if ($operation['amount'] > $freeChargeAmount) {
                $amountToCalculateFee = $operation['amount'] - $freeChargeAmount;
            } else {
                $amountToCalculateFee = 0;
            }

            return number_format(ceil($amountToCalculateFee * $feePercentage * 100) / 100, 2);
        }
        private function calculateDepositCommission($amount)
        {
            return number_format(ceil($amount * 0.0003 * 100) / 100, 2);
        }
    }
