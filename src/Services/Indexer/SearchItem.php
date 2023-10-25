<?php

namespace NotFound\Framework\Services\Indexer;

use DateTime;
use NotFound\Framework\Models\Lang;

final class SearchItem
{
    private string $type = 'page';

    private ?string $language;

    private ?string $content;

    private ?string $image;

    private bool $inSitemap = true;

    private ?DateTime $publicationDate;

    private ?DateTime $lastUpdated;

    private int $priority = 1;

    private array $customValues = [];

    private ?string $filePath;

    // Minimum required fields

    public function __construct(private string $url, private string $title)
    {
    }

    // Setters

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setImage(string $imageUrl): self
    {
        $this->image = $imageUrl;

        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function hideFromSiteMap(): self
    {
        $this->inSitemap = false;

        return $this;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function setFile(string $filePath): self
    {
        $this->type = 'file';
        $this->filePath = $filePath;

        return $this;
    }

    public function setLastUpdated(?DateTime $lastUpdated): self
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    public function setCustomValue(string $key, mixed $value): self
    {
        $this->customValues[$key] = $value;

        return $this;
    }

    public function setPriority(mixed $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function setPublicationDate(?DateTime $publicationDate): self
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    // Getters

    public function language(): ?string
    {
        return $this->language ?? Lang::default()->url;
    }

    public function image(): ?string
    {
        return $this->image;
    }

    public function url(): ?string
    {
        return $this->url;
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function content(): ?string
    {
        return $this->content;
    }

    /**
     * publicationDate
     *
     * Get the publication date of the content
     * This can be used for sorting and prioritizing content
     */
    public function publicationDate(): ?string
    {
        return $this->publicationDate ?? $this->lastUpdated;
    }

    /**
     * lastUpdated
     *
     * This is when the content was last updated
     * This is used to determine if the content needs to be re-indexed
     */
    public function lastUpdated(): ?string
    {
        return $this->lastUpdated ?? $this->publicationDate;
    }

    public function customValues(): ?array
    {
        return $this->customValues;
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function file(): ?string
    {
        return $this->filePath;
    }

    public function sitemap(): bool
    {
        return $this->inSitemap;
    }
}
