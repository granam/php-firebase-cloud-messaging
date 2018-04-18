<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace Granam\FirebaseCloudMessaging;

/**
 * @link https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support
 */
class JsFcmNotification extends FcmNotification
{
    /** @var string */
    private $icon;

    /**
     * @param string $title The notification's title.
     * @param string $body The notification's body text.
     * @param string $icon The URL to use for the notification's icon.
     * @param string $clickAction The action associated with a user click on the notification.
     * For all URL values, secure HTTPS is required.
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ClickActionForJavascriptFcmNotificationRequiresValidUrl
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ClickActionForJavascriptFcmNotificationHasToBeOnHttps
     */
    public function __construct(
        string $title = '',
        string $body = '',
        string $icon = '',
        string $clickAction = ''
    )
    {
        $this->checkHttpsInClickAction($clickAction);
        parent::__construct($title, $body, $clickAction);
        $this->icon = $icon;
    }

    /**
     * @param string $clickAction
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ClickActionForJavascriptFcmNotificationRequiresValidUrl
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ClickActionForJavascriptFcmNotificationHasToBeOnHttps
     */
    private function checkHttpsInClickAction(string $clickAction): void
    {
        if ($clickAction === '') {
            return;
        }
        if (\filter_var($clickAction, FILTER_VALIDATE_URL) === false) {
            throw new Exceptions\ClickActionForJavascriptFcmNotificationRequiresValidUrl(
                "JS click action requires URL with https, got '$clickAction'"
            );
        }
        if (\strpos($clickAction, 'https://') !== 0) {
            throw new Exceptions\ClickActionForJavascriptFcmNotificationHasToBeOnHttps(
                "JS click action requires URL with https, got '$clickAction'"
            );
        }
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
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
        if ($this->icon !== '') {
            $jsonData['icon'] = $this->icon;
        }

        return $jsonData;
    }

}