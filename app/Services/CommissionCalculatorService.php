<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

    class   CommissionCalculatorService
    {
        private $userWithdrawnAmountThisWeek =[];
        private $withdrawalCountThisWeek = [];
    public function calculateCommission(array $operations)
    {

        $operationDate = Carbon::parse($operations[0]);
        $userId = $operations[1];
        $clientType = $operations[2];
        $operationType = $operations[3];
        $amount = $operations[4];
        $currency = $operations[5];
        if ($operationType === 'deposit') {
            return $this->calculateDepositCommission($amount);
        }elseif ($operationType === 'withdraw') {
            return $this->calculateWithdrawCommission($userId, $clientType, $operationDate, $amount);
        }
        return 0;
    }
        private function calculateDepositCommission($amount)
        {
            return $amount * 0.0003;
        }
        private function calculateWithdrawCommission($userId, $clientType,$operationDate, $amount)
        {
            if (!isset($this->userWithdrawnAmountThisWeek[$userId])) {
                $this->userWithdrawnAmountThisWeek[$userId] = 0;
                $this->withdrawalCountThisWeek[$userId] = 0;
            }
            if ($clientType === 'private') {
                if ($this->freeOfCharge($userId, $operationDate, $amount)) {
                    return 0;
                }
                return $amount * 0.003;
            } elseif ($clientType === 'business'){
                return $amount * 0.005;
            }
            return 0;
        }
        private function freeOfCharge($userId, $operationDate, $amount)
        {
            $startOfWeek = Carbon::parse($operationDate)->startOfWeek();
            $endOfWeek = Carbon::parse($operationDate)->endOfWeek();
            if ($operationDate->isBetween($startOfWeek, $endOfWeek)) {
            $this->withdrawalCountThisWeek[$userId]++;
            if ($this->withdrawalCountThisWeek[$userId] <= 3) {
             $this->userWithdrawnAmountThisWeek[$userId] += $amount;

                if ($this->userWithdrawnAmountThisWeek[$userId] <= 1000.00) {
                return true;
                } else {
                 return $amount - (1000.00 - $this->userWithdrawnAmountThisWeek[$userId]);
                }
            }
        }
            return false;
        }
    }
