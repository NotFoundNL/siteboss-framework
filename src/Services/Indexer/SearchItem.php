<?php

namespace NotFound\Framework\Services\Indexer;

class SearchItem
{
    protected ?string $content = null;

    protected ?string $image = null;

    protected ?string $updated = null;

    protected string $type = 'page';

    protected bool $inSitemap = true;

    protected ?string $language = null;

    protected ?string $created_at = null;

    protected int $priority = 1;

    protected bool $isFile = false;

    protected array $customValues = [];

    protected ?string $filePath = null;

    protected ?string $solrDate = null;

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

    public function setUpdated(?string $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setInSitemap(bool $inSitemap): self
    {
        $this->inSitemap = $inSitemap;

        return $this;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function setIsFile(bool $isFile): self
    {
        $this->isFile = $isFile;

        return $this;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

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

    public function setSolrDate(string $solrDate): self
    {
        $this->solrDate = $solrDate;

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

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getUpdated(): ?string
    {
        return $this->updated;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function getCustomValues(): ?array
    {
        return $this->customValues;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function getSolrDate(): ?string
    {
        return $this->solrDate;
    }

    public function getInSitemap(): bool
    {
        return $this->inSitemap;
    }
}
