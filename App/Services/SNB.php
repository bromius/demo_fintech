<?php
namespace App\Services;

/**
 * Handles retrieval and processing of exchange rates from Swiss National Bank
 */
class SNB
{
    /**
     * SNB RSS feed URL for exchange rates
     */
    const SNB_FEED_URL = 'https://www.snb.ch/public/en/rss/exchangeRates';

    /**
     * Gets exchange rates for specified currencies against CHF
     *
     * @param array $currencies Array of currency codes to retrieve
     * @return array Array of rates (currency => rate)
     * @throws Exception If data cannot be fetched or parsed
     */
    public static function getRates(array $currencies)
    {
        $xmlString = file_get_contents(static::SNB_FEED_URL);
        if ($xmlString === false) {
            throw new Exception('Failed to fetch SNB data');
        }

        $xml = simplexml_load_string($xmlString);
        if ($xml === false) {
            throw new Exception('Failed to parse SNB XML');
        }

        $rates = [];
        $currencies = array_map('strtoupper', $currencies);

        foreach ($xml->channel->item as $item) {
            $cb = $item->children('cb', true);
            if (!$cb || !isset($cb->statistics->exchangeRate)) {
                continue;
            }

            $exchangeRate = $cb->statistics->exchangeRate;
            $targetCurrency = (string)$exchangeRate->targetCurrency;
            $baseCurrency = (string)$exchangeRate->baseCurrency;

            if ($baseCurrency !== cfg('app.system_currency') || !in_array($targetCurrency, $currencies)) {
                continue;
            }

            $observation = $exchangeRate->observation;
            $value = (float)$observation->value;
            $unitMult = (int)$observation->unitMult;

            $rate = static::calculateRate($value, $unitMult);

            if (!isset($rates[$targetCurrency])) {
                $rates[$targetCurrency] = $rate;
            }
        }

        return $rates;
    }

    /**
     * Calculates rate based on value and multiplier
     *
     * @param float $value Base rate value
     * @param int $unitMult Multiplier exponent
     * @return float Calculated rate
     */
    protected static function calculateRate($value, $unitMult)
    {
        if ($unitMult === 0) {
            return $value;
        }

        $multiplier = pow(10, abs($unitMult));
        return $unitMult > 0 ? $value * $multiplier : $value / $multiplier;
    }
}