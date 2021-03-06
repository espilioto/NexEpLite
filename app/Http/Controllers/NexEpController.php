<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Show;
use Illuminate\Http\Request;

class NexEpController extends Controller
{

	public function index ()
    {
        // $this->updateAirstampColumn(); //this should be a cronjob

        $shows = Show::orderBy('airstamp', 'asc')->get();
    	
        return view('welcome', [
            'shows' => $shows
        ]);
    }

    public function search (Request $request) 
    {
        //validate input
        $this->validate($request, [
            'showName' => 'required|max:255',
        ]);

        // get show data
        $queryurl = 'http://api.tvmaze.com/singlesearch/shows?q=' . $request['showName']; 
        @$show = json_decode(file_get_contents($queryurl, true)); //error control operator. suppresses the error if no show is found/the response is empty

        //if the show exists
        if (!empty($show)) {
            $showData = array(
                'showID' => $show->id,
                'showTvmazeUrl' => $show->url,
                'showName' => $show->name,
                'showStatus' => $show->status,
                'showPic' => $show->image->medium,
            );
            //if the show exists in the watchlist, disable the add button
            if (Show::where('showid', $show->id)->count()) {
                $exists = true;
            } else {
                $exists = false;
            }        
        } else {
            $showData = 'No show found';
        }
        
        //if the show is complete there wont be a nextepisode property
        if (!empty($show->_links->nextepisode)) {
            $nexteplink = $show->_links->nextepisode->href;
            //get next ep data
            $nextep = json_decode(file_get_contents($nexteplink, true));

            $nextEpData = array(
                'nextEpSeason' => $nextep->season,
                'nextEpNumber' => $nextep->number,
                'nextEpTimestamp' => $nextep->airstamp
            );
        } else {
            $nextEpData = 'No future episode data found';
        }

        //get the existing shows and pass them to the view so it updates
        $shows = Show::orderBy('created_at', 'asc')->get();

        return view('welcome', compact('showData', 'nextEpData', 'shows', 'exists'));
    }

    public function store (Request $request)
    {
        $show = new Show;
        $show->showid = $request['showID'];
        $show->name = $request['showName'];
        $show->tvmazeUrl = $request['showTvmazeUrl'];
        $show->airstamp = $request['nextEpTimestamp'];

        $show->save();

        return redirect('/');
    }

    public function destroy (Request $request, Show $show)
    {
        $show->delete();

        return redirect('/');
    }

    //updates the DB column where the next episode timestamp is stored
    //this should be a cron job and update like once a day or something
    // 
    //https://laravel.com/docs/5.1/scheduling
    //http://www.sitepoint.com/managing-cronjobs-with-laravel/
    //https://github.com/liebig/cron
    private function updateAirstampColumn () 
    {
        $shows = Show::get();
        $showUrl = $nexEpUrl = $showResponse = $nexEpResponse = "";

        foreach ($shows as $show) {
            $showUrl = 'http://api.tvmaze.com/shows/' . $show->showid; 
            @$showResponse = json_decode(file_get_contents($showUrl, true));
            
            $nexEpUrl = $showResponse->_links->nextepisode->href;
            @$nexEpResponse = json_decode(file_get_contents($nexEpUrl, true));

            if (!$nexEpResponse->airstamp == $show->airstamp) {
                $show->update(array("airstamp" => $nexEpResponse->airstamp));
            }
        }
    }

}
