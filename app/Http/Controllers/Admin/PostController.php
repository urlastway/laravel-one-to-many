<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::all();

        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // validazione
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:65535',
            'published' => 'sometimes|accepted'
        ]);
        // prendo i dati dalla request e creo il post
        $data = $request->all();
        $newPost = new Post();
        $newPost->fill($data);

        $newPost->slug = $this->getSlug($data['title']);

        $newPost->published = isset($data['published']); // true o false
        $newPost->save();
        // redirect alla pagina del post appena creato
        return redirect()->route('admin.posts.show', $newPost->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        return view('admin.posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        // validazione
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:65535',
            'published' => 'sometimes|accepted'
        ]);
        // aggiornamento
        $data = $request->all();
        // se cambia il titolo genero un altro slug
        if( $post->title != $data['title'] ) {
            $post->slug = $this->getSlug($data['title']);
        }
        $post->fill($data);

        $post->published = isset($data['published']); // true o false

        $post->save();
        // redirect
        return redirect()->route('admin.posts.show', $post->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index');
    }

    private function getSlug($title)
    {
        $slug = Str::of($title)->slug('-');
        $count = 1;

        while( Post::where('slug', $slug)->first() ) {
            $slug = Str::of($title)->slug('-') . "-{$count}";
            $count++;
        }

        return $slug;
    }
}