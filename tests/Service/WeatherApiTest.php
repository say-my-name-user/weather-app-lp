<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Exception\WeatherApiException;
use App\Service\WeatherApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WeatherApiTest extends TestCase
{
    private $httpClientMock;
    private $validationMock;
    private $weatherApiParameters = [
        'apiUrl' => 'https://api.weatherapi.com/v1/',
        'apiKey' => 'api_key',
        'apiTimeout' => 10,
    ];

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createConfiguredMock(HttpClientInterface::class, [
            'request' => $this->createConfiguredMock(ResponseInterface::class, [
                'toArray' => [
                    'location' => [
                        'name' => 'London',
                        'country' => 'GB',
                    ],
                    'current' => [
                        'temp_c' => 10,
                        'condition' => ['text' => 'Sunny'],
                        'humidity' => 10,
                        'wind_kph' => 10,
                        'last_updated' => '2025-05-10 12:00:00',
                    ]
                ]
            ])
        ]);

        $this->validationMock = $this->createConfiguredMock(ValidatorInterface::class, [
            'validate' => $this->createConfiguredMock(ConstraintViolationListInterface::class, [
                'count' => 0,
            ])
        ]);
    }

    public function testSuccessFetchCurrentByRequest(): void
    {
        $this->httpClientMock->expects($this->once())->method('request');
        $this->validationMock->expects($this->exactly(2))->method('validate');

        $weatherApi = new WeatherApi(
            $this->weatherApiParameters['apiUrl'],
            $this->weatherApiParameters['apiKey'],
            $this->weatherApiParameters['apiTimeout'],
            $this->httpClientMock,
            $this->validationMock,
            $this->createMock(\Psr\Log\LoggerInterface::class),
        );

        $result = $weatherApi->fetchCurrentByRequest('London');

        $this->assertEquals('London', $result->getCity());
        $this->assertEquals('GB', $result->getCountry());
        $this->assertEquals(10, $result->getTemperature());
        $this->assertEquals('Sunny', $result->getCondition());
        $this->assertEquals(10, $result->getHumidity());
        $this->assertEquals(10, $result->getWindSpeed());
        $this->assertEquals('2025-05-10 12:00:00', $result->getLastUpdated());
    }

    public function testFailedFetchCurrentByRequest(): void
    {
        $this->validationMock = $this->createConfiguredMock(ValidatorInterface::class, [
            'validate' => $this->createConfiguredMock(ConstraintViolationList::class, [
                'count' => 1,
                '__toString' => 'Validation failed',
            ])
        ]);

        $weatherApi = new WeatherApi(
            $this->weatherApiParameters['apiUrl'],
            $this->weatherApiParameters['apiKey'],
            $this->weatherApiParameters['apiTimeout'],
            $this->httpClientMock,
            $this->validationMock,
            $this->createMock(\Psr\Log\LoggerInterface::class),
        );

        $this->validationMock->expects($this->exactly(1))->method('validate');
        $this->httpClientMock->expects($this->never())->method('request');
        $this->expectException(WeatherApiException::class);

        $weatherApi->fetchCurrentByRequest('London');
    }
}