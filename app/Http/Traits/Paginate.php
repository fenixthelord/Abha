<?php

namespace App\Http\Traits;

trait Paginate
{
    public function allWithSearch($items,$fields = [] ,$request)
    {
        $page = intval($request->get('page',1));
        $perPage = intval($request->get('perPage',10));
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
