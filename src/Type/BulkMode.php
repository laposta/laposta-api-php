<?php

declare(strict_types=1);

namespace LapostaApi\Type;

/**
 * Enum representing the possible bulk operation modes.
 *
 * This enum is used to specify the behavior when performing bulk operations
 * on resources in the Laposta API.
 */
enum BulkMode: string
{
    /**
     * Only add new records, skip existing ones.
     */
    case ADD = 'add';

    /**
     * Add new records and update existing ones.
     */
    case ADD_AND_EDIT = 'add_and_edit';

    /**
     * Only update existing records, skip new ones.
     */
    case EDIT = 'edit';
}
