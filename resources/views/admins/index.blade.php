@extends('layouts.app')

@section('content')
    @php
        $editingAdminId = old('editing_admin_id');
    @endphp
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="panel-card p-4">
                <span class="eyebrow">Admin Management</span>
                <h1 class="h3 mt-2 mb-3">Add Admin</h1>
                <div class="form-text mb-3">Admin login requires contact number and password.</div>
                <form action="{{ route('admins.store') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">Admin Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Admin Age</label>
                        <input type="number" name="age" value="{{ old('age') }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Admin Contact Number</label>
                        <input type="text" name="contact_number" value="{{ old('contact_number') }}" class="form-control" placeholder="98xxxxxxxx" maxlength="10" inputmode="numeric" title="Admin contact number must be exactly 10 digits.">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Admin Password</label>
                        <div class="input-group">
                            <input type="password" id="admin_create_password" name="password" class="form-control" placeholder="At least 8 characters">
                            <button type="button" class="btn btn-outline-secondary" data-password-toggle data-password-target="admin_create_password">Show</button>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" id="admin_create_password_confirmation" name="password_confirmation" class="form-control" placeholder="Repeat the same password">
                            <button type="button" class="btn btn-outline-secondary" data-password-toggle data-password-target="admin_create_password_confirmation">Show</button>
                        </div>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary flex-grow-1">Add</button>
                        <button type="reset" class="btn btn-outline-secondary flex-grow-1">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="panel-card p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                    <div>
                        <span class="eyebrow">Admin List</span>
                        <h2 class="h4 mt-2 mb-0">All admins</h2>
                    </div>
                    <div class="small text-secondary">Database uses the <strong>users</strong> table for contact number, password, and role, plus <strong>admin_profiles</strong> for age details.</div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Contact</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($admins as $admin)
                                <tr>
                                    <td>{{ $admin->name }}</td>
                                    <td>{{ $admin->adminProfile?->age }}</td>
                                    <td>{{ $admin->contact_number }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-dark" data-bs-toggle="collapse" data-bs-target="#editAdmin{{ $admin->id }}">Edit</button>
                                    </td>
                                </tr>
                                <tr class="collapse {{ (string) $editingAdminId === (string) $admin->id ? 'show' : '' }}" id="editAdmin{{ $admin->id }}">
                                    <td colspan="4">
                                        <div class="row g-3">
                                            <div class="col-lg-10">
                                                <form action="{{ route('admins.update', $admin) }}" method="POST" class="row g-3">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="editing_admin_id" value="{{ $admin->id }}">
                                                    <div class="col-md-4">
                                                        <input type="text" name="name" value="{{ (string) $editingAdminId === (string) $admin->id ? old('name', $admin->name) : $admin->name }}" class="form-control">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="number" name="age" value="{{ (string) $editingAdminId === (string) $admin->id ? old('age', $admin->adminProfile?->age) : $admin->adminProfile?->age }}" class="form-control">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="text" name="contact_number" value="{{ (string) $editingAdminId === (string) $admin->id ? old('contact_number', $admin->contact_number) : $admin->contact_number }}" class="form-control" maxlength="10" inputmode="numeric" title="Admin contact number must be exactly 10 digits.">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="input-group">
                                                            <input type="password" id="admin_edit_password_{{ $admin->id }}" name="password" class="form-control" placeholder="New password (optional)" value="{{ (string) $editingAdminId === (string) $admin->id ? old('password') : '' }}">
                                                            <button type="button" class="btn btn-outline-secondary" data-password-toggle data-password-target="admin_edit_password_{{ $admin->id }}">Show</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="input-group">
                                                            <input type="password" id="admin_edit_password_confirmation_{{ $admin->id }}" name="password_confirmation" class="form-control" placeholder="Confirm new password" value="{{ (string) $editingAdminId === (string) $admin->id ? old('password_confirmation') : '' }}">
                                                            <button type="button" class="btn btn-outline-secondary" data-password-toggle data-password-target="admin_edit_password_confirmation_{{ $admin->id }}">Show</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-text">To change login details, enter both new password fields. Leave them blank to keep the current password.</div>
                                                    </div>
                                                    @if($errors->any() && (string) $editingAdminId === (string) $admin->id)
                                                        <div class="col-12">
                                                            <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-0">
                                                                Please fix the password fields and save again.
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div class="col-12 d-flex gap-2">
                                                        <button class="btn btn-primary btn-sm px-4">Save</button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-toggle="collapse" data-bs-target="#editAdmin{{ $admin->id }}">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-lg-2">
                                                <form action="{{ route('admins.destroy', $admin) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Delete this admin?')">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.dataset.passwordTarget);

                if (! target) {
                    return;
                }

                const isHidden = target.type === 'password';
                target.type = isHidden ? 'text' : 'password';
                button.textContent = isHidden ? 'Hide' : 'Show';
            });
        });
    </script>
@endpush
