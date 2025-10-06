<?php

namespace NotFound\Framework\Mail\Indexer;

use Illuminate\Mail\Mailable;

class QueryError extends Mailable
{
    /**
     * Create a new message instance.
     *
     * @param  \NotFound\Framework\Models\Form  $form
     * @return void
     */
    public function __construct(
        private string $query,
        private string $result,
        private string $server
    ) {}

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('siteboss::mail.indexer.query-error')
            ->subject('Query error')
            ->with([
                'query' => $this->query,
                'result' => $this->result,
                'server' => $this->server,
            ]);
    }
}
