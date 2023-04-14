@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.article.title_singular') }}
    </div>

    <div class="card-body">
        @if(session()->has('error'))
            <div class="row">
                <div class="col">
                    <div class="alert alert-danger fade show text-center" role="alert">
                        {{ session()->get('error') }}
                    </div>
                </div>
            </div>
        @endif
        <form action="{{ route("admin.articles.update", [$article->id]) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
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
                <textarea id="short_text" name="short_text" class="form-control ">{{ old('short_text', isset($article) ? $article->short_text : '') }}</textarea>
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
            <div class="form-group">
                <label>Attachments</label>
                <input type="file" name="files[]" class="form-control" multiple="multiple">
                <ul>
                    @foreach ($files as $file)
                        <li id="item-{{ $file->id }}">
                            <a href="{{ asset('/storage/files/'.$article->slug.'/'.$file->filename) }}" target="_blank">{{ $file->filename }}</a>
                            <span class="open-remove-modal" data-id="{{ $file->id }}" data-name="{{ $file->filename }}" style="font-size: 9pt !important; font-weight: 400; cursor: pointer">&times;</span>
                        </li>
                    @endforeach
                </ul>
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
<div class="modal fade" id="remove-file-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Delete File?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Delete <span id="file-name"></span>?
                <input type="hidden" id="file-id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary remove-file">Confirm</button>
            </div>
        </div>
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
    $(document).ready(function (){
        $('.select2').select2();
        $(".page-content").summernote({
            dialogsInBody: true,
            dialogsFade: true,
            height: "500px",
        });

        $(document).on('click', '.select-all', function (e){
            e.preventDefault();
            var val = $(this).data('bool') ? true : false;
            $('#tags > option').prop('selected', val).trigger('change');
        })

        $(document).on('click', '.open-remove-modal', function (e){
            e.preventDefault();
            $('#file-id').val($(this).data('id'));
            $('#file-name').text($(this).data('name'));
            modal_control('#remove-file-modal', 'show');
        });

        $(document).on('click', '.remove-file', function (e){
            e.preventDefault();
            var id = $('#file-id').val();
            $.ajax({
                url: '/admin/remove_file/article/' + id,
                type: 'GET',
                success: function (response){
                    modal_control('#remove-file-modal', 'hide');
                    $('#item-' + id).remove();
                }
            });
        });

        $('input[name="title"]').change(function(e) {
            $.get('{{ route('articles.check_slug') }}', 
            { 'title': $(this).val() }, 
            function( data ) {
                $('input[name="slug"]').val(data.slug);
            });
        });
    });
</script>
@endsection