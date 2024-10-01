<?php

namespace App\Command;

use App\Repository\UrlRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendNewUrlsDataCommand extends Command
{
    protected static $defaultName = 'app:send-new-urls-data';
    protected static $defaultDescription = 'Send new urls data to REMOTE';
    private $em;
    /**
     * @var string
     */
    private $apiEndpointUrl;
    private $urlRepository;

    public function __construct
    (
        EntityManagerInterface $entityManager,
        UrlRepository $urlRepository,
        string $apiEndpointUrl
    )
    {
        parent::__construct();
        $this->em = $entityManager;
        $this->urlRepository = $urlRepository;
        $this->apiEndpointUrl = $apiEndpointUrl;
    }

    protected function configure(): void
    {
    }

    private function sendCurl($jsonData): array
    {
        try {
            $ch = curl_init($this->apiEndpointUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            $body = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return [
                'status' => 'success',
                'body' => $body,
                'httpCode' => $httpCode,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];

        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $newUrls = $this->urlRepository->findBy(['sentDate' => null]);

        if (count($newUrls) === 0) {
            $io->success('No new URLs to send.');
            return Command::SUCCESS;
        }

        $urlData = [];
        foreach ($newUrls as $url) {
            $urlData[] = [
                'url' => $url->getUrl(),
                'created_at' => $url->getCreatedDate()->format('Y-m-d H:i:s'),
            ];
        }

        $response = $this->sendCurl(json_encode($urlData));
        if($response['status'] == 'success') {
            if($response['httpCode'] == 200) {
                $io->success(count($newUrls) . ' new URLs sent successfully.');
                $currentDate = new \DateTimeImmutable();
                foreach ($newUrls as $url) {
                    $url->setSentDate($currentDate);
                    $this->em->persist($url);
                }
                $this->em->flush();
            } else {
                $io->error('Failed to send URLs. Status code: ' . $response['httpCode'] . '.' . PHP_EOL . 'Response: ' . $response['body']);
                return Command::FAILURE;
            }
        } else {
            $io->error('An error occurred while sending URLs: ' . $response['message']);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
