<?php

declare(strict_types=1);

namespace LapostaApi\Type;

/**
 * Enum containing the supported actions when syncing list members.
 */
enum SyncAction: string
{
    /** Add members that are not yet in the list. */
    case ADD = 'add';

    /** Update existing members based on their identifier. */
    case UPDATE = 'update';

    /**
     * Unsubscribe members that are excluded from the payload.
     *
     * When this action is included in the sync payload, the Laposta API unsubscribes every existing
     * relation that is not part of the provided members collection.
     */
    case UNSUBSCRIBE_EXCLUDED = 'unsubscribe_excluded';
}
