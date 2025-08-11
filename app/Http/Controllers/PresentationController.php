<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class PresentationController extends Controller
{
    public function show()
    {
        $path = base_path('docs/End-User-Presentation.md');
        $markdownContent = File::exists($path) ? File::get($path) : '### Slide 1: Presentation\n- No content found at docs/End-User-Presentation.md';

        return view('presentation.show', [
            'markdownContent' => $markdownContent,
            'appName' => config('app.name', 'RCS App'),
        ]);
    }
}


