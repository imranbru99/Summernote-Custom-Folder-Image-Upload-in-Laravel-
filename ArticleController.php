<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use File;
class ArticleController extends Controller
{
     /**
     * The article list view.
     *
     * @return Illuminate\Http\View
     */
    public function index()
    {
        $articles = Article::get();

        return view('articles.index', compact('articles'));
    }

    /**
     * The article view.
     *
     * @return Illuminate\Http\View
     */
    public function show($id)
    {
        $article = Article::where('id', $id)
            ->first();

        return view('articles.show', compact('article'));
    }

    /**
     * The article create view.
     *
     * @return Illuminate\Http\View
     */
    public function create()
    {
        return view('articles.create');
    }

    /**
     * The article create view.
     *
     * @return Illuminate\Http\View
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
             'title' => 'required',
             'description' => 'required'
        ]);

        $dom = new \DomDocument();
        $dom->loadHtml($validated['description'], LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $image_file = $dom->getElementsByTagName('img');

        foreach($image_file as $key => $image) {
            $data = $image->getAttribute('src');
            list($type, $data) = explode(';', $data);
            list(, $data) = explode(',', $data);
            $img_data = base64_decode($data);
            
            $directory = '/assets/images/post/' . date("Y") . '/' . date("m") . '/';
            if (!File::exists(public_path($directory))) {
                File::makeDirectory(public_path($directory), 0777, true);
            }
            
            $image_name = uniqid() . '.png';
            $path = public_path($directory . $image_name);
            file_put_contents($path, $img_data);
            
            $image->removeAttribute('data-filename'); 
            $image->setAttribute('src', $directory . $image_name);
            $image->setAttribute('data-filename', $image_name);
        }
 
        $validated['description'] = $dom->saveHTML();

        Article::create($validated);

        return redirect()
            ->route('article.index')
            ->with('success', 'Article created successfully.');
    }
}
