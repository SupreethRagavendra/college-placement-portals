@if (session('status') === 'deletion-email-sent')
    <div class="alert alert-info">
        <i class="fas fa-envelope me-2"></i>
        <strong>Email Sent!</strong> We've sent a confirmation email to {{ Auth::user()->email }}. Please check your inbox and follow the instructions to complete the account deletion process. The link will expire in 24 hours.
    </div>
@endif

@if (session('status') === 'deletion-cancelled')
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Deletion Cancelled!</strong> Your account deletion request has been cancelled. Your account remains active.
    </div>
@endif

<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Warning:</strong> {{ __('Account deletion requires email confirmation. Once confirmed, all of your data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
</div>

<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
    <i class="fas fa-trash me-2"></i>{{ __('Request Account Deletion') }}
</button>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAccountModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ __('Confirm Account Deletion') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                
                <div class="modal-body">
                    <p class="mb-3">
                        {{ __('To protect your account, we will send a confirmation email to verify this deletion request.') }}
                    </p>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>What happens next:</strong>
                        <ol class="mb-0 mt-2">
                            <li>You'll receive an email at <strong>{{ Auth::user()->email }}</strong></li>
                            <li>Click the confirmation link in the email</li>
                            <li>Your account will be permanently deleted</li>
                        </ol>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Please enter your password to proceed:') }}</label>
                        <input type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" id="password" name="password" placeholder="{{ __('Password') }}" required>
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>{{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>{{ __('Delete Account') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->userDeletion->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
        modal.show();
    });
</script>
@endif
