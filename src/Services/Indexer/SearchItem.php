<?php

namespace NotFound\Framework\Services\Indexer;

class SearchItem
{
    protected ?string $content = null;

    protected ?string $image = null;

    protected string $type = 'page';

    protected ?string $language = null;

    protected ?string $created_at = null;

    protected int $priority = 1;

    protected array $customValues = [];

    public function __construct(protected string $url, protected string $title)
    {
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function setCreatedAt(string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function setCustomValue(string $key, string $value): self
    {
        $this->customValues[$key] = $value;

        return $this;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function setPriorityHigh(): self
    {
        trigger_error('Method '.__METHOD__.' is not implemented for production use', E_USER_DEPRECATED);
        $this->priority = 2;

        return $this;
    }

    public function get(): object
    {
        return $this;
    }
}
