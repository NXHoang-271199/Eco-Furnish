@extends('layouts.admin')

@section('title')
    Thêm mới bài viết
@endsection

@section('JS')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    var quill = new Quill('#editor-container', {
        theme: 'snow'
    });
</script>
@endsection
@section('CSS')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .quill-editor {
            height: 450px;
            background: #fff;
        }
    </style>

@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Thêm mới bài viết</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Quản lý bài viết</a></li>
                            <li class="breadcrumb-item active">Thêm mới bài viết</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="project-title-input">Project Title</label>
                            <input type="text" class="form-control" id="project-title-input"
                                placeholder="Enter project title">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="project-thumbnail-img">Thumbnail Image</label>
                            <input class="form-control" id="project-thumbnail-img" type="file"
                                accept="image/png, image/gif, image/jpeg">
                        </div> 
                        
                        <div class="mb-3">
                            <label class="form-label">Project Description</label>
                            <div id="editor-container" class="quill-editor"></div>
                        </div>
                    </div>
                    
                </div>
                <!-- end card -->
                <div class="text-end mb-4">
                    <button type="submit" class="btn btn-danger w-sm">Delete</button>
                    <button type="submit" class="btn btn-secondary w-sm">Draft</button>
                    <button type="submit" class="btn btn-success w-sm">Create</button>
                </div>
            </div>
            <!-- end col -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tags</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="choices-categories-input" class="form-label">Categories</label>
                            <div class="choices" data-type="select-one" tabindex="0" role="listbox"
                                aria-label="Categories" aria-haspopup="true" aria-expanded="false">
                                <div class="choices__inner"><select class="form-select choices__input" data-choices=""
                                        data-choices-search-false="" id="choices-categories-input" hidden=""
                                        tabindex="-1" data-choice="active">
                                        <option value="Designing" selected="">Designing</option>
                                        <option value="Development">Development</option>
                                    </select>
                                    <div class="choices__list choices__list--single">
                                        <div class="choices__item choices__item--selectable" data-item=""
                                            data-id="1" data-value="Designing" aria-selected="true" role="option">
                                            Designing</div>
                                    </div>
                                </div>
                                <div class="choices__list choices__list--dropdown" aria-expanded="false">
                                    <div class="choices__list" role="listbox">
                                        <div id="choices--choices-categories-input-item-choice-1"
                                            class="choices__item choices__item--choice is-selected choices__item--selectable is-highlighted"
                                            role="option" data-choice="" data-id="1" data-value="Designing"
                                            data-select-text="Press to select" data-choice-selectable=""
                                            aria-selected="true">Designing</div>
                                        <div id="choices--choices-categories-input-item-choice-2"
                                            class="choices__item choices__item--choice choices__item--selectable"
                                            role="option" data-choice="" data-id="2" data-value="Development"
                                            data-select-text="Press to select" data-choice-selectable="">Development</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="choices-text-input" class="form-label">Skills</label>
                            <div class="choices" data-type="text">
                                <div class="choices__inner"><input class="form-control choices__input"
                                        id="choices-text-input" data-choices="" data-choices-limit="Required Limit"
                                        placeholder="Enter Skills" type="text"
                                        value="UI/UX, Figma, HTML, CSS, Javascript, C#, Nodejs" hidden=""
                                        tabindex="-1" data-choice="active">
                                    <div class="choices__list choices__list--multiple">
                                        <div class="choices__item choices__item--selectable" data-item=""
                                            data-id="1" data-value="UI/UX">UI/UX</div>
                                        <div class="choices__item choices__item--selectable" data-item=""
                                            data-id="2" data-value=" Figma"> Figma</div>
                                        <div class="choices__item choices__item--selectable" data-item=""
                                            data-id="3" data-value=" HTML"> HTML</div>
                                        <div class="choices__item choices__item--selectable" data-item=""
                                            data-id="4" data-value=" CSS"> CSS</div>
                                        <div class="choices__item choices__item--selectable" data-item=""
                                            data-id="5" data-value=" Javascript"> Javascript</div>
                                        <div class="choices__item choices__item--selectable" data-item=""
                                            data-id="6" data-value=" C#"> C#</div>
                                        <div class="choices__item choices__item--selectable" data-item=""
                                            data-id="7" data-value=" Nodejs"> Nodejs</div>
                                    </div><input type="search" class="choices__input choices__input--cloned"
                                        autocomplete="off" autocapitalize="off" spellcheck="false" role="textbox"
                                        aria-autocomplete="list" aria-label="Skills" style="min-width: 1ch; width: 1ch;">
                                </div>
                                <div class="choices__list choices__list--dropdown" aria-expanded="false">
                                    <div class="choices__list" aria-multiselectable="true" role="listbox"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end card body -->
                </div>
            </div>
        </div>

    </div>
@endsection
