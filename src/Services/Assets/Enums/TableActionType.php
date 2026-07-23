<?php

namespace NotFound\Framework\Services\Assets\Enums;

enum TableActionType: string
{
    case ARCHIVE = 'archive';
    case DELETE = 'delete';
    case PURGE = 'purge';

    /**
     * How the action is presented in the CMS editor.
     */
    public function label(): string
    {
        return match ($this) {
            self::ARCHIVE => 'Archive',
            self::DELETE => 'Delete',
            self::PURGE => 'Purge, permanently removes the record',
        };
    }
}
