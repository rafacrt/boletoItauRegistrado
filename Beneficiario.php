<?php

namespace App\Services\Itau;

class Beneficiario
{
    private $cnpj_beneficiario;
    private $agencia_beneficiario;
    private $conta_beneficiario;
    private $digito_verificador_conta_beneficiario ;

    public function __construct($idRegional = '')
    {
        $this->cnpj_beneficiario = config('Itau.identificador');
        $this->agencia_beneficiario = config('Itau.agencia');
        $this->conta_beneficiario   = config('Itau.conta');
        $this->digito_verificador_conta_beneficiario = config('Itau.digito_verificador');
    }


    public function getBeneficiario()
    {
        /// Retorna Dados do Beneficiario
        $beneficiario = [
            'cpf_cnpj_beneficiario'          => $this->cnpj_beneficiario,
            'agencia_beneficiario'       => $this->agencia_beneficiario,
            'conta_beneficiario'         => $this->conta_beneficiario,
            'digito_verificador_conta_beneficiario'        => $this->digito_verificador_conta_beneficiario
        ];
        return $beneficiario;

    }

}
