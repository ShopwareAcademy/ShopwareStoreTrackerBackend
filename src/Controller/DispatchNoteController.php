<?php declare(strict_types=1);

namespace App\Controller;

use Exception;
use JsonException;
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
    private readonly EntityManagerInterface $entityManager;
    private readonly ClientFactory          $clientFactory;
    private readonly ShopResolver           $shopResolver;
    private readonly LoggerInterface        $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        ClientFactory          $clientFactory,
        ShopResolver           $shopResolver,
        LoggerInterface        $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->clientFactory = $clientFactory;
        $this->shopResolver = $shopResolver;
        $this->logger = $logger;
    }

    /**
     * @throws JsonException
     */
    #[Route(
        '/dispatch-note',
        name: 'app_dispatch_note',
        methods: ['POST']
    )]
    public function index(RequestInterface $request): JsonResponse
    {
        $this->logger->alert($request->getBody()->getContents());
        $request->getBody()->rewind();

        // Let's also extract payload data and prepare for sending!
        $responseContents = json_decode(
            $request->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        // array_filter allows us to extract the customer address via a pattern in the array keys
        $customerAddress = array_filter(
            $responseContents,
            fn($key) => str_starts_with($key, 'customerDelivery'),
            ARRAY_FILTER_USE_KEY
        );

        $shop = $this->shopResolver->resolveShop($request);
        $client = $this->clientFactory->createSimpleClient($shop);

        // The URL we will use for updating the Shopware `Order` entity
        $orderPatchURL = $shop->getShopUrl() . '/api/order/' . $responseContents['orderId'];
        try {
            $dispatchNote = new DispatchNote();

            $dispatchNote->setShopwareOrderNumber($responseContents['shopwareOrderNumber']);
            $dispatchNote->setCustomerDeliveryAddress($customerAddress);
            $dispatchNote->setCustomerPhoneNumber($responseContents['customerEmail']);

            $this->entityManager->persist($dispatchNote);

            $client->patch($orderPatchURL, [
                'internalComment' => "Order dispatch note created for customer " . $responseContents['customerEmail']
            ]);
        } catch (Exception $e) {
            $client->patch($orderPatchURL, [
                'internalComment' => "Order dispatch note failed for customer " . $responseContents['customerEmail'] . ".\nError Message: " . $e->getMessage()
            ]);
        }

        return $this->json([
            'message' => 'All processes complete',
        ]);
    }
}
