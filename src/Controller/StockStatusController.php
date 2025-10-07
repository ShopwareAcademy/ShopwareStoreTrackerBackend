<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class StockStatusController extends AbstractController {
    protected Generator $faker;

    public function __construct(
        private TagAwareCacheInterface $cachePool
    ) {
        $this->faker = Factory::create();
    }


    #[Route('/stock-status/{productId}', name: 'stock_status')]
    public function index(string $productId): Response
    {
        $cacheKey = "stock.$productId";
        $stockData = $this->cachePool->get($cacheKey, function (ItemInterface $item) {
            $item->tag(['stock']);
            return $this->generateStockData();
        });

        return new JsonResponse($stockData);
    }

    private function generateStockData(): array {
        $stockData = [];

        for ($i = 0; $i < 5; $i++) {
            $stockData[] = [
                "name" => $this->faker->company(),
                "country" => $this->faker->country(),
                "stockQuantity" => $this->faker->numberBetween(1,100)
            ];
        }

        return $stockData;
    }
}