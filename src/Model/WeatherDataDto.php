<?php
declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WeatherDataDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'City cannot be empty.')]
        private string $city,

        #[Assert\NotBlank(message: 'Country cannot be empty.')]
        private string $country,

        #[Assert\NotNull(message: 'Temperature cannot be null.')]
        private float $temperature,

        #[Assert\NotBlank(message: 'Condition cannot be empty.')]
        private string $condition,

        #[Assert\NotNull(message: 'Humidity cannot be null.')]
        private int $humidity,

        #[Assert\NotNull(message: 'Wind speed cannot be null.')]
        private float $windSpeed,

        #[Assert\NotBlank(message: 'Last updated date cannot be empty.')]
        private string $lastUpdated,
    ) {
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function getCondition(): string
    {
        return $this->condition;
    }

    public function getHumidity(): int
    {
        return $this->humidity;
    }

    public function getWindSpeed(): float
    {
        return $this->windSpeed;
    }

    public function getLastUpdated(): string
    {
        return $this->lastUpdated;
    }

    public function getLoggerContext(): array
    {
        return [
            'city' => $this->city,
            'temperature' => $this->temperature,
            'condition' => $this->condition,
        ];
    }
}