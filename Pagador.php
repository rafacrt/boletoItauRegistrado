<?php

namespace App\Services\Itau;

class Pagador
{
    private $pagador;

    public function __construct($pessoaFisica)
    {
        $this->pagador = $pessoaFisica;
    }

    public function getPagador()
    {
        $pagador = [
            'cpf_cnpj_pagador'      => $this->pagador['cpf_cnpj_pagador'],
            'nome_pagador'          => $this->pagador['nome'],
            'logradouro_pagador'    => $this->pagador['logradouro_pagador'],
            'cidade_pagador'        => $this->pagador['cidade_pagador'],
            'uf_pagador'            => $this->pagador['estado_pagador'],
            'cep_pagador'           => $this->pagador['cep_pagador']
        ];

        return $pagador;
    }
}
