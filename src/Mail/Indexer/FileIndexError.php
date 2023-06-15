<?php

namespace NotFound\Framework\Mail\Indexer;

use Illuminate\Mail\Mailable;

class FileIndexError extends Mailable
{
    private $error;

    private $document;

    private $server;

    private $title;

    /**
     * Create a new message instance.
     *
     * @param  \NotFound\Framework\Models\Form  $form
     * @return void
     */
    public function __construct(string $error, $server, $document, $title)
    {
        $this->error = $error;
        $this->document = $document;
        $this->server = $server;
        $this->title = $title;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('siteboss::mail.indexer.file-index-error')
            ->subject('File index error')
            ->with([
                'error' => $this->error,
                'server' => $this->server,
                'document' => $this->document,
                'title' => $this->title,
            ]);
    }
}
