{{--
Toast Notification Component Usage Examples

This file demonstrates how to use the toast-notification component
based on the Display Position examples from ui-notifications.blade.php
--}}

{{-- Display Position Examples --}}
<div class="mt-4 pt-2">
    <h5 class="fs-14 mb-3">Display Position</h5>
    <div class="hstack flex-wrap gap-2">
        {{-- Top Left --}}
        <x-toast-notification text="Welcome Back ! This is a Toast Notification" gravity="top" position="left"
            duration="3000" :close="true">
            Top Left
        </x-toast-notification>

        {{-- Top Center --}}
        <x-toast-notification text="Welcome Back ! This is a Toast Notification" gravity="top" position="center"
            duration="3000" :close="true">
            Top Center
        </x-toast-notification>

        {{-- Top Right --}}
        <x-toast-notification text="Welcome Back ! This is a Toast Notification" gravity="top" position="right"
            duration="3000" :close="true">
            Top Right
        </x-toast-notification>

        {{-- Bottom Left --}}
        <x-toast-notification text="Welcome Back ! This is a Toast Notification" gravity="bottom" position="left"
            duration="3000" :close="true">
            Bottom Left
        </x-toast-notification>

        {{-- Bottom Center --}}
        <x-toast-notification text="Welcome Back ! This is a Toast Notification" gravity="bottom" position="center"
            duration="3000" :close="true">
            Bottom Center
        </x-toast-notification>

        {{-- Bottom Right --}}
        <x-toast-notification text="Welcome Back ! This is a Toast Notification" gravity="bottom" position="right"
            duration="3000" :close="true">
            Bottom Right
        </x-toast-notification>
    </div>
</div>

{{-- Type Variations (Primary, Success, Warning, Danger) --}}
<div class="mt-4 pt-2">
    <h5 class="fs-14 mb-3">Toast Types</h5>
    <div class="hstack flex-wrap gap-2">
        {{-- Primary Toast --}}
        <x-toast-notification text="Welcome Back! This is a Toast Notification" gravity="top" position="right"
            className="primary" duration="3000" :close="true" :style="true">
            Primary
        </x-toast-notification>

        {{-- Success Toast --}}
        <x-toast-notification text="Your application was successfully sent" gravity="top" position="center"
            className="success" duration="3000">
            Success
        </x-toast-notification>

        {{-- Warning Toast --}}
        <x-toast-notification text="Warning ! Something went wrong try again" gravity="top" position="center"
            className="warning" duration="3000">
            Warning
        </x-toast-notification>

        {{-- Danger Toast --}}
        <x-toast-notification text="Error ! An error occurred." gravity="top" position="center" className="danger"
            duration="3000">
            Error
        </x-toast-notification>
    </div>
</div>

{{-- Additional Options --}}
<div class="row mt-3">
    <div class="col-lg-4">
        <div class="mt-4">
            <h5 class="fs-14 mb-3">Offset Position</h5>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <x-toast-notification text="Welcome Back ! This is a Toast Notification" gravity="top" position="right"
                    duration="3000" :offset="true" :close="true">
                    Click Me
                </x-toast-notification>
            </div>
        </div>
    </div>
    <!--end col-->

    <div class="col-lg-4">
        <div class="mt-4">
            <h5 class="fs-14 mb-3">Close icon Display</h5>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <x-toast-notification text="Welcome Back ! This is a Toast Notification" position="right"
                    duration="3000" :close="true">
                    Click Me
                </x-toast-notification>
            </div>
        </div>
    </div>
    <!--end col-->

    <div class="col-lg-4">
        <div class="mt-4">
            <h5 class="fs-14 mb-3">Duration</h5>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <x-toast-notification text="Toast Duration 5s" gravity="top" position="right" duration="5000">
                    Click Me
                </x-toast-notification>
            </div>
        </div>
    </div>
    <!--end col-->
</div>
<!--end row-->

{{-- Custom Usage Examples --}}
<div class="mt-4 pt-2">
    <h5 class="fs-14 mb-3">Custom Usage</h5>
    <div class="hstack flex-wrap gap-2">
        {{-- Custom button style --}}
        <x-toast-notification text="Custom styled button" gravity="top" position="right" className="success"
            class="btn btn-success btn-sm">
            Custom Button
        </x-toast-notification>

        {{-- Using different tag --}}
        <x-toast-notification text="Click this link" gravity="top" position="right" tag="a"
            class="btn btn-link text-primary" :close="false">
            Link Style
        </x-toast-notification>
    </div>
</div>