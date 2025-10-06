<?php

namespace NotFound\Framework\Models;

class PageInfo
{
    private ?string $metaTitle = null;

    private ?string $metaDescription = null;

    private ?string $metaImage = null;

    private array $keywords = [];

    public function __construct(
        private ?string $title = '',
        private ?string $description = ''
    ) {}

    public function title()
    {
        return $this->title ?? config('app.name');
    }

    public function fullTitle()
    {
        return $this->title ? $this->title.' - '.config('app.name') : config('app.name');
    }

    public function description()
    {
        return $this->description;
    }

    public function keywords()
    {
        return implode(', ', $this->keywords);
    }

    public function metaTitle(): string
    {
        return $this->metaTitle ?? $this->title();
    }

    public function metaDescription(): string
    {
        return $this->metaDescription ?? $this->description();
    }

    public function metaImage(): ?string
    {
        return $this->metaImage;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setMetaTitle(string $title): self
    {
        $this->metaTitle = $title;

        return $this;
    }

    public function setMetaDescription(string $description): self
    {
        $this->metaDescription = $description;

        return $this;
    }

    public function setMetaImage(string $image): self
    {
        $this->metaImage = $image;

        return $this;
    }

    public function addKeyword(string|array $keyword): self
    {
        if (is_array($keyword)) {
            $this->keywords = array_merge($this->keywords, $keyword);

            return $this;
        }
        $this->keywords[] = $keyword;

        return $this;
    }
}
