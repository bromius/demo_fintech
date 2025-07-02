<?php
namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\RatesModel;

/**
 * Handles chart data generation
 */
class ChartController extends Controller
{
    /**
     * Retrieves daily transaction data grouped by account
     * 
     * @return \App\Core\Response JSON response containing transaction data
     */
    public function index()
    {
        $transactionModel = new TransactionModel();
        $result = $transactionModel->getDailyTransactionsByAccount();

        return $this->success($result);
    }
}