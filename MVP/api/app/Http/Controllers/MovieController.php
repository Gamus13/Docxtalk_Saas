<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;

class MovieController extends Controller
{
    //
    public function index(){
        $movies = Movie::all();
        return response()->json(['success' =>true, 'message' => 'Fetch All Movies', 'data' => $movies],200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
        ]);

        $movie = Movie::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Movie Created',
            'data' => $movie
        ], 200);
    }

}
