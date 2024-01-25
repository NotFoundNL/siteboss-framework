<?php

namespace NotFound\Framework\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NotFound\Framework\Models\BaseModel;

class AfterSaveEvent
{
    use Dispatchable, SerializesModels;

    private BaseModel $model;

    /**
     * Create a new event instance.
     *
     * @param  $model  is an model of Table or Strings that they extends from BaseModel
     * @return void
     */
    public function __construct(BaseModel $model)
    {
        $this->model = $model;
    }
}
