<?php

namespace App\Controller;

use App\Entity\ProcessedUrl;
use App\Repository\ProcessedUrlRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class UrlStatisticsController extends AbstractController
{
    /**
     * @Route("/urls", name="urls", methods={"POST"})
     */
    public function urls(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid data format. Expected an array of objects.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $processedUrls = [];
        $errors = [];
        foreach ($data as $index => $item) {
            if (!isset($item['url']) || !isset($item['createdDate'])) {
                $errors[] = [
                    'index' => $index,
                    'message' => 'URL and createdAt fields are required.'
                ];
                continue;
            }

            $url = $item['url'];
            $createdDate = $item['createdDate'];

            $processedUrls[] = [
                'url' => $url,
                'createdDate' => $createdDate
            ];
        }

        if (!empty($errors)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Some entries have missing fields.',
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $date = new \DateTimeImmutable();
        $em = $this->getDoctrine()->getManager();
        foreach ($processedUrls as $processedUrl) {
            $processedDate = new \DateTimeImmutable($processedUrl['createdDate']);
            $url = new ProcessedUrl();
            $url->setUrl($processedUrl['url']);
            $url->setCreatedDate($processedDate);
            $url->setProcessedDate($date);
            $em->persist($url);
        }
        $em->flush();

        return $this->json([
            'status' => 'success',
            'processedUrls' => $processedUrls
        ], Response::HTTP_OK);
    }


    /**
     * @Route("/urls/statistics/range", name="url_statistics_time_range", methods={"GET"})
     */
    public function getUniqueUrlsByTimeRange(Request $request): JsonResponse
    {
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');

        if (!$startDate || !$endDate) {
            return $this->json([
                'status' => 'error',
                'message' => 'Both start_date and end_date are required.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $startDateTime = \DateTime::createFromFormat('Y-m-d_H:i:s', $startDate);
        $endDateTime = \DateTime::createFromFormat('Y-m-d_H:i:s', $endDate);
        $startErrors = \DateTime::getLastErrors();
        $endErrors = \DateTime::getLastErrors();
        if ($startErrors['error_count'] > 0 || $endErrors['error_count'] > 0) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid date format. Acceptable format: Y-m-d_H:i:s',
                'start_date_errors' => $startErrors['errors'],
                'end_date_errors' => $endErrors['errors'],
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var ProcessedUrlRepository $processedUrlRepository */
        $processedUrlRepository = $this->getDoctrine()->getRepository(ProcessedUrl::class);
        $uniqueUrlsCount = $processedUrlRepository->getUniqueUrlsCountByDateRange($startDateTime, $endDateTime);

        return $this->json([
            'status' => 'success',
            'startDate' => $startDateTime->format('Y-m-d'),
            'endDate' => $endDateTime->format('Y-m-d'),
            'uniqueUrls' => $uniqueUrlsCount
        ]);
    }

    /**
     * @Route("/urls/statistics/domain", name="url_statistics_domain", methods={"GET"})
     */
    public function getUniqueUrlsByDomain(Request $request): JsonResponse
    {
        $domain = $request->query->get('domain');

        if (!$domain) {
            return $this->json([
                'status' => 'error',
                'message' => 'Domain is required.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (strpos($domain, 'http://') === 0 || strpos($domain, 'https://') === 0) {
            return $this->json([
                'status' => 'error',
                'message' => 'Domain should not contain http or https.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid domain format.'
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var ProcessedUrlRepository $processedUrlRepository */
        $processedUrlRepository = $this->getDoctrine()->getRepository(ProcessedUrl::class);
        $uniqueUrlsWithDomain = $processedUrlRepository->getUniqueUrlsCountByDomain($domain);

        return $this->json([
            'status' => 'success',
            'domain' => $domain,
            'uniqueUrlsWithDomain' => $uniqueUrlsWithDomain
        ]);
    }

}