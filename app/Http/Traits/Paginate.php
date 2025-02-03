<?php

namespace App\Http\Traits;

use function PHPUnit\Framework\isEmpty;

trait Paginate
{
    public function allWithSearch($items,$fields = [] ,$request, $where = null, $value  = null, $con = null)
    {
        $page = intval($request->get('page',1));
        $perPage = intval($request->get('per_page',10));
        $fields = is_array($fields) ? $fields : [];
        $search = request()->input('search', null);
        $data = $items->query()
            ->when($search, function ($query) use ($search,$fields) {
                $query->whereAny($fields, 'like', '%' . $search . '%');
            });
        if (isEmpty($where)) {
            $data = $data->where($where,$con,$value);
        }
        $results = $data->paginate($perPage ,['*'] ,'page' ,$page);
        if ($page > $results->lastPage()) {
            $results = $data->paginate($perPage, ['*'], 'page', $results->lastPage());
        }
        return $results;
    }
}
