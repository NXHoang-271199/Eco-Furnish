<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">


<!-- Mirrored from themesbrand.com/velzon/html/master/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 29 Oct 2024 07:29:52 GMT -->

<head>

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />

    <title>@yield('title')</title>
    {{-- Điền các link CSS dùng chung --}}
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/admins/images/favicon.ico') }}">
    <link href="{{ asset('assets/libs/quill/quill.core.css" rel="stylesheet" type="text/css')}}" />
    <link href="{{ asset('assets/libs/quill/quill.bubble.css" rel="stylesheet" type="text/css')}}" />
    <link href="{{ asset('assets/libs/quill/quill.snow.css" rel="stylesheet" type="text/css')}}" />
    <!-- jsvectormap css -->
    <link href="{{ asset('assets/admins/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />

    <!--Swiper slider css-->
    <link href="{{ asset('assets/admins/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Layout config Js -->
    <script src="{{ asset('assets/admins/js/layout.js') }}"></script>
    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/admins/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/admins/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/admins/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="{{ asset('assets/admins/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Choices.js CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admins/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!-- ckeditor -->
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/44.2.1/ckeditor5.css"/>
    @yield('CSS')
</head>

<body>
    <div id="layout-wrapper">

        @include('admins.blocks.header')

        @include('admins.blocks.siderbar')

        <div class="vertical-overlay"></div>

        <div class="main-content">
            <div class="page-content">
                <x-alert />
                @yield('content')
            </div>

            @include('admins.blocks.footer')

        </div>
    </div>

    {{-- Các đoạn script dùng chung --}}
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="{{ asset('assets/admins/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admins/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/admins/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/admins/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('assets/admins/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('assets/admins/js/plugins.js') }}"></script>

    <!-- apexcharts -->
    <script src="{{ asset('assets/admins/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Vector map-->
    <script src="{{ asset('assets/admins/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/admins/libs/jsvectormap/maps/world-merc.js') }}"></script>

    <!--Swiper slider js-->
    <script src="{{ asset('assets/admins/libs/swiper/swiper-bundle.min.js') }}"></script>

  <!-- ckeditor -->
  {{-- <script src="{{ asset('assets/libs/%40ckeditor/ckeditor5-build-classic/build/ckeditor.js')}}"></script> --}}

    <!-- quill js -->
    {{-- <script src="{{ asset('assets/libs/quill/quill.min.js')}}"></script> --}}

    <!-- Dashboard init -->
    <script src="{{ asset('assets/admins/js/pages/dashboard-ecommerce.init.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/admins/js/app.js') }}"></script>
    <!-- jQuery -->
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- Choices.js -->
    <script src="{{ asset('assets/admins/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <!-- Flatpickr -->
    <script src="{{ asset('assets/admins/libs/flatpickr/flatpickr.min.js') }}"></script>
    <!-- CKEditor -->
    <script type="importmap">
        {
            "imports": {
                "ckeditor5": "{{ asset('assets/admins/js/ckeditor5/ckeditor5.js') }}",
                "ckeditor5/": "{{ asset('assets/admins/js/ckeditor5/') }}"
            }
        }
    </script>
    <script type="module">
        import {
            ClassicEditor,
            AccessibilityHelp,
            Autosave,
            Essentials,
            Italic,
            Mention,
            Paragraph,
            SelectAll,
            Undo,
            Font,
            Heading,
            Strikethrough,
            Subscript,
            Superscript,
            CodeBlock,
            Link,
            AutoLink,
            AutoImage,
            Image,
            ImageInsert,
            BlockQuote,
            List,
            TodoList,
            Indent,
            IndentBlock,
            SimpleUploadAdapter,
            Alignment,
            ImageResizeEditing,
            ImageResizeHandles,
            Table,
            TableToolbar,
            Bold,
            SourceEditing,
            RemoveFormat,
            HorizontalLine,
            SpecialCharacters,
            SpecialCharactersEssentials
        } from 'ckeditor5';

        $('textarea').each(function (index, element) {
            const name = $(element).attr('name');
            ClassicEditor
                .create(element, {
                    plugins: [
                        Essentials,
                        Heading,
                        Strikethrough,
                        Paragraph,
                        Bold,
                        Italic,
                        AccessibilityHelp,
                        Autosave,
                        Mention,
                        SelectAll,
                        Undo,
                        Font,
                        Subscript,
                        Superscript,
                        CodeBlock,
                        Link,
                        AutoLink,
                        AutoImage,
                        Image,
                        ImageInsert,
                        BlockQuote,
                        List,
                        TodoList,
                        Indent,
                        IndentBlock,
                        SimpleUploadAdapter,
                        Alignment,
                        ImageResizeEditing,
                        ImageResizeHandles,
                        TableToolbar,
                        Table,
                        SourceEditing,
                        RemoveFormat,
                        HorizontalLine,
                        SpecialCharacters,
                        SpecialCharactersEssentials
                    ],
                    table: {
                        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'],
                        defaultHeadings: { rows: 1, columns: 1 }
                    },
                    toolbar: [
                        'undo', 'redo',
                        '|',
                        'heading', 'insertTable', 'sourceEditing', 'removeFormat', 'horizontalLine',
                        '|',
                        'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor', 'specialCharacters',
                        '|',
                        'bold', 'italic', 'strikethrough', 'subscript', 'superscript', 'codeBlock',
                        '|',
                        'link', 'insertImage', 'blockQuote', 'codeBlock',
                        '|',
                        'bulletedList', 'numberedList', 'todoList', 'outdent', 'indent', 'alignment'
                    ],
                    simpleUpload: {
                        uploadUrl: '{{ route('upload.image') }}',
                        withCredentials: true,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }
                })
                .then(editor => {
                    // Xử lý sự kiện paste
                    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                        return {
                            upload: () => {
                                return new Promise((resolve, reject) => {
                                    const formData = new FormData();
                                    loader.file.then(file => {
                                        formData.append('upload', file);

                                        fetch('{{ route('upload.image') }}', {
                                            method: 'POST',
                                            body: formData,
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            }
                                        })
                                        .then(response => response.json())
                                        .then(result => {
                                            if (result.uploaded) {
                                                resolve({
                                                    default: result.url
                                                });
                                            } else {
                                                reject(result.error.message);
                                            }
                                        })
                                        .catch(error => reject(error));
                                    });
                                });
                            },
                            abort: () => {}
                        };
                    };

                    window[name] = editor;
                })
                .catch(error => {
                    console.error(error);
                });
        });
    </script>

    @yield('JS')
</body>

</html>
