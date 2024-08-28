<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\ListingApiTrait;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ListingApiTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //listing validation from trait
        $this->ListingValidation();

        $query =  Category::query()->select('id', 'name', 'is_active', 'created_at')->withCount('products');

        // search 
        if (isset($request->search)) {
            $search = $request->search;
            $query = $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        $data = $this->filterSortPagination($query);

        return ok('get category list successfully...', [
            'category' =>  $data['query']->get(),
            'count'    =>  $data['count'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|unique:categories,name|max:255',
            'display_order' => 'required|integer',
            'is_active'     => 'required|boolean',
        ]);

        $payload = $request->only('name', 'display_order', 'is_active');

        $category = Category::create($payload);

        return ok('create category successfully..!', $category);
    }

    /**
     * Display the specified resource.
     */
    public function show($id, Category $category)
    {
        $category = Category::find($id);

        if (!$category) {
            return error('Category not found', [], 'validation');
        }

        return ok('get category successfully..!', ['category'=>$category]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $category = Category::findOrFail($request->id);

        $request->validate([
            'id'            => 'required|exists:categories,id',
            'name'          => 'required|max:255|unique:categories,name,' . $request->id,
            'display_order' => 'sometimes|required|integer',
            'is_active'     => 'sometimes|required|boolean',
        ]);

        $payload = $request->only('name', 'is_active', 'display_order');

        $category->update($payload);

        return ok('Update category successfully..!',$category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id) 
    {
        $category = Category::findOrFail($id);

        $category->delete();

        return ok('delete category successfully..!');
    }

    /**
     * Bulk Delete Category
     */
     public function bulkDelete(Request $request){
        
        $this->validate($request, [
            'ids'   => 'required|array',
            'ids.*' => 'required|exists:categories,id',
        ]);

        Category::whereIn('id',$request->ids)->delete();

        return ok('Category Bluck Delete Successfully..!');
    }

    /**
     * Status Change
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statusChange($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return error('Category not found', [], 'validation');
        }

        $category->update(['is_active' => !$category->is_active]);

        $message =  $category->is_active ? 'Active' : 'InActive';
        
        return ok('Status '.$message.' Successfuly');
    }
    
}
