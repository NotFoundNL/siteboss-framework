<?php

namespace NotFound\Framework\Interfaces;

interface FormHandlerInterface
{
    public function __construct(string $langUrl, $formInfo, $formValidator);

    public function run(): bool;
}
