<?php

namespace App\Http\Controllers;

use App\Helpers\PhoneHelper;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\MembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $customers = Customer::query()
            ->search($request->input('search'))
            ->orderByDesc('last_purchase_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => [
                'search' => $request->input('search', ''),
            ],
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['phone'] = PhoneHelper::normalize($data['phone']);

        Customer::create($data);

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil ditambahkan!');
    }

    public function show(Customer $customer): Response
    {
        $customer->load([
            'transactions' => fn ($q) => $q->orderByDesc('date')->limit(20),
            'transactions.items',
            'pointLogs' => fn ($q) => $q->orderByDesc('created_at')->limit(50),
        ]);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
            'maskedPhone' => PhoneHelper::mask($customer->phone),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $data = $request->validated();
        $data['phone'] = PhoneHelper::normalize($data['phone']);

        $customer->update($data);

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil diperbarui!');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil dihapus!');
    }

    /**
     * API endpoint: Search customer by phone for POS.
     */
    public function search(Request $request): JsonResponse
    {
        $phone = PhoneHelper::normalize($request->input('phone', ''));

        if (strlen($phone) < 4) {
            return response()->json(['customer' => null]);
        }

        $customer = Customer::where('phone', $phone)->first();

        if (! $customer) {
            // Partial match search
            $suggestions = Customer::where('phone', 'like', "%{$phone}%")
                ->limit(5)
                ->get(['id', 'name', 'phone', 'points', 'birth_date', 'last_birthday_used_at']);

            return response()->json([
                'customer' => null,
                'suggestions' => $suggestions,
            ]);
        }

        return response()->json([
            'customer' => $customer,
            'isBirthday' => app(MembershipService::class)->isBirthdayPeriod($customer),
            'maskedPhone' => PhoneHelper::mask($customer->phone),
        ]);
    }
}
