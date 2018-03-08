<?php
namespace sngrl\Granam\Tests;

use Granam\FirebaseCloudMessaging\FcmClient;
use Granam\FirebaseCloudMessaging\FcmMessage;
use Granam\FirebaseCloudMessaging\Target\FcmTopicTarget;
use Granam\Tests\Tools\TestWithMockery;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Mockery\MockInterface;

class ClientTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_send_message_to_topic(): void
    {
        $apiKey = 'foo';
        $headers = [
            'Authorization' => 'key=' . $apiKey,
            'Content-Type' => 'application/json'
        ];
        $guzzleClient = $this->createGuzzleClient();
        $guzzleClient->shouldReceive('post')
            ->once()
            ->with(FcmClient::DEFAULT_API_URL, ['headers' => $headers, 'body' => '{"to":"\\/topics\\/bar"}'])
            ->andReturn($guzzleResponse = $this->createGuzzleResponse());

        $client = new FcmClient($guzzleClient, $apiKey);
        $message = new FcmMessage();
        $message->addTarget(new FcmTopicTarget('bar'));
        self::assertSame($guzzleResponse, $client->send($message));
    }

    /**
     * @return GuzzleClient|MockInterface
     */
    private function createGuzzleClient(): GuzzleClient
    {
        return $this->mockery(GuzzleClient::class);
    }

    /**
     * @return GuzzleResponse|MockInterface
     */
    private function createGuzzleResponse(): GuzzleResponse
    {
        return $this->mockery(GuzzleResponse::class);
    }
}