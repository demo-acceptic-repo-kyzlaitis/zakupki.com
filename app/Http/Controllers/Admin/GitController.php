<?php

namespace App\Http\Controllers\Admin;



use Event;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Validator;


class GitController extends Controller
{
    public function listsAllRemoteBranches(Request $request) {

        if(env('APP_ENV') !== 'server') {

            $gitRemoteBranches = [];
            exec('git fetch');
            exec('git for-each-ref --sort=-committerdate "refs/remotes/origin/" --format="%(objectname)?%(refname)?%(authorname)?%(authoremail)?%(authordate)?%(subject)"', $gitRemoteBranches);

            $currentBranch = explode('/', exec('git symbolic-ref HEAD'))[2];

            return view('admin.pages.git.list', compact('gitRemoteBranches', 'currentBranch'));
        }
    }


    public function switchBranch(Request $request) {

        if(env('APP_ENV') !== 'server') {

            $result = exec('git checkout -f ' . escapeshellarg($request->get('branch')));
            $result .= exec('composer dump-autoload');

            $request->session()->flash('status', $result."\n\nSuccess switch!");

            return redirect()->route('admin::git.list');
        }
    }

    public function make(Request $request) {

        if(env('APP_ENV') !== 'server') {

            exec('gulp');
            exec('composer dump-autoload');
            exec('php artisan migrate');

            $request->session()->flash('status', 'Success gulp!');

            return redirect()->route('admin::git.list');
        }
    }
}
