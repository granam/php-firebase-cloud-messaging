<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace Granam\FirebaseCloudMessaging\Exceptions;

class MissingMultipleTopicsCondition extends \LogicException implements Logic
{

}