@extends('layouts.admin')

@section('title', 'Firebase Apps')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-fire text-warning"></i> Firebase Apps
            </h4>
            <p class="text-muted small mb-0">Manage and configure Firebase projects and service account credentials for push notifications.</p>
        </div>
        <div>
            <button type="button" class="btn btn-dark d-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addAppModal">
                <i class="fa-solid fa-plus"></i> Add New App
            </button>
        </div>
    </div>

    <!-- Apps Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4" style="width: 60px;">#</th>
                        <th>App Name</th>
                        <th>Package Name</th>
                        <th>Service Account / Project</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($apps as $index => $app)
                    <tr>
                        <td class="ps-4 fw-bold text-muted">{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center text-warning fw-bold" style="width: 36px; height: 36px;">
                                    <i class="fa-solid fa-fire"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $app->app_name }}</div>
                                    <small class="text-muted">{{ $app->package_name }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <code class="text-primary bg-primary bg-opacity-10 px-2 py-1 rounded small">{{ $app->package_name }}</code>
                        </td>
                        <td>
                            @if($app->project_id)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="fa-solid fa-circle-check"></i> {{ $app->project_id }}
                                </span>
                            @elseif($app->server_key)
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                    <i class="fa-solid fa-key"></i> Legacy Server Key
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Mock / Default
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($app->is_active)
                                <span class="badge bg-success px-3 py-1 rounded-pill">
                                    <i class="fa-solid fa-check me-1"></i> Active
                                </span>
                            @else
                                <span class="badge bg-secondary px-3 py-1 rounded-pill">Inactive</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $app->created_at ? $app->created_at->format('d M, Y') : 'N/A' }}
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewAppModal{{ $app->id }}" title="View Credentials">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editAppModal{{ $app->id }}" title="Edit App">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('admin.firebase.apps.destroy', $app->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this Firebase App?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete App">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- View App Modal -->
                    <div class="modal fade" id="viewAppModal{{ $app->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow">
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title fw-bold">Firebase App Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <label class="text-muted small fw-bold">App Name</label>
                                        <p class="fw-bold fs-6 mb-0">{{ $app->app_name }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small fw-bold">Package Name</label>
                                        <p class="mb-0"><code>{{ $app->package_name }}</code></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small fw-bold">Firebase Project ID</label>
                                        <p class="mb-0">{{ $app->project_id ?: 'Not specified' }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small fw-bold">Client Email</label>
                                        <p class="mb-0">{{ $app->client_email ?: 'Not specified' }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small fw-bold">Server Key</label>
                                        <p class="text-truncate mb-0 font-monospace small bg-light p-2 rounded">{{ $app->server_key ?: 'None' }}</p>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit App Modal -->
                    <div class="modal fade" id="editAppModal{{ $app->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title fw-bold">Edit Firebase App</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('admin.firebase.apps.update', $app->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">App Name <span class="text-danger">*</span></label>
                                            <input type="text" name="app_name" class="form-control" value="{{ $app->app_name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Package Name <span class="text-danger">*</span></label>
                                            <input type="text" name="package_name" class="form-control font-monospace" value="{{ $app->package_name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Firebase JSON File (Optional update)</label>
                                            <input type="file" name="firebase_json_file" class="form-control" accept=".json,.txt">
                                            <div class="form-text small">Upload service account JSON file from Firebase Console &rarr; Project Settings &rarr; Service Accounts.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">FCM Server Key (Optional)</label>
                                            <textarea name="server_key" class="form-control font-monospace" rows="3">{{ $app->server_key }}</textarea>
                                            <div class="form-text small">Legacy server key (optional).</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Update App</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-fire fa-3x text-secondary mb-3 d-block opacity-50"></i>
                            <h6 class="fw-bold">No Firebase Apps Added Yet</h6>
                            <p class="small mb-3">Click below to add your first Firebase app and service account credentials.</p>
                            <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addAppModal">
                                <i class="fa-solid fa-plus me-1"></i> Add New App
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add New Firebase App Modal (Screenshot 1) -->
<div class="modal fade" id="addAppModal" tabindex="-1" aria-labelledby="addAppModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0" style="background: #eff6ff; border-top-left-radius: 1rem; border-top-right-radius: 1rem; padding: 1.5rem 1.5rem 1rem;">
                <h5 class="modal-title fw-bold text-primary" id="addAppModalLabel" style="font-size: 1.25rem;">
                    Add New Firebase App
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.firebase.apps.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4 pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark mb-1">
                            App Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="app_name" class="form-control rounded-3 py-2" placeholder="e.g., My Awesome App" required>
                        <div class="form-text text-muted small mt-1">Enter a friendly name for your app</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark mb-1">
                            Package Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="package_name" class="form-control rounded-3 py-2 font-monospace" placeholder="com.myapp.example" required>
                        <div class="form-text text-muted small mt-1">Must be unique for each app</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark mb-1">
                            Firebase JSON File <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="firebase_json_file" class="form-control rounded-3 py-2" accept=".json,.txt" required>
                        <div class="form-text text-muted small mt-1">Download service account JSON file from Firebase Console &rarr; Project Settings &rarr; Service Accounts</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark mb-1">
                            FCM Server Key (Optional)
                        </label>
                        <textarea name="server_key" class="form-control rounded-3 font-monospace" rows="3" placeholder=""></textarea>
                        <div class="form-text text-muted small mt-1">Legacy server key (optional)</div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3" style="border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;">
                    <button type="button" class="btn btn-secondary px-4 py-2 rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save App
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
