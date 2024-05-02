<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Doctrine;

enum IndexOption
{
    case UseOnlyAsOutbox;
    case AccessByStreamId;
    case EnforceUniqueStreamVersionPerStreamId;
}
