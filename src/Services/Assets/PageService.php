<?php

namespace NotFound\Framework\Services\Assets;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Menu;
use NotFound\Framework\Models\Strings;
use NotFound\Framework\Services\Assets\Components\AbstractComponent;
use NotFound\Framework\Services\Assets\Enums\AssetType;
use NotFound\Framework\Services\Assets\Enums\TemplateType;
use NotFound\Framework\Services\Indexer\ContentBlockService;
use NotFound\Layout\Inputs\LayoutInputCheckbox;
use NotFound\Layout\Inputs\LayoutInputSlug;
use NotFound\Layout\Inputs\LayoutInputText;
use stdClass;

class PageService extends AbstractAssetService
{
    protected Collection $fieldComponents;

    protected Collection $staticInputValues;

    protected bool $valuesSet = false;

    private array $indexableTypes = ['Text'];

    public function __construct(
        private Menu $menu,
        protected Lang $lang,
    ) {
        $menu->with('template');
        if (! $template = $menu->template) {
            abort(500, 'No template set');
        }

        $this->assetModel = $template;
        $this->fieldComponents = $this->getFieldComponents($menu->id);
        $this->staticInputValues = new Collection;
    }

    public function getProperty(string $property): mixed
    {
        $p = $this->menu->template?->properties;

        return $p->$property ?? null;
    }

    public function getComponents(): Collection
    {
        $this->setCurrentValues();

        return $this->fieldComponents;
    }

    public function getType(): AssetType
    {
        return AssetType::PAGE;
    }

    public function getStaticInputs(): Collection
    {
        $collect = new collection;

        $title = new LayoutInputText('__template_title', __('siteboss::page.pageTitle'));
        $titleModel = Strings::whereTable('menu')
            ->whereName('name')
            ->whereLangId($this->lang->id)
            ->whereStringId($this->menu->id)
            ->first();
        $title->setRequired();
        $title->setValue($titleModel->value ?? '');
        $collect->add($title);

        $slug = new LayoutInputSlug('__template_slug', __('siteboss::page.slug'));
        $slug->setRequired();
        $slug->setDescription(__('siteboss::page.slugDescription'));
        $slug->setValue($this->menu->url ?? '');
        $collect->add($slug);

        $active = new LayoutInputCheckbox('__template_active', __('siteboss::page.active'));
        $active->setValue((bool) $this->menu->enabled ?? true);
        $collect->add($active);

        $menu = new LayoutInputCheckbox('__template_menu', __('siteboss::page.inMenu'));
        $menu->setValue((bool) $this->menu->menu ?? true);
        $collect->add($menu);

        return $collect;
    }

    public function getMetaInputs(): Collection
    {
        $strings = Strings::whereTable('meta')
            ->whereLangId($this->lang->id)
            ->whereStringId($this->menu->id)
            ->get();

        $metaStrings = new stdClass;
        foreach ($strings as $string) {
            $metaStrings->{$string->name} = $string->value;
        }

        $collect = new collection;
        $metaTitleInput = new LayoutInputText('__meta_title', __('siteboss::page.meta_title'));
        $metaTitleInput->setPlaceholder(__('siteboss::page.meta_title_placeholder'));
        $metaTitleInput->setValue($metaStrings->title ?? '');
        $collect->add($metaTitleInput);

        $metaDescriptionInput = new LayoutInputText('__meta_description', __('siteboss::page.meta_description'));
        $metaDescriptionInput->setValue($metaStrings->description ?? '');
        $collect->add($metaDescriptionInput);

        return $collect;
    }

    public function validate(FormDataRequest $request): bool
    {
        // Update meta values
        if ($this->getProperty('meta') === true) {
            // BUG: TODO: Validate meta values
            $metaTitle = $request->{'__meta_title'};
            $metaDescription = $request->{'__meta_description'};

            // TODO: huh, why is this is validate
            Strings::upsert(
                [
                    [
                        'table' => 'meta',
                        'name' => 'title',
                        'string_id' => $this->menu->id,
                        'lang_id' => $this->lang->id,
                        'value' => $metaTitle,
                    ],
                    [
                        'table' => 'meta',
                        'name' => 'description',
                        'string_id' => $this->menu->id,
                        'lang_id' => $this->lang->id,
                        'value' => $metaDescription,
                    ],
                ],
                ['table', 'name', 'string_id', 'lang_id']
            );
        }

        $this->staticInputValues['__template_title'] = $request->{'__template_title'};
        $this->staticInputValues['__template_slug'] = Str::limit(Str::slug($request->{'__template_slug'}), 33, '');
        $this->staticInputValues['__template_active'] = $request->{'__template_active'};
        $this->staticInputValues['__template_menu'] = $request->{'__template_menu'};

        foreach ($this->getComponents() as $component) {
            /** @var AbstractComponent $component */
            $component->setNewValue($request->{$component->assetItem->internal});

            if ($component->isDisabled()) {
                continue;
            }

            if (! $component->validate($request->{$component->assetItem->internal})) {
                return false;
            }
        }

        // TODO: only update cache if the slug had changed.
        Menu::removeRouteCache();

        return true;
    }

    public function create(): int
    {
        return $this->updateModel();
    }

    public function update(): int
    {
        return $this->updateModel();
    }

    public function delete(): void
    {
        // $this->assetModel->deleteRecord($this->recordId); //, $langUrl);
    }

    protected function updateModel(): int
    {
        foreach ($this->getComponents() as $component) {
            $component->beforeSave();
        }

        $id = $this->upsertTemplateItemsStrings();
        $this->upsertStaticInputs();

        foreach ($this->getComponents() as $component) {
            $component->save();
        }

        Cache::forget($this->getCacheKey());
        Menu::removeRouteCache();

        $this->menu->touch();

        foreach ($this->getComponents() as $component) {
            $component->afterSave();
        }

        return $id;
    }

    private function upsertStaticInputs(): void
    {
        $this->staticInputValues['__template_active'];

        $menu = $this->menu;

        $this->staticInputValues['__template_slug'] = preg_replace('/ /', '-', preg_replace('/[ ]{2,100}/', ' ', trim(preg_replace('/[^a-z0-9]/', ' ', strtolower($this->staticInputValues['__template_slug'])))));

        $newValue = $this->staticInputValues['__template_slug'];

        if (Menu::where('id', '!=', $this->menu->id)->where('parent_id', $this->menu->parent_id)->where('url', $this->staticInputValues['__template_slug'])->first()) {
            if ($slug = Menu::where('id', '!=', $this->menu->id)
                ->where('parent_id', $this->menu->parent_id)
                ->where(function ($q) {
                    return $q
                        ->where('url', $this->staticInputValues['__template_slug'])
                        ->orWhere('url', 'regexp', $this->staticInputValues['__template_slug'].'\-[0-9]+');
                })
                ->orderBy('url', 'DESC')
                ->first()
            ) {
                $highestSlug = explode('-', $slug->url);
                $highestSlug = end($highestSlug);
                if (is_numeric($highestSlug)) {
                    $newValue .= '-'.($highestSlug + 1);
                } else {
                    $newValue .= '-1';
                }
            }
        }

        $menu->url = ($menu->url != $newValue) ? $newValue : $menu->url;
        $menu->menu = (bool) $this->staticInputValues['__template_menu'];
        $menu->enabled = $this->staticInputValues['__template_active'];
        $menu->save();

        $titleValue = $this->staticInputValues['__template_title'];
        $pageTitle = Strings::whereTable('menu')
            ->whereName('name')
            ->whereStringId($this->menu->id)
            ->whereLangId($this->lang->id)
            ->first();

        if ($pageTitle) {
            $pageTitle->value = $titleValue;
            $pageTitle->save();
        } else {
            Strings::create([
                'table' => 'menu',
                'name' => 'name',
                'string_id' => $this->menu->id,
                'lang_id' => $this->lang->id,
                'value' => $titleValue,
            ]);
        }
    }

    private function upsertTemplateItemsStrings(): int
    {
        $models = [];
        foreach ($this->getComponents() as $component) {
            /** @var AbstractComponent $component */
            if (
                ! $component->usesDefaultStorageMechanism()
                || $component->isDisabled()
            ) {
                continue;
            }

            $langId = $this->lang->id;
            if (! $component->isLocalized()) {
                $langId = 0;
            }

            $models[] = [
                'table' => 'template',
                'name' => $component->assetItem->internal,
                'lang_id' => $langId,
                'string_id' => $component->getRecordId(),
                'value' => $component->getValueForStorage(),
            ];
        }

        return Strings::upsert($models, ['table', 'lang_id', 'string_id'], ['value']);
    }

    /**
     * Set current values for the components
     */
    private function setCurrentValues(): void
    {
        if ($this->valuesSet) {
            return;
        }

        $strings = Strings::whereTable(TemplateType::TEMPLATE)
            ->where(function ($query) {
                $query->where('string_id', $this->menu->id)
                    ->orWhere('string_id', '=', 0);
            })
            ->where(function ($query) {
                $query->where('lang_id', $this->lang->id)
                    ->orWhere('lang_id', '=', 0);
            })
            ->get();

        $this->fieldComponents->transform(function ($component) use ($strings) {
            /** @var AbstractComponent $component */
            $string = $strings->where('name', $component->assetItem->internal)->first();
            $component->setValueFromStorage($string->value ?? '');

            return $component;
        });

        $this->valuesSet = true;
    }

    protected function getCacheKey(): string
    {
        return 'page_'.$this->lang->url.'_'.$this->menu->id;
    }

    public function getCachedValues(): array
    {
        $secondsToRemember = 7 * 24 * 60 * 60;

        $key = $this->getCacheKey();

        return Cache::remember($key, $secondsToRemember, function () {
            $array = [];

            foreach ($this->getComponents() as $component) {
                /** @var AbstractComponent $component */
                $array[$component->assetItem->internal] = (object) [
                    'type' => $component->getFieldType(),
                    'properties' => $component->properties(),
                    'val' => $component->getDisplayValue(),
                ];
            }

            // include meta values for the page
            $metaStrings = Strings::whereTable('meta')
                ->whereLangId($this->lang->id)
                ->whereStringId($this->menu->id)
                ->get();

            foreach ($metaStrings as $string) {
                $array['meta'.$string->name] = (object) [
                    'type' => 'Text',
                    'properties' => new stdClass,
                    'val' => $string->value,
                ];
            }

            return $array;
        });
    }

    public function getContentForIndexer(): string
    {
        $searchText = '';
        $values = $this->getCachedValues();

        foreach ($values as $value) {
            if (! (isset($value->properties->noIndex)) || $value->properties->noIndex === 0) {

                if ($value->type == 'Text') {
                    if ($value->val !== null) {
                        $searchText .= $value->val.' ';
                    }
                } elseif ($value->type == 'ContentBlocks') {
                    $cbs = new ContentBlockService($value->val);
                    $searchText .= $cbs->toText().' ';
                }
            }
        }

        return trim($searchText);
    }
}
