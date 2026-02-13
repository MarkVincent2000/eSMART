@extends('layouts.master-without-nav')
@section('title')
    @lang('translation.signup')
@endsection
@section('content')
    <!-- auth-page wrapper -->
    <div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="bg-overlay"></div>
        <!-- auth-page content -->
        <div class="auth-page-content overflow-hidden pt-lg-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card overflow-hidden m-0 card-bg-fill galaxy-border-none">
                            <div class="row justify-content-center g-0">
                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4 auth-one-bg h-100">
                                        <div class="bg-overlay"></div>
                                        <div class="position-relative h-100 d-flex flex-column">
                                            <div class="mb-1 text-center">
                                                <a href="{{ url('/') }}" class="d-block text-decoration-none">
                                                    <img src="{{ \App\Models\SystemSetting::getAsset('site.login_logo', URL::asset('build/images/smart-logo-sm3.png')) }}"
                                                        alt="" height="100">
                                                    <h3 class="text-white mt-2 mb-0 fw-semibold"
                                                        style="text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3); -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
                                                        {{ \App\Models\SystemSetting::getValue('site.short_name', 'Smart') }}
                                                    </h3>
                                                </a>
                                            </div>
                                            <div class="mt-auto">
                                                <div class="mb-3">
                                                    <i class="ri-double-quotes-l display-4 text-success"></i>
                                                </div>

                                                <div id="qoutescarouselIndicators" class="carousel slide"
                                                    data-bs-ride="carousel">
                                                    <div class="carousel-indicators">
                                                        <button type="button" data-bs-target="#qoutescarouselIndicators"
                                                            data-bs-slide-to="0" class="active" aria-current="true"
                                                            aria-label="Slide 1"></button>
                                                        <button type="button" data-bs-target="#qoutescarouselIndicators"
                                                            data-bs-slide-to="1" aria-label="Slide 2"></button>
                                                        <button type="button" data-bs-target="#qoutescarouselIndicators"
                                                            data-bs-slide-to="2" aria-label="Slide 3"></button>
                                                    </div>
                                                    <div class="carousel-inner text-center text-white-50 pb-5">
                                                        <div class="carousel-item active">
                                                            <p class="fs-15 fst-italic">"
                                                                {{ \App\Models\SystemSetting::getValue('landing.quote_1', 'Sa Manongol High, Gaganda ang Buhay!') }}"
                                                            </p>
                                                        </div>
                                                        <div class="carousel-item">
                                                            <p class="fs-15 fst-italic">"
                                                                {{ \App\Models\SystemSetting::getValue('landing.quote_2', 'Manage your campus tasks in one unified place.') }}"
                                                            </p>
                                                        </div>
                                                        <div class="carousel-item">
                                                            <p class="fs-15 fst-italic">"
                                                                {{ \App\Models\SystemSetting::getValue('landing.quote_3', 'SMART Campus keeps everyone connected and informed.') }}"
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- end carousel -->

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4">
                                        <div>
                                            <h5 class="text-primary">
                                                {{ \App\Models\SystemSetting::getValue('auth.register_title', 'Create New Account') }}
                                            </h5>
                                            <p class="text-muted">
                                                {{ \App\Models\SystemSetting::getValue('auth.register_subtitle', 'Get your free eSMART Campus account now') }}
                                            </p>
                                        </div>

                                        <div class="mt-4">
                                            <form class="needs-validation" novalidate method="POST"
                                                action="{{ route('register') }}">
                                                @csrf

                                                <div class="mb-3">
                                                    <label for="useremail" class="form-label">Email <span
                                                            class="text-danger">*</span></label>
                                                    <input type="email"
                                                        class="form-control @error('email') is-invalid @enderror"
                                                        name="email" value="{{ old('email') }}" id="useremail"
                                                        placeholder="Enter email address" required>
                                                    @error('email')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                    <div class="invalid-feedback">
                                                        Please enter email
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="password-input" class="form-label">Password <span
                                                            class="text-danger">*</span></label>
                                                    <div class="position-relative auth-pass-inputgroup">
                                                        <input type="password"
                                                            class="form-control pe-5 password-input material-shadow-none @error('password') is-invalid @enderror"
                                                            name="password" id="password-input" placeholder="Enter password"
                                                            onpaste="return false"
                                                            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>
                                                        <button
                                                            class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                            type="button" id="password-addon"><i
                                                                class="ri-eye-fill align-middle"></i></button>
                                                        @error('password')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <div class="invalid-feedback">
                                                            Password must be at least 8 characters and include uppercase, lowercase, and a number
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="confirm-password-input" class="form-label">Confirm Password <span
                                                            class="text-danger">*</span></label>
                                                    <div class="position-relative auth-pass-inputgroup">
                                                        <input type="password"
                                                            class="form-control pe-5 password-input material-shadow-none @error('password_confirmation') is-invalid @enderror"
                                                            name="password_confirmation" id="confirm-password-input"
                                                            placeholder="Enter Confirm Password" onpaste="return false"
                                                            required>
                                                        <button
                                                            class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                            type="button" id="password-confirm-addon"><i
                                                                class="ri-eye-fill align-middle"></i></button>
                                                        <div class="invalid-feedback">
                                                            Password confirmation must match the password
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-4">
                                                    <p class="mb-0 fs-12 text-muted fst-italic">By registering you agree to
                                                        the eSMART Campus <a href="#" data-bs-toggle="modal"
                                                            data-bs-target="#termsModal"
                                                            class="text-primary text-decoration-underline fst-normal fw-medium">Terms
                                                            of Use</a></p>
                                                </div>

                                                <div id="password-contain" class="p-3 bg-light mb-2 rounded">
                                                    <h5 class="fs-13">Password must contain:</h5>
                                                    <p id="pass-length" class="invalid fs-12 mb-2">Minimum <b>8
                                                            characters</b></p>
                                                    <p id="pass-lower" class="invalid fs-12 mb-2">At <b>lowercase</b> letter
                                                        (a-z)</p>
                                                    <p id="pass-upper" class="invalid fs-12 mb-2">At least <b>uppercase</b>
                                                        letter (A-Z)</p>
                                                    <p id="pass-number" class="invalid fs-12 mb-0">A least <b>number</b>
                                                        (0-9)</p>
                                                </div>

                                                <div class="mt-4">
                                                    <button class="btn btn-success w-100" type="submit">Sign Up</button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="mt-5 text-center">
                                            <p class="mb-0">Already have an account ? <a href="{{ route('login') }}"
                                                    class="fw-semibold text-primary text-decoration-underline"> Signin</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->

                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->

        <!-- footer -->
        <footer class="footer galaxy-border-none">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0">&copy;
                                <script>
                                    document.write(new Date().getFullYear())
                                </script>
                                {{ \App\Models\SystemSetting::getValue('site.footer_text', 'eSMART Campus. Crafted with ❤️ by eSMART Campus Team') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
    </div>
    <!-- end auth-page-wrapper -->

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions - eSMART Campus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">1. Acceptance of Terms</h6>
                        <p class="text-muted">By accessing and using the eSMART Campus platform, you accept and agree to be
                            bound by the terms and provision of this agreement. If you do not agree to abide by the above,
                            please do not use this service.</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-primary mb-3">2. Use License</h6>
                        <p class="text-muted">Permission is granted to temporarily access the materials on eSMART Campus's
                            website for personal, non-commercial transitory viewing only. This is the grant of a license,
                            not a transfer of title, and under this license you may not:</p>
                        <ul class="text-muted">
                            <li>Modify or copy the materials</li>
                            <li>Use the materials for any commercial purpose or for any public display</li>
                            <li>Attempt to reverse engineer any software contained on the platform</li>
                            <li>Remove any copyright or other proprietary notations from the materials</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-primary mb-3">3. User Account</h6>
                        <p class="text-muted">When you create an account with us, you must provide information that is
                            accurate, complete, and current at all times. You are responsible for safeguarding the password
                            and for all activities that occur under your account.</p>
                        <p class="text-muted">You agree not to disclose your password to any third party and to take sole
                            responsibility for any activities or actions under your account, whether or not you have
                            authorized such activities or actions.</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-primary mb-3">4. Academic Integrity</h6>
                        <p class="text-muted">All users of eSMART Campus are expected to maintain the highest standards of
                            academic integrity. This includes:</p>
                        <ul class="text-muted">
                            <li>Submitting original work and properly citing sources</li>
                            <li>Not engaging in plagiarism, cheating, or academic dishonesty</li>
                            <li>Respecting intellectual property rights</li>
                            <li>Following all academic policies and procedures</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-primary mb-3">5. Privacy and Data Protection</h6>
                        <p class="text-muted">eSMART Campus is committed to protecting your privacy. We collect and use your
                            personal information in accordance with our Privacy Policy. By using our platform, you consent
                            to the collection and use of information in accordance with this policy.</p>
                        <p class="text-muted">We implement appropriate security measures to protect your personal data
                            against unauthorized access, alteration, disclosure, or destruction.</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-primary mb-3">6. Code of Conduct</h6>
                        <p class="text-muted">Users must conduct themselves in a respectful and professional manner.
                            Prohibited behaviors include:</p>
                        <ul class="text-muted">
                            <li>Harassment, bullying, or discrimination of any kind</li>
                            <li>Posting offensive, inappropriate, or illegal content</li>
                            <li>Impersonating others or providing false information</li>
                            <li>Interfering with the platform's operation or security</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-primary mb-3">7. Intellectual Property</h6>
                        <p class="text-muted">The platform and its original content, features, and functionality are owned
                            by eSMART Campus and are protected by international copyright, trademark, patent, trade secret,
                            and other intellectual property laws.</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-primary mb-3">8. Termination</h6>
                        <p class="text-muted">We may terminate or suspend your account and bar access to the platform
                            immediately, without prior notice or liability, for any reason whatsoever, including without
                            limitation if you breach the Terms.</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-primary mb-3">9. Limitation of Liability</h6>
                        <p class="text-muted">In no event shall eSMART Campus, nor its directors, employees, partners,
                            agents, suppliers, or affiliates, be liable for any indirect, incidental, special,
                            consequential, or punitive damages, including without limitation, loss of profits, data, use,
                            goodwill, or other intangible losses, resulting from your use of the platform.</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-primary mb-3">10. Changes to Terms</h6>
                        <p class="text-muted">We reserve the right, at our sole discretion, to modify or replace these Terms
                            at any time. If a revision is material, we will provide at least 30 days notice prior to any new
                            terms taking effect.</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-primary mb-3">11. Contact Information</h6>
                        <p class="text-muted">If you have any questions about these Terms and Conditions, please contact us
                            through the eSMART Campus support channels.</p>
                    </div>

                    <div class="alert alert-info mt-4">
                        <p class="mb-0"><strong>Last Updated:</strong> {{ date('F Y') }}</p>
                        <p class="mb-0 mt-2">By continuing to use eSMART Campus, you acknowledge that you have read,
                            understood, and agree to be bound by these Terms and Conditions.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Terms and Conditions Modal -->
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/passowrd-create.init.js') }}"></script>
@endsection