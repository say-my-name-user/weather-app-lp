<?php
declare(strict_types=1);

namespace App\Service;

use App\Exception\WeatherApiException;
use App\Model\WeatherDataDto;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class WeatherApi
{
    public function __construct(
        #[Assert\NotBlank(message: 'API URL cannot be empty.')]
        #[Assert\Url(message: 'Invalid API URL format.')]
        private string $apiUrl,

        #[Assert\NotBlank(message: 'API Key cannot be empty.')]
        private string $apiKey,

        #[Assert\Type(type: 'integer', message: 'Timeout must be an integer.')]
        #[Assert\GreaterThan(value: 0, message: 'Timeout must be greater than 0.')]
        private int|string $apiTimeout,

        private HttpClientInterface $httpClient,
        private ValidatorInterface $validator,
        private LoggerInterface $weatherApiLogger
    ) {
        $this->apiTimeout = (int)$apiTimeout;
    }

    /**
     * Fetches the current weather data for the provided city.
     *
     * @param string $request
     *
     * @return WeatherDataDto
     *
     * @throws WeatherApiException
     */
    public function fetchCurrentByRequest(string $request): WeatherDataDto
    {
        // validate API client config by #[Assert] attributes
        $violations = $this->validator->validate($this);
        if (count($violations)) {
            throw new WeatherApiException((string)$violations);
        }

        try {
            $response = $this->httpClient->request(
                Request::METHOD_GET,
                rtrim($this->apiUrl, '/') . "/current.json?key={$this->apiKey}&q={$request}",
                ['timeout' => $this->apiTimeout]
            )->toArray();
        } catch (\Throwable $e) {
            throw new WeatherApiException($e->getMessage());
        }

        $weatherData = $this->createResponseDto($response);

        $this->weatherApiLogger->info('Weather data fetched successfully.', $weatherData->getLoggerContext());

        return $weatherData;
    }

    /**
     * Maps the provided data array to a WeatherDataDto object and validates it.
     *
     * @param array $data
     *
     * @return WeatherDataDto
     *
     * @throws WeatherApiException
     */
    private function createResponseDto(array $data): WeatherDataDto
    {
        $weatherData = new WeatherDataDto(
            $data['location']['name'],
            $data['location']['country'],
            $data['current']['temp_c'],
            $data['current']['condition']['text'],
            $data['current']['humidity'],
            $data['current']['wind_kph'],
            $data['current']['last_updated'],
        );

        // validate API response fields by #[Assert] attributes
        $violations = $this->validator->validate($weatherData);
        if (count($violations)) {
            throw new WeatherApiException((string)$violations);
        }

        return $weatherData;
    }
}