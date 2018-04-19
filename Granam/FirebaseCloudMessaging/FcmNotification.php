<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace Granam\FirebaseCloudMessaging;

use Granam\Strict\Object\StrictObject;

/**
 * @link https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support
 */
abstract class FcmNotification extends StrictObject implements \JsonSerializable
{
    /** @var string */
    protected $title;
    /** @var string */
    protected $body;
    /** @var string */
    protected $clickAction;

    /**
     * @param string $title The notification's title.
     * @param string $body The notification's body text.
     * @param string $clickAction The action associated with a user click on the notification.
     */
    public function __construct(
        string $title = '',
        string $body = '',
        string $clickAction = ''
    )
    {
        $this->title = $title;
        $this->body = $body;
        $this->clickAction = $clickAction;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * @param string $clickAction
     */
    public function setClickAction(string $clickAction): void
    {
        $this->clickAction = $clickAction;
    }

    abstract public function isSilent(): bool;

    abstract public function canBeSilenced(): bool;

    abstract public function setSilent();

    /**
     * @return array|string[]
     */
    public function jsonSerialize(): array
    {
        $jsonData = [];
        if ($this->title !== '') {
            $jsonData['title'] = $this->title;
        }
        if ($this->body !== '') {
            $jsonData['body'] = $this->body;
        }
        if ($this->clickAction !== '') {
            $jsonData['click_action'] = $this->clickAction;
        }

        return $jsonData;
    }

}