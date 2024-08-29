<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Events;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\CommonFunctionTrait;
use App\Http\Traits\ListingApiTrait;

class EventController extends Controller
{
    use ListingApiTrait , CommonFunctionTrait;

     /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //listing validation from trait
        $this->ListingValidation();

        $query =  Events::query()->select('id', 'title', 'url','type', 'start_date', 'end_date', 'is_allday');

        // search 
        if (isset($request->search)) {
            $search = $request->search;
            $query = $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            });
        }

        // company filter
        // if (auth()->user()->role === 'M') {
        //     $company_id = isset($request->company_id) ? $request->company_id : auth()->user()->company_id;
        //     $query->where('company_id', $company_id);
        // }

        // type
        if (isset($request->type) && count($request->type)>1) {
            $query->whereIn('type', $request->type);
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
            $query->id     = $query->id;
            $query->url    = $query->url;
            $query->title  = ucfirst($query->title);
            $query->start  = $query->start_date;
            $query->end    = $query->end_date;
            $query->allDay = $query->is_allday;
            $query->extendedProps = [
                'calendar'=>$this->eventType($query->type)
            ];
                
            return $query; // Return the modified item

            
        });

        return ok('get events list ', [
            'events' =>  $filterData,
            'count'  =>  $data['count'],
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'type'       => 'required|string',
            'title'      => 'required|string',
            'url'        => 'nullable|string',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'is_allday'  => 'required|boolean',
        ]);

        $events = Events::create($validatedData);

        return ok('create event successfully..!', $events);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $events = Events::find($id);

        if (!$events) {
            return error('Event not found', [], 'validation');
        }

        return ok('get events successfully!', ['events' => $events]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'type'       => 'required|string',
            'title'      => 'required|string',
            'url'        => 'nullable|string',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'is_allday'  => 'required',
        ]);

        $events = Events::findOrFail($request->id);

        $events->update($validatedData);

        return ok('Update events successfully..!', $events);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        $events = Events::find($id);

        if (!$events) {
            return error('Events not found', [], 'validation');
        }
        
        $events->delete();

        return ok('events delete successfully!');
    }
    
}
