<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace Granam\FirebaseCloudMessaging;

/**
 * @link https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support
 */
class IosFcmNotification extends DeviceFcmNotification
{
    /** @var int|null */
    private $badge;
    /** @var string */
    private $subTitle;
    /** @var bool */
    private $silent;

    /**
     * @var string $title = '' The notification's title. This field is not visible on iOS phones and tablets.
     * @var string $body = '' The notification's body text.
     * @var string $sound = '' The sound to play when the device receives the notification.
     * Sound files can be in the main bundle of the client app or in the Library/Sounds folder of the app's data container.
     * See the iOS Developer Library for more information https://developer.apple.com/library/content/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/SupportingNotificationsinYourApp.html#//apple_ref/doc/uid/TP40008194-CH4-SW10.
     * @var int $badge = null The value of the badge on the home screen app icon. If not specified (null), the badge is not changed.
     * If set to 0, the badge is removed.
     * @var string $clickAction = '' The action associated with a user click on the notification. Corresponds to category in the APNs payload.
     * @var string $subTitle = '' The notification's subtitle.
     * @var bool $silent = false Removes sound, badge and sends content-available as 1
     * @var string $bodyLocKey = '' The key to the body string in the app's string resources to use to localize the body text to the user's current localization.
     * Corresponds to loc-key in the APNs payload.
     * @var array|string[] $bodyLocArgs = array Variable string values to be used in place of the format specifiers in body_loc_key to use to localize the body text to the user's current localization.
     * Corresponds to loc-args in the APNs payload.
     * @var string $titleLocKey = '' The key to the title string in the app's string resources to use to localize the title text to the user's current localization.
     * Corresponds to title-loc-key in the APNs payload.
     * @var array|string[] $titleLocArgs = []
     */
    public function __construct(
        string $title = '',
        string $body = '',
        string $sound = '',
        int $badge = null,
        string $clickAction = '',
        string $subTitle = '',
        bool $silent = false,
        string $bodyLocKey = '',
        array $bodyLocArgs = [],
        string $titleLocKey = '',
        array $titleLocArgs = []
    )
    {
        parent::__construct($title, $body, $sound, $clickAction, $bodyLocKey, $bodyLocArgs, $titleLocKey, $titleLocArgs);
        $this->badge = $badge;
        $this->subTitle = $subTitle;
        $this->silent = $silent;
    }

    /**
     * The value of the badge on the home screen app icon. If not specified (null), the badge is not changed.
     * If set to 0, the badge is removed.
     * @param int $badge
     * @return IosFcmNotification
     */
    public function setBadge(int $badge = null): IosFcmNotification
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * The notification's subtitle.
     * @param string $subTitle
     * @return IosFcmNotification
     */
    public function setSubTitle(string $subTitle): IosFcmNotification
    {
        $this->subTitle = $subTitle;

        return $this;
    }

    /**
     * Removes sound, badge and sends content-available as 1
     * @param bool $silent
     * @return IosFcmNotification
     */
    public function setSilent(bool $silent): IosFcmNotification
    {
        $this->silent = $silent;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSilent(): bool
    {
        return $this->silent;
    }

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
        if ($this->silent) {
            $jsonData['content-available'] = 1;
            unset($jsonData['sound']);
        } elseif ($this->badge !== null) {
            $jsonData['badge'] = $this->badge;
        }
        if ($this->subTitle !== '') {
            $jsonData['sub_title'] = $this->subTitle;
        }

        return $jsonData;
    }
}