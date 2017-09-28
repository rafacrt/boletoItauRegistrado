# Boleto Itau Registrado
Classe Para Emitir Boleto com Registro Itau.

Utilizando a Classe.

```php
      $dadosBoleto = [ 
      'pagador' => [
            'cpf_cnpj_pagador'                  => '00000000000',
            'nome'                              => 'Nome Pagador',
            'logradouro_pagador'                => 'Rua do pagador, Numero',
            'cidade_pagador'                    => 'CIDADE',
            'estado_pagador'                    => 'SP',
            'cep_pagador'                       => '00000000'
        ], 
        'dados_boleto' => [
            'vencimento_boleto'                 => 'YYYY-M-D',
            'valor_boleto'                      => '10000'
        ]];

        $boleto = new BoletoItau($dadosBoleto);
        $boleto->gerarBoleto();
  ```
