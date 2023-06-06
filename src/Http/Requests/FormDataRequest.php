<?php

namespace NotFound\Framework\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class FormDataRequest extends FormRequest
{
    public function rules()
    {
        return [
            'payload' => 'json',
        ];
    }

    protected function prepareForValidation(): void
    {
        try {
            $this->merge(json_decode($this->payload, true, 512, JSON_THROW_ON_ERROR));
        } catch (\Exception $e) {
            Log::warning(' No payload found');
        }
    }
}
