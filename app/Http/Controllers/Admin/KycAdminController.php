<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KycAdminController extends Controller
{
    /**
     * Display a listing of all KYC identity verification submissions.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $docType = $request->input('document_type', 'all');

        $query = KycVerification::with(['user', 'reviewer'])->latest('submitted_at');

        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        if (in_array($docType, ['nid', 'passport', 'birth_certificate'])) {
            $query->where('document_type', $docType);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('nickname', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('account_id', 'like', "%{$search}%");
                  });
            });
        }

        $kycList = $query->paginate(15)->withQueryString();

        $stats = [
            'total'             => KycVerification::count(),
            'pending'           => KycVerification::where('status', 'pending')->count(),
            'approved'          => KycVerification::where('status', 'approved')->count(),
            'rejected'          => KycVerification::where('status', 'rejected')->count(),
            'nid_count'         => KycVerification::where('document_type', 'nid')->count(),
            'passport_count'    => KycVerification::where('document_type', 'passport')->count(),
            'birth_cert_count'  => KycVerification::where('document_type', 'birth_certificate')->count(),
        ];

        return view('admin.kyc.index', compact('kycList', 'stats', 'status', 'docType'));
    }

    /**
     * Show detailed KYC submission review page.
     */
    public function show($id)
    {
        $kyc = KycVerification::with(['user', 'reviewer'])->findOrFail($id);
        return view('admin.kyc.show', compact('kyc'));
    }

    /**
     * Approve KYC verification and mark user as verified.
     */
    public function approve(Request $request, $id)
    {
        $kyc = KycVerification::with('user')->findOrFail($id);

        if ($kyc->status === 'approved') {
            return back()->with('info', "This KYC request has already been approved.");
        }

        DB::beginTransaction();
        try {
            $user = $kyc->user;
            if (!$user) {
                return back()->with('error', 'Associated user not found.');
            }

            // 1. Update KYC record
            $kyc->status = 'approved';
            $kyc->reviewed_at = now();
            $kyc->reviewed_by = Auth::id() ?: 1;
            $kyc->admin_note = $request->input('admin_note') ?: 'Identity verified and approved by admin';
            $kyc->rejection_reason = null;
            $kyc->save();

            // 2. Mark User as Verified
            $user->update([
                'is_verified' => true,
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => true,
                    'message' => "KYC Verification for {$user->display_name} has been Approved!",
                    'data'    => $kyc->fresh(['user']),
                ]);
            }

            return back()->with('success', "KYC Verification for {$user->display_name} has been Approved! Verified badge is now active on their profile.");
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Error approving KYC: ' . $e->getMessage());
        }
    }

    /**
     * Reject KYC verification with a reason.
     */
    public function reject(Request $request, $id)
    {
        $kyc = KycVerification::with('user')->findOrFail($id);

        $reason = $request->input('rejection_reason') 
               ?: $request->input('admin_note') 
               ?: 'Document images or selfie were unclear or information did not match.';

        DB::beginTransaction();
        try {
            $user = $kyc->user;

            $kyc->status = 'rejected';
            $kyc->reviewed_at = now();
            $kyc->reviewed_by = Auth::id() ?: 1;
            $kyc->rejection_reason = $reason;
            $kyc->admin_note = $request->input('admin_note');
            $kyc->save();

            // If user had no other approved KYC, set is_verified to false
            $hasOtherApproved = KycVerification::where('user_id', $kyc->user_id)
                ->where('id', '!=', $kyc->id)
                ->where('status', 'approved')
                ->exists();

            if (!$hasOtherApproved && $user) {
                $user->update([
                    'is_verified' => false,
                ]);
            }

            DB::commit();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => true,
                    'message' => "KYC Verification #{$kyc->id} rejected.",
                    'data'    => $kyc->fresh(['user']),
                ]);
            }

            return back()->with('success', "KYC Verification #{$kyc->id} rejected with reason: '{$reason}'.");
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Error rejecting KYC: ' . $e->getMessage());
        }
    }

    /**
     * Revoke an approved KYC verification.
     */
    public function revoke(Request $request, $id)
    {
        $kyc = KycVerification::with('user')->findOrFail($id);

        DB::beginTransaction();
        try {
            $user = $kyc->user;

            $kyc->status = 'rejected';
            $kyc->rejection_reason = $request->input('reason') ?: 'Verification revoked by admin.';
            $kyc->reviewed_at = now();
            $kyc->reviewed_by = Auth::id() ?: 1;
            $kyc->save();

            if ($user) {
                $user->update(['is_verified' => false]);
            }

            DB::commit();

            return back()->with('success', "Verification for {$user?->display_name} has been revoked.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error revoking KYC: ' . $e->getMessage());
        }
    }
}
