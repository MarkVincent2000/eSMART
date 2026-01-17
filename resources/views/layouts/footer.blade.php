<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <script>document.write(new Date().getFullYear())</script> ©
                {{ \App\Models\SystemSetting::getValue('site.name', 'SMART CAMPUS') }}.
            </div>
            <div class="col-sm-6">
                <div class="text-sm-end d-none d-sm-block">
                    {{ \App\Models\SystemSetting::getValue('site.footer_text', 'Design & Develop by SMART CAMPUS TEAM') }}
                </div>
            </div>
        </div>
    </div>
</footer>