<?php

namespace App\Trait;

trait HttpResponses
{
    protected function success($data, $message = null, $code = 200){
		return response()->json([
			'status' => true,
			'message' => $message,
			'data' => $data
		], $code);
	}

    protected function withPagination($collection, $message = null, $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $collection->items(),
            'pagination' => [
                'current_page' => $collection->currentPage(),
                'last_page' => $collection->lastPage(),
                'per_page' => $collection->perPage(),
                'total' => $collection->total(),
                'prev_page_url' => $collection->previousPageUrl(),
                'next_page_url' => $collection->nextPageUrl(),
            ],
        ], $code);
    }

	protected function error($data, $message = null, $code = 500){
		return response()->json([
			'status' => false,
			'message' => $message,
			'data' => $data
		], $code);
	}
}
