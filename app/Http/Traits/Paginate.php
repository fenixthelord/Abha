<?php

namespace App\Http\Traits;

trait Paginate
{
    public function allWithSearch($items,$fields = [] ,$request)
    {
        $page = is_int($request->page) ? $request->page : 1;
        $perPage = is_int($request->per_page) ? $request->per_page : 10;
        $fields = is_array($fields) ? $fields : [];
        $search = request()->input('search', null);
        $data = $items->query()
            ->when($search, function ($query) use ($search,$fields) {
                $query->whereAny($fields, 'like', '%' . $search . '%');
            });
        $results = $data->paginate($perPage ,['*'] ,'page' ,$page);
        if ($page > $results->lastPage()) {
            $results = $data->paginate($perPage, ['*'], 'page', $results->lastPage());
        }
        return $results;
    }
}
