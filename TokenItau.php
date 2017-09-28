<?php

namespace App\Services\Itau;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class TokenItau
{
    private $token = '7be0dc92270ca70966c31c291a87bf837be0dc92270ca70966c31c291a87bf83';

    public function __construct()
    {
        // Ao chamar Precisamo Gerar um Boleto;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function gerartoken()
    {
         $dadosPedirToken = [
             'client_id' => 'gNMh2GwifEOx0',
             'client_secret' => 'R5W3M396i9NtHMCDC6fUsKll5Z4f1Yuf5SiyJvIthbjhvDi2t9v6kxwt3avlhvDAJZZMe3GYSeTJEUUkV4kRUg2',
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
