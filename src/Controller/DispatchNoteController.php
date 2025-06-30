<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\DispatchNote;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\HttpClient\ClientFactory;
use Shopware\App\SDK\Shop\ShopResolver;
use Psr\Log\LoggerInterface;

class DispatchNoteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClientFactory $clientFactory,
        private ShopResolver $shopResolver,
        private LoggerInterface $logger,
    ) {}

    #[Route('/dispatch-note', name: 'app_dispatch_note', methods: ['POST'])]
    public function index(
        RequestInterface $request
    ): JsonResponse
    {
        $this->logger->alert($request->getBody()->getContents());
        $responseContents = json_decode($request->getBody()->getContents());
        $shop = $this->shopResolver->resolveShop($request);
        $client = $this->clientFactory->createSimpleClient($shop);
        $requestUrl = $shop->getShopUrl() . '/api/order';
        
        
        try {
            $dispatchNote = new DispatchNote();
            $dispatchNote->setShopwareOrderNumber($responseContents['shopwareOrderNumber']);
            $dispatchNote->setCustomerDeliveryAddress($responseContents['customerDeliveryAddress']);
            $dispatchNote->setCustomerPhoneNumber($responseContents['customerPhoneNumber']);
    
            $this->entityManager->persist($dispatchNote);

            $client->patch($requestUrl, [
                'json' => [
                    'filter' => [['type' => 'equals', 'field' => 'orderNumber', 'value' => $responseContents['shopwareOrderNumber']]],
                    'updates' => [
                        'additionalComment' => "Order dispatch note created"
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            $client->patch($requestUrl, [
                'json' => [
                    'filter' => [['type' => 'equals', 'field' => 'orderNumber', 'value' => $responseContents['shopwareOrderNumber']]],
                    'updates' => [
                        'additionalComment' => "Order dispatch note failed. Message {$e->getMessage()}"
                    ],
                ],
            ]);
        }
    
        return $this->json([
            'message' => 'All processes complete',
        ]);
    }
}
