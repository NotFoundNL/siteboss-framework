<?php

namespace NotFound\Framework\Helpers\Layout\Validations;

/**
 * IsText
 *
 * This class will validate the input is of type string.
 * This will be used for validation in Inputs.
 *
 * Laravel validation is used under the hood.
 */
class IsText extends AbstractValidation
{
    public function validate($newValue)
    {
        if ($newValue != null && ! is_string($newValue)) {
            abort(200, __('validation.string', ['attribute' => $this->input->internal]));
        }

        return true;
    }
}
