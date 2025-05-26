<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Shopware\App\SDK\Context\Webhook\WebhookAction;
use Shopware\App\SDK\HttpClient\ClientFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;

#[AsController]
class UploadController {
    public const SHOPWARE_API_PATH = "/api/ce-physical-shop/";
    // This could be overridden with an environment variable to make the generated quantity variable
    private int $uploadLimit = 10;
    private Generator $faker;

    public function __construct(
        private ClientFactory $clientFactory,
        private RequestFactoryInterface $requestFactory,
        private HttpMessageFactoryInterface $httpFactory,
        private LoggerInterface $logger
    ) {
        $this->faker = Factory::create();
    }

    #[Route('/upload/physical-stores', methods: ['POST'])]
    public function handle(WebhookAction $webhook): Response
    {
        $client = $this->clientFactory->createSimpleClient(
            $webhook->shop
        );
        $requestUrl = $webhook->shop->getShopUrl() . static::SHOPWARE_API_PATH;

        for ($i = 0; $i < $this->uploadLimit; $i++) {
            $shopData = [
                "name" => $this->faker->company(),
                "streetAddress" => [
                    "street" => $this->faker->streetName(),
                    "townOrCity" => $this->faker->city(),
                    "countyOrProvince" => null,
                    "zipCode" => $this->faker->postcode(),
                ],
                "country" => $this->faker->country(),
                "description" => $this->faker->paragraph(),
                "email" => $this->faker->email(),
                "phone" => $this->faker->phoneNumber(),
            ];

            $client->post(
                $requestUrl,
                $shopData,
            );
        }
        
        return new Response(null, 204);
    }
}
