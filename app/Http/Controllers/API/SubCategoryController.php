<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\ListingApiTrait;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    use ListingApiTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //listing validation from trait
        $this->ListingValidation();

        $query =  SubCategory::query()->select('id', 'name', 'is_active', 'category_id', 'created_at')->with('category:id,name')->withCount('products');

        // category
        if(isset($request->category_id)){
            $query = $query->where('category_id',$request->category);
        }

        // search 
        if (isset($request->search)) {
            $search = $request->search;
            $query = $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        $data = $this->filterSortPagination($query);

        return ok('get subcategory list successfully...', [
            'subcategory' =>  $data['query']->get(),
            'count'       =>  $data['count'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|unique:sub_categories,name|max:255',
            'display_order' => 'required|integer',
            'is_active'     => 'required|boolean',
            'category_id'   => 'required|exists:categories,id',
        ]);

        $payload = $request->only('name', 'display_order', 'is_active', 'category_id');

        $subcategory = SubCategory::create($payload);

        $subcategory->load('category:id,name');

        return ok('create subcategory  successfully', $subcategory);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $subcategory = SubCategory::with('category:id,name')->find($id);

        if (!$subcategory) {
            return error('Subcategory not found', [], 'validation');
        }

        return ok('get subcategory successfully..!', ['subcategory'=>$subcategory]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubCategory $subcategory)
    {
        $subcategory = SubCategory::findOrFail($request->id);

        $request->validate([
            'id'            => 'required|exists:sub_categories,id',
            'name'          => 'required|max:255|unique:sub_categories,name,' . $request->id,
            'display_order' => 'required|integer',
            'is_active'     => 'required|boolean',
            'category_id'   => 'required|exists:categories,id',
        ]);

        $payload = $request->only('name', 'display_order', 'is_active', 'category_id');

        $subcategory->update($payload);

        $subcategory->load('category:id,name');

        return ok('update subcategory successfully', $subcategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        $subcategory = SubCategory::find($id);

        if (!$subcategory) {
            return error('Subcategory not found', [], 'validation');
        }

        $subcategory->delete();

        return ok('delete subcategory successfully');
    }

     /**
     * Bulk Delete Sub Category
     */
    public function bulkDelete(Request $request){
        
        $this->validate($request, [
            'ids'   => 'required|array',
            'ids.*' => 'required|exists:sub_categories,id',
        ]);

        SubCategory::whereIn('id',$request->ids)->delete();

        return ok('SubCategory Bluck Delete Successfully..!');
    }

     /**
     * Status Change
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statusChange($id)
    {
        $subcategory = SubCategory::find($id);

        if (!$subcategory) {
            return error('Subcategory not found', [], 'validation');
        }

        $subcategory->update(['is_active' => !$subcategory->is_active]);

        $message =  $subcategory->is_active ? 'Active' : 'InActive';
        
        return ok('Status '.$message.' Successfully');
    }
}
