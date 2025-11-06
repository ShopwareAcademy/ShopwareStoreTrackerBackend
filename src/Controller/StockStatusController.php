<?php declare(strict_types=1);

namespace App\Controller;

use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class StockStatusController extends AbstractController
{
    private Generator $faker;
    private readonly TagAwareCacheInterface $cachePool;

    public function __construct(TagAwareCacheInterface $cachePool)
    {
        $this->faker = Factory::create();
        $this->cachePool = $cachePool;
    }

    /**
     * @throws InvalidArgumentException
     */
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

    private function generateStockData(): array
    {
        $stockData = [];

        for ($i = 0; $i < 5; $i++) {
            $stockData[] = [
                "name" => $this->faker->company(),
                "country" => $this->faker->country(),
                "stockQuantity" => $this->faker->numberBetween(1, 100)
            ];
        }

        return $stockData;
    }
}