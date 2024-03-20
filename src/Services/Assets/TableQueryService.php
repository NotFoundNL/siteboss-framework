<?php

namespace NotFound\Framework\Services\Assets;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Services\Assets\Components\AbstractComponent;
use NotFound\Framework\Services\Legacy\StatusColumn;

class TableQueryService
{
    protected $numberOfRecordPerPage;

    public function __construct(
        private Table $table,
        private Collection $components
    ) {
        $this->setRecordPerPage();
    }

    public function getSiteTableRows()
    {
        $siteTableRowsQuery = StatusColumn::wherePublished(DB::table($this->table->getSiteTableName()), $this->table->getSiteTableName());

        $siteTableRowsQuery = $this->setFilter($siteTableRowsQuery);

        $siteTableRowsQuery = $this->setOrdering($siteTableRowsQuery);

        $siteTableRowsQuery = $this->setSearch($siteTableRowsQuery);

        if ($this->table->isLocalized()) {
            $siteTableRowsQuery = $this->joinLocalize($siteTableRowsQuery);
        }

        $siteTableRowsPaginator = $this->paginate($siteTableRowsQuery);

        return $siteTableRowsPaginator;
    }

    private function setOrdering(Builder $query): Builder
    {
        // TODO: Find out why 'sort' is assigned the string value 'null'
        $orderColumn = (request()->query('sort') !== null && request()->query('sort') !== 'null') ? request()->query('sort') : null;

        if ($this->table->isOrdered() && ! $orderColumn) {
            return $query->orderBy('order', 'ASC');
        }

        if ($orderColumn !== null) {
            $order = 'ASC';
            if (request()->query('asc') && request()->query('asc') === 'false') {
                $order = 'DESC';
            }

            return $query->orderBy($orderColumn, $order);
        }

        return $query;
    }

    private function setSearch(Builder $query): Builder
    {
        if (request()->query('search')) {
            $query->where(function ($query) {
                foreach ($this->components as $component) {
                    /** @var AbstractComponent $component */
                    if ($component->assetItem->isSearchable()) {
                        $query->orWhere($component->assetItem->internal, 'LIKE', '%'.request()->query('search').'%');
                    }
                }
            });
        }

        return $query;
    }

    private function setFilter(Builder $query)
    {
        if (request()->query('filter')) {
            foreach (request()->query('filter') as $key => $value) {
                if ($this->table->items()->where('type', 'Filter')->where('internal', $key)->first()) {
                    $query->where($key, '=', $value);
                }
            }
        }

        return $query;
    }

    public function setRecordPerPage()
    {
        $amount = request()->query('pitems') ?? $this->table->properties->itemsPerPage ?? 25;
        if (! $amount || ! is_numeric($amount)) {
            $amount = 25;
        }
        $this->numberOfRecordPerPage = $amount;
    }

    private function paginate(Builder $query)
    {
        return $query->paginate($this->numberOfRecordPerPage);
    }

    private function joinLocalize(Builder $query): Builder
    {
        $siteTableNameTr = $this->table->getSiteTableName().'_tr';

        $query->leftJoin($siteTableNameTr, $this->table->getSiteTableName().'.id', '=', $siteTableNameTr.'.entity_id')
            ->where($siteTableNameTr.'.lang_id', Lang::current()->id)
            ->select($siteTableNameTr.'.*', $this->table->getSiteTableName().'.*');

        return $query;
    }
}
