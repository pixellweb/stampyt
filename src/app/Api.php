<?php


namespace Citadelle\Stampyt\app;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;

/**
 * Class Api
 * @package App\Citadelle
 */
class Api
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    protected $token;


    /**
     * Api constructor.
     */
    public function __construct()
    {
        $this->url = config('stampyt.url');
        $this->token = config('stampyt.apiKey');
    }


    /**
     * @param string $ressource_path
     * @param array $params
     * @return array
     * @throws StampytException
     */
    public function get(string $ressource_path, array $params = []): array
    {
        $client = new Client([
                'base_uri' => $this->url
            ]
        );

        $headers = [
            'query' => $params,
            'headers' => [
                'x-api-key' => $this->token,
            ]
        ];

        try {
            $response = $client->get($ressource_path, $headers);

            if ($response->getStatusCode() != 200 or empty($response->getBody()->getContents())) {
                throw new StampytException("Api::get : code http error (" . $response->getStatusCode() . ") ou body vide : " . $ressource_path);
            }

        } catch (RequestException $exception) {
            /*$errors['request'] = Psr7\Message::toString($e->getRequest());
            if ($e->hasResponse()) {
                $errors['response'] = Psr7\Message::toString($e->getResponse());
            }*/

            throw new StampytException("Api::get : " . $exception->getMessage());
        }

        return json_decode($response->getBody(), true);

    }

    /**
     * @param string $ressource_path
     * @param array $params
     * @return bool
     * @throws GuzzleException
     * @throws StampytException
     */
    /*public function post(string $ressource_path, array $params): bool
    {
        $client = new Client(['base_uri' => $this->url]);
        $headers = [
            'query' => ['api_token' => $this->token],
            'headers' => [
                'Accept' => 'application/json'
            ],
            'form_params' => $params
        ];

        try {

            $response = $client->request('POST', $ressource_path, $headers);
            return true;

        } catch (RequestException $exception) {

            throw new StampytException("Api::post : " . $exception->getMessage());

        }
    }*/
}
