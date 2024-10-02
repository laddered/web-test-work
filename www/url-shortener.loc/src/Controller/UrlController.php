<?php

namespace App\Controller;

use App\Entity\Url;
use App\Repository\UrlRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class UrlController extends AbstractController
{
    private function isValidUrl(string $url): bool
    {
        // Регулярное выражение для проверки URL с протоколом http или https
        return preg_match('/^https?:\/\/[a-zA-Z0-9-._~:\/?#[\]@!$&\'()*+,;=.]+$/', $url) === 1;
    }

    private function isValidHash(string $hash): bool
    {
        // Проверка, что хеш состоит только из букв, цифр и некоторых специальных символов (например, "-", "_", ".")
        return preg_match('/^[a-zA-Z0-9-_\.]+$/', $hash) === 1;
    }

    /**
     * @Route("/encode-url", name="encode_url")
     */
    public function encodeUrl(Request $request): JsonResponse
    {
        $urlString = $request->get('url');

        if (empty($urlString) || !$this->isValidUrl($urlString)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid URL format. URL should not contain extra characters and must contain a protocol (http:// or https://).'
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $existingUrl = $urlRepository->findOneBy(['url' => $urlString]);
        if ($existingUrl) {
            if ($existingUrl->getExpiredDate() > new \DateTime()) {
                return $this->json([
                    'hash' => $existingUrl->getHash()
                ]);
            } else {
                $urlRepository->remove($existingUrl);
            }
        }

        $url = new Url();
        $url->setUrl($urlString);
        $em = $this->getDoctrine()->getManager();
        $em->persist($url);
        $em->flush();

        return $this->json([
            'hash' => $url->getHash()
        ]);
    }

    /**
     * @Route("/decode-url", name="decode_url")
     */
    public function decodeUrl(Request $request): JsonResponse
    {
        $hash = $request->get('hash');

        if (empty($hash) || !$this->isValidHash($hash)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid hash format.'
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $url = $urlRepository->findOneByHash($hash);
        if (empty ($url)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Non-existent hash.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if ($url->getExpiredDate() < new \DateTime()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Expired hash.'
            ], Response::HTTP_FORBIDDEN);
        }
        return $this->json([
            'url' => $url->getUrl()
        ]);
    }

    /**
     * @Route("/go-url", name="go_url")
     */
    public function goUrl(Request $request): Response
    {
        $hash = $request->get('hash');

        if (empty($hash) || !$this->isValidHash($hash)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid hash format.'
            ],Response::HTTP_BAD_REQUEST);
        }

        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $url = $urlRepository->findOneByHash($hash);
        if (empty ($url)) {
            return $this->json([
                'error' => 'Non-existent hash.'
            ],Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return $this->redirect($url->getUrl());
    }

}
