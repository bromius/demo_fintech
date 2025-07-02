<?php
namespace App\Models;

use Medoo\Medoo;

class TransactionModel
{
    /**
     * Truncate transactions table
     * 
     * @return bool Result of operation
     */
    public function truncate()
    {
        return db()->query('TRUNCATE TABLE transactions');
    }

    /**
     * Get transactions
     * 
     * @param int $limit Number of records to return
     * @param int $offset Records offset
     * @return array List of transactions
     */
    public function getList($limit = null, $offset = null)
    {
        $options = [
            'ORDER' => ['id' => 'DESC'],
            'deleted_at' => null
        ];

        if ($limit) {
            $options['LIMIT'] = $limit;
            if ($offset) {
                $options['LIMIT'] = [$offset, $limit];
            }
        }

        return db()->select('transactions', [
            'id',
            'account',
            'ident',
            'amount',
            'currency',
            'date'
        ], $options);
    }

    /**
     * Get unique currency values
     * 
     * @return array List of currencies
     */
    public function getUniqueCurrencies()
    {
        $stmt = db()->pdo->prepare('
            SELECT DISTINCT currency 
            FROM transactions 
            WHERE deleted_at IS NULL
        ');

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get accounts with their amounts
     * 
     * @return array List of accounts with sums
     */
    public function getAccountsWithAmounts()
    {
        return db()->select('transactions', [
            'account',
            'currency',
            'amount' => Medoo::raw('SUM(amount)')
        ], [
            'GROUP' => ['account', 'currency'],
            'deleted_at' => null
        ]);
    }

    /**
     * Get daily transactions grouped by account
     * 
     * @return array Grouped transactions
     */
    public function getDailyTransactionsByAccount()
    {
        return db()->select('transactions', [
            'account',
            'date' => Medoo::raw('DATE(date)'),
            'amount' => Medoo::raw('SUM(amount)')
        ], [
            'deleted_at' => null,
            'GROUP' => ['account', 'date'],
            'ORDER' => ['id' => 'ASC'],
        ]);
    }

    /**
     * Get daily transactions totals
     * 
     * @return array Daily totals
     */
    public function getDailyTotals()
    {
        return db()->select('transactions', [
            'date' => Medoo::raw('DATE(date)'),
            'amount' => Medoo::raw('SUM(amount)')
        ], [
            'GROUP' => 'DATE(date)',
            'deleted_at' => null
        ]);
    }

    /**
     * Update transaction by ID
     * 
     * @param int $id ID
     * @param array $data New data
     * @return bool Result of operation
     */
    public function update($id, array $data)
    {
        if (isset($data['ident']) && !$data['ident']) {
            throw new \Exception('Transaction No is not defined');
        }

        if (isset($data['date']) && !$data['date']) {
            throw new \Exception('Transaction date is not defined');
        }

        return db()->update('transactions', $data, [
            'id' => $id
        ]);
    }

    /**
     * Delete transaction by ID
     * 
     * @param int $id ID
     * @return bool Result of operation
     */
    public function delete($id)
    {
        return db()->update('transactions', [
            'deleted_at' => date('Y-m-d H:i:s')
        ], [
            'id' => $id
        ]);
    }
}