<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\ListingApiTrait;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

class CompanyController extends Controller
{
    use ListingApiTrait;

    protected $directory;

    // Constructor
    public function __construct()
    {
        // Dependency Injection
        $this->directory = 'company/logo';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //listing validation from trait
        $this->ListingValidation();

        $query =  Company::query()->select('id', 'name', 'address', 'owner_id', 'logo', 'employee_size', 'created_at','is_active')->with('owner:id,name')->withCount('employees');
        // search 
        if (isset($request->search)) {
            $search = $request->search;
            $query = $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        // WithOut Owner
        if (isset($request->without_owner)) {
            $query->whereNull('owner_id');
        }

        $data = $this->filterSortPagination($query);

        return ok('get companies list', [
            'companies' =>  $data['query']->get(),
            'count'     =>  $data['count'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'          => 'required|string|max:255',
            'owner_id'      => 'required|exists:users,id',
            'address'       => 'required|string|max:255',
            'employee_size' => 'required|integer',
            'logo'          => 'nullable|image',
            'is_active'     => 'boolean',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $file =  $request->file('logo');
            $filename = mt_rand(1000000000, time()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs($this->directory, $filename, 'public');
            $validatedData['logo'] = $filename;
        }

        $company = Company::create($validatedData);

        $company->owner()->update(['is_owner' => 1, 'company_id' => $company->id]);

        PersonalAccessToken::where('tokenable_id', $company->owner_id)->delete();

        return ok('create company successfully..!', $company);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $company = Company::with(['owner:id,name,email,image,phone'])->withCount('employees')->find($id);

        if (!$company) {
            return error('Company not found', [], 'validation');
        }

        return ok('get company successfully!', ['company' => $company]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'name'          => 'required|string|max:255',
            'owner_id'      => 'required|exists:users,id',
            'address'       => 'required|string|max:255',
            'employee_size' => 'required|integer',
            'logo'          => 'nullable',
            'is_active'     => 'boolean',
        ]);

        $company = Company::findOrFail($request->id);

        if ($request->hasFile('logo')) {
            // check if company exits then delete this company logo 
            if ($company->logo) {
                $Image = $this->directory . '/' . $company->logo;
                Storage::disk('public')->delete($Image);
            }
            // New logo store 
            $file     =  $request->file('logo');
            $filename = mt_rand(1000000000, time()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs($this->directory, $filename, 'public');
            $validatedData['logo'] = $filename;
        }

        if($company->owner_id!=$request->owner_id){
            PersonalAccessToken::where('tokenable_id', $company->owner_id)->delete();
        }

        $company->update($validatedData);

        $company->owner()->update(['is_owner' => 1, 'company_id' => $company->id]);
        
        $company->load('owner:id,name');

        return ok('company update successfully..!', $company);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        $company = Company::find($id);

        if (!$company) {
            return error('Company not found', [], 'validation');
        }
        // company logo default 
        if ($company->logo) {
            if ($company->logo) {
                $Image = $this->directory . '/' . $company->logo;
                Storage::disk('public')->delete($Image);
            }
        }
        // Force logout  
        PersonalAccessToken::where('tokenable_id', $company->owner_id)->delete();
        $company->delete();

        return ok('company delete successfully!');
    }

     /**
     * Status Change
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statusChange($id)
    {
        $company = Company::find($id);

        if (!$company) {
            return error('Company not found', [], 'validation');
        }

        $company->update(['is_active' => !$company->is_active]);

        $message =  $company->is_active ? 'Active' : 'InActive';

        return ok('Status ' . $message . ' Successfully');
    }
}
