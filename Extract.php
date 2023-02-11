<?php

class PhpExtractAddress{

	private $_spacadores = '.,;-_/:';

	private $_maxNumDigitos = 4;

	private $_maxComplementos = 2;

	private $_semNumero = [
		'sn',
		's/n',
		'sn',
		's n',
		'km'
	];

	private $_complementos = [
		'acesso',
		'anexo',
		'ap',
		'apartamento',
		'apt',
		'apto',
		'bl',
		'bl a',
		'bl b',
		'bl c',
		'bl d',
		'bl e',
		'bl f',
		'bl g',
		'bloco',
		'bloco a',
		'bloco b',
		'bloco c',
		'bloco d',
		'bloco e',
		'bloco f',
		'bloco g',
		'cx',
		'cx postal',
		'cxpst',
		'cas',
		'casa fundos',
		'casa',
		'casa a',
		'casa b',
		'casa c',
		'casa d',
		'casa e',
		'casa f',
		'casa g',
		'caza',
		'conj',
		'conjunto',
		'frente',
		'fundos',
		'loja',
		'pavilhao',
		'pavilhao a',
		'pavilhao b',
		'pavilhao c',
		'pavilhao d',
		'pavilhao e',
		'pavilhao f',
		'pavilhao g',
		'pavlh',
		'pavlh a',
		'pavlh b',
		'pavlh c',
		'pavlh d',
		'pavlh f',
		'pavlh g',
		'sala',
		'terreo',
	];

	private $_numThreshold = 70;
	private $_compThreshold = 90;
	
	private function _stringWalk($source, $search)
	{

		$searchLength = mb_strlen($search, 'UTF-8');

		$startCut = 0;
		$walking = [];

		$cut = mb_substr($source, $startCut, $searchLength, 'UTF-8');

		while(mb_strlen($cut, 'UTF-8') > 0 and $searchLength === mb_strlen($cut, 'UTF-8')){

			$cut = mb_substr($source, $startCut, $searchLength, 'UTF-8');

			$walking[] = $cut;
			$startCut++;
		}

		return $walking;
	}
	
	private function _stringWalkReverse($source, $search)
	{

		$sourceLength = mb_strlen($source, 'UTF-8');
		$searchLength = mb_strlen($search, 'UTF-8');

		$startCut = $sourceLength - $searchLength;
		$walking = [];

		$cut = mb_substr($source, $startCut, $searchLength, 'UTF-8');

		while($startCut >= 0  and $searchLength === mb_strlen($cut, 'UTF-8')){

			$cut = mb_substr($source, $startCut, $searchLength, 'UTF-8');

			$walking[] = $cut;
			$startCut--;
		}

		return $walking;
	}

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

		$better = 0;
		$result = '';

		// SOMENTE UMA PALAVRA, RETORNAR NADA
		if(!preg_match('/[\s]/', trim($string, ' '))){
			return '';
		}

		// COMPLEMENTOS DEVEM ESTAR ORDENADOS DO MAIOR TAMANHO PARA O MENOR
		usort($this->_complementos, function($a, $b){
			return strlen($a) < strlen($b);
		});
		 

		foreach($this->_complementos as $complemento){

			$slices = $this->_stringWalkReverse($string, $complemento);

			foreach($slices as $slice){

				similar_text(mb_strtolower($slice, 'UTF-8'), mb_strtolower($complemento, 'UTF-8'), $percentage);

				// FIRST OCCURRENCE
				if($percentage == 100){

					$better = 100;
					$result = $slice;
					break;

				}else if($this->_compThreshold < $percentage and $better < $percentage){

					$better = $percentage;
					$result = $slice;
				}
			}

			// FIRST OCCURRENCE
			if($better == 100){
				break;
			}
		}

		// test if complement has number
		if($result != ''){

			$hasNumber = mb_strtolower(str_replace(str_split(' '.$this->_spacadores, 1), '', $string), 'UTF-8');

			/*if($string == 'Rua Avenida, 111,PAVLH D-04'){
				print $hasNumber.PHP_EOL;

				print $result.' [0-9]+?'.PHP_EOL;
				exit;
			}*/

			$regex = mb_strtolower($result, 'UTF-8');

			preg_match('/'.$regex.'([0-9]+)/', $hasNumber, $exit);
			if(isset($exit[1])){
				$result = $result.' '.$exit[1];
			}
		}

		return $result;
	}

	public function extract($string)
	{

		$numero = $this->_extrairNumero($string);
		$complemento = $this->_extrairComplemento($string);
		
		$rua = $string;

		if($complemento != ''){
			$rua = rtrim($rua, $complemento);
		}

		$rua = trim($rua, ' '.$this->_spacadores);

		// DETECÇÃO DO SEGUNDO COMPLEMENTO
		$complemento2 = $this->_extrairComplemento($rua);
		
		$rua = $string;

		if($complemento != '' and $complemento2 != '' and $complemento != $complemento2){
			$complemento = $complemento2.'; '.$complemento;
			$rua = rtrim($rua, $complemento);
		}

		$rua = trim($rua, ' '.$this->_spacadores);

		if(!empty($rua) and !empty($numero)){

			$rua = rtrim($rua, $numero);
		}

		$rua = trim($rua, ' '.$this->_spacadores);

		$numero = $numero == '' ? '???' : $numero;
		$complemento = $complemento == '' ? '???' : $complemento;

		return ['???', '???', $complemento];
	}
}