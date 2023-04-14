<?php

namespace App\Http\Controllers\Admin;

use App\Article;
use App\Category;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyArticleRequest;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Tag;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ArticlesController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('article_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $articles = Article::all();

        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        abort_if(Gate::denies('article_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $categories = Category::all()->pluck('name', 'id');

        $tags = Tag::all()->pluck('name', 'id');

        return view('admin.articles.create', compact('categories', 'tags'));
    }

    public function store(StoreArticleRequest $request)
    {
        DB::beginTransaction();
        try {
            $request->except('files');
            $article = Article::create($request->all());
            $article->tags()->sync($request->input('tags', []));

            if(count($request->files) > 0){
                $validation = Validator::make($request->all(), [
                    'files' => 'size:5120'
                ]);
    
                if ($validation->fails()){
                    return redirect()->back()->withInput()->with('error', 'Sorry, your file is too large.'); 
                }

                foreach ($request->file('files') as $i => $file) {
                    $this->upload_file($file, $article->slug, $article->id);
                }
            }

            DB::commit();
            return redirect()->route('admin.articles.index');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', 'An error occured. Please try again');
        }
    }

    private function upload_file($file, $dir, $id){
        try {
            $file_name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-');
            $file_ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

            $filename = $file_name.'.'.$file_ext;

            if(!Storage::disk('public')->exists('/files/'.$dir)){
                Storage::disk('public')->makeDirectory('/files/'.$dir);
            }
            
            $file->move(storage_path('/app/public/files/'.$dir), $filename);

            DB::table('article_files')->insert([
                'article_id' => $id,
                'filename' => $filename,
                'created_by' => Auth::user()->email,
                'last_modified_by' => Auth::user()->email
            ]);

            return 1;
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function remove_file($id){
        DB::beginTransaction();
        try {
            DB::table('article_files')->where('id', $id)->delete();
            DB::commit();
            return response()->json(['success' => 1, 'message' => 'File deleted.']);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['success' => 0, 'message' => 'An error occured. Please try again.']);
        }
    }

    public function edit(Article $article)
    {
        abort_if(Gate::denies('article_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $categories = Category::all()->pluck('name', 'id');

        $tags = Tag::all()->pluck('name', 'id');

        $article->load('category', 'tags');

        $files = DB::table('article_files')->where('article_id', $article->id)->get();

        return view('admin.articles.edit', compact('categories', 'tags', 'article', 'files'));
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        DB::beginTransaction();
        try {
            $article->update($request->except('files'));
            $article->tags()->sync($request->input('tags', []));

            if(count($request->files) > 0){
                $validation = Validator::make($request->all(), [
                    'files' => 'max:5120'
                ]);

                if ($validation->fails()){
                    return redirect()->back()->withInput()->with('error', 'Sorry, your file is too large.'); 
                }

                foreach ($request->file('files') as $i => $file) {
                    $this->upload_file($file, $article->slug, $article->id);
                }
            }

            DB::commit();
            return redirect()->route('admin.articles.index'); 
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', 'An error occured. Please try again.'); 
        }
    }

    public function show(Article $article)
    {
        abort_if(Gate::denies('article_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $article->load('category', 'tags');

        return view('admin.articles.show', compact('article'));
    }

    public function destroy(Article $article)
    {
        abort_if(Gate::denies('article_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $article->delete();

        return back();
    }

    public function massDestroy(MassDestroyArticleRequest $request)
    {
        Article::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
