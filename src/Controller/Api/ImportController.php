<?php

namespace App\Controller\Api;

use App\Entity\Import;
use App\Form\ImportType;
use App\Message\ImportMessage;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

final class ImportController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private MessageBusInterface $bus,) {}

    #[Route('/api/import', name: 'api_import', methods: ['POST'])]
    #[OA\Tag('Upload import file')]
    #[OA\Post(
        path: '/api/import',
        summary: 'Upload import file',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'import[file_name]',
                            description: 'Import file',
                            type: 'string',
                            format: 'binary'
                        ),
                        new OA\Property(
                            property: 'import',
                            description: 'Just for Symfony form compatibility',
                            type: 'string',
                            nullable: true
                        ),
                    ],
                    type: 'object'
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Import file has been uploaded'
            )
        ]
    )]
    public function index(
        Request $request,
        #[Autowire('%import_dir%')] string $importDir,
    ): JsonResponse {
        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $file_name = $form->get('file_name')->getData()) {
            $import = new Import();
            $unique_file_name = bin2hex(random_bytes(12)) . '.' . $file_name->guessExtension();
            $file_name->move($importDir, $unique_file_name);
            $import->setFileName($unique_file_name);
            $this->entityManager->persist($import);
            $this->entityManager->flush();

            $this->bus->dispatch(new ImportMessage($import->getId()));

            return $this->json($import)->setStatusCode(Response::HTTP_CREATED);
        } else {
            $errors = ['Error import creation'];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage() . ' - ' . $error->getOrigin()->getName();
            }

            return $this->json([
                'messages' => $errors,
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
    }
}
