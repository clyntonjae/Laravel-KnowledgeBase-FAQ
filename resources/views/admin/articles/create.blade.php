@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.article.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.articles.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                <label for="title">{{ trans('cruds.article.fields.title') }}*</label>
                <input type="text" id="title" name="title" class="form-control"
                    value="{{ old('title', isset($article) ? $article->title : '') }}" required>
                @if($errors->has('title'))
                <em class="invalid-feedback">
                    {{ $errors->first('title') }}
                </em>
                @endif
                <p class="helper-block">
                    {{ trans('cruds.article.fields.title_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('slug') ? 'has-error' : '' }}">
                <label for="slug">{{ trans('cruds.article.fields.slug') }}*</label>
                <input type="text" id="slug" name="slug" class="form-control"
                    value="{{ old('slug', isset($article) ? $article->slug : '') }}" required>
                @if($errors->has('slug'))
                <em class="invalid-feedback">
                    {{ $errors->first('slug') }}
                </em>
                @endif
                <p class="helper-block">
                    {{ trans('cruds.article.fields.slug_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('short_text') ? 'has-error' : '' }}">
                <label for="short_text">{{ trans('cruds.article.fields.short_text') }}</label>
                <textarea id="short_text" name="short_text"
                    class="form-control ">{{ old('short_text', isset($article) ? $article->short_text : '') }}</textarea>
                @if($errors->has('short_text'))
                <em class="invalid-feedback">
                    {{ $errors->first('short_text') }}
                </em>
                @endif
                <p class="helper-block">
                    {{ trans('cruds.article.fields.short_text_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('full_text') ? 'has-error' : '' }}">
                <label for="full_text">{{ trans('cruds.article.fields.full_text') }}</label>
                <textarea class="page-content" name="full_text">{{ old('full_text', isset($article) ? $article->full_text : '') }}</textarea>
                @if($errors->has('full_text'))
                <em class="invalid-feedback">
                    {{ $errors->first('full_text') }}
                </em>
                @endif
                <p class="helper-block">
                    {{ trans('cruds.article.fields.full_text_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('is_private') ? 'has-error' : '' }}">
                <label><input type="checkbox" name="is_private" id="private-article"> Is Private</label>
                <div id="allowed-departments" style="display: none;">
                    <label>Allowed Departments</label>
                    <select name="allowed_departments[]" class="form-control select2 p-2" multiple="multiple">
                        @foreach ($departments as $department)
                            <option value="{{ $department->department_id }}">{{ $department->department }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Attachments</label>
                <input type="file" name="files[]" class="form-control" multiple="multiple">
            </div>
            <div class="form-group {{ $errors->has('category_id') ? 'has-error' : '' }}">
                <label for="category_id">{{ trans('cruds.article.fields.category') }}</label>
                <select name="category_id" id="category_id" class="form-control select2">
                    @foreach($categories as $id => $categories)
                    <option value="{{ $id }}" {{ (old('category_id', 0)==$id || isset($article) && $article->category->id == $id) ? 'selected' : '' }}>{{ $categories }}</option>
                    @endforeach
                </select>
                @if($errors->has('category_id'))
                <em class="invalid-feedback">
                    {{ $errors->first('category_id') }}
                </em>
                @endif
                <p class="helper-block">
                    {{ trans('cruds.article.fields.category_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('tags') ? 'has-error' : '' }}">
                <label for="tags">{{ trans('cruds.article.fields.tags') }}
                    <span class="btn btn-info btn-xs select-all" data-bool="1">{{ trans('global.select_all') }}</span>
                    <span class="btn btn-info btn-xs select-all" data-bool="0">{{ trans('global.deselect_all') }}</span></label>
                <select name="tags[]" id="tags" class="form-control select2" multiple="multiple">
                    @foreach($tags as $id => $tags)
                    <option value="{{ $id }}" {{ (in_array($id, old('tags', [])) || isset($article) && $article->tags->contains($id)) ? 'selected' : '' }}>{{ $tags }}</option>
                    @endforeach
                </select>
                @if($errors->has('tags'))
                <em class="invalid-feedback">
                    {{ $errors->first('tags') }}
                </em>
                @endif
                <p class="helper-block">
                    {{ trans('cruds.article.fields.tags_helper') }}
                </p>
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>
@endsection
@section('styles')
    <style>
        label{
            font-weight: 700;
        }
    </style>
@endsection
@section('scripts')
<script>
    $(document).ready(function ($q){
        $('.select2').select2();
        $(".page-content").summernote({
            dialogsInBody: true,
            dialogsFade: true,
            height: "500px",
        });

        toggle_departments();
        $(document).on('click', '#private-article', function (e){
            toggle_departments();
        });

        function toggle_departments(){
            if($('#private-article').prop('checked')){
                $('#allowed-departments').slideDown();
                $('#allowed-departments select').prop('required', true);
            }else{ // is public
                $('#allowed-departments').slideUp();
                $('#allowed-departments select').prop('required', false);
            }
        }

        $(document).on('click', '.select-all', function (e){
            e.preventDefault();
            var val = $(this).data('bool') ? true : false;
            $('#tags > option').prop('selected', val).trigger('change');
        })

        $('input[name="title"]').change(function(e) {
            $.get('{{ route('articles.check_slug') }}', 
                { 'title': $(this).val() }, 
                function( data ) {
                    $('input[name="slug"]').val(data.slug);
                }
            );
        });
    });
</script>
@endsection