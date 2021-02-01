<?php

class CurrencyServerData
{
	private $cacheTime = false;
	private $fromCache = 60 * 60;

	// debug
	private $debug = false;
	private $response = "";
	private $clearCache = false;
	private $debugInfo = "";

	public function __construct()
	{

	}

	/**
	 * Cache timeout.
	 * @param $cacheTime
	 */
	public function setCacheTime($cacheTime)
	{
		$this->cacheTime = $cacheTime;

		// get from admin settings
		$frequency_value = get_option('frequency_value');
		if (!empty($frequency_value)){
			$this->cacheTime =$frequency_value;
		}

	}

	/**
	 * Get Exchange rate
	 * @param $currency_symbol_from - 3 letter currency id
	 * @param $currency_symbol_to - 3 letter currency id
	 * @param $log_it - write to log
	 * @return int  - exchange rate
	 */
	public function getRate($currency_symbol_from, $currency_symbol_to)
	{
		$currencyData = $this->getCurrencyExchangeRates($currency_symbol_from);
		$rate = 0;
		foreach ($currencyData as $currencyRateObj) {
			if ($currencyRateObj->symbol == $currency_symbol_to) {
				$rate = $currencyRateObj->price;
			}
		}
		return $rate;
	}

	/**
	 * Get currencies rates from server data
	 * @param $currency_symbol_from  - 3 letter currency id
	 * @return array - array of currency rate objects (symbol, price)
	 */
	public function getCurrencyExchangeRates($currency_symbol_from)
	{
		$data = $this->getCurrencyData($currency_symbol_from);
		$arr = array();
		foreach ($data as $currencyDataObj) {
			$priceObj = array_values((array)$currencyDataObj->quote)[0]; //get first element ()
			$currencyRateObj = new stdClass();
			$currencyRateObj->symbol = $currencyDataObj->symbol;
			$currencyRateObj->price = $priceObj->price;
			$arr[] = $currencyRateObj;
		}
		return $arr;
	}



	/**
	 * Get currencies list from server data
	 * @return array - array of currency objects (name, symbol)
	 */
	public function getCurrenciesList()
	{
		// use exchange rates data to get currencies list
		// alternatively we can get currencies list by calling /v1/cryptocurrency/map (CoinMarketCap ID map), but it costs 1 credit
		$data = $this->getCurrencyData("BTC");
		$arr = array();
		foreach ($data as $currencyDataObj) {
			$currencyObj = new stdClass();
			$currencyObj->name = $currencyDataObj->name;
			$currencyObj->symbol = $currencyDataObj->symbol;
			$arr[] = $currencyObj;
		}
		return $arr;
	}



	/**
	 * Get currency exchange rates list
	 * Plan credit use: 1 call credit per 200 cryptocurrencies returned (rounded up) and 1 call credit per convert option beyond the first
	 * We use cache to save credit.
	 * @param $currency_symbol_from  - 3 letter currency id
	 * @return |null
	 */
	private function getCurrencyData($currency_symbol_from)
	{

		$cache_key = 'currencyServerResponse' . $currency_symbol_from;

		if ($this->clearCache) {
			wp_cache_delete($cache_key);
		}

		$currencyServerResponse = wp_cache_get($cache_key); // check cache
		if (false === $currencyServerResponse) { // no data in cache
			$currencyServerResponse = $this->loadCurrencyData_fromServer($currency_symbol_from); // read from server
			if ($currencyServerResponse == null) {
				return null;
			}
			wp_cache_set($cache_key, $currencyServerResponse, null, $this->cacheTime);
			$this->fromCache = false;
		} else {
			$this->fromCache = true;
		}
		return $currencyServerResponse->data;
	}

	/**
	 * load raw data from https://pro-api.coinmarketcap.com

	API Info: https://coinmarketcap.com/api/documentation/v1/#operation/getV1CryptocurrencyListingsLatest
	name: The cryptocurrency name.
	symbol: The cryptocurrency symbol.
	quote: A map of market quotes in different currency conversions. The default map included is USD.
	quote->price:  Price in the specified currency for this historical.

	 *
	 * @param $currency_symbol_from  - 3 letter currency id
	 * @return mixed|null
	 */
	private function loadCurrencyData_fromServer($currency_symbol_from)
	{
		$currencyCount = 199;
		$this->response = "";
		$decodedData = null;
		try {
			$url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest';
			$parameters = [
				'start' => '1',
				'limit' => $currencyCount,
				'convert' => $currency_symbol_from
			];
			$headers = [
				'Accepts: application/json',
				'X-CMC_PRO_API_KEY: 9ef831d4-1538-456d-b625-f7c39589480b'
			];
			$qs = http_build_query($parameters); // query string encode the parameters
			$request = "{$url}?{$qs}"; // create the request URL

			$curl = curl_init(); // Get cURL resource
			curl_setopt_array($curl, array(
				CURLOPT_URL => $request,            // set the request URL
				CURLOPT_HTTPHEADER => $headers,     // set the headers
				CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
			));

			$this->response = curl_exec($curl); // Send the request, save the response
			$decodedData = json_decode($this->response);
			curl_close($curl); // Close request
		} catch (Exception $e) {

		}
		return $decodedData;
	}



	function debugInfo()
	{
		echo "<h1>Debug info:</h1>";
		echo $this->fromCache ? 'from cache <br>' : 'from server <br>';
		echo '<hr>';
		echo '<pre>';
		echo $this->response;
		echo '</pre>';
	}


}




