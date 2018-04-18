<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace Granam\FirebaseCloudMessaging;

/**
 * @link https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support
 */
abstract class DeviceFcmNotification extends FcmNotification
{
    /** @var string */
    protected $sound;
    /** @var string */
    protected $bodyLocKey;
    /** @var array|string[] */
    protected $bodyLocArgs;
    /** @var string */
    protected $titleLocKey;
    /** @var array|string[] */
    protected $titleLocArgs;

    /**
     * @var string $title = '' The notification's title. This field is not visible on iOS phones and tablets.
     * @var string $body = '' The notification's body text.
     * @var string $sound = '' The sound to play when the device receives the notification.
     * @var string $clickAction = ''  The action associated with a user click on the notification.
     * @var string $bodyLocKey = '' The key to the body string in the app's string resources to use to localize the body text to the user's current localization.
     * @var array $bodyLocArgs = []  Variable string values to be used in place of the format specifiers in body_loc_key to use to localize the body text to the user's current localization.
     * @var string $titleLocKey = '' The key to the title string in the app's string resources to use to localize the title text to the user's current localization.
     * @var array $titleLocArgs = [] Variable string values to be used in place of the format specifiers in title_loc_key to use to localize the title text to the user's current localization.
     */
    public function __construct(
        string $title = '',
        string $body = '',
        string $sound = '',
        string $clickAction = '',
        string $bodyLocKey = '',
        array $bodyLocArgs = [],
        string $titleLocKey = '',
        array $titleLocArgs = []
    )
    {
        parent::__construct($title, $body, $clickAction);
        $this->sound = $sound;
        $this->bodyLocKey = $bodyLocKey;
        $this->bodyLocArgs = $bodyLocArgs;
        $this->titleLocKey = $titleLocKey;
        $this->titleLocArgs = $titleLocArgs;
    }

    /**
     * @param string $sound
     * @return DeviceFcmNotification
     */
    public function setSound(string $sound): DeviceFcmNotification
    {
        $this->sound = $sound;

        return $this;
    }

    /**
     * @param string $bodyLocKey
     */
    public function setBodyLocKey(string $bodyLocKey): void
    {
        $this->bodyLocKey = $bodyLocKey;
    }

    /**
     * @param array|string[] $bodyLocArgs
     */
    public function setBodyLocArgs(array $bodyLocArgs): void
    {
        $this->bodyLocArgs = $bodyLocArgs;
    }

    /**
     * @param string $titleLocKey
     */
    public function setTitleLocKey(string $titleLocKey): void
    {
        $this->titleLocKey = $titleLocKey;
    }

    /**
     * @param array|string[] $titleLocArgs
     */
    public function setTitleLocArgs(array $titleLocArgs): void
    {
        $this->titleLocArgs = $titleLocArgs;
    }

    /**
     * @return bool
     */
    abstract public function isSilent(): bool;

    /**
     * @return array
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ExceededLimitOfTopics
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\MissingMultipleTopicsCondition
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\CountOfTopicsDoesNotMatchConditionPattern
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ExceededLimitOfDevices
     */
    public function jsonSerialize(): array
    {
        $jsonData = parent::jsonSerialize();
        if ($this->sound !== '') {
            $jsonData['sound'] = $this->sound;
        }
        if ($this->bodyLocKey !== '') {
            $jsonData['body_loc_key'] = $this->bodyLocKey;
        }
        if ($this->bodyLocArgs !== []) {
            $jsonData['body_loc_args'] = $this->bodyLocArgs;
        }
        if ($this->titleLocKey !== '') {
            $jsonData['title_loc_key'] = $this->titleLocKey;
        }
        if ($this->titleLocArgs !== []) {
            $jsonData['title_loc_args'] = $this->titleLocArgs;
        }

        return $jsonData;
    }

}