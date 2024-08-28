<?php

namespace App\Http\Controllers\API;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\ListingApiTrait;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    use ListingApiTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //listing validation from trait
        $this->ListingValidation();

        $query =  Product::query()->select('id', 'name', 'is_active', 'description', 'price', 'stock', 'category_id', 'sub_category_id', 'created_at', 'image')->with('category:id,name', 'subCategory:id,name', 'images');

        // filter by status
        if (isset($request->status)) {
            $query = $query->where('is_active', $request->status);
        }
        // search 
        if (isset($request->search)) {
            $search = $request->search;
            $query = $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $data = $this->filterSortPagination($query);

        return ok('get product list successfully...', [
            'products' =>  $data['query']->get(),
            'count'    =>  $data['count'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'            => 'required|string|max:255',
            'price'           => 'required|numeric',
            'description'     => 'nullable|string',
            'display_order'   => 'integer',
            'is_active'       => 'boolean',
            'category_id'     => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            // 'images.*'        => 'required|image|mimes:jpg,png,jpeg,gif|max:2048',
            'stock'           => 'required|integer',
        ]);

        // if ($request->hasFile('image')) {
        //     $file =  $request->file('image');
        //     $directory = 'product_images';
        //     $filename = mt_rand(1000000000, time()) . '.' . $file->getClientOriginalExtension();
        //     $file->storeAs($directory, $filename, 'public');
        //     $validatedData['image'] = $filename;
        // }

        // Handle image uploads

        $imageFilenames = [];
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $directory = 'product_images';

            foreach ($images as $file) {
                $filename = mt_rand(1000000000, time()) . '.' . $file->getClientOriginalExtension();
                $file->storeAs($directory, $filename, 'public');
                $imageFilenames[] = $filename;
            }
        }

        // Create the product
        $product = Product::create($validatedData);

        // Associate images with the product
        foreach ($imageFilenames as $filename) {
            Image::create([
                'type'        => 'product',
                'product_id' => $product->id,
                'image'      => $filename
            ]);
        }

        return ok('create product successfully..!', $product);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::with(['category:id,name', 'subCategory:id,name', 'images:id,product_id,image'])->find($id);

        if (!$product) {
            return error('Product not found', [], 'validation');
        }

        return ok('get product successfully!', ['product' => $product]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'name'            => 'sometimes|required|string|max:255',
            'price'           => 'sometimes|required|numeric',
            'description'     => 'nullable|string',
            'display_order'   => 'integer',
            'is_active'       => 'boolean',
            'category_id'     => 'sometimes|required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'stock'           => 'integer',
        ]);

        $product = Product::with('images')->findOrFail($request->id);

        // if ($request->hasFile('image')) {
        //     // check if product exits then delete this product image 
        //     if ($product->image) {
        //         $productImage = 'product_images/' . $product->image;
        //         Storage::disk('public')->delete($productImage);
        //     }
        //     // New image store 
        //     $file =  $request->file('image');
        //     $directory = 'product_images';
        //     $filename = mt_rand(1000000000, time()) . '.' . $file->getClientOriginalExtension();
        //     $file->storeAs($directory, $filename, 'public');
        //     $validatedData['image'] = $filename;
        // }
        $imageFilenames = [];
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $directory = 'product_images';
            // old file is deleted
            // foreach ($product->images as $key => $item) {
            //     if ($item->image) {
            //         $Image = 'product_images/' . $item->image;
            //         Storage::disk('public')->delete($Image);
            //         Image::find($item->id)->delete();
            //     }
            // }
            // New File added
            foreach ($images as $file) {
                $filename = mt_rand(1000000000, time()) . '.' . $file->getClientOriginalExtension();
                $file->storeAs($directory, $filename, 'public');
                $imageFilenames[] = $filename;
            }
        }

        // Associate images with the product
        foreach ($imageFilenames as $filename) {
            Image::create([
                'type'       => 'product',
                'product_id' => $product->id,
                'image'      => $filename
            ]);
        }

        $product->update($validatedData);

        $product->load('category:id,name', 'subCategory:id,name', 'images');

        return ok('Update product successfully..!', $product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        $product = Product::with('images')->find($id);

        if (!$product) {
            return error('Product not found', [], 'validation');
        }
        // product image default 
        if ($product->image) {
            $productImage = 'product_images/' . $product->image;
            Storage::disk('public')->delete($productImage);
        }

        // Multiple  image delete from storage 
        foreach ($product->images as $key => $item) {
            if ($item->image) {
                $Image = 'product_images/' . $item->image;
                Storage::disk('public')->delete($Image);
                Image::find($item->id)->delete();
            }
        }

        $product->delete();

        return ok('product delete successfully!');
    }

    // bulk delete product
    public function bulkDelete(Request $request)
    {

        $this->validate($request, [
            'ids'   => 'required|array',
            'ids.*' => 'required|exists:products,id',
        ]);

        $products = Product::withTrashed()->whereIn('id', $request->ids)->with('images')->get();

        foreach ($products as $key => $product) {

            if ($product->image) {
                $productImage = 'product_images/' . $product->image;
                Storage::disk('public')->delete($productImage);
            }
        
            // Multiple  image delete from storage 
            foreach ($product->images as $key => $item) {
                if ($item->image) {
                    $Image = 'product_images/' . $item->image;
                    Storage::disk('public')->delete($Image);
                }
            }
            // image delete
            $product->images()->delete();
            // produt delete
            $product->delete();
        }

        return ok('Product Bluck Delete Successfully!');
    }

    /**
     * Status Change
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statusChange($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return error('Product not found', [], 'validation');
        }

        $product->update(['is_active' => !$product->is_active]);

        $message =  $product->is_active ? 'Active' : 'InActive';

        return ok('Status ' . $message . ' Successfully');
    }
    /**
     * Status Change
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkStatusChange(Request $request)
    {
        $this->validate($request, [
            'ids'    => 'required|array',
            'ids.*'  => 'required|exists:products,id',
            'status' => 'required'

        ]);

        Product::whereIn('id', $request->ids)->update(['is_active' => $request->status]);

        return ok('Status Change Successfully');
    }

    /**
     * Image Delete 
     */
    public function deleteImage(Request $request)
    {

        $this->validate($request, [
            'product_id'  => 'required|exists:products,id',
            'id'          => 'required|exists:images,id',
        ]);

        $image = Image::where(['id' => $request->id, 'product_id'=>$request->product_id])->first();

        if (!$image) {
            return error('Image not found', [], 'validation');
        }

        if ($image->image) {
            $productImage = 'product_images/' . $image->image;
            Storage::disk('public')->delete($productImage);
        }

        $image->delete();

        return ok('Image Delete Successfully');
    }
}
