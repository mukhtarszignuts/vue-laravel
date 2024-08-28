<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Product;
use App\Models\Project;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {

        $categoryQry    = Category::query();
        $SubCategoryQry = SubCategory::query();
        $productQry     = Product::query();
        $projectQry     = Project::query();
        $usersQry       = User::query();

        $company_id = isset($request->company_id) ? $request->company_id : auth()->user()->company_id;
        // 
        if (auth()->user()->role == 'M') {
            $data = [
                'project'  => (clone $projectQry)->where(['company_id' => $company_id])->count(),
                'client'   => (clone $usersQry)->where(['company_id' => $company_id, 'role' => 'C'])->count(),
                'employee' => (clone $usersQry)->where(['company_id' => $company_id])->count(),
            ];
        } else {
            $data = [
                'category'     => $categoryQry->count(),
                'sub_category' => $SubCategoryQry->count(),
                'product'      => $productQry->count(),
                'users'        => $usersQry->count(),
                'users'        => $usersQry->count(),
            ];
        }
        return ok('get dashboard details successfully.', $data);
    }
}
