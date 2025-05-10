<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\WeatherFormType;
use App\Service\WeatherApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WeatherController extends AbstractController
{
    public function __construct(
        private WeatherApi $weatherApi,
        private LoggerInterface $logger
    ) {
    }

    #[Route('/', name: 'weather_form', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(WeatherFormType::class);

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('weather/form.html.twig', [
                'form' => $form,
                'errors' => $form->getErrors(),
            ]);
        }

        try {
            return $this->render('weather/current.html.twig', [
                'weather' => $this->weatherApi->fetchCurrentByRequest($form->get('city')->getData())
            ]);
        } catch (\Throwable $e) {
            $this->logger->error($e);

            return $this->render('weather/form.html.twig', [
                'form' => $form,
                'errors' => [['message' => 'Sorry, something went wrong, try again later.']],
            ]);
        }
    }
}
