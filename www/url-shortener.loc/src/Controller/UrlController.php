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
    /**
     * @Route("/encode-url", name="encode_url")
     */
    public function encodeUrl(Request $request): JsonResponse
    {
        $urlString = $request->get('url');
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
        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $url = $urlRepository->findOneByHash($request->get('hash'));
        if (empty ($url)) {
            return $this->json([
                'error' => 'Non-existent hash.'
            ]);
        }
        if ($url->getExpiredDate() < new \DateTime()) {
            return $this->json([
                'error' => 'Expired hash.'
            ]);
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
        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $url = $urlRepository->findOneByHash($request->get('hash'));
        if (empty ($url)) {
            return $this->json([
                'error' => 'Non-existent hash.'
            ]);
        }
        return $this->redirect($url->getUrl());
    }

}
