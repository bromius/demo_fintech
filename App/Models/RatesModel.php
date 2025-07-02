<?php
namespace App\Models;

/**
 * Model for working with exchange rates data
 */
class RatesModel
{
    /**
     * Saves rates data to database
     *
     * @param array $rates Array of rates [currency => rate]
     * @return bool True on success
     * @throws Exception On database error
     */
    public function saveRates(array $rates)
    {
        if (!$rates) {
            return;
        }

        $data = [];
        foreach ($rates as $currency => $rate) {
            $data[] = [
                'currency' => $currency,
                'rate' => $rate,
            ];
        }

        return db()->insert('rates', $data);
    }

    /**
     * Gets latest rates for specified currencies
     *
     * @param array $currencies Array of currency codes
     * @return array Array of latest rates
     */
    public function getLatestRates(array $currencies)
    {
        if (!$currencies) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($currencies), '?'));

        $stmt = db()->pdo->prepare("
            SELECT 
                r1.currency,
                r1.rate
            FROM rates r1
            INNER JOIN (
                SELECT currency, MAX(id) as max_id
                FROM rates
                WHERE currency IN ($placeholders)
                GROUP BY currency
            ) r2 
            ON r1.currency = r2.currency AND r1.id = r2.max_id
            ORDER BY r1.id DESC
        ");
        
        $stmt->execute($currencies);

        return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}