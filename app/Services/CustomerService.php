<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerService
{
    /**
     * The base URL of the external notification service.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * NotificationService constructor.
     *
     * Retrieves the service URL from the config file or the .env file.
     */
    public function __construct()
    {
        $this->baseUrl = config('services.customer_serv', env('CUSTOMER_SERV'));
    }

    /**
     * Sends a POST request to the external API.
     *
     * @param string $method The API endpoint method (e.g., '/device-tokens/add')
     * @param array  $params The parameters to send with the request
     * @return array The JSON-decoded response from the API, or an error array on failure
     */
    public function postCall($method, $params)
    {

        return $this->sendRequest('POST', $method, $params);
    }

    /**
     * Sends a GET request to the external API.
     *
     * @param string $method The API endpoint method (e.g., '/device-tokens')
     * @param array  $params Optional query parameters for the GET request
     * @return array The JSON-decoded response from the API, or an error array on failure
     */
    public function getCall($method, $params = [])
    {
        return $this->sendRequest('GET', $method, $params);
    }

    /**
     * Sends a PUT request to the external API.
     *
     * @param string $method The API endpoint method
     * @param array  $params The parameters to send with the request
     * @return array The JSON-decoded response from the API, or an error array on failure
     */
    public function putCall($method, $params)
    {
        return $this->sendRequest('PUT', $method, $params);
    }

    /**
     * Sends a PATCH request to the external API.
     *
     * @param string $method The API endpoint method
     * @param array  $params The parameters to send with the request
     * @return array The JSON-decoded response from the API, or an error array on failure
     */
    public function patchCall($method, $params)
    {
        return $this->sendRequest('PATCH', $method, $params);
    }

    /**
     * Sends a DELETE request to the external API.
     *
     * @param string $method The API endpoint method
     * @param array  $params The parameters to send with the request
     * @return array The JSON-decoded response from the API, or an error array on failure
     */
    public function deleteCall($method, $params = [])
    {
        return $this->sendRequest('DELETE', $method, $params);
    }

    /**
     * Generalized function to handle all request types.
     *
     * @param string $type The HTTP method (GET, POST, PUT, PATCH, DELETE)
     * @param string $method The API endpoint method
     * @param array  $params The request parameters
     * @return array The response from the API
     */
    protected function sendRequest($type, $method, $params = []) {
        try {
            $url = "{$this->baseUrl}/api/v1/{$method}";            // Prepare request//            \Log::info( $url);
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->{$type}($url, $params);
            $responseData = $response->json();

            if (isset($responseData['status']) && !$responseData['status']) {

                $message = $responseData['message'] ?? 'An unknown error occurred';

                Log::error("Error in {$type} request to {$method}: {$message}");

                return ['error' => $message];
            }
            return $responseData;
        } catch (\Exception $e) {
            Log::error("Error in {$type} request to {$method}: " . $e->getMessage());
            return ['error' => "Failed to send {$type} request"];
        }
    }
}
