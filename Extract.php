<?php

class PhpExtractAddress{

	private $_semNumero = ['sn', 's/n', 'sn', 's n', 'km'];

	private $_complementos = ['casa', 'casa 1', 'casa 2', 'casa 3', 'casa 4', 'casa 5', 'casa 6', 'casa 7', 'apt', 'apto'];

	private $_numThreshold = 83;
	private $_compThreshold = 70;

	private function _extrairNumero($string)
	{

		$string = str_replace([',', '.', '-', '_', ';'], ' ', $string);

		$words = explode(' ', $string);

		$better = 0;
		$result = '';
	
		foreach($words as $word){

			$somenteNumero = preg_replace('/[^0-9]/', '', $word);

			if(!empty($somenteNumero)){

				return $somenteNumero;

			}else{

				foreach($this->_semNumero as $sn){

					similar_text($word, $sn, $percentage);

					if($percentage > $this->_numThreshold and $better < $percentage){

						$better = $percentage;
						$result = $word;
					}
				}
			}
		}

		return $result;
	}

	private function _extrairComplemento($string)
	{

		$string = str_replace([',', '.', '-', '_', ';'], ' ', $string);

		$words = explode(' ', $string);

		$better = 0;
		$result = '';
	
		foreach($words as $word){

			foreach($this->_complementos as $complemento){

				similar_text(strtolower($word), strtolower($complemento), $percentage);

				if($percentage > $this->_compThreshold and $better < $percentage){

					$better = $percentage;
					$result = $word;
				}
			}
		}

		return $result;
	}

	public function extract($string)
	{

		$numero = $this->_extrairNumero($string);
		$complemento = $this->_extrairComplemento($string);
		
		$rua = $string;
		if(!empty($rua) and !empty($numero)){

			$rua = implode(' ', explode($numero, $rua));
		}
		if(!empty($rua) and !empty($complemento)){

			$rua = implode(' ', explode($complemento, $rua));
		}

		$rua = trim($rua, " .,;-_/");

		return [$rua, $numero, $complemento];
	}
}