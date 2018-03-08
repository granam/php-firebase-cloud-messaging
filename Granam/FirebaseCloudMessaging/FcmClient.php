<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace Granam\FirebaseCloudMessaging;

use Granam\Strict\Object\StrictObject;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

/**
 * @author sngrl
 */
class FcmClient extends StrictObject
{
    public const DEFAULT_API_URL = 'https://fcm.googleapis.com/fcm/send';
    public const DEFAULT_URL_TO_SUBSCRIBE_TO_TOPIC = 'https://iid.googleapis.com/iid/v1:batchAdd';
    public const DEFAULT_URL_TO_UNSUBSCRIBE_FROM_TOPIC = 'https://iid.googleapis.com/iid/v1:batchRemove';

    /** @var \GuzzleHttp\Client */
    private $guzzleClient;
    /** @var string */
    private $apiKey;
    /** @var string|null */
    private $apiUrl;
    /** @var string|null */
    private $subscribeToTopicUrl;
    /** @var string|null */
    private $unsubscribeFromTopicUrl;

    /**
     * @param GuzzleClient $guzzleClient
     * @param string $apiKey How to get API key @link https://firebase.google.com/docs/server/setup#prerequisites
     * @param string $proxyApiUrl You can overwrite the API url with a proxy server url of your own
     * @param string $proxySubscribeToTopicUrl You can overwrite the API url with a proxy server url of your own
     * @param string $proxyUnsubscribeFromTopicUrl You can overwrite the API url with a proxy server url of your own
     */
    public function __construct(
        GuzzleClient $guzzleClient,
        string $apiKey,
        string $proxyApiUrl = null,
        string $proxySubscribeToTopicUrl = null,
        string $proxyUnsubscribeFromTopicUrl = null
    )
    {
        $this->guzzleClient = $guzzleClient;
        $this->apiKey = $apiKey;
        $this->apiUrl = $proxyApiUrl ?? self::DEFAULT_API_URL;
        $this->subscribeToTopicUrl = $proxySubscribeToTopicUrl ?? self::DEFAULT_URL_TO_SUBSCRIBE_TO_TOPIC;
        $this->unsubscribeFromTopicUrl = $proxyUnsubscribeFromTopicUrl ?? self::DEFAULT_URL_TO_UNSUBSCRIBE_FROM_TOPIC;
    }

    /**
     * Sends notification to the Google servers and returns a Guzzle response object with the Google servers answer.
     *
     * @param FcmMessage $message
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function send(FcmMessage $message): ResponseInterface
    {
        return $this->guzzleClient->post(
            $this->apiUrl,
            [
                'headers' => [
                    'Authorization' => 'key=' . $this->apiKey,
                    'Content-Type' => 'application/json'
                ],
                'body' => \json_encode($message)
            ]
        );
    }

    /**
     * @param string $topicCode
     * @param array $recipientsTokens
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function subscribeToTopic(string $topicCode, array $recipientsTokens): ResponseInterface
    {
        return $this->changeTopicSubscription($topicCode, $recipientsTokens, $this->subscribeToTopicUrl);
    }

    /**
     * @param string $topicCode
     * @param array $recipientsTokens
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function unsubscribeFromTopic(string $topicCode, array $recipientsTokens): ResponseInterface
    {
        return $this->changeTopicSubscription($topicCode, $recipientsTokens, $this->unsubscribeFromTopicUrl);
    }

    /**
     * @param string $topicCode
     * @param array|string $recipientsTokens
     * @param string $topicApiUrl
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function changeTopicSubscription(string $topicCode, array $recipientsTokens, string $topicApiUrl): ResponseInterface
    {
        return $this->guzzleClient->post(
            $topicApiUrl,
            [
                'headers' => [
                    'Authorization' => 'key=' . $this->apiKey,
                    'Content - Type' => 'application / json'
                ],
                'body' => \json_encode([
                    'to' => ' / topics / ' . $topicCode,
                    'registration_tokens' => $recipientsTokens,
                ])
            ]
        );
    }
}