<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace Granam\FirebaseCloudMessaging\Target;

class FcmDeviceTarget extends FcmTarget
{
    private $deviceToken;

    public function __construct(string $deviceToken)
    {
        $this->deviceToken = $deviceToken;
    }

    public function getDeviceToken(): string
    {
        return $this->deviceToken;
    }
}