@extends('layouts.master')
@section('title')
    Events
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/nouislider/nouislider.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('build/libs/gridjs/theme/mermaid.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/libs/flatpickr/flatpickr.min.css') }}">
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Vanilla JS Select Component Examples</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Single Select</h5>
                    </div>
                    <div class="card-body">
                        <form id="singleSelectForm">
                            <x-select-vanilla :options="[
            '1' => 'Option 1',
            '2' => 'Option 2',
            '3' => 'Option 3',
            '4' => 'Option 4',
            '5' => 'Option 5',
        ]" name="single_select" label="Select an option"
                                placeholder="Choose one..." :searchable="true" />

                            <button type="submit" class="btn btn-primary mt-3">Submit</button>
                        </form>

                        <div id="singleSelectOutput" class="mt-3"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Multiple Select</h5>
                    </div>
                    <div class="card-body">
                        <form id="multipleSelectForm">
                            <x-select-vanilla :options="[
            'apple' => 'Apple',
            'banana' => 'Banana',
            'cherry' => 'Cherry',
            'date' => 'Date',
            'elderberry' => 'Elderberry',
            'fig' => 'Fig',
            'grape' => 'Grape',
        ]"
                                name="multiple_select" label="Select multiple fruits" placeholder="Choose fruits..."
                                :multiple="true" :searchable="true" />

                            <button type="submit" class="btn btn-primary mt-3">Submit</button>
                        </form>

                        <div id="multipleSelectOutput" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">With Pre-selected Value</h5>
                    </div>
                    <div class="card-body">
                        <x-select-vanilla :options="[
            'red' => 'Red',
            'blue' => 'Blue',
            'green' => 'Green',
            'yellow' => 'Yellow',
        ]" name="color" label="Select a color" placeholder="Choose a color..." value="blue" />
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Disabled State</h5>
                    </div>
                    <div class="card-body">
                        <x-select-vanilla :options="[
            '1' => 'Option 1',
            '2' => 'Option 2',
            '3' => 'Option 3',
        ]"
                            name="disabled_select" label="Disabled select" placeholder="This is disabled"
                            :disabled="true" />
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Without Search</h5>
                    </div>
                    <div class="card-body">
                        <x-select-vanilla :options="[
            'xs' => 'Extra Small',
            's' => 'Small',
            'm' => 'Medium',
            'l' => 'Large',
            'xl' => 'Extra Large',
        ]" name="size" label="Select size" placeholder="Choose a size..."
                            :searchable="false" />
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Array Format Options</h5>
                    </div>
                    <div class="card-body">
                        <x-select-vanilla :options="[
            ['value' => '1', 'label' => 'First Option'],
            ['value' => '2', 'label' => 'Second Option'],
            ['value' => '3', 'label' => 'Third Option'],
        ]" name="array_format"
                            label="Array format select" placeholder="Select..." />
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Programmatic Control</h5>
                    </div>
                    <div class="card-body">
                        <x-select-vanilla :options="[
            'js' => 'JavaScript',
            'py' => 'Python',
            'php' => 'PHP',
            'java' => 'Java',
            'cpp' => 'C++',
        ]" name="language" id="programmable-select" label="Select programming language"
                            placeholder="Choose a language..." />

                        <div class="mt-3">
                            <button class="btn btn-sm btn-info" onclick="setLanguage('py')">Set to Python</button>
                            <button class="btn btn-sm btn-info" onclick="setLanguage('js')">Set to JavaScript</button>
                            <button class="btn btn-sm btn-warning" onclick="getLanguage()">Get Current Value</button>
                            <button class="btn btn-sm btn-secondary" onclick="clearLanguage()">Clear Selection</button>
                        </div>

                        <div id="programmaticOutput" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script>
        // Handle single select form submission
        document.getElementById('singleSelectForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const value = formData.get('single_select');
            document.getElementById('singleSelectOutput').innerHTML =
                '<div class="alert alert-success">Selected: <strong>' + (value || 'Nothing') + '</strong></div>';
        });

        // Handle multiple select form submission
        document.getElementById('multipleSelectForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const values = formData.getAll('multiple_select[]');
            document.getElementById('multipleSelectOutput').innerHTML =
                '<div class="alert alert-success">Selected: <strong>' + (values.length ? values.join(', ') : 'Nothing') + '</strong></div>';
        });

        // Programmatic control functions
        function setLanguage(value) {
            window['vanillaSelect_programmable-select'].setValue(value);
        }

        function getLanguage() {
            const value = window['vanillaSelect_programmable-select'].getValue();
            document.getElementById('programmaticOutput').innerHTML =
                '<div class="alert alert-info">Current value: <strong>' + (value || 'Nothing') + '</strong></div>';
        }

        function clearLanguage() {
            window['vanillaSelect_programmable-select'].setValue('');
        }
    </script>
@endsection