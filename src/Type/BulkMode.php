<?php

declare(strict_types=1);

namespace LapostaApi\Type;

enum BulkMode: string
{
    case ADD = 'add';
    case ADD_AND_EDIT = 'add_and_edit';
    case EDIT = 'edit';
}
