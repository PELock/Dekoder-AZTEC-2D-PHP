<?php

/******************************************************************************
 *
 * Dekoder kodow AZTEC 2D z dowodow rejestracyjnych interfejs Web API
 *
 * Wersja         : AZTecDecoder v1.0
 * Jezyk          : PHP
 * Zaleznosci     : Biblioteka cURL (http://php.net/manual/en/book.curl.php)
 * Autor          : Bartosz Wójcik (support@pelock.com)
 * Strona domowa  : https://www.dekoderaztec.pl | https://www.pelock.com
 *
 *****************************************************************************/

namespace PELock\AZTecDecoder;

class AZTecDecoder
{
	/**
	 * @var string domyslna koncowka WebApi
	 */
	const API_URL = "https://www.pelock.com/api/aztec-decoder/v1";

	/**
	 * @var string klucz WebApi do uslugi AZTecDecoder
	 */
	protected $_ApiKey = "";

	/**
	 * Inicjalizacja klasy AZTecDecoder
	 *
	 * @param string $ApiKey  Klucz do uslugi WebApi
	 */
	function __construct($ApiKey)
	{
		$this->_ApiKey = $ApiKey;
	}

	/**
	 * Dekodowanie zaszyfrowanej wartosci tekstowej do
	 * wyjsciowej tablicy w formacie JSON.
	 *
	 * @param string $Text           Odczytana wartosc z kodem AZTEC2D w formie ASCII
	 * @param string $ReturnAsArray  Czy zwrocic wartosc jako tablice asocjacyjna lub ciag tekstowy JSON
	 * @return mixed                 Tablica z odczytanymi wartosciami, ciag JSON lub false jesli blad
	 */
	public function DecodeText($Text, $ReturnAsArray = true)
	{
		// parametry
		$Params["command"] = "decode-text";
		$Params["text"] = $Text;

		return $this->PostRequest($Params, $ReturnAsArray);
	}

	/**
	 * Dekodowanie zaszyfrowanej wartosci tekstowej
	 * ze wskaznego pliku do wyjsciowej tablicy z
	 * formatu JSON.
	 *
	 * @param string $TextFilePath  Sciezka do pliku z odczytana wartoscia kodu AZTEC2D
	 * @param string $ReturnAsArray Czy zwrocic wartosc jako tablice asocjacyjna lub ciag tekstowy JSON
	 * @return mixed                Tablica z odczytanymi wartosciami, ciag JSON lub false jesli blad
	 */
	public function DecodeTextFromFile($TextFilePath, $ReturnAsArray = true)
	{
		$Text = @file_get_contents($TextFilePath);

		if (!$Text) return null;

		return $this->DecodeText($Text, $ReturnAsArray);
	}

	/**
	 * Dekodowanie zaszyfrowanej wartosci zakodowanej
	 * w obrazku PNG lub JPG/JPEG do wyjsciowej tablicy
	 * w formacie JSON.
	 *
	 * @param string $ImageFilePath  Sciezka do obrazka z kodem AZTEC2D
	 * @param string $ReturnAsArray  Czy zwrocic wartosc jako tablice asocjacyjna lub ciag tekstowy JSON
	 * @return mixed                 Tablica z odczytanymi wartosciami, ciag JSON lub false jesli blad
	 */
	public function DecodeImageFromFile($ImageFilePath, $ReturnAsArray = true)
	{
		// parametry
		$Params["command"] = "decode-image";
		$Params["image"] = $ImageFilePath;

		return $this->PostRequest($Params);
	}

	/**
	 * Wysyla zapytanie POST do serwera WebApi
	 *
	 * @param array $ParamsArray     Tablica z parametrami dla zapytania POST
	 * @param string $ReturnAsArray  Czy zwrocic wartosc jako tablice asocjacyjna lub ciag tekstowy JSON
	 * @return mixed                 Tablica z odczytanymi wartosciami, ciag JSON lub false jesli blad
	 */
	protected function PostRequest($ParamsArray)
	{
		// czy jest ustawiony klucz Web API?
		if (empty($this->_ApiKey))
		{
			return false;
		}

		// do parametrow dodaj klucz Web API
		$ParamsArray["key"] = $this->_ApiKey;

		if (!function_exists('curl_version'))
		{
			return false;
		}

		// ustaw poprawnie element z plikiem
		if (array_key_exists('image', $ParamsArray))
		{
			if (function_exists('curl_file_create'))
			{
				$ParamsArray['image'] = curl_file_create($ParamsArray['image']);
			}
			else
			{
				$ParamsArray['image'] = '@' . realpath($ParamsArray['image']);
			}
		}

		$ch = curl_init();

		// URL
		curl_setopt($ch, CURLOPT_URL, self::API_URL);

		// zapytanie jako POST
		curl_setopt($ch, CURLOPT_POST, true);

		// parametry POST
		curl_setopt($ch, CURLOPT_POSTFIELDS, $ParamsArray);

		// user agent
		curl_setopt($ch, CURLOPT_USERAGENT, "PELock AZTecDecoder");

		// zwroc tylko wynik
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// wykonaj zapytanie
		$response = curl_exec($ch);

		// zamknij sesje
		curl_close($ch);

		return $response;
	}
}
?>