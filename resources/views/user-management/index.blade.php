@extends('layouts.master')
@section('title')
    @lang('translation.dashboards')
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/dropzone/dropzone.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('build/libs/filepond/filepond.min.css') }}" type="text/css" />
    <link rel="stylesheet"
        href="{{ URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}">
    <link href="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <x-breadcrumb title="Users" li_1="Admin Management" />

    @include('user-management.user-blade.table-page')
    @include('user-management.modals.user-create-edit')

@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>
    <!-- dashboard init -->
    <script src="{{ URL::asset('build/js/pages/dashboard-ecommerce.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="{{ URL::asset('build/libs/dropzone/dropzone-min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/filepond/filepond.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js') }}">
    </script>
    <script
        src="{{ URL::asset('build/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js') }}">
        </script>
    <script
        src="{{ URL::asset('build/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js') }}">
        </script>
    <script src="{{ URL::asset('build/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js') }}"></script>

    {{-- Inline script to load and display users in the table (using invoiceslist.init.js as reference) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableBody = document.getElementById('user-list-data');
            const searchInput = document.getElementById('user-search');
            const checkAll = document.getElementById('checkAll');
            const bulkDeleteBtn = document.getElementById('remove-actions');
            const noResultBlock = document.querySelector('.noresult');
            const statusSelect = document.getElementById('idStatus');
            const statusFilterBtn = document.getElementById('status-filter-btn');
            const paginationWrap = document.querySelector('.pagination-wrap');
            const paginationPrev = document.querySelector('.pagination-prev');
            const paginationNext = document.querySelector('.pagination-next');
            const paginationList = document.querySelector('.pagination.listjs-pagination');

            let currentSearch = '';
            let currentStatus = '';
            let rowsPerPage = 10;
            let currentPage = 1;
            let allRows = [];
            let filteredRows = [];

            // Helper: build status badge HTML similar to invoices JS
            function buildStatusBadge(activeStatus) {
                const isActive = activeStatus === 1 || activeStatus === '1' || String(activeStatus).toLowerCase() === 'active';
                const label = isActive ? 'Active' : 'Inactive';
                const color = isActive ? 'success' : 'danger';
                return `<span class="badge bg-${color}-subtle text-${color} text-uppercase">${label}</span>`;
            }

            // Fetch users from backend and render table rows
            fetch("{{ route('user-management.users') }}")
                .then(response => response.json())
                .then(users => {
                    tableBody.innerHTML = '';

                    if (!users || users.length === 0) {
                        if (noResultBlock) noResultBlock.style.display = 'block';
                        return;
                    }

                    users.forEach(user => {
                        const rowHtml = `
                                                                                                <tr>
                                                                                                    <th scope="row">
                                                                                                        <div class="form-check">
                                                                                                            <input class="form-check-input" type="checkbox" name="chk_child" value="${user.id}">
                                                                                                        </div>
                                                                                                    </th>
                                                                                                    <td class="id">#${user.id}</td>
                                                                                                    <td class="customer_name">${user.name || ''}</td>
                                                                                                    <td class="email">${user.email || ''}</td>
                                                                                                    <td class="status">
                                                                                                        ${buildStatusBadge(user.active_status)}
                                                                                                    </td>
                                                                                                    <td class="action">
                                                                                                        <button class="btn btn-soft-primary btn-sm me-1" type="button">
                                                                                                            <i class="ri-eye-fill align-middle"></i>
                                                                                                        </button>
                                                                                                        <button class="btn btn-soft-warning btn-sm" type="button">
                                                                                                            <i class="ri-pencil-fill align-middle"></i>
                                                                                                        </button>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            `;
                        tableBody.insertAdjacentHTML('beforeend', rowHtml);
                    });

                    allRows = Array.from(tableBody.querySelectorAll('tr'));
                    attachRowCheckboxHandlers();
                    setupPaginationHandlers();
                    updateRowsVisibility();
                })
                .catch(error => {
                    console.error('Error loading users:', error);
                    if (noResultBlock) noResultBlock.style.display = 'block';
                });

            // Reusable visibility update (search + status filter together) + pagination (10 per page)
            function updateRowsVisibility() {
                let anyVisible = false;
                // Recompute filtered rows based on current search + status
                filteredRows = allRows.filter(row => {
                    const text = row.textContent.toLowerCase();
                    const statusCell = row.querySelector('.status');
                    const statusText = statusCell ? statusCell.textContent.trim().toLowerCase() : '';

                    const matchesSearch = !currentSearch || text.indexOf(currentSearch) !== -1;
                    const matchesStatus =
                        !currentStatus ||
                        statusText.indexOf(currentStatus) !== -1;

                    return matchesSearch && matchesStatus;
                });

                const totalRows = filteredRows.length;
                const totalPages = Math.max(1, Math.ceil(totalRows / rowsPerPage));

                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                // Hide all rows, then show only current page
                allRows.forEach(row => {
                    row.style.display = 'none';
                });

                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                filteredRows.slice(start, end).forEach(row => {
                    row.style.display = '';
                    anyVisible = true;
                });

                if (noResultBlock) noResultBlock.style.display = anyVisible ? 'none' : 'block';

                // Update pagination UI
                if (paginationWrap) {
                    if (totalRows <= rowsPerPage) {
                        paginationWrap.style.display = 'none';
                    } else {
                        paginationWrap.style.display = 'flex';
                    }
                }

                if (paginationList) {
                    paginationList.innerHTML = '';
                    for (let i = 1; i <= totalPages; i++) {
                        const li = document.createElement('li');
                        li.className = 'page-item' + (i === currentPage ? ' active' : '');
                        const a = document.createElement('a');
                        a.className = 'page-link';
                        a.href = '#';
                        a.dataset.page = i;
                        a.textContent = i;
                        li.appendChild(a);
                        paginationList.appendChild(li);
                    }
                }

                if (paginationPrev) {
                    if (currentPage === 1) {
                        paginationPrev.classList.add('disabled');
                    } else {
                        paginationPrev.classList.remove('disabled');
                    }
                }

                if (paginationNext) {
                    if (currentPage === totalPages) {
                        paginationNext.classList.add('disabled');
                    } else {
                        paginationNext.classList.remove('disabled');
                    }
                }
            }

            // Simple client-side search similar in spirit to invoiceslist.init.js
            if (searchInput) {
                searchInput.addEventListener('keyup', function () {
                    currentSearch = this.value.toLowerCase();
                    currentPage = 1;
                    updateRowsVisibility();
                });
            }

            // Status filter (Active / Inactive / All)
            function applyStatusFilter() {
                if (!statusSelect) return;
                // Empty value = no filter (show all)
                const value = statusSelect.value || '';
                currentStatus = value.toLowerCase();
                currentPage = 1;
                updateRowsVisibility();
            }

            if (statusSelect) {
                statusSelect.addEventListener('change', applyStatusFilter);
            }

            if (statusFilterBtn) {
                statusFilterBtn.addEventListener('click', applyStatusFilter);
            }

            // Pagination controls (Previous / Next / page numbers)
            function setupPaginationHandlers() {
                if (paginationPrev) {
                    paginationPrev.addEventListener('click', function (e) {
                        e.preventDefault();
                        if (paginationPrev.classList.contains('disabled')) return;
                        currentPage = Math.max(1, currentPage - 1);
                        updateRowsVisibility();
                    });
                }

                if (paginationNext) {
                    paginationNext.addEventListener('click', function (e) {
                        e.preventDefault();
                        if (paginationNext.classList.contains('disabled')) return;
                        currentPage = currentPage + 1;
                        updateRowsVisibility();
                    });
                }

                if (paginationList) {
                    paginationList.addEventListener('click', function (e) {
                        const target = e.target;
                        if (target.tagName.toLowerCase() === 'a' && target.dataset.page) {
                            e.preventDefault();
                            const page = parseInt(target.dataset.page, 10);
                            if (!isNaN(page)) {
                                currentPage = page;
                                updateRowsVisibility();
                            }
                        }
                    });
                }
            }

            // Check-all checkbox behavior (mirrors the invoices JS behavior)
            if (checkAll) {
                checkAll.addEventListener('click', function () {
                    const checkboxes = document.querySelectorAll('.form-check-all input[type="checkbox"][name="chk_child"]');
                    checkboxes.forEach(cb => {
                        cb.checked = checkAll.checked;
                        const tr = cb.closest('tr');
                        if (tr) {
                            if (cb.checked) {
                                tr.classList.add('table-active');
                            } else {
                                tr.classList.remove('table-active');
                            }
                        }
                    });

                    const checkedCount = document.querySelectorAll('[name="chk_child"]:checked').length;
                    if (bulkDeleteBtn) {
                        bulkDeleteBtn.style.display = checkedCount > 0 ? 'block' : 'none';
                    }
                });
            }

            function attachRowCheckboxHandlers() {
                const rowCheckboxes = document.querySelectorAll('[name="chk_child"]');
                rowCheckboxes.forEach(cb => {
                    cb.addEventListener('change', function (e) {
                        const tr = e.target.closest('tr');
                        if (tr) {
                            if (e.target.checked) {
                                tr.classList.add('table-active');
                            } else {
                                tr.classList.remove('table-active');
                            }
                        }

                        const checkedCount = document.querySelectorAll('[name="chk_child"]:checked').length;
                        if (bulkDeleteBtn) {
                            bulkDeleteBtn.style.display = checkedCount > 0 ? 'block' : 'none';
                        }
                    });
                });
            }
        });
    </script>
@endsection