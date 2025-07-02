<?php
namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\RatesModel;

/**
 * Handles currency exchange rate data retrieval
 */
class RatesController extends Controller
{
    /**
     * Retrieves the latest exchange rates for all supported currencies
     * 
     * @return \App\Core\Response JSON response containing current exchange rates
     */
    public function index()
    {
        $transactionModel = new TransactionModel();
        $currencies = $transactionModel->getUniqueCurrencies();

        // Remove system currency
        $currencies = array_diff($currencies, [cfg('app.system_currency')]);

        $ratesModel = new RatesModel();
        $result = $ratesModel->getLatestRates($currencies);
        
        return $this->success($result);
    }
}