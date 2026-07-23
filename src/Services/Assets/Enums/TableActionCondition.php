<?php

namespace NotFound\Framework\Services\Assets\Enums;

enum TableActionCondition: string
{
    case CREATE = 'create';
    case EDIT = 'edit';
    case ARCHIVE = 'archive';
    case DELETE = 'delete';

    /**
     * The column on the site table that holds the moment this condition
     * refers to. A record qualifies when this column has not changed for
     * the configured number of days.
     */
    public function timestampColumn(): string
    {
        return match ($this) {
            self::CREATE => 'created_at',
            self::EDIT => 'updated_at',
            self::ARCHIVE => 'archived_at',
            self::DELETE => 'deleted_at',
        };
    }

    /**
     * How the condition is presented in the CMS editor.
     */
    public function label(): string
    {
        return match ($this) {
            self::CREATE => 'Created',
            self::EDIT => 'Last edited',
            self::ARCHIVE => 'Archived',
            self::DELETE => 'Deleted',
        };
    }
}
