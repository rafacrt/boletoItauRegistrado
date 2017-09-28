<?php

namespace App\Services\Itau;

use GuzzleHttp\Client;
/**
 * Classe Responsável Para Gerar Boleto Itau
 */
class BoletoItau
{
    private $pagador;
    private $parametros;
    private $beneficiario;
    private $token;
    private $parametrosGerados;

    public function __construct($parametros)
    {
        $this->parametros = $parametros;
        /// Informando Dados do pagador
        $a = new Pagador($parametros['pagador']);
        $this->pagador = $a->getPagador();

        /// Informando dados parametros boleto
        $this->parametros = new ParametrosBoleto($parametros['dados_boleto']);
        $this->parametrosGerados = $this->parametros->getParametros();

        /// Setando Informações Beneficiário
        $b = new Beneficiario();
        $this->beneficiario = $b->getBeneficiario();
        /// Buscando Token Itau
        try {
            $token = new TokenItau();
            $this->token = $token->gerartoken();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

    }

    public function gerarBoleto()
    {
        /// Gerar Boleto
        $json = [
            'beneficiario' => $this->beneficiario,
            'pagador' => $this->pagador,
            'moeda' => [ 'codigo_moeda_cnab' => '09'],
            'juros' => [ 'tipo_juros' => 5 ],
            'multa' => [ 'tipo_multa' => 3 ],
            'grupo_desconto' =>  ['tipo_desconto' => 0],
            'recebimento_divergente' => [ 'tipo_autorizacao_recebimento' => "1"]
        ];
        /// Inserindo parametros
        $dados = array_merge($this->parametrosGerados, $json);

        $headers = [
            'Accept'    => 'application/vnd.itau',
            'access_token' => $this->token,
            'itau-chave' => config('Itau.chave'),
            'identificador' => config('Itau.identificador')
        ];

        // ABrindo Requisicao Nova Para itau
        $client = new Client([
            'base_uri' => 'https://gerador-boletos.itau.com.br',
            'http_errors' => false
        ]);
        // Gerando Novo Boleto
        $res = $client->request(
        'POST',
        '/router-gateway-app/public/codigo_barras/registro',
        ['headers' => $headers, 'json' => $dados]);

        $resultado = json_decode($res->getBody());
        if (isset($resultado->codigo)) {
            throw new \Exception("Erro ao tentar Emitir o Boleto");

        }

            // Atualiza o nosso numero
            $this->parametros->atualizanossonumero();
            // Salvar dados do boleto
            // Buscar e imprimir layout boleto
            $this->layoutBoleto($resultado);
    }

    public function layoutBoleto($dadosBoleto = '')
    {
        $dataVencimento = date_create($dadosBoleto->vencimento_titulo);
        $dataDocumento = date_create($dadosBoleto->data_emissao);
        $dataProcessamento = date_create($dadosBoleto->data_processamento);
        /// Criando Layout do boleto
        echo "<html>
                    <HEAD>
                        <TITLE>Boleto Itau</TITLE>
                        <STYLE>
                            td.BoletoCodigoBanco {font-size: 6mm; font-family: arial, verdana; font-weight : bold;
                                FONT-STYLE: italic; text-align: center; vertical-align: bottom;
                                border-bottom: 0.15mm solid #000000; border-right: 0.15mm solid #000000;
                            padding-bottom : 1mm}
                            td.BoletoLogo { border-bottom: 0.15mm solid #000000;  border-right: 0.15mm solid #000000;
                            text-align: center; height: 10mm}
                            td.BoletoLinhaDigitavel {font-size: 4 mm; font-family: arial, verdana; font-weight : bold;
                                text-align: center; vertical-align: bottom;
                            border-bottom: 0.15mm solid #000000; padding-bottom : 1mm; }
                            td.BoletoTituloEsquerdo{font-size: 0.2cm; font-family: arial, verdana; padding-left : 0.15mm;
                            border-right: 0.15mm solid #000000; text-align: left}
                            td.BoletoTituloDireito{font-size: 2mm; font-family: arial, verdana; padding-left : 0.15mm;
                            text-align: left}
                            td.BoletoValorEsquerdo{font-size: 3mm; font-family: arial, verdana; text-align: center;
                                border-right: 0.15mm solid #000000; font-weight: bold;
                            border-bottom: 0.15mm solid #000000; padding-top: 0.5mm}
                            td.BoletoValorDireito{font-size: 3mm; font-family: arial, verdana; text-align:right;
                                padding-right: 3mm; padding-top: 0.8mm; border-bottom: 0.15mm solid #000000;
                            font-weight: bold;}
                            td.BoletoTituloSacado{font-size: 2mm; font-family: arial, verdana; padding-left : 0.15mm;
                            vertical-align: top; padding-top : 0.15mm; text-align: left}
                            td.BoletoValorSacado{font-size: 3mm; font-family: arial, verdana;  font-weight: bold;
                            text-align : left}
                            td.BoletoTituloSacador{font-size: 2mm; font-family: arial, verdana; padding-left : 0.15mm;
                                vertical-align: bottom; padding-bottom : 0.8mm;
                            border-bottom: 0.15mm solid #000000}
                            td.BoletoValorSacador{font-size: 3mm; font-family: arial, verdana; vertical-align: bottom;
                                padding-bottom : 0.15mm; border-bottom: 0.15mm solid #000000;
                            font-weight: bold; text-align: left}
                            td.BoletoPontilhado{border-top: 0.3mm dashed #000000; font-size: 1mm}
                            ul.BoletoInstrucoes{font-size : 3mm; font-family : verdana, arial}
                        </STYLE>
                    </HEAD>
                    <BODY onload='window.print()'>

                        <P align=center>
                            <TABLE cellSpacing=0 cellPadding=0 border=0 class=Boleto>

                                <TR>
                                    <TD style='width: 0.9cm'></TD>
                                    <TD style='width: 1cm'></TD>
                                    <TD style='width: 1.9cm'></TD>

                                    <TD style='width: 0.5cm'></TD>
                                    <TD style='width: 1.3cm'></TD>
                                    <TD style='width: 0.8cm'></TD>
                                    <TD style='width: 1cm'></TD>

                                    <TD style='width: 1.9cm'></TD>
                                    <TD style='width: 1.9cm'></TD>

                                    <TD style='width: 3.8cm'></TD>

                                    <TD style='width: 3.8cm'></TD>
                                    <tr><td colspan=11>
                                            <ul class=BoletoInstrucoes>
                                                <li>Imprima em papel A4 ou Carta</li>
                                                <li>Utilize margens minimas a direita e a esquerda</li>
                                                <li>Recorte na linha pontilhada</li>
                                                <li>Não rasure o código de barras</li>
                                            </ul>
                                    </td></tr>
                                </TR>
                                <tr><td colspan=11 class=BoletoPontilhado>&nbsp;</td></tr>
                                <TR>
                                    <TD colspan=4 class=BoletoLogo><img src='/imagens/logoitau.jpg' style='height:80%;'></TD>
                                    <TD colspan=2 class=BoletoCodigoBanco>341-7</TD>
                                    <TD colspan=6 class=BoletoLinhaDigitavel>".$dadosBoleto->numero_linha_digitavel."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 class=BoletoTituloEsquerdo>Local de Pagamento</TD>
                                    <TD class=BoletoTituloDireito>Vencimento</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 class=BoletoValorEsquerdo style='text-align: left; padding-left : 0.1cm'>".$dadosBoleto->local_pagamento."</TD>
                                    <TD class=BoletoValorDireito>".date_format($dataVencimento, 'd/m/Y')."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 class=BoletoTituloEsquerdo>Beneficiário</TD>
                                    <TD class=BoletoTituloDireito>Agência/Código do Beneficiário</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 class=BoletoValorEsquerdo style='text-align: left; padding-left : 0.1cm'>".$dadosBoleto->beneficiario->nome_razao_social_beneficiario."</TD>
                                    <TD class=BoletoValorDireito>".$dadosBoleto->beneficiario->agencia_beneficiario."/".$dadosBoleto->beneficiario->conta_beneficiario."-".$dadosBoleto->beneficiario->digito_verificador_conta_beneficiario."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=3 class=BoletoTituloEsquerdo>Data do Documento</TD>
                                    <TD colspan=4 class=BoletoTituloEsquerdo>Número do Documento</TD>
                                    <TD class=BoletoTituloEsquerdo>Espécie doc</TD>
                                    <TD class=BoletoTituloEsquerdo>Aceite</TD>
                                    <TD class=BoletoTituloEsquerdo>Data do Processamento</TD>
                                    <TD class=BoletoTituloDireito>Nosso Número</TD>
                                </TR>
                                <TR>
                                    <TD colspan=3 class=BoletoValorEsquerdo>".date_format($dataDocumento, 'd/m/Y')."</TD>
                                    <TD colspan=4 class=BoletoValorEsquerdo></TD>
                                    <TD class=BoletoValorEsquerdo>".$dadosBoleto->especie_documento."</TD>
                                    <TD class=BoletoValorEsquerdo>S</TD>
                                    <TD class=BoletoValorEsquerdo>".date_format($dataProcessamento, 'd/m/Y')."</TD>
                                    <TD class=BoletoValorDireito>".$dadosBoleto->nosso_numero."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=3 class=BoletoTituloEsquerdo>Uso do Banco</TD>
                                    <TD colspan=2 class=BoletoTituloEsquerdo>Carteira</TD>
                                    <TD colspan=2 class=BoletoTituloEsquerdo>Moeda</TD>
                                    <TD colspan=2 class=BoletoTituloEsquerdo>Quantidade</TD>
                                    <TD class=BoletoTituloEsquerdo>(x) Valor</TD>
                                    <TD class=BoletoTituloDireito>(=) Valor do Documento</TD>
                                </TR>
                                <TR>
                                    <TD colspan=3 class=BoletoValorEsquerdo>&nbsp;</TD>
                                    <TD colspan=2 class=BoletoValorEsquerdo>".config('Itau.carteira')."</TD>
                                    <TD colspan=2 class=BoletoValorEsquerdo>R$</TD>
                                    <TD colspan=2 class=BoletoValorEsquerdo>&nbsp;</TD>
                                    <TD class=BoletoValorEsquerdo>&nbsp;</TD>
                                    <TD class=BoletoValorDireito>".$dadosBoleto->valor_titulo."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 class=BoletoTituloEsquerdo>Instruco</TD>
                                    <TD class=BoletoTituloDireito>(-) Desconto</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 rowspan=9 class=BoletoValorEsquerdo style='text-align: left; vertical-align:top; padding-left : 0.1cm'>Instruções</TD>
                                    <TD class=BoletoValorDireito>&nbsp;</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoTituloDireito>(-) Outras Deduções/Abatimento</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoValorDireito>&nbsp;</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoTituloDireito>(+) Mora/Multa/Juros</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoValorDireito>&nbsp;</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoTituloDireito>(+) Outros Acr閟cimos</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoValorDireito>&nbsp;</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoTituloDireito>(=) Valor Cobrado</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoValorDireito>&nbsp;</TD>
                                </TR>
                                <TR>
                                    <TD rowspan=3 Class=BoletoTituloSacado>Sacado:</TD>
                                    <TD colspan=8 Class=BoletoValorSacado>".$dadosBoleto->pagador->nome_razao_social_pagador."</TD>
                                    <TD colspan=2 Class=BoletoValorSacado>".$dadosBoleto->pagador->cpf_cnpj_pagador."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 Class=BoletoValorSacado>".$dadosBoleto->pagador->logradouro_pagador."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 Class=BoletoValorSacado>".$dadosBoleto->pagador->cidade_pagador." ".$dadosBoleto->pagador->uf_pagador."&nbsp;&nbsp;&nbsp;".$dadosBoleto->pagador->cep_pagador."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=2 Class=BoletoTituloSacador>Sacador / Avalista:</TD>
                                    <TD colspan=9 Class=BoletoValorSacador></TD>
                                </TR>
                                <TR>
                                    <TD colspan=11 class=BoletoTituloDireito style='text-align: right; padding-right: 0.1cm'>Recibo do Sacado - Autentica玢o Mec鈔ica</TD>
                                </TR>
                                <TR>
                                    <TD colspan=11 height=60 valign=top>&nbsp</TD>
                                </TR>
                                <tr><td colspan=11 class=BoletoPontilhado>&nbsp;</td></tr>
                                       <TR>
                                    <TD colspan=4 class=BoletoLogo><img src='/imagens/logoitau.jpg' style='height:80%;'></TD>
                                    <TD colspan=2 class=BoletoCodigoBanco>341-7</TD>
                                    <TD colspan=6 class=BoletoLinhaDigitavel>".$dadosBoleto->numero_linha_digitavel."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 class=BoletoTituloEsquerdo>Local de Pagamento</TD>
                                    <TD class=BoletoTituloDireito>Vencimento</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 class=BoletoValorEsquerdo style='text-align: left; padding-left : 0.1cm'>".$dadosBoleto->local_pagamento."</TD>
                                    <TD class=BoletoValorDireito>".date_format($dataVencimento, 'd/m/Y')."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 class=BoletoTituloEsquerdo>Beneficiário</TD>
                                    <TD class=BoletoTituloDireito>Agência/Código do Beneficiário</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 class=BoletoValorEsquerdo style='text-align: left; padding-left : 0.1cm'>".$dadosBoleto->beneficiario->nome_razao_social_beneficiario."</TD>
                                    <TD class=BoletoValorDireito>".$dadosBoleto->beneficiario->agencia_beneficiario."/".$dadosBoleto->beneficiario->conta_beneficiario."-".$dadosBoleto->beneficiario->digito_verificador_conta_beneficiario."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=3 class=BoletoTituloEsquerdo>Data do Documento</TD>
                                    <TD colspan=4 class=BoletoTituloEsquerdo>Número do Documento</TD>
                                    <TD class=BoletoTituloEsquerdo>Espécie doc</TD>
                                    <TD class=BoletoTituloEsquerdo>Aceite</TD>
                                    <TD class=BoletoTituloEsquerdo>Data do Processamento</TD>
                                    <TD class=BoletoTituloDireito>Nosso Número</TD>
                                </TR>
                                <TR>
                                    <TD colspan=3 class=BoletoValorEsquerdo>".date_format($dataDocumento, 'd/m/Y')."</TD>
                                    <TD colspan=4 class=BoletoValorEsquerdo></TD>
                                    <TD class=BoletoValorEsquerdo>".$dadosBoleto->especie_documento."</TD>
                                    <TD class=BoletoValorEsquerdo>S</TD>
                                    <TD class=BoletoValorEsquerdo>".date_format($dataProcessamento, 'd/m/Y')."</TD>
                                    <TD class=BoletoValorDireito>".$dadosBoleto->nosso_numero."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=3 class=BoletoTituloEsquerdo>Uso do Banco</TD>
                                    <TD colspan=2 class=BoletoTituloEsquerdo>Carteira</TD>
                                    <TD colspan=2 class=BoletoTituloEsquerdo>Moeda</TD>
                                    <TD colspan=2 class=BoletoTituloEsquerdo>Quantidade</TD>
                                    <TD class=BoletoTituloEsquerdo>(x) Valor</TD>
                                    <TD class=BoletoTituloDireito>(=) Valor do Documento</TD>
                                </TR>
                                <TR>
                                    <TD colspan=3 class=BoletoValorEsquerdo>&nbsp;</TD>
                                    <TD colspan=2 class=BoletoValorEsquerdo>".config('Itau.carteira')."</TD>
                                    <TD colspan=2 class=BoletoValorEsquerdo>R$</TD>
                                    <TD colspan=2 class=BoletoValorEsquerdo>&nbsp;</TD>
                                    <TD class=BoletoValorEsquerdo>&nbsp;</TD>
                                    <TD class=BoletoValorDireito>".$dadosBoleto->valor_titulo."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 class=BoletoTituloEsquerdo>Instruco</TD>
                                    <TD class=BoletoTituloDireito>(-) Desconto</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 rowspan=9 class=BoletoValorEsquerdo style='text-align: left; vertical-align:top; padding-left : 0.1cm'>Instrucoes</TD>
                                    <TD class=BoletoValorDireito>&nbsp;</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoTituloDireito>(-) Outras Dedu珲es/Abatimento</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoValorDireito>&nbsp;</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoTituloDireito>(+) Mora/Multa/Juros</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoValorDireito>&nbsp;</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoTituloDireito>(+) Outros Acr閟cimos</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoValorDireito>&nbsp;</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoTituloDireito>(=) Valor Cobrado</TD>
                                </TR>
                                <TR>
                                    <TD class=BoletoValorDireito>&nbsp;</TD>
                                </TR>
                                 <TR>
                                    <TD rowspan=3 Class=BoletoTituloSacado>Sacado:</TD>
                                    <TD colspan=8 Class=BoletoValorSacado>".$dadosBoleto->pagador->nome_razao_social_pagador."</TD>
                                    <TD colspan=2 Class=BoletoValorSacado>".$dadosBoleto->pagador->cpf_cnpj_pagador."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 Class=BoletoValorSacado>".$dadosBoleto->pagador->logradouro_pagador."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=10 Class=BoletoValorSacado>".$dadosBoleto->pagador->cidade_pagador." ".$dadosBoleto->pagador->uf_pagador."&nbsp;&nbsp;&nbsp;".$dadosBoleto->pagador->cep_pagador."</TD>
                                </TR>
                                <TR>
                                    <TD colspan=11 class=BoletoTituloDireito style='text-align: right; padding-right: 0.1cm'>Ficha de Compensação - Autenticaçãoo Mecânica</TD>
                                </TR>
                                <TR>
                                    <TD colspan=11 height=60 valign=top>".$this->parametros->getBarCode($dadosBoleto->codigo_barras)."</TD>
                                </TR>
                                <tr><td colspan=11 class=BoletoPontilhado>&nbsp;</td></tr>
                        </TABLE></P>

                    </BODY>
                </HTML>";
    }

   }


