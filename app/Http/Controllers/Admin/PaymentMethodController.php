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
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'account_type' => 'required|string|max:50',
                'account_number' => 'required|string|max:100',
                'instructions' => 'nullable|string',
                'min_deposit' => 'required|numeric|min:1',
                'max_deposit' => 'required|numeric|gte:min_deposit',
                'rate_coins' => 'nullable|integer|min:1',
                'rate_bdt' => 'nullable|numeric|min:0.01',
                'rate_per_bdt' => 'nullable|numeric|min:0.01',
                'icon' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
                'is_active' => 'nullable|boolean',
            ]);

            // Calculate coin exchange rate properly
            $rateCoins = (int) ($request->input('rate_coins') ?: 500);
            $rateBdt = (float) ($request->input('rate_bdt') ?: 50);
            if ($rateBdt <= 0) $rateBdt = 1;
            
            $validated['rate_coins'] = $rateCoins;
            $validated['rate_bdt'] = $rateBdt;
            $validated['rate_per_bdt'] = round($rateCoins / $rateBdt, 4);

            $validated['is_active'] = $request->has('is_active');
            $slug = strtolower(Str::slug($validated['name']));
            $validated['code'] = $slug ?: ('pm_' . time() . '_' . Str::random(4));

            // Handle icon upload directly to public/uploads/payment_gateways
            if ($request->hasFile('icon')) {
                try {
                    $file = $request->file('icon');
                    $filename = 'pm_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                    $uploadDir = public_path('uploads/payment_gateways');
                    
                    if (!file_exists($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    @chmod($uploadDir, 0777);

                    $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;
                    if (@copy($file->getRealPath(), $destination) || @move_uploaded_file($file->getPathname(), $destination) || $file->move($uploadDir, $filename)) {
                        $validated['icon'] = 'uploads/payment_gateways/' . $filename;
                    }
                } catch (\Throwable $fileEx) {
                    \Illuminate\Support\Facades\Log::warning('Icon upload error: ' . $fileEx->getMessage());
                }
            }

            PaymentMethod::create($validated);

            return redirect()->route('admin.payment-methods.index')->with('success', 'Payment Method added successfully!');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return back()->withErrors($ve->validator)->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not save payment method: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update the specified payment method.
     */
    public function update(Request $request, $id)
    {
        try {
            $method = PaymentMethod::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'account_type' => 'required|string|max:50',
                'account_number' => 'required|string|max:100',
                'instructions' => 'nullable|string',
                'min_deposit' => 'required|numeric|min:1',
                'max_deposit' => 'required|numeric|gte:min_deposit',
                'rate_coins' => 'nullable|integer|min:1',
                'rate_bdt' => 'nullable|numeric|min:0.01',
                'rate_per_bdt' => 'nullable|numeric|min:0.01',
                'icon' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
                'is_active' => 'nullable|boolean',
            ]);

            $rateCoins = (int) ($request->input('rate_coins') ?: ($method->rate_coins ?: 500));
            $rateBdt = (float) ($request->input('rate_bdt') ?: ($method->rate_bdt ?: 50));
            if ($rateBdt <= 0) $rateBdt = 1;

            $validated['rate_coins'] = $rateCoins;
            $validated['rate_bdt'] = $rateBdt;
            $validated['rate_per_bdt'] = round($rateCoins / $rateBdt, 4);

            $validated['is_active'] = $request->has('is_active');
            $slug = strtolower(Str::slug($validated['name']));
            $validated['code'] = $slug ?: ($method->code ?: ('pm_' . time() . '_' . Str::random(4)));

            if ($request->hasFile('icon')) {
                try {
                    $file = $request->file('icon');
                    $filename = 'pm_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                    $uploadDir = public_path('uploads/payment_gateways');
                    
                    if (!file_exists($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    @chmod($uploadDir, 0777);

                    $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;
                    if (@copy($file->getRealPath(), $destination) || @move_uploaded_file($file->getPathname(), $destination) || $file->move($uploadDir, $filename)) {
                        $validated['icon'] = 'uploads/payment_gateways/' . $filename;
                    }
                } catch (\Throwable $fileEx) {
                    \Illuminate\Support\Facades\Log::warning('Icon upload error: ' . $fileEx->getMessage());
                }
            }

            $method->update($validated);

            return redirect()->route('admin.payment-methods.index')->with('success', 'Payment Method updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return back()->withErrors($ve->validator)->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not update payment method: ' . $e->getMessage())->withInput();
        }
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
