<?php

namespace App\Http\Traits;

trait PaginateTrait
{
    public function allWithSearch($items, $fields = [], $request, $filters = [])
    {
        $page = intval($request->get('page', 1));
        $perPage = intval($request->get('per_page', 10));
        $search = $request->input('search', null);


        $query = $items->query()->when($search, function ($q) use ($search, $fields) {
            $q->whereAny($fields, 'like', '%' . $search . '%');
        });

        foreach ($filters as $column => $value) {
            if (!empty($value)) {
                $query->where($column, $value);
            }
        }

        $results = $query->paginate($perPage, ['*'], 'page', $page);

        if ($page > $results->lastPage()) {
            $results = $query->paginate($perPage, ['*'], 'page', $results->lastPage());
        }

        return $results;
    }
}
