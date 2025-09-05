<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - College Placement Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="card-title mb-3">Verify your email</h3>
                        <p class="mb-4">We've sent a verification link to your email address. Please click the link in the email to complete your registration. The link will expire in 1 hour.</p>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success">
                                A new verification link has been sent to your email.
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('login') }}" class="btn btn-primary">Back to Login</a>
                            <a href="{{ route('landing') }}" class="btn btn-outline-secondary">Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>


