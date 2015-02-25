<?php namespace App\Http\Controllers;

use App\Models\Dvd;
use Illuminate\Http\Request;

use DB;

class DvdController extends Controller {

  public function search()
  {
    return view('search', [
    ]);
  }

  public function results(Request $request)
  {
    $title = $request->input('title');
    $genre = $request->input('genre');
    $rating = $request->input('rating');

    if ($genre) {
      $genre_query = DB::table('genres')
        ->select('genre_name')
        ->where('id', '=', $genre);
      $genre_name = $genre_query->get()[0];
    } else $genre_name = '';

    if ($rating) {
      $rating_query = DB::table('ratings')
        ->select('rating_name')
        ->where('id', '=', $rating);
      $rating_name = $rating_query->get()[0];
    } else $rating_name = '';

    return view('results', [
        'dvds' => (new Dvd())->search([ 'title' => $title , 'genre' => $genre , 'rating' => $rating ]),
        'searchTitle' => $title,
        'searchGenre' => $genre_name,
        'searchRating' => $rating_name,
        'dvdCount' => Dvd::create()->count()
    ]);
  }

  public function createReview(Request $request, $id)
  {
    $name_query = DB::table('dvds')
      ->select('title')
      ->where('id', '=', $id);
    $name_array = $name_query->get();
    if (sizeof($name_array) > 0) {
      $name = $name_array[0];
    } else {
      return redirect('/dvds/');
    }
    $reviews_query = DB::table('reviews')
      ->select('title', 'description', 'rating')
      ->where('dvd_id', '=', $id);
    $reviews = $reviews_query->get();
    return view('reviews', [
        'dvd' => $name,
        'dvd_id' => $id,
        'reviews' => $reviews
    ]);
  }

  public function storeReview(Request $request)
  {
    $id = $request->input('dvd_id');
    $validation = \Validator::make($request->all(), [
      'dvd_id' => 'required|integer',
      'title' => 'required|min:5',
      'rating' => 'required|integer',
      'description' => 'required|min:20'
      ]);
    if ($validation->passes()) {
      DB::table('reviews')->insert([
        'dvd_id' => $request->input('dvd_id'),
        'title' => $request->input('title'),
        'rating' => $request->input('rating'),
        'description' => $request->input('description')
        ]);
      return redirect('/dvds/' . $id)->with('success', 'Review successfully saved');
    } else {
      return redirect('/dvds/' . $id)
      ->withInput()
      ->withErrors($validation);
    }
    return view('/dvds/' . $id, [
    ]);
  }

} 