<?php

namespace NotFound\Framework\Services\Assets;

use Illuminate\Support\Str;

class AssetValues
{
    public function __construct(
        private array $cachedValues
    ) {
    }

    public function __get(string $key): mixed
    {
        if (isset($this->cachedValues[$key]) && isset($this->cachedValues[$key]->val)) {
            return $this->cachedValues[$key]->val;
        }

        if (app()->hasDebugModeEnabled()) {
            return sprintf('[KEY NAME NOT FOUND: %s]', $key);
        }

        return '';
    }

    public function raw(string $key): mixed
    {
        if (isset($this->cachedValues[$key]) && isset($this->cachedValues[$key]->val)) {
            return $this->cachedValues[$key]->val;
        }

        return null;
    }

    public function list(): string
    {
        if (! app()->hasDebugModeEnabled()) {
            return '';
        }

        $html = '<ul>';
        foreach ($this->cachedValues as $key => $value) {
            $stringValue = '';
            if (is_string($value->val)) {
                $stringValue = e(Str::limit($value->val, 50));
            } else {
                $stringValue = json_encode($value->val);
            }

            $html .= sprintf(
                '
                <li>
                    <strong>{{ $p->%s) }}(%s)</strong>: %s
                </li>',
                $key,
                $value->type,
                $stringValue,
            );
        }
        $html .= '</ul>';

        return $html;
    }
}
