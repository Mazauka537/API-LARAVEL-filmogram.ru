<?php

namespace App\Http\Controllers;

use App\Models\DefaultCollection;
use Illuminate\Http\Request;

class DefaultCollectionController extends Controller
{
    public function getDefaultCollection(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);

        $collection = DefaultCollection::findOrFail($request->id);

        return response($collection, 200);
    }

    public function getDefaultCollections(Request $request)
    {
        $collections = DefaultCollection::get();

        return response($collections, 200);
    }
}
