<?php

namespace App\Services;

use App\Services\Dto\BearerToken;
use GuzzleHttp\Client;
use App\Exceptions\KhanbankException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Http\Response;
use Psr\Http\Message\RequestInterface;
use Illuminate\Config\Repository;

class KhanbankService
{
    private $client;

    private $config;

    public function __construct($config)
    {
        $url = $config['base_url'];
        $this->config = $config;
        $stack = HandlerStack::create();

        info('url' . $url);

        $this->client = new Client([
            'base_uri' => $url,
            'handler' => $stack,
        ]);

        $stack->push(new RefreshToken($this->client, $config));
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getStatements($record)
    {
        //  https://doob.world:6442/v1/statements/{Account}?from={startDate}&to={Enddate}&page=1&size=10&record=100			
        $account = $this->config['account'];
        $response = $this->getClient()->request('GET', '/v1/statements/' . $account, [
            'query' => [
                "record" => $record,
            ],
        ]);

        if ($response->getStatusCode() == 200) {
            return json_decode((string) $response->getBody());
        }

    }

    public function transferDomestic($toAccount, $amount, $description)
    {
        $account = $this->config['account'];
        $loginName = $this->config['login_name'];
        $tranPassword = $this->config['tran_password'];

        $body = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                "fromAccount" => $account,
                "toAccount" => $toAccount,
                "toCurrency" => "MNT",
                "amount" => $amount,
                "description" => $description,
                "transferId" => $description,
                "loginName" => $loginName,
                "tranPassword" => $tranPassword
            ]
        ];

        $response = $this->getClient()->request('POST', '/v1/transfer/domestic', $body);


        return json_decode((string) $response->getBody());
    }

    public function transferInterbank($toAccount, $toAccountName, $amount, $toBank, $toCurrency, $description)
    {
        $account = $this->config['account'];
        $loginName = $this->config['login_name'];
        $tranPassword = $this->config['tran_password'];

        $body = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                "fromAccount" => $account,
                "toAccount" => $toAccount,
                "toCurrency" => $toCurrency,
                "toAccountName" => $toAccountName,
                "toBank" => $toBank,
                "amount" => $amount,
                "description" => $description,
                "mainCurrency" => 'MNT',
                "loginName" => $loginName,
                "tranPassword" => $tranPassword
            ]
        ];

        $response = $this->getClient()->request('POST', '/v1/transfer/interbank', $body);

        return json_decode((string) $response->getBody());
    }

    public function getAccountNameKhan($account)
    {
        $body = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];

        $response = $this->getClient()->request('GET', "/v1/accounts/$account/", $body);

        return json_decode((string) $response->getBody());
    }

    public function getAccountName($account, $bank_code)
    {
        $body = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'query' => [
                'bank' => $bank_code
            ]
        ];

        $response = $this->getClient()->request('GET', "/v1/accounts/$account/name", $body);

        return json_decode((string) $response->getBody());
    }

}

class RefreshToken
{
    private $token;
    private $client;
    private $config;

    public function __construct(Client $client, $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    public function __invoke(callable $next)
    {
        return function (RequestInterface $request, array $options = []) use ($next) {
            $request = $this->applyToken($request);
            return $next($request, $options);
        };
    }

    protected function applyToken(RequestInterface $request)
    {
        if ($this->token == null || $this->token->isTokenExpired()) {
            $this->acquireToken();
        }

        return $request->withHeader('Authorization', 'Bearer ' . $this->token->getToken());
    }


    private function acquireToken()
    {
        $basicAuth = explode(':', $this->config['basic_auth']);
        $username = $basicAuth[0];
        $password = $basicAuth[1];

        info('basicAuth' . $username);

        $response = $this->client->request('POST', '/v1/auth/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'auth' => [$username, $password],
            "query" => [
                "grant_type" => "client_credentials"
            ],
            'handler' => \GuzzleHttp\choose_handler(),
        ]);

        $data = json_decode((string) $response->getBody());
        $this->token = new BearerToken($data->access_token, $data->access_token_expires_in);
    }
}