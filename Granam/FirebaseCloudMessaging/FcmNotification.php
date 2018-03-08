<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace Granam\FirebaseCloudMessaging;

/**
 * @link https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support
 */
class FcmNotification extends FcmMessage
{
    private $title;
    private $body;
    private $iosBadge;
    private $androidIcon;
    private $sound;
    private $clickAction;
    private $tag;
    private $contentAvailable;

    public function __construct(
        string $title,
        string $body,
        string $sound = '',
        string $clickAction = '',
        string $tag = '',
        bool $contentAvailable = null,
        int $iosBadge = 0,
        string $androidIcon = ''
    )
    {
        $this->title = $title;
        $this->body = $body;
        $this->sound = $sound;
        $this->clickAction = $clickAction;
        $this->tag = $tag;
        $this->contentAvailable = $contentAvailable;
        $this->iosBadge = $iosBadge;
        $this->androidIcon = $androidIcon;
    }

    /**
     * iOS only, will add small red bubbles indicating the number of notifications to your apps icon
     *
     * @param integer $iosBadge
     * @return $this
     */
    public function setIosBadge(int $iosBadge): FcmNotification
    {
        $this->iosBadge = $iosBadge;

        return $this;
    }

    /**
     * Android only, set the name of your drawable resource as string
     *
     * @param string $androidIcon
     * @return $this
     */
    public function setAndroidIcon(string $androidIcon): FcmNotification
    {
        $this->androidIcon = $androidIcon;

        return $this;
    }

    public function setClickAction(string $actionName): FcmNotification
    {
        $this->clickAction = $actionName;

        return $this;
    }

    public function setSound(string $sound): FcmNotification
    {
        $this->sound = $sound;

        return $this;
    }

    public function setTag(string $tag): FcmNotification
    {
        $this->tag = $tag;

        return $this;
    }

    public function enableContentAvailable(): FcmNotification
    {
        $this->contentAvailable = true;

        return $this;
    }

    public function disableContentAvailable(): FcmNotification
    {
        $this->contentAvailable = true;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $jsonData = $this->getJsonData();
        if ($this->title !== '') {
            $jsonData['title'] = $this->title;
        }
        if ($this->body !== '') {
            $jsonData['body'] = $this->body;
        }
        if ($this->clickAction !== '') {
            $jsonData['click_action'] = $this->clickAction;
        }
        if ($this->sound !== '') {
            $jsonData['sound'] = $this->sound;
        }
        if ($this->tag !== '') {
            $jsonData['tag'] = $this->tag;
        }
        if ($this->contentAvailable !== null) {
            $jsonData['content_available'] = $this->contentAvailable;
        }
        if ($this->iosBadge > 0) {
            $jsonData['badge'] = $this->iosBadge;
        }
        if ($this->androidIcon !== '') {
            $jsonData['icon'] = $this->androidIcon;
        }

        return $jsonData;
    }
}