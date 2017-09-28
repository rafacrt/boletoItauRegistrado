<?php

namespace App\Services\Itau;


use App\Models\ge_paramline as ParamLine;

class ParametrosBoleto
{
    private $parametros;
    private $nossoNumero;
    private $digitoVerificador;

    public function __construct($parametros)
    {
        $this->parametros = $parametros;
        $this->geraNossoNumero();
        $this->digitoVerificador();
    }

    public function getParametros()
    {
        $parametros = [
            'tipo_ambiente'                     => config('Itau.ambiente'),
            'tipo_registro'                     => 1, // 1 Registro , 2 Ateração, 3 Consulta
            'tipo_cobranca'                     => 1, // tipo 1 Para Boletos
            'tipo_produto'                      => '00006', // Padrao
            'subproduto'                        => '00008', // Padrao
            'titulo_aceite'                     => 'S', // S cobrança, N Proposta
            'tipo_carteira_titulo'              =>  config('Itau.carteira'), // Carteira
            'nosso_numero'                      => $this->nossoNumero, //
            'digito_verificador_nosso_numero'   => $this->digitoVerificador,
            'data_vencimento'                   => $this->parametros['vencimento_boleto'],
            'valor_cobrado'                     => $this->parametros['valor_boleto'],
            'especie'                           => '01',
            'data_emissao'                      => date('Y-m-d'),
            'tipo_pagamento'                    => 1,
            'indicador_pagamento_parcial'       => 'false'
        ];
        return $parametros;
    }

    public function geraNossoNumero()
    {
        /// Buscando Informacoes de boleto banco
        $parametros = ParamLine::where('c_codparam', 'NUMDOCINTCOBESC')->first();

        $this->nossoNumero = '0'.($parametros->c_vlrparam + 1);
    }

    public function digitoVerificador()
    {

       // Gerando o digito verificador
       // Primeiro MOnta toda a string
       $numero = config('Itau.agencia').config('Itau.conta').config('Itau.carteira').$this->nossoNumero;
       $arryChar = str_split($numero);

       $numeros = array();
       $posChar = 0;
        /// Loop para verificar e alterar os valores regras DAC10 1,2,1,2,1,2,1,2
       do {
           $valor = intval(($posChar % 2 == 0) ? $arryChar[$posChar] : $arryChar[$posChar] * 2);
           // Verifico se resultado foi de 2 digitos, caso sim soma 1 digito mais o outro
           if ($valor > 9) {
               $a = str_split($valor);
              $valor = $a[0] + $a[1];
           }
           array_push($numeros, $valor);
           $posChar++;
        } while ($posChar < strlen($numero));

       $numeros = join('',$numeros);
       /// Somando todos os numeros e retornando o digito verificador
       $total = array_reduce(str_split($numeros), function($total, $item) {
            $total += $item;
           return $total;
       });

       // Total divido por 10, pego o resto e 10 - resto
       if ($total % 10 == 0) {
           $this->digitoVerificador = 0;
       } else {
           $this->digitoVerificador = 10 - ($total % 10);
       }
    }

    public function atualizaNossoNumero()
    {
        /// Atualizando nosso numero no banco
        $param = ParamLine::where('c_codparam','NUMDOCINTCOBESC')->first();
        $param->c_vlrparam = $param->c_vlrparam + 1;
        $param->save();


    }


    public function getBarCode($numero)
    {

	    $fino = 1;
		$largo = 3;
		$altura = 50;

		$barcodes[0] = '00110';
		$barcodes[1] = '10001';
		$barcodes[2] = '01001';
		$barcodes[3] = '11000';
		$barcodes[4] = '00101';
		$barcodes[5] = '10100';
		$barcodes[6] = '01100';
		$barcodes[7] = '00011';
		$barcodes[8] = '10010';
		$barcodes[9] = '01010';

		for($f1 = 9; $f1 >= 0; $f1--){
			for($f2 = 9; $f2 >= 0; $f2--){
				$f = ($f1*10)+$f2;
				$texto = '';
				for($i = 1; $i < 6; $i++){
					$texto .= substr($barcodes[$f1], ($i-1), 1).substr($barcodes[$f2] ,($i-1), 1);
				}
				$barcodes[$f] = $texto;
			}
		}

		$html = '<img src="/imagens/p.gif" width="'.$fino.'" height="'.$altura.'" border="0" />';
		$html .= '<img src="/imagens/b.gif" width="'.$fino.'" height="'.$altura.'" border="0" />';
		$html .= '<img src="/imagens/p.gif" width="'.$fino.'" height="'.$altura.'" border="0" />';
		$html .= '<img src="/imagens/b.gif" width="'.$fino.'" height="'.$altura.'" border="0" />';

		$html .= '<img ';

		$texto = $numero;

		if((strlen($texto) % 2) <> 0){
			$texto = '0'.$texto;
		}

		while(strlen($texto) > 0){
			$i = round(substr($texto, 0, 2));
			$texto = substr($texto, strlen($texto)-(strlen($texto)-2), (strlen($texto)-2));

			if(isset($barcodes[$i])){
				$f = $barcodes[$i];
			}

			for($i = 1; $i < 11; $i+=2){
				if(substr($f, ($i-1), 1) == '0'){
  					$f1 = $fino ;
  				}else{
  					$f1 = $largo ;
  				}

  				$html .= 'src="/imagens/p.gif" width="'.$f1.'" height="'.$altura.'" border="0">';
  				$html .= '<img ';

  				if(substr($f, $i, 1) == '0'){
					$f2 = $fino ;
				}else{
					$f2 = $largo ;
				}

				$html .= 'src="/imagens/b.gif" width="'.$f2.'" height="'.$altura.'" border="0">';
				$html .= '<img ';
			}
		}
		$html .= 'src="/imagens/p.gif" width="'.$largo.'" height="'.$altura.'" border="0" />';
		$html .= '<img src="/imagens/b.gif" width="'.$fino.'" height="'.$altura.'" border="0" />';
		$html .= '<img src="/imagens/p.gif" width="1" height="'.$altura.'" border="0" />';

        return $html;

    }

}
