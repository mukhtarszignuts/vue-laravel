<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Exports\UserExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\ListingApiTrait;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use ListingApiTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //listing validation from trait
        $this->ListingValidation();

        $query =  User::query()->select('id', 'name', 'email', 'phone', 'city', 'role', 'status', 'created_at', 'image', 'company_id', 'is_owner')
            ->with(['company' => function ($query) {
                // Specify the columns you want to select from the company table
                $query->select('id', 'name', 'logo', 'owner_id')->with('owner:id,name'); // Adjust columns as needed
            }]);

        // search 
        if (isset($request->search)) {
            $search = $request->search;
            $query = $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhereHas('company', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // company
        if (auth()->user()->role == 'M') {
            $query->where('company_id', auth()->user()->company_id);
        }

        // role
        if (isset($request->role)) {
            $query->where('role', $request->role);
        }
        // status
        if (isset($request->status)) {
            $query->where('status', $request->status);
        }
        // date range 
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }
        //company
        if (isset($request->company_id)) {
            $query->where('company_id', $request->company_id);
        }

        $users = $this->filterSortPagination($query);

        if ($request->is_export) {
            //export csv file base on
            return Excel::download(new UserExport($users['query']->get()), 'users.xlsx');
        }

        return ok('get users list successfully', [
            'users'     =>  $users['query']->get(),
            'count'     =>  $users['count'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming data
        $this->validate($request, [
            'email'  => 'required|email|unique:users,email|max:250',
            'image'  => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name'   => 'required',
            'role'   => 'required',
            'phone'  => 'required',
            'status' => 'required',
        ]);

        $payload = $request->only('name', 'email', 'phone', 'role', 'status', 'city', 'company_id');

        $payload['password'] = bcrypt('password');

        if ($request->hasFile('image')) {

            $file =  $request->file('image');
            $directory = 'profile_images';
            $filename = mt_rand(1000000000, time()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs($directory, $filename, 'public');
            $payload['image'] = $filename;
        }

        $user = User::create($payload);

        $user->load('company:id,name,owner_id');

        return ok('User Create SuccessFully..', $user);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        $user = User::findOrFail($id);

        return ok('get user successfully..!', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Validate incoming data
        $this->validate($request, [
            'id'     => 'required|exists:users,id',
            'email'  => 'required|max:250|email|unique:users,email,' . $request->id,
            'image'  => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = User::findOrFail($request->id);

        $payload =  $request->only('name', 'email', 'role', 'phone', 'city', 'status', 'company_id');

        if ($request->hasFile('image')) {
            // check if user exits then delete this user image 
            if ($user->image) {
                $Image = 'profile_images/' . $user->image;
                Storage::disk('public')->delete($Image);
            }
            // New image store 
            $file       =  $request->file('image');
            $directory  = 'profile_images';
            $filename   = mt_rand(1000000000, time()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs($directory, $filename, 'public');
            $payload['image'] = $filename;
        }

        $user->update($payload);



        $user->load('company:id,name,owner_id');

        return ok('User Update Successfully.!', $user);
    }

    /**
     * Remove user  in database.
     */
    public function delete($id, User $user)
    {

        $user = User::find($id);

        if (!$user) {
            return error('User not found', [], 'validation');
        }
        // profile image  
        if ($user->image) {
            if ($user->image) {
                $Image = 'profile_images/' . $user->image;
                Storage::disk('public')->delete($Image);
            }
        }

        $user->delete();

        return ok('User Delete SuccessFully..!');
    }

    // Bluk delete 
    public function bulkDelete(Request $request)
    {

        $this->validate($request, [
            'ids'   => 'required|array',
            'ids.*' => 'required|exists:users,id',
        ]);

        $users = User::whereIn('id', $request->ids)->get();

        foreach ($users as $key => $user) {
            if ($user->image) {
                $Image = 'profile_images/' . $user->image;
                Storage::disk('public')->delete($Image);
            }

            $user->delete();
        }

        return ok('User Bluck Delete Successfully..!');
    }
}
