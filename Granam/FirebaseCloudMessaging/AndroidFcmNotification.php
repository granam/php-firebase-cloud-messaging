<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace Granam\FirebaseCloudMessaging;

/**
 * @link https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support
 */
class AndroidFcmNotification extends DeviceFcmNotification
{
    /** @var string */
    private $androidChannelId;
    /** @var string */
    private $icon;
    /** @var string */
    private $tag;
    /** @var string */
    private $color;

    /**
     * @var string $title = '' The notification's title. This field is not visible on iOS phones and tablets.
     * @var string $body = '' The notification's body text.
     * @var string $androidChannelId = '' The notification's channel id (new in Android O).
     * https://developer.android.com/preview/features/notification-channels.html
     * The app must create a channel with this ID before any notification with this key is received.
     * If you don't send this key in the request, or if the channel id provided has not yet been created by your app, FCM uses the channel id specified in your app manifest.
     * @var string $icon The notification's icon.
     * Sets the notification icon to myicon for drawable resource myicon. If you don't send this key in the request, FCM displays the launcher icon specified in your app manifest.
     * @var string $sound = '' The sound to play when the device receives the notification.
     * Supports "default" or the filename of a sound resource bundled in the app. Sound files must reside in /res/raw/.
     * @var string $tag = '' Identifier used to replace existing notifications in the notification drawer.
     * If not specified, each request creates a new notification.
     * If specified and a notification with the same tag is already being shown, the new notification replaces the existing one in the notification drawer.
     * @var string $color The notification's icon color, expressed in #rrggbb format.
     * @var string $clickAction = ''  The action associated with a user click on the notification.
     * If specified, an activity with a matching intent filter is launched when a user clicks on the notification.
     * @var string $bodyLocKey = '' The key to the body string in the app's string resources to use to localize the body text to the user's current localization.
     * See String Resources for more information, https://developer.android.com/guide/topics/resources/string-resource.html
     * @var array $bodyLocArgs = []  Variable string values to be used in place of the format specifiers in body_loc_key to use to localize the body text to the user's current localization.
     * See Formatting and Styling for more information, https://developer.android.com/guide/topics/resources/string-resource.html#FormattingAndStyling
     * @var string $titleLocKey = ''  The key to the title string in the app's string resources to use to localize the title text to the user's current localization.
     * See String Resources for more information, https://developer.android.com/guide/topics/resources/string-resource.html
     * @var array $titleLocArgs = [] Variable string values to be used in place of the format specifiers in title_loc_key to use to localize the title text to the user's current localization.
     * See Formatting and Styling for more information, https://developer.android.com/guide/topics/resources/string-resource.html#FormattingAndStyling
     */
    public function __construct(
        string $title = '',
        string $body = '',
        string $androidChannelId = '',
        string $icon = '',
        string $sound = '',
        string $tag = '',
        string $color = '',
        string $clickAction = '',
        string $bodyLocKey = '',
        array $bodyLocArgs = [],
        string $titleLocKey = '',
        array $titleLocArgs = []
    )
    {
        parent::__construct($title, $body, $sound, $clickAction, $bodyLocKey, $bodyLocArgs, $titleLocKey, $titleLocArgs);
        $this->androidChannelId = $androidChannelId;
        $this->icon = $icon;
        $this->tag = $tag;
        $this->color = $color;
    }

    /**
     * The notification's channel id (new in Android O).
     * https://developer.android.com/preview/features/notification-channels.html
     * The app must create a channel with this ID before any notification with this key is received.
     * If you don't send this key in the request, or if the channel id provided has not yet been created by your app, FCM uses the channel id specified in your app manifest.
     * @param string $androidChannelId
     * @return FcmNotification
     */
    public function setAndroidChannelId(string $androidChannelId): FcmNotification
    {
        $this->androidChannelId = $androidChannelId;

        return $this;
    }

    /**
     * The notification's icon.
     * Sets the notification icon to myicon for drawable resource myicon. If you don't send this key in the request,
     * FCM displays the launcher icon specified in your app manifest.
     *
     * @param string $icon
     * @return FcmNotification
     */
    public function setIcon(string $icon): FcmNotification
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Identifier used to replace existing notifications in the notification drawer.
     * If not specified, each request creates a new notification.
     * If specified and a notification with the same tag is already being shown, the new notification replaces the existing one in the notification drawer.
     * @param string $tag
     * @return FcmNotification
     */
    public function setTag(string $tag): FcmNotification
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Android only, set the notification's icon color, expressed in #rrggbb format.
     *
     * @param string $color
     * @return FcmNotification
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\InvalidRgbFormatOfAndroidColor
     */
    public function setColor(string $color): FcmNotification
    {
        if ($color !== '' && !\preg_match('~^#[0-9a-fA-F]{6}$~', $color)) {
            throw new Exceptions\InvalidRgbFormatOfAndroidColor(
                "Expected something like '#6563a4' because of required #rrggbb format, got '{$color}'"
            );
        }
        $this->color = $color;

        return $this;
    }

    /**
     * An Android push message with 'notification' is NOT silent.
     * https://stackoverflow.com/questions/36555653/push-silent-notification-through-gcm-to-android-ios
     *
     * @return bool
     */
    public function isSilent(): bool
    {
        return false;
    }

    /**
     * An Android push message with 'notification' is NOT silent.
     * https://stackoverflow.com/questions/36555653/push-silent-notification-through-gcm-to-android-ios
     *
     * @return bool
     */
    public function canBeSilenced(): bool
    {
        return false;
    }

    /**
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\AndroidFcmNotificationCanNotBeSilenced
     */
    public function setSilent()
    {
        throw new Exceptions\AndroidFcmNotificationCanNotBeSilenced(
            'Whenever an Android notification is used, then notification is not silent, but shown to the user.'
            . ' Just send ' . FcmMessage::class . ' without ' . static::class
        );
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
        if ($this->androidChannelId !== '') {
            $jsonData['android_channel_id'] = $this->androidChannelId;
        }
        if ($this->icon !== '') {
            $jsonData['icon'] = $this->icon;
        }
        if ($this->tag !== '') {
            $jsonData['tag'] = $this->tag;
        }
        if ($this->color !== '') {
            $jsonData['color'] = $this->color;
        }

        return $jsonData;
    }

}