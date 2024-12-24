<?php

namespace App\Http\Controllers\Organization;
use App\Http\Controllers\Controller;
use App\DataTables\Organization\PayoutDataTable;
use App\Http\Requests\Admin\PayoutRequest;
use App\Models\Checkout;
use App\Models\Course;
use App\Models\Organization;
use App\Repositories\PayoutRepository;
use App\Repositories\UserRepository;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayoutController extends Controller
{
    protected $payoutRepository;

    public function __construct(PayoutRepository $payoutRepository)
    {
        $this->payoutRepository = $payoutRepository;
    }

    public function index(PayoutDataTable $dataTable)
    {
        return $dataTable->render('backend.organization.payout.index');
    }

    public function create()
    {
        return view('backend.organization.payout.create');
    }

    public function store(PayoutRequest $request, UserRepository $userRepository): \Illuminate\Http\JsonResponse
    {
        if (isDemoMode()) {
            $data = [
                'status' => 'danger',
                'error'  => __('this_function_is_disabled_in_demo_server'),
                'title'  => 'error',
            ];

            return response()->json($data);
        }
        DB::beginTransaction();

        $organization = Organization::where('id', $request->organization)->first();
        $org_id       = $organization->id;
        $payout_id    = $this->payoutID($organization);
        try {
            $request['organization_id']  = $organization->id;
            $request['email']            = $organization->email;
            $request['phone_country_id'] = $organization->phone_country_id;
            $request['phone']            = $organization->phone;
            $request['payout_id']        = $payout_id;
            $request['transaction_id']   = Str::random(10);
            $request['user_id']          = auth()->user()->id;

            $balance                     = $this->OrganizationBalance($organization->id);
            if ($request->amount > $balance) {
                return response()->json(['error' => __('sorry_balance_not_sufficient')]);
            }

            $this->payoutRepository->store($request->all());
            Toastr::success(__('create_successful'));
            DB::commit();

            return response()->json([
                'success' => __('create_successful'),
                'route'   => route('organization.payout'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()]);
        }

    }
    public function payoutID($organization)
    {
        try {
            $last_id           = $this->payoutRepository->all()->count();
            $organization_name = substr($organization->org_name, 0, 2);
            $first_name        = auth()->user()->first_name;
            $name              = substr($first_name, 0, 2);

            return $payout_id  = $organization_name.random_int(100, 999).$last_id.$name;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function OrganizationBalance($id)
    {
        try {
            $balance = Checkout::whereHas('enrolls', function ($query) use ($id) {
                $query->whereHasMorph('enrollable', [Course::class], function ($query) use ($id) {
                    $query->where('organization_id', $id);
                });
            })->sum('payable_amount');

            return $balance;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    public function paymentMethod()
    {

        return view('backend.organization.payout.payout_method');
    }
}
