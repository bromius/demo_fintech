<?php
namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\RatesModel;

/**
 * Controller for handling bank accounts related operations
 */
class AccountsController extends Controller
{
    /**
     * @var TransactionModel Instance of transaction model
     */
    private $transactionModel;
    
    /**
     * @var RatesModel Instance of currency rates model
     */
    private $ratesModel;

    /**
     * Retrieves list of bank accounts with their balances
     * 
     * @return \App\Core\Response Returns JSON response with accounts data
     * @throws \Exception If there's an error processing the request
     */
    public function index()
    {
        $transactionModel = new TransactionModel();
        $accountsWithAmounts = $transactionModel->getAccountsWithAmounts();

        if (!$accountsWithAmounts) {
            return;
        }

        $currencies = $transactionModel->getUniqueCurrencies();

        // Remove system currency
        $currencies = array_diff($currencies, [cfg('app.system_currency')]);

        $ratesModel = new RatesModel();
        $rates = $ratesModel->getLatestRates($currencies);

        $result = [];
        foreach ($accountsWithAmounts as $account) {
            $result[] = [
                'account' => $account['account'],
                'currency' => $account['currency'],
                'end_balance' => $account['amount'],
                'end_balance_system_currency' => $account['currency'] != cfg('app.system_currency') 
                    ? ($account['amount'] * $rates[$account['currency']])
                    : $account['amount'],
            ];
        }

        return $this->success($result);
    }
}