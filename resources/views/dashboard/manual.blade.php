@extends('layouts.master')
@section('title')
    Manual
@endsection

@section('css')
    <style>
        :root {
            --fb-bg: #1a1d29;
            --fb-card: #252836;
            --fb-accent: #3b82f6;
            --fb-text: #e4e6ef;
            --fb-toolbar: rgba(30, 32, 45, 0.9);
        }

        /* Wrapper */
        .manual-flipbook-wrapper {
            background: radial-gradient(circle at center, #2d3142 0%, #1a1d29 100%);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            min-height: 800px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Toolbar */
        .manual-toolbar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 0.75rem 1.5rem;
            background: var(--fb-toolbar);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            z-index: 100;
            transition: all 0.3s ease;
        }

        .manual-toolbar .btn-nav {
            background: rgba(255, 255, 255, 0.05);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .manual-toolbar .btn-nav:hover:not(:disabled) {
            background: var(--fb-accent);
            transform: scale(1.1);
        }

        .manual-toolbar .btn-nav:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .manual-page-info {
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--fb-text);
            padding: 0 1rem;
            border-left: 1px solid rgba(255, 255, 255, 0.1);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            min-width: 100px;
            text-align: center;
        }

        /* Flipbook Container */
        .manual-flipbook-container {
            position: relative;
            width: 100%;
            height: 100%;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            perspective: 2000px;
        }

        #flipbook {
            transition: transform 0.3s ease; 
            transform-origin: center center;
        }

        #flipbook .page {
            background-color: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }

        /* Spine Shadow Effect */
        #flipbook .page.even {
            background: linear-gradient(to right, #fff 95%, #ddd 100%);
        }
        
        #flipbook .page.odd {
            background: linear-gradient(to left, #fff 95%, #ddd 100%);
        }

        #flipbook .page-wrapper {
            perspective: 2000px;
        }
        
        #flipbook .odd {
            background: linear-gradient(to right, #f5f5f5 95%, #ddd 100%);
        }

        #flipbook .even {
            background: linear-gradient(to left, #f5f5f5 95%, #ddd 100%);
        }

        .page-content {
            width: 100%;
            height: 100%;
            overflow: hidden;
            position: relative;
        }

        .page-content canvas {
            /* Zoom in to crop margins */
            width: 100% !important; 
            height: 100% !important;
            
            /* Center the zoomed canvas */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            
            object-fit: contain;
            padding: 20px;
            box-sizing: border-box;
        }

        /* Loading Overlay */
        .manual-loading-overlay {
            position: absolute;
            inset: 0;
            background: var(--fb-bg);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease;
        }

        .manual-loading-overlay.fade-out {
            opacity: 0;
            pointer-events: none;
        }

        /* Error Message */
        .error-message {
            text-align: center;
            color: #ef4444;
            padding: 2rem;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 8px;
            display: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .manual-flipbook-wrapper {
                padding: 0.5rem; /* Reduced padding */
                min-height: auto; /* Remove fixed min-height */
            }
            .manual-toolbar {
                gap: 0.5rem;
                padding: 0.5rem;
                flex-wrap: wrap;
            }
            .manual-page-info {
                padding: 0 0.5rem;
                min-width: auto;
                font-size: 0.8rem;
            }
        }
        
        /* Drag & Zoom */
        .manual-flipbook-container.grab {
            cursor: grab;
        }
        .manual-flipbook-container.grabbing {
            cursor: grabbing;
        }
    </style>
@endsection

@section('content')
    <x-breadcrumb title="Digital User Manual" li_1="Dashboard" />

    <div class="manual-flipbook-wrapper" id="flipbookWrapper">
        <!-- Loader -->
        <div class="manual-loading-overlay" id="mainLoader">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
            <h5 class="mt-4 text-white" id="loadingText">Initializing...</h5>
            <div class="error-message mt-3" id="errorMessage">
                <i class="ri-error-warning-line fs-24 mb-2"></i>
                <p class="mb-2">Failed to load the manual.</p>
                <a href="{{ route('manual.show.pdf') }}" target="_blank" class="btn btn-sm btn-outline-light">Download Manual Instead</a>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="manual-toolbar">
            <button class="btn-nav" id="btnFirst" title="First Page"><i class="ri-arrow-left-double-line"></i></button>
            <button class="btn-nav" id="btnPrev" title="Previous"><i class="ri-arrow-left-s-line"></i></button>
            
            <div class="manual-page-info">
                <span id="currentPage">0</span> / <span id="totalPages">0</span>
            </div>

            <button class="btn-nav" id="btnNext" title="Next"><i class="ri-arrow-right-s-line"></i></button>
            <button class="btn-nav" id="btnLast" title="Last Page"><i class="ri-arrow-right-double-line"></i></button>
            
            <div class="ms-2 d-flex gap-2 border-start border-white border-opacity-10 ps-2">
                <button class="btn-nav" id="btnZoomOut" title="Zoom Out"><i class="ri-zoom-out-line"></i></button>
                <button class="btn-nav" id="btnZoomIn" title="Zoom In"><i class="ri-zoom-in-line"></i></button>
            </div>
        </div>

        <!-- Flipbook -->
        <div class="manual-flipbook-container" id="flipbookContainer">
            <div id="flipbook">
                <!-- Pages will be injected here -->
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    
    <!-- PDF.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Turn.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/turn.js/3/turn.min.js"></script>

    <script>
        $(document).ready(function() {
            // Configuration
            const pdfUrl = '{{ route("manual.show.pdf") }}'; // Ensure this route is defined
            const $flipbook = $('#flipbook');
            const $container = $('#flipbookContainer');
            const $mainLoader = $('#mainLoader');
            const $loadingText = $('#loadingText');
            const $errorMessage = $('#errorMessage');
            
            // State
            let pdfDoc = null;
            let totalPages = 0;
            let currentScale = 1;
            let renderedCount = 0;
            let isMobile = window.innerWidth < 768;

            // Drag State
            let isDragging = false;
            let startX, startY, translateX = 0, translateY = 0;

            // Calculate dimensions based on screen size
            function getBookDimensions() {
                const isSmall = window.innerWidth < 768;
                // On mobile: width is screen width minus padding
                // On desktop: fixed reasonable width or scaled based on container
                let w = isSmall ? $(window).width() - 40 : 960; // Increased margin for mobile
                // Maintain aspect ratio. 
                // Mobile: Use 1.414 (Standard A4)
                // Desktop: Use 600px height default
                let h = isSmall ? (w * 1.414) : 600; 
                
                return { width: w, height: h, display: isSmall ? 'single' : 'double' };
            }

            // Initialize
            async function init() {
                try {
                    $loadingText.text('Loading PDF Document...');
                    pdfDoc = await pdfjsLib.getDocument(pdfUrl).promise;
                    totalPages = pdfDoc.numPages;
                    $('#totalPages').text(totalPages);

                    // Render all pages first
                    await renderAllPages();

                    // Initialize Turn.js after rendering
                    initTurnJs();

                    // Complete
                    $mainLoader.addClass('fade-out');

                } catch (error) {
                    console.error('Error loading PDF:', error);
                    $loadingText.hide();
                    $errorMessage.show().find('.spinner-border').remove();
                }
            }

            async function renderAllPages() {
                for (let i = 1; i <= totalPages; i++) {
                    $loadingText.text(`Rendering Page ${i} of ${totalPages}...`);
                    
                    // Create page container
                    const $page = $('<div />', { 'class': 'page' });
                    const $content = $('<div />', { 'class': 'page-content' });
                    const canvas = document.createElement('canvas');
                    $content.append(canvas);
                    $page.append($content);
                    $flipbook.append($page);

                    // Render PDF page to canvas
                    const page = await pdfDoc.getPage(i);
                    // Standard scale for rendering
                    const viewport = page.getViewport({ scale: 2.0 }); 
                    const context = canvas.getContext('2d');
                    
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    await page.render({
                        canvasContext: context,
                        viewport: viewport
                    }).promise;
                    
                    renderedCount++;
                }
            }

            function initTurnJs() {
                const dims = getBookDimensions();

                $flipbook.turn({
                    width: dims.width,
                    height: dims.height,
                    autoCenter: true,
                    display: dims.display,
                    duration: 1000,
                    acceleration: true,
                    gradients: true,
                    elevation: 50,
                    when: {
                        turning: function(e, page, view) {
                            $('#currentPage').text(page);
                            updateNavButtons(page);
                        },
                        turned: function(e, page, view) {
                             $('#currentPage').text(page);
                             updateNavButtons(page);
                        }
                    }
                });

                // Set initial page text
                $('#currentPage').text(1);
                updateNavButtons(1);
            }

            function updateNavButtons(page) {
                $('#btnPrev, #btnFirst').prop('disabled', page === 1);
                $('#btnNext, #btnLast').prop('disabled', page === totalPages);
            }

            function applyTransform() {
                $flipbook.css('transform', `scale(${currentScale}) translate(${translateX}px, ${translateY}px)`);
                
                // Update cursor
                if (currentScale > 1) {
                    $container.addClass('grab');
                    // Disable animation when zoomed to prevent alignment bugs
                    if ($flipbook.data().turn) $flipbook.data().turn.opts.duration = 0;
                } else {
                    $container.removeClass('grab grabbing');
                    // data().turn might be undefined during init, check first
                    if ($flipbook.data().turn) $flipbook.data().turn.opts.duration = 1000;
                }
            }

            // Controls
            $('#btnNext').click(() => $flipbook.turn('next'));
            $('#btnPrev').click(() => $flipbook.turn('previous'));
            $('#btnFirst').click(() => $flipbook.turn('page', 1));
            $('#btnLast').click(() => $flipbook.turn('page', totalPages));
            
            $('#btnZoomIn').click(() => {
                if (currentScale < 2) {
                    currentScale += 0.2;
                    if (currentScale > 2) currentScale = 2; // Clamp to max 200%
                    applyTransform();
                }
            });
            
            $('#btnZoomOut').click(() => {
                if (currentScale > 0.4) {
                    currentScale -= 0.2;
                    // Reset translation if zoomed out to 1 or less
                    if (currentScale <= 1) {
                        currentScale = 1;
                        translateX = 0;
                        translateY = 0;
                    }
                    applyTransform();
                }
            });

            // Drag Events
            $container.on('mousedown', function(e) {
                if (currentScale > 1) {
                    isDragging = true;
                    startX = e.pageX - translateX;
                    startY = e.pageY - translateY;
                    $container.addClass('grabbing');
                    e.preventDefault(); // Prevent text selection
                }
            });

            $(document).on('mousemove', function(e) {
                if (isDragging) {
                    e.preventDefault();
                    translateX = e.pageX - startX;
                    translateY = e.pageY - startY;
                    applyTransform();
                }
            });

            $(document).on('mouseup mouseleave', function() {
                if (isDragging) {
                    isDragging = false;
                    $container.removeClass('grabbing');
                }
            });

            // Keyboard Navigation
            $(document).keydown(function(e) {
                if (e.keyCode == 37) $flipbook.turn('previous');
                if (e.keyCode == 39) $flipbook.turn('next');
            });

            // Responsive Resize
            let resizeTimer;
            $(window).resize(function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                     const dims = getBookDimensions();
                     $flipbook.turn('size', dims.width, dims.height);
                     
                     // Check if display mode needs to change
                     const currentDisplay = $flipbook.turn('display');
                     if (currentDisplay !== dims.display) {
                         $flipbook.turn('display', dims.display);
                     }
                }, 200);
            });

            // Start
            init();
        });

        // Global Helper for TOC or External access
        window.jumpToPage = function(pageNumber) {
            $('#flipbook').turn('page', pageNumber);
        }
    </script>
@endsection
