<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller Portal Login - ChinChins Live</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #f59e0b, #ec4899, #6366f1);
        }
        .btn-gradient {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #fff;
            font-weight: 700;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 15px;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
            transition: all 0.2s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: linear-gradient(135deg, #f59e0b, #ef4444); border-radius: 18px; color: #fff; font-size: 28px; box-shadow: 0 8px 24px rgba(245,158,11,0.4); margin-bottom: 16px;">
                <i class="fa-solid fa-gem"></i>
            </div>
            <h3 class="fw-bold mb-1" style="color: #0f172a;">Reseller Portal</h3>
            <p class="text-muted" style="font-size: 14px;">Sign in to your coin agent & recharge account</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger py-2 px-3 rounded-3 mb-3" style="font-size: 13px;">
                <i class="fa-solid fa-circle-exclamation me-1"></i> {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success py-2 px-3 rounded-3 mb-3" style="font-size: 13px;">
                <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('reseller.login.submit') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold" style="font-size: 13px; color: #334155;">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-regular fa-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control bg-light border-start-0 ps-0" placeholder="your.email@example.com" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold" style="font-size: 13px; color: #334155;">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control bg-light border-start-0 ps-0" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-gradient w-100 mb-3">
                <i class="fa-solid fa-right-to-bracket me-2"></i> Log In to Dashboard
            </button>

            <div class="text-center">
                <small class="text-muted" style="font-size: 12px;">Need a reseller account? Contact ChinChins Admin.</small>
            </div>
        </form>
    </div>
</body>
</html>
