<?php declare(strict_types=1);

namespace App\Controller;

use Shopware\App\SDK\Context\Webhook\WebhookAction;
use Shopware\App\SDK\HttpClient\ClientFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Faker\Factory;
use Faker\Generator;

#[AsController]
class UploadController
{
    // Relative API path for sending data back to the Shopware API
    private const SHOPWARE_API_PATH = "/api/ce-physical-shop/";

    // Maximum number for demo records to upload
    private const UPLOAD_LIMIT = 10;

    private ClientFactory $clientFactory;
    private Generator     $faker;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
        $this->faker         = Factory::create();
    }

    #[Route('/upload/physical-stores', methods: ['POST'])]
    public function handle(WebhookAction $webhook): Response
    {
        $client     = $this->clientFactory->createSimpleClient($webhook->shop);
        $requestUrl = $webhook->shop->getShopUrl() . self::SHOPWARE_API_PATH;

        for ($i = 0; $i < self::UPLOAD_LIMIT; $i++) {
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
        // Return HTTP 204 (No Content) - Webhook handled successfully
        return new Response(null, 204);
    }
}
