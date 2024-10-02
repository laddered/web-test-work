<?php

namespace App\Tests\Controller;

use App\Entity\Url;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UrlControllerTest extends WebTestCase
{
    private $client;
    private $em;
    private $validHash;
    private $expiredHash;
    private $url;
    private $expiredUrl;

    protected function setUp(): void
    {
//        self::bootKernel();
        $this->client = static::createClient();

        $this->em = $this->client->getContainer()->get('doctrine')->getManager();

        $date = new \DateTimeImmutable();
        $this->validHash = $date->format('YmdHis');
        $this->url = new Url();
        $this->url->setUrl('http://test.com');
        $this->url->setHash($this->validHash);
        $this->url->setExpiredDate($date->modify('+1 minutes'));
        $this->em->persist($this->url);

        $expiredDate = new \DateTimeImmutable();
        $this->expiredHash = $expiredDate->modify('-2 minutes')->format('YmdHis');
        $this->expiredUrl = new Url();
        $this->expiredUrl->setUrl('http://expired.com');
        $this->expiredUrl->setHash($this->expiredHash);
        $this->expiredUrl->setExpiredDate($expiredDate->modify('-1 minute'));
        $this->em->persist($this->expiredUrl);

        $this->em->flush();
    }

    public function tearDown(): void
    {
        if ($this->url) {
            $this->em->remove($this->url);
        }
        if ($this->expiredUrl) {
            $this->em->remove($this->expiredUrl);
        }
        $this->em->flush();
        parent::tearDown();
    }

    public function testDecodeUrlWithValidHash()
    {
        $this->client->request('GET', '/decode-url', ['hash' => $this->validHash]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonStringEqualsJsonString(
            json_encode(['url' => 'http://test.com']),
            $this->client->getResponse()->getContent()
        );
    }

    public function testDecodeUrlWithNonExistentHash()
    {
        $this->client->request('GET', '/decode-url', ['hash' => 'invalidhash']);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'status' => 'error',
                'message' => 'Non-existent hash.'
            ]),
            $this->client->getResponse()->getContent()
        );
    }

    public function testDecodeUrlWithInvalidHash()
    {
        $this->client->request('GET', '/decode-url', ['hash' => '']);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'status' => 'error',
                'message' => 'Invalid hash format.'
            ]),
            $this->client->getResponse()->getContent()
        );
    }

    public function testDecodeUrlWithExpiredHash()
    {
        $this->client->request('GET', '/decode-url', ['hash' => $this->expiredHash]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'error', 'message' => 'Expired hash.']),
            $this->client->getResponse()->getContent()
        );
    }
}
