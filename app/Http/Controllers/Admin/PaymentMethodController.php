<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of payment methods.
     */
    public function index()
    {
        $methods = PaymentMethod::withCount('depositRequests')->orderBy('sort_order')->latest()->get();
        return view('admin.payment-methods.index', compact('methods'));
    }

    /**
     * Store a newly created payment method in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'account_type' => 'required|string|max:50',
            'account_number' => 'required|string|max:100',
            'instructions' => 'nullable|string',
            'min_deposit' => 'required|numeric|min:1',
            'max_deposit' => 'required|numeric|gte:min_deposit',
            'rate_per_bdt' => 'required|integer|min:1',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['code'] = strtolower(Str::slug($validated['name']));

        // Handle icon upload
        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $filename = 'pm_icon_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/payment_methods'), $filename);
            $validated['icon'] = 'payment_methods/' . $filename;
        }

        PaymentMethod::create($validated);

        return redirect()->route('admin.payment-methods.index')->with('success', 'Payment Method added successfully!');
    }

    /**
     * Update the specified payment method.
     */
    public function update(Request $request, $id)
    {
        $method = PaymentMethod::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'account_type' => 'required|string|max:50',
            'account_number' => 'required|string|max:100',
            'instructions' => 'nullable|string',
            'min_deposit' => 'required|numeric|min:1',
            'max_deposit' => 'required|numeric|gte:min_deposit',
            'rate_per_bdt' => 'required|integer|min:1',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['code'] = strtolower(Str::slug($validated['name']));

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $filename = 'pm_icon_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/payment_methods'), $filename);
            $validated['icon'] = 'payment_methods/' . $filename;
        }

        $method->update($validated);

        return redirect()->route('admin.payment-methods.index')->with('success', 'Payment Method updated successfully!');
    }

    /**
     * Toggle active status of payment method.
     */
    public function toggleStatus($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->is_active = !$method->is_active;
        $method->save();

        $statusStr = $method->is_active ? 'Activated' : 'Deactivated';
        return back()->with('success', "Payment method {$method->name} has been {$statusStr}.");
    }

    /**
     * Delete payment method.
     */
    public function destroy($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->delete();

        return redirect()->route('admin.payment-methods.index')->with('success', 'Payment Method removed successfully.');
    }
}
