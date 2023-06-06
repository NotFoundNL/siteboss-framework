<?php

namespace NotFound\Framework\Events;

use NotFound\Framework\Models\BaseModel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BeforeSaveEvent
{
    use Dispatchable, SerializesModels;

    private BaseModel $model;

    /**
     * Create a new event instance.
     *
     * @param $model is an model of Table or Strings that they implements IAsset
     * @return void
     */
    public function __construct(BaseModel $model)
    {
        $this->model = $model;
    }
}
