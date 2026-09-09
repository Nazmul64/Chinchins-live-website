@extends('layouts.admin')

@section('title', 'Reseller System Settings')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.resellers.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Resellers</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Settings</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-sliders text-primary"></i>
                <span>Reseller System Settings & Limits</span>
            </h1>
            <p class="page-subtitle">Configure deposit/withdrawal thresholds, commission fees, coin exchange rates, and offer badges.</p>
        </div>
        <a href="{{ route('admin.resellers.index') }}" class="btn btn-outline-secondary rounded-3 px-3">
            <i class="fa-solid fa-store"></i> All Resellers
        </a>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm" style="background: var(--card-bg-light); border: 1px solid var(--border-color) !important;">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.resellers.settings.update') }}">
                        @csrf
                        
                        <h5 class="fw-bold mb-3 text-dark pb-2 border-bottom">
                            <i class="fa-solid fa-circle-down text-success me-2"></i> Deposit / Refill Rules
                        </h5>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Minimum Deposit (BDT)</label>
                                <input type="number" name="min_deposit" class="form-control rounded-3" value="{{ $settings['min_deposit'] }}" min="1" required>
                                <small class="text-muted">Minimum amount a reseller can request to refill</small>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Maximum Deposit (BDT)</label>
                                <input type="number" name="max_deposit" class="form-control rounded-3" value="{{ $settings['max_deposit'] }}" min="1" required>
                                <small class="text-muted">Maximum amount per refill request</small>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Reseller Coin Rate (Coins per 1 BDT)</label>
                                <input type="number" step="0.01" name="reseller_coin_rate_per_bdt" class="form-control rounded-3 font-monospace" value="{{ $settings['reseller_coin_rate_per_bdt'] }}" required>
                                <small class="text-muted">e.g. 60 coins per 1 BDT wholesale rate</small>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Default Offer Badge (In App)</label>
                                <input type="text" name="reseller_offer_badge" class="form-control rounded-3" value="{{ $settings['reseller_offer_badge'] }}" required>
                                <small class="text-muted">e.g. "Up To 29%↑" shown in payment options</small>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-3 text-dark pb-2 border-bottom">
                            <i class="fa-solid fa-circle-up text-danger me-2"></i> Cash-Out / Withdrawal Rules
                        </h5>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold" style="font-size: 13px;">Minimum Withdrawal (Coins)</label>
                                <input type="number" name="min_withdraw" class="form-control rounded-3 font-monospace" value="{{ $settings['min_withdraw'] }}" min="1" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold" style="font-size: 13px;">Maximum Withdrawal (Coins)</label>
                                <input type="number" name="max_withdraw" class="form-control rounded-3 font-monospace" value="{{ $settings['max_withdraw'] }}" min="1" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold" style="font-size: 13px;">Withdrawal Commission / Fee (%)</label>
                                <input type="number" step="0.01" name="withdraw_commission_rate" class="form-control rounded-3" value="{{ $settings['withdraw_commission_rate'] }}" min="0" max="50" required>
                                <small class="text-muted">Admin profit fee deducted upon payout</small>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-3 text-dark pb-2 border-bottom">
                            <i class="fa-solid fa-circle-info text-primary me-2"></i> Reseller Portal Guidelines
                        </h5>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold" style="font-size: 13px;">Guidelines & Notice (Shown on Reseller Dashboard)</label>
                                <textarea name="reseller_instructions" class="form-control rounded-3" rows="4">{{ $settings['reseller_instructions'] }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn-ch-primary">
                                <i class="fa-solid fa-floppy-disk"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm p-4" style="background: var(--card-bg-light); border: 1px solid var(--border-color) !important;">
                <h6 class="fw-bold mb-3"><i class="fa-solid fa-lightbulb text-warning me-2"></i> How Resellers Work</h6>
                <ul class="text-muted" style="font-size: 13px; padding-left: 18px; line-height: 1.6;">
                    <li><strong>Stock Refill:</strong> Resellers request coin refills by depositing money to Admin. Once approved, coins are instantly credited to their wallet.</li>
                    <li><strong>User Recharges:</strong> Users select a reseller in the app to open a chat. The reseller verifies user payment and enters the user's 8-digit Account ID to transfer coins instantly.</li>
                    <li><strong>Cash-Out:</strong> Resellers can convert their balance into cash withdrawals anytime, subject to your configured commission fee.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
