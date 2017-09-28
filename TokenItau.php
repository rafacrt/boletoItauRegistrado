<?php

namespace App\Services\Itau;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class TokenItau
{
 
    public function __construct()
    {
        // Ao chamar Precisamo Gerar um Boleto;
    }

    public function gerartoken()
    {
         $dadosPedirToken = [
             'client_id' => 'NUMERO DO MEU CLIENTE ID',
             'client_secret' => 'DADOS DO MEU CLIENT SECRET',
             'grant_type' => 'client_credentials',
             'scope' => 'readonly'];

         $client = new Client([
             'base_uri' => 'https://oauth.itau.com.br/'
         ]);

         $result = $client->request("POST", "identity/connect/token", [
            "form_params" => $dadosPedirToken
            ]);

        $body = $result->getBody();
        $data = json_decode($body, true);
        if ($result->getStatusCode() == 200) {
            return $data['access_token'];
        } else {
            throw new \Exception("Erro ao gerar token junto ao banco Itau, entre em contato como suporte.", 1);
        }
    }
}
