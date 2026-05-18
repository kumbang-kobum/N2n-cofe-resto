<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = Sale::query()
            ->select([
                DB::raw('LOWER(TRIM(customer_name)) as name_key'),
                DB::raw('MAX(customer_name) as customer_name'),
                DB::raw('MAX(customer_phone) as customer_phone'),
                DB::raw('COUNT(*) as visit_count'),
                DB::raw('SUM(grand_total) as total_spending'),
                DB::raw('MAX(paid_at) as last_visit'),
            ])
            ->whereIn('status', ['PAID', 'REFUND'])
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->groupBy(DB::raw('LOWER(TRIM(customer_name))'))
            ->orderByDesc('last_visit');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $search . '%');
            });
        }

        $customers = $query->paginate(25)->withQueryString();

        return view('admin.customers.index', compact('customers', 'search'));
    }
}
