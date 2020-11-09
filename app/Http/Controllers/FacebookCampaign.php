<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FacebookCampaign extends Controller
{
    //
    // Create new compaign 
    public function createNewCompaign()
    {
        $form = true;
        return view('reports.createNewCompaign', compact('form'));
    }

    // Save it
    public function saveCompaign()
    {
        $form = true;
        return view('reports.saveCompaign', compact('form'));
    }

    // Get the reports.
    
}
