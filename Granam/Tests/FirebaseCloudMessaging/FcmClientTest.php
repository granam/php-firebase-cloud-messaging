<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace Granam\Tests\FirebaseCloudMessaging;

use Granam\FirebaseCloudMessaging\FcmClient;
use Granam\FirebaseCloudMessaging\FcmMessage;
use Granam\FirebaseCloudMessaging\Target\FcmTopicTarget;
use Granam\Tests\Tools\TestWithMockery;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Mockery\MockInterface;

class FcmClientTest extends TestWithMockery
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
        $message = new FcmMessage(new FcmTopicTarget('bar'));
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

    /**
     * @test
     * @expectedException \Granam\FirebaseCloudMessaging\Exceptions\ApiKeyCanNotBeEmpty
     */
    public function I_can_not_create_it_with_empty_api_key(): void
    {
        new FcmClient($this->createGuzzleClient(), '');
    }

    /**
     * @test
     */
    public function I_can_switch_topics(): void
    {
        $apiKey = 'foo';
        $headers = [
            'Authorization' => 'key=' . $apiKey,
            'Content-Type' => 'application/json'
        ];
        $guzzleClient = $this->createGuzzleClient();
        $guzzleClient->shouldReceive('post')
            ->once()
            ->with(
                FcmClient::DEFAULT_URL_TO_SUBSCRIBE_TO_TOPIC,
                ['headers' => $headers, 'body' => '{"to":"\\/topics\\/baz","registration_tokens":["qux","foo BAR"]}']
            )
            ->andReturn($guzzleResponse = $this->createGuzzleResponse());
        $client = new FcmClient($guzzleClient, $apiKey);
        self::assertSame($guzzleResponse, $client->subscribeToTopic('baz', ['qux', 'foo BAR']));

        $guzzleClient->shouldReceive('post')
            ->once()
            ->with(
                FcmClient::DEFAULT_URL_TO_UNSUBSCRIBE_FROM_TOPIC,
                ['headers' => $headers, 'body' => '{"to":"\\/topics\\/bar","registration_tokens":["quux","baaz"]}']
            )
            ->andReturn($guzzleResponse = $this->createGuzzleResponse());
        self::assertSame($guzzleResponse, $client->unsubscribeFromTopic('bar', ['quux', 'baaz']));
    }
}