<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\AffiliatedCode;
use App\Models\Branch;
use App\Models\Role;
use App\Models\SubscriptionDay;
use App\Models\SubscriptionMeal;
use App\Models\User;
use App\Models\UserSubcrption;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $customers = User::with(['addresses', 'affiliatedCode'])
            ->whereDoesntHave('roles')
            ->whereNull('branch_id')
            ->orderByDesc('id')
            ->paginate(25);

        $fullAdmins = User::with(['roles'])
            ->whereHas('roles')
            ->whereNull('branch_id')
            ->orderByDesc('id')
            ->get();

        $branchAdmins = User::with(['roles', 'branch'])
            ->whereNotNull('branch_id')
            ->orderByDesc('id')
            ->get();

        return view('admin.users.index', compact('customers', 'fullAdmins', 'branchAdmins'));
    }

    public function create()
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles    = Role::pluck('title', 'id');
        $branches = Branch::where('status', 'active')->pluck('name', 'id');

        return view('admin.users.create', compact('roles', 'branches'));
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->all());
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index');
    }

    public function createCustomer()
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.users.create-customer');
    }

    public function storeCustomer(Request $request)
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $normalizedCountryCode = preg_replace('/[^0-9]/', '', $request->input('country_code', ''));
        $normalizedMobile      = ltrim(preg_replace('/[^0-9]/', '', $request->input('mobile', '')), '0');

        $validator = Validator::make($request->all(), [
            'country_code'   => ['required', 'digits_between:1,4'],
            'mobile'         => ['required', 'digits_between:5,12'],
            'name'           => ['required', 'string', 'max:255'],
            'date_of_birth'  => ['required', 'date_format:Y-m-d'],
            'gender'         => ['required', 'in:male,female,other'],
            'height'         => ['required', 'numeric', 'min:0'],
            'weight'         => ['required', 'numeric', 'min:0'],
            'goal'           => ['required', Rule::in(['eat_healthy','lose_weight','gain_weight','build_muscle','maintain_weight'])],
            'activity_level' => ['required', Rule::in(['sedentary','lightly_active','very_active','highly_active'])],
            'has_food_allergies' => ['required', 'boolean'],
            'allergies'      => ['nullable', 'array'],
            'allergies.*'    => ['string', 'max:255'],
            'affiliated_code'=> ['nullable', 'string', 'max:50'],
        ], [
            'country_code.required'  => 'Country code is required.',
            'mobile.required'        => 'Mobile number is required.',
            'mobile.digits_between'  => 'Mobile number must be between 5 and 12 digits.',
            'name.required'          => 'Full name is required.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.date_format' => 'Date of birth must be in YYYY-MM-DD format.',
            'gender.required'        => 'Gender is required.',
            'height.required'        => 'Height is required.',
            'weight.required'        => 'Weight is required.',
            'goal.required'          => 'Goal is required.',
            'activity_level.required'=> 'Activity level is required.',
            'has_food_allergies.required' => 'Please indicate whether the customer has food allergies.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check unique country_code + mobile combination (matches OtpService lookup)
        $exists = User::where('country_code', $normalizedCountryCode)
            ->where('mobile', $normalizedMobile)
            ->exists();
        if ($exists) {
            return redirect()->back()
                ->withErrors(['mobile' => 'This mobile number (+' . $normalizedCountryCode . ' ' . $normalizedMobile . ') is already registered.'])
                ->withInput();
        }

        $hasFoodAllergies = $request->boolean('has_food_allergies');

        // Merge custom allergy text into array
        $allergies = [];
        if ($hasFoodAllergies) {
            $allergies = array_values(array_filter($request->input('allergies', [])));
            if ($request->filled('allergy_other')) {
                $allergies[] = trim($request->allergy_other);
            }
        }

        // Resolve affiliated code
        $affiliatedCodeId = null;
        if ($request->filled('affiliated_code')) {
            $code = AffiliatedCode::where('code', strtoupper($request->affiliated_code))
                ->where('is_active', true)
                ->first();
            if ($code) {
                $affiliatedCodeId = $code->id;
                $code->incrementUsage();
            }
        }

        User::create([
            'country_code'        => $normalizedCountryCode,
            'mobile'              => $normalizedMobile,
            'name'                => $request->name,
            'dob'                 => $request->date_of_birth,
            'gender'              => $request->gender,
            'height'              => $request->height,
            'weight'              => $request->weight,
            'goal'                => $request->goal,
            'activity_level'      => $request->activity_level,
            'has_food_allergies'  => $hasFoodAllergies,
            'allergies'           => $hasFoodAllergies ? $allergies : null,
            'affiliated_code_id'  => $affiliatedCodeId,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Customer registered successfully.');
    }

    public function edit(User $user)
    {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles    = Role::pluck('title', 'id');
        $branches = Branch::where('status', 'active')->pluck('name', 'id');

        $user->load('roles');

        return view('admin.users.edit', compact('roles', 'branches', 'user'));
    }

    public function update(Request $request, User $user)
    {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $isCustomer = $user->roles()->count() === 0;

        if ($isCustomer) {
            $normalizedCountryCode = preg_replace('/[^0-9]/', '', $request->input('country_code', ''));
            $normalizedMobile      = ltrim(preg_replace('/[^0-9]/', '', $request->input('mobile', '')), '0');

            $validator = Validator::make($request->all(), [
                'country_code'   => ['required', 'digits_between:1,4'],
                'mobile'         => ['required', 'digits_between:5,12'],
                'name'           => ['required', 'string', 'max:255'],
                'dob'            => ['nullable', 'date'],
                'gender'         => ['nullable', 'in:male,female,other'],
                'height'         => ['nullable', 'numeric', 'min:0'],
                'weight'         => ['nullable', 'numeric', 'min:0'],
                'goal'           => ['nullable', Rule::in(['eat_healthy','lose_weight','gain_weight','build_muscle','maintain_weight'])],
                'activity_level' => ['nullable', Rule::in(['sedentary','lightly_active','very_active','highly_active'])],
                'has_food_allergies' => ['nullable', 'boolean'],
                'allergies'      => ['nullable', 'array'],
                'allergies.*'    => ['string', 'max:255'],
            ]);

            // Check unique country_code + mobile combination excluding this user
            $duplicate = User::where('country_code', $normalizedCountryCode)
                ->where('mobile', $normalizedMobile)
                ->where('id', '!=', $user->id)
                ->exists();
            if ($duplicate) {
                return redirect()->back()
                    ->withErrors(['mobile' => 'This mobile number (+' . $normalizedCountryCode . ' ' . $normalizedMobile . ') is already registered to another customer.'])
                    ->withInput();
            }

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $hasFoodAllergies = $request->boolean('has_food_allergies');
            $allergies = [];
            if ($hasFoodAllergies) {
                $allergies = array_values(array_filter($request->input('allergies', [])));
                if ($request->filled('allergy_other')) {
                    $allergies[] = trim($request->input('allergy_other'));
                }
            }

            // Resolve affiliated code
            $affiliatedCodeId = $user->affiliated_code_id;
            if ($request->filled('affiliated_code_text')) {
                $code = AffiliatedCode::where('code', strtoupper($request->input('affiliated_code_text')))
                    ->where('is_active', true)
                    ->first();
                if ($code) {
                    if ($code->id !== $affiliatedCodeId) {
                        $affiliatedCodeId = $code->id;
                        $code->incrementUsage();
                    }
                } else {
                    $affiliatedCodeId = null;
                }
            } else {
                $affiliatedCodeId = null;
            }

            $user->update([
                'country_code'       => $normalizedCountryCode,
                'mobile'             => $normalizedMobile,
                'name'               => $request->input('name'),
                'dob'                => $request->input('dob'),
                'gender'             => $request->input('gender'),
                'height'             => $request->input('height'),
                'weight'             => $request->input('weight'),
                'goal'               => $request->input('goal'),
                'activity_level'     => $request->input('activity_level'),
                'has_food_allergies' => $hasFoodAllergies,
                'allergies'          => $hasFoodAllergies ? $allergies : null,
                'affiliated_code_id' => $affiliatedCodeId,
            ]);

            return redirect()->route('admin.users.index')->with('success', 'Customer updated successfully.');
        }

        // Admin / staff user update
        $validator = Validator::make($request->all(), [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => ['nullable', 'string', 'min:8'],
            'roles'     => ['required', 'array'],
            'roles.*'   => ['integer'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [
            'name'      => $request->input('name'),
            'email'     => $request->input('email'),
            'branch_id' => $request->input('branch_id'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $user->update($data);
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function show(User $user)
    {
        abort_if(Gate::denies('user_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->load(['roles', 'addresses', 'paymentMethods', 'affiliatedCode']);

        return view('admin.users.show', compact('user'));
    }

    public function destroy(User $user)
    {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DB::transaction(function () use ($user) {
            // Hard-delete all subscription meals → days → subscriptions
            $subscriptionIds = UserSubcrption::withTrashed()->where('user_id', $user->id)->pluck('id');

            if ($subscriptionIds->isNotEmpty()) {
                $dayIds = SubscriptionDay::withTrashed()->whereIn('user_subcrptions_id', $subscriptionIds)->pluck('id');
                if ($dayIds->isNotEmpty()) {
                    SubscriptionMeal::withTrashed()->whereIn('subscription_days_id', $dayIds)->forceDelete();
                }
                SubscriptionDay::withTrashed()->whereIn('user_subcrptions_id', $subscriptionIds)->forceDelete();
                UserSubcrption::withTrashed()->whereIn('id', $subscriptionIds)->forceDelete();
            }

            // Hard-delete addresses and payment methods
            $user->addresses()->forceDelete();
            $user->paymentMethods()->delete();

            // Force-delete the user
            $user->forceDelete();
        });

        return back()->with('success', 'Customer and all related data deleted successfully.');
    }

    public function massDestroy(MassDestroyUserRequest $request)
    {
        $users = User::find(request('ids'));

        foreach ($users as $user) {
            $user->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
