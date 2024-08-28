<?php

namespace App\Http\Controllers\API;

use App\Models\Image;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Traits\ListingApiTrait;
use Illuminate\Support\Facades\Storage;
use App\Http\Traits\CommonFunctionTrait;

class ProjectController extends Controller
{
    use ListingApiTrait, CommonFunctionTrait;

    protected $directory;

    // Constructor
    public function __construct()
    {
        // Dependency Injection
        $this->directory = 'projects/images';
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //listing validation from trait
        $this->ListingValidation();

        $query =  Project::query()->select('id', 'name', 'description', 'location', 'type', 'start_date', 'end_date', 'total_budget', 'budget', 'client_id', 'status', 'created_at', 'thumbnil')->with('client:id,name,phone,image');

        // search 
        if (isset($request->search)) {
            $search = $request->search;
            $query = $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        // company filter
        if (auth()->user()->role === 'M') {
            $company_id = isset($request->company_id) ? $request->company_id : auth()->user()->company_id;
            $query->where('company_id', $company_id);
        }

        // type
        if (isset($request->type)) {
            $query->where('type', $request->type);
        }
        // status
        if (isset($request->status)) {
            $query->where('status', $request->status);
        }
        // date range 
        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $query->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            });
        }

        $data = $this->filterSortPagination($query);

        $filterData = $data['query']->get()->map(function ($query) {
            // Modify the start_date field
            $query->name = ucfirst($query->name);
            $query->start_date = Carbon::parse($query->start_date)->format('Y/m/d');
            $query->end_date = Carbon::parse($query->end_date)->format('Y/m/d');
            $query->total_budget = $this->formatNumber($query->total_budget);
            $query->hours = $this->calculateHours($query->start_date, $query->end_date);
            $query->daysLeft = $this->calculateDaysLeft($query->end_date);

            $query->chipColor = $query->daysLeft < 90 ? 'error' : ($query->daysLeft > 90 ? 'success' : 'warning');

            return $query; // Return the modified item
        });

        $projects = auth()->user()->role === 'M' ? $filterData : $data['query']->get();

        return ok('get projects list ', [
            'projects' =>  $projects,
            'count'    =>  $data['count'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'location'     => 'required|string',
            'type'         => 'required|string',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after:start_date',
            'total_budget' => 'required|numeric',
            'budget'       => 'nullable|numeric',
            'client_id'    => 'required|exists:users,id',
            'company_id'   => 'nullable|exists:companies,id',
            'status'       => 'nullable|string',
            'thumbnil'     => 'nullable|string',
        ]);

        // Handle thumnilimage upload
        if ($request->hasFile('thumbnil')) {
            $file =  $request->file('thumbnil');
            $filename = mt_rand(1000000000, time()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs($this->directory, $filename, 'public');
            $validatedData['thumbnil'] = $filename;
        }
        // handle Multiple image for projects
        $imageFilenames = [];
        if ($request->hasFile('images')) {
            $images = $request->file('images');

            foreach ($images as $file) {
                $filename = mt_rand(1000000000, time()) . '.' . $file->getClientOriginalExtension();
                $file->storeAs($this->directory, $filename, 'public');
                $imageFilenames[] = $filename;
            }
        }

        $project = Project::create($validatedData);

        // Associate images with the project
        foreach ($imageFilenames as $filename) {
            Image::create([
                'type'       => 'project',
                'project_id' => $project->id,
                'image'      => $filename
            ]);
        }

        return ok('create project successfully..!', $project);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $project = Project::with(['client:id,name'])->find($id);

        if (!$project) {
            return error('Project not found', [], 'validation');
        }

        return ok('get project successfully!', ['project' => $project]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'location'     => 'required|string',
            'type'         => 'required|string',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after:start_date',
            'total_budget' => 'required|numeric',
            'budget'       => 'nullable|numeric',
            'client_id'    => 'required|exists:users,id',
            'company_id'   => 'nullable|exists:companies,id',
            'status'       => 'nullable|string',
            'thumbnil'     => 'nullable|string',
        ]);

        $project = Project::with('images')->findOrFail($request->id);

        if ($request->hasFile('thumbnil')) {
            // check if project exits then delete this project image 
            if ($project->thumbnil) {
                $productImage = $this->directory . '/' . $project->thumbnil;
                Storage::disk('public')->delete($productImage);
            }
            // New image store 
            $file     =  $request->file('thumbnil');
            $filename = mt_rand(1000000000, time()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs($this->directory, $filename, 'public');
            $validatedData['thumbnil'] = $filename;
        }

        $imageFilenames = [];
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            foreach ($images as $file) {
                $filename = mt_rand(1000000000, time()) . '.' . $file->getClientOriginalExtension();
                $file->storeAs($this->directory, $filename, 'public');
                $imageFilenames[] = $filename;
            }
        }

        // Associate images with the project
        foreach ($imageFilenames as $filename) {
            Image::create([
                'type'       => 'project',
                'project_id' => $project->id,
                'image'      => $filename
            ]);
        }

        $project->update($validatedData);

        $project->load('client:id,name', 'images');

        return ok('Update project successfully..!', $project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        $project = Project::with('images')->find($id);

        if (!$project) {
            return error('Project not found', [], 'validation');
        }
        // project image default 
        if ($project->image) {
            if ($project->image) {
                $productImage = $this->directory . '/' . $project->image;
                Storage::disk('public')->delete($productImage);
            }
        }

        // Multiple  image delete from storage 
        foreach ($project->images as $key => $item) {
            if ($item->image) {
                $Image = $this->directory . '/' . $item->image;
                Storage::disk('public')->delete($Image);
            }
        }

        $project->delete();

        return ok('project delete successfully!');
    }

    /**
     * Image Delete 
     */
    public function deleteImage(Request $request)
    {

        $this->validate($request, [
            'project_id'  => 'required|exists:projects,id',
            'id'          => 'required|exists:images,id',
        ]);

        $image = Image::where(['id' => $request->id, 'project_id' => $request->project_id])->first();

        if (!$image) {
            return error('Image not found', [], 'validation');
        }

        $image->delete();

        return ok('Image Delete Successfully');
    }
}
